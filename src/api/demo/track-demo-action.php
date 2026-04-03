<?php

/**
 * API pour tracker les actions en mode démo
 * Sauvegarde le nombre d'actions dans la session
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

api_require([
    'method' => 'POST',
    'csrf' => true,
]);

header('Content-Type: application/json; charset=utf-8');

// Charger le système de sécurité démo
require_once __DIR__ . '/../../includes/demo_security.php';

// SÉCURITÉ : Vérifier que l'utilisateur est bien en mode démo
if (!isDemoUser()) {
    json_error('Accès refusé', 403, 'ERR_FORBIDDEN');
}

// Lire les données JSON
$data = api_get_json_body(true);

if (!isset($data['action']) || !isset($data['count'])) {
    json_error('Données invalides', 400, 'ERR_VALIDATION');
}

// Sauvegarder dans la session
$_SESSION['demo_action_count'] = (int) $data['count'];

// Répondre avec succès
echo json_encode([
    'success' => true,
    'action_count' => $_SESSION['demo_action_count'],
]);
