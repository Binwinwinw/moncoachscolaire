<?php
$page_title = 'Collège+';
// page-specific stylesheet (kept for page-specific customizations)
$page_css = 'college/index.css';
// header and footer are provided by the router (index.php)

// Vérifier si l'utilisateur est connecté et filtrer selon son niveau
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(dirname(__DIR__, 3) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 3) . '/includes/admin_auth.php';
}

// Charger la normalisation des niveaux
if (is_file(dirname(__DIR__, 3) . '/includes/level_normalization.php')) {
    require_once dirname(__DIR__, 3) . '/includes/level_normalization.php';
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

if (!function_exists('get_theme_variant_by_level') && is_file($basePath . '/config/site_boot.php')) {
    require_once $basePath . '/config/site_boot.php';
}

$college_theme = (is_array($GLOBALS['app_theme']['variant'] ?? null) && !empty($GLOBALS['app_theme']['variant']))
    ? $GLOBALS['app_theme']['variant']
    : (function_exists('get_theme_variant_by_level')
        ? get_theme_variant_by_level('6eme')
        : [
        'title' => 'text-green-700',
        'subtitle' => 'text-green-800',
        'nav_primary' => 'bg-green-600 hover:bg-green-700 focus:ring-green-400',
        'nav_secondary' => 'bg-green-500 hover:bg-green-600 focus:ring-green-300',
        'nav_tertiary' => 'bg-green-400 hover:bg-green-500 focus:ring-green-200',
        'nav_dashboard' => 'bg-green-800 hover:bg-green-900 focus:ring-green-400',
        'soft_buttons' => [
            'bg-green-50 text-green-700 hover:bg-green-100 focus:ring-green-300',
            'bg-green-100 text-green-800 hover:bg-green-200 focus:ring-green-400',
            'bg-green-200 text-green-900 hover:bg-green-300 focus:ring-green-500',
            'bg-green-100 text-green-900 hover:bg-green-200 focus:ring-green-500',
        ],
    ]);

// Charger le helper cours/exercices
if (is_file($basePath . '/includes/course_markdown_loader.php')) {
    require_once $basePath . '/includes/course_markdown_loader.php';
}

$is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
$user_level = $_SESSION['user_level'] ?? '';
$user_name = $_SESSION['user_name'] ?? 'Élève';
// Normaliser le niveau pour comparaisons
$user_level_normalized = function_exists('normalize_school_level') ? normalize_school_level($user_level) : $user_level;
$user_level_display = function_exists('get_level_display_name') ? get_level_display_name($user_level) : $user_level;
$is_college_level = function_exists('is_college_level') ? is_college_level($user_level) : in_array($user_level_normalized, ['6eme', '5eme', '4eme', '3eme']);
$is_admin = function_exists('isAdmin') && isAdmin();

// Données pour les sections "Objectif du jour" et "Continue"
$daily_course = null;
$daily_exercise = null;
$continue_exercises = [];
$daily_goal_xp = 10;

if ($is_logged_in && ($is_college_level || $is_admin) && isset($pdo) && $pdo) {
    $level_candidates = array_values(array_unique(array_filter([
        $user_level,
        $user_level_display,
        $user_level_normalized,
    ])));

    if (empty($level_candidates)) {
        $level_candidates = ['6eme', '6ème'];
    }

    $preferred_subject = null;

    try {
        $stmt = $pdo->prepare("\n            SELECT e.Subject\n            FROM exercises e\n            INNER JOIN mastery m ON m.exercise_id = e.Id\n            WHERE m.user_id = ?\n            ORDER BY m.last_attempt_at DESC\n            LIMIT 1\n        ");
        $stmt->execute([$_SESSION['user_id']]);
        $preferred_subject = $stmt->fetchColumn() ?: null;
    } catch (PDOException $e) {
        error_log('college-accueil.php: erreur sujet préféré: ' . $e->getMessage());
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
        error_log('college-accueil.php: erreur cours du jour: ' . $e->getMessage());
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
            error_log('college-accueil.php: erreur exercice du jour: ' . $e->getMessage());
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
        error_log('college-accueil.php: erreur continue: ' . $e->getMessage());
    }
}

// Option A: Ne pas rediriger — afficher un message d’indisponibilité si hors-collège
?>


<main class="main-content college-accueil-main min-h-screen">
    <div class="mx-auto flex w-full max-w-7xl flex-col items-center px-4 py-8 md:px-6 lg:px-8">
    <?php
    if (function_exists('render_accueil_navigation')) {
        echo render_accueil_navigation('college');
    }

// IMPORTANT: Définir les URLs de navigation AVANT de les utiliser dans les boutons
if ($is_logged_in && ($is_college_level || $is_admin)) {
    $level_normalized = function_exists('normalize_level_for_url') ? normalize_level_for_url($user_level ?: '6eme') : '6eme';
    $courses_url = function_exists('site_url') ? site_url('cours', ['niveau' => $level_normalized]) : 'public/index.php?page=cours&niveau=' . $level_normalized;
    $exercises_url = function_exists('site_url') ? site_url('exercices') : 'public/index.php?page=exercices';
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
        <a href="<?php echo function_exists('site_url') ? site_url('landingpage') : '/public/index.php'; ?>"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl <?php echo htmlspecialchars($college_theme['soft_buttons'][0]); ?> font-semibold shadow transition">
            🏠 Accueil
        </a>
            <?php if ($is_logged_in && ($is_college_level || $is_admin)): ?>
                <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl <?php echo htmlspecialchars($college_theme['nav_primary']); ?> text-white font-semibold shadow transition"
                    href="<?php echo htmlspecialchars($exercises_url); ?>">🧩 Mes exercices</a>
                <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl <?php echo htmlspecialchars($college_theme['nav_secondary']); ?> text-white font-semibold shadow transition"
                    href="<?php echo htmlspecialchars($courses_url); ?>">📚 Mes cours</a>
                <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl <?php echo htmlspecialchars($college_theme['nav_tertiary']); ?> text-white font-semibold shadow transition"
                    href="<?php echo htmlspecialchars($quiz_url); ?>">🎯 Quiz du jour</a>
                <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl <?php echo htmlspecialchars($college_theme['nav_dashboard']); ?> text-white font-semibold shadow transition"
                    href="<?php echo htmlspecialchars($dashboard_url); ?>">📊 Mon dashboard</a>
            <?php endif; ?>
    </div>

    <section class="intro w-full rounded-[2rem] border border-emerald-200/70 bg-white/80 p-6 shadow-[0_25px_70px_-28px_rgba(34,197,94,0.35)] backdrop-blur md:p-8">
        <?php
    $show_user_card_title = false;
$show_user_card_lead = false;
if (is_file(dirname(__DIR__, 3) . '/includes/user_card.php')) {
    include_once dirname(__DIR__, 3) . '/includes/user_card.php';
}
?>

        <?php if ($is_admin): ?>
            <h2 class="text-2xl font-bold text-[#2f3e46] mb-2">
                Bienvenue <?php echo htmlspecialchars($user_name); ?> ! ⚙️
            </h2>
            <p class="text-[#52796f]">
                Espace <strong>Administrateur</strong> – accès complet à tous les niveaux et ressources.
            </p>
        <?php elseif ($is_logged_in && $is_college_level): ?>
            <h2 class="text-2xl font-bold <?php echo htmlspecialchars($college_theme['title']); ?> mb-2">
                Bienvenue <?php echo htmlspecialchars($user_name); ?> !
            </h2>
            <p class="<?php echo htmlspecialchars($college_theme['subtitle']); ?>">
                Ton espace <strong><?php echo htmlspecialchars($user_level_display); ?></strong> – ressources adaptées à ton niveau.
            </p>
        <?php elseif ($is_logged_in && !$is_college_level && !$is_admin): ?>
            <h2 class="text-2xl font-bold text-[#2f3e46] mb-2">
                Accès non disponible pour ton niveau
            </h2>
            <div class="coach-message mt-4 bg-[#fee2e2] border-l-4 border-[#ef4444] p-6 rounded-lg">
                <strong>❌ Les matières ne sont pas disponibles pour ce niveau.</strong><br>
                Tu es connecté en tant qu'élève de <strong><?php echo htmlspecialchars($user_level_display ?: 'N/A'); ?></strong>.<br>
                Cette section est réservée aux niveaux du <strong>Collège</strong> (6ème à 3ème).
            </div>
            <?php if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
                require_once dirname(__DIR__, 3) . '/config/site_boot.php';
            } ?>
            <?php if (function_exists('normalize_level_for_url') && function_exists('site_url')): ?>
                <?php $level_normalized = normalize_level_for_url($user_level); ?>
                <p class="mt-4">
                    <a class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-[#52796f] text-[#52796f] hover:bg-[#dce6e0] transition"
                        href="<?php echo site_url('eleve/dashboard'); ?>">📊 Revenir à mon dashboard</a>
                </p>
                <p class="mt-2">
                    <a class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#52796f] text-white hover:bg-[#354f52] transition"
                        href="<?php echo site_url('exercices'); ?>">🧩 Aller à mes exercices (<?php echo htmlspecialchars($user_level_display); ?>)</a>
                </p>
            <?php endif; ?>
        <?php else: ?>
            <div class="college-accueil-center mb-10 flex flex-col items-center">
                <h1 class="text-4xl md:text-5xl font-extrabold <?php echo htmlspecialchars($college_theme['title']); ?> mb-3 text-center drop-shadow">
                    Bienvenue sur MonCoachScolaire Collège+ !
                </h1>
                <p class="text-base <?php echo htmlspecialchars($college_theme['subtitle']); ?> text-center max-w-2xl">
                    Des ressources adaptées à ton niveau, pour progresser à ton rythme et atteindre tes objectifs sereinement.
                </p>
            </div>
        <?php endif; ?>

        <?php if ($is_logged_in && ($is_college_level || $is_admin)): ?>
            <?php
            // Variables $level_normalized, $courses_url, $exercises_url, $quiz_url, $dashboard_url
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="rounded-2xl p-7 shadow-lg flex flex-col gap-4 border border-[#c0d4c9] bg-gradient-to-br from-[#e3f0e8] to-[#f4faf6]">
                    <h3 class="text-xl font-bold text-[#2f3e46] flex items-center gap-2">
                        🎯 Ton objectif du jour
                        <span class="ml-2 px-2 py-0.5 rounded text-sm border border-[#cbdad1] bg-[#f0f5f2] text-[#2f3e46]">
                            <?php echo htmlspecialchars($user_level_display); ?>
                        </span>
                    </h3>

                    <?php if (!empty($daily_course)): ?>
                        <p class="text-[#355f48]">
                            Cours recommandé :
                            <strong><?php echo htmlspecialchars($daily_course['Title'] ?? 'Cours'); ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="text-[#355f48]">
                            Un cours te sera proposé dès qu’il est disponible pour ton niveau.
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($daily_exercise)): ?>
                        <p class="text-[#355f48]">
                            Exercice lié :
                            <strong><?php echo htmlspecialchars($daily_exercise['Title'] ?? 'Exercice'); ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="text-[#355f48]">
                            Un exercice te sera proposé dès qu’il est disponible.
                        </p>
                    <?php endif; ?>

                    <div class="flex flex-wrap gap-4 mt-4">
                        <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#52796f] text-white font-semibold shadow hover:bg-[#354f52] focus:outline-none focus:ring-2 focus:ring-[#84a98c] transition"
                            href="<?php echo htmlspecialchars($goal_cta_url); ?>">
                            🚀 <?php echo htmlspecialchars($goal_cta_label); ?>
                        </a>

                        <?php if (!empty($daily_course)): ?>
                            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#84a98c] text-white font-semibold shadow hover:bg-[#52796f] focus:outline-none focus:ring-2 focus:ring-[#84a98c] transition"
                                href="<?php
                    echo function_exists('site_url')
                        ? site_url('view_course', ['id' => $daily_course['Id']])
                        : 'public/index.php?page=view_course&id=' . $daily_course['Id'];
                            ?>">📚 Voir le cours</a>
                        <?php endif; ?>

                        <?php if (!empty($daily_exercise)): ?>
                            <a class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#355f48] text-white font-semibold shadow hover:bg-[#2f3e46] focus:outline-none focus:ring-2 focus:ring-[#84a98c] transition"
                                href="<?php
                            echo function_exists('site_url')
                            ? site_url('view_exercise', ['id' => $daily_exercise['Id']])
                            : 'public/index.php?page=view_exercise&id=' . $daily_exercise['Id'];
                            ?>">🧩 Faire l'exercice</a>
                        <?php endif; ?>
                    </div>

                    <div class="text-sm text-[#355f48] mt-2">
                        +<?php echo (int) $daily_goal_xp; ?> XP si tu le complètes ✅
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow flex flex-col gap-4 border border-[#d3ded7]">
                    <h3 class="text-lg font-bold text-[#2f3e46]">⏳ Continue où tu t'es arrêté</h3>
                    <?php if (!empty($continue_exercises)): ?>
                        <ul class="flex flex-col gap-3">
                            <?php foreach ($continue_exercises as $ex): ?>
                                <li class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-[#52796f]">
                                        <?php echo htmlspecialchars($ex['Title'] ?? 'Exercice'); ?>
                                    </span>
                                    <a class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-[#52796f] text-white text-sm font-semibold hover:bg-[#354f52] transition"
                                                href="<?php
                                    echo function_exists('site_url')
                                            ? site_url('view_exercise', ['id' => $ex['Id']])
                                            : 'public/index.php?page=view_exercise&id=' . $ex['Id'];
                                ?>">Reprendre</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-sm text-[#52796f]">
                            Tu es à jour 🎉 Choisis un nouveau chapitre pour continuer !
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$is_logged_in && !$is_admin): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 w-full max-w-7xl">
            <?php
            // Affichage des 4 cartes collège harmonisées UNIQUEMENT pour les visiteurs
            if (is_file(dirname(__DIR__, 3) . '/config/site_boot.php')) {
                require_once dirname(__DIR__, 3) . '/config/site_boot.php';
            }
            $levels = [
                '6ème' => [
                    'title' => '6ème',
                    'desc' => 'Transition vers le collège',
                    'features' => ['Révisions des bases', 'Découverte du collège', 'Méthodologie'],
                ],
                '5ème' => [
                    'title' => '5ème',
                    'desc' => 'Consolidation des acquis',
                    'features' => ['Approfondissement', 'Nouvelles matières', 'Projets collaboratifs'],
                ],
                '4ème' => [
                    'title' => '4ème',
                    'desc' => 'Consolidation des acquis',
                    'features' => ['Approfondissement', 'Nouvelles matières', 'Projets collaboratifs'],
                ],
                '3ème' => [
                    'title' => '3ème - Brevet',
                    'desc' => 'Préparation intensive',
                    'features' => ['Révisions ciblées', 'Sujets types', 'Coaching brevet'],
                    'special' => true,
                ],
            ];
            require_once $basePath . '/components/level_card.php';
            foreach ($levels as $level => $data) {
                $level_normalized = normalize_level_for_url($level);
                echo render_level_card([
                    'title' => $data['title'],
                    'desc' => $data['desc'],
                    'features' => $data['features'],
                    'variant' => 'college',
                    'special' => !empty($data['special']),
                    // Responsive width: full on mobile, sm/md/lg on desktop
                    'extra_classes' => 'bg-green-100 border border-green-200 rounded-xl shadow-md p-6 flex flex-col items-center w-full max-w-full md:max-w-sm lg:max-w-md xl:max-w-lg min-h-[480px]',
                    'ctas' => [
                        [
                            'text' => 'Guide de remédiation ' . htmlspecialchars($level),
                            'url' => site_url('college/' . $level_normalized . '/guide-remediation'),
                            'classes' => 'bg-green-600 hover:bg-green-700 text-white rounded-lg px-5 py-2 font-semibold transition-colors w-full mb-1',
                        ],
                        [
                            'text' => '🧩 Exercices ' . htmlspecialchars($level),
                            'url' => site_url('college/' . $level_normalized . '/exercices-' . $level_normalized),
                            'classes' => 'bg-white text-green-700 border border-green-400 hover:bg-green-50 rounded-lg px-5 py-2 font-semibold transition-colors w-full mb-1',
                        ],
                        [
                            'text' => '📚 Cours ' . htmlspecialchars($level),
                            'url' => site_url('cours', ['niveau' => $level_normalized]),
                            'classes' => 'bg-white text-green-700 border border-green-400 hover:bg-green-50 rounded-lg px-5 py-2 font-semibold transition-colors w-full',
                        ],
                    ],
                ]);
            }
            ?>
        </div>
        <?php endif; ?>
        </div>
    </section>
    </div>
</main>

