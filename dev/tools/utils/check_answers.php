<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/db/connection.php';

echo "=== Analyse des réponses ===\n\n";

$stmt = $pdo->query("SELECT COUNT(*) as total, COUNT(CASE WHEN Answer IS NULL OR TRIM(Answer) = '' THEN 1 END) as empty FROM Exercises");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Exercices totaux: {$row['total']}\n";
echo "Exercices avec réponses vides: {$row['empty']}\n";
echo "Exercices avec réponses remplies: " . ($row['total'] - $row['empty']) . "\n\n";

$stmt = $pdo->query("SELECT MIN(LENGTH(Answer)) as min, MAX(LENGTH(Answer)) as max, AVG(LENGTH(Answer)) as avg FROM Exercises WHERE Answer IS NOT NULL AND TRIM(Answer) != ''");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row['min'] !== NULL) {
    echo "Longueur des réponses remplies:\n";
    echo "  Min: {$row['min']} caractères\n";
    echo "  Max: {$row['max']} caractères\n";
    echo "  Moyenne: " . round($row['avg']) . " caractères\n";
} else {
    echo "❌ Aucune réponse remplie trouvée!\n";
}

// Afficher un exemple d'exercice
echo "\n=== Exemple d'exercice ===\n";
$stmt = $pdo->query("SELECT Id, Subject, Level, Title, Answer FROM Exercises WHERE Answer IS NOT NULL AND TRIM(Answer) != '' LIMIT 1");
$ex = $stmt->fetch(PDO::FETCH_ASSOC);
if ($ex) {
    echo "ID: {$ex['Id']}\n";
    echo "Niveau: {$ex['Level']}\n";
    echo "Matière: {$ex['Subject']}\n";
    echo "Titre: {$ex['Title']}\n";
    echo "Réponse: " . substr($ex['Answer'], 0, 100) . (strlen($ex['Answer']) > 100 ? "..." : "") . "\n";
}
?>
