<?php
// ...existing code...
// site_boot.php — initialize site-wide variables and session for pages outside root

// Load project config FIRST (baseUrl, DB, etc.) - this must happen BEFORE session_start()
if (!isset($baseUrl) || !isset($pdo)) {
    // Try to find config.php in the same directory
    $candidate = __DIR__ . '/config.php';
    if (is_file($candidate)) {
        require_once $candidate;
    }
}

// NOW start the session (after config.php has configured session ini settings)
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('site_boot: impossible de démarrer la session — headers déjà envoyés.');
        }
    }
}
// Basic auth vars expected by legacy pages
// Vérifier à la fois user_id ET logged_in pour être cohérent avec la topbar
$is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
$user_name = $is_logged_in ? ($_SESSION['user_name'] ?? 'Utilisateur') : '';
$user_level = $is_logged_in ? ($_SESSION['user_level'] ?? '') : '';

// Keep session role/level in sync with DB so role changes (parent -> admin, etc.) take effect immediately
if ($is_logged_in && isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare('SELECT Role, UserLevel FROM users WHERE Id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        if ($row = $stmt->fetch()) {
            $dbRole = $row['Role'] ?? 'student';
            $dbLevel = $row['UserLevel'] ?? '';

            // Update role if it differs from session
            if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== $dbRole) {
                $_SESSION['user_role'] = $dbRole;
                if ($dbRole === 'admin') {
                    unset($_SESSION['parent_id']);
                } elseif ($dbRole === 'parent') {
                    $_SESSION['parent_id'] = $_SESSION['user_id'];
                }
            }

            // Refresh level if stored in DB
            if (!empty($dbLevel)) {
                $_SESSION['user_level'] = $dbLevel;
                $user_level = $dbLevel;
            }
        }
    } catch (Exception $e) {
        error_log('site_boot: sync role failed - ' . $e->getMessage());
    }
}

if (!function_exists('get_theme_by_level')) {
    function get_theme_by_level($level)
    {
        $themes = [
            '6ème' => ['icon' => '🗡️', 'name' => 'Aventurier', 'color' => '#2e5984'],
            '5ème' => ['icon' => '🧭', 'name' => 'Explorateur', 'color' => '#8B4513'],
            '4ème' => ['icon' => '⚙️', 'name' => 'Ingénieur', 'color' => '#2E8B57'],
            '3ème' => ['icon' => '🏰', 'name' => 'Expert', 'color' => '#8B0000'],
        ];
        return $themes[$level] ?? $themes['6ème'];
    }
}

/**
 * Thème applicatif — règle d'usage :
 * - Topbar : fond via theme-level.css (header.topbar + body.theme-*)
 * - Boutons topbar : classes Tailwind compilées (600) ou btn-theme-primary
 * - Contenu pages : utilitaires theme-level.css (banner-theme, cover-theme…)
 * - Résolution : bootstrap_app_theme() / resolve_app_theme() — priorité page > session > neutre
 */

if (!function_exists('get_theme_variant_by_level')) {
    function get_theme_variant_by_level($level)
    {
        $normalized = strtolower((string) $level);

        if ($normalized === 'bac') {
            return [
                'cover' => 'bg-gradient-to-br from-amber-100 to-yellow-50',
                'title' => 'text-amber-700',
                'subtitle' => 'text-amber-800',
                'topbar_bg' => 'bg-amber-700',
                'topbar_border' => 'border-amber-800',
                'button_demo' => 'bg-amber-600 text-white hover:bg-amber-700',
                'button_dashboard' => 'bg-amber-600 text-white hover:bg-amber-700',
                'button_logout' => 'bg-slate-700 text-white hover:bg-slate-800',
                'nav_primary' => 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-400',
                'nav_secondary' => 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-300',
                'nav_tertiary' => 'bg-amber-700 hover:bg-amber-800 focus:ring-amber-400',
                'nav_dashboard' => 'bg-amber-800 hover:bg-amber-900 focus:ring-amber-400',
                'soft_buttons' => [
                    'bg-amber-50 text-amber-800 hover:bg-amber-100 focus:ring-amber-300',
                    'bg-amber-100 text-amber-800 hover:bg-amber-200 focus:ring-amber-400',
                    'bg-amber-100 text-amber-900 hover:bg-amber-200 focus:ring-amber-500',
                    'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 focus:ring-yellow-400',
                ],
                'banner' => 'bg-gradient-to-br from-amber-50 to-yellow-100 border-l-4 border-amber-600',
                'menu_primary' => 'bg-amber-700 text-white hover:bg-amber-800',
                'menu_secondary' => 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100',
                'modal_close_hover' => 'hover:text-amber-700',
                'modal_title' => 'text-amber-700',
                'modal_heading' => 'text-amber-700',
                'modal_advice' => 'text-amber-700',
            ];
        }

        if (in_array($normalized, ['seconde', '2nde', 'premiere', 'première', '1ere', '1ère', 'terminale', 'lycee', 'lycée'], true)) {
            return [
                'cover' => 'bg-gradient-to-br from-purple-100 to-violet-50',
                'title' => 'text-purple-700',
                'subtitle' => 'text-purple-800',
                'topbar_bg' => 'bg-violet-700',
                'topbar_border' => 'border-violet-800',
                'button_demo' => 'bg-violet-600 text-white hover:bg-violet-700',
                'button_dashboard' => 'bg-violet-600 text-white hover:bg-violet-700',
                'button_logout' => 'bg-slate-700 text-white hover:bg-slate-800',
                'nav_primary' => 'bg-violet-600 hover:bg-violet-700 focus:ring-violet-400',
                'nav_secondary' => 'bg-purple-500 hover:bg-purple-600 focus:ring-purple-300',
                'nav_tertiary' => 'bg-violet-700 hover:bg-violet-800 focus:ring-violet-400',
                'nav_dashboard' => 'bg-purple-800 hover:bg-purple-900 focus:ring-purple-400',
                'soft_buttons' => [
                    'bg-purple-50 text-purple-800 hover:bg-purple-100 focus:ring-purple-300',
                    'bg-purple-100 text-purple-800 hover:bg-purple-200 focus:ring-purple-400',
                    'bg-violet-100 text-violet-800 hover:bg-violet-200 focus:ring-violet-400',
                    'bg-violet-100 text-violet-900 hover:bg-violet-200 focus:ring-violet-500',
                ],
                'banner' => 'bg-gradient-to-br from-purple-50 to-violet-100 border-l-4 border-violet-600',
                'menu_primary' => 'bg-violet-700 text-white hover:bg-violet-800',
                'menu_secondary' => 'bg-purple-50 text-purple-800 border border-purple-200 hover:bg-purple-100',
                'modal_close_hover' => 'hover:text-purple-700',
                'modal_title' => 'text-purple-700',
                'modal_heading' => 'text-purple-700',
                'modal_advice' => 'text-purple-700',
            ];
        }

        return [
            'cover' => 'bg-gradient-to-br from-green-100 to-green-50',
            'title' => 'text-green-700',
            'subtitle' => 'text-green-800',
            'topbar_bg' => 'bg-emerald-700',
            'topbar_border' => 'border-emerald-800',
            'button_demo' => 'bg-emerald-600 text-white hover:bg-emerald-700',
            'button_dashboard' => 'bg-emerald-600 text-white hover:bg-emerald-700',
            'button_logout' => 'bg-slate-700 text-white hover:bg-slate-800',
            'nav_primary' => 'bg-green-600 hover:bg-green-700 focus:ring-green-400',
            'nav_secondary' => 'bg-green-500 hover:bg-green-600 focus:ring-green-300',
            'nav_tertiary' => 'bg-emerald-700 hover:bg-emerald-800 focus:ring-emerald-400',
            'nav_dashboard' => 'bg-green-800 hover:bg-green-900 focus:ring-green-400',
            'soft_buttons' => [
                'bg-green-50 text-green-800 hover:bg-green-100 focus:ring-green-300',
                'bg-green-100 text-green-800 hover:bg-green-200 focus:ring-green-400',
                'bg-emerald-100 text-emerald-900 hover:bg-emerald-200 focus:ring-emerald-500',
                'bg-green-100 text-green-900 hover:bg-green-200 focus:ring-green-500',
            ],
            'banner' => 'bg-gradient-to-br from-green-50 to-emerald-100 border-l-4 border-emerald-600',
            'menu_primary' => 'bg-emerald-700 text-white hover:bg-emerald-800',
            'menu_secondary' => 'bg-green-50 text-green-800 border border-green-200 hover:bg-green-100',
            'modal_close_hover' => 'hover:text-green-700',
            'modal_title' => 'text-green-700',
            'modal_heading' => 'text-green-700',
            'modal_advice' => 'text-green-700',
        ];
    }
}

if (!function_exists('get_neutral_theme_variant')) {
    function get_neutral_theme_variant(): array
    {
        return [
            'cover' => 'bg-gradient-to-br from-slate-100 to-slate-50',
            'title' => 'text-slate-800',
            'subtitle' => 'text-slate-700',
            'topbar_bg' => 'bg-slate-700',
            'topbar_border' => 'border-slate-800',
            'button_demo' => 'bg-slate-600 text-white hover:bg-slate-700',
            'button_dashboard' => 'bg-slate-600 text-white hover:bg-slate-700',
            'button_logout' => 'bg-slate-700 text-white hover:bg-slate-800',
            'nav_primary' => 'bg-slate-700 hover:bg-slate-800 focus:ring-slate-400',
            'nav_secondary' => 'bg-slate-600 hover:bg-slate-700 focus:ring-slate-300',
            'nav_tertiary' => 'bg-slate-600 hover:bg-slate-700 focus:ring-slate-400',
            'nav_dashboard' => 'bg-slate-800 hover:bg-slate-900 focus:ring-slate-400',
            'soft_buttons' => [
                'bg-slate-50 text-slate-800 hover:bg-slate-100 focus:ring-slate-300',
                'bg-slate-100 text-slate-800 hover:bg-slate-200 focus:ring-slate-400',
                'bg-slate-100 text-slate-900 hover:bg-slate-200 focus:ring-slate-500',
                'bg-slate-100 text-slate-900 hover:bg-slate-200 focus:ring-slate-500',
            ],
            'banner' => 'bg-gradient-to-br from-slate-50 to-slate-100 border-l-4 border-slate-500',
            'menu_primary' => 'bg-slate-700 text-white hover:bg-slate-800',
            'menu_secondary' => 'bg-slate-50 text-slate-800 border border-slate-200 hover:bg-slate-100',
            'modal_close_hover' => 'hover:text-slate-700',
            'modal_title' => 'text-slate-800',
            'modal_heading' => 'text-slate-700',
            'modal_advice' => 'text-slate-700',
        ];
    }
}

if (!function_exists('get_theme_tier')) {
    /**
     * @return 'college'|'lycee'|'bac'
     */
    function get_theme_tier(string $level): string
    {
        $normalized = strtolower(trim($level));

        if ($normalized === 'bac') {
            return 'bac';
        }

        if (in_array($normalized, ['seconde', '2nde', 'premiere', 'première', '1ere', '1ère', 'terminale', 'lycee', 'lycée'], true)) {
            return 'lycee';
        }

        return 'college';
    }
}

if (!function_exists('infer_page_theme_level')) {
    /**
     * Déduit le niveau thème depuis le slug routeur (ex. college/6eme/guide-remediation → 6eme).
     */
    function infer_page_theme_level(string $pageRaw): ?string
    {
        $page = strtolower(str_replace('\\', '/', trim($pageRaw, '/ ')));

        if (preg_match('#(?:^|/)(6eme|5eme|4eme|3eme|2nde|1ere|terminale|seconde|premiere|bac)(?:/|$)#', $page, $m)) {
            $aliases = [
                'seconde' => '2nde',
                'premiere' => '1ere',
            ];
            return $aliases[$m[1]] ?? $m[1];
        }

        if (preg_match('#(?:^|/)bac(?:/|$)#', $page)) {
            return 'bac';
        }
        if (preg_match('#(?:^|/)lycee(?:/|$)#', $page)) {
            return '2nde';
        }
        if (preg_match('#(?:^|/)college(?:/|$)#', $page)) {
            return '6eme';
        }

        return null;
    }
}

if (!function_exists('resolve_app_theme')) {
    /**
     * @return array{tier: string, level_key: string, variant: array<string, mixed>}
     */
    function resolve_app_theme(?string $pageThemeLevel = null): array
    {
        $neutral = [
            'tier' => 'neutral',
            'level_key' => 'neutral',
            'variant' => get_neutral_theme_variant(),
        ];

        $resolveLevel = static function (string $level): array {
            $levelKey = function_exists('normalize_level_for_url')
                ? normalize_level_for_url($level)
                : strtolower(trim($level));

            if ($levelKey === '') {
                return [];
            }

            return [
                'tier' => get_theme_tier($levelKey),
                'level_key' => $levelKey,
                'variant' => get_theme_variant_by_level($levelKey),
            ];
        };

        // Priorité 1 : contexte page (visiteurs inclus — ex. guide remédiation 6e)
        if (!empty($pageThemeLevel)) {
            $fromPage = $resolveLevel($pageThemeLevel);
            if ($fromPage !== []) {
                return $fromPage;
            }
        }

        $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
        $is_demo = function_exists('isDemoUser') && isDemoUser();
        $is_parent = !empty($_SESSION['parent_id']);

        if (!$is_logged_in && !$is_demo) {
            return $neutral;
        }

        $is_admin = false;
        if ($is_logged_in && function_exists('isAdmin')) {
            $is_admin = isAdmin();
        } elseif ($is_logged_in && is_file(dirname(__DIR__) . '/includes/admin_auth.php')) {
            require_once dirname(__DIR__) . '/includes/admin_auth.php';
            $is_admin = function_exists('isAdmin') && isAdmin();
        }

        if ($is_admin || $is_parent) {
            return $neutral;
        }

        $levelKey = '';
        if ($is_demo && !empty($_SESSION['demo_level'])) {
            $levelKey = function_exists('normalize_level_for_url')
                ? normalize_level_for_url((string) $_SESSION['demo_level'])
                : strtolower((string) $_SESSION['demo_level']);
        } elseif (!empty($_SESSION['user_level'])) {
            $levelKey = function_exists('normalize_level_for_url')
                ? normalize_level_for_url((string) $_SESSION['user_level'])
                : strtolower((string) $_SESSION['user_level']);
        }

        if ($levelKey === '') {
            return $neutral;
        }

        return [
            'tier' => get_theme_tier($levelKey),
            'level_key' => $levelKey,
            'variant' => get_theme_variant_by_level($levelKey),
        ];
    }
}
$current_theme = get_theme_by_level($user_level);

/**
 * Normalise un niveau scolaire pour les URLs (slugs routeur).
 * Chaîne : normalize_school_level() (canonique) → slug URL (6eme, 2nde, 1ere, terminale, bac).
 * Ne pas utiliser normalize_school_level() directement pour les URLs — toujours passer par ici.
 */
if (!function_exists('normalize_level_for_url')) {
    function normalize_level_for_url($level)
    {
        // Si le niveau est vide, retourner une valeur par défaut
        if (empty($level)) {
            return '6eme'; // Valeur par défaut pour éviter les URLs invalides
        }

        $trimmed = trim((string) $level);
        if (preg_match('/^bac$/i', $trimmed)) {
            return 'bac';
        }

        if (function_exists('normalize_school_level')) {
            $schoolNorm = normalize_school_level($level);
            $fromSchool = [
                '6eme' => '6eme',
                '5eme' => '5eme',
                '4eme' => '4eme',
                '3eme' => '3eme',
                'Seconde' => '2nde',
                'Premiere' => '1ere',
                'Terminale' => 'terminale',
            ];
            if (isset($fromSchool[$schoolNorm])) {
                return $fromSchool[$schoolNorm];
            }
        }

        // Premier essai avec le mapping exact (pour les cas normaux)
        $mapping = [
            '6ème' => '6eme',
            '5ème' => '5eme',
            '4ème' => '4eme',
            '3ème' => '3eme',
            'Seconde' => '2nde',
            'Première' => '1ere',
            'Terminale' => 'terminale',
            'BAC' => 'bac',
            '1ère' => '1ere',
            '1ere' => '1ere',
            '2nde' => '2nde',
            '2nd' => '2nde',
            // Versions anglaises/minuscules comme fallback
            '6eme' => '6eme',
            '5eme' => '5eme',
            '4eme' => '4eme',
            '3eme' => '3eme',
            'seconde' => '2nde',
            'premiere' => '1ere',
            'terminale' => 'terminale',
            'bac' => 'bac',
        ];

        // Si dans le mapping, utiliser la valeur directement
        if (isset($mapping[$level])) {
            return $mapping[$level];
        }

        // Nettoyer: supprimer caractères non-ASCII corrompus, conserver alphanumérique ASCII
        $cleaned = preg_replace('/[^a-zA-Z0-9]/i', '', trim($level));
        $lower = strtolower($cleaned);

        // Détecter les types par patterns robustes
        // Collège (6, 5, 4, 3 + "eme")
        if (preg_match('/^([6543])/', $lower, $m)) {
            return $m[1] . 'eme';
        }

        // Lycée: essayer les patterns même avec caractères manquants
        // "Seconde" → peut devenir "secode" après nettoyage
        if (preg_match('/^se[cd].*de$|^secode$|^second|^2nd/', $lower)) {
            return '2nde';
        }
        // "Première" → peut devenir "premiere" ou "premiers"
        if (preg_match('/^pre[mi].*re$|^premiere$|^premie|^1er/', $lower)) {
            return '1ere';
        }
        // "Terminale" → peut devenir "terminale" ou "termina"
        if (preg_match('/^term/', $lower)) {
            return 'terminale';
        }
        // BAC
        if (preg_match('/^bac/', $lower)) {
            return 'bac';
        }

        // Fallback : retourner la version nettoyée et minuscule
        return $lower;
    }
}

// URL helper: build canonical site URLs using the router index.php?page=..
if (!function_exists('site_url')) {
    /**
     * Build a canonical site URL using the router. Example:
     *   site_url('cours', ['niveau' => 'seconde']) -> <baseUrl>/public/index.php?page=cours&niveau=seconde
     * Accepts either string page (e.g. 'college/6eme/exercices-6eme') or array for path segments.
     */
    function site_url($page = '', array $query = [])
    {
        global $baseUrl;
        $base = isset($baseUrl) ? rtrim($baseUrl, '/') : '';

        // Si baseUrl n'est pas défini, essayer de le détecter
        if (empty($base) && function_exists('detectBaseUrl')) {
            $base = rtrim(detectBaseUrl(), '/');
        }

        // allow $page to be array (segments)
        if (is_array($page)) {
            $page = implode('/', $page);
        }
        // default landing page
        if ($page === '' || $page === 'index') {
            $page = 'landingpage';
        }
        // Normaliser les slashes : supprimer les doubles slashes et les slashes en début/fin
        $page = trim($page, '/');
        $page = preg_replace('#/+#', '/', $page);
        $qs = '';
        if (!empty($query)) {
            $qs = '&' . http_build_query($query);
        }

        // Pointer vers la racine (index.php) et laisser .htaccess router vers public/index.php
        // Correction : ne pas encoder les slashes pour que le routeur fonctionne
        return $base . '/index.php?page=' . $page . $qs;
    }
}

// Helper pour générer des URLs d'assets (CSS, JS, images)
if (!function_exists('asset_url')) {
    function asset_url($path)
    {
        global $baseUrl;
        $path = ltrim($path, '/');
        $base = isset($baseUrl) ? rtrim($baseUrl, '/') : '';

        $query = '';
        if (strpos($path, '?') !== false) {
            $parts = explode('?', $path, 2);
            $path = $parts[0];
            $query = $parts[1] ?? '';
        }

        // Si baseUrl n'est pas défini, essayer de le détecter
        if (empty($base) && function_exists('detectBaseUrl')) {
            $base = rtrim(detectBaseUrl(), '/');
        }

        // En local, base peut être '/moncoachscolaire/public', donc asset_url doit produire
        // '/moncoachscolaire/public/assets/css/style.css' (pas '/moncoachscolaire/public/public/assets/css/style.css').
        // En prod, base peut être '', donc on renvoie '/assets/css/style.css'.
        $url = ($base === '') ? ('/' . $path) : ($base . '/' . $path);

        // Cache-busting: ajoute ?v=<filemtime> pour les assets locaux.
        $projectRoot = dirname(__DIR__, 2);
        $assetFile = $projectRoot . '/public/' . str_replace('\\', '/', $path);
        if (!is_file($assetFile)) {
            $assetFile = $projectRoot . '/' . str_replace('\\', '/', $path);
        }

        $params = [];
        if ($query !== '') {
            parse_str($query, $params);
        }
        if (is_file($assetFile) && !isset($params['v'])) {
            $params['v'] = (string) filemtime($assetFile);
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }
}

// Helper pour générer des URLs absolues complètes
if (!function_exists('absolute_url')) {
    function absolute_url($path = '')
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        if (strpos($path, 'index.php') !== false || strpos($path, '?page=') !== false) {
            return $protocol . '://' . $host . $path;
        } elseif (strpos($path, '/') === 0) {
            return $protocol . '://' . $host . $path;
        } elseif (strpos($path, 'assets/') === 0 || strpos($path, 'api/') === 0) {
            return $protocol . '://' . $host . asset_url($path);
        } else {
            return $protocol . '://' . $host . site_url($path);
        }
    }
}

/**
 * Génère la navigation entre niveaux pour les pages d'exercices, cours et remédiation
 * @param string $level Le niveau actuel (ex: '6ème', 'Seconde', etc.)
 * @param string $page_type Type de page: 'exercices', 'cours', ou 'guide-remediation'
 * @return string HTML de la navigation
 */
if (!function_exists('render_level_navigation')) {
    function render_level_navigation($level, $page_type = 'exercices')
    {
        // Mapping des niveaux et leurs relations
        $levels_config = [
            '6ème' => [
                'prev' => null,
                'next' => '5ème',
                'accueil' => 'college',
                'accueil_url' => 'college-accueil',
                'accueil_label' => 'Collège',
            ],
            '5ème' => [
                'prev' => '6ème',
                'next' => '4ème',
                'accueil' => 'college',
                'accueil_url' => 'college-accueil',
                'accueil_label' => 'Collège',
            ],
            '4ème' => [
                'prev' => '5ème',
                'next' => '3ème',
                'accueil' => 'college',
                'accueil_url' => 'college-accueil',
                'accueil_label' => 'Collège',
            ],
            '3ème' => [
                'prev' => '4ème',
                'next' => 'Seconde',
                'accueil' => 'college',
                'accueil_url' => 'college-accueil',
                'accueil_label' => 'Collège',
            ],
            'Seconde' => [
                'prev' => '3ème',
                'next' => 'Première',
                'accueil' => 'lycee',
                'accueil_url' => 'lycee-accueil',
                'accueil_label' => 'Lycée',
            ],
            'Première' => [
                'prev' => 'Seconde',
                'next' => 'Terminale',
                'accueil' => 'lycee',
                'accueil_url' => 'lycee-accueil',
                'accueil_label' => 'Lycée',
            ],
            'Terminale' => [
                'prev' => 'Première',
                'next' => 'BAC',
                'accueil' => 'lycee',
                'accueil_url' => 'lycee-accueil',
                'accueil_label' => 'Lycée',
            ],
            'BAC' => [
                'prev' => 'Terminale',
                'next' => null,
                'accueil' => 'bac',
                'accueil_url' => 'bac-accueil',
                'accueil_label' => 'BAC',
            ],
        ];

        $config = $levels_config[$level] ?? null;
        if (!$config) {
            return '';
        }

        $current_normalized = normalize_level_for_url($level);
        // Pour BAC, ne pas normaliser car les URLs sont différentes
        if ($level === 'BAC') {
            $prev_normalized = $config['prev'] ? normalize_level_for_url($config['prev']) : null;
            $next_normalized = null; // BAC n'a pas de niveau suivant
        } else {
            $prev_normalized = $config['prev'] ? normalize_level_for_url($config['prev']) : null;
            $next_normalized = $config['next'] ? normalize_level_for_url($config['next']) : null;
        }

        // Construire les URLs selon le type de page
        $prev_url = null;
        $next_url = null;
        $accueil_url = site_url($config['accueil'] . '/' . $config['accueil_url']);

        if ($config['prev']) {
            // Déterminer si le niveau précédent est au collège, au lycée ou au BAC
            $college_levels = ['6ème', '5ème', '4ème', '3ème'];
            $lycee_levels = ['Seconde', 'Première', 'Terminale'];

            if ($config['prev'] === 'BAC') {
                // Le niveau précédent est le BAC
                if ($page_type === 'exercices') {
                    $prev_url = site_url('bac/exercices-bac');
                } elseif ($page_type === 'guide-remediation') {
                    $prev_url = site_url('bac/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $prev_url = site_url('cours', ['niveau' => 'bac']);
                }
            } elseif (in_array($config['prev'], $college_levels)) {
                // Le niveau précédent est au COLLÈGE
                if ($page_type === 'exercices') {
                    $prev_url = site_url('college/' . $prev_normalized . '/exercices-' . $prev_normalized);
                } elseif ($page_type === 'guide-remediation') {
                    $prev_url = site_url('college/' . $prev_normalized . '/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $prev_url = site_url('cours', ['niveau' => $prev_normalized]);
                }
            } elseif (in_array($config['prev'], $lycee_levels)) {
                // Le niveau précédent est au LYCÉE (y compris Terminale pour BAC)
                if ($page_type === 'exercices') {
                    $prev_url = site_url('lycee/' . $prev_normalized . '/exercices-' . $prev_normalized);
                } elseif ($page_type === 'guide-remediation') {
                    $prev_url = site_url('lycee/' . $prev_normalized . '/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $prev_url = site_url('cours', ['niveau' => $prev_normalized]);
                }
            }
        }

        if ($config['next']) {
            // Déterminer si le niveau suivant est au collège, au lycée ou au BAC
            $college_levels = ['6ème', '5ème', '4ème', '3ème'];
            $lycee_levels = ['Seconde', 'Première', 'Terminale'];

            if ($config['next'] === 'BAC') {
                // Le niveau suivant est le BAC
                if ($page_type === 'exercices') {
                    $next_url = site_url('bac/exercices-bac');
                } elseif ($page_type === 'guide-remediation') {
                    $next_url = site_url('bac/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $next_url = site_url('cours', ['niveau' => 'bac']);
                }
            } elseif (in_array($config['next'], $college_levels)) {
                // Le niveau suivant est au COLLÈGE
                if ($page_type === 'exercices') {
                    $next_url = site_url('college/' . $next_normalized . '/exercices-' . $next_normalized);
                } elseif ($page_type === 'guide-remediation') {
                    $next_url = site_url('college/' . $next_normalized . '/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $next_url = site_url('cours', ['niveau' => $next_normalized]);
                }
            } elseif (in_array($config['next'], $lycee_levels)) {
                // Le niveau suivant est au LYCÉE
                if ($page_type === 'exercices') {
                    $next_url = site_url('lycee/' . $next_normalized . '/exercices-' . $next_normalized);
                } elseif ($page_type === 'guide-remediation') {
                    $next_url = site_url('lycee/' . $next_normalized . '/guide-remediation');
                } elseif ($page_type === 'cours') {
                    $next_url = site_url('cours', ['niveau' => $next_normalized]);
                }
            }
        }

        // HTML de navigation
        ob_start();
        ?>
        <div class="exercise-navigation">
            <div class="nav-group">
                <?php if ($prev_url): ?>
                    <a href="<?php echo htmlspecialchars($prev_url); ?>" class="nav-btn nav-prev">◀️ <?php echo htmlspecialchars($config['prev']); ?></a>
                    <span><?php echo htmlspecialchars($config['accueil_label']); ?></span>
                <?php else: ?>
                    <span>Premier niveau</span>
                <?php endif; ?>
            </div>
            <div class="nav-group">
                <a href="<?php echo htmlspecialchars($accueil_url); ?>" class="nav-btn">🏠 Accueil <?php echo htmlspecialchars($config['accueil_label']); ?></a>
            </div>
            <div class="nav-group">
                <?php if ($next_url): ?>
                    <?php if ($config['next'] === 'BAC'): ?>
                        <span>Vers le BAC :</span>
                    <?php else: ?>
                        <span>Niveau suivant :</span>
                    <?php endif; ?>
                    <a href="<?php echo htmlspecialchars($next_url); ?>" class="nav-btn nav-next">▶️ <?php echo htmlspecialchars($config['next']); ?></a>
                <?php else: ?>
                    <span>Niveau terminal</span>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}


