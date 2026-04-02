<?php
// Vérifie la cohérence des champs principaux d'un exercice
// Usage : php dev/tools/ex1_check_coherence.php

$sourceFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES.json';
$outputFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES_coherence.json';

function load_json($file) {
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$fields = ['Subject','Level','Title','Content','Instruction','Answer','AnswerType','Choices'];
$exercises = load_json($sourceFile);

foreach ($exercises as &$ex) {
    $coherence = true;
    foreach ($fields as $f) {
        if (!isset($ex[$f]) || $ex[$f] === "") $coherence = false;
    }
    $ex['Coherence'] = $coherence;
}
unset($ex);

save_json($outputFile, $exercises);
echo "Vérification de cohérence terminée. Export : $outputFile\n";
