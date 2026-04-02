<?php
// parser_quiz.php - Batch CSV Quiz → JSON Enrichi pour MonCoachScolaire
// Usage: php parser_quiz.php [csv_file]  | default: src/data/quiz/*.csv

require_once '../../config/database.php';  // PDO tes prefs
$inputDir = 'quiz/';
$outputDir = 'quiz_packs/';
$batchSize = 20;

if (!is_dir($outputDir)) mkdir($outputDir, 0777, true);

// Enrichissement pédagogique (exemples)
$enrichissements = [
    'ADDITION' => ['explication' => 'Additionner deux nombres entiers.', 'exemple' => '2 + 3 = 5'],
    'SOUSTRACTION' => ['explication' => 'Soustraire deux nombres.', 'exemple' => '5 - 2 = 3'],
    // Ajoute COMPETENCE tes DB
];

foreach (glob($inputDir . '*.csv') as $csvFile) {
    $csvData = array_map('str_getcsv', file($csvFile));
    $header = array_shift($csvData);

    $batches = array_chunk($csvData, $batchSize);
    $baseName = pathinfo($csvFile, PATHINFO_FILENAME);

    foreach ($batches as $i => $batch) {
        $jsonData = [];
        foreach ($batch as $row) {
            $quiz = array_combine($header, $row);
            $identifier = $quiz['Identifier'] ?? 'UNKNOWN';
            $parts = explode('-', $identifier);
            $competence = $parts[2] ?? 'GENERAL';

            $quiz['enrichment'] = $enrichissements[$competence] ?? ['explication' => 'À développer', 'exemple' => 'Exemple générique'];
            $quiz['niveau'] = $parts[1] ?? 'UNKNOWN';
            $jsonData[] = $quiz;
        }

        $outputFile = "$outputDir/{$baseName}_batch" . ($i+1) . ".json";
        file_put_contents($outputFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Généré: $outputFile (" . count($jsonData) . " quizzes)\n";
    }
}
echo "Parser terminé !\n";
?>
