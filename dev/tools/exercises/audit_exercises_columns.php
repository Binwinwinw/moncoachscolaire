<?php
require_once __DIR__ . '/../../../src/database/connection.php';
$result = $pdo->query('DESCRIBE exercises');
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . PHP_EOL;
}
