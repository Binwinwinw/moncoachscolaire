<?php
require_once __DIR__ . '/../src/database/connection.php';
$stmt = $pdo->query('DESCRIBE Exercises');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . " : " . $col['Type'] . "\n";
}
?>
