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

<main class="main-content learning-path-page">
    <div class="path-header">
        <a href="<?= site_url('cours') ?>" class="btn-back">← Retour aux cours</a>

        <h1><?= htmlspecialchars($path['Title']) ?></h1>
        <p class="path-description"><?= htmlspecialchars($path['Description']) ?></p>

        <div class="path-meta">
            <span class="meta-item">📚 <?= htmlspecialchars($path['Subject']) ?></span>
            <span class="meta-item">🎓 <?= htmlspecialchars($path['Level']) ?></span>
            <span class="meta-item">⏱️ <?= $path['Duration'] ?> min</span>
            <span class="meta-item difficulty-<?= $path['Difficulty'] ?>">
                <?= $path['Difficulty'] === 'facile' ? '🟢' : ($path['Difficulty'] === 'difficile' ? '🔴' : '🟠') ?>
                <?= ucfirst($path['Difficulty']) ?>
            </span>
        </div>

        <!-- Barre de progression -->
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: <?= $progressPercentage ?>%"></div>
            <span class="progress-text"><?= $progressPercentage ?>% complété</span>
        </div>

        <?php if ($isCompleted): ?>
        <div class="completion-badge">
            <h2>🎉 Parcours terminé !</h2>
            <p>Score final : <?= $progress['Score'] ?>%</p>
            <p>Temps total : <?= round($progress['TimeSpent'] / 60) ?> min</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Liste des étapes -->
    <div class="steps-list">
        <?php foreach ($path['Steps'] as $index => $step):
            $isCompleted = in_array($index, $progress['CompletedSteps']);
            $isCurrent = ($index === $progress['CurrentStepIndex']);
            $isLocked = ($index > $progress['CurrentStepIndex']);

            $stepClass = 'step-item';
            if ($isCompleted) {
                $stepClass .= ' completed';
            }
            if ($isCurrent) {
                $stepClass .= ' current';
            }
            if ($isLocked) {
                $stepClass .= ' locked';
            }
            ?>
        <div class="<?= $stepClass ?>" data-step-index="<?= $index ?>">
            <div class="step-number">
                <?php if ($isCompleted): ?>
                    ✅
                <?php elseif ($isCurrent): ?>
                    ▶️
                <?php else: ?>
                    <?= $index + 1 ?>
                <?php endif; ?>
            </div>

            <div class="step-content">
                <h3><?= htmlspecialchars($step['title']) ?></h3>
                <p class="step-type">
                    <?php
                        $icons = [
                            'course' => '📖',
                            'exercise' => '✏️',
                            'quiz' => '❓',
                            'assessment' => '📊',
                        ];
            echo $icons[$step['type']] ?? '📄';
            echo ' ' . ucfirst($step['type']);
            ?>
                    · <?= $step['duration'] ?> min
                    <?= $step['is_mandatory'] ? '(Obligatoire)' : '(Optionnel)' ?>
                </p>

                <?php if (!$isLocked): ?>
                <a href="<?= getStepUrl($step) ?>" class="btn-step">
                    <?= $isCompleted ? 'Revoir' : ($isCurrent ? 'Continuer' : 'Commencer') ?>
                </a>
                <?php else: ?>
                <span class="step-locked">🔒 Étape verrouillée</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

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
