<?php
$page_title = 'Sur le chemin du BAC - Préparation intensive';
$page_css = 'bac/index.css';
// Header and footer are provided by the router (index.php)

// Bloc "Retour à l'accueil" supprimé (voir patch précédent)
require_once dirname(__DIR__, 3) . '/includes/admin_auth.php';

// Charger la normalisation des niveaux (obligatoire)
require_once dirname(__DIR__, 3) . '/includes/level_normalization.php';

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

// Vérifier si l'utilisateur est connecté
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('bac-accueil.php: impossible de démarrer la session — headers déjà envoyés.');
        }
    }
}
$is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
$user_level = $_SESSION['user_level'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'Élève';
$user_level_display = function_exists('get_level_display_name') ? get_level_display_name($user_level) : $user_level;
$is_admin = function_exists('isAdmin') && isAdmin();

// Autoriser BAC uniquement pour Terminale (ou admin)
$is_terminal = function_exists('levels_match') ? levels_match($user_level, 'Terminale') : ($user_level === 'Terminale');
$deny_bac = ($is_logged_in && !$is_admin && !$is_terminal);

// Initialiser $is_bac_level (pour compatibilité et affichage conditionnel)
$user_level_normalized = function_exists('normalize_school_level') ? normalize_school_level($user_level) : $user_level;
$is_bac_level = is_bac_level($user_level);

// Données pour les sections "Objectif du jour" et "Continue"
$daily_course = null;
$daily_exercise = null;
$continue_exercises = [];
$daily_goal_xp = 20;

if ($is_logged_in && !$deny_bac && isset($pdo) && $pdo) {
    $user_level_normalized = function_exists('normalize_school_level') ? normalize_school_level($user_level) : $user_level;
    $level_candidates = array_values(array_unique(array_filter([
        $user_level,
        $user_level_display,
        $user_level_normalized,
        'BAC',
    ])));

    if (empty($level_candidates)) {
        $level_candidates = ['Terminale', 'BAC'];
    }

    $preferred_subject = null;

    try {
        $stmt = $pdo->prepare("\n            SELECT e.Subject\n            FROM exercises e\n            INNER JOIN mastery m ON m.exercise_id = e.Id\n            WHERE m.user_id = ?\n            ORDER BY m.last_attempt_at DESC\n            LIMIT 1\n        ");
        $stmt->execute([$_SESSION['user_id']]);
        $preferred_subject = $stmt->fetchColumn() ?: null;
    } catch (PDOException $e) {
        error_log('bac-accueil.php: erreur sujet préféré: ' . $e->getMessage());
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
        error_log('bac-accueil.php: erreur cours du jour: ' . $e->getMessage());
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
            error_log('bac-accueil.php: erreur exercice du jour: ' . $e->getMessage());
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
        error_log('bac-accueil.php: erreur continue: ' . $e->getMessage());
    }
}
?>

<main class="main-content bac-accueil-main min-h-screen flex flex-col bg-yellow-50">
    <?php
    // Afficher l'outil de navigation entre accueils
    if (function_exists('render_accueil_navigation')) {
        echo render_accueil_navigation('bac');
    }
// Bouton retour à l'accueil
// ...bloc supprimé : doublon navigation
?>
    <div class="mb-6 flex flex-wrap justify-center gap-3">
        <a href="<?php echo function_exists('site_url') ? site_url('landingpage') : '/public/index.php'; ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-yellow-50 text-yellow-700 font-semibold shadow hover:bg-yellow-100 transition">
            🏠 Accueil
        </a>
        <?php if ($is_logged_in && ($is_bac_level || $is_admin)): ?>
            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-yellow-200 text-yellow-900 font-semibold shadow hover:bg-yellow-300 transition"
                href="<?php echo htmlspecialchars($exercises_url); ?>">📝 Mes exercices</a>
            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-yellow-100 text-yellow-900 font-semibold shadow hover:bg-yellow-200 transition"
                href="<?php echo htmlspecialchars($courses_url); ?>">📚 Mes cours</a>
            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-amber-100 text-amber-900 font-semibold shadow hover:bg-amber-200 transition"
                href="<?php echo htmlspecialchars($quiz_url); ?>">🎯 Quiz du jour</a>
            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-yellow-50 text-yellow-700 font-semibold shadow hover:bg-yellow-100 transition"
                href="<?php echo htmlspecialchars($dashboard_url); ?>">📊 Mon dashboard</a>
        <?php endif; ?>
    </div>
    <section class="intro">
        <div class="bac-accueil-center mb-8 flex flex-col items-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-yellow-500 via-amber-400 to-yellow-600 mb-3 text-center drop-shadow-lg">
                🚀 Objectif BAC réussi !
            </h1>
            <p class="text-base text-yellow-700 text-center max-w-2xl">
                Un programme intensif et personnalisé pour maximiser tes chances de réussite.
            </p>
        </div>
        <?php $show_user_card_title = false;
$show_user_card_lead = false;
if (is_file(dirname(__DIR__, 3) . '/includes/user_card.php')) {
    include_once dirname(__DIR__, 3) . '/includes/user_card.php';
} ?>
        <?php if ($deny_bac): ?>
            <div class="coach-message bg-red-100 border-l-4 border-red-500 p-6 my-6 rounded-lg">
                <strong>❌ Les matières ne sont pas disponibles pour ce niveau.</strong><br>
                Tu es connecté en tant qu'élève de <strong><?php echo htmlspecialchars($user_level_display ?: 'N/A'); ?></strong>.<br>
                Cette section est réservée aux élèves de <strong>Terminale</strong> (ou administrateur).
            </div>
            <p><a class="btn btn-outline border-red-500 text-red-700 hover:bg-red-50 rounded-lg px-5 py-2 font-semibold transition-colors" href="<?php echo site_url('eleve/dashboard'); ?>">📊 Revenir à mon dashboard</a></p>
        <?php else: ?>


            <?php if ($is_logged_in): ?>
                <?php
        $courses_url = function_exists('site_url') ? site_url('cours', ['niveau' => 'bac']) : 'public/index.php?page=cours&niveau=bac';
                $exercises_url = function_exists('site_url') ? site_url('bac/exercices-bac') : 'public/index.php?page=bac/exercices-bac';
                $quiz_url = function_exists('site_url') ? site_url('quiz') : 'public/index.php?page=quiz';
                $dashboard_url = function_exists('site_url') ? site_url('eleve/dashboard') : 'public/index.php?page=eleve/dashboard';
                ?>

                <div class="level-home-grid grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="level-home-card level-home-card--goal bg-yellow-50 border border-yellow-200 rounded-xl shadow-md p-6 mb-6 flex flex-col">
                        <h3 class="text-lg font-bold text-yellow-700 mb-2">🎯 Ton objectif du jour</h3>
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
                            <?php if (!empty($daily_course)): ?>
                                <a class="btn btn-outline border-yellow-500 text-yellow-700 hover:bg-yellow-100 rounded-lg px-5 py-2 font-semibold transition-colors" href="<?php
                                    echo function_exists('site_url')
                                        ? site_url('view_course', ['id' => $daily_course['Id']])
                                        : 'public/index.php?page=view_course&id=' . $daily_course['Id'];
                                ?>">📚 Voir le cours</a>
                            <?php endif; ?>

                            <?php if (!empty($daily_exercise)): ?>
                                <a class="btn btn-secondary bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg px-5 py-2 font-semibold transition-colors" href="<?php
                                    echo function_exists('site_url')
                                        ? site_url('view_exercise', ['id' => $daily_exercise['Id']])
                                        : 'public/index.php?page=view_exercise&id=' . $daily_exercise['Id'];
                                ?>">📝 Faire l'exercice</a>
                            <?php endif; ?>
                        </div>
                        <div class="level-home-meta text-yellow-700 mt-2">+<?php echo (int) $daily_goal_xp; ?> XP si tu le complètes ✅</div>
                    </div>

                    <div class="level-home-card level-home-card--continue bg-yellow-50 border border-yellow-200 rounded-xl shadow p-6 mb-6">
                        <h3 class="text-lg font-bold text-yellow-700 mb-2">⏳ Continue où tu t'es arrêté</h3>
                        <?php if (!empty($continue_exercises)): ?>
                            <ul class="level-home-list">
                                <?php foreach ($continue_exercises as $ex): ?>
                                    <li class="flex items-center justify-between py-2">
                                        <span><?php echo htmlspecialchars($ex['Title'] ?? 'Exercice'); ?></span>
                                        <a class="btn btn-outline border-yellow-500 text-yellow-700 hover:bg-yellow-100 rounded-lg px-4 py-2 font-semibold transition-colors" href="<?php
                                            echo function_exists('site_url')
                                                ? site_url('view_exercise', ['id' => $ex['Id']])
                                                : 'public/index.php?page=view_exercise&id=' . $ex['Id'];
                                    ?>">Reprendre</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="level-home-sub text-yellow-700">Tu es à jour 🎉 Choisis un nouveau chapitre pour continuer !</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!$is_logged_in): ?>
            <div class="niveau-cards flex flex-wrap gap-6 justify-center my-8">
                <?php
                require_once $basePath . '/components/level_card.php';
                $bac_cards = [
                    [
                        'title' => '💡 Guide de Remédiation BAC',
                        'desc' => 'Trucs et astuces pour réussir',
                        'features' => ['Planification des révisions', 'Techniques de mémorisation', 'Gestion du stress', 'Conseils pratiques'],
                        'variant' => 'bac',
                        'ctas' => [
                            [
                                'text' => 'Consulter le guide',
                                'url' => site_url('bac/guide-remediation'),
                                'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg px-5 py-2 font-semibold transition-colors',
                            ],
                        ],
                    ],
                    [
                        'title' => '📚 Révisions Express',
                        'desc' => 'Les essentiels à maîtriser',
                        'features' => ['Fiches de synthèse', 'Points clés par matière', 'Méthodes efficaces'],
                        'variant' => 'bac',
                        'ctas' => [
                            [
                                'text' => 'Commencer les révisions',
                                'url' => site_url('cours', ['niveau' => 'bac']),
                                'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg px-5 py-2 font-semibold transition-colors',
                            ],
                        ],
                    ],
                    [
                        'title' => '📝 Sujets Types & Corrigés',
                        'desc' => 'S\'entraîner sur du concret',
                        'features' => ['Annales récentes', 'Corrigés détaillés', 'Barèmes expliqués'],
                        'variant' => 'bac',
                        'ctas' => [
                            [
                                'text' => 'S\'entraîner',
                                'url' => site_url('bac/exercices-bac'),
                                'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg px-5 py-2 font-semibold transition-colors',
                            ],
                        ],
                    ],
                    [
                        'title' => '🎤 Grand Oral',
                        'desc' => 'Maîtriser l\'art oratoire',
                        'features' => ['Préparation du support', 'Techniques de présentation', 'Gestion du stress'],
                        'variant' => 'bac',
                        'ctas' => [
                            [
                                'text' => 'Préparer l\'oral',
                                'url' => site_url('bac/preparation-orale'),
                                'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg px-5 py-2 font-semibold transition-colors',
                            ],
                        ],
                    ],
                ];
                foreach ($bac_cards as $card) {
                    echo render_level_card($card);
                }
                ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
