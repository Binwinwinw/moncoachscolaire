<?php
/**
 * Page de Contact - MonCoachScolaire
 * Styles externes : assets/css/pages/contact.css
 */

$hide_site_header = true;
$hide_skip_link = true;
$page_title = 'Contact - MonCoachScolaire';
$page_css = 'contact.css';

// Charger site_boot si ce fichier est accédé directement
if (!function_exists('site_url')) {
    if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
        require_once dirname(__DIR__, 2) . '/config/site_boot.php';
    } elseif (is_file(__DIR__ . '/../config/site_boot.php')) {
        require_once __DIR__ . '/../config/site_boot.php';
    }
}

if (is_file(dirname(__DIR__, 2) . '/includes/login_security.php')) {
    require_once dirname(__DIR__, 2) . '/includes/login_security.php';
}

$csrfToken = function_exists('generateCSRFToken')
    ? generateCSRFToken()
    : ((string) ($_SESSION['csrf_token'] ?? ''));
?>

<div class="min-h-screen bg-slate-50 px-4 py-10 sm:px-6 lg:px-8">
  <main class="mx-auto flex max-w-5xl flex-col gap-8 lg:flex-row lg:items-start">
    <section class="flex-1 rounded-3xl border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-200/70 backdrop-blur-sm">
      <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-700">Support</p>
      <h1 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Contactez-nous</h1>
      <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Une question sur un exercice, un besoin d’aide ou un retour à partager ? Nous vous répondons rapidement.</p>

      <?php if (isset($_GET['success']) && (int) $_GET['success'] === 1): ?>
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status" aria-live="polite">
          Votre message a bien été envoyé.
        </div>
      <?php elseif (isset($_GET['error'])): ?>
        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" role="alert" aria-live="assertive">
          Une erreur est survenue. Merci de réessayer.
        </div>
      <?php endif; ?>

      <form class="mt-8 space-y-6" method="post" action="<?php echo site_url('users/send_contact'); ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="grid gap-6 md:grid-cols-2">
          <div>
            <label for="nom" class="mb-2 block text-sm font-semibold text-slate-700">👤 Nom</label>
            <input type="text" id="nom" name="nom" required placeholder="Votre nom" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
          </div>
          <div>
            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">📧 Email</label>
            <input type="email" id="email" name="email" required placeholder="votre.email@exemple.com" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
          </div>
        </div>

        <div>
          <label for="message" class="mb-2 block text-sm font-semibold text-slate-700">💬 Message</label>
          <textarea id="message" name="message" rows="6" required placeholder="Votre message..." class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm transition focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"></textarea>
        </div>

        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
          <span class="mr-2">📤</span>
          Envoyer
        </button>
      </form>
    </section>

    <aside class="w-full max-w-md rounded-3xl border border-slate-200 bg-slate-900 p-7 text-white shadow-lg shadow-slate-300/70">
      <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-300">Besoin d’un coup de pouce ?</p>
      <h2 class="mt-3 text-2xl font-semibold">Nous sommes là pour vous aider</h2>
      <p class="mt-3 text-sm leading-7 text-slate-300">Le support répond généralement en quelques heures pour les questions liées au compte, aux exercices ou au suivi de progression.</p>
      <div class="mt-6 space-y-3 text-sm text-slate-200">
        <div class="rounded-2xl border border-white/10 bg-white/10 p-3">📚 Aide sur les exercices et notions</div>
        <div class="rounded-2xl border border-white/10 bg-white/10 p-3">🧠 Suivi de progression et conseils</div>
        <div class="rounded-2xl border border-white/10 bg-white/10 p-3">🔐 Questions liées au compte</div>
      </div>
    </aside>
  </main>
</div>
