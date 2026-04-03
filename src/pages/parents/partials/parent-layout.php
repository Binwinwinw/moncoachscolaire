<?php
// Inclusion du header global (balises <head> et <body>)
require_once dirname(__DIR__, 3) . '/includes/header.php';
// Inclusion explicite de la topbar globale
require_once dirname(__DIR__, 3) . '/includes/topbar.php';
$parent_bg_img = function_exists('asset_url') ? asset_url('assets/img/background_school_material.webp') : '/assets/img/background_school_material.webp';
?>
<div class="min-h-screen bg-cover bg-center bg-no-repeat bg-fixed relative" style="background-color:transparent!important; background-image: url('<?= htmlspecialchars($parent_bg_img, ENT_QUOTES) ?>');">
    <main class="max-w-5xl mx-auto px-4 md:px-0 pb-16 font-sans">
        <?php include __DIR__ . '/../components/hero-onboarding.php'; ?>
        <?php include __DIR__ . '/../components/child-grid.php'; ?>
        <?php include __DIR__ . '/../components/family-invite.php'; ?>
        <?php include __DIR__ . '/../components/stats-widgets.php'; ?>
        <?php include __DIR__ . '/../components/notifications-feed.php'; ?>
    </main>
</div>
<style>
body, .font-sans {
    font-family: 'Inter', 'Roboto', Arial, sans-serif;
    font-size: 1rem;
    color: #222;
}
h1, h2, h3, h4 {
    font-family: 'Inter', 'Roboto', Arial, sans-serif;
    font-weight: 700;
    letter-spacing: -0.01em;
}
/* Rendre le background du contenu principal quasi transparent pour laisser apparaître l'image de fond */
/* Suppression du dégradé pour laisser l'image de fond visible */
.app-bg, .bg-gradient-to-b {
    background: transparent !important;
}
</style>
<?php
// Inclusion du footer global
require_once dirname(__DIR__, 3) . '/includes/footer.php';
?>
