<?php

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/connection.php';

api_require([
    'method' => 'GET',
]);

if (!isset($pdo) || !$pdo) {
    json_error('Base de données indisponible', 503, 'ERR_DB');
}

header('Content-Type: application/json; charset=utf-8');
$stmt = $pdo->query("SELECT * FROM cours");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($courses, JSON_UNESCAPED_UNICODE);
