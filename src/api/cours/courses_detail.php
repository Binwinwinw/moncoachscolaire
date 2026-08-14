<?php

/**
 * api/courses_detail.php
 *
 * API pour récupérer les détails des cours (JSON)
 * Endpoints:
 *   GET /api/courses_detail.php?id=ID → Détails d'un cours
 *   GET /api/courses_detail.php?level=6ème → Cours d'un niveau
 *   GET /api/courses_detail.php?subject=Maths → Cours d'une matière
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';
// @deprecated Préférer api/cours/get_cours.php pour le hub ?page=cours
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../database/connection.php';

api_require([
    'method' => 'GET',
]);

if (!isset($pdo) || !$pdo) {
    json_error('Base de données indisponible', 503, 'ERR_DB');
}

$levelAccessFile = __DIR__ . '/../../includes/level_access.php';
if (!is_file($levelAccessFile)) {
    json_error('Autorisation introuvable', 500, 'ERR_AUTH_MISSING');
}
require_once $levelAccessFile;
if (!function_exists('can_current_user_access_level')) {
    json_error('Autorisation introuvable', 500, 'ERR_AUTH_MISSING');
}

$courseId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$level = $_GET['level'] ?? null;
$subject = $_GET['subject'] ?? null;

try {
    if ($courseId) {
        // Détails d'un cours avec ses ressources
        $stmt = $pdo->prepare("
            SELECT c.* FROM courses c
            WHERE c.Id = ? AND c.is_active = 1
        ");
        $stmt->execute([$courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            json_error('Cours introuvable', 404, 'ERR_NOT_FOUND');
        }

        // Vérifier l'accès par niveau (API)
        if (!can_current_user_access_level($course['Level'] ?? $course['level'] ?? '')) {
            json_error('Accès refusé : niveau supérieur au vôtre', 403, 'ERR_FORBIDDEN');
        }

        // Récupérer les exercices du cours
        $exStmt = $pdo->prepare("
            SELECT e.* FROM exercises e
            INNER JOIN exercisecourselinks ecl ON e.Id = ecl.ExerciseId
            WHERE ecl.CourseId = ?
            ORDER BY e.Difficulty DESC
        ");
        $exStmt->execute([$courseId]);
        $course['exercises'] = $exStmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les ressources du cours
        $resStmt = $pdo->prepare("
            SELECT * FROM CourseExternalResources
            WHERE CourseId = ? AND IsActive = 1
            ORDER BY ResourceType
        ");
        $resStmt->execute([$courseId]);
        $course['resources'] = $resStmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'success' => true,
            'data' => $course,
        ];
    } elseif ($level || $subject) {
        // Lister les cours selon critères
        // Vérifier si le niveau demandé est supérieur à celui de l'utilisateur
        if (!$level || !can_current_user_access_level($level)) {
            json_error('Accès refusé : un niveau autorisé est requis', 403, 'ERR_FORBIDDEN');
        }

        $sql = "SELECT c.* FROM courses c WHERE c.is_active = 1";
        $params = [];

        if ($level) {
            $sql .= " AND c.Level = ?";
            $params[] = $level;
        }
        if ($subject) {
            $sql .= " AND c.Subject = ?";
            $params[] = $subject;
        }

        $sql .= " ORDER BY c.Title";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'success' => true,
            'count' => count($courses),
            'filters' => [],
            'data' => $courses,
        ];

        if ($level) {
            $response['filters']['level'] = $level;
        }
        if ($subject) {
            $response['filters']['subject'] = $subject;
        }
    } else {
        // Tous les cours
        json_error('Accès refusé : un niveau autorisé est requis', 403, 'ERR_FORBIDDEN');

        $stmt = $pdo->query("
            SELECT * FROM courses
            WHERE is_active = 1
            ORDER BY Title
            LIMIT 50
        ");
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'success' => true,
            'count' => count($courses),
            'data' => $courses,
        ];
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la récupération des cours',
        'code' => 'ERR_COURSES',
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
