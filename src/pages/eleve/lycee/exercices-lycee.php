<?php
// Page fusionnée pour exercices de Seconde et Première
// Utilisation : src/pages/lycee/exercices-lycee.php
// Détection automatique du niveau via l'URL ou paramètre GET

$redirect_helpers = dirname(__DIR__, 3) . '/includes/redirect_helpers.php';
if (is_file($redirect_helpers)) {
    require_once $redirect_helpers;
}

// Détection du niveau
$niveau = null;
$uri = $_SERVER['REQUEST_URI'] ?? '';
if (isset($_GET['niveau'])) {
    $niveau = strtolower(trim($_GET['niveau']));
} elseif (strpos($uri, '2nde') !== false || strpos($uri, 'seconde') !== false) {
    $niveau = '2nde';
} elseif (strpos($uri, '1ere') !== false || strpos($uri, 'premiere') !== false) {
    $niveau = '1ere';
} elseif (strpos($uri, 'terminale') !== false) {
    $niveau = 'terminale';

}

// Normalisation
if ($niveau === 'seconde') {
    $niveau = '2nde';
}
if ($niveau === 'première') {
    $niveau = '1ere';
}
if ($niveau === 'terminale') {
    $niveau = 'terminale';
}

// Redirections legacy
$legacy_redirects = [
    'lycee/seconde/exercices-seconde' => 'lycee/2nde/exercices-2nde',
    'lycee/2nde/exercices-2nde' => 'lycee/2nde/exercices-lycee?niveau=2nde',
    'lycee/premiere/exercices-premiere' => 'lycee/1ere/exercices-lycee?niveau=1ere',
    'lycee/1ere/exercices-1ere' => 'lycee/1ere/exercices-lycee?niveau=1ere',
    'lycee/terminale/exercices-terminale' => 'lycee/terminale/exercices-lycee?niveau=terminale',
];
foreach ($legacy_redirects as $from => $to) {
    if (strpos($uri, $from) !== false) {
        $redirectUrl = site_url($to);
        if (function_exists('safe_redirect')) {
            safe_redirect($redirectUrl, 301);
        }
        exit;
    }
}

// Sécurité : niveau obligatoire
if (!$niveau) {
    $page_title = '404 Page non trouvée';
    $page_css = '404.css';
    include __DIR__ . '/../../../includes/topbar.php';
    echo '<main class="main-content"><h1>404 Page non trouvée</h1><p>Niveau scolaire non détecté.</p><a href="' . site_url('eleve/dashboard') . '" class="btn">Retour au tableau de bord</a></main>';
    include __DIR__ . '/../../../includes/footer.php';
    exit;
}

// Paramètres dynamiques
$titles = [
    '2nde' => [
        'title' => '🌌 Exercices Seconde - Maître en Formation',
        'subtitle' => 'Programme 2025 | Explore les nouveaux horizons du savoir !',
        'nav' => 'Seconde',
        'cours' => 'seconde',
        'level_db' => ['2nde', 'Seconde'],
    ],
    '1ere' => [
        'title' => '⭐ Exercices Première - Expert Académique',
        'subtitle' => 'Programme 2025 | Prépare-toi pour le Bac avec confiance !',
        'nav' => 'Première',
        'cours' => 'premiere',
        'level_db' => ['1ere', 'Première'],
    ],
    'terminale' => [
        'title' => '🔥 Exercices Terminale - Maître en Formation',
        'subtitle' => 'Programme 2025 | Atteins l\'excellence pour le Bac !',
        'nav' => 'Terminale',
        'cours' => 'terminale',
        'level_db' => ['terminale', 'Terminale'],
    ],
];
$params = $titles[$niveau];
$page_title = $params['title'];
// Use common exercices stylesheet for lycée pages
$page_css = 'lycee/exercices-lycee.css';

// ...existing code for includes, auth, access guard, navigation...
if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 3) . '/config/site_boot.php';
}
if (is_file(dirname(__DIR__, 3) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 3) . '/includes/admin_auth.php';
}

// ========== VERIFICATION ACCES ==========
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$has_access = !empty($is_logged_in) || $is_admin || $is_demo;

if (!$has_access) {
    ?>
    <main class="main-content max-w-6xl mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold text-slate-800 text-center"><?php echo htmlspecialchars($params['title']); ?></h1>
        <p class="subtitle text-center text-slate-600 mt-2"><?php echo htmlspecialchars($params['subtitle']); ?></p>

        <div class="coach-preview text-center bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl p-8 shadow-lg mt-6">
             <h2 class="text-2xl font-bold text-slate-800">🔒 Accès restreint</h2>
             <p class="text-slate-600">Le contenu pédagogique est réservé aux membres inscrits.</p>
             <p class="mt-5 flex flex-wrap gap-3 justify-center">
                 <a href="<?php echo site_url('register'); ?>" class="btn-theme-primary inline-flex items-center justify-center px-4 py-2 rounded-lg font-semibold">Créer un compte</a>
                 <a href="<?php echo site_url('login'); ?>" class="btn btn-secondary inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white text-slate-800 font-semibold border border-slate-200 hover:bg-slate-50 transition-colors">Se connecter</a>
             </p>
        </div>
    </main>
<?php
        return;
}

if (is_file(__DIR__ . '/../../../includes/level_access_guard.php')) {
    require_once __DIR__ . '/../../../includes/level_access_guard.php';
    $access_status = render_level_access_guard($params['nav'], 'exercices');
    if ($access_status === 'blocked') {
        echo '</main>';
        exit;
    }
}
// Suppression de l'inclusion du menu de navigation de niveau

$is_admin = function_exists('isAdmin') && isAdmin();
$has_coach_access = !empty($is_logged_in) || $is_admin;

$exercisesLoaded = false;
$mathExercises = [];
$frenchExercises = [];
$scienceExercises = [];

try {
    if (is_file(__DIR__ . '/../../../includes/exercice_loader.php')) {
        require_once __DIR__ . '/../../../includes/exercice_loader.php';
    }
    if (is_file(__DIR__ . '/../../../includes/exercice_card.php')) {
        require_once __DIR__ . '/../../../includes/exercice_card.php';
    }
    if (!function_exists('getExercisesByLevel')) {
        throw new Exception('Fonction getExercisesByLevel non trouvée');
    }
    if (isset($pdo) && $pdo) {
        $selectedSubject = isset($_GET['subject']) && strlen(trim($_GET['subject'])) ? trim($_GET['subject']) : null;
        $allExercises = [];
        foreach ($params['level_db'] as $dbLevel) {
            $allExercises = array_merge($allExercises, getExercisesByLevel($dbLevel, $selectedSubject));
        }
        if (!empty($allExercises)) {
            $exercisesLoaded = true;
            foreach ($allExercises as $exercise) {
                $subject = $exercise['Subject'] ?? '';
                if ($subject === 'Mathematiques') {
                    $mathExercises[] = $exercise;
                } elseif ($subject === 'Francais') {
                    $frenchExercises[] = $exercise;
                } elseif (in_array($subject, ['SVT', 'Physique-Chimie', 'Sciences'], true)) {
                    $scienceExercises[] = $exercise;
                }
            }
        }
    } else {
        error_log("MonCoachScolaire: Base de données non disponible pour charger les exercices");
    }
} catch (Exception $e) {
    error_log("Erreur chargement exercices: " . $e->getMessage());
    $exercisesLoaded = false;
}
?>
<main class="main-content min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
    <div class="header text-center rounded-[2rem] border border-emerald-200/70 bg-white/85 p-8 shadow-[0_25px_70px_-28px_rgba(16,185,129,0.28)] backdrop-blur">
        <h1 class="text-4xl font-bold"><?php echo $params['title']; ?></h1>
        <p class="subtitle text-lg text-white/90"><?php echo $params['subtitle']; ?></p>
        <div class="exercices-navigation flex flex-wrap gap-4 justify-center mt-6">
            <a href="<?php echo site_url('cours', ['niveau' => $params['cours']]); ?>" class="nav-btn nav-cours btn-theme-primary inline-flex items-center justify-center px-6 py-3 rounded-lg font-semibold">📚 Mes Cours</a>
            <a href="<?php echo site_url('lycee/lycee-accueil'); ?>" class="nav-btn nav-accueil inline-flex items-center justify-center px-6 py-3 rounded-lg bg-emerald-500 text-white font-semibold hover:bg-emerald-600 transition-colors">🏠 Accueil Lycée</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard inline-flex items-center justify-center px-6 py-3 rounded-lg bg-purple-500 text-white font-semibold hover:bg-purple-600 transition-colors">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <?php // Subject selector for logged-in users?>
    <?php if (!empty($is_logged_in) && function_exists('getSubjectsByLevels')): ?>
        <?php $availableSubjects = getSubjectsByLevels($params['level_db']); ?>
        <div class="subject-filter max-w-4xl mx-auto mt-3 flex justify-center">
            <form method="get" id="subject-filter-form">
                <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page'] ?? 'lycee/exercices-lycee'); ?>">
                <input type="hidden" name="niveau" value="<?php echo htmlspecialchars($_GET['niveau'] ?? ($niveau)); ?>">
            <label for="subject-select" class="mr-2 font-semibold self-center text-slate-800">Choisir une matière :</label>
            <select id="subject-select" name="subject" onchange="document.getElementById('subject-filter-form').submit()" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-800">
                    <option value="">Toutes les matières</option>
                    <?php foreach ($availableSubjects as $sub): ?>
                        <option value="<?php echo htmlspecialchars($sub); ?>" <?php echo (isset($_GET['subject']) && $_GET['subject'] === $sub) ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>
    <!-- ... (le reste du contenu interactif, coach, exercices, etc. à compléter selon besoins) ... -->
</main>

