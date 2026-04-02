<?php

/**
 * API: Récupérer le cours associé à un exercice
 * GET /src/api/exercices/get_course.php?exercise_id=123
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/middleware.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../database/connection.php';
require_once __DIR__ . '/../../includes/response.php';
header('Content-Type: application/json; charset=utf-8');
if (false) {
    echo json_encode(['wrapper' => true]);
}
header('Content-Type: application/json; charset=utf-8');

api_require([
    'method' => 'GET',
]);

try {
    // Récupérer l'ID de l'exercice
    $exerciseId = $_GET['exercise_id'] ?? null;

    if (!$exerciseId) {
        sendJsonResponse(false, null, 'ID exercice manquant', 400);
    }

    // Récupérer l'exercice avec son cours
    $stmt = $pdo->prepare("
        SELECT
            e.ExerciseID,
            e.Title as exercise_title,
            e.Subject,
            e.Level,
            e.Competence,
            e.course_id,
            c.id as course_id,
            c.subject as course_subject,
            c.level as course_level,
            c.competence as course_competence,
            c.section as course_section,
            c.key_point,
            c.explanation,
            c.example,
            c.formula
        FROM exercises e
        LEFT JOIN courses c ON e.course_id = c.id
        WHERE e.ExerciseID = :exercise_id
        AND e.is_active = 'true'
    ");

    $stmt->execute(['exercise_id' => $exerciseId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        sendJsonResponse(false, null, 'Exercice introuvable', 404);
    }

    // Si pas de cours lié
    if (!$result['course_id']) {
        sendJsonResponse(true, [
            'has_course' => false,
            'exercise' => [
                'id' => $result['ExerciseID'],
                'title' => $result['exercise_title'],
                'subject' => $result['Subject'],
                'level' => $result['Level'],
                'competence' => $result['Competence'],
            ],
        ]);
    }

    // Cours trouvé
    sendJsonResponse(true, [
        'has_course' => true,
        'exercise' => [
            'id' => $result['ExerciseID'],
            'title' => $result['exercise_title'],
            'subject' => $result['Subject'],
            'level' => $result['Level'],
            'competence' => $result['Competence'],
        ],
        'course' => [
            'id' => $result['course_id'],
            'subject' => $result['course_subject'],
            'level' => $result['course_level'],
            'competence' => $result['course_competence'],
            'section' => $result['course_section'],
            'key_point' => $result['key_point'],
            'explanation' => $result['explanation'],
            'example' => $result['example'],
            'formula' => $result['formula'],
        ],
    ]);

} catch (Exception $e) {
    error_log("Erreur get_course: " . $e->getMessage());
    sendJsonResponse(false, null, 'Erreur serveur', 500);
}
