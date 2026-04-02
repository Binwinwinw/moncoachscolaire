<?php

// Simple admin action logger API
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../../includes/admin_audit.php';
require_once __DIR__ . '/../../includes/login_security.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    requireAdmin();
} catch (Exception $e) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'error' => 'Accès refusé']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];
$action = $data['action'] ?? null;
$details = $data['details'] ?? null;
$resource = $data['resource'] ?? null;
$meta = $data['meta'] ?? [];

$csrfToken = $data['csrf_token'] ?? null;
if (!$csrfToken && function_exists('getallheaders')) {
    $headers = getallheaders();
    $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
}

if (!function_exists('verifyCSRFToken') || !verifyCSRFToken((string) $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF invalide']);
    exit;
}

if (!$action) {
    echo json_encode(['success' => false, 'error' => 'Action required']);
    exit;
}

$ok = false;
if (function_exists('logAdminAction')) {
    $ok = logAdminAction($action, $details, $resource, $meta);
}

echo json_encode(['success' => (bool) $ok]);
