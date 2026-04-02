<?php

/**
 * api/user_xp.php
 *
 * API pour récupérer les statistiques XP et gamification (JSON)
 * Endpoints:
 *   GET /api/user_xp.php?user=ID → XP et stats de l'utilisateur
 *   GET /api/user_xp.php?user=ID&breakdown=1 → Avec détails par matière
 *   GET /api/user_xp.php?leaderboard=1 → Top 10 utilisateurs
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/connection.php';

api_require([
    'method' => 'GET',
]);

if (!isset($pdo) || !$pdo) {
    json_error('Base de données indisponible', 503, 'ERR_DB');
}

// Charger gamification helper
require_once __DIR__ . '/../includes/gamification.php';

$userId = isset($_GET['user']) ? (int) $_GET['user'] : null;
$breakdown = isset($_GET['breakdown']) ? true : false;
$leaderboard = isset($_GET['leaderboard']) ? true : false;

if ($userId) {
    require_auth();
    $sessionUserId = (int) ($_SESSION['user_id'] ?? 0);
    $role = $_SESSION['user_role'] ?? null;
    if ($sessionUserId !== $userId && $role !== 'admin') {
        json_error('Accès refusé', 403, 'ERR_FORBIDDEN');
    }
}

try {
    if ($leaderboard) {
        // Top 10 utilisateurs par XP
        $stmt = $pdo->query("
            SELECT u.Id, u.Name, up.xp, COUNT(up2.Id) as exercises_completed
            FROM users u
            LEFT JOIN UserProgress up ON u.Id = up.UserId
            LEFT JOIN UserProgress up2 ON u.Id = up2.UserId AND up2.IsComplete = 1
            WHERE u.is_active = 1
            GROUP BY u.Id
            ORDER BY up.xp DESC
            LIMIT 10
        ");

        $leaderboardData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Enrichir avec niveau
        foreach ($leaderboardData as &$user) {
            $user['level'] = function_exists('getUserLevel') ? getUserLevel($user['xp'] ?? 0) : 1;
        }

        $response = [
            'success' => true,
            'type' => 'leaderboard',
            'count' => count($leaderboardData),
            'data' => $leaderboardData,
        ];

    } elseif ($userId) {
        // Stats XP d'un utilisateur
        $stmt = $pdo->prepare("
            SELECT up.* FROM UserProgress up
            WHERE up.UserId = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $userProgress = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userProgress) {
            // Créer des stats vides pour cet utilisateur
            $userProgress = [
                'UserId' => $userId,
                'xp' => 0,
                'cristaux' => 0,
                'badges' => '[]',
            ];
        }

        // Récupérer les infos utilisateur
        $userStmt = $pdo->prepare("
            SELECT Id, Name, Email FROM users
            WHERE Id = ?
        ");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            json_error('Utilisateur non trouvé', 404, 'ERR_NOT_FOUND');
        }

        $xp = $userProgress['xp'] ?? 0;
        $userLevel = function_exists('getUserLevel') ? getUserLevel($xp) : 1;
        $nextLevelXp = function_exists('getNextLevelXP') ? getNextLevelXP($userLevel) : 1000;

        $response = [
            'success' => true,
            'user' => $user,
            'stats' => [
                'xp' => $xp,
                'level' => $userLevel,
                'crystals' => $userProgress['cristaux'] ?? 0,
                'badges' => json_decode($userProgress['badges'] ?? '[]', true) ?? [],
                'next_level_xp' => $nextLevelXp,
                'progress_percentage' => $nextLevelXp > 0 ? round(($xp / $nextLevelXp) * 100, 2) : 0,
            ],
        ];

        // Si breakdown demandé, ajouter stats par matière
        if ($breakdown) {
            $subjectStmt = $pdo->prepare("
                SELECT e.Subject, COUNT(*) as exercises, SUM(up.IsComplete ? 1 : 0) as completed, SUM(e.XP_Points) as total_xp
                FROM exercises e
                LEFT JOIN UserProgress up ON e.Id = up.ExerciseId AND up.UserId = ?
                GROUP BY e.Subject
                ORDER BY completed DESC
            ");
            $subjectStmt->execute([$userId]);
            $response['stats']['by_subject'] = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);
        }

    } else {
        json_error('Paramètres manquants', 400, 'ERR_VALIDATION');
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    json_error('Erreur lors de la récupération des statistiques XP', 500, 'ERR_SERVER');
}
