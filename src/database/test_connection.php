<?php

// db/test_connection.php — test DB connect
require_once __DIR__ . '/connection.php';

if (!isset($pdo) || !$pdo) {
    echo "DB indisponible (pas de configuration).";
    exit;
}

try {
    $stmt = $pdo->query("SELECT 1 AS ok");
    $row = $stmt->fetch();
    if ($row && $row['ok'] == 1) {
        echo "Connexion OK — base : " . (isset($dbName) ? $dbName : getenv('DB_DATABASE')) . "\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
