<?php
// Exporte les exercices de all_exercises.json qui ne sont pas dans la BDD
// Usage : php dev/tools/export_exercises_diff_from_source.php

$sourceFile = __DIR__ . '/../../docs/data/exercices/converted_html/all_exercises.json';
$bddFile = __DIR__ . '/../../db/json/all_exercises_clean.json';
$outputFile = __DIR__ . '/../../docs/data/exercices/converted_html/exercises_diff.json';

function load_json($file) {
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function normalize($ex) {
    // Normalisation simple pour la comparaison
    return [
        'subject' => strtolower(trim($ex['subject'] ?? $ex['Subject'] ?? '')),
        'level' => strtolower(trim($ex['level'] ?? $ex['Level'] ?? '')),
        'title' => strtolower(trim(strip_tags($ex['title'] ?? $ex['Title'] ?? ''))),
        'content' => strtolower(trim(strip_tags($ex['content'] ?? $ex['Content'] ?? ''))),
    ];
}

$source = load_json($sourceFile);
$bdd = load_json($bddFile);

// Index BDD pour recherche rapide
$bddIndex = [];
foreach ($bdd as $ex) {
    $key = json_encode(normalize($ex));
    $bddIndex[$key] = true;
}

$diff = [];
foreach ($source as $ex) {
    $key = json_encode(normalize($ex));
    if (!isset($bddIndex[$key])) {
        $diff[] = $ex;
    }
}

file_put_contents($outputFile, json_encode($diff, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Export terminé : " . count($diff) . " exercices différents exportés depuis la source dans $outputFile\n";
