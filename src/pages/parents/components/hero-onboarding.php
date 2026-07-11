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
<section class="relative mb-8 overflow-hidden rounded-[2rem] border border-slate-200/70 bg-white/85 shadow-[0_24px_70px_-30px_rgba(15,23,42,0.35)] backdrop-blur">
    <?php if ($hero_video_src): ?>
        <video autoplay loop muted playsinline class="pointer-events-none absolute inset-0 h-full w-full object-cover opacity-10">
            <source src="<?= htmlspecialchars($hero_video_src, ENT_QUOTES) ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    <div class="relative z-10 flex min-h-[340px] flex-col items-center justify-center px-4 py-10 text-center md:min-h-[420px] md:px-12">
        <div class="mb-4 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700">
            Espace parent
        </div>
        <h1 class="mb-3 text-3xl font-extrabold tracking-tight text-slate-900 md:text-5xl">Bienvenue sur MonCoachScolaire</h1>
        <p class="mb-6 max-w-2xl text-base leading-7 text-slate-700 md:text-xl">Suivez la progression de vos enfants, encouragez-les et découvrez des conseils personnalisés pour les accompagner au quotidien.</p>
        <div class="flex flex-col items-center gap-4 md:flex-row">
            <?php if ($hero_img_src): ?>
                <img src="<?= htmlspecialchars($hero_img_src, ENT_QUOTES) ?>" alt="Famille" class="h-20 w-20 rounded-full border-2 border-white/80 object-cover shadow-lg">
            <?php else: ?>
                <div class="flex h-20 w-20 items-center justify-center rounded-full border-2 border-white/80 bg-gradient-to-br from-emerald-500 via-cyan-600 to-slate-800 text-lg font-bold tracking-wide text-white shadow-lg" aria-hidden="true">
                    MCS
                </div>
            <?php endif; ?>
            <div class="rounded-2xl border border-slate-200/80 bg-white/70 px-4 py-3 text-left shadow-sm">
                <p class="text-sm font-semibold text-slate-900">Un accompagnement simple</p>
                <p class="mt-1 text-sm text-slate-600">Des repères clairs pour soutenir votre enfant sans surcharge.</p>
            </div>
        </div>
    </div>
</section>

