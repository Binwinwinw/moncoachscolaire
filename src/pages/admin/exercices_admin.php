<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
// Page d'administration des exercices
// Affiche tous les exercices avec filtres niveau et classe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__, 2) . '/database/connection.php';
if (!isset($pdo) || !$pdo) {
    die('<div class="bg-red-100 text-red-700 p-3 rounded mb-4">Erreur : Connexion à la base de données impossible. Vérifiez la configuration dans .env.</div>');
}
require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
requireAdmin();
$page_title = 'Gestion des Exercices';
$page_css = 'exercices-admin.css';

// Récupération des niveaux et classes
$levels = [
    'college' => ['6ème', '5ème', '4ème', '3ème'],
    'lycee'   => ['Seconde', 'Première', 'Terminale'],
    'bac'     => ['BAC'],
];

// Récupération des matières disponibles (distinctes) et cartographie par niveau
$subjects = [];
$subjects_by_level = [];
try {
    // Helpers pour normaliser et choisir le libellé canonique
    $canonical = [];
    $subjects_by_level_keys = [];

    $subject_key = function ($s) {
        $s = trim((string) $s);
        if ($s === '') {
            return '';
        }
        $k = mb_strtolower($s, 'UTF-8');
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $k);
        if ($trans !== false) {
            $k = $trans;
        }
        $k = preg_replace('/[^a-z0-9]+/', '', $k);
        return $k;
    };

    $prefer_subject = function ($new, $old) {
        if ($new === $old) {
            return false;
        }
        // prefer labels that contain multibyte (accents)
        $new_mb = preg_match('/[^\x00-\x7F]/', $new);
        $old_mb = preg_match('/[^\x00-\x7F]/', $old);
        if ($new_mb && !$old_mb) {
            return true;
        }
        // prefer without stray apostrophe
        if (strpos($old, "'") !== false && strpos($new, "'") === false) {
            return true;
        }
        // prefer longer (more informative)
        if (mb_strlen($new, 'UTF-8') > mb_strlen($old, 'UTF-8')) {
            return true;
        }
        return false;
    };

    $stmt = $pdo->query("SELECT DISTINCT Level, Subject FROM exercises ORDER BY Subject");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sub = trim((string) ($row['Subject'] ?? ''));
        if ($sub === '') {
            continue;
        }
        $k = $subject_key($sub);
        if ($k === '') {
            continue;
        }

        if (!isset($canonical[$k]) || $prefer_subject($sub, $canonical[$k])) {
            $canonical[$k] = $sub;
        }

        $lvl = $row['Level'] ?? '';
        if ($lvl) {
            if (!isset($subjects_by_level_keys[$lvl])) {
                $subjects_by_level_keys[$lvl] = [];
            }
            if (!in_array($k, $subjects_by_level_keys[$lvl])) {
                $subjects_by_level_keys[$lvl][] = $k;
            }
        }
    }

    // Construire la mapping finale par niveau en utilisant les labels canoniques
    foreach ($subjects_by_level_keys as $lvl => $keys) {
        $subjects_by_level[$lvl] = [];
        foreach ($keys as $k) {
            if (isset($canonical[$k]) && !in_array($canonical[$k], $subjects_by_level[$lvl])) {
                $subjects_by_level[$lvl][] = $canonical[$k];
            }
        }
    }

    $subjects = array_values($canonical);

} catch (Exception $e) {
    // en cas d'erreur DB, laisser les listes vides
}

// Filtres GET
$filtre_niveau = $_GET['niveau'] ?? '';
// Compatibilité ascendante : accepter 'classe' comme alias pour 'matiere'
$filtre_matiere = $_GET['matiere'] ?? $_GET['classe'] ?? '';

// Construction de la requête SQL avec filtres dynamiques
$sql = 'SELECT * FROM exercises WHERE 1';
$params = [];
if ($filtre_niveau) {
    $sql .= ' AND Level = ?';
    $params[] = $filtre_niveau;
}
if ($filtre_matiere) {
    $sql .= ' AND Subject = ?';
    $params[] = $filtre_matiere;
}
$sql .= ' LIMIT 100';

$exercices = [];
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $exercices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="<?= isset($baseUrl) ? $baseUrl : '' ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= isset($baseUrl) ? $baseUrl : '' ?>/assets/css/pages/exercices-admin.css">
</head>
<body class="exercices-admin">
    <header class="mb-8 border-b pb-4">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Gestion des Exercices</h1>
        <nav class="mb-2">
            <a href="<?php echo site_url('admin/dashboard_admin'); ?>" class="text-blue-600 hover:underline">← Retour Admin</a>
        </nav>
    </header>
    <main class="max-w-5xl mx-auto p-6 bg-white rounded-2xl shadow-lg">
        <section class="mb-6">
            <form method="get" id="filtre-exercices" class="flex flex-wrap gap-4 items-center bg-gray-50 p-4 rounded-xl shadow">
                <input type="hidden" name="page" value="exercices_admin">
                <label for="niveau" class="font-medium">Niveau scolaire :</label>
                <select name="niveau" id="niveau" onchange="updateSubjects()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400">
                    <option value="">Tous</option>
                    <?php foreach (array_merge(...array_values($levels)) as $classe): ?>
                        <option value="<?= htmlspecialchars($classe) ?>" <?= $filtre_niveau === $classe ? 'selected' : '' ?>><?= htmlspecialchars($classe) ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="matiere" class="font-medium">Matière :</label>
                <select name="matiere" id="matiere" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400">
                    <option value="">Toutes</option>
                    <?php foreach ($subjects as $sub): ?>
                        <option value="<?= htmlspecialchars($sub) ?>" <?= $filtre_matiere === $sub ? 'selected' : '' ?>><?= htmlspecialchars($sub) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">Filtrer</button>
                <span class="text-sm text-gray-500">Remplacé : <strong>Classe</strong> → <strong>Matière</strong> pour éviter les doublons.</span>
            </form>
        </section>
        <section>
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4">Erreur : <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($exercices as $ex): ?>
                    <div class="bg-white rounded-xl shadow p-4 flex flex-col gap-2">
                        <div class="text-xs text-gray-500 mb-1 font-mono">
                            <?= htmlspecialchars($ex['Identifier'] ?? '-') ?>
                        </div>
                        <div class="font-semibold text-lg text-slate-800">
                            <?= htmlspecialchars($ex['Title'] ?? '-') ?>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Niveau : <?= htmlspecialchars($ex['Level'] ?? '-') ?></span>
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Matière : <?= htmlspecialchars($ex['Subject'] ?? '-') ?></span>
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Type : <?= htmlspecialchars($ex['AnswerType'] ?? '-') ?></span>
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">XP : <?= htmlspecialchars($ex['XP_Points'] ?? '-') ?></span>
                            <?php if (isset($ex['is_active'])): ?>
                                <span class="px-2 py-1 rounded text-xs <?= $ex['is_active'] ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' ?>">
                                    <?= $ex['is_active'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex gap-2 mt-3">
                            <a href="#" class="bg-blue-500 text-white px-3 py-1 rounded shadow hover:bg-blue-600 transition">✏️ Modifier</a>
                            <a href="#" class="bg-red-500 text-white px-3 py-1 rounded shadow hover:bg-red-600 transition">🗑️ Supprimer</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <script>
    // JS pour filtrer dynamiquement les matières selon le niveau
    const levels = <?= json_encode($levels) ?>;
    const subjectsByLevel = <?= json_encode($subjects_by_level) ?>;
    const allSubjects = <?= json_encode($subjects) ?>;
    function updateSubjects() {
        const niveau = document.getElementById('niveau').value;
        const matiereSelect = document.getElementById('matiere');
        matiereSelect.innerHTML = '<option value="">Toutes</option>';
        let opts = allSubjects;
        if (niveau && subjectsByLevel[niveau]) {
            opts = subjectsByLevel[niveau];
        }
        opts.forEach(function(sub) {
            const opt = document.createElement('option');
            opt.value = sub;
            opt.textContent = sub;
            if (sub === <?= json_encode($filtre_matiere) ?>) opt.selected = true;
            matiereSelect.appendChild(opt);
        });
    }
    // Init on load
    document.addEventListener('DOMContentLoaded', function(){
        // If niveau select uses onchange attribute, make sure it triggers updateSubjects
        const niveauEl = document.getElementById('niveau');
        if (niveauEl) niveauEl.addEventListener('change', updateSubjects);
        updateSubjects();
    });
    </script>
    <script src="<?= isset($baseUrl) ? $baseUrl : '' ?>/assets/js/exercises-admin.js" defer></script>
</body>
</html>
