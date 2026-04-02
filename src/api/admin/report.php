<?php

// Endpoint: /api/admin/report
// Méthode: GET
// Paramètres: type, period, filtres optionnels
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
    'rate' => ['key' => 'admin_report_get', 'limit' => 60, 'window' => 60],
]);

$type = $_GET['type'] ?? 'progression';
$period = $_GET['period'] ?? '30d';
$type = validate_enum($type, 'type', ['progression', 'utilisateurs', 'exercices', 'activite']);
$period = validate_enum($period, 'period', ['7d', '30d', '90d', 'all']);

// Définir la période
switch ($period) {
    case '7d':
        $dateMin = date('Y-m-d', strtotime('-7 days'));
        break;
    case '30d':
        $dateMin = date('Y-m-d', strtotime('-30 days'));
        break;
    case '90d':
        $dateMin = date('Y-m-d', strtotime('-90 days'));
        break;
    case 'all':
    default:
        $dateMin = null;
        break;
}

try {
    switch ($type) {
        case 'progression':
            $sql = 'SELECT UserId, SUM(XP) as TotalXP, COUNT(*) as Actions FROM progression';
            if ($dateMin) {
                $sql .= ' WHERE Date >= ?';
            }
            $sql .= ' GROUP BY UserId ORDER BY TotalXP DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($dateMin ? [$dateMin] : []);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'utilisateurs':
            $sql = 'SELECT Id, Username, Email, Role, UserLevel, XP, CreatedAt FROM users';
            if ($dateMin) {
                $sql .= ' WHERE CreatedAt >= ?';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($dateMin ? [$dateMin] : []);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'exercices':
            $sql = 'SELECT Id, Title, Subject, Level, CreatedAt FROM exercices';
            if ($dateMin) {
                $sql .= ' WHERE CreatedAt >= ?';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($dateMin ? [$dateMin] : []);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'activite':
            $sql = 'SELECT Id, UserId, Action, Date FROM logs';
            if ($dateMin) {
                $sql .= ' WHERE Date >= ?';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($dateMin ? [$dateMin] : []);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    json_response($data);
} catch (Exception $e) {
    json_error('Erreur serveur', 500, 'ERR_REPORT');
}
