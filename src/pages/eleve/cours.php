<?php
/**
 * cours.php - Hub centralisé des cours (collège, lycée, bac)
 * Thème : livre ouvert, fond subtil, responsive Tailwind
 * Auteur : Copilot/Assistance - Création 20/02/2026
 */

// Protection session et config
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/level_access.php';
require_once __DIR__ . '/../../includes/topbar.php';

$page_title = 'Tous les cours - MonCoachScolaire';
$page_css = 'cours.css';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="/public/assets/css/pages/<?php echo $page_css; ?>">
    <link rel="stylesheet" href="/public/assets/css/pages/exercices.css">
    <!-- DEBUG: Patch refonte cours.php appliqué, CSS exercices.css chargé -->
    <script>window.baseUrl = "/";</script>
</head>
<body class="bg-gradient-to-b from-amber-50 to-white min-h-screen flex flex-col">
<?php include_once __DIR__ . '/../../includes/topbar.php'; ?>


<main class="main-content page-exercices flex-1 flex flex-col items-center justify-center py-10 px-4 bg-blue-50">
    <div class="max-w-3xl w-full mx-auto">
        <header class="header text-center mb-10 p-8 bg-white border border-blue-100 rounded-2xl shadow-sm">
            <h1 class="text-3xl md:text-5xl font-extrabold text-blue-800 mb-2 tracking-tight font-[Poppins,ui-sans-serif]">📖 Tous les cours par niveau</h1>
            <p class="subtitle text-lg text-slate-600 mb-0 font-medium">Découvre tous les cours de collège, lycée et BAC sur une seule page.</p>
        </header>

        <?php if (!isset($_SESSION['user_id'])): ?>
        <section class="coach-preview bg-white/80 border border-blue-100 rounded-xl p-6 text-center shadow-sm mb-10">
            <h2 class="text-xl font-semibold text-blue-700 mb-2 flex items-center justify-center gap-2">👀 Accès visiteur limité</h2>
            <p class="text-slate-600 mb-5">Pour accéder à tous les cours, crée ton compte gratuitement !</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <!-- Collège -->
                <a href="<?php echo site_url('college/cours-college'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-lg border border-blue-100 bg-blue-50/60 shadow-sm p-5 transition hover:shadow-md hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <span class="text-3xl mb-1">📗</span>
                    <span class="font-bold text-lg mb-0.5">Collège</span>
                    <span class="text-sm text-slate-500">6e à 3e</span>
                </a>
                <!-- Lycée -->
                <a href="<?php echo site_url('lycee/cours-lycee'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-lg border border-blue-100 bg-blue-50/60 shadow-sm p-5 transition hover:shadow-md hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <span class="text-3xl mb-1">📙</span>
                    <span class="font-bold text-lg mb-0.5">Lycée</span>
                    <span class="text-sm text-slate-500">2nde à Terminale</span>
                </a>
                <!-- BAC -->
                <a href="<?php echo site_url('bac/cours-bac'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-lg border border-blue-100 bg-blue-50/60 shadow-sm p-5 transition hover:shadow-md hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <span class="text-3xl mb-1">🏅</span>
                    <span class="font-bold text-lg mb-0.5">BAC</span>
                    <span class="text-sm text-slate-500">Cours & révisions BAC</span>
                </a>
            </div>
            <a href="<?php echo site_url('register'); ?>" class="coach-cta inline-block mt-1 px-7 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm text-base transition">Créer un compte gratuit</a>
        </section>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-7 w-full relative z-10">
            <!-- Collège -->
            <a href="<?php echo site_url('college/cours-college'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-lg border border-blue-100 bg-blue-50/60 shadow-sm p-5 transition hover:shadow-md hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-300">
                <span class="text-3xl mb-1">📗</span>
                <span class="font-bold text-lg mb-0.5">Collège</span>
                <span class="text-sm text-slate-500">6e à 3e</span>
            </a>
            <!-- Lycée -->
            <a href="<?php echo site_url('lycee/cours-lycee'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-lg border border-blue-100 bg-blue-50/60 shadow-sm p-5 transition hover:shadow-md hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-300">
                <span class="text-3xl mb-1">📙</span>
                <span class="font-bold text-lg mb-0.5">Lycée</span>
                <span class="text-sm text-slate-500">2nde à Terminale</span>
            </a>
            <!-- BAC -->
            <a href="<?php echo site_url('bac/cours-bac'); ?>" class="niveau-card flex flex-col items-center justify-center rounded-lg border border-blue-100 bg-blue-50/60 shadow-sm p-5 transition hover:shadow-md hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-blue-300">
                <span class="text-3xl mb-1">🏅</span>
                <span class="font-bold text-lg mb-0.5">BAC</span>
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
