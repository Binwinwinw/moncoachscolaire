#!/usr/bin/env php
<?php
/**
 * Script pour examiner un exercice spécifique
 */

require_once __DIR__ . '/../config.php';

$exerciseId = $argv[1] ?? 184;

$stmt = $pdo->prepare('SELECT Id, Title, Subject, Level, Content, Answer FROM Exercises WHERE Id = ?');
$stmt->execute([$exerciseId]);
$ex = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ex) {
    echo "❌ Exercice ID $exerciseId non trouvé\n";
    exit(1);
}

echo "📝 EXERCICE ID: " . $ex['Id'] . "\n";
echo "Titre: " . $ex['Title'] . "\n";
echo "Matière: " . $ex['Subject'] . "\n";
echo "Niveau: " . $ex['Level'] . "\n";
echo "\n";
echo "📄 CONTENU:\n";
echo "============================================================\n";
echo $ex['Content'] . "\n";
echo "============================================================\n";
echo "\n";
echo "✅ RÉPONSE:\n";
echo "============================================================\n";
echo $ex['Answer'] . "\n";
echo "============================================================\n";
