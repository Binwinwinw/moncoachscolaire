<?php

/**
 * Endpoint pour sauvegarder un cours généré par l'IA dans la bibliothèque
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ai_course_generator.php';
require_once __DIR__ . '/../../includes/exercice_loader.php';
if (is_file(__DIR__ . '/../../includes/login_security.php')) {
    require_once __DIR__ . '/../../includes/login_security.php';
}
require_once __DIR__ . '/../_core/bootstrap.php';

api_require([
    'method' => 'POST',
    'auth' => true,
    'csrf' => true,
    'rate' => [
        'key' => 'ai_save_course',
        'limit' => 20,
        'window' => 60,
    ],
]);

try {
    $input = api_get_json_body(true);
    if (!is_array($input)) {
        throw new Exception('JSON invalide.');
    }

    $course = $input['cours'] ?? $input['course'] ?? null;
    $subject = $input['matiere'] ?? $input['subject'] ?? '';
    $level = $input['level'] ?? $input['niveau'] ?? '';

    if (!is_array($course) || empty($course['title'])) {
        throw new Exception('Aucun cours à sauvegarder.');
    }

    if ($subject === '' || $level === '') {
        throw new Exception('Sujet ou niveau manquant.');
    }

    global $pdo;
    $pdoConnection = $pdo instanceof PDO ? $pdo : null;
    if (!$pdoConnection instanceof PDO) {
        throw new Exception('Connexion à la base de données impossible.');
    }

    $levelDb = function_exists('normalizeLevelForDB') ? normalizeLevelForDB($level) : $level;
    $subjectDb = function_exists('normalizeSubject') ? normalizeSubject($subject) : $subject;

    $course['subject'] = $course['subject'] ?? $subjectDb;
    $course['level'] = $course['level'] ?? $levelDb;

    $courseId = saveCourseToDatabase($course);
    $revisionId = 0;
    try {
        if (!empty($_SESSION['user_id']) && function_exists('saveAiRevision')) {
            $revisionId = saveAiRevision(
                (int) $_SESSION['user_id'],
                'course',
                $course['title'] ?? 'Cours IA généré',
                $subjectDb,
                $levelDb,
                (int) $courseId,
                [],
                ['source' => 'save_generated_cours']
            );
        }
    } catch (Throwable $revisionError) {
        error_log('ai_revision save error: ' . $revisionError->getMessage());
    }

    $courseUrl = function_exists('site_url')
        ? site_url('view_course', ['id' => (int) $courseId])
        : 'index.php?page=view_course&id=' . (int) $courseId;

    api_additive_response([
        'success' => true,
        'course_id' => (int) $courseId,
        'course_url' => $courseUrl,
        'revision_id' => $revisionId,
        'message' => 'Cours sauvegardé dans la bibliothèque.',
        'data' => [
            'course_id' => (int) $courseId,
            'course_url' => $courseUrl,
            'revision_id' => $revisionId,
        ],
        'meta' => [
            'saved_at' => date('c'),
            'level' => $levelDb,
            'subject' => $subjectDb,
        ],
    ], 200);
} catch (Throwable $e) {
    api_additive_error($e->getMessage(), 400);
}
