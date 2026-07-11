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
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
  <main class="mx-auto max-w-5xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-700">Administration</p>
    <h1 class="mt-3 text-3xl font-semibold text-slate-900">Monitoring sécurité</h1>
    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Vue de surveillance pour les tentatives de connexion, les alertes et les événements sensibles.</p>

    <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-6">
      <h2 class="text-lg font-semibold text-amber-800">Alertes récentes</h2>
      <ul class="mt-4 space-y-3 text-sm text-amber-900">
        <li class="rounded-xl border border-amber-200 bg-white/70 px-4 py-3">Aucune alerte critique enregistrée pour le moment.</li>
        <li class="rounded-xl border border-amber-200 bg-white/70 px-4 py-3">Les logs de connexion et les tentatives excessives pourront être suivis ici.</li>
      </ul>
    </div>
  </main>
</div>
