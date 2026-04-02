<?php

/**
 * API: Logger un essai d'exercice et calculer l'XP
 * - Enregistre l'essai dans exerciseresponses (avec temps, source, device, notion)
 * - Met à jour mastery (agrégats par exercice)
 * - Met à jour userprogress (XP total)
 *
 * POST/JSON:
 *   exercise_id (int, requis)
 *   score (0-100, optionnel, défaut 100 si correct=true)
 *   correct (bool, optionnel, défaut true)
 *   time_spent_seconds (int, optionnel)
 *   source (string, optionnel)
 *   device (string, optionnel)
 *   notion_id (int, optionnel)
 *   metadata (string/JSON, optionnel)
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

api_require([
    'method' => 'POST',
    'auth' => true,
    'csrf' => true,
]);

header('Content-Type: application/json; charset=utf-8');

$respond = function (int $code, array $data) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
};

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    $respond(401, ['success' => false, 'error' => 'Non connecté']);
}

// Connexion DB
if (file_exists(__DIR__ . '/../database/connection.php')) {
    require_once __DIR__ . '/../database/connection.php';
} elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
    require_once __DIR__ . '/../../db/connection.php';
}
if (!isset($pdo) || !$pdo) {
    $respond(503, ['success' => false, 'error' => 'Base de données indisponible']);
}

// Charger système de badges
require_once __DIR__ . '/../includes/badge_system.php';

// Sécurité compte démo (si disponible)
if (file_exists(__DIR__ . '/../includes/demo_security.php')) {
    require_once __DIR__ . '/../includes/demo_security.php';
    if (function_exists('isDemoAccount') && (isDemoAccount($userId) || (function_exists('isDemoUser') && isDemoUser()))) {
        $respond(403, ['success' => false, 'error' => 'Mode démo : écriture désactivée']);
    }
}

// Payload JSON ou POST
$input = [];
if (!empty($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}
$input = array_merge($_POST, $input);

$exerciseId = isset($input['exercise_id']) ? (int) $input['exercise_id'] : 0;
$correct = isset($input['correct']) ? filter_var($input['correct'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : true;
$score = isset($input['score']) ? (float) $input['score'] : ($correct ? 100.0 : 0.0);
$timeSpent = isset($input['time_spent_seconds']) ? (int) $input['time_spent_seconds'] : 0;
$source = isset($input['source']) ? trim((string) $input['source']) : null;
$device = isset($input['device']) ? trim((string) $input['device']) : null;
$notionId = isset($input['notion_id']) ? (int) $input['notion_id'] : null;
$metadata = isset($input['metadata']) ? (string) $input['metadata'] : null;

if ($exerciseId <= 0) {
    $respond(400, ['success' => false, 'error' => 'exercise_id requis']);
}

$score = max(0, min(100, $score));
$timeSpent = max(0, $timeSpent);

try {
    // Vérifier l'exercice
    $stmt = $pdo->prepare('SELECT Id, XP_Points FROM exercises WHERE Id = ?');
    $stmt->execute([$exerciseId]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$exercise) {
        $respond(404, ['success' => false, 'error' => 'Exercice introuvable']);
    }
    $baseXp = (int) ($exercise['XP_Points'] ?? 10);
    $xpEarned = (int) round($baseXp * ($score / 100));

    $pdo->beginTransaction();

    // Log dans exerciseresponses
    $insert = $pdo->prepare('INSERT INTO exerciseresponses (UserId, ExerciseId, Correct, Score, TimeSpentSeconds, XpEarned, Source, Device, NotionId, Metadata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insert->execute([
        $userId,
        $exerciseId,
        $correct ? 1 : 0,
        $score,
        $timeSpent,
        $xpEarned,
        $source,
        $device,
        $notionId ?: null,
        $metadata,
    ]);

    // Upsert mastery
    $stmt = $pdo->prepare('SELECT id, attempts, best_score, xp_earned, total_time_seconds FROM mastery WHERE user_id = ? AND exercise_id = ?');
    $stmt->execute([$userId, $exerciseId]);
    $mastery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mastery) {
        $newAttempts = ((int) $mastery['attempts']) + 1;
        $newBest = max((float) $mastery['best_score'], $score);
        $newXp = ((int) $mastery['xp_earned']) + $xpEarned;
        $newTime = ((int) $mastery['total_time_seconds']) + $timeSpent;
        $upd = $pdo->prepare('UPDATE mastery SET attempts = ?, best_score = ?, xp_earned = ?, total_time_seconds = ?, last_attempt_at = NOW(), updated_at = NOW() WHERE id = ?');
        $upd->execute([$newAttempts, $newBest, $newXp, $newTime, $mastery['id']]);
    } else {
        $ins = $pdo->prepare('INSERT INTO mastery (user_id, exercise_id, level, xp_earned, attempts, best_score, total_time_seconds, last_attempt_at, created_at, updated_at) VALUES (?, ?, "beginner", ?, 1, ?, ?, NOW(), NOW(), NOW())');
        $ins->execute([$userId, $exerciseId, $xpEarned, $score, $timeSpent]);
        $newAttempts = 1;
        $newBest = $score;
        $newXp = $xpEarned;
        $newTime = $timeSpent;
    }

    // Upsert userprogress (XP total)
    $stmt = $pdo->prepare('SELECT Id, XP FROM userprogress WHERE UserId = ?');
    $stmt->execute([$userId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($progress) {
        $totalXp = ((int) $progress['XP']) + $xpEarned;
        $upd = $pdo->prepare('UPDATE userprogress SET XP = ?, UpdatedAt = NOW() WHERE Id = ?');
        $upd->execute([$totalXp, $progress['Id']]);
    } else {
        $totalXp = $xpEarned;
        $ins = $pdo->prepare('INSERT INTO userprogress (UserId, XP, CurrentPosition, UpdatedAt) VALUES (?, ?, 1, NOW())');
        $ins->execute([$userId, $totalXp]);
    }

    $pdo->commit();

    // Vérifier et attribuer badges automatiques
    $newBadges = [];
    if (function_exists('checkAndAwardBadges')) {
        $newBadges = checkAndAwardBadges($userId);
    }

    $respond(200, [
        'success' => true,
        'exercise_id' => $exerciseId,
        'xp_earned' => $xpEarned,
        'total_xp' => $totalXp,
        'badges_earned' => $newBadges,
        'mastery' => [
            'attempts' => $newAttempts,
            'best_score' => $newBest,
            'xp_earned' => $newXp,
            'total_time_seconds' => $newTime,
        ],
        'meta' => [
            'correct' => (bool) $correct,
            'score' => $score,
            'time_spent_seconds' => $timeSpent,
            'source' => $source,
            'device' => $device,
            'notion_id' => $notionId,
        ],
    ]);
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('log_exercise_result error: ' . $e->getMessage());
    $respond(500, ['success' => false, 'error' => 'Erreur serveur', 'message' => $e->getMessage()]);
}
