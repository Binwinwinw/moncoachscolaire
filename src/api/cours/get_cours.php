<?php

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

handleCoursApiRequest();

function handleCoursApiRequest(): void
{
    ensureGetMethod();

    $projectRoot = dirname(__DIR__, 2);
    require_once $projectRoot . '/config/config.php';

    if (file_exists($projectRoot . '/database/connection.php')) {
        require_once $projectRoot . '/database/connection.php';
    }

    require_once $projectRoot . '/includes/exercice_loader.php';
    require_once $projectRoot . '/includes/cours_loader.php';
    require_once $projectRoot . '/includes/course_markdown_loader.php';

    $action = getQueryString('action');
    if ($action === '') {
        sendError('Action manquante', 400, 'ERR_MISSING_ACTION');
    }

    try {
        dispatchCoursAction($action, $projectRoot);
    } catch (Throwable $e) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        error_log('[Cours API] ' . $e->getMessage() . ' | action=' . ($action ?: 'none'));
        sendError('Erreur lors du traitement de la requête', 400, 'ERR_COURS_API');
    }
}

function dispatchCoursAction(string $action, string $projectRoot): void
{
    $routes = [
        'subjects' => 'serveSubjectsList',
        'cours' => 'serveCourseList',
        'cours_html' => 'serveCourseHtml',
    ];

    if (!isset($routes[$action])) {
        sendError('Action inconnue', 400, 'ERR_UNKNOWN_ACTION');
    }

    $routes[$action]($projectRoot);
}

function serveSubjectsList(string $projectRoot): void
{
    $level = normalizeQueryString(getQueryString('level'));
    if ($level === null) {
        sendError('Niveau absent ou invalide', 400, 'ERR_BAD_LEVEL');
    }

    authorizeLevel($level, $projectRoot);
    $subjects = getCourseSubjectsByLevel($level);

    $payload = array_map('formatSubjectItem', $subjects);

    sendSuccess([
        'niveau' => $level,
        'subjects' => $payload,
    ]);
}

function serveCourseList(string $projectRoot): void
{
    global $pdo;

    if (!$pdo) {
        sendError('Connexion PDO indisponible', 500, 'ERR_DB');
    }

    $level = normalizeQueryString(getQueryString('level'));
    if ($level === null) {
        sendError('Niveau absent ou invalide', 400, 'ERR_BAD_LEVEL');
    }

    $subject = normalizeQueryString(getQueryString('subject'));
    authorizeLevel($level, $projectRoot);

    $courses = getCoursesByLevel($level, $subject, 100);
    $payload = array_map('formatCourseRecord', $courses);

    sendSuccess([
        'niveau' => $level,
        'subject' => $subject,
        'cours' => $payload,
        'count' => count($payload),
    ]);
}

function serveCourseHtml(string $projectRoot): void
{
    global $pdo;

    if (!$pdo) {
        sendError('Connexion PDO indisponible', 500, 'ERR_DB');
    }

    $courseId = getQueryInt('id');
    if ($courseId <= 0) {
        sendError('Identifiant de cours invalide', 400, 'ERR_BAD_ID');
    }

    $templatePath = $projectRoot . '/includes/cours_card.php';
    if (!is_readable($templatePath)) {
        sendError('Fichier de rendu introuvable', 500, 'ERR_TEMPLATE_MISSING');
    }
    require_once $templatePath;

    $course = getCourseById($courseId, true);
    if (!$course) {
        sendError('Cours introuvable', 404, 'ERR_NOT_FOUND');
    }

    $level = normalizeQueryString((string) ($course['Level'] ?? $course['level'] ?? ''));
    authorizeLevel($level, $projectRoot);

    ob_start();
    renderCourseCard($course, ['showDetails' => true]);
    $html = ob_get_clean();

    sendSuccess([
        'courseId' => $courseId,
        'html' => $html,
    ]);
}

function ensureGetMethod(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Méthode HTTP non autorisée', 405, 'ERR_INVALID_METHOD');
    }
}

function getQueryString(string $key): string
{
    return trim((string) ($_GET[$key] ?? ''));
}

function getQueryInt(string $key): int
{
    return filter_var($_GET[$key] ?? null, FILTER_VALIDATE_INT) ?: 0;
}

function normalizeQueryString(string $value): ?string
{
    $clean = preg_replace('/\s+/', ' ', trim($value));
    return $clean === '' ? null : $clean;
}

function authorizeLevel(string $level = null, string $projectRoot = null): void
{
    if ($level === null) {
        return;
    }

    $accessFile = $projectRoot . '/includes/level_access.php';
    if (!is_file($accessFile)) {
        sendError('Autorisation introuvable', 500, 'ERR_AUTH_MISSING');
    }

    require_once $accessFile;
    if (!function_exists('can_current_user_access_level') || !function_exists('get_level_order')) {
        sendError('Autorisation introuvable', 500, 'ERR_AUTH_MISSING');
    }

    if (get_level_order($level) === null) {
        sendError('Niveau absent ou invalide', 400, 'ERR_BAD_LEVEL');
    }

    if (!can_current_user_access_level($level)) {
        sendError('Accès refusé : niveau non autorisé', 403, 'ERR_FORBIDDEN');
    }
}

function formatSubjectItem(string $subject): array
{
    $symbols = [
        'Mathématiques' => '🧮',
        'Français' => '📖',
        'Histoire-Géographie' => '🗺️',
        'SVT' => '🔬',
        'Physique-Chimie' => '⚗️',
        'Anglais' => '🇬🇧',
        'Espagnol' => '🇪🇸',
        'Philosophie' => '💭',
    ];

    return [
        'name' => $subject,
        'icon' => $symbols[$subject] ?? '📘',
    ];
}

function formatCourseRecord(array $course): array
{
    return [
        'Id' => $course['Id'] ?? $course['id'] ?? null,
        'Title' => $course['Title'] ?? $course['title'] ?? null,
        'Subject' => $course['Subject'] ?? $course['subject'] ?? null,
        'Level' => $course['Level'] ?? $course['level'] ?? null,
        'Description' => $course['Description'] ?? $course['description'] ?? null,
    ];
}

function sendSuccess(array $payload, int $status = 200): void
{
    respond([
        'success' => true,
        'request_id' => api_request_id(),
        'data' => $payload,
    ], $status);
}

function sendError(string $message, int $status = 400, string $code = 'ERR_GENERIC'): void
{
    respond([
        'success' => false,
        'request_id' => api_request_id(),
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ], $status);
}

function respond(array $payload, int $status): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
