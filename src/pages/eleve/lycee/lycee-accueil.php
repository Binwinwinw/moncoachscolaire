<?php
$page_title = 'Lycée+';
$page_css = 'lycee/index.css';
// Header and footer are provided by the router (index.php)

// Vérifier si l'utilisateur est connecté et filtrer selon son niveau
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('lycee-accueil.php: impossible de démarrer la session — headers déjà envoyés.');
        }
    }
}

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(__DIR__ . '/../../includes/admin_auth.php')) {
    require_once __DIR__ . '/../../includes/admin_auth.php';
}

// Charger la fonction de navigation entre accueils
if (is_file(__DIR__ . '/../../includes/level_navigation.php')) {
    require_once __DIR__ . '/../../includes/level_navigation.php';
}

// Charger la normalisation des niveaux
if (is_file(__DIR__ . '/../../includes/level_normalization.php')) {
    require_once __DIR__ . '/../../includes/level_normalization.php';
} elseif (is_file(__DIR__ . '/../../../includes/level_normalization.php')) {
    require_once __DIR__ . '/../../../includes/level_normalization.php';
}

// Charger la configuration et la BDD pour les blocs dynamiques
$basePath = dirname(__DIR__, 3);
if (!isset($pdo)) {
    if (is_file($basePath . '/config/config.php')) {
        require_once $basePath . '/config/config.php';
    }
    if (is_file($basePath . '/database/connection.php')) {
        require_once $basePath . '/database/connection.php';
    }
}

// Charger le helper cours/exercices
if (is_file($basePath . '/includes/course_markdown_loader.php')) {
    require_once $basePath . '/includes/course_markdown_loader.php';
}

$is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
$user_level = $_SESSION['user_level'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'Élève';
$user_level_display = function_exists('get_level_display_name') ? get_level_display_name($user_level) : $user_level;
$user_level_normalized = function_exists('normalize_school_level') ? normalize_school_level($user_level) : strtolower($user_level);
$is_lycee_level = function_exists('is_lycee_level') ? is_lycee_level($user_level_normalized) : in_array($user_level_normalized, ['seconde','2nde','premiere','1ere','terminale']);
$is_admin = function_exists('isAdmin') && isAdmin();

// Données pour les sections "Objectif du jour" et "Continue"
$daily_course = null;
$daily_exercise = null;
$continue_exercises = [];
$daily_goal_xp = 15;

if ($is_logged_in && ($is_lycee_level || $is_admin) && isset($pdo) && $pdo) {
    $user_level_normalized = function_exists('normalize_school_level') ? normalize_school_level($user_level) : $user_level;
    $level_candidates = array_values(array_unique(array_filter([
        $user_level,
        $user_level_display,
        $user_level_normalized,
    ])));

    if (empty($level_candidates)) {
        $level_candidates = ['Seconde'];
    }

    $preferred_subject = null;

    try {
        $stmt = $pdo->prepare("\n            SELECT e.Subject\n            FROM exercises e\n            INNER JOIN mastery m ON m.exercise_id = e.Id\n            WHERE m.user_id = ?\n            ORDER BY m.last_attempt_at DESC\n            LIMIT 1\n        ");
        $stmt->execute([$_SESSION['user_id']]);
        $preferred_subject = $stmt->fetchColumn() ?: null;
    } catch (PDOException $e) {
        error_log('lycee-accueil.php: erreur sujet préféré: ' . $e->getMessage());
    }

    try {
        $placeholders = implode(',', array_fill(0, count($level_candidates), '?'));
        $params = $level_candidates;
        $sql = "SELECT * FROM courses WHERE Level IN ($placeholders)";

        if (!empty($preferred_subject)) {
            $sql .= " AND Subject = ?";
            $params[] = $preferred_subject;
        }

        $sql .= " ORDER BY RAND() LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $daily_course = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        if (!$daily_course) {
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE Level IN ($placeholders) ORDER BY RAND() LIMIT 1");
            $stmt->execute($level_candidates);
            $daily_course = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    } catch (PDOException $e) {
        error_log('lycee-accueil.php: erreur cours du jour: ' . $e->getMessage());
    }

    if ($daily_course && function_exists('getExercisesForCourse')) {
        $linked = getExercisesForCourse($daily_course['Id']);
        if (!empty($linked)) {
            $daily_exercise = $linked[0];
        }
    }

    if (!$daily_exercise) {
        try {
            $placeholders = implode(',', array_fill(0, count($level_candidates), '?'));
            $params = $level_candidates;
            $sql = "SELECT * FROM exercises WHERE is_active = 1 AND Level IN ($placeholders)";

            if (!empty($preferred_subject)) {
                $sql .= " AND Subject = ?";
                $params[] = $preferred_subject;
            } elseif (!empty($daily_course['Subject'])) {
                $sql .= " AND Subject = ?";
                $params[] = $daily_course['Subject'];
            }

            $sql .= " ORDER BY RAND() LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $daily_exercise = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('lycee-accueil.php: erreur exercice du jour: ' . $e->getMessage());
        }
    }

    try {
        $placeholders = implode(',', array_fill(0, count($level_candidates), '?'));
        $sql = "\n            SELECT e.Id, e.Title, e.Subject\n            FROM exercises e\n            LEFT JOIN mastery m\n                ON m.exercise_id = e.Id\n                AND m.user_id = ?\n                AND m.xp_earned > 0\n            WHERE e.is_active = 1\n              AND e.Level IN ($placeholders)\n              AND m.exercise_id IS NULL\n            ORDER BY e.Id DESC\n            LIMIT 3\n        ";
        $params = array_merge([$_SESSION['user_id']], $level_candidates);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $continue_exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('lycee-accueil.php: erreur continue: ' . $e->getMessage());
    }
}

// Option A: Ne pas rediriger — afficher un message d’indisponibilité si hors-lycée
?>

<main class="main-content lycee-accueil-main min-h-screen">
    <div class="mx-auto flex w-full max-w-7xl flex-col px-4 py-8 md:px-6 lg:px-8">
    <?php
    // Afficher l'outil de navigation entre accueils
    if (function_exists('render_accueil_navigation')) {
        echo render_accueil_navigation('lycee');
    }
// Bouton retour à l'accueil
// IMPORTANT: Définir les URLs AVANT de les utiliser dans les boutons
if ($is_logged_in && ($is_lycee_level || $is_admin)) {
    $level_url = '2nde';
    if (function_exists('levels_match')) {
        if (levels_match($user_level, 'Seconde')) {
            $level_url = '2nde';
        } elseif (levels_match($user_level, 'Première')) {
            $level_url = '1ere';
        } elseif (levels_match($user_level, 'Terminale')) {
            $level_url = 'terminale';
        }
    }
    $courses_url = function_exists('site_url') ? site_url('cours', ['niveau' => $level_url]) : 'public/index.php?page=cours&niveau=' . $level_url;
    $exercises_url = function_exists('site_url') ? site_url('lycee/' . $level_url . '/exercices-' . $level_url) : 'public/index.php?page=lycee/' . $level_url . '/exercices-' . $level_url;
    $quiz_url = function_exists('site_url') ? site_url('quiz') : 'public/index.php?page=quiz';
    $dashboard_url = function_exists('site_url') ? site_url('eleve/dashboard') : 'public/index.php?page=eleve/dashboard';
} else {
    $exercises_url = '';
    $courses_url = '';
    $quiz_url = '';
    $dashboard_url = '';
}
?>
    <div class="mb-6 flex flex-wrap justify-center gap-3">
        <a href="<?php echo function_exists('site_url') ? site_url('landingpage') : '/public/index.php'; ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-100 text-slate-700 font-semibold shadow hover:bg-slate-200 transition">
            🏠 Accueil
        </a>
        <?php if ($is_logged_in && ($is_lycee_level || $is_admin)): ?>
            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-purple-200 text-purple-900 font-semibold shadow hover:bg-purple-300 transition"
               href="<?php echo htmlspecialchars($exercises_url); ?>">🧩 Mes exercices</a>
            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-violet-200 text-violet-900 font-semibold shadow hover:bg-violet-300 transition"
               href="<?php echo htmlspecialchars($courses_url); ?>">📚 Mes cours</a>
            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-fuchsia-100 text-fuchsia-900 font-semibold shadow hover:bg-fuchsia-200 transition"
               href="<?php echo htmlspecialchars($quiz_url); ?>">🎯 Quiz du jour</a>
            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-yellow-100 text-yellow-900 font-semibold shadow hover:bg-yellow-200 transition"
               href="<?php echo htmlspecialchars($dashboard_url); ?>">📊 Mon dashboard</a>
        <?php endif; ?>
    </div>
    <?php
?>
    <section class="intro w-full rounded-[2rem] border border-violet-200/70 bg-white/80 p-6 shadow-[0_25px_70px_-28px_rgba(139,92,246,0.3)] backdrop-blur md:p-8">
        <?php if ($is_admin): ?>
            <div class="lycee-accueil-center mb-8 flex flex-col items-center">
                <h1 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-violet-700 via-purple-500 to-fuchsia-600 mb-3 text-center drop-shadow-lg">
                    Bienvenue sur MonCoachScolaire Lycée+ !
                </h1>
                <p class="text-base text-slate-600 text-center max-w-2xl">
                    Espace <strong>Administrateur</strong> — Accès complet à tous les niveaux et ressources.
                </p>
            </div>
            <?php $show_user_card_title = false;
            $show_user_card_lead = false;
            if (is_file(__DIR__ . '/../../../includes/user_card.php')) {
                include_once __DIR__ . '/../../../includes/user_card.php';
            } ?>
        <?php elseif ($is_logged_in && $is_lycee_level): ?>
            <h2>Bienvenue <?php echo htmlspecialchars($user_name); ?> !</h2>
            <p>Ton espace <strong><?php echo htmlspecialchars($user_level_display); ?></strong> — Des ressources adaptées à ton niveau.</p>
        <?php elseif ($is_logged_in && !$is_lycee_level && !$is_admin): ?>
            <h2 class="text-xl font-bold text-red-700 mb-2">Accès non disponible pour ton niveau</h2>
            <div class="coach-message bg-red-100 border-l-4 border-red-500 p-6 my-6 rounded-lg">
                <strong>❌ Les matières ne sont pas disponibles pour ce niveau.</strong><br>
                Tu es connecté en tant qu'élève de <strong><?php echo htmlspecialchars($user_level_display ?: 'N/A'); ?></strong>.<br>
                Cette section est réservée aux niveaux du <strong>Lycée</strong> (Seconde à Terminale).
            </div>
            <?php if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
                require_once dirname(__DIR__, 3) . '/config/site_boot.php';
            } ?>
            <?php if (function_exists('normalize_level_for_url') && function_exists('site_url')): ?>
                <?php $level_normalized = normalize_level_for_url($user_level); ?>
                <p><a class="btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-100 rounded-lg px-5 py-2 font-semibold transition-colors" href="<?php echo site_url('eleve/dashboard'); ?>">📊 Revenir à mon dashboard</a></p>
                <p><a class="btn btn-secondary bg-violet-600 hover:bg-violet-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors" href="<?php echo site_url('college/' . $level_normalized . '/exercices-' . $level_normalized); ?>">🧩 Aller à mes exercices (<?php echo htmlspecialchars($user_level_display); ?>)</a></p>
            <?php endif; ?>
        <?php else: ?>
            <div class="lycee-accueil-center mb-8 flex flex-col items-center">
                <h1 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-violet-700 via-purple-500 to-fuchsia-600 mb-3 text-center drop-shadow-lg">
                    Bienvenue sur MonCoachScolaire Lycée+ !
                </h1>
                <p class="text-base text-slate-600 text-center max-w-2xl">
                    Des cours approfondis et une méthodologie solide pour réussir au lycée.
                </p>
            </div>
        <?php endif; ?>

        <?php if ($is_logged_in && ($is_lycee_level || $is_admin)): ?>
            <?php
            // Variables $level_url, $courses_url, $exercises_url, $quiz_url, $dashboard_url
            // sont déjà définies au-dessus pour les boutons de navigation

            $goal_cta_label = '🚀 Commencer maintenant';
            if (!empty($daily_exercise)) {
                $goal_cta_url = function_exists('site_url')
                    ? site_url('view_exercise', ['id' => $daily_exercise['Id']])
                    : 'public/index.php?page=view_exercise&id=' . $daily_exercise['Id'];
            } elseif (!empty($daily_course)) {
                $goal_cta_url = function_exists('site_url')
                    ? site_url('view_course', ['id' => $daily_course['Id']])
                    : 'public/index.php?page=view_course&id=' . $daily_course['Id'];
            } else {
                $goal_cta_url = $courses_url;
            }
?>

            <div class="level-home-grid">
                <div class="level-home-card level-home-card--goal bg-violet-50 border border-violet-200 rounded-xl shadow-md p-6 mb-6 flex flex-col">
                    <h3 class="text-lg font-bold text-violet-700 mb-2">🎯 Ton objectif du jour</h3>
                    <?php if (!empty($daily_course)): ?>
                        <p class="level-home-sub">
                            Cours recommandé : <strong><?php echo htmlspecialchars($daily_course['Title'] ?? 'Cours'); ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="level-home-sub">Un cours te sera proposé dès qu’il est disponible pour ton niveau.</p>
                    <?php endif; ?>

                    <?php if (!empty($daily_exercise)): ?>
                        <p class="level-home-sub">
                            Exercice lié : <strong><?php echo htmlspecialchars($daily_exercise['Title'] ?? 'Exercice'); ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="level-home-sub">Un exercice te sera proposé dès qu’il est disponible.</p>
                    <?php endif; ?>

                    <div class="level-home-actions flex flex-wrap gap-2 mt-4">
                        <a class="btn level-home-primary bg-violet-600 hover:bg-violet-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors" href="<?php echo htmlspecialchars($goal_cta_url); ?>">
                            <?php echo htmlspecialchars($goal_cta_label); ?>
                        </a>
                        <?php if (!empty($daily_course)): ?>
                            <a class="btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-50 rounded-lg px-5 py-2 font-semibold transition-colors" href="<?php
                    echo function_exists('site_url')
                        ? site_url('view_course', ['id' => $daily_course['Id']])
                        : 'public/index.php?page=view_course&id=' . $daily_course['Id'];
                            ?>">📚 Voir le cours</a>
                        <?php endif; ?>

                        <?php if (!empty($daily_exercise)): ?>
                            <a class="btn btn-secondary bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors" href="<?php
                                echo function_exists('site_url')
                                    ? site_url('view_exercise', ['id' => $daily_exercise['Id']])
                                    : 'public/index.php?page=view_exercise&id=' . $daily_exercise['Id'];
                            ?>">📝 Faire l'exercice</a>
                        <?php endif; ?>
                    </div>
                    <div class="level-home-meta text-violet-700 mt-2">+<?php echo (int) $daily_goal_xp; ?> XP si tu le complètes ✅</div>
                </div>

                <div class="level-home-card level-home-card--continue bg-violet-50 border border-violet-200 rounded-xl shadow p-6 mb-6 flex flex-col">
                    <h3 class="text-lg font-bold text-violet-700 mb-2">⏳ Continue où tu t'es arrêté</h3>
                    <?php if (!empty($continue_exercises)): ?>
                        <ul class="level-home-list">
                            <?php foreach ($continue_exercises as $ex): ?>
                                <li class="flex items-center justify-between py-2">
                                    <span><?php echo htmlspecialchars($ex['Title'] ?? 'Exercice'); ?></span>
                                    <a class="btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-100 rounded-lg px-4 py-2 font-semibold transition-colors" href="<?php
                                        echo function_exists('site_url')
                                            ? site_url('view_exercise', ['id' => $ex['Id']])
                                            : 'public/index.php?page=view_exercise&id=' . $ex['Id'];
                                ?>">Reprendre</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="level-home-sub text-violet-700">Tu es à jour 🎉 Choisis un nouveau chapitre pour continuer !</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Les boutons principaux sont désormais en haut, à côté du bouton Accueil -->
        <?php endif; ?>


            <div class="niveau-cards flex flex-wrap gap-6 justify-center my-8">
                <?php
                // Si admin, afficher tous les niveaux. Sinon, afficher uniquement le niveau de l'élève
                if ($is_admin):
                    // Admin : afficher tous les niveaux du lycée
                    if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
                        require_once dirname(__DIR__, 3) . '/config/site_boot.php';
                    }
                    $all_levels = [
                        ['label' => 'Seconde', 'url' => '2nde'],
                        ['label' => 'Première', 'url' => '1ere'],
                        ['label' => 'Terminale', 'url' => 'terminale'],
                    ];
                    foreach ($all_levels as $level):
                        ?>
                <div class="niveau-card <?php echo $level['label'] === 'Terminale' ? 'special' : ''; ?> bg-violet-100 border border-violet-300 rounded-xl shadow p-6 flex flex-col items-center w-full max-w-xs">
                    <h3 class="text-xl font-bold text-violet-800 mb-2"><?php echo htmlspecialchars($level['label']); ?></h3>
                    <p class="text-violet-700 mb-4"><?php echo $level['label'] === 'Seconde' ? 'Adaptation au lycée' : ($level['label'] === 'Première' ? 'Spécialisation et épreuves' : 'Vers l\'enseignement supérieur'); ?></p>
                    <a href="<?php echo site_url('lycee/' . $level['url'] . '/exercices-' . $level['url']); ?>" class="btn btn-secondary bg-violet-600 hover:bg-violet-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2">🧩 Exercices <?php echo htmlspecialchars($level['label']); ?></a>
                    <a href="<?php echo site_url('cours', ['niveau' => $level['url']]); ?>" class="btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-50 rounded-lg px-5 py-2 font-semibold transition-colors">📚 Cours <?php echo htmlspecialchars($level['label']); ?></a>
                </div>
                <?php
                    endforeach;
                elseif ($is_logged_in && $is_lycee_level):
                    if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
                        require_once dirname(__DIR__, 3) . '/config/site_boot.php';
                    }
                    // Pour les URLs, on veut la version courte (2nde, 1ere, terminale)
                    $level_url = '';
                    if (function_exists('levels_match')) {
                        if (levels_match($user_level, 'Seconde')) {
                            $level_url = '2nde';
                        } elseif (levels_match($user_level, 'Première')) {
                            $level_url = '1ere';
                        } elseif (levels_match($user_level, 'Terminale')) {
                            $level_url = 'terminale';
                        }
                    } else {
                        // Fallback si levels_match n'existe pas
                        if (function_exists('normalize_school_level')) {
                            $norm = normalize_school_level($user_level);
                            if ($norm === 'Seconde') {
                                $level_url = '2nde';
                            } elseif ($norm === 'Premiere') {
                                $level_url = '1ere';
                            } elseif ($norm === 'Terminale') {
                                $level_url = 'terminale';
                            }
                        }
                    }
$level_cards = [
    'Seconde' => [
        'title' => 'Seconde',
        'desc' => 'Adaptation au lycée',
        'features' => ['Nouvelles méthodes de travail', 'Exploration des spécialités', 'Orientation progressive'],
    ],
    'Première' => [
        'title' => 'Première',
        'desc' => 'Spécialisation et épreuves',
        'features' => ['Spécialités choisies', 'Français écrit et oral', 'Contrôle continu'],
    ],
    'Terminale' => [
        'title' => 'Terminale',
        'desc' => 'Vers l\'enseignement supérieur',
        'features' => ['Spécialités approfondies', 'Philosophie', 'Grand Oral'],
    ],
];
$current_level = $level_cards[$user_level] ?? null;
$hide_level_card_for_terminale = function_exists('levels_match')
    ? levels_match($user_level, 'Terminale')
    : ($user_level === 'Terminale');
if ($current_level && !$hide_level_card_for_terminale):
    require_once $basePath . '/components/level_card.php';
    $ctas = [
        [
            'text' => 'Guide de remédiation ' . htmlspecialchars($user_level_display),
            'url' => site_url('lycee/' . $level_url . '/guide-remediation'),
            'classes' => 'bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2',
        ],
        [
            'text' => '🧩 Mes Exercices',
            'url' => site_url('lycee/' . $level_url . '/exercices-' . $level_url),
            'classes' => 'btn btn-secondary bg-violet-600 hover:bg-violet-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2',
        ],
        [
            'text' => '📚 Mes Cours',
            'url' => site_url('cours'),
            'classes' => 'btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-50 rounded-lg px-5 py-2 font-semibold transition-colors mb-2',
        ],
        [
            'text' => '❓ Quiz du jour',
            'url' => site_url('quiz'),
            'classes' => 'btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-50 rounded-lg px-5 py-2 font-semibold transition-colors mb-2',
        ],
        [
            'text' => '📊 Mon Dashboard',
            'url' => site_url('eleve/dashboard'),
            'classes' => 'btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-50 rounded-lg px-5 py-2 font-semibold transition-colors mb-2',
        ],
    ];
    if ($user_level === 'Première' || $user_level === 'Terminale') {
        if (function_exists('levels_match') && levels_match($user_level, 'Terminale')) {
            $ctas[] = [
                'text' => '🏆 Préparer le BAC',
                'url' => site_url('bac/bac-accueil'),
                'classes' => 'btn btn-special bg-yellow-400 hover:bg-yellow-500 text-black rounded-lg px-5 py-2 font-semibold transition-colors mt-2',
            ];
        }
    }
    echo render_level_card([
        'title' => $current_level['title'],
        'desc' => $current_level['desc'],
        'features' => $current_level['features'],
        'variant' => 'lycee',
        'ctas' => $ctas,
    ]);
endif;
elseif ($is_logged_in && !$is_lycee_level && !$is_admin):
    // Élève connecté hors-lycée : ne pas afficher les cartes du lycée
    // L'en-tête ci-dessus affiche déjà le message d'indisponibilité et des liens
else:
    // Affichage pour visiteur non connecté : tous les niveaux
    ?>
                <div class="niveau-card bg-violet-100 border border-violet-300 rounded-xl shadow p-6 flex flex-col items-center w-full max-w-xs">
                    <h3 class="text-xl font-bold text-violet-800 mb-2">Seconde</h3>
                    <p class="text-violet-700 mb-4">Adaptation au lycée</p>
                    <ul class="mb-4 text-violet-700">
                        <li>• Nouvelles méthodes de travail</li>
                        <li>• Exploration des spécialités</li>
                        <li>• Orientation progressive</li>
                    </ul>
                    <a href="<?php echo site_url('lycee/seconde/guide-remediation'); ?>" class="btn bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2">Guide de remédiation Seconde</a>
                    <a href="<?php echo site_url('lycee/seconde/exercices-seconde'); ?>" class="btn btn-secondary bg-violet-600 hover:bg-violet-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2">🧩 Exercices Seconde</a>
                    <a href="<?php echo site_url('cours', ['niveau' => 'seconde']); ?>" class="btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-50 rounded-lg px-5 py-2 font-semibold transition-colors">Accéder aux cours</a>
                </div>

                <div class="niveau-card bg-violet-100 border border-violet-300 rounded-xl shadow p-6 flex flex-col items-center w-full max-w-xs">
                    <h3 class="text-xl font-bold text-violet-800 mb-2">Première</h3>
                    <p class="text-violet-700 mb-4">Spécialisation et épreuves</p>
                    <ul class="mb-4 text-violet-700">
                        <li>• Spécialités choisies</li>
                        <li>• Français écrit et oral</li>
                        <li>• Contrôle continu</li>
                    </ul>
                    <a href="<?php echo site_url('lycee/premiere/guide-remediation'); ?>" class="btn bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2">Guide de remédiation Première</a>
                    <a href="<?php echo site_url('lycee/premiere/exercices-premiere'); ?>" class="btn btn-secondary bg-violet-600 hover:bg-violet-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2">🧩 Exercices Première</a>
                    <a href="<?php echo site_url('cours', ['niveau' => 'premiere']); ?>" class="btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-50 rounded-lg px-5 py-2 font-semibold transition-colors">Accéder aux cours</a>
                </div>

                <div class="niveau-card bg-violet-100 border border-violet-300 rounded-xl shadow p-6 flex flex-col items-center w-full max-w-xs">
                    <h3 class="text-xl font-bold text-violet-800 mb-2">Terminale</h3>
                    <p class="text-violet-700 mb-4">Vers l'enseignement supérieur</p>
                    <ul class="mb-4 text-violet-700">
                        <li>• Spécialités approfondies</li>
                        <li>• Philosophie</li>
                        <li>• Grand Oral</li>
                    </ul>
                    <a href="<?php echo site_url('lycee/terminale/guide-remediation'); ?>" class="btn bg-fuchsia-600 hover:bg-fuchsia-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2">Guide de remédiation Terminale</a>
                    <a href="<?php echo site_url('lycee/terminale/exercices-terminale'); ?>" class="btn btn-secondary bg-violet-600 hover:bg-violet-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors mb-2">🧩 Exercices Terminale</a>
                    <a href="<?php echo site_url('cours', ['niveau' => 'terminale']); ?>" class="btn btn-outline border-violet-600 text-violet-700 hover:bg-violet-50 rounded-lg px-5 py-2 font-semibold transition-colors">Accéder aux cours</a>
                </div>
                <?php endif; ?>
            </div>
    </section>
    </div>
</main>
