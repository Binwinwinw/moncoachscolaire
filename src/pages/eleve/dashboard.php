
<?php
// AUCUN ESPACE NI LIGNE VIDE AVANT CE PHP !
// Démarrer la session et vérifier l'authentification AVANT toute inclusion ou configuration
$site_boot = dirname(__DIR__, 2) . '/config/site_boot.php';
if (!is_file($site_boot)) {
    $site_boot = __DIR__ . '/bootstrap/site_boot.php';
}
if (is_file($site_boot)) {
    require_once $site_boot;
}

$redirect_helpers = dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
if (is_file($redirect_helpers)) {
    require_once $redirect_helpers;
}

if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('dashboard.php: impossible de démarrer la session — headers déjà envoyés.');
        }
    }
}
if (empty($_SESSION['user_id']) || empty($_SESSION['logged_in'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'eleve/dashboard';
    $loginUrl = site_url('login');
    if (function_exists('safe_redirect')) {
        safe_redirect($loginUrl);
    }
    exit;
}

$page_title = 'Tableau de Bord - MonCoachScolaire';
$page_css = 'dashboard.css';

// Charger la configuration et les fichiers nécessaires
if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}
// Charger site_boot.php pour avoir accès aux fonctions utilitaires (site_url, normalize_level_for_url, etc.)
require_once dirname(__DIR__, 2) . '/database/connection.php';

// Charger le système de normalisation des niveaux pour gérer les problèmes d'encodage UTF-8
require_once dirname(__DIR__, 2) . '/includes/level_normalization.php';

// Charger le système de sécurité démo
require_once dirname(__DIR__, 2) . '/includes/demo_security.php';

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
}

// VÉRIFICATION ADMIN : Si l'utilisateur est admin et accède au dashboard normal,
// ne pas rediriger (il peut choisir d'aller au dashboard admin ou normal)
// Cette vérification permet aux admins d'accéder aux deux dashboards
$is_admin = function_exists('isAdmin') && isAdmin();

// SÉCURITÉ : Rediriger le compte démo vers la page de démo
if (isDemoUser()) {
    $demoUrl = site_url('demo');
    $demoRedirectUrl = $demoUrl . '?demo=1&redirected=1';
    if (function_exists('safe_redirect')) {
        safe_redirect($demoRedirectUrl);
    }
    exit;
}

// IMPORTANT : NE PAS INCLURE TOPBAR ICI - Elle sera incluse plus bas, après tous les appels header()
// Inclusion de topbar.php reportée APRÈS vérifications de redirection pour éviter "headers already sent"

error_log("DASHBOARD: Session valide - user_id=" . $_SESSION['user_id']);

// Récupérer les informations de l'utilisateur depuis la session
$user_name = $_SESSION['user_name'] ?? 'Élève';
// Pour les admins, utiliser un niveau par défaut pour l'affichage, mais ils auront accès à tous les niveaux
$user_level = $_SESSION['user_level'] ?? ($is_admin ? '6ème' : '6ème');
$user_id = $_SESSION['user_id'] ?? null;

// Normaliser le niveau de l'utilisateur pour affichage correct (6ème et non 6??me)
$user_level_display = get_level_display_name($user_level);
$user_level_normalized = normalize_school_level($user_level);

// Si ce fichier est accédé directement (pas via le router), charger les CSS et le header/footer
$direct_access = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));
if ($direct_access) {
    // === DÉCLENCHEUR HEAD/HTML : n'émettre le <head> que si accès direct ===
    // Charger les helpers et la configuration
    // Emettre le head avec les CSS
    ?><!doctype html>
        <html lang="fr">
        <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <title><?php echo htmlspecialchars($page_title); ?></title>
                <?php
            $root = rtrim($baseUrl ?? '', '/');
    if (!$root) {
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir && $scriptDir !== '.' && $scriptDir !== '/') {
            $root = $scriptDir;
        } else {
            $root = '';
        }
    }
    if (function_exists('asset_url')) {
        $cssStyle = asset_url('assets/css/style.css');
        $cssPage = asset_url('assets/css/pages/' . $page_css);
        $coachWebmScript = asset_url('assets/js/coach-webm.js');
    } else {
        $assetBase = ($root !== '') ? $root : '';
        $cssStyle = $assetBase . '/assets/css/style.css';
        $cssPage = $assetBase . '/assets/css/pages/' . rawurlencode($page_css);
        $coachWebmScript = $assetBase . '/assets/js/coach-webm.js';
    }
    ?>
                <link rel="stylesheet" href="<?php echo htmlspecialchars($cssStyle, ENT_QUOTES); ?>">
                <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPage, ENT_QUOTES); ?>">
                <?php
    // Exposer baseUrl pour JavaScript
    if (function_exists('detectBaseUrl')) {
        $jsBaseUrl = detectBaseUrl();
    } else {
        $jsBaseUrl = isset($baseUrl) ? $baseUrl : '';
    }
    echo "<script>window.baseUrl = " . json_encode($jsBaseUrl, JSON_UNESCAPED_SLASHES) . ";</script>\n";
    ?>
                <!-- Coach WebM Script -->
                <script src="<?= htmlspecialchars($coachWebmScript, ENT_QUOTES); ?>"></script>
                <style>
                    /* Conteneur du coach avec positionnement fixe */
                    .coach-overlay {
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        width: 250px;
                        height: auto;
                        z-index: 9999;
                        pointer-events: none;
                        opacity: 0;
                        transition: opacity 0.5s ease-out;
                    }
                    .coach-overlay.active {
                        opacity: 1;
                        animation: slideInUp 0.6s ease-out;
                    }
                    .coach-overlay video {
                        width: 100%;
                        height: auto;
                        display: block;
                        border-radius: 12px;
                        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                        background: transparent;
                    }
                    @keyframes slideInUp {
                        from {
                            opacity: 0;
                            transform: translateY(30px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    @media (max-width: 480px) {
                        .coach-overlay {
                            width: 180px;
                            bottom: 10px;
                            right: 10px;
                        }
                    }
                                        /* Désactiver toute ancienne mascotte Colibri si présente */
                                        .colibri-mascot-container,
                                        .colibri-mascot-global,
                                        [data-colibri],
                                        [data-colibri-global] { display: none !important; }
                </style>
        </head>
        <body>
            // === FIN DÉCLENCHEUR HEAD/HTML ===
        <?php
        // Inclure le topbar
        if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
            include_once dirname(__DIR__, 2) . '/includes/topbar.php';
        }
}
if (!$direct_access && is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
    // Inclure la topbar APRÈS toutes les vérifications de redirection (authentification, démo, etc.)
    require_once dirname(__DIR__, 2) . '/includes/topbar.php';
}

// Charger les fonctions de progression et gamification
require_once dirname(__DIR__, 2) . '/includes/gamification.php';
require_once dirname(__DIR__, 2) . '/includes/progress_display.php';
require_once dirname(__DIR__, 2) . '/includes/dashboard_extensions.php';
require_once dirname(__DIR__, 2) . '/includes/course_display.php';
require_once dirname(__DIR__, 2) . '/includes/resource_display.php';

// Initialiser les extensions du dashboard
if ($user_id) {
    // Mettre à jour le streak de connexion
    updateLoginStreak($user_id);
    // Mettre à jour l'historique de progression
    updateProgressHistory($user_id);
    // Mettre à jour les objectifs quotidiens
    updateDailyGoalProgress($user_id);
    // Vérifier et créer les notifications
    checkAndCreateNotifications($user_id);
}

// Récupérer la progression de l'utilisateur
$userProgress = null;
$dailyGoals = null;
$weeklyGoals = null;
$userStreak = null;
$weatherMood = null;
$notifications = null;

if ($user_id) {
    $userProgress = getUserProgress($user_id);
    $dailyGoals = getDailyGoals($user_id);
    $weeklyGoals = getWeeklyGoals($user_id);
    $userStreak = getUserStreak($user_id);
    $weatherMood = getWeatherMood($user_id);
    $notifications = getUnreadNotifications($user_id, 5);
}

// Configuration par niveau scolaire
$levelConfig = [
    '6eme' => [
        'title' => 'Apprenti Érudit',
        'theme' => 'forest',
        'color' => '#10b981', // Vert
        'icon' => '🌱',
        'message' => 'Bienvenue dans ta grande aventure ! Chaque exercice te rapproche de la maîtrise.',
    ],
    '5eme' => [
        'title' => 'Explorateur du Savoir',
        'theme' => 'ocean',
        'color' => '#3b82f6', // Bleu
        'icon' => '🌊',
        'message' => 'Tu progresses ! Continue d\'explorer les nouveaux horizons du savoir.',
    ],
    '4eme' => [
        'title' => 'Chercheur Confirmé',
        'theme' => 'mountain',
        'color' => '#8b5cf6', // Violet
        'icon' => '⛰️',
        'message' => 'Excellent travail ! Tu gravis les sommets de la connaissance.',
    ],
    '3eme' => [
        'title' => 'Champion du Savoir',
        'theme' => 'sunset',
        'color' => '#f59e0b', // Orange
        'icon' => '🔥',
        'message' => 'Tu es sur la voie de l\'excellence ! Prépare-toi pour le DNB avec confiance.',
    ],
    'Seconde' => [
        'title' => 'Maître en Formation',
        'theme' => 'galaxy',
        'color' => '#6366f1', // Indigo
        'icon' => '🌌',
        'message' => 'Bienvenue au lycée ! Ton parcours vers l\'excellence continue.',
    ],
    'Premiere' => [
        'title' => 'Expert Académique',
        'theme' => 'cosmic',
        'color' => '#a855f7', // Pourpre lycée
        'icon' => '⭐',
        'message' => 'Tu te prépares pour le Bac ! Chaque effort compte dans ta quête.',
    ],
    'Terminale' => [
        'title' => 'Roi du Savoir',
        'theme' => 'royal',
        'color' => '#dc2626', // Rouge
        'icon' => '👑',
        'message' => 'Dernière ligne droite ! Tu es prêt(e) à conquérir le Bac avec brio.',
    ],
    'BAC' => [
        'title' => 'Maître du Savoir',
        'theme' => 'royal',
        'color' => '#dc2626', // Rouge
        'icon' => '',
        'message' => 'Tu as atteint le sommet de ton parcours scolaire. Continue comme ça !',
    ],
];

$config = $levelConfig[$user_level_normalized] ?? $levelConfig['6eme'];
$userXP = $userProgress['xp'] ?? 0;
$userLevelNumber = getUserLevel($userXP);
$nextLevelXP = getNextLevelXP($userLevelNumber);
$xpProgress = $nextLevelXP > 0 ? ($userXP / $nextLevelXP) * 100 : 0;
$cristaux = $userProgress['cristaux'] ?? 0;
$badges = $userProgress['badges'] ?? [];
$exercisesCompleted = $userProgress['exercises_completed'] ?? 0;
$bySubject = $userProgress['by_subject'] ?? [];

// Récupérer les derniers exercices complétés
$recentExercises = [];
if ($user_id) {
    $recentExercises = getCompletedExercises($user_id, $user_level, null, 5);
}

// Messages du coach personnalisés
$coachMessages = [
    'daily' => "C'est un nouveau jour ! Prêt(e) à relever de nouveaux défis ?",
    'progress' => $exercisesCompleted > 0 ? "Tu as déjà complété {$exercisesCompleted} exercice(s) ! Continue comme ça !" : "Commence ta première série d'exercices aujourd'hui.",
    'level' => "Tu es au niveau {$userLevelNumber} ! Plus que " . ($nextLevelXP - $userXP) . " XP pour le niveau suivant.",
    'badge' => count($badges) > 0 ? "Tu as débloqué " . count($badges) . " badge(s) ! 🏆" : "Débloque ton premier badge en complétant un exercice.",
];
?>

<main class="main-content max-w-7xl mx-auto px-4 py-8">
    <!-- Header personnalisé selon le niveau -->
    <?php
        // Palette sobriété: pas de couleurs flashy, style neutre.
        $user_level_norm = function_exists('normalize_school_level') ? normalize_school_level($user_level) : $user_level;
        $theme = function_exists('get_theme_variant_by_level') ? get_theme_variant_by_level($user_level_norm) : [];
        $dominant_bg = 'bg-slate-100';
        $dominant_border = 'border-slate-300';
        $dominant_accent = 'text-slate-900';
        $card_title = 'text-slate-800';
        $btn_primary = 'bg-slate-700 text-white hover:bg-slate-800';
        $btn_secondary = 'bg-slate-500 text-white hover:bg-slate-600';
        $status_admin = 'text-slate-500';
    ?>
    <div class="dashboard-header rounded-2xl p-8 mb-8 shadow-sm flex flex-col gap-6 <?php echo $dominant_bg; ?> <?php echo $dominant_border; ?>">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">
            <div class="flex-1 min-w-[220px]">
                <h1 class="flex items-center gap-3 text-3xl md:text-4xl font-bold <?php echo $dominant_accent; ?>">
                    <span class="text-4xl md:text-5xl"><?php echo $config['icon']; ?></span>
                    Salut <?php echo htmlspecialchars($user_name); ?> !
                </h1>
                <p class="text-base mt-1 <?php echo $card_title; ?>">
                    <strong><?php echo $config['title']; ?></strong>
                    <?php if (!$is_admin): ?>
                        <span class="mx-2">•</span> Niveau scolaire : <strong><?php echo htmlspecialchars($user_level_display); ?></strong>
                    <?php else: ?>
                        <span class="mx-2 <?php echo $status_admin; ?>">• Administrateur</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex gap-4 flex-wrap">
                <div class="flex items-center gap-2 px-6 py-4 bg-white/80 backdrop-blur rounded-xl shadow min-w-[110px] border <?php echo $dominant_border; ?>">
                    <span class="text-2xl">⭐</span>
                    <div>
                        <div class="text-xl font-bold <?php echo $dominant_accent; ?> leading-none"><?php echo $userLevelNumber; ?></div>
                        <div class="text-xs <?php echo $dominant_accent; ?> mt-1">Niveau</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-6 py-4 bg-white/80 backdrop-blur rounded-xl shadow min-w-[110px] border <?php echo $dominant_border; ?>">
                    <span class="text-2xl">⚗️</span>
                    <div>
                        <div class="text-xl font-bold <?php echo $dominant_accent; ?> leading-none"><?php echo $cristaux; ?></div>
                        <div class="text-xs <?php echo $dominant_accent; ?> mt-1">Éléments</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 px-6 py-4 bg-white/80 backdrop-blur rounded-xl shadow min-w-[110px] border <?php echo $dominant_border; ?>">
                    <span class="text-2xl">🏆</span>
                    <div>
                        <div class="text-xl font-bold <?php echo $dominant_accent; ?> leading-none"><?php echo count($badges); ?></div>
                        <div class="text-xs <?php echo $dominant_accent; ?> mt-1">Badges</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PREMIÈRE POSITION : Cards principales de progression -->
    <div class="dashboard-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        <!-- Card: Diagnostic Initial -->
        <div class="dashboard-card card-diagnostic bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gradient-to-br from-blue-50 to-blue-100">
                <h3 class="text-lg font-semibold flex items-center gap-2">🩺 Diagnostic Initial</h3>
            </div>
            <div class="p-6 flex flex-col items-center justify-center text-center">
                <p class="text-slate-700 mb-4">Évalue tes connaissances sur chaque notion clé pour personnaliser ton parcours&nbsp;!</p>
                <a href="<?php echo site_url('diagnostic'); ?>" class="inline-block px-6 py-3 rounded-lg bg-slate-700 text-white font-bold shadow hover:bg-slate-800 transition text-base">Lancer le diagnostic</a>
            </div>
        </div>
        <!-- Card: Progression Globale -->
        <div class="dashboard-card card-progression bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gradient-to-br from-slate-50 to-slate-100">
                <h3 class="text-lg font-semibold flex items-center gap-2">📊 Ma Progression</h3>
                <span class="px-3 py-1 rounded-full text-xs font-semibold">Niveau <?php echo $userLevelNumber; ?></span>
            </div>
            <div class="p-6">
                <div class="flex items-end gap-2 mb-4">
                    <div class="text-3xl font-bold text-slate-800"><?php echo $userXP; ?> XP</div>
                    <div class="text-base text-slate-500">/ <?php echo $nextLevelXP; ?> XP</div>
                </div>
                <div class="w-full h-4 bg-slate-200 rounded-lg overflow-hidden mb-6">
                    <div class="h-full rounded-lg transition-all duration-500"></div>
                </div>
                <div class="flex justify-around gap-2">
                    <div class="text-center flex-1">
                        <span class="block text-xl">📝</span>
                        <span class="block text-lg font-bold text-slate-800"><?php echo $exercisesCompleted; ?></span>
                        <span class="block text-xs text-slate-500 mt-1">Exercices</span>
                    </div>
                    <div class="text-center flex-1">
                        <span class="block text-xl">⚗️</span>
                        <span class="block text-lg font-bold text-slate-800"><?php echo $cristaux; ?></span>
                        <span class="block text-xs text-slate-500 mt-1">Éléments</span>
                    </div>
                    <div class="text-center flex-1">
                        <span class="block text-xl">🏆</span>
                        <span class="block text-lg font-bold text-slate-800"><?php echo count($badges); ?></span>
                        <span class="block text-xs text-slate-500 mt-1">Badges</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Famille (Parent(s)) -->
        <div class="dashboard-card card-family bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gradient-to-br from-slate-50 to-slate-100">
                <h3 class="text-lg font-semibold flex items-center gap-2">👪 Ma famille</h3>
                <span id="family-status-tag" class="px-3 py-1 rounded-full text-xs font-semibold text-white bg-slate-400">Chargement...</span>
            </div>
            <div class="p-6" id="family-card-content">
                <p class="text-slate-500">Chargement des parents...</p>
                <div class="mt-4 flex gap-2">
                    <input id="family-code-input" placeholder="Code parent 6 caractères" maxlength="6" class="px-4 py-2 border rounded-lg w-full" />
                    <button id="family-code-attach" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-800 transition">Rattacher</button>
                </div>
                <div id="family-code-message" class="mt-3 text-sm"></div>
            </div>
        </div>

        <!-- Card: Progression par Matière -->
        <div class="dashboard-card card-subjects bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gradient-to-br from-slate-50 to-slate-100">
                <h3 class="text-lg font-semibold flex items-center gap-2">📚 Progression par Matière</h3>
            </div>
            <div class="p-6">
                <?php if (!empty($bySubject)): ?>
                    <div class="flex flex-col gap-4">
                        <?php foreach ($bySubject as $subjectStat): ?>
                        <div class="flex justify-between items-center bg-slate-50 rounded-lg px-4 py-3 hover:bg-slate-100 transition">
                            <div class="flex items-center gap-3 flex-1">
                                <span class="text-xl">
                                    <?php
                                $icons = [
                                    'Mathématiques' => '🧮',
                                    'Français' => '📚',
                                    'Sciences' => '🔬',
                                    'Histoire-Géo' => '🏛️',
                                    'Anglais' => '🇬🇧',
                                ];
                            echo $icons[$subjectStat['Subject']] ?? '📖';
                            ?>
                                </span>
                                <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($subjectStat['Subject']); ?></span>
                            </div>
                            <div class="flex items-center gap-3 flex-1 max-w-[200px]">
                                <span class="text-xs text-slate-500 whitespace-nowrap"><?php echo $subjectStat['count']; ?> exercices</span>
                                <div class="flex-1 h-2 bg-slate-200 rounded overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-500 to-violet-500 transition-all duration-500"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-slate-500">
                        <span class="block text-3xl mb-2">📝</span>
                        <p class="mb-4">Aucun exercice complété pour le moment.</p>
                        <?php if ($is_admin): ?>
                            <a href="<?php echo site_url('eleve/college/college-accueil'); ?>" class="inline-block px-6 py-2 rounded bg-slate-700 text-white font-semibold shadow hover:bg-slate-800 transition">📚 Accéder aux Exercices</a>
                        <?php else: ?>
                            <a href="<?php
                                $level_normalized = normalize_level_for_url($user_level);
                            $level_lower = strtolower(trim($user_level ?? ''));
                            $is_college = (preg_match('/[6543]/', $level_lower) && !preg_match('/seconde|premiere|terminale/', $level_lower));
                            if ($is_college) {
                                echo site_url('eleve/college/' . $level_normalized . '/exercices-' . $level_normalized);
                            } else {
                                $level_raw = strtolower(trim($user_level ?? ''));
                                $level_norm = normalize_school_level($user_level);
                                if (in_array($level_norm, ['Seconde', '2nd', '2nde'])) {
                                    echo site_url('eleve/lycee/2nde/exercices-2nde');
                                } elseif (in_array($level_norm, ['Premiere', '1ere', '1ère'])) {
                                    echo site_url('eleve/lycee/1ere/exercices-1ere');
                                } elseif ($level_norm === 'Terminale') {
                                    echo site_url('eleve/lycee/terminale/exercices-terminale');
                                } else {
                                    echo '#erreur-niveau-lycee';
                                }
                            }
                            ?>" class="inline-block px-6 py-2 rounded bg-slate-700 text-white font-semibold shadow hover:bg-slate-800 transition">Commencer</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card: Derniers Exercices -->
        <div class="dashboard-card card-recent bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gradient-to-br from-slate-50 to-slate-100">
                <h3 class="text-lg font-semibold flex items-center gap-2">⚡ Activité Récente</h3>
            </div>
            <div class="p-6">
                <?php if (!empty($recentExercises)): ?>
                    <div class="flex flex-col gap-4">
                        <?php foreach ($recentExercises as $exercise): ?>
                        <div class="flex items-center gap-4 bg-slate-50 rounded-lg px-4 py-3 hover:bg-slate-100 transition">
                            <div class="text-xl flex-shrink-0">✅</div>
                            <div class="flex-1">
                                <div class="font-semibold text-slate-800 mb-1"><?php echo htmlspecialchars($exercise['Title'] ?? 'Exercice'); ?></div>
                                <div class="text-xs text-slate-500 flex gap-2">
                                    <span><?php echo htmlspecialchars($exercise['Subject'] ?? ''); ?></span>
                                    <span>•</span>
                                    <span><?php echo date('d/m/Y', strtotime($exercise['SubmittedAt'] ?? 'now')); ?></span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-green-500 font-bold text-lg">
                                <?php echo $exercise['Score'] ?? 0; ?>%
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-slate-500">
                        <span class="block text-3xl mb-2">📝</span>
                        <p>Aucun exercice complété récemment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card: Cours Recommandés -->
        <div class="dashboard-card card-recommended-courses bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gradient-to-br from-slate-50 to-slate-100">
                <h3 class="text-lg font-semibold flex items-center gap-2">📚 Cours à Explorer</h3>
            </div>
            <div class="p-6">
                <?php
                // Récupérer les cours du MÊME NIVEAU que l'utilisateur avec exercices
                $recommendedCourses = [];
                if ($pdo instanceof PDO) {
                    try {
                        $stmt = $pdo->prepare("
                            SELECT DISTINCT c.* FROM Courses c
                            INNER JOIN exercisecourselinks ecl ON c.Id = ecl.CourseId
                            WHERE c.is_active = 1
                            AND c.Level = ?
                            ORDER BY c.Title
                            LIMIT 4
                        ");
                        $stmt->execute([$user_level]);
                        $recommendedCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        error_log("Erreur récupération cours: " . $e->getMessage());
                        $recommendedCourses = [];
                    }
                } else {
                    error_log('dashboard.php: PDO non initialisé pour la récupération des cours recommandés.');
                }

if (!empty($recommendedCourses)): ?>
                            <?php
            // Optimisation : compter les exercices pour tous les cours d'un coup
            $courseIds = array_column($recommendedCourses, 'Id');
    $exCounts = [];
    if (!empty($courseIds) && $pdo) {
        $in = implode(',', array_fill(0, count($courseIds), '?'));
        $stmtExAll = $pdo->prepare("SELECT CourseId, COUNT(*) as nb FROM exercisecourselinks WHERE CourseId IN ($in) GROUP BY CourseId");
        $stmtExAll->execute($courseIds);
        foreach ($stmtExAll->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $exCounts[$row['CourseId']] = (int) $row['nb'];
        }
    } elseif (!$pdo) {
        error_log("dashboard.php: PDO non initialisé pour comptage exercices par cours.");
    }
?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($recommendedCourses as $course): ?>
                                <?php $exCount = $exCounts[$course['Id']] ?? 0; ?>
                                <div class="flex flex-col bg-slate-50 rounded-lg p-4 shadow hover:bg-slate-100 transition">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-semibold text-slate-800 text-lg"><?php echo htmlspecialchars($course['Title']); ?></h4>
                                        <span class="px-2 py-1 rounded bg-slate-200 text-slate-800 text-xs font-semibold"><?php echo htmlspecialchars($course['Level'] ?? 'Tous'); ?></span>
                                    </div>
                                    <div class="mb-2 text-sm text-slate-500">📖 <?php echo htmlspecialchars($course['Subject'] ?? 'Général'); ?></div>
                                    <div class="mb-2 text-xs text-slate-500">⚡ <?php echo $exCount; ?> exercice<?php echo $exCount > 1 ? 's' : ''; ?></div>
                                    <a href="<?php echo site_url('view_course', ['id' => $course['Id']]); ?>" class="inline-block px-4 py-2 rounded bg-slate-700 text-white font-semibold shadow hover:bg-slate-800 transition text-xs">Démarrer</a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                <?php else: ?>
                    <div class="text-center py-8 text-slate-500">
                        <span class="block text-3xl mb-2">📚</span>
                        <p>Aucun cours disponible pour le moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card: Ressources Recommandées -->
        <div class="dashboard-card card-recommended-resources bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gradient-to-br from-slate-50 to-slate-100">
                <h3 class="text-lg font-semibold flex items-center gap-2">🔗 Ressources Utiles</h3>
            </div>
            <div class="p-6">
                <?php
                // Récupérer les ressources populaires
                $popularResources = getPopularResources(3);

if (!empty($popularResources)): ?>
                    <div class="flex flex-col gap-4">
                        <?php foreach ($popularResources as $resource): ?>
                        <div class="flex items-center gap-4 bg-slate-50 rounded-lg px-4 py-3 shadow hover:bg-slate-100 transition">
                            <div class="text-xl flex-shrink-0">
                                <?php
                $icons = [
                    'video' => '▶️',
                    'article' => '📄',
                    'website' => '🌐',
                    'podcast' => '🎙️',
                    'document' => '📋',
                ];
                            echo $icons[$resource['ResourceType'] ?? 'website'] ?? '📌';
                            ?>
                            </div>
                            <div class="flex-1">
                                <h5 class="font-semibold text-slate-800 text-base mb-1"><?php echo htmlspecialchars($resource['Title']); ?></h5>
                                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($resource['Source'] ?? 'Source'); ?></p>
                            </div>
                            <a href="<?php echo htmlspecialchars($resource['URL']); ?>" target="_blank" rel="noopener" class="inline-block px-3 py-1 rounded bg-slate-700 text-white font-semibold shadow hover:bg-slate-800 transition text-xs">Accéder</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-slate-500">
                        <span class="block text-3xl mb-2">🔗</span>
                        <p>Aucune ressource disponible.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- TROISIÈME POSITION : Météo / Coach / Recommandations -->
    <div class="dashboard-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8 items-stretch">
        <!-- Widget Météo -->
        <?php if ($weatherMood): ?>
        <div class="dashboard-card card-weather bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl h-full">
            <div class="card-header">
                <h3>🌤️ Météo de Progression</h3>
            </div>
            <div class="card-body">
                <div class="weather-display">
                    <div class="weather-icon-large">
                        <?php echo $weatherMood['icon']; ?>
                    </div>
                    <div class="weather-info">
                        <div class="weather-mood"><?php echo $weatherMood['mood']; ?></div>
                        <div class="weather-message"><?php echo $weatherMood['message']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="dashboard-card card-weather bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl h-full">
            <div class="card-header">
                <h3>🌤️ Météo de Progression</h3>
            </div>
            <div class="card-body text-center py-8 text-slate-500">
                <span class="block text-3xl mb-2">🌤️</span>
                <p>La météo de progression sera disponible après quelques activités.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Message du Coach -->
        <div class="dashboard-card coach-message-card bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl h-full">
            <div class="card-header">
                <h3>👨‍🏫 Message de ton Coach</h3>
            </div>
            <div class="card-body">
                <div class="coach-content">
                    <p class="coach-text"><?php echo $config['message']; ?></p>
                    <p class="coach-text-secondary"><?php echo $coachMessages['daily']; ?></p>
                    <?php if ($coachMessages['progress']): ?>
                    <div class="coach-tip">
                        <strong>💡 Conseil :</strong> <?php echo $coachMessages['progress']; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card: Recommandations du Coach -->
        <div class="dashboard-card card-recommendations bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl h-full">
            <div class="card-header">
                <h3>💡 Recommandations</h3>
            </div>
            <div class="card-body">
                <div class="recommendations-list">
                    <?php if ($exercisesCompleted === 0): ?>
                    <div class="recommendation-item">
                        <span class="recommendation-icon">🎯</span>
                        <div class="recommendation-content">
                            <strong>Premier pas</strong>
                            <p>Commence par faire ton premier exercice de <?php echo htmlspecialchars($user_level_display); ?> !</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($userLevelNumber < 3): ?>
                    <div class="recommendation-item">
                        <span class="recommendation-icon">⭐</span>
                        <div class="recommendation-content">
                            <strong>Objectif : Niveau 3</strong>
                            <p>Continue tes efforts pour atteindre le niveau 3 !</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($bySubject)): ?>
                    <div class="recommendation-item">
                        <span class="recommendation-icon">📚</span>
                        <div class="recommendation-content">
                            <strong>Explore les matières</strong>
                            <p>Découvre les exercices de différentes matières !</p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="recommendation-item">
                        <span class="recommendation-icon">💪</span>
                        <div class="recommendation-content">
                            <strong>Régularité</strong>
                            <p>Fais au moins un exercice chaque jour pour progresser régulièrement.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QUATRIÈME POSITION : Streak / Badges / Actions -->
    <div class="dashboard-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8 items-stretch">
        <!-- Widget Streak -->
        <?php if ($userStreak): ?>
        <div class="dashboard-card card-streak bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl h-full">
            <div class="card-header">
                <h3>🔥 Série de Connexion</h3>
            </div>
            <div class="card-body">
                <div class="streak-display">
                    <div class="streak-value-large"><?php echo $userStreak['current']; ?></div>
                    <div class="streak-label">jours consécutifs</div>
                    <?php if ($userStreak['longest'] > $userStreak['current']): ?>
                    <div class="streak-record">Record : <?php echo $userStreak['longest']; ?> jours</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="dashboard-card card-streak bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl h-full">
            <div class="card-header">
                <h3>🔥 Série de Connexion</h3>
            </div>
            <div class="card-body text-center py-8 text-slate-500">
                <span class="block text-3xl mb-2">🔥</span>
                <p>Ta série de connexion démarre avec ta prochaine visite.</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card: Badges -->
        <div class="dashboard-card card-badges bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl h-full">
            <div class="card-header">
                <h3>🏆 Mes Badges</h3>
                <span class="card-badge"><?php echo count($badges); ?></span>
            </div>
            <div class="card-body">
                <?php if (!empty($badges)): ?>
                    <div class="badges-grid-mini">
                        <?php
                        $displayedBadges = array_slice($badges, 0, 6);
                    foreach ($displayedBadges as $badge):
                        ?>
                        <div class="badge-mini">
                            <div class="badge-mini-icon">🏆</div>
                            <div class="badge-mini-name"><?php echo htmlspecialchars($badge['Name']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($badges) > 6): ?>
                    <a href="<?php echo site_url('system/progression'); ?>" class="view-all-link">Voir tous les badges →</a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">🏆</span>
                        <p>Débloque ton premier badge en complétant des exercices !</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card: Actions Rapides -->
        <div class="dashboard-card card-actions bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl h-full">
            <div class="card-header">
                <h3>🚀 Actions Rapides</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <?php if ($is_admin): ?>
                        <a href="<?php echo site_url('eleve/college/college-accueil'); ?>" class="action-btn action-primary">
                            <span class="action-icon">📝</span>
                            <span class="action-text">Accéder aux Exercices</span>
                        </a>
                    <?php else: ?>
                        <a href="<?php
                            $level_norm = normalize_school_level($user_level);
                        if (in_array($level_norm, ['6eme', '5eme', '4eme', '3eme'])) {
                            echo site_url('eleve/college/' . $level_norm . '/exercices-' . $level_norm);
                        } elseif (in_array($level_norm, ['Seconde', '2nd', '2nde'])) {
                            echo site_url('eleve/lycee/2nde/exercices-2nde');
                        } elseif (in_array($level_norm, ['Premiere', '1ere', '1ère'])) {
                            echo site_url('eleve/lycee/1ere/exercices-1ere');
                        } elseif (in_array($level_norm, ['Terminale', 'Tale'])) {
                            echo site_url('eleve/lycee/terminale/exercices-terminale');
                        } else {
                            echo '#erreur-niveau-lycee';
                        }
                        ?>" class="action-btn action-primary">
                            <span class="action-icon">📝</span>
                            <span class="action-text">Faire des exercices</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo site_url('system/cours'); ?>" class="action-btn action-secondary">
                        <span class="action-icon">📚</span>
                        <span class="action-text">Voir mes cours</span>
                    </a>
                    <a href="<?php echo site_url('system/quiz'); ?>" class="action-btn action-secondary">
                        <span class="action-icon">❓</span>
                        <span class="action-text">Quiz du jour</span>
                    </a>
                    <a href="<?php echo site_url('system/progression'); ?>" class="action-btn action-secondary">
                        <span class="action-icon">🧙‍♂️</span>
                        <span class="action-text">Le Labo des Génies</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- CINQUIÈME POSITION : ligne graphique inchangée -->
    <div class="dashboard-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        <div class="dashboard-card card-chart bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="card-header">
                <h3>📈 Évolution de ta Progression</h3>
                <div class="chart-controls">
                    <button class="chart-period-btn active" data-days="7">7 jours</button>
                    <button class="chart-period-btn" data-days="30">30 jours</button>
                    <button class="chart-period-btn" data-days="90">90 jours</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="progressChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <?php
    // INTÉGRATION SPRINT 1 & 2 : Widgets XP, Badges, Diagnostics
    if (is_file(__DIR__ . '/../includes/dashboard_sprint_widgets.php')) {
        require_once __DIR__ . '/../includes/dashboard_sprint_widgets.php';
    }
?>

    <!-- Bloc complémentaire : notifications et objectifs -->
    <div class="dashboard-grid grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        <!-- Widget Notifications -->
        <?php if ($notifications && count($notifications) > 0): ?>
        <div class="dashboard-card card-notifications bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="card-header">
                <h3>🔔 Notifications</h3>
                <span class="notification-badge"><?php echo count($notifications); ?></span>
            </div>
            <div class="card-body">
                <div class="notifications-list">
                    <?php foreach (array_slice($notifications, 0, 3) as $notif): ?>
                    <div class="notification-item" data-id="<?php echo $notif['Id']; ?>">
                        <span class="notification-icon"><?php echo $notif['Icon'] ?? '🔔'; ?></span>
                        <div class="notification-content">
                            <div class="notification-title"><?php echo htmlspecialchars($notif['Title']); ?></div>
                            <div class="notification-time"><?php echo date('d/m H:i', strtotime($notif['CreatedAt'])); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($notifications) > 3): ?>
                <a href="#" class="view-all-link" onclick="showAllNotifications(); return false;">Voir toutes (<?php echo count($notifications); ?>) →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card: Objectifs Quotidiens -->
        <?php if ($dailyGoals): ?>
        <div class="dashboard-card card-goals bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="card-header">
                <h3>🎯 Objectifs du Jour</h3>
            </div>
            <div class="card-body">
                <?php foreach ($dailyGoals as $goal): ?>
                <div class="goal-item">
                    <div class="goal-header">
                        <span class="goal-icon">📝</span>
                        <div class="goal-info">
                            <div class="goal-label">Exercices</div>
                            <div class="goal-progress-text">
                                <?php echo $goal['Completed']; ?> / <?php echo $goal['Target']; ?>
                            </div>
                        </div>
                    </div>
                    <div class="goal-bar">
                        <div class="goal-bar-fill"></div>
                    </div>
                    <div class="goal-percentage"><?php echo min($goal['Progress'], 100); ?>%</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card: Objectifs Hebdomadaires -->
        <?php if ($weeklyGoals): ?>
        <div class="dashboard-card card-weekly-goals bg-white rounded-2xl shadow-lg overflow-hidden transition hover:-translate-y-1 hover:shadow-xl">
            <div class="card-header">
                <h3>📅 Objectifs de la Semaine</h3>
            </div>
            <div class="card-body">
                <?php foreach ($weeklyGoals as $goal): ?>
                <div class="goal-item">
                    <div class="goal-header">
                        <span class="goal-icon">📝</span>
                        <div class="goal-info">
                            <div class="goal-label">Exercices cette semaine</div>
                            <div class="goal-progress-text">
                                <?php echo $goal['Completed']; ?> / <?php echo $goal['Target']; ?>
                            </div>
                        </div>
                    </div>
                    <div class="goal-bar">
                        <div class="goal-bar-fill"></div>
                    </div>
                    <div class="goal-percentage"><?php echo min($goal['Progress'], 100); ?>%</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
(function () {
    const apiBase = <?php echo json_encode(function_exists('site_url') ? site_url('api/parent_family') : '/api/parent_family', JSON_UNESCAPED_SLASHES); ?>;
    const familyTag = document.getElementById('family-status-tag');
    const familyContent = document.getElementById('family-card-content');
    const familyMessage = document.getElementById('family-code-message');
    const codeInput = document.getElementById('family-code-input');

    function setStatus(text, color) {
        if (familyTag) {
            familyTag.textContent = text;
            familyTag.className = 'px-3 py-1 rounded-full text-xs font-semibold text-white ' + (color || 'bg-slate-400');
        }
    }

    async function apiRequest(action, method = 'GET', body = null) {
        let url = apiBase.includes('?') ? (apiBase + '&action=' + action) : (apiBase + '?action=' + action);
        const opts = { method, headers: {} };
        if (method === 'POST') {
            opts.headers['Content-Type'] = 'application/json';
            opts.headers['X-CSRF-Token'] = window.csrfToken || '';
            opts.body = JSON.stringify(body);
            url = apiBase;
        }

        const resp = await fetch(url, opts);
        const data = await resp.json();
        if (!resp.ok || !data.success) {
            throw new Error(data.error || 'Erreur d\'API');
        }
        return data.data;
    }

    async function refreshFamily() {
        try {
            const data = await apiRequest('my_family', 'GET');
            if (!data.parents || data.parents.length === 0) {
                setStatus('Aucun parent rattaché', 'bg-orange-500');
                document.getElementById('family-card-content').insertAdjacentHTML('beforeend', '<p class="text-slate-500">Aucun parent n\'est encore rattaché à votre compte.</p>');
            } else {
                setStatus('Parent(s) rattaché(s)', 'bg-emerald-500');
                const list = data.parents.map(p => {
                    return '<div class="mb-2 p-3 border rounded-lg bg-slate-50">' +
                        '<strong>' + (p.Prenom || p.Nom ? (p.Prenom + ' ' + p.Nom).trim() : p.Username) + '</strong> (' + (p.Email || 'sans email') + ')<br>' +
                        '<span class="text-xs text-slate-500">Accepté le: ' + (p.accepted_at ? new Date(p.accepted_at).toLocaleString('fr-FR') : '—') + '</span>' +
                        '</div>';
                }).join('');
                const existing = document.querySelector('.family-parents-list');
                if (existing) existing.innerHTML = list; else {
                    const container = document.createElement('div');
                    container.className = 'family-parents-list mt-3';
                    container.innerHTML = list;
                    familyContent.appendChild(container);
                }
            }
            familyMessage.textContent = '';
        } catch (err) {
            setStatus('Erreur famille', 'bg-red-500');
            familyMessage.textContent = err.message;
            familyMessage.className = 'text-sm text-red-600';
        }
    }

    async function attachCode() {
        const code = (codeInput.value || '').trim().toUpperCase();
        if (!code) {
            familyMessage.textContent = 'Veuillez saisir un code parent.';
            familyMessage.className = 'text-sm text-red-600';
            return;
        }

        try {
            const data = await apiRequest('attach_code', 'POST', { action: 'attach_code', code });
            familyMessage.textContent = data.message || 'Parent rattaché.';
            familyMessage.className = 'text-sm text-emerald-600';
            codeInput.value = '';
            await refreshFamily();
        } catch (err) {
            familyMessage.textContent = err.message;
            familyMessage.className = 'text-sm text-red-600';
        }
    }

    document.getElementById('family-code-attach').addEventListener('click', attachCode);
    refreshFamily();
})();
</script>

<!-- Chart.js pour les graphiques -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Graphique de progression
let progressChart = null;
let chartCtx = null;

// Fonction pour créer des données vides par défaut
function createEmptyChartData(days = 30) {
    const labels = [];
    for (let i = days - 1; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        labels.push(date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }));
    }
    return {
        labels: labels,
        datasets: [
            {
                label: 'XP',
                data: new Array(days).fill(0),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            },
            {
                label: 'Exercices complétés',
                data: new Array(days).fill(0),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4
            },
            {
                label: 'Cristaux',
                data: new Array(days).fill(0),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4
            }
        ]
    };
}

function loadProgressChart(days = 30) {
    // Vérifier que le canvas existe
    if (!chartCtx) {
        chartCtx = document.getElementById('progressChart');
    }

    if (!chartCtx) {
        console.error('Canvas progressChart non trouvé dans le DOM');
        return;
    }

    // Vérifier que Chart.js est chargé
    if (typeof Chart === 'undefined') {
        console.error('Chart.js n\'est pas chargé');
        return;
    }

    // Déterminer le chemin de l'API
    // window.baseUrl = '/moncoachscolaire/public' (LOCAL) ou '' (PROD)
    // L'API doit être appelée via le routeur : /index.php?page=api/get_progress_chart
    let apiBaseUrl = '';
    if (typeof window.baseUrl !== 'undefined' && window.baseUrl) {
        // Toujours utiliser window.baseUrl comme préfixe, sans le /public final
        apiBaseUrl = window.baseUrl.replace(/\/public$/, '');
    } else {
        // Fallback absolu : récupérer dynamiquement le chemin courant
        const pathParts = window.location.pathname.split('/');
        // Cherche le segment 'public' et remonte d’un cran
        const publicIdx = pathParts.indexOf('public');
        if (publicIdx > 0) {
            apiBaseUrl = pathParts.slice(0, publicIdx).join('/') + '/public';
        } else {
            apiBaseUrl = '';
        }
    }
    // Toujours préfixer l’URL API par apiBaseUrl
    const apiUrl = `${apiBaseUrl}/index.php?page=api/exercices/get_progress_chart&days=${days}`;

    fetch(apiUrl)
        .then(response => {
            // Vérifier le statut HTTP
            if (!response.ok) {
                console.warn(`Erreur HTTP ${response.status}: ${response.statusText}`);
                // Retourner des données vides au lieu de throw
                return createEmptyChartData(days);
            }
            // Vérifier que la réponse est bien du JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // Si ce n'est pas du JSON, lire le texte pour voir ce qui a été retourné
                return response.text().then(text => {
                    console.error('Réponse non-JSON reçue:', text.substring(0, 200));
                    // Retourner des données vides au lieu de throw
                    return createEmptyChartData(days);
                });
            }
            return response.json();
        })
        .then(data => {
            // Vérifier si l'API a retourné une erreur
            if (data.error) {
                console.warn('API a retourné une erreur:', data.error);
                // Si on n'a pas de données valides, utiliser des données vides
                if (!data.labels || !data.datasets) {
                    console.log('Utilisation de données vides par défaut');
                    data = createEmptyChartData(days);
                }
            }

            // Vérifier que les données sont valides, sinon utiliser des données vides
            if (!data.labels || !Array.isArray(data.labels) || !data.datasets || !Array.isArray(data.datasets)) {
                console.warn('Format de données invalide, utilisation de données vides par défaut');
                data = createEmptyChartData(days);
            }

            // S'assurer que les datasets ont bien la structure attendue
            if (data.datasets.length === 0) {
                data = createEmptyChartData(days);
            }

            if (progressChart) {
                progressChart.destroy();
            }

            // Vérifier si on a des données ou si tout est à zéro
            const hasData = data.datasets.some(dataset =>
                dataset.data && Array.isArray(dataset.data) && dataset.data.some(value => value > 0)
            );

            if (!hasData && data.labels.length > 0) {
                // Afficher un message informatif si pas de données
                console.log('Aucune donnée de progression disponible pour la période sélectionnée - Affichage d\'un graphique vide');
            }

            // Créer le graphique
            try {
                progressChart = new Chart(chartCtx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: data.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
                });
                console.log('✅ Graphique de progression créé avec succès');
            } catch (chartError) {
                console.error('Erreur lors de la création du graphique:', chartError);
                // Essayer avec des données vides
                try {
                    const emptyData = createEmptyChartData(days);
                    progressChart = new Chart(chartCtx, {
                        type: 'line',
                        data: emptyData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top' },
                                tooltip: { mode: 'index', intersect: false }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });
                } catch (fallbackError) {
                    console.error('Erreur même avec données vides:', fallbackError);
                }
            }
        })
        .catch(error => {
            console.error('Erreur chargement graphique:', error);
            // Au lieu d'afficher un message d'erreur, créer un graphique vide
            if (chartCtx && typeof Chart !== 'undefined') {
                try {
                    const emptyData = createEmptyChartData(days);
                    if (progressChart) {
                        progressChart.destroy();
                    }
                    progressChart = new Chart(chartCtx, {
                        type: 'line',
                        data: emptyData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top' },
                                tooltip: { mode: 'index', intersect: false }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });
                    console.log('✅ Graphique vide créé après erreur');
                } catch (chartError) {
                    console.error('Impossible de créer le graphique même vide:', chartError);
                    // Dernier recours : afficher un message
                    if (chartCtx && chartCtx.parentNode) {
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'chart-error';
                        errorMsg.style.cssText = 'padding: 20px; text-align: center; color: #666;';
                        errorMsg.innerHTML = '<p>⚠️ Impossible de charger le graphique de progression</p><p>Aucune donnée disponible pour le moment</p>';
                        chartCtx.parentNode.replaceChild(errorMsg, chartCtx);
                    }
                }
            }
        });
}

// Charger le graphique au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser le canvas
    chartCtx = document.getElementById('progressChart');

    if (!chartCtx) {
        console.warn('Canvas progressChart non trouvé, réessai dans 500ms...');
        setTimeout(function() {
            chartCtx = document.getElementById('progressChart');
            if (chartCtx) {
                loadProgressChart(30);
            } else {
                console.error('Canvas progressChart toujours introuvable après délai');
            }
        }, 500);
    } else {
        // Vérifier que Chart.js est chargé avant de charger le graphique
        if (typeof Chart !== 'undefined') {
            loadProgressChart(30);
        } else {
            console.warn('Chart.js non chargé, attente...');
            setTimeout(function() {
                if (typeof Chart !== 'undefined') {
                    loadProgressChart(30);
                } else {
                    console.error('Chart.js toujours non chargé après délai');
                }
            }, 500);
        }
    }

    // Boutons de période
    document.querySelectorAll('.chart-period-btn').forEach(btn => {
        btn.addEventListener('click', function(event) {
            const button = event.target.closest('.chart-period-btn');
            if (!button) {
                console.error('❌ Button not found in event target');
                return;
            }
            document.querySelectorAll('.chart-period-btn').forEach(b => b.classList.remove('active'));
            button.classList.add('active');
            const days = parseInt(button.dataset.days) || 30;
            loadProgressChart(days);
        });
    });
});

// Gestion des notifications
function showAllNotifications() {
    alert('Fonctionnalité à venir : voir toutes les notifications');
}
</script>

<!-- Coach WebM - Afficher uniquement si authentifié -->
<?php if (!empty($_SESSION['user_id']) && !empty($_SESSION['logged_in'])): ?>
<script>
  // Marquer la première visite du jour et déclencher la salutation
  // Attendre que la fonction soit disponible avant de l'appeler
  function callDailyGreeting() {
    if (typeof window.showDailyGreeting === 'function') {
      window.showDailyGreeting();
    } else {
      // Réessayer dans 100ms si la fonction n'est pas encore chargée
      setTimeout(callDailyGreeting, 100);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(callDailyGreeting, 500);
  });
</script>
<?php endif; ?>

</main>

<?php
// Si accès direct, inclure le footer et fermer les balises HTML
if (isset($direct_access) && $direct_access) {
    if (is_file(__DIR__ . '/footer.php')) {
        include_once __DIR__ . '/../includes/footer.php';
    }
    echo "</body>\n</html>";
}
?>

