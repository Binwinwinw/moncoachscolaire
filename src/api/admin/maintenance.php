<?php

/**
 * API Admin - Gestion du mode maintenance
 * Endpoint: /api/admin/maintenance.php
 *
 * Actions:
 * - GET : Récupère l'état actuel de la maintenance
 * - POST : Active/désactive la maintenance
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
    $securityPath = __DIR__ . '/../../includes/login_security.php';

    if (!file_exists($configPath)) {
        sendJsonError('Fichier de configuration non trouvé', 500);
    }
    require_once $configPath;

    if (!file_exists($authPath)) {
        sendJsonError('Fichier d\'authentification admin non trouvé', 500);
    }
    require_once $authPath;

    if (file_exists($securityPath)) {
        require_once $securityPath;
    }

    // Vérifier que l'utilisateur est admin
    if (!function_exists('isAdmin')) {
        sendJsonError('Fonction isAdmin() non disponible', 500);
    }

    if (!isAdmin()) {
        sendJsonError('Accès refusé. Administrateur requis.', 403);
    }

    // Chemin du fichier de maintenance (RACINE du projet)
    // src/api/admin/../../../.maintenance.json = racine/.maintenance.json
    $maintenanceFile = __DIR__ . '/../../../.maintenance.json';
    $maintenanceDir = dirname($maintenanceFile);

    // S'assurer que le dossier existe et est accessible
    if (!is_dir($maintenanceDir)) {
        if (!mkdir($maintenanceDir, 0755, true)) {
            sendJsonError('Impossible de créer le dossier de maintenance', 500);
        }
    }

    // Fonction pour lire l'état de maintenance
    function getMaintenanceStatus()
    {
        global $maintenanceFile;
        if (!file_exists($maintenanceFile)) {
            return [
                'enabled' => false,
                'message' => 'Le site est actuellement en maintenance. Nous serons de retour bientôt !',
                'activated_at' => null,
                'activated_by' => null,
            ];
        }

        $content = file_get_contents($maintenanceFile);
        $data = json_decode($content, true);

        if ($data === null) {
            // Fichier corrompu, retourner état par défaut
            return [
                'enabled' => false,
                'message' => 'Le site est actuellement en maintenance. Nous serons de retour bientôt !',
                'activated_at' => null,
                'activated_by' => null,
            ];
        }

        return $data;
    }

    // Fonction pour sauvegarder l'état de maintenance
    function saveMaintenanceStatus($enabled, $message = null, $userId = null)
    {
        global $maintenanceFile;

        $status = getMaintenanceStatus();
        $status['enabled'] = (bool) $enabled;

        if ($message !== null) {
            $status['message'] = $message;
        }

        if ($enabled) {
            $status['activated_at'] = date('Y-m-d H:i:s');
            $status['activated_by'] = $userId ?? $_SESSION['user_id'] ?? null;
        } else {
            $status['deactivated_at'] = date('Y-m-d H:i:s');
            $status['deactivated_by'] = $userId ?? $_SESSION['user_id'] ?? null;
        }

        $json = json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($maintenanceFile, $json, LOCK_EX) === false) {
            return false;
        }

        return $status;
    }

    // Gérer les requêtes
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($method === 'GET') {
        // Récupérer l'état actuel
        $status = getMaintenanceStatus();
        sendJsonResponse([
            'success' => true,
            'maintenance' => $status,
        ]);
    } elseif ($method === 'POST') {
        // Activer ou désactiver la maintenance
        $input = json_decode(file_get_contents('php://input'), true);

        if ($input === null && !empty($_POST)) {
            $input = $_POST;
        }

        $csrfToken = $input['csrf_token'] ?? null;
        if (!$csrfToken && function_exists('getallheaders')) {
            $headers = getallheaders();
            $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
        }

        if (!function_exists('verifyCSRFToken') || !verifyCSRFToken((string) $csrfToken)) {
            sendJsonError('CSRF invalide', 403);
        }

        $enabled = isset($input['enabled']) ? (bool) $input['enabled'] : false;
        $message = $input['message'] ?? null;

        $result = saveMaintenanceStatus($enabled, $message);

        if ($result === false) {
            sendJsonError('Impossible de sauvegarder l\'état de maintenance', 500);
        }

        // Logger l'action
        if (function_exists('logAdminAction')) {
            $action = $enabled ? 'Maintenance activée' : 'Maintenance désactivée';
            logAdminAction($action, [
                'message' => $message,
                'status' => $enabled,
            ]);
        }

        sendJsonResponse([
            'success' => true,
            'maintenance' => $result,
            'message' => $enabled
                ? 'Mode maintenance activé avec succès'
                : 'Mode maintenance désactivé avec succès',
        ]);
    } else {
        sendJsonError('Méthode non autorisée', 405);
    }

} catch (Exception $e) {
    error_log("API maintenance.php: Exception - " . $e->getMessage());
    sendJsonError('Erreur serveur: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("API maintenance.php: Error - " . $e->getMessage());
    sendJsonError('Erreur fatale: ' . $e->getMessage(), 500);
}

// Fallback si aucune réponse n'a été envoyée
ob_end_clean();
http_response_code(500);
echo json_encode(['success' => false, 'error' => 'Erreur inconnue'], JSON_UNESCAPED_UNICODE);
exit;
