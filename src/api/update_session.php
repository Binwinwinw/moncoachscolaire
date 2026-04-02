<?php

/**
 * API Endpoint pour mettre à jour une session en cours
 * POST /api/update_session.php
 */

require_once __DIR__ . '/_core/bootstrap.php';
require_once __DIR__ . '/_core/response.php';
require_once __DIR__ . '/_core/middleware.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/connection.php';

api_require([
    'method' => 'POST',
    'auth' => true,
    'csrf' => true,
]);

// Récupérer les données JSON
$input = api_get_json_body(true);

if (!isset($input['session_id'])) {
    json_error('Missing session_id', 400, 'ERR_VALIDATION');
}

$sessionId = intval($input['session_id']);
$completionRate = isset($input['completion_rate']) ? intval($input['completion_rate']) : 0;

try {
    global $pdo;

    // Mettre à jour la completion rate sans terminer la session
    $stmt = $pdo->prepare("
        UPDATE study_sessions
        SET CompletionRate = ?
        WHERE Id = ? AND UserId = ?
    ");

    $stmt->execute([$completionRate, $sessionId, $_SESSION['user_id']]);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'session_id' => $sessionId,
        'completion_rate' => $completionRate,
    ]);

} catch (Exception $e) {
    error_log('Erreur update_session: ' . $e->getMessage());
    json_error('Internal server error', 500, 'ERR_SERVER');
}
