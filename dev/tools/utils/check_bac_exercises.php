<?php
require_once __DIR__ . '/../db/connection.php';

echo "=== Analyse des exercices BAC ===" . PHP_EOL . PHP_EOL;

// Compter exercices BAC
$stmt = $pdo->query('SELECT COUNT(*) as nb FROM exercises WHERE Level = "BAC"');
$bacCount = $stmt->fetch(PDO::FETCH_ASSOC)['nb'];
echo "Exercices BAC: " . $bacCount . PHP_EOL;

// Compter exercices Terminale
$stmt = $pdo->query('SELECT COUNT(*) as nb FROM exercises WHERE Level = "Terminale"');
$termCount = $stmt->fetch(PDO::FETCH_ASSOC)['nb'];
echo "Exercices Terminale: " . $termCount . PHP_EOL . PHP_EOL;

// Matières BAC
$stmt = $pdo->query('SELECT DISTINCT Subject FROM exercises WHERE Level = "BAC" ORDER BY Subject');
echo "Matières BAC:" . PHP_EOL;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $subject = $row['Subject'];
    $count = $pdo->query("SELECT COUNT(*) FROM exercises WHERE Level='BAC' AND Subject='$subject'")->fetchColumn();
    echo "  - $subject: $count exercices" . PHP_EOL;
}

echo PHP_EOL . "Matières Terminale:" . PHP_EOL;
$stmt = $pdo->query('SELECT DISTINCT Subject FROM exercises WHERE Level = "Terminale" ORDER BY Subject');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $subject = $row['Subject'];
    $count = $pdo->query("SELECT COUNT(*) FROM exercises WHERE Level='Terminale' AND Subject='$subject'")->fetchColumn();
    echo "  - $subject: $count exercices" . PHP_EOL;
}
