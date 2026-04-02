<?php
// Enrichit le champ Instruction si identique à Content ou vide
// Usage : php dev/tools/ex2_enrich_instructions.php

$sourceFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES.json';
$outputFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES_instruction.json';

function load_json($file) {
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$exercises = load_json($sourceFile);

foreach ($exercises as &$ex) {
    if (!isset($ex['Instruction']) || $ex['Instruction'] === '' || $ex['Instruction'] === $ex['Content']) {
        $ex['Instruction'] = 'Complète chaque phrase en choisissant la bonne réponse.';
    }
}
unset($ex);

save_json($outputFile, $exercises);
echo "Enrichissement des instructions terminé. Export : $outputFile\n";
