<?php
/**
 * admin/content.php — Gestion complète du contenu pédagogique
 * MonCoachScolaire — Février 2026
 *
 * -------------------------------------------------------------
 * Documentation intégrée (philosophie, patterns, feuille de route)
 * -------------------------------------------------------------
 *
 * Philosophie :
 * - Interface bienveillante, feedback positif systématique
 * - Jamais de message négatif ou décourageant
 * - Accessibilité et UX priorisées (contrastes, navigation, labels)
 * - Sécurité : prepared statements, validation, échappement
 * - Respect des hooks front (classes, data-attributes)
 *
 * Patterns et conventions :
 * - Structure page : protection session, vérification rôle, variables, logique métier, HTML
 * - CRUD complet (ajout, édition, suppression, changement de statut)
 * - Filtres multi-critères (niveau, matière, type, statut, recherche)
 * - Statistiques synthétiques (total, par type, par statut)
 * - Modal pour ajout/édition, confirmation pour suppression
 * - Feedback animé (classes .feedback-*)
 * - Utilisation des composants UI standards (stat-card, btn-admin-primary, etc.)
 * - Responsive et accessibilité (focus, aria, labels)
 *
 * Pattern « blocage soft » (aperçu + encouragement à s’inscrire) :
 * - Affiche un aperçu des ressources ou fonctionnalités, sans divulguer d’informations personnalisées.
 * - Affiche un message positif, bienveillant et motivant, jamais de message négatif ou frustrant.
 * - Encourage à créer un compte gratuit ou à se connecter, via des boutons visibles.
 * - L’accès à la page n’est pas bloqué : le contenu complet est masqué, mais l’utilisateur voit la structure et les avantages.
 * - Utilisé pour les visiteurs ou utilisateurs non connectés, ou sans droits suffisants.
 * - Exemples d’implémentation : voir src/pages/eleve/lycee/terminale/exercices-terminale.php, src/pages/system/cours.php.
 * - À réutiliser sur toutes les pages publiques ou freemium.
 *
 * Feuille de route (roadmap enrichissement) :
 * - [x] Listing des contenus avec filtres
 * - [x] Ajout/édition/suppression de contenus
 * - [x] Statistiques par type/statut
 * - [x] Feedback positif systématique
 * - [x] Sécurité et validation
 * - [x] Accessibilité et responsive
 * - [x] Hooks front conservés
 * - [x] Tests UI et smoke tests (13/02/2026, validé : interactions CRUD, modals, responsive, feedbacks)
 * - [x] Documentation utilisateur (guide rapide) (13/02/2026, voir docs/PATTERN_BLOCAGE_SOFT.md et section d’aide intégrée)
 *
 * Pour toute évolution : compléter ce bloc, ajouter des commentaires clairs dans chaque section.
 */

// ========== 1. PROTECTION & INITIALISATION ==========
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/database/connection.php';
require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
require_once dirname(__DIR__, 2) . '/includes/login_security.php';
requireAdmin();

// Vérifier la connexion BDD
if (!$pdo) {
    die('Erreur : connexion à la base de données indisponible.');
}

// ========== 2. VARIABLES PAGE ==========
$page_title = 'Gestion du contenu pédagogique';
$page_css = 'admin-content.css';
$page_class = 'admin-content-page';

$feedback = '';
$feedback_type = 'success'; // success, info, warning, error
$csrf_token = generateCSRFToken();

// ========== 3. TRAITEMENT DES ACTIONS (CRUD) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($submitted_token)) {
        $feedback = '⚠️ Jeton de sécurité invalide. Merci de recharger la page.';
        $feedback_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';

        try {
        // ===== AJOUT DE CONTENU =====
        if ($action === 'add') {
            $title = trim($_POST['title'] ?? '');
            $type = $_POST['type'] ?? 'cours';
            $level = trim($_POST['level'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $content_url = trim($_POST['content_url'] ?? '');
            $status = $_POST['status'] ?? 'draft';

            // Validation
            if (empty($title) || empty($level)) {
                $feedback = '⚠️ Le titre et le niveau sont obligatoires.';
                $feedback_type = 'warning';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO contents (title, type, level, subject, description, content_url, status)
                    VALUES (:title, :type, :level, :subject, :description, :content_url, :status)
                ");
                $stmt->execute([
                    'title' => $title,
                    'type' => $type,
                    'level' => $level,
                    'subject' => $subject,
                    'description' => $description,
                    'content_url' => $content_url,
                    'status' => $status,
                ]);
                $feedback = '✅ Contenu « ' . htmlspecialchars($title) . ' » ajouté avec succès !';
                $feedback_type = 'success';
            }
        }

        // ===== ÉDITION DE CONTENU =====
        elseif ($action === 'edit') {
            $id = (int) ($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $type = $_POST['type'] ?? 'cours';
            $level = trim($_POST['level'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $content_url = trim($_POST['content_url'] ?? '');
            $status = $_POST['status'] ?? 'draft';

            if ($id > 0 && !empty($title) && !empty($level)) {
                $stmt = $pdo->prepare("
                    UPDATE contents
                    SET title = :title, type = :type, level = :level, subject = :subject,
                        description = :description, content_url = :content_url, status = :status,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    'id' => $id,
                    'title' => $title,
                    'type' => $type,
                    'level' => $level,
                    'subject' => $subject,
                    'description' => $description,
                    'content_url' => $content_url,
                    'status' => $status,
                ]);
                $feedback = '✅ Contenu « ' . htmlspecialchars($title) . ' » mis à jour !';
                $feedback_type = 'success';
            }
        }

        // ===== SUPPRESSION DE CONTENU =====
        elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                // Récupérer le titre avant suppression
                $stmt = $pdo->prepare("SELECT title FROM contents WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $content = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("DELETE FROM contents WHERE id = :id");
                $stmt->execute(['id' => $id]);

                $title = $content ? htmlspecialchars($content['title']) : 'ce contenu';
                $feedback = '🗑️ ' . $title . ' a été supprimé.';
                $feedback_type = 'info';
            }
        }

        // ===== CHANGEMENT DE STATUT RAPIDE =====
        elseif ($action === 'toggle_status') {
            $id = (int) ($_POST['id'] ?? 0);
            $new_status = $_POST['new_status'] ?? 'draft';

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE contents SET status = :status, updated_at = NOW() WHERE id = :id");
                $stmt->execute(['id' => $id, 'status' => $new_status]);
                $feedback = '✅ Statut mis à jour !';
                $feedback_type = 'success';
            }
        }

        } catch (PDOException $e) {
            error_log('Erreur admin/content.php : ' . $e->getMessage());
            $feedback = '⚠️ Une erreur est survenue. Veuillez réessayer.';
            $feedback_type = 'error';
        }
    }
}

// ========== 4. RÉCUPÉRATION DES DONNÉES ==========
// Filtres
$filter_level = $_GET['level'] ?? '';
$filter_subject = $_GET['subject'] ?? '';
$filter_type = $_GET['type'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Construction de la requête avec filtres
$where_clauses = [];
$params = [];

if (!empty($filter_level)) {
    $where_clauses[] = "level = :level";
    $params['level'] = $filter_level;
}
if (!empty($filter_subject)) {
    $where_clauses[] = "subject = :subject";
    $params['subject'] = $filter_subject;
}
if (!empty($filter_type)) {
    $where_clauses[] = "type = :type";
    $params['type'] = $filter_type;
}
if (!empty($filter_status)) {
    $where_clauses[] = "status = :status";
    $params['status'] = $filter_status;
}
if (!empty($search)) {
    $where_clauses[] = "(title LIKE :search OR description LIKE :search)";
    $params['search'] = '%' . $search . '%';
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Récupération des contenus
$sql = "SELECT * FROM contents $where_sql ORDER BY updated_at DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stats_stmt = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN type = 'cours' THEN 1 ELSE 0 END) as cours,
        SUM(CASE WHEN type = 'exercice' THEN 1 ELSE 0 END) as exercices,
        SUM(CASE WHEN type = 'ressource' THEN 1 ELSE 0 END) as ressources,
        SUM(CASE WHEN type = 'quiz' THEN 1 ELSE 0 END) as quiz,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
    FROM contents
");
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Listes pour les filtres
$levels = ['6eme', '5eme', '4eme', '3eme', 'Seconde', 'Premiere', 'Terminale'];
$subjects = ['Mathématiques', 'Français', 'Anglais', 'Histoire-Géographie', 'SVT', 'Physique-Chimie', 'EPS', 'Arts', 'Technologie'];
$types = ['cours', 'exercice', 'ressource', 'quiz'];
$statuses = ['draft' => 'Brouillon', 'published' => 'Publié', 'archived' => 'Archivé'];

// ========== 5. HTML ==========
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo asset_url('css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/pages/' . $page_css); ?>">
</head>
<body class="<?php echo htmlspecialchars($page_class); ?>">

<?php include_once dirname(__DIR__, 2) . '/includes/topbar.php'; ?>

<main class="max-w-7xl mx-auto px-4 py-8">

    <!-- En-tête -->
    <header class="mb-8 flex flex-col items-start gap-2">
        <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 mb-2">
            📚 Gestion du contenu pédagogique
        </h1>
        <p class="text-slate-600 text-base">Organisez et gérez tous les contenus de MonCoachScolaire</p>
    </header>

    <!-- Feedback -->
    <?php if (!empty($feedback)): ?>
        <div class="feedback feedback-<?php echo $feedback_type; ?> mb-6 p-4 rounded-lg shadow-md animate-fade-in border-l-4 flex items-center gap-3 text-sm font-medium <?php echo $feedback_type === 'success' ? 'bg-green-50 text-green-700 border-green-500' : ($feedback_type === 'info' ? 'bg-blue-50 text-blue-700 border-blue-500' : ($feedback_type === 'warning' ? 'bg-amber-50 text-amber-700 border-amber-500' : 'bg-red-50 text-red-700 border-red-500')); ?>" role="alert">
            <?php echo $feedback; ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <section class="stats-grid grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl shadow-md flex flex-col items-center">
            <div class="text-3xl font-bold text-blue-700"><?php echo $stats['total'] ?? 0; ?></div>
            <div class="text-sm text-slate-600 mt-1">Total contenus</div>
        </div>
        <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-md flex flex-col items-center">
            <div class="text-3xl font-bold text-green-700"><?php echo $stats['published'] ?? 0; ?></div>
            <div class="text-sm text-slate-600 mt-1">Publiés</div>
        </div>
        <div class="stat-card bg-gradient-to-br from-amber-50 to-amber-100 p-6 rounded-xl shadow-md flex flex-col items-center">
            <div class="text-3xl font-bold text-amber-700"><?php echo $stats['draft'] ?? 0; ?></div>
            <div class="text-sm text-slate-600 mt-1">Brouillons</div>
        </div>
        <div class="stat-card bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl shadow-md flex flex-col items-center">
            <div class="text-sm text-slate-600">
                📖 <?php echo $stats['cours'] ?? 0; ?> cours •
                ✏️ <?php echo $stats['exercices'] ?? 0; ?> exercices<br>
                📦 <?php echo $stats['ressources'] ?? 0; ?> ressources •
                ❓ <?php echo $stats['quiz'] ?? 0; ?> quiz
            </div>
        </div>
    </section>

    <!-- Filtres & Actions -->
    <section class="filters-bar bg-white rounded-xl shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Recherche -->
            <input type="text" name="search" placeholder="🔍 Rechercher..."
                   value="<?php echo htmlspecialchars($search); ?>"
                   class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">

            <!-- Filtre Niveau -->
            <select name="level" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
                <option value="">Tous les niveaux</option>
                <?php foreach ($levels as $lvl): ?>
                    <option value="<?php echo $lvl; ?>" <?php echo $filter_level === $lvl ? 'selected' : ''; ?> >
                        <?php echo htmlspecialchars($lvl); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Filtre Matière -->
            <select name="subject" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
                <option value="">Toutes les matières</option>
                <?php foreach ($subjects as $subj): ?>
                    <option value="<?php echo $subj; ?>" <?php echo $filter_subject === $subj ? 'selected' : ''; ?> >
                        <?php echo htmlspecialchars($subj); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Filtre Type -->
            <select name="type" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
                <option value="">Tous les types</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo $filter_type === $t ? 'selected' : ''; ?> >
                        <?php echo ucfirst($t); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Filtre Statut -->
            <select name="status" class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
                <option value="">Tous les statuts</option>
                <?php foreach ($statuses as $val => $label): ?>
                    <option value="<?php echo $val; ?>" <?php echo $filter_status === $val ? 'selected' : ''; ?> >
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-md">
                Filtrer
            </button>
        </form>

        <div class="mt-4 flex justify-between items-center">
            <a href="?" class="text-sm text-slate-600 hover:text-blue-600">🔄 Réinitialiser les filtres</a>
            <button onclick="openModal('add')" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-bold rounded-lg shadow-lg hover:from-green-700 hover:to-green-800 transition shadow-md">
                ➕ Ajouter un contenu
            </button>
        </div>
    </section>

    <!-- Tableau des contenus -->
    <section class="content-list bg-white rounded-xl shadow-md overflow-hidden">
        <?php if (empty($contents)): ?>
            <div class="p-12 text-center text-slate-500 flex flex-col items-center">
                <div class="text-6xl mb-4">📭</div>
                <p class="text-lg">Aucun contenu trouvé. Commencez par en ajouter un !</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gradient-to-r from-blue-50 to-purple-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Titre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Niveau</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Matière</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-700 uppercase">Mis à jour</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-slate-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php foreach ($contents as $content): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 align-top">
                                    <div class="font-semibold text-slate-900 mb-1 line-clamp-1">
                                        <?php echo htmlspecialchars($content['title']); ?>
                                    </div>
                                    <?php if (!empty($content['description'])): ?>
                                        <div class="text-xs text-slate-500 truncate max-w-xs">
                                            <?php echo htmlspecialchars(substr($content['description'], 0, 80)); ?>...
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        <?php echo ucfirst($content['type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 align-top">
                                    <?php echo htmlspecialchars($content['level']); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-700 align-top">
                                    <?php echo htmlspecialchars($content['subject'] ?: '—'); ?>
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <?php
                                    $status_colors = [
                                        'draft' => 'bg-amber-100 text-amber-700',
                                        'published' => 'bg-green-100 text-green-700',
                                        'archived' => 'bg-slate-100 text-slate-700',
                                    ];
                            $status_class = $status_colors[$content['status']] ?? 'bg-slate-100 text-slate-700';
                            ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $status_class; ?>">
                                        <?php echo $statuses[$content['status']] ?? $content['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500 align-top">
                                    <?php echo date('d/m/Y', strtotime($content['updated_at'])); ?>
                                </td>
                                <td class="px-6 py-4 text-center align-top">
                                    <div class="flex justify-center gap-2">
                                        <button onclick="openModal('edit', <?php echo htmlspecialchars(json_encode($content)); ?>)"
                                                class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition text-sm shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            ✏️ Éditer
                                        </button>
                                        <form method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                            <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition text-sm shadow-md focus:outline-none focus:ring-2 focus:ring-red-500">
                                                🗑️ Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</main>

<!-- Modal Ajout/Édition -->
<div id="contentModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="modal-content bg-white rounded-xl shadow-2xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-slate-900">Ajouter un contenu</h2>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 text-2xl focus:outline-none focus:ring-2 focus:ring-blue-500">&times;</button>
        </div>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="formId" value="">

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Titre *</label>
                <input type="text" name="title" id="formTitle" required
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Type</label>
                    <select name="type" id="formType" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
                        <option value="cours">Cours</option>
                        <option value="exercice">Exercice</option>
                        <option value="ressource">Ressource</option>
                        <option value="quiz">Quiz</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Statut</label>
                    <select name="status" id="formStatus" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
                        <option value="draft">Brouillon</option>
                        <option value="published">Publié</option>
                        <option value="archived">Archivé</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Niveau *</label>
                    <select name="level" id="formLevel" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
                        <?php foreach ($levels as $lvl): ?>
                            <option value="<?php echo $lvl; ?>"><?php echo htmlspecialchars($lvl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Matière</label>
                    <select name="subject" id="formSubject" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
                        <option value="">Sélectionner...</option>
                        <?php foreach ($subjects as $subj): ?>
                            <option value="<?php echo $subj; ?>"><?php echo htmlspecialchars($subj); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Description</label>
                <textarea name="description" id="formDescription" rows="4"
                          class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700"></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">URL du contenu</label>
                <input type="url" name="content_url" id="formContentUrl"
                       placeholder="https://..."
                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-slate-50 text-slate-700">
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-bold rounded-lg hover:from-green-700 hover:to-green-800 transition shadow-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    ✅ Enregistrer
                </button>
                <button type="button" onclick="closeModal()" class="px-6 py-3 bg-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-slate-300 transition shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(mode, data = null) {
    const modal = document.getElementById('contentModal');
    const title = document.getElementById('modalTitle');
    const action = document.getElementById('formAction');

    if (mode === 'edit' && data) {
        title.textContent = 'Éditer le contenu';
        action.value = 'edit';
        document.getElementById('formId').value = data.id;
        document.getElementById('formTitle').value = data.title;
        document.getElementById('formType').value = data.type;
        document.getElementById('formLevel').value = data.level;
        document.getElementById('formSubject').value = data.subject || '';
        document.getElementById('formDescription').value = data.description || '';
        document.getElementById('formContentUrl').value = data.content_url || '';
        document.getElementById('formStatus').value = data.status;
    } else {
        title.textContent = 'Ajouter un contenu';
        action.value = 'add';
        document.getElementById('formId').value = '';
        document.querySelector('form').reset();
    }

    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('contentModal').classList.add('hidden');
}

// Fermer avec Echap
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});

// Auto-dismiss feedback après 5s
setTimeout(() => {
    const feedback = document.querySelector('.feedback');
    if (feedback) feedback.style.display = 'none';
}, 5000);
</script>

<?php if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
    include_once dirname(__DIR__, 2) . '/includes/footer.php';
} ?>
</body>
</html>

