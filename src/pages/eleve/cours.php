<?php
/**
 * cours.php - Hub centralisé des cours (collège, lycée, bac)
 * Thème : livre ouvert, fond subtil, responsive Tailwind
 * Auteur : Copilot/Assistance - Création 20/02/2026
 */

// Protection session et config
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/level_access.php';

$page_title = 'Tous les cours - MonCoachScolaire';
$page_css = 'cours.css';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/style.css') : '/public/assets/css/style.css'; ?>">
    <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/' . $page_css) : '/public/assets/css/pages/' . htmlspecialchars($page_css, ENT_QUOTES); ?>">
    <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/exercices.css') : '/public/assets/css/pages/exercices.css'; ?>">
    <!-- DEBUG: Patch refonte cours.php appliqué, CSS exercices.css chargé -->
    <script>window.baseUrl = "/";</script>
</head>
<body class="bg-gradient-to-b from-amber-50 to-white min-h-screen flex flex-col">
<?php if (empty($GLOBALS['__topbar_included']) && is_file(__DIR__ . '/../../includes/topbar.php')) {
    include_once __DIR__ . '/../../includes/topbar.php';
} ?>


<main class="main-content page-exercices flex-1 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto flex w-full max-w-6xl flex-col">
        <header class="mb-8 rounded-[2rem] border border-sky-200/70 bg-white/85 p-8 text-center shadow-[0_25px_70px_-28px_rgba(59,130,246,0.3)] backdrop-blur">
            <p class="mb-3 inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-sm font-semibold uppercase tracking-[0.2em] text-sky-700">Parcours d’apprentissage</p>
            <h1 class="mb-3 text-3xl font-extrabold tracking-tight text-sky-900 md:text-5xl">📖 Tous les cours par niveau</h1>
            <p class="mx-auto max-w-3xl text-lg font-medium text-slate-600">Découvre les cours du collège, du lycée et du BAC sur une seule page, avec une navigation plus claire et plus rassurante.</p>
        </header>

        <?php if (!isset($_SESSION['user_id'])): ?>
        <section class="mb-10 rounded-[1.75rem] border border-sky-200/70 bg-white/85 p-6 text-center shadow-sm backdrop-blur">
            <h2 class="mb-2 flex items-center justify-center gap-2 text-xl font-semibold text-sky-700">👀 Accès visiteur limité</h2>
            <p class="mb-6 text-slate-600">Pour accéder à tous les cours, crée ton compte gratuitement et débloque un parcours plus fluide.</p>
            <div class="mb-6 grid grid-cols-1 gap-5 md:grid-cols-3">
                <!-- Collège -->
                <a href="<?php echo site_url('college/cours-college'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-2xl border border-sky-200 bg-sky-50/80 p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-300">
                    <span class="mb-2 text-3xl">📗</span>
                    <span class="mb-1 text-lg font-bold text-sky-900">Collège</span>
                    <span class="text-sm text-slate-500">6e à 3e</span>
                </a>
                <!-- Lycée -->
                <a href="<?php echo site_url('lycee/cours-lycee'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-2xl border border-violet-200 bg-violet-50/80 p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-violet-300">
                    <span class="mb-2 text-3xl">📙</span>
                    <span class="mb-1 text-lg font-bold text-violet-900">Lycée</span>
                    <span class="text-sm text-slate-500">2nde à Terminale</span>
                </a>
                <!-- BAC -->
                <a href="<?php echo site_url('bac/cours-bac'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-2xl border border-amber-200 bg-amber-50/80 p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-300">
                    <span class="mb-2 text-3xl">🏅</span>
                    <span class="mb-1 text-lg font-bold text-amber-900">BAC</span>
                    <span class="text-sm text-slate-500">Cours & révisions BAC</span>
                </a>
            </div>
            <a href="<?php echo site_url('register'); ?>" class="coach-cta inline-flex items-center justify-center rounded-xl bg-sky-600 px-7 py-2.5 text-base font-semibold text-white shadow-sm transition hover:bg-sky-700">Créer un compte gratuit</a>
        </section>
        <?php else: ?>
        <div class="relative z-10 grid w-full grid-cols-1 gap-7 md:grid-cols-3">
            <!-- Collège -->
            <a href="<?php echo site_url('college/cours-college'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-2xl border border-sky-200 bg-sky-50/80 p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-300">
                <span class="mb-2 text-3xl">📗</span>
                <span class="mb-1 text-lg font-bold text-sky-900">Collège</span>
                <span class="text-sm text-slate-500">6e à 3e</span>
            </a>
            <!-- Lycée -->
            <a href="<?php echo site_url('lycee/cours-lycee'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-2xl border border-violet-200 bg-violet-50/80 p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-violet-300">
                <span class="mb-2 text-3xl">📙</span>
                <span class="mb-1 text-lg font-bold text-violet-900">Lycée</span>
                <span class="text-sm text-slate-500">2nde à Terminale</span>
            </a>
            <!-- BAC -->
            <a href="<?php echo site_url('bac/cours-bac'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-2xl border border-amber-200 bg-amber-50/80 p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-300">
                <span class="mb-2 text-3xl">🏅</span>
                <span class="mb-1 text-lg font-bold text-amber-900">BAC</span>
                <span class="text-sm text-slate-500">Cours & révisions BAC</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</main>
<?php if (is_file(__DIR__ . '/../../includes/footer.php')) {
    include_once __DIR__ . '/../../includes/footer.php';
} ?>
</body>
</html>
