<?php
// Préparer variables d'état pour éviter les warnings
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
}
$is_authenticated = isset($is_authenticated) ? $is_authenticated : (!empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']));
$is_demo_account = isset($is_demo_account) ? $is_demo_account : (!empty($_SESSION['is_demo']));
// Helpers de niveau
if (!function_exists('normalize_school_level')) {
    $level_file = dirname(__DIR__) . '/includes/level_normalization.php';
    if (file_exists($level_file)) {
        require_once $level_file;
    }
}
$user_level_raw = $_SESSION['user_level'] ?? '';
$user_role = $_SESSION['user_role'] ?? '';
$user_level_normalized = function_exists('normalize_school_level') ? normalize_school_level($user_level_raw) : $user_level_raw;
$is_admin = in_array($user_role, ['admin', 'administrator'], true);
$is_terminal = function_exists('levels_match') ? (levels_match($user_level_normalized, 'Terminale') || levels_match($user_level_normalized, 'BAC')) : ($user_level_normalized === 'Terminale' || $user_level_normalized === 'BAC');
$show_bac_link = $is_admin || $is_terminal;
?>
    <?php
        // Utiliser le composant Footer réutilisable (présentation uniquement)
        // Le helper get_exercices_url_from_session s'occupe du calcul d'URL si nécessaire
        if (!function_exists('get_exercices_url_from_session')) {
            require_once dirname(__DIR__) . '/includes/footer_helpers.php';
        }
require_once dirname(__DIR__) . '/components/footer_component.php';
?>

    <!-- Scripts -->
    <?php
    if (function_exists('asset_url')) {
        $footerScriptPath = asset_url('assets/js/footer-animations.js');
    } else {
        // Fallback projet historique si asset_url n'est pas disponible
        if (isset($basePath)) {
            $footerScriptPath = $basePath . '/public/assets/js/footer-animations.js';
        } else {
            $scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php');
            $basePath = preg_replace('#/public$#', '', $scriptPath);
            $footerScriptPath = $basePath . '/public/assets/js/footer-animations.js';
        }
    }
?>
    <script src="<?php echo htmlspecialchars($footerScriptPath, ENT_QUOTES); ?>" defer></script>

    <!-- Coach WebM - Chargé automatiquement par dashboard et pages d'exercices/quiz -->

    <!-- Système de déconnexion automatique basé sur l'inactivité -->
    <?php
// Charger le système de timeout uniquement pour les utilisateurs authentifiés (pas pour les visiteurs)
// Exclure le mode démo car il n'a pas besoin de déconnexion automatique
if ($is_authenticated && !$is_demo_account):
    // Déterminer basePath si non défini
    if (!isset($basePath)) {
        $scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php');
        $basePath = preg_replace('#/public$#', '', $scriptPath);
    }
    if (function_exists('asset_url')) {
        $sessionTimeoutCssPath = asset_url('assets/css/session-timeout.css');
        $sessionTimeoutJsPath = asset_url('assets/js/session-timeout.js');
    } else {
        $sessionTimeoutCssPath = $basePath . '/public/assets/css/session-timeout.css';
        $sessionTimeoutJsPath = $basePath . '/public/assets/js/session-timeout.js';
    }
    ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($sessionTimeoutCssPath, ENT_QUOTES); ?>">
        <script>
            // Définir l'ID utilisateur pour le système de timeout
            (function() {
                const userId = <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>;
                if (document.body) {
                    if(document.body){document.body.dataset.userId = userId;}
                    return;
                }
                document.addEventListener('DOMContentLoaded', function() {
                    if (document.body) {
                        if(document.body){document.body.dataset.userId = userId;}
                    }
                });
            })();
        </script>
        <script src="<?php echo htmlspecialchars($sessionTimeoutJsPath, ENT_QUOTES); ?>"></script>
    <?php endif; ?>
</body>
</html>
