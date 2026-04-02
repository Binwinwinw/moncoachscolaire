<?php
// Déduit le type de réponse à partir du contenu et de la réponse
// Usage : php dev/tools/ex5_detect_answertype.php

$sourceFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES.json';
$outputFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES_answertype.json';

function load_json($file) {
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function get_answertype($ex) {
    $answer = strtolower(strip_tags($ex['Answer'] ?? ''));
    $choices = $ex['Choices'] ?? null;
    if ($choices && is_array($choices) && count($choices) > 0) return 'choix_multiple';
    if (strpos($answer, 'calcule') !== false || strpos($answer, 'calcul') !== false) return 'calcule';
    if (strpos($answer, 'vrai') !== false || strpos($answer, 'faux') !== false) return 'case_a_cocher';
    return 'texte';
}

$exercises = load_json($sourceFile);
foreach ($exercises as &$ex) {
    $ex['AnswerType'] = get_answertype($ex);
}
unset($ex);

save_json($outputFile, $exercises);
echo "Détection du type de réponse terminée. Export : $outputFile\n";
