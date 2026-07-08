<?php

/**
 * API hub cours — miroir de api/exercices/get_exercises.php
 *
 * Actions : subjects | cours | cours_html
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

ob_start();

api_require([
    'method' => 'GET',
]);

$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/config/config.php';

if (file_exists($projectRoot . '/database/connection.php')) {
    require_once $projectRoot . '/database/connection.php';
}

require_once $projectRoot . '/includes/exercice_loader.php';
require_once $projectRoot . '/includes/cours_loader.php';
require_once $projectRoot . '/includes/course_markdown_loader.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'subjects':
            handleGetCourseSubjects();
            break;
        case 'cours':
            handleGetCourses();
            break;
        case 'cours_html':
            handleGetCourseHTML();
            break;
        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    error_log('API get_cours.php: ' . $e->getMessage() . ' | action=' . ($action ?: 'none'));
    json_error('Erreur lors du chargement des cours', 400, 'ERR_COURSES');
}

function handleGetCourseSubjects(): void
{
    $level = $_GET['level'] ?? '';
    if ($level === '') {
        throw new Exception('Niveau non spécifié');
    }

    if (is_file(dirname(__DIR__, 2) . '/includes/level_access.php')) {
        require_once dirname(__DIR__, 2) . '/includes/level_access.php';
        if (!can_current_user_access_level($level)) {
            json_error('Accès refusé : niveau supérieur au vôtre', 403, 'ERR_FORBIDDEN');
        }
    }

    $normalizedLevel = normalizeLevelForDB($level);
    if ($normalizedLevel === '') {
        throw new Exception('Niveau invalide');
    }

    $subjects = getCourseSubjectsByLevel($normalizedLevel);
    $subjectIcons = [
        'Mathématiques' => '🧮',
        'Français' => '📚',
        'Histoire-Géographie' => '🗺️',
        'Histoire' => '🗺️',
        'Géographie' => '🌍',
        'SVT' => '🔬',
        'Sciences' => '🔬',
        'Physique-Chimie' => '⚗️',
        'Anglais' => '🇬🇧',
        'Espagnol' => '🇪🇸',
        'Philosophie' => '💭',
    ];

    $subjectsData = [];
    foreach ($subjects as $subjectName) {
        $subjectsData[] = [
            'name' => $subjectName,
            'icon' => $subjectIcons[$subjectName] ?? '📚',
        ];
    }

    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    json_response([
        'level' => $normalizedLevel,
        'subjects' => $subjectsData,
    ]);
}

function handleGetCourses(): void
{
    global $pdo;

    if (!$pdo) {
        throw new Exception('Base de données non disponible');
    }

    $level = $_GET['level'] ?? '';
    $subject = isset($_GET['subject']) ? (string) $_GET['subject'] : null;

    if ($level === '') {
        throw new Exception('Niveau non spécifié');
    }

    $normalizedLevel = normalizeLevelForDB($level);
    $courses = getCoursesByLevel($normalizedLevel, $subject, 100);

    $coursesData = [];
    foreach ($courses as $course) {
        $coursesData[] = [
            'Id' => $course['Id'] ?? $course['id'] ?? null,
            'Title' => $course['Title'] ?? $course['title'] ?? null,
            'Subject' => $course['Subject'] ?? $course['subject'] ?? null,
            'Level' => $course['Level'] ?? $course['level'] ?? null,
            'Description' => $course['Description'] ?? $course['description'] ?? null,
        ];
    }

    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    json_response([
        'level' => $normalizedLevel,
        'subject' => $subject,
        'cours' => $coursesData,
        'count' => count($coursesData),
    ]);
}

function handleGetCourseHTML(): void
{
    global $pdo, $projectRoot;

    if (!$pdo) {
        throw new Exception('Base de données non disponible');
    }

    $courseId = (int) ($_GET['id'] ?? 0);
    if ($courseId <= 0) {
        throw new Exception('ID de cours non spécifié');
    }

    $coursCardPath = $projectRoot . '/includes/cours_card.php';
    if (!is_file($coursCardPath)) {
        throw new Exception('Fichier cours_card.php non trouvé');
    }
    require_once $coursCardPath;

    $course = getCourseById($courseId, true);
    if (!$course) {
        throw new Exception('Cours non trouvé');
    }

    if (is_file($projectRoot . '/includes/level_access.php')) {
        require_once $projectRoot . '/includes/level_access.php';
        if (!can_current_user_access_level($course['Level'] ?? $course['level'] ?? '')) {
            json_error('Accès refusé : niveau supérieur au vôtre', 403, 'ERR_FORBIDDEN');
        }
    }

    ob_start();
    renderCourseCard($course, ['showDetails' => true]);
    $html = ob_get_clean();

    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    json_response([
        'courseId' => $courseId,
        'html' => $html,
    ]);
}
