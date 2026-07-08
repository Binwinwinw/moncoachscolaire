<?php
$parent_page_direct = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(dirname(__DIR__) . '/dashboard_parent.php');

// pages_sensibles (index.php) n'émet pas le shell HTML : header si absent.
if (!defined('HEAD_EMITTED')) {
    require_once dirname(__DIR__, 3) . '/includes/header.php';
}
if ($parent_page_direct || empty($GLOBALS['__topbar_included'])) {
    require_once dirname(__DIR__, 3) . '/includes/topbar.php';
}
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
$parent_bg_img = function_exists('asset_url')
    ? asset_url('assets/img/background_school_material.webp')
    : $resolveParentAsset('assets/img/background_school_material.webp');
$parentDb = null;
if (isset($db) && $db instanceof PDO) {
    $parentDb = $db;
} elseif (isset($pdo) && $pdo instanceof PDO) {
    $parentDb = $pdo;
}
?>
<div class="parent-dashboard-shell relative min-h-screen bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: url('<?php echo htmlspecialchars($parent_bg_img, ENT_QUOTES, 'UTF-8'); ?>');">
    <div class="absolute inset-0 bg-white/10"></div>
    <main class="relative z-10 mx-auto max-w-6xl px-4 pb-16 pt-6 md:px-6 lg:px-8">
        <?php include __DIR__ . '/../components/hero-onboarding.php'; ?>

        <?php
        $parentGuidanceChildName = 'votre enfant';
        $parentGuidanceChildId = null;
        $parentGuidanceTitle = 'Commencez par rattacher votre enfant';
        $parentGuidanceSummary = 'Pour accéder au suivi parent, vous devez d’abord rattacher au moins un enfant à votre compte avec un code de rattachement.';
        $parentGuidanceActionLabel = 'Aller au rattachement';
        $parentGuidanceActionUrl = '#family-invite-section';
        $parentGuidanceAdvice = 'Générez un code de rattachement puis partagez-le à votre enfant pour activer le suivi.';

        if (!empty($enfants)) {
            $firstChild = $enfants[0];
            $parentGuidanceChildName = $firstChild['Prenom'] ?? $firstChild['prenom'] ?? $firstChild['Username'] ?? 'votre enfant';
            $parentGuidanceChildId = $firstChild['Id'] ?? $firstChild['id'] ?? $firstChild['user_id'] ?? $firstChild['enfant_id'] ?? null;
            $firstProgress = $progressions[0] ?? null;
            $firstProgressPercent = isset($firstProgress['pourcent']) ? (int) $firstProgress['pourcent'] : null;

            if ($parentDb instanceof PDO) {
                require_once dirname(__DIR__, 3) . '/includes/learning_repository.php';
                $parentLearningRepository = new LearningRepository($parentDb);
                $parentChildProfile = $parentGuidanceChildId ? $parentLearningRepository->getActiveProfileForUser((int) $parentGuidanceChildId) : null;
                if ($parentChildProfile) {
                    $parentRecommendationContext = $parentLearningRepository->buildRecommendationContext((int) $parentChildProfile['id'], 'consolidation');
                    $parentRecommendationCopy = $parentRecommendationContext['next_step'] ?? null;
                    if ($parentRecommendationCopy) {
                        $parentGuidanceTitle = $parentRecommendationCopy['title'];
                        $parentGuidanceSummary = $parentRecommendationCopy['description'];
                        $parentGuidanceActionLabel = $parentRecommendationCopy['label'];
                    }
                }
            }

            if ($firstProgressPercent !== null && $firstProgressPercent < 70) {
                $parentGuidanceTitle = $parentGuidanceChildName . ' pourrait reprendre une révision';
                $parentGuidanceSummary = $parentGuidanceChildName . ' a déjà des traces de progression. Une petite révision ciblée pourrait lui faire beaucoup de bien.';
                $parentGuidanceActionLabel = 'Voir le suivi de ' . $parentGuidanceChildName;
                $parentGuidanceAdvice = 'Proposez-lui 10 minutes de travail calme et une petite félicitation à la fin.';
            } elseif ($parentGuidanceTitle === 'Une action simple à faire aujourd’hui') {
                $parentGuidanceTitle = 'Le moment idéal pour soutenir ' . $parentGuidanceChildName;
                $parentGuidanceSummary = 'Les progrès sont présents. Une simple mise au point sur une matière ou une notion suffit pour maintenir la motivation.';
                $parentGuidanceActionLabel = 'Ouvrir le suivi de ' . $parentGuidanceChildName;
                $parentGuidanceAdvice = 'Un échange simple à la maison vaut souvent mieux qu’un long bilan.';
            }
        }

        if ($parentGuidanceChildId) {
            $parentGuidanceActionUrl = site_url('parents/suivi_enfant') . '?id=' . urlencode((string) $parentGuidanceChildId);
        }

        $parentGuidanceActionDisabled = empty($parentGuidanceChildId);
        ?>
        <section class="mb-8 rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-700">Conseil parent</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900"><?php echo htmlspecialchars((string) $parentGuidanceTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="mt-2 text-sm leading-6 text-slate-700"><?php echo htmlspecialchars((string) $parentGuidanceSummary, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 lg:min-w-[260px]">
                    <p class="text-sm font-semibold text-emerald-800">Prochaine action</p>
                    <p class="mt-1 text-sm text-slate-700"><?php echo $parentGuidanceActionDisabled ? 'Commencez par relier votre compte parent à celui de votre enfant pour débloquer le suivi.' : 'Une action claire et rassurante pour accompagner votre enfant cette semaine.'; ?></p>
                    <div class="group relative mt-3 inline-flex w-full flex-col items-start">
                        <?php if ($parentGuidanceActionDisabled): ?>
                            <a href="<?php echo htmlspecialchars((string) $parentGuidanceActionUrl, ENT_QUOTES, 'UTF-8'); ?>" aria-describedby="parent-guidance-disabled-help" class="inline-flex items-center rounded-full border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                                <?php echo htmlspecialchars((string) $parentGuidanceActionLabel, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                            <div id="parent-guidance-disabled-help" role="status" class="mt-2 hidden max-w-xs rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 shadow-sm group-hover:block group-focus-within:block">
                                Vous devez rattacher au moins un enfant.
                            </div>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars((string) $parentGuidanceActionUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                                <?php echo htmlspecialchars((string) $parentGuidanceActionLabel, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-slate-700">
                <span class="font-semibold text-amber-700">À faire à la maison :</span>
                <span class="ml-2"><?php echo htmlspecialchars((string) $parentGuidanceAdvice, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </section>

        <?php include __DIR__ . '/../components/child-grid.php'; ?>
        <?php include __DIR__ . '/../components/family-invite.php'; ?>
        <?php include __DIR__ . '/../components/stats-widgets.php'; ?>
        <?php include __DIR__ . '/../components/notifications-feed.php'; ?>
    </main>
</div>
<?php
require_once dirname(__DIR__, 3) . '/includes/footer.php';
?>

