<?php

/**
 * API pour sauvegarder la progression d'un exercice
 * Endpoint AJAX pour marquer un exercice comme complété
 */

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
    'rate' => ['key' => 'legacy_save_exercise_progress', 'limit' => 30, 'window' => 60],
]);

function sendLegacyJson(array $payload): void
{
    header('Content-Type: application/json; charset=utf-8');
    $payload['request_id'] = api_request_id();
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Charger les dépendances
// Connexion DB (préférence src/database, fallback legacy db/connection.php)
if (file_exists(__DIR__ . '/../database/connection.php')) {
    require_once __DIR__ . '/../database/connection.php';
} elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
    require_once __DIR__ . '/../../db/connection.php';
}
require_once __DIR__ . '/../includes/exercice_loader.php';
require_once __DIR__ . '/../includes/gamification.php';

// Récupérer les données POST
$exerciseId = isset($_POST['exercise_id']) ? (int) $_POST['exercise_id'] : 0;
$correct = isset($_POST['correct']) ? filter_var($_POST['correct'], FILTER_VALIDATE_BOOLEAN) : true;

if (!$exerciseId) {
    echo json_encode(['success' => false, 'error' => 'ID exercice manquant']);
    exit;
}

// Charger l'exercice pour extraire les récompenses
$exercise = getExerciseById($exerciseId);
if (!$exercise) {
    echo json_encode(['success' => false, 'error' => 'Exercice non trouvé']);
    exit;
}

// Extraire les récompenses depuis les métadonnées de l'exercice
// Pour l'instant, valeurs par défaut (sera amélioré avec extraction depuis Markdown)
$rewards = [
    'cristaux' => 25,
    'xp' => 15,
    'badge' => null,
];

// Marquer l'exercice comme complété
$result = completeExercise($userId, $exerciseId, $correct, $rewards);

// Récupérer la progression mise à jour
$progress = getUserProgress($userId);

$response = [
    'success' => $result['success'],
    'exercise_id' => $exerciseId,
    'cristaux' => $result['cristaux'] ?? 0,
    'xp' => $result['xp'] ?? 0,
    'badge_unlocked' => $result['badge_unlocked'] ?? false,
    'badge_name' => $result['badge_name'] ?? null,
    'progress' => $progress,
    'message' => $result['success']
        ? 'Exercice complété avec succès !'
        : ($result['error'] ?? 'Erreur inconnue'),
];

// Gérer le cas "déjà complété"
if (isset($result['already_completed']) && $result['already_completed']) {
    $response['message'] = 'Exercice déjà complété précédemment.';
    $response['already_completed'] = true;
}

sendLegacyJson($response);
