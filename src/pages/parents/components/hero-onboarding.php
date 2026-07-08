<?php
// Hero onboarding premium (état vide)
$assetsBase = '';
if (function_exists('detectBaseUrl')) {
    $assetsBase = rtrim((string) detectBaseUrl(), '/');
} elseif (isset($baseUrl)) {
    $assetsBase = rtrim((string) $baseUrl, '/');
}

$resolveParentAsset = static function (string $relativePath) use ($assetsBase): string {
    if (function_exists('asset_url')) {
        return asset_url($relativePath);
    }

    $assetBase = $assetsBase;
    $projectRoot = dirname(__DIR__, 4);
    $normalizedPath = ltrim($relativePath, '/');
    if ($assetBase !== ''
        && stripos($assetBase, '/public') === false
        && is_file($projectRoot . '/public/' . $normalizedPath)) {
        $assetBase .= '/public';
    }

    return $assetBase . '/' . $normalizedPath;
};

$hero_video_asset = 'assets/videos/bonjour_bienvenue.mp4';
$hero_video_path = dirname(__DIR__, 4) . '/public/' . $hero_video_asset;
$hero_video_src = is_file($hero_video_path)
    ? $resolveParentAsset($hero_video_asset)
    : null;
$hero_asset_path = dirname(__DIR__, 4) . '/public/assets/img/parent-famille-demo.png';
$hero_img_src = is_file($hero_asset_path)
    ? $resolveParentAsset('assets/img/parent-famille-demo.png')
    : null;
?>
<section class="relative mb-8 flex min-h-[340px] flex-col items-center justify-center overflow-hidden rounded-3xl border border-white/40 bg-white/25 shadow-lg md:min-h-[420px]">
    <?php if ($hero_video_src): ?>
        <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover opacity-10 pointer-events-none">
            <source src="<?= htmlspecialchars($hero_video_src, ENT_QUOTES) ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    <div class="relative z-10 flex flex-col items-center justify-center py-10 px-4 md:px-12">
        <h1 class="mb-3 text-center text-3xl font-bold text-slate-900 md:text-5xl">Bienvenue sur MonCoachScolaire</h1>
        <p class="mb-5 max-w-xl text-center text-base text-slate-700 md:text-xl">Suivez la progression de vos enfants, encouragez-les et découvrez des conseils personnalisés pour les accompagner au quotidien.</p>
        <div class="flex flex-col md:flex-row gap-4 items-center mt-2">
            <?php if ($hero_img_src): ?>
                <img src="<?= htmlspecialchars($hero_img_src, ENT_QUOTES) ?>" alt="Famille" class="w-20 h-20 rounded-full shadow border-2 border-white/80 object-cover">
            <?php else: ?>
                <div class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-white/80 bg-gradient-to-br from-emerald-500 via-cyan-600 to-slate-800 text-lg font-bold tracking-wide text-white shadow" aria-hidden="true">
                    MCS
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

