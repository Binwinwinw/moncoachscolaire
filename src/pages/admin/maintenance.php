<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
/**
 * Page de Maintenance - MonCoachScolaire
 * Affichée lorsque le mode maintenance est activé
 */
$page_title = 'Maintenance en cours - MonCoachScolaire';
$page_css = 'maintenance.css';
// Charger la configuration
if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}
// Lire le message de maintenance personnalisé
$maintenanceFile = __DIR__ . '/../../.maintenance.json';  // Racine du projet
$maintenanceMessage = 'Le site est actuellement en maintenance. Nous serons de retour bientôt !';
$maintenanceActivatedAt = null;
if (file_exists($maintenanceFile)) {
    $maintenanceData = json_decode(file_get_contents($maintenanceFile), true);
    if ($maintenanceData) {
        $maintenanceMessage = $maintenanceData['message'] ?? $maintenanceMessage;
        $maintenanceActivatedAt = $maintenanceData['activated_at'] ?? null;
    }
}
// Si accès direct, charger le head
$direct_access = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));
if ($direct_access) {
    if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
        require_once dirname(__DIR__, 2) . '/config/site_boot.php';
    } elseif (is_file(__DIR__ . '/bootstrap/site_boot.php')) {
        require_once __DIR__ . '/bootstrap/site_boot.php';
    }
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
    // Utiliser asset_url() si disponible
    if (function_exists('asset_url')) {
        $cssStyle = asset_url('assets/css/tailwind.css');
        $cssPage = asset_url('assets/css/pages/maintenance.css');
    } else {
        $cssStyle = ($root !== '' ? $root : '') . '/assets/css/tailwind.css';
        $cssPage = ($root !== '' ? $root : '') . '/assets/css/pages/maintenance.css';
    }
    ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($cssStyle, ENT_QUOTES); ?>">
        <link rel="stylesheet" href="<?php echo htmlspecialchars($cssPage, ENT_QUOTES); ?>">
    </head>
    <body class="maintenance-page app-bg">
    <?php
    if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) {
        include_once dirname(__DIR__, 2) . '/includes/topbar.php';
    }
}
?>

<main class="maintenance-container">
    <div class="maintenance-content">
        <div class="maintenance-icon">
            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="maintenance-title">Maintenance en cours</h1>

        <p class="maintenance-message">
            <?php echo htmlspecialchars($maintenanceMessage); ?>
        </p>

        <?php if ($maintenanceActivatedAt): ?>
        <p class="maintenance-time">
            <small>Maintenance activée le <?php echo htmlspecialchars($maintenanceActivatedAt); ?></small>
        </p>
        <?php endif; ?>

        <div class="maintenance-info">
            <p>Nous travaillons actuellement sur des améliorations pour vous offrir une meilleure expérience.</p>
            <p>Merci de votre patience !</p>
        </div>

        <div class="maintenance-actions">
            <a href="<?php echo site_url('landingpage'); ?>" class="btn-maintenance">
                Retour à l'accueil
            </a>
        </div>
    </div>
</main>

<?php

if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) {
    include_once dirname(__DIR__, 2) . '/includes/footer.php';
}
?>

</body>
</html>
