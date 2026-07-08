<?php

if (!function_exists('remediation_guide_bootstrap')) {
    /**
     * Charge config, thème et droits d'accès pour les guides de remédiation par niveau.
     *
     * @return array{is_admin: bool, has_access: bool, user_level: string, theme: array}
     */
    function remediation_guide_bootstrap(string $srcRoot, ?string $themeLevel = null): array
    {
        if (!isset($pdo) && is_file($srcRoot . '/config.php')) {
            require_once $srcRoot . '/config.php';
        }
        if (is_file($srcRoot . '/config/site_boot.php')) {
            require_once $srcRoot . '/config/site_boot.php';
        }
        if (is_file($srcRoot . '/includes/level_navigation.php')) {
            require_once $srcRoot . '/includes/level_navigation.php';
        }
        if (is_file($srcRoot . '/includes/admin_auth.php')) {
            require_once $srcRoot . '/includes/admin_auth.php';
        }

        $is_admin = function_exists('isAdmin') && isAdmin();
        $has_access = !empty($GLOBALS['is_logged_in']) || !empty($_SESSION['logged_in']) || !empty($_SESSION['user_id']) || $is_admin;

        $level = $themeLevel ?? ($_SESSION['user_level'] ?? 'college');

        return [
            'is_admin' => $is_admin,
            'has_access' => $has_access,
            'user_level' => $_SESSION['user_level'] ?? '',
            'user_name' => $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Élève',
            'theme' => get_theme_variant_by_level($level),
        ];
    }
}
