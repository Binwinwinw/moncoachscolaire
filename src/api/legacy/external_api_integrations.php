<?php

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/deprecated.php';

api_deprecated('/api/admin/log_action');
json_error('Endpoint obsolète. Merci d’utiliser la nouvelle API.', 410, 'ERR_GONE');
exit;
/**
 * Admin API - External API Integrations
 * Endpoint: /src/api/admin/external_api_integrations.php
 * Methods:
 *  - GET : list (with optional id)
 *  - POST: create
 *  - PUT : update
 *  - DELETE: delete?id=
 *  - POST?action=test&id= : test connection
 */

// Basic protections and setup
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../../database/connection.php';

try {
    requireAdmin();
} catch (Exception $e) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

$pdo = $GLOBALS['pdo'] ?? null;
if (!$pdo) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Base de données indisponible']);
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

function sendJson($data)
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($method === 'GET') {
        if (!empty($_GET['id'])) {
            $stmt = $pdo->prepare('SELECT * FROM admin_api_integrations WHERE id = ? LIMIT 1');
            $stmt->execute([$_GET['id']]);
            sendJson(['data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        }
        $stmt = $pdo->query('SELECT id,name,type,enabled,last_status,last_checked_at,created_at,updated_at FROM admin_api_integrations ORDER BY name');
        sendJson(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    if ($method === 'POST') {
        // Create or action=test
        if ($action === 'test' && !empty($_GET['id'])) {
            $id = (int) $_GET['id'];
            $stmt = $pdo->prepare('SELECT * FROM admin_api_integrations WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                sendJson(['success' => false, 'error' => 'Intégration introuvable']);
            }
            $config = json_decode($row['config'] ?? '{}', true) ?? [];

            // Simple connection check: attempt a GET to url in config (timeout)
            $testResult = ['ok' => false, 'http_code' => null, 'message' => 'Non testé'];
            if (!empty($config['url'])) {
                $url = $config['url'];
                $opts = stream_context_create(['http' => ['timeout' => 6]]);
                try {
                    $headers = @get_headers($url, 1);
                    if ($headers && is_array($headers)) {
                        $testResult['ok'] = true;
                        $testResult['http_code'] = substr($headers[0], 9, 3) ?: null;
                        $testResult['message'] = 'OK';
                    } else {
                        $testResult['message'] = 'No response';
                    }
                } catch (Exception $e) {
                    $testResult['message'] = $e->getMessage();
                }
            } else {
                $testResult['message'] = 'Aucune URL fournie dans la config';
            }

            // Update last status
            $stmt = $pdo->prepare('UPDATE admin_api_integrations SET last_status = ?, last_checked_at = NOW() WHERE id = ?');
            $stmt->execute([$testResult['ok'] ? 'ok' : 'failed', $id]);

            // Log action
            if (function_exists('logAdminAction')) {
                logAdminAction('integration_test', 'Test integration ID ' . $id, $id, ['result' => $testResult]);
            }

            sendJson(['success' => true, 'result' => $testResult]);
        }

        // Create new integration
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || empty($data['name'])) {
            sendJson(['success' => false, 'error' => 'Données manquantes']);
        }
        $stmt = $pdo->prepare('INSERT INTO admin_api_integrations (name,type,config,enabled,created_at,updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
        $stmt->execute([
            $data['name'],
            $data['type'] ?? null,
            json_encode($data['config'] ?? new stdClass(), JSON_UNESCAPED_UNICODE),
            !empty($data['enabled']) ? 1 : 0,
        ]);
        $id = (int) $pdo->lastInsertId();
        if (function_exists('logAdminAction')) {
            logAdminAction('integration_create', 'Création integration: ' . ($data['name'] ?? ''), $id, $data);
        }
        sendJson(['success' => true, 'id' => $id]);
    }

    if ($method === 'PUT') {
        // Update
        parse_str(file_get_contents('php://input'), $putVars);
        $id = (int) ($putVars['id'] ?? 0);
        if (!$id) {
            sendJson(['success' => false, 'error' => 'ID requis']);
        }
        $fields = [];
        $params = [];
        if (isset($putVars['name'])) {
            $fields[] = 'name = ?';
            $params[] = $putVars['name'];
        }
        if (isset($putVars['type'])) {
            $fields[] = 'type = ?';
            $params[] = $putVars['type'];
        }
        if (isset($putVars['config'])) {
            $fields[] = 'config = ?';
            $params[] = $putVars['config'];
        }
        if (isset($putVars['enabled'])) {
            $fields[] = 'enabled = ?';
            $params[] = $putVars['enabled'] ? 1 : 0;
        }
        if (empty($fields)) {
            sendJson(['success' => false, 'error' => 'Aucune modification fournie']);
        }
        $params[] = $id;
        $sql = 'UPDATE admin_api_integrations SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (function_exists('logAdminAction')) {
            logAdminAction('integration_update', 'Mise à jour integration ID ' . $id, $id, $putVars);
        }
        sendJson(['success' => true]);
    }

    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            sendJson(['success' => false, 'error' => 'ID requis']);
        }
        $stmt = $pdo->prepare('DELETE FROM admin_api_integrations WHERE id = ?');
        $stmt->execute([$id]);
        if (function_exists('logAdminAction')) {
            logAdminAction('integration_delete', 'Suppression integration ID ' . $id, $id);
        }
        sendJson(['success' => true]);
    }

    // Unsupported
    sendJson(['success' => false, 'error' => 'Méthode non supportée']);
} catch (Exception $e) {
    error_log('external_api_integrations error: ' . $e->getMessage());
    sendJson(['success' => false, 'error' => $e->getMessage()]);
}
