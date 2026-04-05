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
<section class="relative flex flex-col items-center justify-center min-h-[340px] md:min-h-[420px] rounded-3xl shadow-lg overflow-hidden mb-8">
    <?php if ($hero_video_src): ?>
        <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover opacity-10 pointer-events-none">
            <source src="<?= htmlspecialchars($hero_video_src, ENT_QUOTES) ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    <div class="relative z-10 flex flex-col items-center justify-center py-10 px-4 md:px-12">
        <h1 class="text-3xl md:text-5xl font-bold text-indigo-900 mb-3">Bienvenue sur MonCoachScolaire</h1>
        <p class="text-base md:text-xl text-gray-700 mb-5 max-w-xl text-center">Suivez la progression de vos enfants, encouragez-les et découvrez des conseils personnalisés pour les accompagner au quotidien.</p>
        <a href="<?= site_url('parents/ajouter_enfant') ?>" class="inline-block bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-base px-7 py-3 rounded-full shadow transition-all duration-200 mb-4 border border-indigo-200">+ Ajouter mon enfant</a>
        <div class="flex flex-col md:flex-row gap-4 items-center mt-2">
            <?php if ($hero_img_src): ?>
                <img src="<?= htmlspecialchars($hero_img_src, ENT_QUOTES) ?>" alt="Famille" class="w-20 h-20 rounded-full shadow border-2 border-white/80 object-cover">
            <?php else: ?>
                <div class="w-20 h-20 rounded-full shadow border-2 border-white/80 bg-gradient-to-br from-indigo-500 via-violet-500 to-blue-500 text-white flex items-center justify-center text-lg font-bold tracking-wide" aria-hidden="true">
                    MCS
                </div>
            <?php endif; ?>
            <div class="text-gray-700 text-sm md:text-base max-w-xs">
                <span class="font-semibold">Nouveau&nbsp;?</span> Regardez notre vidéo tutoriel (30s) ou découvrez les témoignages de parents satisfaits.
            </div>
        </div>
    </div>
</section>

