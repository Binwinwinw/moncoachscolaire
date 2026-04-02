<?php
require_once __DIR__ . '/../db/connection.php';

$stmt = $pdo->query('DESCRIBE exercises');
echo "Structure de la table exercises:" . PHP_EOL . PHP_EOL;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("%-20s %-20s %s", $row['Field'], $row['Type'], $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . PHP_EOL;
}
