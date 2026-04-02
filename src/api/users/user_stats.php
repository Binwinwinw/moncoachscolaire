<?php

/**
 * API: User Statistics
 * Endpoint pour récupérer les statistiques complètes d'un utilisateur
 *
 * GET /api/user_stats
 *
 * Returns: {
 *   success: bool,
 *   user: {id, name, email},
 *   xp: {total, level, level_name, progress_to_next, next_level_xp},
 *   badges: {total, earned, list},
 *   exercises: {total_attempted, total_completed, avg_score, total_time},
 *   diagnostics: {total, avg_score, passed_count},
 *   notions: {mastered, in_progress, not_started},
 *   activity: {last_exercise, last_diagnostic, streak_days}
 * }
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/connection.php';

api_require([
    'method' => 'GET',
    'auth' => true,
]);

$user_id = $_SESSION['user_id'];

try {
    // 1. Informations utilisateur de base
    $stmt = $pdo->prepare("SELECT Id, Name, Email FROM users WHERE Id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
        exit;
    }

    // 2. XP et niveau
    $stmt = $pdo->prepare("SELECT XP FROM userprogress WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_xp = $stmt->fetchColumn() ?: 0;

    $level_info = calculateLevel($total_xp);

    // 3. Badges
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as earned_count
        FROM userbadge
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $earned_badges = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM badge");
    $total_badges = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT b.id, b.name, b.slug, b.description, b.icon, ub.earned_at
        FROM userbadge ub
        JOIN badge b ON ub.badge_id = b.id
        WHERE ub.user_id = ?
        ORDER BY ub.earned_at DESC
    ");
    $stmt->execute([$user_id]);
    $badge_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Exercices
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT exercise_id) as total_attempted,
            SUM(CASE WHEN correct = 1 THEN 1 ELSE 0 END) as total_correct,
            AVG(score) as avg_score,
            SUM(TimeSpentSeconds) as total_time
        FROM exerciseresponses
        WHERE UserId = ?
    ");
    $stmt->execute([$user_id]);
    $exercises = $stmt->fetch(PDO::FETCH_ASSOC);

    // 5. Diagnostics
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) as total,
            AVG(score) as avg_score,
            SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_count
        FROM quizresult qr
        JOIN quiz q ON qr.quiz_id = q.id
        WHERE qr.user_id = ? AND q.type = 'diagnostic'
    ");
    $stmt->execute([$user_id]);
    $diagnostics = $stmt->fetch(PDO::FETCH_ASSOC);

    // 6. Notions
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT CASE WHEN m.best_score >= 80 THEN en.notion_id END) as mastered,
            COUNT(DISTINCT CASE WHEN m.best_score < 80 AND m.attempts > 0 THEN en.notion_id END) as in_progress
        FROM mastery m
        JOIN exercisenotion en ON m.exercise_id = en.exercise_id
        WHERE m.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $notions_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT COUNT(*) FROM notion");
    $total_notions = $stmt->fetchColumn();

    // 7. Activité récente
    $stmt = $pdo->prepare("
        SELECT MAX(CreatedAt) as last_exercise
        FROM exerciseresponses
        WHERE UserId = ?
    ");
    $stmt->execute([$user_id]);
    $last_exercise = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT MAX(qr.created_at) as last_diagnostic
        FROM quizresult qr
        JOIN quiz q ON qr.quiz_id = q.id
        WHERE qr.user_id = ? AND q.type = 'diagnostic'
    ");
    $stmt->execute([$user_id]);
    $last_diagnostic = $stmt->fetchColumn();

    // Calculer streak (jours consécutifs d'activité)
    $streak = calculateStreak($pdo, $user_id);

    // 8. Assembler réponse
    $response = [
        'success' => true,
        'user' => [
            'id' => $user['Id'],
            'name' => $user['Name'],
            'email' => $user['Email'],
        ],
        'xp' => [
            'total' => (int) $total_xp,
            'level' => $level_info['level'],
            'level_name' => $level_info['name'],
            'progress_to_next' => $level_info['progress'],
            'next_level_xp' => $level_info['next_threshold'],
        ],
        'badges' => [
            'total' => (int) $total_badges,
            'earned' => (int) $earned_badges,
            'list' => $badge_list,
        ],
        'exercises' => [
            'total_attempted' => (int) ($exercises['total_attempted'] ?? 0),
            'total_correct' => (int) ($exercises['total_correct'] ?? 0),
            'avg_score' => round($exercises['avg_score'] ?? 0, 1),
            'total_time_seconds' => (int) ($exercises['total_time'] ?? 0),
        ],
        'diagnostics' => [
            'total' => (int) ($diagnostics['total'] ?? 0),
            'avg_score' => round($diagnostics['avg_score'] ?? 0, 1),
            'passed_count' => (int) ($diagnostics['passed_count'] ?? 0),
        ],
        'notions' => [
            'mastered' => (int) ($notions_stats['mastered'] ?? 0),
            'in_progress' => (int) ($notions_stats['in_progress'] ?? 0),
            'not_started' => $total_notions - ($notions_stats['mastered'] ?? 0) - ($notions_stats['in_progress'] ?? 0),
        ],
        'activity' => [
            'last_exercise' => $last_exercise,
            'last_diagnostic' => $last_diagnostic,
            'streak_days' => $streak,
        ],
    ];
    if (!function_exists('clean_utf8_recursive')) {
        function clean_utf8_recursive($data)
        {
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    $data[$k] = clean_utf8_recursive($v);
                }
                return $data;
            } elseif (is_string($data)) {
                if (!mb_check_encoding($data, 'UTF-8')) {
                    return mb_convert_encoding($data, 'UTF-8', 'auto');
                }
                return $data;
            }
            return $data;
        }
    }
    $response = clean_utf8_recursive($response);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Erreur user_stats: " . $e->getMessage());
    json_error('Erreur serveur', 500, 'ERR_DB');
}

/**
 * Calculer le niveau basé sur l'XP total
 */
function calculateLevel($xp)
{
    $levels = [
        ['threshold' => 0, 'level' => 1, 'name' => 'Débutant'],
        ['threshold' => 100, 'level' => 2, 'name' => 'Apprenti'],
        ['threshold' => 500, 'level' => 3, 'name' => 'Confirmé'],
        ['threshold' => 1000, 'level' => 4, 'name' => 'Expert'],
        ['threshold' => 2500, 'level' => 5, 'name' => 'Maître'],
        ['threshold' => 5000, 'level' => 6, 'name' => 'Grand Maître'],
        ['threshold' => 10000, 'level' => 7, 'name' => 'Légende'],
    ];

    $current = $levels[0];
    $next = $levels[1] ?? ['threshold' => 10000];

    foreach ($levels as $i => $level) {
        if ($xp >= $level['threshold']) {
            $current = $level;
            $next = $levels[$i + 1] ?? ['threshold' => $current['threshold'] + 10000];
        } else {
            break;
        }
    }

    $xp_in_level = $xp - $current['threshold'];
    $xp_needed = $next['threshold'] - $current['threshold'];
    $progress = ($xp_needed > 0) ? round(($xp_in_level / $xp_needed) * 100, 1) : 100;

    return [
        'level' => $current['level'],
        'name' => $current['name'],
        'threshold' => $current['threshold'],
        'next_threshold' => $next['threshold'],
        'progress' => $progress,
    ];
}

/**
 * Calculer le streak (jours consécutifs d'activité)
 */
function calculateStreak($pdo, $user_id)
{
    $stmt = $pdo->prepare("
        SELECT DISTINCT DATE(CreatedAt) as activity_date
        FROM exerciseresponses
        WHERE UserId = ?
        ORDER BY activity_date DESC
        LIMIT 30
    ");
    $stmt->execute([$user_id]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($dates)) {
        return 0;
    }

    $streak = 1;
    $today = new DateTime();
    $yesterday = (new DateTime())->modify('-1 day');

    // Vérifier si activité aujourd'hui ou hier
    $last_activity = new DateTime($dates[0]);
    $today_str = $today->format('Y-m-d');
    $yesterday_str = $yesterday->format('Y-m-d');
    $last_str = $last_activity->format('Y-m-d');

    if ($last_str !== $today_str && $last_str !== $yesterday_str) {
        return 0; // Pas d'activité récente
    }

    // Compter jours consécutifs
    for ($i = 1; $i < count($dates); $i++) {
        $current = new DateTime($dates[$i]);
        $previous = new DateTime($dates[$i - 1]);
        $diff = $previous->diff($current)->days;

        if ($diff === 1) {
            $streak++;
        } else {
            break;
        }
    }

    return $streak;
}
