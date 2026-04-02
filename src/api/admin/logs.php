<?php

/**
 * API Admin - Gestion des logs
 * Endpoint: /api/admin/logs.php
 */

// Activer le rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Démarrer le buffer de sortie
ob_start();

// Headers
header('Content-Type: application/json; charset=utf-8');

// Fonction pour retourner une erreur JSON proprement
function sendJsonError($message, $code = 500)
{
    ob_end_clean();
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// Fonction pour retourner une réponse JSON
function sendJsonResponse($data)
{
    ob_end_clean();
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        error_log("Erreur encodage JSON: " . json_last_error_msg());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'encodage JSON'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo $json;
    exit;
}

try {
    // Démarrer la session si nécessaire
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Charger les dépendances
    $configPath = __DIR__ . '/../../config/config.php';
    $connectionPath = __DIR__ . '/../../database/connection.php';
    $legacyConnectionPath = __DIR__ . '/../../../db/connection.php';
    $authPath = __DIR__ . '/../../includes/admin_auth.php';

    if (!file_exists($configPath)) {
        sendJsonError('Fichier de configuration non trouvé', 500);
    }
    require_once $configPath;

    if (!file_exists($authPath)) {
        sendJsonError('Fichier d\'authentification admin non trouvé', 500);
    }
    require_once $authPath;

    // Connexion DB
    if (file_exists($connectionPath)) {
        require_once $connectionPath;
    } elseif (file_exists($legacyConnectionPath)) {
        require_once $legacyConnectionPath;
    } else {
        sendJsonError('Fichier de connexion DB non trouvé', 500);
    }

    // Vérifier la connexion à la base de données
    if (!isset($pdo) || !$pdo) {
        sendJsonError('Base de données non disponible', 503);
    }

    // Vérifier l'authentification admin
    if (!function_exists('isAdmin')) {
        sendJsonError('Fonction d\'authentification non disponible', 500);
    }

    if (!isAdmin()) {
        sendJsonError('Accès refusé. Administrateur requis.', 403);
    }
} catch (Exception $e) {
    error_log("API logs.php - Erreur initiale: " . $e->getMessage());
    sendJsonError('Erreur lors de l\'initialisation: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("API logs.php - Erreur fatale: " . $e->getMessage());
    sendJsonError('Erreur fatale lors de l\'initialisation', 500);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        handleGetLogs();
    } else {
        sendJsonError('Méthode non autorisée', 405);
    }
} catch (Exception $e) {
    error_log("API logs.php - Erreur: " . $e->getMessage());
    sendJsonError('Erreur lors du traitement: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("API logs.php - Erreur fatale: " . $e->getMessage());
    sendJsonError('Erreur fatale lors du traitement', 500);
}

function isDebugEnabled()
{
    $appDebug = strtolower((string) getenv('APP_DEBUG'));
    $appEnv = strtolower((string) getenv('APP_ENV'));
    $debugFlags = ['1', 'true', 'yes', 'on'];
    return in_array($appDebug, $debugFlags, true) || $appEnv === 'local';
}

function handleGetLogs()
{
    global $pdo;

    $type = $_GET['type'] ?? 'all'; // 'all', 'admin', 'error', 'system'
    if (!isDebugEnabled() && $type !== 'admin') {
        $type = 'admin';
    }
    // Pagination (accepter 'p' ou 'page' pour compatibilité LOCAL/PROD)
    $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : (isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1);
    $limit = isset($_GET['limit']) ? min(100, max(10, (int) $_GET['limit'])) : 50;
    $limitInt = (int) $limit;
    $offsetInt = ($page - 1) * $limitInt;

    $logs = [];

    // Logs admin (depuis AdminLogs)
    if ($type === 'all' || $type === 'admin') {
        try {
            // Vérifier si la table AdminLogs existe
            $tableExists = false;
            try {
                $checkStmt = $pdo->query("SHOW TABLES LIKE 'AdminLogs'");
                $tableExists = $checkStmt->rowCount() > 0;
            } catch (Exception $e) {
                // Table n'existe pas
            }

            if ($tableExists) {
                $stmt = $pdo->prepare("
                    SELECT
                        al.Id,
                        al.Action,
                        al.Details,
                        al.TargetUserId,
                        al.IpAddress,
                        al.CreatedAt,
                        u.Username as AdminUsername
                    FROM AdminLogs al
                    LEFT JOIN Users u ON al.AdminId = u.Id
                    ORDER BY al.CreatedAt DESC
                    LIMIT {$limitInt} OFFSET {$offsetInt}
                ");
                $stmt->execute();
                $adminLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($adminLogs as $log) {
                    $logs[] = [
                        'id' => $log['Id'],
                        'type' => 'admin',
                        'action' => $log['Action'],
                        'details' => $log['Details'],
                        'admin' => $log['AdminUsername'],
                        'targetUserId' => $log['TargetUserId'],
                        'ip' => $log['IpAddress'],
                        'timestamp' => $log['CreatedAt'],
                    ];
                }
            }
        } catch (Exception $e) {
            error_log("Erreur récupération logs admin: " . $e->getMessage());
            // Continuer sans les logs admin
        }
    }

    // Logs système (depuis error_log PHP - lecture du fichier de log)
    if (isDebugEnabled() && ($type === 'all' || $type === 'error' || $type === 'system')) {
        $logFile = ini_get('error_log');
        if ($logFile && file_exists($logFile) && is_readable($logFile)) {
            try {
                $fileLogs = readErrorLogFile($logFile, $limitInt);
                $logs = array_merge($logs, $fileLogs);
            } catch (Exception $e) {
                error_log("Erreur lecture fichier log: " . $e->getMessage());
            }
        }
    }

    // Trier par timestamp (plus récent en premier)
    usort($logs, function ($a, $b) {
        $timeA = strtotime($a['timestamp'] ?? '1970-01-01');
        $timeB = strtotime($b['timestamp'] ?? '1970-01-01');
        return $timeB - $timeA;
    });

    // Limiter au nombre demandé
    $logs = array_slice($logs, 0, $limitInt);

    sendJsonResponse([
        'success' => true,
        'data' => $logs,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => count($logs),
        ],
    ]);
}

function readErrorLogFile($filePath, $limit = 50)
{
    $logs = [];

    if (!file_exists($filePath) || !is_readable($filePath)) {
        return $logs;
    }

    try {
        // Lire les dernières lignes du fichier
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return $logs;
        }

        $lines = array_slice($lines, -$limit);

        foreach ($lines as $line) {
            // Parser les lignes de log PHP
            if (preg_match('/\[(.*?)\]\s*(.*)/', $line, $matches)) {
                $logs[] = [
                    'type' => 'error',
                    'timestamp' => $matches[1],
                    'message' => trim($matches[2]),
                    'raw' => trim($line),
                ];
            } else {
                $logs[] = [
                    'type' => 'system',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'message' => trim($line),
                    'raw' => trim($line),
                ];
            }
        }
    } catch (Exception $e) {
        error_log("Erreur lecture fichier log: " . $e->getMessage());
    }

    return $logs;
}
