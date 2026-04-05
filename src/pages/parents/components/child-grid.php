<?php
// Grille enfants
$build_inline_avatar = static function ($label, $accent = '#4f46e5') {
    $initial = strtoupper(substr(trim((string) $label), 0, 1));
    if ($initial === '') {
        $initial = 'E';
    }

    $svg = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96" role="img" aria-label="Avatar %1$s"><rect width="96" height="96" rx="48" fill="#eef2ff"/><circle cx="48" cy="48" r="42" fill="%2$s" opacity="0.16"/><text x="48" y="56" text-anchor="middle" font-family="Arial, sans-serif" font-size="38" font-weight="700" fill="%2$s">%1$s</text></svg>',
        htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($accent, ENT_QUOTES, 'UTF-8')
    );

    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
};

$build_avatar_url = static function ($path, $label = 'E') use ($build_inline_avatar) {
    $path = trim((string) $path);
    if ($path === '') {
        return $build_inline_avatar($label);
    }

    if (preg_match('#^(https?:)?//#i', $path) === 1 || strpos($path, 'data:image/') === 0) {
        return $path;
    }

    $normalizedPath = ltrim($path, '/');
    $publicPath = dirname(__DIR__, 4) . '/public/' . preg_replace('#^public/#', '', $normalizedPath);

    if (is_file($publicPath)) {
        if (function_exists('asset_url')) {
            return asset_url(preg_replace('#^public/#', '', $normalizedPath));
        }

        return '/' . preg_replace('#^public/#', '', $normalizedPath);
    }

    return $build_inline_avatar($label);
};
?>
<section class="mb-10">
    <h2 class="text-xl md:text-2xl font-bold text-indigo-800 mb-5 flex items-center gap-2">
        <span class="text-lg">👨‍👩‍👧‍👦</span> Vos enfants suivis
    </h2>
    <?php if (empty($enfants)): ?>
        <div class="rounded-2xl shadow p-6 md:p-8 mcs-card-bg border border-indigo-100/70">
            <div class="flex flex-col md:flex-row md:items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 via-violet-500 to-blue-500 text-white flex items-center justify-center text-xl font-bold shrink-0">
                    +
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-indigo-950 mb-1">Aucun enfant rattaché pour l’instant</h3>
                    <p class="text-sm text-gray-600 max-w-2xl">Ajoutez un enfant pour faire apparaître sa progression réelle, ses matières fortes et ses derniers quiz sur ce tableau de bord.</p>
                </div>
                <div class="shrink-0">
                    <a href="<?= site_url('parents/ajouter_enfant') ?>" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-full font-semibold shadow-sm hover:bg-indigo-700 transition-colors">Ajouter un enfant</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($enfants as $enfant): ?>
                <div class="rounded-2xl shadow p-6 flex flex-col items-center hover:scale-[1.02] transition-transform">
                    <?php $enfant_id = $enfant['Id'] ?? $enfant['id'] ?? $enfant['user_id'] ?? $enfant['enfant_id'] ?? null; ?>
                    <?php $avatar = $build_avatar_url($enfant['avatar'] ?? '', $enfant['prenom'] ?? $enfant['Prenom'] ?? $enfant['Username'] ?? 'E'); ?>
                    <img src="<?= htmlspecialchars($avatar, ENT_QUOTES) ?>" alt="Avatar" class="w-16 h-16 rounded-full mb-2 border-2 border-indigo-100">
                    <div class="font-semibold text-base text-indigo-900 mb-1"><?= htmlspecialchars($enfant['prenom'] ?? $enfant['Prenom'] ?? 'Enfant') ?> <span class="text-xs text-gray-400 align-top">(<?= htmlspecialchars($enfant['niveau'] ?? $enfant['Niveau'] ?? '') ?>)</span></div>
                    <div class="text-xs text-gray-500 mb-2">Suivi actif</div>
                    <a href="<?= site_url('parents/suivi_enfant') ?>?id=<?= urlencode((string) $enfant_id) ?>" class="mt-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-full font-semibold border border-indigo-100 shadow-sm hover:bg-indigo-100">Voir le détail</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
