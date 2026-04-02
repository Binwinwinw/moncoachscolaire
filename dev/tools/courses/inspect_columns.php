<?php
require_once __DIR__ . '/../../../src/database/connection.php';
try {
    $stmt = $pdo->query("SELECT * FROM courses LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r(array_keys($row));
} catch (Exception $e) {
    echo $e->getMessage();
}
