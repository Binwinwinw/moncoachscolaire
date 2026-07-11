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
<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
  <main class="mx-auto max-w-5xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-700">Administration</p>
    <h1 class="mt-3 text-3xl font-semibold text-slate-900">Gestion des notifications</h1>
    <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">Interface minimale pour préparer la gestion des templates et du journal des notifications côté administrateur.</p>

    <div class="mt-8 grid gap-4 md:grid-cols-2">
      <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <h2 class="text-lg font-semibold text-slate-800">À venir</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Templates, ciblage, historique et relances pourront être gérés depuis cette vue.</p>
      </div>
      <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <h2 class="text-lg font-semibold text-emerald-800">État</h2>
        <p class="mt-2 text-sm leading-6 text-emerald-700">La structure de la page est maintenant alignée avec le système Tailwind du reste de l’application.</p>
      </div>
    </div>
  </main>
</div>
