#!/usr/bin/env php
<?php
/**
 * Test du parsing des fichiers sans connexion BDD
 */

// Copier les fonctions de parsing du script principal
function parseJSON($filepath) {
    if (!file_exists($filepath)) {
        die("❌ Fichier non trouvé: $filepath\n");
    }
    
    $json = file_get_contents($filepath);
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("❌ Erreur JSON: " . json_last_error_msg() . "\n");
    }
    
    return $data;
}

function parseCSV($filepath) {
    if (!file_exists($filepath)) {
        die("❌ Fichier non trouvé: $filepath\n");
    }
    
    $exercises = [];
    $handle = fopen($filepath, 'r');
    
    $headers = fgetcsv($handle);
    
    while (($row = fgetcsv($handle)) !== false) {
        $exercise = array_combine($headers, $row);
        $exercises[] = [
            'level' => $exercise['Level'],
            'subject' => $exercise['Subject'],
            'title' => $exercise['Title'],
            'content' => $exercise['Content'],
            'answer' => $exercise['Answer'],
            'tips' => $exercise['Tips'] ?? ''
        ];
    }
    
    fclose($handle);
    return $exercises;
}

// Test
echo "📚 Test de parsing des fichiers d'exercices\n\n";

// Test JSON
if (file_exists(__DIR__ . '/../docs/exercices/exemple_exercices.json')) {
    echo "1️⃣  Test JSON\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $jsonExercises = parseJSON(__DIR__ . '/../docs/exercices/exemple_exercices.json');
    echo "✅ " . count($jsonExercises) . " exercices trouvés\n\n";
    
    foreach ($jsonExercises as $i => $ex) {
        echo "  Exercice #" . ($i + 1) . ":\n";
        echo "    Niveau: " . $ex['level'] . "\n";
        echo "    Matière: " . $ex['subject'] . "\n";
        echo "    Titre: " . $ex['title'] . "\n";
        echo "    Content: " . strlen($ex['content']) . " caractères\n";
        echo "    Answer: " . strlen($ex['answer']) . " caractères\n";
        echo "    Tips: " . (empty($ex['tips']) ? 'Vide' : strlen($ex['tips']) . ' caractères') . "\n\n";
    }
}

// Test CSV
if (file_exists(__DIR__ . '/../docs/exercices/exemple_exercices.csv')) {
    echo "\n2️⃣  Test CSV\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    $csvExercises = parseCSV(__DIR__ . '/../docs/exercices/exemple_exercices.csv');
    echo "✅ " . count($csvExercises) . " exercices trouvés\n\n";
    
    foreach ($csvExercises as $i => $ex) {
        echo "  Exercice #" . ($i + 1) . ":\n";
        echo "    Niveau: " . $ex['level'] . "\n";
        echo "    Matière: " . $ex['subject'] . "\n";
        echo "    Titre: " . $ex['title'] . "\n";
        echo "    Content: " . strlen($ex['content']) . " caractères\n";
        echo "    Answer: " . strlen($ex['answer']) . " caractères\n";
        echo "    Tips: " . (empty($ex['tips']) ? 'Vide' : strlen($ex['tips']) . ' caractères') . "\n\n";
    }
}

echo "✅ Tests terminés\n";
?>
