<?php

/**
 * API: Obtenir la progression de l'utilisateur
 *
 * Requête: GET /api/get_user_progress.php?user_id=1&level=1ere&subject=Mathematiques
 * Réponse: JSON avec progression XP, badges, notions
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';
require_once __DIR__ . '/../database/connection.php';

api_require([
    'method' => 'GET',
    'auth' => true,
]);

try {
    if (!isset($pdo) || !$pdo) {
        json_error('Base de données indisponible', 503, 'ERR_DB');
    }

    $userId = $_GET['user_id'] ?? null;
    $level = $_GET['level'] ?? null;
    $subject = $_GET['subject'] ?? null;

    if (!$userId) {
        json_error('user_id requis', 400, 'ERR_VALIDATION');
    }

    $sessionUserId = (int) ($_SESSION['user_id'] ?? 0);
    $role = $_SESSION['user_role'] ?? null;
    if ($sessionUserId !== (int) $userId && $role !== 'admin') {
        json_error('Accès refusé', 403, 'ERR_FORBIDDEN');
    }

    // Vérifier que l'utilisateur existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Utilisateur non trouvé');
    }

    $response = [
        'user_id' => (int) $userId,
        'total_xp' => 0,
        'total_exercises' => 0,
        'exercises_completed' => 0,
        'level_progress' => null,
        'badges_earned' => [],
        'notions_progress' => [],
    ];

    // 1. XP total
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(xp_earned), 0) as total FROM mastery WHERE user_id = ?");
    $stmt->execute([$userId]);
    $response['total_xp'] = (int) $stmt->fetch()['total'];

    // 2. Exercices total et complétés
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM exercises");
    $stmt->execute();
    $response['total_exercises'] = (int) $stmt->fetch()['total'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM mastery WHERE user_id = ? AND xp_earned > 0");
    $stmt->execute([$userId]);
    $response['exercises_completed'] = (int) $stmt->fetch()['count'];

    // 3. Badges gagnés
    $stmt = $pdo->prepare("
        SELECT b.id, b.name, b.icon_emoji, ub.earned_at
        FROM UserBadge ub
        JOIN Badge b ON ub.badge_id = b.id
        WHERE ub.user_id = ?
        ORDER BY ub.earned_at DESC
    ");
    $stmt->execute([$userId]);
    $response['badges_earned'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Si level spécifié, progression par niveau
    if ($level) {
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as exercises,
                SUM(CASE WHEN xp_earned > 0 THEN 1 ELSE 0 END) as completed,
                ROUND(AVG(best_score), 2) as avg_score
            FROM Mastery m
            JOIN exercises e ON m.exercise_id = e.id
            WHERE m.user_id = ? AND e.Level = ?
        ");
        $stmt->execute([$userId, $level]);
        $levelData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($levelData) {
            $response['level_progress'] = [
                'level' => $level,
                'exercises' => (int) $levelData['exercises'],
                'completed' => (int) $levelData['completed'] ?? 0,
                'avg_score' => (float) $levelData['avg_score'] ?? 0,
            ];
        }
    }

    // 5. Si subject spécifié, progression par notion
    if ($subject && $level) {
        $stmt = $pdo->prepare("
            SELECT
                n.id,
                n.name,
                n.difficulty,
                COUNT(m.id) as attempts,
                ROUND(AVG(m.best_score), 2) as avg_score,
                COALESCE(SUM(m.xp_earned), 0) as total_xp
            FROM Notion n
            LEFT JOIN ExerciseNotion en ON n.id = en.notion_id
            LEFT JOIN Mastery m ON en.exercise_id = m.exercise_id AND m.user_id = ?
            WHERE n.subject = ? AND n.level = ?
            GROUP BY n.id
            ORDER BY n.order_index
        ");
        $stmt->execute([$userId, $subject, $level]);
        $response['notions_progress'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retourner JSON
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
            } else {
                return $data;
            }
        }
    }
    $response = clean_utf8_recursive($response);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération de la progression',
        'code' => 'ERR_PROGRESS',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
