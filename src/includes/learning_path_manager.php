<?php

/**
 * Gestionnaire de parcours pédagogiques
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Récupère un parcours par son ID
 */
function getLearningPath($pathId)
{
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM learning_paths WHERE Id = ? AND IsActive = 1");
    $stmt->execute([$pathId]);
    $path = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$path) {
        return null;
    }

    // Décoder le JSON des étapes
    $path['Steps'] = json_decode($path['Steps'], true);
    $path['Prerequisites'] = json_decode($path['Prerequisites'] ?? '[]', true);

    return $path;
}

/**
 * Récupère tous les parcours pour un niveau et une matière
 */
function getLearningPathsBySubjectAndLevel($subject, $level)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT * FROM learning_paths
        WHERE Subject = ? AND Level = ? AND IsActive = 1
        ORDER BY CreatedAt DESC
    ");
    $stmt->execute([$subject, $level]);
    $paths = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($paths as &$path) {
        $path['Steps'] = json_decode($path['Steps'], true);
    }

    return $paths;
}

/**
 * Récupère la progression d'un utilisateur dans un parcours
 */
function getUserPathProgress($userId, $pathId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT * FROM user_path_progress
        WHERE UserId = ? AND PathId = ?
    ");
    $stmt->execute([$userId, $pathId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$progress) {
        // Créer une nouvelle progression
        return createUserPathProgress($userId, $pathId);
    }

    $progress['CompletedSteps'] = json_decode($progress['CompletedSteps'] ?? '[]', true);
    return $progress;
}

/**
 * Crée une nouvelle progression pour un utilisateur
 */
function createUserPathProgress($userId, $pathId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO user_path_progress (UserId, PathId, CurrentStepIndex, CompletedSteps)
        VALUES (?, ?, 0, '[]')
    ");
    $stmt->execute([$userId, $pathId]);

    return [
        'Id' => $pdo->lastInsertId(),
        'UserId' => $userId,
        'PathId' => $pathId,
        'CurrentStepIndex' => 0,
        'CompletedSteps' => [],
        'Score' => 0,
        'TimeSpent' => 0,
    ];
}

/**
 * Marque une étape comme terminée
 */
function completePathStep($userId, $pathId, $stepIndex, $score = 100, $timeSpent = 0)
{
    global $pdo;

    $progress = getUserPathProgress($userId, $pathId);
    $path = getLearningPath($pathId);

    if (!$path) {
        return ['error' => 'Parcours introuvable'];
    }

    $step = $path['Steps'][$stepIndex] ?? null;
    if (!$step) {
        return ['error' => 'Étape introuvable'];
    }

    // Vérifier si l'étape respecte les critères de complétion
    $isCompleted = checkStepCompletion($step, $score);

    if (!$isCompleted) {
        return [
            'success' => false,
            'message' => "Score insuffisant. Minimum requis : {$step['completion_criteria']['threshold']}%",
        ];
    }

    // Ajouter l'étape aux étapes terminées
    $completedSteps = $progress['CompletedSteps'];
    if (!in_array($stepIndex, $completedSteps)) {
        $completedSteps[] = $stepIndex;
    }

    // Calculer le score global et le temps total
    $totalScore = $progress['Score'] + $score;
    $totalTime = $progress['TimeSpent'] + $timeSpent;

    // Passer à l'étape suivante
    $nextStepIndex = $stepIndex + 1;
    $isPathCompleted = $nextStepIndex >= count($path['Steps']);

    // Mettre à jour la progression
    $stmt = $pdo->prepare("
        UPDATE user_path_progress
        SET CurrentStepIndex = ?,
            CompletedSteps = ?,
            Score = ?,
            TimeSpent = ?,
            CompletedAt = ?,
            LastAccessAt = NOW()
        WHERE UserId = ? AND PathId = ?
    ");

    $completedAt = $isPathCompleted ? date('Y-m-d H:i:s') : null;

    $stmt->execute([
        $nextStepIndex,
        json_encode($completedSteps),
        $totalScore,
        $totalTime,
        $completedAt,
        $userId,
        $pathId,
    ]);

    // Si le parcours est terminé, attribuer les récompenses
    $rewards = [];
    if ($isPathCompleted) {
        $rewards = grantPathRewards($userId, $path);
    }

    return [
        'success' => true,
        'completed' => $isCompleted,
        'path_completed' => $isPathCompleted,
        'next_step_index' => $nextStepIndex,
        'score' => $score,
        'total_score' => $totalScore,
        'rewards' => $rewards,
    ];
}

/**
 * Vérifie si une étape respecte les critères de complétion
 */
function checkStepCompletion($step, $score)
{
    $criteria = $step['completion_criteria'];

    switch ($criteria['type']) {
        case 'score':
            return $score >= $criteria['threshold'];
        case 'read':
            return $score >= 100; // Considérer comme "lu" si scroll 100%
        default:
            return true;
    }
}

/**
 * Attribue les récompenses à la fin d'un parcours
 */
function grantPathRewards($userId, $path)
{
    global $pdo;

    $rewards = $path['rewards'] ?? [];

    if (empty($rewards)) {
        return [];
    }

    // XP
    if (!empty($rewards['xp'])) {
        $stmt = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
        $stmt->execute([$rewards['xp'], $userId]);
    }

    // Cristaux
    if (!empty($rewards['cristaux'])) {
        $stmt = $pdo->prepare("UPDATE users SET cristaux = cristaux + ? WHERE id = ?");
        $stmt->execute([$rewards['cristaux'], $userId]);
    }

    // Badge
    if (!empty($rewards['badge_id'])) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO user_badges (user_id, badge_id, earned_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$userId, $rewards['badge_id']]);
    }

    return $rewards;
}

/**
 * Récupère l'étape actuelle d'un utilisateur dans un parcours
 */
function getCurrentStep($userId, $pathId)
{
    $progress = getUserPathProgress($userId, $pathId);
    $path = getLearningPath($pathId);

    if (!$path) {
        return null;
    }

    $currentIndex = $progress['CurrentStepIndex'];
    $currentStep = $path['Steps'][$currentIndex] ?? null;

    if (!$currentStep) {
        return null;
    }

    // Enrichir avec les données de la ressource
    $currentStep['resource'] = getStepResource($currentStep);
    $currentStep['progress_percentage'] = calculateProgressPercentage($progress, $path);

    return $currentStep;
}

/**
 * Récupère la ressource associée à une étape
 */
function getStepResource($step)
{
    global $pdo;

    switch ($step['type']) {
        case 'course':
            $stmt = $pdo->prepare("SELECT * FROM courses WHERE Id = ?");
            break;
        case 'exercise':
            $stmt = $pdo->prepare("SELECT * FROM exercises WHERE Id = ?");
            break;
        case 'quiz':
            $stmt = $pdo->prepare("SELECT * FROM quiz WHERE Id = ?");
            break;
        default:
            return null;
    }

    $stmt->execute([$step['resource_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Calcule le pourcentage de progression dans un parcours
 */
function calculateProgressPercentage($progress, $path)
{
    $totalSteps = count($path['Steps']);
    $completedSteps = count($progress['CompletedSteps']);

    return $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
}

/**
 * Créer un parcours depuis un template ou manuellement
 */
function createLearningPath($data)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO learning_paths (Title, Subject, Level, Description, Duration, Difficulty, Steps, Prerequisites)
        VALUES (:title, :subject, :level, :description, :duration, :difficulty, :steps, :prerequisites)
    ");

    $stmt->execute([
        'title' => $data['title'],
        'subject' => $data['subject'],
        'level' => $data['level'],
        'description' => $data['description'] ?? '',
        'duration' => $data['duration'] ?? 60,
        'difficulty' => $data['difficulty'] ?? 'moyen',
        'steps' => json_encode($data['steps'], JSON_UNESCAPED_UNICODE),
        'prerequisites' => json_encode($data['prerequisites'] ?? [], JSON_UNESCAPED_UNICODE),
    ]);

    return $pdo->lastInsertId();
}
