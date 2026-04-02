<?php

/**
 * API ADMIN: Validation d'exercice avant insertion
 * Endpoint: /api/admin/validate_exercise.php
 * Méthode: POST
 * Sécurité: Admin uniquement
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

api_require([
    'method' => 'POST',
    'auth' => true,
    'csrf' => true,
]);

$role = $_SESSION['user_role'] ?? ($_SESSION['role'] ?? ($_SESSION['user_type'] ?? null));
if ($role !== 'admin') {
    json_error('Accès réservé aux administrateurs', 403, 'ERR_FORBIDDEN');
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ExerciseValidator.php';
header('Content-Type: application/json; charset=utf-8');

// Récupérer les données JSON
$input = api_get_json_body(true);

if (!$input) {
    json_error('Données JSON invalides', 400, 'ERR_JSON');
}

// Préparer l'exercice pour validation
$exercise = [
    'title' => $input['title'] ?? '',
    'content' => $input['content'] ?? '',
    'answer' => $input['answer'] ?? '',
    'level' => $input['level'] ?? '',
    'subject' => $input['subject'] ?? '',
];

// Valider l'exercice
$validation = ExerciseValidator::validate($exercise);

// Retourner le résultat
json_response([
    'validation' => $validation,
    'canInsert' => $validation['valid'],
    'timestamp' => date('Y-m-d H:i:s'),
]);
