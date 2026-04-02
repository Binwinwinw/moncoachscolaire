<?php
// Template Tailwind pour pages légales (CGV, Confidentialité, Mentions légales)
$page_title = $page_title ?? 'Page légale - MonCoachScolaire';
$page_class = $page_class ?? 'legal-page';
$page_css = $page_css ?? null;
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="Page légale MonCoachScolaire">
    <link rel="stylesheet" href="<?= asset_url('assets/css/tailwind.css') ?>">
    <?php if ($page_css): ?>
        <link rel="stylesheet" href="<?= asset_url('assets/css/pages/' . $page_css) ?>">
    <?php endif; ?>
</head>
<body class="bg-gray-50 text-gray-900 <?= htmlspecialchars($page_class) ?>">
    <?php if (is_file(dirname(__DIR__, 2) . '/includes/topbar.php')) require_once dirname(__DIR__, 2) . '/includes/topbar.php'; ?>
    <main class="max-w-3xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-2xl p-8 md:p-12 mb-8 border border-gray-100">
            <h1 class="text-3xl md:text-4xl font-bold text-center mb-8">
                <?= htmlspecialchars($page_title) ?>
            </h1>
            <!-- CONTENU ICI -->
        </div>
    </main>
    <?php if (is_file(dirname(__DIR__, 2) . '/includes/footer.php')) require_once dirname(__DIR__, 2) . '/includes/footer.php'; ?>
</body>
</html>
