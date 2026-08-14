<?php

if (!function_exists('render_exercises_level_template')) {
    /**
     * Rend une page d'exercices niveau à partir d'une configuration compacte.
     *
     * @param array<string, mixed> $config
     */
    function render_exercises_level_template(array $config): void
    {
        $srcRoot = dirname(__DIR__);

        if (is_file($srcRoot . '/config/site_boot.php')) {
            require_once $srcRoot . '/config/site_boot.php';
        }
        if (is_file($srcRoot . '/includes/exercices_page_header.php')) {
            require_once $srcRoot . '/includes/exercices_page_header.php';
        }

        if (function_exists('ensure_session_started')) {
            ensure_session_started();
        } elseif (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
        $levelLabel = (string) ($config['level_label'] ?? '');
        $dynamicLevel = (string) ($config['dynamic_level'] ?? $levelLabel);
        $routes = is_array($config['routes'] ?? null) ? $config['routes'] : [];
        $header = is_array($config['header'] ?? null) ? $config['header'] : [];
        $coach = is_array($config['coach'] ?? null) ? $config['coach'] : [];

        $routeUrl = static function (string $name) use ($routes): string {
            $route = $routes[$name] ?? null;
            if (!is_array($route)) {
                return '#';
            }
            $page = (string) ($route['page'] ?? '#');
            $params = is_array($route['params'] ?? null) ? $route['params'] : [];
            return function_exists('site_url') ? site_url($page, $params) : 'index.php?page=' . rawurlencode($page);
        };

        $headerNav = [
            [
                'href' => $routeUrl('courses'),
                'label' => 'Cours ' . $levelLabel,
                'icon' => '📚',
                'tone' => 'primary',
            ],
            [
                'href' => $routeUrl('home'),
                'label' => 'Accueil Collège',
                'icon' => '🏠',
                'tone' => 'secondary',
            ],
        ];

        if ($is_logged_in) {
            $headerNav[] = [
                'href' => $routeUrl('dashboard'),
                'label' => 'Mon Dashboard',
                'icon' => '📊',
                'tone' => 'dashboard',
            ];
        }
?>
        <main class="main-content min-h-screen">
            <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">
                <?php
                render_exercices_page_header([
                    'icon' => (string) ($header['icon'] ?? ''),
                    'title' => (string) ($header['title'] ?? 'Exercices'),
                    'subtitle' => (string) ($header['subtitle'] ?? ''),
                    'nav_links' => $headerNav,
                ]);
                ?>

                <?php if (!$is_logged_in): ?>
                    <?php
                    if (is_file($srcRoot . '/includes/exercice_card.php')) {
                        require_once $srcRoot . '/includes/exercice_card.php';
                    }
                    if (function_exists('renderExercisePreview')) {
                        renderExercisePreview($levelLabel, (int) ($config['preview_count'] ?? 1));
                    }
                    ?>

                    <div class="banner-theme rounded-xl p-8 mb-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                            <span class="text-3xl">🔒</span>
                            Débloque ton Coach Scolaire Personnalisé
                        </h2>
                        <p class="text-gray-700 mb-6">
                            Tu vois ici un aperçu des exercices disponibles, mais pour accéder à ton coach personnel,
                            à ses conseils adaptés à ton niveau, et à l'accompagnement complet, tu dois créer un compte gratuit !
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2"><span>👨‍🏫</span>Coach Personnel</h3>
                                <p class="text-sm text-gray-600">Messages motivants et conseils adaptés à TON niveau et TES besoins spécifiques</p>
                            </div>
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2"><span>📊</span>Suivi Personnalisé</h3>
                                <p class="text-sm text-gray-600">Dashboard avec tes progrès, statistiques, et recommandations sur mesure</p>
                            </div>
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2"><span>🎯</span>Exercices Adaptés</h3>
                                <p class="text-sm text-gray-600">Contenu qui s'ajuste à tes forces et faiblesses pour maximiser tes progrès</p>
                            </div>
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2"><span>🏆</span>Récompenses & Badges</h3>
                                <p class="text-sm text-gray-600">Système de gamification pour te motiver et célébrer tes victoires</p>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="<?php echo htmlspecialchars($routeUrl('register'), ENT_QUOTES, 'UTF-8'); ?>" class="btn-theme-primary inline-flex items-center justify-center px-6 py-3 font-semibold rounded-lg shadow-lg">
                                <span class="mr-2">✨</span>
                                Créer mon compte gratuit
                            </a>
                            <a href="<?php echo htmlspecialchars($routeUrl('login'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors shadow-lg">
                                <span class="mr-2">🔑</span>
                                Me connecter
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-theme-soft border border-theme rounded-lg p-6 mb-6">
                        <strong class="text-blue-800">🌟 Salut ! Ton Coach est fier de toi !</strong><br>
                        <span class="text-blue-700"><?php echo htmlspecialchars((string) ($coach['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 mb-8 border border-green-200">
                        <h4 class="text-xl font-bold text-green-800 mb-3 flex items-center gap-2">
                            <span>🎯</span>
                            <?php echo htmlspecialchars((string) ($coach['objective_title'] ?? 'Objectif du jour'), ENT_QUOTES, 'UTF-8'); ?>
                        </h4>
                        <p class="text-green-700 mb-4"><?php echo htmlspecialchars((string) ($coach['objective_text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                <div class="bg-green-600 h-3 rounded-full transition-all duration-300" id="globalProgress"></div>
                            </div>
                            <p class="text-sm text-gray-600">
                                <strong><?php echo htmlspecialchars((string) ($coach['progress_label'] ?? 'Progression'), ENT_QUOTES, 'UTF-8'); ?> : <span id="progressText">0%</span></strong>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <section class="bg-white rounded-xl shadow-lg p-6" id="dynamic-exercises-section">
                    <div class="dynamic-exercises-container" data-dynamic-exercises data-level="<?php echo htmlspecialchars($dynamicLevel, ENT_QUOTES, 'UTF-8'); ?>"></div>
                </section>

                <?php if ($is_logged_in): ?>
                    <div class="coach-message">
                        <strong>🎉 Mission accomplie, Explorateur !</strong>
                        <?php echo htmlspecialchars((string) ($coach['complete_message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <br><br>
                        <strong>🧭 Ton Coach t'accompagne dans toutes tes aventures !</strong>
                    </div>
                <?php endif; ?>

                <link rel="stylesheet" href="<?php echo htmlspecialchars(function_exists('asset_url') ? asset_url('assets/css/pages/dynamic-exercises.css') : 'assets/css/pages/dynamic-exercises.css', ENT_QUOTES, 'UTF-8'); ?>">
                <script src="<?php echo htmlspecialchars(function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : 'assets/js/interactive-exercises.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
                <script src="<?php echo htmlspecialchars(function_exists('asset_url') ? asset_url('assets/js/dynamic-exercises.js') : 'assets/js/dynamic-exercises.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
            </div>
        </main>
<?php
    }
}
?>