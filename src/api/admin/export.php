<?php

// Endpoint: /api/admin/export
// Méthode: GET
// Paramètres: type, format, filtres optionnels
// Retour: Fichier à télécharger ou lien de téléchargement

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../../includes/admin_auth.php';

if (!isset($pdo) || !$pdo) {
    json_error('Base de données non disponible', 503, 'ERR_DB');
}

api_require([
    'method' => 'GET',
    'auth' => true,
    'roles' => ['admin'],
    'rate' => ['key' => 'admin_export_get', 'limit' => 20, 'window' => 60],
]);

$type = $_GET['type'] ?? 'users';
$format = $_GET['format'] ?? 'csv';
$type = validate_enum($type, 'type', ['users', 'exercices', 'progression', 'logs']);
$format = validate_enum($format, 'format', ['csv', 'json', 'pdf']);

switch ($type) {
    case 'users':
        $stmt = $pdo->prepare('SELECT Id, Username, Email, Role, UserLevel, XP, CreatedAt FROM users');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'exercices':
        $stmt = $pdo->prepare('SELECT * FROM exercices');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'progression':
        $stmt = $pdo->prepare('SELECT * FROM progression');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'logs':
        $stmt = $pdo->prepare('SELECT * FROM logs');
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
}

if ($format === 'json') {
    header('Content-Type: application/json');
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export_' . $type . '.csv"');
    if (!empty($rows)) {
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        fclose($out);
    }
    exit;
}

if ($format === 'pdf') {
    // PDF export à implémenter (librairie nécessaire)
    header('Content-Type: text/plain');
    echo "Export PDF non encore disponible.";
    exit;
}

json_error('Format non supporté.', 400, 'ERR_EXPORT');
