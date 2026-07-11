<?php
$page_title = 'Parcours d\'apprentissage - MonCoachScolaire';
$page_css = 'pages/learning_path.css';

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/site_boot.php';
require_once dirname(__DIR__, 2) . '/includes/learning_path_manager.php';

// Vérifier connexion
if (!$is_logged_in) {
    header('Location: ' . site_url('login'));
    exit;
}

$pathId = $_GET['id'] ?? 0;
$path = getLearningPath($pathId);

if (!$path) {
    echo '<p>Parcours introuvable.</p>';
    exit;
}

$userId = $_SESSION['user_id'];
$progress = getUserPathProgress($userId, $pathId);
$currentStep = getCurrentStep($userId, $pathId);

$progressPercentage = calculateProgressPercentage($progress, $path);
$isCompleted = !empty($progress['CompletedAt']);
?>

<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <main class="mx-auto max-w-5xl">
        <div class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm sm:p-8">
            <a href="<?= site_url('cours') ?>" class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700">
                ← Retour aux cours
            </a>

            <div class="mt-6 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-700">Parcours d’apprentissage</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900 sm:text-4xl"><?= htmlspecialchars($path['Title']) ?></h1>
                    <p class="mt-3 text-base leading-7 text-slate-600"><?= htmlspecialchars($path['Description']) ?></p>
                </div>

                <?php if ($isCompleted): ?>
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                    <p class="font-semibold">🎉 Parcours terminé</p>
                    <p class="mt-1">Score final : <?= $progress['Score'] ?>%</p>
                    <p>Temps total : <?= round($progress['TimeSpent'] / 60) ?> min</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-700">📚 <?= htmlspecialchars($path['Subject']) ?></span>
                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-700">🎓 <?= htmlspecialchars($path['Level']) ?></span>
                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-700">⏱️ <?= $path['Duration'] ?> min</span>
                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm text-slate-700">
                    <?= $path['Difficulty'] === 'facile' ? '🟢' : ($path['Difficulty'] === 'difficile' ? '🔴' : '🟠') ?>
                    <?= ucfirst($path['Difficulty']) ?>
                </span>
            </div>

            <div class="mt-8">
                <div class="flex items-center justify-between text-sm text-slate-600">
                    <span>Avancement du parcours</span>
                    <span class="font-semibold text-slate-800"><?= $progressPercentage ?>%</span>
                </div>
                <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full rounded-full bg-emerald-500 transition-all duration-300" style="width: <?= (int) $progressPercentage ?>%;"></div>
                </div>
            </div>
        </div>

        <div class="mt-8 space-y-4">
            <?php foreach ($path['Steps'] as $index => $step):
                $isCompleted = in_array($index, $progress['CompletedSteps']);
                $isCurrent = ($index === $progress['CurrentStepIndex']);
                $isLocked = ($index > $progress['CurrentStepIndex']);

                $cardClasses = ['rounded-2xl border p-5 shadow-sm transition'];
                if ($isCompleted) {
                    $cardClasses[] = 'border-emerald-300 bg-emerald-50/70';
                } elseif ($isCurrent) {
                    $cardClasses[] = 'border-amber-300 bg-amber-50/70';
                } elseif ($isLocked) {
                    $cardClasses[] = 'border-slate-200 bg-slate-50/70 opacity-80';
                } else {
                    $cardClasses[] = 'border-slate-200 bg-white';
                }
                if (!$isLocked) {
                    $cardClasses[] = 'hover:-translate-y-0.5 hover:shadow-md';
                }
                $cardClass = implode(' ', $cardClasses);
            ?>
            <article class="<?= htmlspecialchars($cardClass, ENT_QUOTES, 'UTF-8') ?>" data-step-index="<?= $index ?>">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-lg shadow-sm">
                            <?php if ($isCompleted): ?>
                                ✅
                            <?php elseif ($isCurrent): ?>
                                ▶️
                            <?php else: ?>
                                <?= $index + 1 ?>
                            <?php endif; ?>
                        </div>

                        <div>
                            <h2 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($step['title']) ?></h2>
                            <p class="mt-2 text-sm text-slate-600">
                                <?php
                                    $icons = [
                                        'course' => '📖',
                                        'exercise' => '✏️',
                                        'quiz' => '❓',
                                        'assessment' => '📊',
                                    ];
                                    echo ($icons[$step['type']] ?? '📄') . ' ' . ucfirst($step['type']) . ' · ' . $step['duration'] . ' min';
                                    echo $step['is_mandatory'] ? ' · Obligatoire' : ' · Optionnel';
                                ?>
                            </p>
                        </div>
                    </div>

                    <?php if (!$isLocked): ?>
                    <a href="<?= getStepUrl($step) ?>" class="inline-flex items-center justify-center rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        <?= $isCompleted ? 'Revoir' : ($isCurrent ? 'Continuer' : 'Commencer') ?>
                    </a>
                    <?php else: ?>
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600">🔒 Étape verrouillée</span>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php
function getStepUrl($step)
{
    switch ($step['type']) {
        case 'course':
            return site_url('view_course', ['id' => $step['resource_id']]);
        case 'exercise':
            return site_url('view_exercise', ['id' => $step['resource_id']]);
        case 'quiz':
            return site_url('quiz', ['id' => $step['resource_id']]);
        default:
            return '#';
    }
}
?>

