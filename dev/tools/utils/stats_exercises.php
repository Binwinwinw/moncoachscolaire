<?php
require_once __DIR__ . '/../db/connection.php';

echo "=== Statistiques des exercices ===\n\n";

// Par niveau
$stmt = $pdo->query("SELECT Level, COUNT(*) as cnt FROM Exercises GROUP BY Level ORDER BY FIELD(Level, '6ème', '5ème', '4ème', '3ème')");
echo "Par niveau:\n";
foreach ($stmt as $r) {
    echo "  {$r['Level']}: {$r['cnt']} exercices\n";
}

// Par matière
$stmt = $pdo->query("SELECT Subject, COUNT(*) as cnt FROM Exercises GROUP BY Subject ORDER BY Subject");
echo "\nPar matière:\n";
foreach ($stmt as $r) {
    echo "  {$r['Subject']}: {$r['cnt']} exercices\n";
}

// Total
$total = $pdo->query("SELECT COUNT(*) as cnt FROM Exercises")->fetch();
echo "\n✓ Total: {$total['cnt']} exercices\n";

// Détail par niveau + matière
echo "\n=== Détail (Niveau × Matière) ===\n";
$stmt = $pdo->query("SELECT Level, Subject, COUNT(*) as cnt FROM Exercises GROUP BY Level, Subject ORDER BY FIELD(Level, '6ème', '5ème', '4ème', '3ème'), Subject");
foreach ($stmt as $r) {
    echo "  {$r['Level']} | {$r['Subject']}: {$r['cnt']}\n";
}
