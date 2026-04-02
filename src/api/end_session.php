<?php

/**
 * API Endpoint pour terminer une session d'étude
 * POST /api/end_session.php
 */

require_once __DIR__ . '/_core/bootstrap.php';
require_once __DIR__ . '/_core/response.php';
require_once __DIR__ . '/_core/middleware.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/study_tracker.php';

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
$completionRate = isset($input['completion_rate']) ? intval($input['completion_rate']) : 100;
$score = isset($input['score']) ? intval($input['score']) : null;

try {
    // Terminer la session
    $result = endStudySession($sessionId, $score, $completionRate);

    if ($result === false) {
        json_error('Session not found', 404, 'ERR_NOT_FOUND');
    }

    // Supprimer de la session PHP
    unset($_SESSION['current_study_session']);

    // Réponse de succès
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'session_id' => $sessionId,
        'duration' => $result['duration'],
        'completion_rate' => $result['completion_rate'],
        'message' => 'Session terminée avec succès',
    ]);

} catch (Exception $e) {
    error_log('Erreur end_session: ' . $e->getMessage());
    json_error('Internal server error', 500, 'ERR_SERVER');
}
exit;
