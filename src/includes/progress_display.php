<?php
/**
 * Composant d'affichage de la progression utilisateur
 *
 * Usage: require_once __DIR__ . '/progress_display.php';
 */

// S'assurer que les dépendances sont chargées
if (!function_exists('getUserProgress')) {
    require_once __DIR__ . '/gamification.php';
}

/**
 * Affiche la barre de progression globale
 */
function renderProgressBar($userId, $options = [])
{
    $defaultOptions = [
        'showXP' => true,
        'showCristaux' => true,
        'showBadges' => true,
        'compact' => false,
    ];
    $options = array_merge($defaultOptions, $options);

    $progress = getUserProgress($userId);
    if (!$progress) {
        echo '<p>Aucune progression disponible.</p>';
        return;
    }

    $xp = $progress['xp'] ?? 0;
    $level = getUserLevel($xp);
    $nextLevelXP = getNextLevelXP($level);
    $xpProgress = $nextLevelXP > 0 ? ($xp / $nextLevelXP) * 100 : 0;

    $cristaux = $progress['cristaux'] ?? 0;
    $badgesCount = $progress['badges_count'] ?? 0;
    $exercisesCompleted = $progress['exercises_completed'] ?? 0;
    ?>
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 my-4">
        <?php if ($options['showXP']): ?>
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-2xl mr-2">⭐</span>
                <span class="flex-1 font-semibold text-blue-900">Niveau <?php echo $level; ?></span>
                <span class="font-bold text-green-600"><?php echo $xp; ?> XP</span>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-500" style="width: <?php echo min($xpProgress, 100); ?>%"></div>
            </div>
            <div class="text-sm text-gray-500 mt-1">Prochain niveau : <?php echo $nextLevelXP; ?> XP</div>
        </div>
        <?php endif; ?>

        <?php if ($options['showCristaux']): ?>
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-2xl mr-2">💎</span>
                <span class="flex-1 font-semibold text-blue-900">Cristaux</span>
                <span class="font-bold text-green-600"><?php echo $cristaux; ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($options['showBadges']): ?>
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-2xl mr-2">🏆</span>
                <span class="flex-1 font-semibold text-blue-900">Badges</span>
                <span class="font-bold text-green-600"><?php echo $badgesCount; ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-2xl mr-2">📝</span>
                <span class="flex-1 font-semibold text-blue-900">Exercices complétés</span>
                <span class="font-bold text-green-600"><?php echo $exercisesCompleted; ?></span>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Affiche les statistiques détaillées
 */
function renderDetailedProgress($userId)
{
    $progress = getUserProgress($userId);
    if (!$progress) {
        echo '<p>Aucune progression disponible.</p>';
        return;
    }

    $xp = $progress['xp'] ?? 0;
    $level = getUserLevel($xp);
    $cristaux = $progress['cristaux'] ?? 0;
    $cristauxBySubject = $progress['cristaux_by_subject'] ?? [];
    $exercisesCompleted = $progress['exercises_completed'] ?? 0;
    $bySubject = $progress['by_subject'] ?? [];
    $badges = $progress['badges'] ?? [];

    ?>
    <div class="bg-white rounded-xl p-8 my-8 shadow-lg">
        <h3 class="text-blue-600 mb-6 text-2xl font-bold">📊 Ma Progression</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                <div class="text-4xl mb-2">⭐</div>
                <div class="text-3xl font-bold text-blue-900"><?php echo $level; ?></div>
                <div class="text-gray-500 text-sm mt-2">Niveau</div>
            </div>

            <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                <div class="text-4xl mb-2">💎</div>
                <div class="text-3xl font-bold text-blue-900"><?php echo $cristaux; ?></div>
                <div class="text-gray-500 text-sm mt-2">Cristaux</div>
            </div>

            <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                <div class="text-4xl mb-2">📝</div>
                <div class="text-3xl font-bold text-blue-900"><?php echo $exercisesCompleted; ?></div>
                <div class="text-gray-500 text-sm mt-2">Exercices</div>
            </div>

            <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                <div class="text-4xl mb-2">🏆</div>
                <div class="text-3xl font-bold text-blue-900"><?php echo count($badges); ?></div>
                <div class="text-gray-500 text-sm mt-2">Badges</div>
            </div>
        </div>

        <?php if (!empty($bySubject)): ?>
        <div class="mb-8">
            <h4 class="text-lg font-semibold mb-4">Par Matière</h4>
            <div class="flex flex-col gap-2">
                <?php foreach ($bySubject as $subjectStat): ?>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="font-semibold"><?php echo htmlspecialchars($subjectStat['Subject']); ?></span>
                    <span class="text-gray-500"><?php echo $subjectStat['count']; ?> exercices</span>
                    <?php if (isset($cristauxBySubject[$subjectStat['Subject']])): ?>
                    <span class="text-green-600 font-semibold">💎 <?php echo $cristauxBySubject[$subjectStat['Subject']]; ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($badges)): ?>
        <div class="mb-8">
            <h4 class="text-lg font-semibold mb-4">🏆 Mes Badges</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($badges as $badge): ?>
                <div class="text-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border-2 border-yellow-400">
                    <div class="text-5xl mb-2">🏆</div>
                    <div class="font-bold text-amber-900 mb-1"><?php echo htmlspecialchars($badge['Name']); ?></div>
                    <?php if ($badge['Description']): ?>
                    <div class="text-sm text-amber-800 mb-2"><?php echo htmlspecialchars($badge['Description']); ?></div>
                    <?php endif; ?>
                    <div class="text-xs text-yellow-700">Débloqué le <?php echo date('d/m/Y', strtotime($badge['UnlockedAt'])); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Calcule l'XP nécessaire pour le prochain niveau
 */
function getNextLevelXP($currentLevel)
{
    $levels = [
        1 => 100,
        2 => 300,
        3 => 600,
        4 => 1000,
        5 => 1500,
        6 => 2100,
        7 => 2800,
        8 => 3600,
        9 => 4500,
        10 => PHP_INT_MAX,
    ];

    return $levels[$currentLevel] ?? PHP_INT_MAX;
}

?>

