<?php

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/deprecated.php';

api_deprecated('/api/exercices/save-progress');
json_error('Endpoint obsolète. Merci d’utiliser la nouvelle API.', 410, 'ERR_GONE');
exit;

api_require([
    'method' => 'POST',
    'auth' => true,
    'roles' => ['student', 'admin'],
    'csrf' => true,
    'rate' => ['key' => 'legacy_save_progress', 'limit' => 30, 'window' => 60],
]);

$data = api_get_json_body(true);

$exerciseId = isset($data['exerciseId']) ? (int) $data['exerciseId'] : null;
$score = isset($data['score']) ? (int) $data['score'] : 0;
$correct = isset($data['correct']) ? (int) $data['correct'] : 0;

// Simple session-based user detection (header.php handles session start)
$userId = $_SESSION['user_id'] ?? null;

// If DB available and user logged-in, persist there
if (isset($pdo) && $pdo && $userId) {
    try {
        $stmt = $pdo->prepare('INSERT INTO ExerciseResponses (UserId, ExerciseId, Correct, Score) VALUES (:uid, :eid, :correct, :score)');
        $stmt->execute([':uid' => $userId, ':eid' => $exerciseId, ':correct' => $correct, ':score' => $score]);
        echo json_encode(['ok' => true, 'message' => 'Saved to DB']);
        exit;
    } catch (PDOException $e) {
        json_error('Erreur serveur', 500, 'ERR_DB');
    }
}

// If DB not available or no user logged in, fallback to a local file for later processing
$storeDir = __DIR__ . '/../data';
if (!is_dir($storeDir)) {
    @mkdir($storeDir, 0755, true);
}
$file = $storeDir . '/unsaved_progress.json';
$payload = ['exerciseId' => $exerciseId, 'score' => $score, 'correct' => $correct, 'userId' => $userId, 'at' => date('c')];

$arr = [];
if (file_exists($file)) {
    $cur = @file_get_contents($file);
    $arr = json_decode($cur, true) ?: [];
}
$arr[] = $payload;
@file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT));

// If user not logged in, require login for real DB save — but we accepted the fallback
json_response(['saved' => 'file', 'message' => 'Progression sauvegardée localement'], 202);
