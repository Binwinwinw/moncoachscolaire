<?php
// Génère un identifiant unique si vide, selon le schéma [Subject]-[Level]-[Domain]-[num]
// Usage : php dev/tools/ex3_generate_identifier.php

$sourceFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES.json';
$outputFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES_identifier.json';

function load_json($file) {
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function get_domain_keyword($domain, $title) {
    if ($domain && trim($domain) !== '') {
        $words = preg_split('/\W+/', $domain);
        return strtoupper($words[0] ?? 'GEN');
    }
    // Si Domain vide, déduire du Title
    $words = preg_split('/\W+/', $title);
    return strtoupper($words[0] ?? 'GEN');
}

$exercises = load_json($sourceFile);
$domainIndex = [];
foreach ($exercises as $ex) {
    $domain = get_domain_keyword($ex['Domain'] ?? '', $ex['Title'] ?? '');
    if (!isset($domainIndex[$domain])) $domainIndex[$domain] = 1;
}

foreach ($exercises as &$ex) {
    if (empty($ex['Identifier'])) {
        $subject = strtoupper($ex['Subject'] ?? 'GEN');
        $level = strtoupper($ex['Level'] ?? 'GEN');
        $domain = get_domain_keyword($ex['Domain'] ?? '', $ex['Title'] ?? '');
        $num = str_pad($domainIndex[$domain], 3, '0', STR_PAD_LEFT);
        $ex['Identifier'] = "$subject-$level-$domain-$num";
        $domainIndex[$domain]++;
    }
}
unset($ex);

save_json($outputFile, $exercises);
echo "Génération des identifiants terminée. Export : $outputFile\n";
