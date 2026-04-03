<?php

/**
 * Helpers de redirection pour éviter les erreurs "headers already sent"
 * Ces fonctions doivent être appelées AVANT toute sortie HTML
 */

if (!defined('REDIRECT_HELPERS_LOADED')) {
    define('REDIRECT_HELPERS_LOADED', true);

    /**
     * Effectue une redirection sécurisée
     * Nettoie le buffer de sortie si nécessaire
     */
    function safe_redirect($url, $status_code = 302)
    {
        // Nettoyer tous les buffers de sortie actifs
        while (ob_get_level()) {
            ob_end_clean();
        }
        // Vérifier si les headers ne sont pas déjà envoyés
        if (!headers_sent($file, $line)) {
            header("Location: $url", true, $status_code);
            exit;
        } else {
            // Fallback : redirection JavaScript si headers déjà envoyés
            error_log("Headers already sent in $file on line $line. Using JavaScript redirect.");
            echo '<script>window.location.href = "' . htmlspecialchars($url) . '";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"></noscript>';
            exit;
        }
    }

    function redirect_if_not_authenticated($redirect_to = null)
    {
        $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
        $is_parent_logged_in = !empty($_SESSION['parent_id']);
        if (!$is_logged_in && !$is_parent_logged_in) {
            $url = $redirect_to ?? site_url('login');
            safe_redirect($url);
        }
    }

    function redirect_if_not_parent($redirect_to = null)
    {
        $is_parent = !empty($_SESSION['parent_id']);
        $is_admin = false;
        if (function_exists('isAdmin')) {
            $is_admin = isAdmin();
        }
        if (!$is_parent && !$is_admin) {
            $url = $redirect_to ?? site_url('login');
            safe_redirect($url);
        }
    }

    function redirect_if_not_admin($redirect_to = null)
    {
        $is_admin = false;
        if (!function_exists('isAdmin')) {
            $admin_auth_path = dirname(__DIR__) . '/includes/admin_auth.php';
            if (file_exists($admin_auth_path)) {
                require_once $admin_auth_path;
            }
        }
        if (function_exists('isAdmin')) {
            $is_admin = isAdmin();
        }
        if (!$is_admin) {
            $url = $redirect_to ?? site_url('login');
            safe_redirect($url);
        }
    }

    function redirect_if_authenticated($redirect_to = null)
    {
        $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
        $is_parent_logged_in = !empty($_SESSION['parent_id']);
        if ($is_logged_in || $is_parent_logged_in) {
            if ($redirect_to === null) {
                if (function_exists('isAdmin') && isAdmin()) {
                    $redirect_to = site_url('admin/dashboard_admin');
                } elseif ($is_parent_logged_in) {
                    $redirect_to = site_url('parents/dashboard_parent');
                } else {
                    $redirect_to = site_url('eleve/dashboard');
                }
            }
            safe_redirect($redirect_to);
        }
    }

    function require_param($param_name, $type = 'string', $redirect_on_fail = null)
    {
        $value = $_GET[$param_name] ?? $_POST[$param_name] ?? null;
        if ($value === null) {
            if ($redirect_on_fail) {
                safe_redirect($redirect_on_fail);
            }
            return null;
        }
        switch ($type) {
            case 'int':
                if (!is_numeric($value)) {
                    if ($redirect_on_fail) {
                        safe_redirect($redirect_on_fail);
                    }
                    return null;
                }
                return (int) $value;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    if ($redirect_on_fail) {
                        safe_redirect($redirect_on_fail);
                    }
                    return null;
                }
                return $value;
            case 'string':
            default:
                return $value;
        }
    }

    function require_database($pdo, $redirect_on_fail = null)
    {
        if (!$pdo) {
            if ($redirect_on_fail) {
                safe_redirect($redirect_on_fail);
            }
            if (function_exists('should_show_db_notice') && should_show_db_notice()) {
                die('<div><h2>⚠️ Base de données indisponible</h2><p>Le service est temporairement indisponible. Veuillez réessayer dans quelques instants.</p></div>');
            } else {
                safe_redirect(site_url('landingpage'));
            }
        }
    }
}

