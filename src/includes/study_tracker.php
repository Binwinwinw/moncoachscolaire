<?php

/**
 * Système de tracking des révisions et statistiques
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Démarre une session d'étude
 */
function startStudySession($userId, $resourceType, $resourceId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO study_sessions (UserId, ResourceType, ResourceId, StartedAt, DeviceType)
        VALUES (?, ?, ?, NOW(), ?)
    ");

    $deviceType = detectDeviceType();
    $stmt->execute([$userId, $resourceType, $resourceId, $deviceType]);

    $sessionId = $pdo->lastInsertId();

    // Enregistrer l'événement
    logActivityEvent($userId, getEventTypeFromResource($resourceType), [
        'resource_id' => $resourceId,
        'session_id' => $sessionId,
    ]);

    return $sessionId;
}

/**
 * Termine une session d'étude
 */
function endStudySession($sessionId, $score = null, $completionRate = 100)
{
    global $pdo;

    // Récupérer la session
    $stmt = $pdo->prepare("SELECT * FROM study_sessions WHERE Id = ?");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        return false;
    }

    // Calculer la durée
    $startTime = strtotime($session['StartedAt']);
    $endTime = time();
    $duration = $endTime - $startTime;

    // Mettre à jour la session
    $stmt = $pdo->prepare("
        UPDATE study_sessions
        SET EndedAt = NOW(),
            Duration = ?,
            Score = ?,
            CompletionRate = ?
        WHERE Id = ?
    ");

    $stmt->execute([$duration, $score, $completionRate, $sessionId]);

    // Mettre à jour les statistiques quotidiennes
    updateDailyStats($session['UserId'], $session['ResourceType'], $duration, $score);

    return [
        'duration' => $duration,
        'score' => $score,
        'completion_rate' => $completionRate,
    ];
}

/**
 * Met à jour les statistiques quotidiennes
 */
function updateDailyStats($userId, $resourceType, $duration, $score = null)
{
    global $pdo;

    $today = date('Y-m-d');

    // Vérifier si une entrée existe pour aujourd'hui
    $stmt = $pdo->prepare("
        SELECT * FROM daily_stats
        WHERE UserId = ? AND Date = ?
    ");
    $stmt->execute([$userId, $today]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stats) {
        // Créer une nouvelle entrée
        $stmt = $pdo->prepare("
            INSERT INTO daily_stats (UserId, Date, TotalStudyTime)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $today, $duration]);
        $stats = ['Id' => $pdo->lastInsertId()];
    } else {
        // Mettre à jour
        $stmt = $pdo->prepare("
            UPDATE daily_stats
            SET TotalStudyTime = TotalStudyTime + ?
            WHERE Id = ?
        ");
        $stmt->execute([$duration, $stats['Id']]);
    }

    // Mettre à jour les compteurs selon le type
    switch ($resourceType) {
        case 'course':
            $stmt = $pdo->prepare("UPDATE daily_stats SET CoursesViewed = CoursesViewed + 1 WHERE Id = ?");
            break;
        case 'exercise':
            $stmt = $pdo->prepare("UPDATE daily_stats SET ExercisesCompleted = ExercisesCompleted + 1 WHERE Id = ?");
            break;
        case 'quiz':
            $stmt = $pdo->prepare("UPDATE daily_stats SET QuizzesTaken = QuizzesTaken + 1 WHERE Id = ?");
            break;
    }

    if (isset($stmt)) {
        $stmt->execute([$stats['Id']]);
    }

    // Mettre à jour le score moyen si applicable
    if ($score !== null) {
        $stmt = $pdo->prepare("
            UPDATE daily_stats
            SET AverageScore = (
                SELECT AVG(Score)
                FROM study_sessions
                WHERE UserId = ?
                  AND DATE(StartedAt) = ?
                  AND Score IS NOT NULL
            )
            WHERE Id = ?
        ");
        $stmt->execute([$userId, $today, $stats['Id']]);
    }
}

/**
 * Récupère les statistiques d'un utilisateur pour une période
 */
function getUserStats($userId, $startDate = null, $endDate = null)
{
    global $pdo;

    if (!$startDate) {
        $startDate = date('Y-m-d', strtotime('-30 days'));
    }
    if (!$endDate) {
        $endDate = date('Y-m-d');
    }

    // Stats globales
    $stmt = $pdo->prepare("
        SELECT
            SUM(TotalStudyTime) as total_time,
            SUM(CoursesViewed) as total_courses,
            SUM(ExercisesCompleted) as total_exercises,
            SUM(QuizzesTaken) as total_quizzes,
            AVG(AverageScore) as avg_score,
            SUM(XPEarned) as total_xp,
            SUM(CristauxEarned) as total_cristaux
        FROM daily_stats
        WHERE UserId = ?
          AND Date BETWEEN ? AND ?
    ");
    $stmt->execute([$userId, $startDate, $endDate]);
    $globalStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Stats par jour (pour les graphiques)
    $stmt = $pdo->prepare("
        SELECT
            Date,
            TotalStudyTime,
            CoursesViewed,
            ExercisesCompleted,
            AverageScore
        FROM daily_stats
        WHERE UserId = ?
          AND Date BETWEEN ? AND ?
        ORDER BY Date ASC
    ");
    $stmt->execute([$userId, $startDate, $endDate]);
    $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Matières les plus étudiées
    $stmt = $pdo->prepare("
        SELECT
            e.Subject,
            COUNT(DISTINCT ss.Id) as sessions_count,
            SUM(ss.Duration) as total_time
        FROM study_sessions ss
        JOIN exercises e ON ss.ResourceId = e.Id
        WHERE ss.UserId = ?
          AND ss.ResourceType = 'exercise'
          AND DATE(ss.StartedAt) BETWEEN ? AND ?
        GROUP BY e.Subject
        ORDER BY total_time DESC
        LIMIT 5
    ");
    $stmt->execute([$userId, $startDate, $endDate]);
    $topSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Streak (série de jours consécutifs)
    $streak = calculateStreak($userId);

    return [
        'global' => $globalStats,
        'daily' => $dailyStats,
        'top_subjects' => $topSubjects,
        'streak' => $streak,
        'period' => [
            'start' => $startDate,
            'end' => $endDate,
        ],
    ];
}

/**
 * Calcule la série de jours consécutifs d'étude
 */
function calculateStreak($userId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT Date
        FROM daily_stats
        WHERE UserId = ?
          AND TotalStudyTime > 0
        ORDER BY Date DESC
    ");
    $stmt->execute([$userId]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($dates)) {
        return 0;
    }

    $streak = 0;
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Vérifier si aujourd'hui ou hier a été étudié
    if ($dates[0] !== $today && $dates[0] !== $yesterday) {
        return 0;
    }

    // Compter les jours consécutifs
    $currentDate = new DateTime($dates[0]);
    foreach ($dates as $date) {
        $checkDate = new DateTime($date);
        $diff = $currentDate->diff($checkDate)->days;

        if ($diff <= 1) {
            $streak++;
            $currentDate = $checkDate;
        } else {
            break;
        }
    }

    return $streak;
}

/**
 * Récupère la heatmap d'activité (type GitHub)
 */
function getActivityHeatmap($userId, $year = null)
{
    global $pdo;

    if (!$year) {
        $year = date('Y');
    }

    $startDate = "$year-01-01";
    $endDate = "$year-12-31";

    $stmt = $pdo->prepare("
        SELECT
            Date,
            TotalStudyTime
        FROM daily_stats
        WHERE UserId = ?
          AND Date BETWEEN ? AND ?
        ORDER BY Date ASC
    ");
    $stmt->execute([$userId, $startDate, $endDate]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transformer en format heatmap
    $heatmap = [];
    foreach ($data as $day) {
        $heatmap[$day['Date']] = [
            'time' => $day['TotalStudyTime'],
            'level' => getHeatmapLevel($day['TotalStudyTime']),
        ];
    }

    return $heatmap;
}

/**
 * Détermine le niveau d'intensité pour la heatmap
 */
function getHeatmapLevel($seconds)
{
    $minutes = $seconds / 60;

    if ($minutes === 0) {
        return 0;
    }
    if ($minutes < 15) {
        return 1;
    }
    if ($minutes < 30) {
        return 2;
    }
    if ($minutes < 60) {
        return 3;
    }
    return 4;
}

/**
 * Enregistre un événement d'activité
 */
function logActivityEvent($userId, $eventType, $eventData = [])
{
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO activity_events (UserId, EventType, EventData)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $eventType,
        json_encode($eventData, JSON_UNESCAPED_UNICODE),
    ]);
}

/**
 * Récupère la timeline d'activité récente
 */
function getActivityTimeline($userId, $limit = 20)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT * FROM activity_events
        WHERE UserId = ?
        ORDER BY CreatedAt DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($events as &$event) {
        $event['EventData'] = json_decode($event['EventData'], true);
    }

    return $events;
}

/**
 * Crée ou met à jour un objectif
 */
function setUserGoal($userId, $goalType, $targetValue, $daysToComplete = 7)
{
    global $pdo;

    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime("+$daysToComplete days"));

    $stmt = $pdo->prepare("
        INSERT INTO user_goals (UserId, GoalType, TargetValue, StartDate, EndDate)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([$userId, $goalType, $targetValue, $startDate, $endDate]);

    return $pdo->lastInsertId();
}

/**
 * Récupère les objectifs actifs d'un utilisateur
 */
function getUserGoals($userId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT * FROM user_goals
        WHERE UserId = ?
          AND IsCompleted = 0
          AND EndDate >= CURDATE()
        ORDER BY EndDate ASC
    ");
    $stmt->execute([$userId]);
    $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer la progression de chaque objectif
    foreach ($goals as &$goal) {
        $goal['progress_percentage'] = min(100, round(($goal['CurrentValue'] / $goal['TargetValue']) * 100));
        $goal['remaining_days'] = max(0, (strtotime($goal['EndDate']) - time()) / 86400);
    }

    return $goals;
}

// Fonctions utilitaires
function detectDeviceType()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if (preg_match('/mobile/i', $userAgent)) {
        return 'mobile';
    } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
        return 'tablet';
    }
    return 'desktop';
}

function getEventTypeFromResource($resourceType)
{
    $map = [
        'course' => 'course_view',
        'exercise' => 'exercise_start',
        'quiz' => 'quiz_complete',
    ];
    return $map[$resourceType] ?? 'course_view';
}
