<?php

// Endpoint: /api/admin/security
// Méthode: GET
// Paramètres: type (alerts, logins, changements)
// Retour: Données structurées (JSON)

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../../includes/admin_auth.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($pdo) || !$pdo) {
    json_error('Base de données non disponible', 503, 'ERR_DB');
}

api_require([
    'method' => 'GET',
    'auth' => true,
    'roles' => ['admin'],
    'rate' => ['key' => 'admin_security_get', 'limit' => 120, 'window' => 60],
]);

$type = $_GET['type'] ?? 'alerts';
$type = validate_enum($type, 'type', ['alerts', 'logins', 'changements']);

/**
 * Vérifie si une table existe dans la base courante
 * @param PDO $pdo
 * @param string $table
 * @return bool
 */
function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE :table');
    $stmt->execute(['table' => $table]);
    return (bool) $stmt->fetchColumn();
}

try {
    switch ($type) {
        case 'alerts':
            if (!tableExists($pdo, 'security_alerts')) {
                $data = [];
                break;
            }
            $stmt = $pdo->query("SELECT `Id`, `Type`, `Message`, `Date` FROM `security_alerts` ORDER BY `Date` DESC LIMIT 50");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'logins':
            if (!tableExists($pdo, 'login_attempts')) {
                $data = [];
                break;
            }
            $stmt = $pdo->query("SELECT `Id`, `UserId`, `Success`, `IP`, `Date` FROM `login_attempts` ORDER BY `Date` DESC LIMIT 50");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'changements':
            if (!tableExists($pdo, 'security_changes')) {
                $data = [];
                break;
            }
            $stmt = $pdo->query("SELECT `Id`, `UserId`, `Action`, `Date` FROM `security_changes` ORDER BY `Date` DESC LIMIT 50");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    json_response($data);
} catch (Exception $e) {
    error_log('[security.php] ' . $e->getMessage());
    json_error('Erreur serveur', 500, 'ERR_SECURITY');
}
