<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
// Start session robustly
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
// Charger admin_auth de façon robuste
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
    requireAdmin();
} else {
    require_once __DIR__ . '/../../../includes/admin_auth.php';
    requireAdmin();
}
?>
<main>
  <h1>Reporting personnalisé</h1>
  <p>Générateur de rapports et planification — interface minimale.</p>
</main>
