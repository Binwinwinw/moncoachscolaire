<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
require_once __DIR__ . '/../../../includes/admin_auth.php';
requireAdmin();
?>
<main>
  <h1>Gestion des notifications</h1>
  <p>Interface minimale: gestion des templates et journal des notifications (à implémenter).</p>
</main>
