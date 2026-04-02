<?php
// Attribue un niveau de difficulté (facile, moyen, difficile) selon le contenu
// Usage : php dev/tools/ex4_set_difficulty.php

$sourceFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES.json';
$outputFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES_difficulty.json';

function load_json($file) {
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function get_difficulty($ex) {
    $content = strtolower(strip_tags($ex['Content'] ?? ''));
    $answer = strtolower(strip_tags($ex['Answer'] ?? ''));
    $choices = $ex['Choices'] ?? null;
    if (strpos($content, 'explique') !== false || strpos($content, 'justifie') !== false || strpos($content, 'démontre') !== false) return 'difficile';
    if ($choices && is_array($choices) && count($choices) > 4) return 'moyen';
    if (strlen($content) < 200 && strlen($answer) < 100) return 'facile';
    return 'moyen';
}

$exercises = load_json($sourceFile);
foreach ($exercises as &$ex) {
    $ex['Difficulty'] = get_difficulty($ex);
}
unset($ex);

save_json($outputFile, $exercises);
echo "Attribution du niveau de difficulté terminée. Export : $outputFile\n";
