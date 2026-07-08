<?php

/**
 * Système de "guards" pour les pages - VERSION CORRIGÉE
 * Les paramètres validés sont stockés dans $GLOBALS['validated_params']
 */

if (!defined('PAGE_GUARDS_LOADED')) {
    define('PAGE_GUARDS_LOADED', true);

    // Charger les helpers de redirection
    if (!defined('REDIRECT_HELPERS_LOADED')) {
        require_once __DIR__ . '/redirect_helpers.php';
    }

    /**
     * Configuration des guards par page
     */
    function get_page_guards_config()
    {
        return [
            // Pages parents
            'parents/dashboard_parent' => [
                'guards' => ['auth', 'parent'],
                'redirect_on_fail' => 'login',
            ],
            'parents/suivi_enfant' => [
                'guards' => ['auth', 'parent'],
                'params' => ['id' => 'int'],
                'redirect_on_fail' => 'parents/dashboard_parent',
            ],
            'parents/suivi_abo' => [
                'guards' => ['auth', 'parent'],
                'redirect_on_fail' => 'parents/dashboard_parent',
            ],
            // Pages élèves
            'eleve/dashboard' => [
                'guards' => ['auth', 'student'],
                'redirect_on_fail' => 'login',
            ],
            'exercices' => [
                'guards' => ['auth', 'student'],
                'redirect_on_fail' => 'login',
            ],
            // Pages admin
            'admin/dashboard_admin' => [
                'guards' => ['auth', 'admin'],
                'redirect_on_fail' => 'login',
            ],
            'admin/users' => [
                'guards' => ['auth', 'admin'],
                'redirect_on_fail' => 'login',
            ],
            // Pages publiques (rediriger si déjà connecté)
            'login' => [
                'guards' => ['guest'],
                'redirect_on_fail' => 'eleve/dashboard',
            ],
            'register' => [
                'guards' => ['guest'],
                'redirect_on_fail' => 'eleve/dashboard',
            ],
            // Pages accessibles à tous
            'landingpage' => [],
            'cgv' => [],
            'mentions-legales' => [],
        ];
    }

    /**
     * Exécute les guards pour une page donnée
     * Les paramètres validés sont stockés dans $GLOBALS['validated_params']
     */
    function execute_page_guards($page_path, $pdo = null)
    {
        $config = get_page_guards_config();
        // Initialiser le tableau des paramètres validés
        $GLOBALS['validated_params'] = [];
        // Si la page n'a pas de config, elle est accessible à tous
        if (!isset($config[$page_path])) {
            return true;
        }
        $page_config = $config[$page_path];
        $guards = $page_config['guards'] ?? [];
        $params = $page_config['params'] ?? [];
        $redirect_on_fail = $page_config['redirect_on_fail'] ?? 'landingpage';
        // Vérifier les guards
        foreach ($guards as $guard) {
            switch ($guard) {
                case 'auth':
                    $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
                    $is_parent_logged_in = !empty($_SESSION['parent_id']);
                    if (!$is_logged_in && !$is_parent_logged_in) {
                        safe_redirect(site_url($redirect_on_fail));
                        return false;
                    }
                    break;
                case 'guest':
                    redirect_if_authenticated(site_url($redirect_on_fail));
                    break;
                case 'parent':
                    redirect_if_not_parent(site_url($redirect_on_fail));
                    break;
                case 'admin':
                    redirect_if_not_admin(site_url($redirect_on_fail));
                    break;
                case 'student':
                    $is_student = !empty($_SESSION['user_id'])
                                  && !empty($_SESSION['logged_in'])
                                  && empty($_SESSION['parent_id']);
                    $is_admin = function_exists('isAdmin') && isAdmin();
                    if (!$is_student && !$is_admin) {
                        safe_redirect(site_url($redirect_on_fail));
                        return false;
                    }
                    break;
                case 'database':
                    require_database($pdo, site_url($redirect_on_fail));
                    break;
            }
        }
        // Vérifier et valider les paramètres requis
        foreach ($params as $param_name => $param_type) {
            $validated_value = require_param($param_name, $param_type, site_url($redirect_on_fail));
            $GLOBALS['validated_params'][$param_name] = $validated_value;
        }
        return true;
    }

    /**
     * Récupère un paramètre validé par les guards
     * @param string $param_name Nom du paramètre
     * @param mixed $default Valeur par défaut si le paramètre n'existe pas
     * @return mixed La valeur validée ou la valeur par défaut
     */
    function get_validated_param($param_name, $default = null)
    {
        return $GLOBALS['validated_params'][$param_name] ?? $default;
    }

    /**
     * Permet à une page de définir ses propres guards dynamiquement
     */
    function apply_custom_guards($guards = [], $params = [], $redirect_on_fail = 'landingpage')
    {
        if (!isset($GLOBALS['validated_params'])) {
            $GLOBALS['validated_params'] = [];
        }
        foreach ($guards as $guard) {
            switch ($guard) {
                case 'auth':
                    redirect_if_not_authenticated(site_url($redirect_on_fail));
                    break;
                case 'parent':
                    redirect_if_not_parent(site_url($redirect_on_fail));
                    break;
                case 'admin':
                    redirect_if_not_admin(site_url($redirect_on_fail));
                    break;
                case 'guest':
                    redirect_if_authenticated(site_url($redirect_on_fail));
                    break;
            }
        }
        foreach ($params as $param_name => $param_type) {
            $validated_value = require_param($param_name, $param_type, site_url($redirect_on_fail));
            $GLOBALS['validated_params'][$param_name] = $validated_value;
        }
    }
}
