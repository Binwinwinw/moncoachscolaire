<?php

/**
 * Admin Notifications API
 * - Templates CRUD
 * - Enqueue send (action=send)
 * - List notifications (journal)
 */
require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/deprecated.php';
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../../database/connection.php';
require_once __DIR__ . '/../../includes/admin_audit.php';

api_deprecated('/api/admin/logs');
json_error('Endpoint obsolète. Merci d’utiliser la nouvelle API.', 410, 'ERR_GONE');
exit;

try {
    requireAdmin();
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false,'error' => 'Accès refusé']);
    exit;
}
$pdo = $GLOBALS['pdo'] ?? null;
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false,'error' => 'DB indisponible']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? null;

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    require_same_origin();
    $token = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        $token = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
    }
    if (!$token) {
        $json = api_get_json_body(false);
        $token = $json['csrf_token'] ?? ($_POST['csrf_token'] ?? null);
    }
    require_csrf($token);
}

function sendJson($d)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($method === 'GET') {
        if (!empty($_GET['templates'])) {
            $stmt = $pdo->query('SELECT * FROM notification_templates ORDER BY name');
            sendJson(['success' => true,'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        }
        if (!empty($_GET['notifications'])) {
            // simple filters
            $where = '1=1';
            $bind = [];
            if (!empty($_GET['status'])) {
                $where .= ' AND status = :status';
                $bind[':status'] = $_GET['status'];
            }
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT 200");
            $stmt->execute($bind);
            sendJson(['success' => true,'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        }
        // default: list templates
        $stmt = $pdo->query('SELECT id,name,channel,subject,created_at,updated_at FROM notification_templates ORDER BY name');
        sendJson(['success' => true,'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($method === 'POST') {
        if ($action === 'send') {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: [];
            $template_id = $data['template_id'] ?? null;
            $user_id = $data['user_id'] ?? null;
            $channel = $data['channel'] ?? null;
            $payload = $data['payload'] ?? [];
            $scheduled_at = $data['scheduled_at'] ?? null;
            // insert into notifications
            $stmt = $pdo->prepare('INSERT INTO notifications (template_id,user_id,channel,payload,scheduled_at,status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$template_id, $user_id, $channel, json_encode($payload, JSON_UNESCAPED_UNICODE), $scheduled_at, 'pending']);
            $nid = $pdo->lastInsertId();
            if (function_exists('logAdminAction')) {
                logAdminAction('notification_enqueue', 'Notification enqueued #' . $nid, $nid, ['template_id' => $template_id,'user_id' => $user_id]);
            }
            sendJson(['success' => true,'id' => $nid]);
        }
        // Create template
        $raw = file_get_contents('php://input');
        $d = json_decode($raw, true) ?: [];
        if (empty($d['name'])) {
            sendJson(['success' => false,'error' => 'name required']);
        }
        $stmt = $pdo->prepare('INSERT INTO notification_templates (name,channel,subject,body,created_at,updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([$d['name'], $d['channel'] ?? 'email', $d['subject'] ?? null, $d['body'] ?? null]);
        $id = $pdo->lastInsertId();
        if (function_exists('logAdminAction')) {
            logAdminAction('notification_template_create', 'Template created ' . $id, $id, $d);
        }
        sendJson(['success' => true,'id' => $id]);
    }

    if ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $put);
        $id = (int) ($put['id'] ?? 0);
        if (!$id) {
            sendJson(['success' => false,'error' => 'id required']);
        }
        $fields = [];
        $params = [];
        if (isset($put['name'])) {
            $fields[] = 'name=?';
            $params[] = $put['name'];
        }
        if (isset($put['channel'])) {
            $fields[] = 'channel=?';
            $params[] = $put['channel'];
        }
        if (isset($put['subject'])) {
            $fields[] = 'subject=?';
            $params[] = $put['subject'];
        }
        if (isset($put['body'])) {
            $fields[] = 'body=?';
            $params[] = $put['body'];
        }
        if (empty($fields)) {
            sendJson(['success' => false,'error' => 'no changes']);
        }
        $params[] = $id;
        $sql = 'UPDATE notification_templates SET ' . implode(',', $fields) . ', updated_at = NOW() WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (function_exists('logAdminAction')) {
            logAdminAction('notification_template_update', 'Update template ' . $id, $id, $put);
        }
        sendJson(['success' => true]);
    }

    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            sendJson(['success' => false,'error' => 'id required']);
        }
        $stmt = $pdo->prepare('DELETE FROM notification_templates WHERE id = ?');
        $stmt->execute([$id]);
        if (function_exists('logAdminAction')) {
            logAdminAction('notification_template_delete', 'Delete template ' . $id, $id);
        }
        sendJson(['success' => true]);
    }

    sendJson(['success' => false,'error' => 'méthode non supportée']);
} catch (Exception $e) {
    error_log('notifications api error: ' . $e->getMessage());
    sendJson(['success' => false,'error' => $e->getMessage()]);
}
