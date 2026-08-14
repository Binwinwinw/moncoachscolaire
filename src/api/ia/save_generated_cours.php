<?php

/**
 * Endpoint pour sauvegarder un cours généré par l'IA dans les révisions privées
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

    $revisionId = saveAiRevision(
        (int) $_SESSION['user_id'],
        'course',
        (string) ($course['title'] ?? 'Cours IA généré'),
        (string) $subjectDb,
        (string) $levelDb,
        null,
        [],
        ['source' => 'save_generated_cours'],
        $course
    );

    if ($revisionId <= 0) {
        throw new RuntimeException('Impossible de sauvegarder le brouillon du cours.');
    }

    $revisionUrl = function_exists('site_url')
        ? site_url('revisions', ['id' => $revisionId])
        : 'index.php?page=revisions&id=' . $revisionId;

    api_additive_response([
        'success' => true,
        'revision_id' => $revisionId,
        'revision_url' => $revisionUrl,
        'message' => 'Cours sauvegardé dans tes révisions privées.',
        'data' => [
            'revision_id' => $revisionId,
            'revision_url' => $revisionUrl,
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
