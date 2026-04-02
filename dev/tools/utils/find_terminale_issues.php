<?php
require_once __DIR__ . '/../db/connection.php';

echo "=== Recherche des exercices Terminale mal catégorisés ===" . PHP_EOL . PHP_EOL;

$checks = [
    ['subject' => 'Anglais', 'keywords' => ['chimie', 'organique', 'probabilité', 'mathématique']],
    ['subject' => 'Français', 'keywords' => ['chimie', 'probabilité', 'mathématique', 'physique']],
    ['subject' => 'Philosophie', 'keywords' => ['chimie', 'probabilité', 'mathématique', 'nombre', 'complexe']],
];

echo "Exercices Terminale suspects:" . PHP_EOL;
foreach ($checks as $check) {
    foreach ($check['keywords'] as $keyword) {
        $stmt = $pdo->prepare('SELECT Id, Subject, Title FROM exercises WHERE Level="Terminale" AND Subject=? AND Title LIKE ?');
        $stmt->execute([$check['subject'], '%' . $keyword . '%']);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            printf("  #%d [%s] %s (keyword: %s)\n", $row['Id'], $row['Subject'], $row['Title'], $keyword);
        }
    }
}
