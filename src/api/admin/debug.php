<?php

/**
 * API Admin - Outils de debug
 * Endpoint: /api/admin/debug.php
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
    $authPath = __DIR__ . '/../../includes/admin_auth.php';

    if (!file_exists($configPath)) {
        sendJsonError('Fichier de configuration non trouvé', 500);
    }
    require_once $configPath;

    if (!file_exists($authPath)) {
        sendJsonError('Fichier d\'authentification admin non trouvé', 500);
    }
    require_once $authPath;

    // Vérifier la connexion à la base de données (optionnel pour debug)
    // On ne bloque pas si PDO n'est pas disponible pour certaines actions

    // Vérifier l'authentification admin
    if (!function_exists('isAdmin')) {
        sendJsonError('Fonction d\'authentification non disponible', 500);
    }

    if (!isAdmin()) {
        sendJsonError('Accès refusé. Administrateur requis.', 403);
    }
} catch (Exception $e) {
    error_log("API debug.php - Erreur initiale: " . $e->getMessage());
    sendJsonError('Erreur lors de l\'initialisation: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("API debug.php - Erreur fatale: " . $e->getMessage());
    sendJsonError('Erreur fatale lors de l\'initialisation', 500);
}

function isDebugEnabled()
{
    $appDebug = strtolower((string) getenv('APP_DEBUG'));
    $appEnv = strtolower((string) getenv('APP_ENV'));
    $debugFlags = ['1', 'true', 'yes', 'on'];
    return in_array($appDebug, $debugFlags, true) || $appEnv === 'local';
}

if (!isDebugEnabled()) {
    sendJsonError('Debug désactivé', 403);
}

$action = $_GET['action'] ?? 'info';

try {
    switch ($action) {
        case 'info':
            getSystemInfo();
            break;

        case 'env':
            getEnvInfo();
            break;

        case 'db':
            getDbInfo();
            break;

        case 'cache':
            clearCache();
            break;

        case 'session':
            getSessionInfo();
            break;

        default:
            sendJsonError('Action non reconnue: ' . $action, 400);
    }
} catch (Exception $e) {
    error_log("API debug.php - Erreur action $action: " . $e->getMessage());
    sendJsonError('Erreur lors de l\'exécution: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("API debug.php - Erreur fatale action $action: " . $e->getMessage());
    sendJsonError('Erreur fatale lors de l\'exécution', 500);
}

function getSystemInfo()
{
    $info = [
        'php' => [
            'version' => PHP_VERSION,
            'sapi' => php_sapi_name(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ],
        'server' => [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'os' => PHP_OS,
            'timezone' => date_default_timezone_get(),
            'server_time' => date('Y-m-d H:i:s'),
        ],
        'paths' => [
            'root' => __DIR__ . '/../..',
            'config' => __DIR__ . '/../../config/config.php',
            'env_production' => 'PROTECTED', // Ne pas exposer le chemin réel
            'env_local' => 'PROTECTED', // Ne pas exposer le chemin réel
        ],
        'permissions' => [
            'config_readable' => is_readable(__DIR__ . '/../../config/config.php'),
            'env_prod_exists' => file_exists(__DIR__ . '/../../.env.production'), // Existence uniquement, pas le contenu
            'env_local_exists' => file_exists(__DIR__ . '/../../.env'), // Existence uniquement, pas le contenu
        ],
    ];

    sendJsonResponse(['success' => true, 'data' => $info]);
}

function getEnvInfo()
{
    $env = [];

    // Variables importantes (masquées pour sécurité)
    $importantVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'APP_ENV', 'APP_DEBUG'];

    foreach ($importantVars as $var) {
        $value = getenv($var);
        if ($value !== false) {
            if (strpos($var, 'PASSWORD') !== false || strpos($var, 'SECRET') !== false) {
                $env[$var] = str_repeat('*', min(strlen($value), 10));
            } else {
                $env[$var] = $value;
            }
        } else {
            $env[$var] = 'NOT SET';
        }
    }

    sendJsonResponse(['success' => true, 'data' => $env]);
}

function getDbInfo()
{
    global $pdo;

    if (!$pdo) {
        sendJsonError('Base de données non connectée', 503);
    }

    $info = [
        'connected' => true,
        'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
        'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
        'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
    ];

    // Liste des tables
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $info['tables'] = $tables;
        $info['table_count'] = count($tables);
    } catch (Exception $e) {
        error_log("Erreur récupération tables: " . $e->getMessage());
        $info['tables_error'] = $e->getMessage();
        $info['tables'] = [];
        $info['table_count'] = 0;
    }

    sendJsonResponse(['success' => true, 'data' => $info]);
}

function clearCache()
{
    // Nettoyer les caches possibles
    $cleared = [];

    // Opcache (si disponible)
    if (function_exists('opcache_reset')) {
        opcache_reset();
        $cleared[] = 'opcache';
    }

    // Session (optionnel - commenté pour sécurité)
    // session_destroy();
    // $cleared[] = 'session';

    sendJsonResponse([
        'success' => true,
        'message' => 'Cache nettoyé',
        'cleared' => $cleared,
    ]);
}

function getSessionInfo()
{
    $info = [
        'session_id' => session_id(),
        'session_status' => [
            'status' => session_status(),
            'name' => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive',
        ],
        'session_data' => [
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null,
            'logged_in' => $_SESSION['logged_in'] ?? null,
            'parent_id' => $_SESSION['parent_id'] ?? null,
        ],
        'session_config' => [
            'name' => ini_get('session.name'),
            'save_path' => ini_get('session.save_path'),
            'gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
            'cookie_lifetime' => ini_get('session.cookie_lifetime'),
        ],
    ];

    sendJsonResponse(['success' => true, 'data' => $info]);
}
