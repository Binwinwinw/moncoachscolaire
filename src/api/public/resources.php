<?php

/**
 * api/resources.php
 *
 * API pour récupérer les ressources externes (JSON)
 * Endpoints:
 *   GET /api/resources.php → Toutes les ressources
 *   GET /api/resources.php?type=video → Ressources par type
 *   GET /api/resources.php?course=ID → Ressources d'un cours
 *   GET /api/resources.php?exercise=ID → Ressources d'un exercice
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

// Charger les helpers
require_once __DIR__ . '/../includes/resource_display.php';
require_once __DIR__ . '/../includes/course_display.php';

$type = $_GET['type'] ?? null;
$courseId = isset($_GET['course']) ? (int) $_GET['course'] : null;
$exerciseId = isset($_GET['exercise']) ? (int) $_GET['exercise'] : null;

try {
    if ($exerciseId) {
        // Ressources d'un exercice (via son cours)
        $resources = getExerciseExternalResources($exerciseId);
    } elseif ($courseId) {
        // Ressources d'un cours spécifique
        $resources = getCourseExternalResources($courseId);
    } elseif ($type) {
        // Ressources par type
        $resources = getResourcesByType($type, 50);
    } else {
        // Toutes les ressources
        $stmt = $pdo->prepare("
            SELECT * FROM CourseExternalResources
            WHERE IsActive = 1
            ORDER BY Title
            LIMIT 100
        ");
        $stmt->execute();
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter des statistiques
    $response = [
        'success' => true,
        'count' => count($resources),
        'data' => $resources,
    ];

    if ($type) {
        $response['filter'] = ['type' => $type];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    json_error('Erreur lors de la récupération des ressources', 500, 'ERR_RESOURCES');
}
