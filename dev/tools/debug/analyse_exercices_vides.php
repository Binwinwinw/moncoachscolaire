<?php
// Script d'analyse des exercices vides (Content ou Answer)
require_once __DIR__ . '/../src/includes/exercice_loader.php';

if (!$pdo) {
    die("Erreur : connexion à la base de données impossible\n");
}

$query = "SELECT Id, Subject, Level, Title, Content, Answer FROM Exercises WHERE (Content IS NULL OR Content = '' OR Answer IS NULL OR Answer = '') ORDER BY Id";
$stmt = $pdo->query($query);
$emptyExercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

file_put_contents(__DIR__ . '/empty_exercises_report.txt', "ID\tSubject\tLevel\tTitle\tContent\tAnswer\n");

echo "Analyse terminée. Rapport généré : empty_exercises_report.txt\n";
foreach ($emptyExercises as $exo) {
    $line = $exo['Id'] . "\t" . ($exo['Subject'] ?? '') . "\t" . ($exo['Level'] ?? '') . "\t" . ($exo['Title'] ?? '') . "\t" . (empty($exo['Content']) ? '[VIDE]' : '[OK]') . "\t" . (empty($exo['Answer']) ? '[VIDE]' : '[OK]') . "\n";
    file_put_contents(__DIR__ . '/empty_exercises_report.txt', $line, FILE_APPEND);
}

$count = count($emptyExercises);
echo "\nNombre d'exercices avec Content ou Answer vide : $count\n";
if ($count > 0) {
    echo "\nAperçu des 5 premiers :\n";
    for ($i = 0; $i < min(5, $count); $i++) {
        $exo = $emptyExercises[$i];
        echo "ID: " . $exo['Id'] . " | Matière: " . ($exo['Subject'] ?? '') . " | Niveau: " . ($exo['Level'] ?? '') . " | Titre: " . ($exo['Title'] ?? '') . " | Content: " . (empty($exo['Content']) ? '[VIDE]' : '[OK]') . " | Answer: " . (empty($exo['Answer']) ? '[VIDE]' : '[OK]') . "\n";
    }
}
$reportPath = realpath(__DIR__ . '/empty_exercises_report.txt');
echo "\nRapport généré : $reportPath\n";
