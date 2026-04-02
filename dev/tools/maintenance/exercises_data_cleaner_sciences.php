<?php
// Audit, correction et enrichissement pour exercices de Sciences Première
// Usage : php dev/tools/exercises_data_cleaner_sciences.php

$sourceFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES.json';
$outputFile = __DIR__ . '/../../db/json/lycee/1ere/exercices/exercice-PREMIERE-SCIENCES_enriched.json';

function load_json($file) {
    $json = file_get_contents($file);
    return json_decode($json, true);
}

function save_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function get_domain_keyword($domain) {
    if (!$domain) return 'GEN';
    $words = preg_split('/\W+/', $domain);
    return strtoupper($words[0] ?? 'GEN');
}

function get_difficulty($ex) {
    $content = strtolower($ex['Content'] ?? '');
    $answer = strtolower($ex['Answer'] ?? '');
    $choices = $ex['Choices'] ?? null;
    if (strpos($content, 'explique') !== false || strpos($content, 'justifie') !== false || strpos($content, 'démontre') !== false) return 'difficile';
    if ($choices && count($choices) > 4) return 'moyen';
    if (strlen(strip_tags($content)) < 200 && strlen(strip_tags($answer)) < 100) return 'facile';
    return 'moyen';
}

function get_answertype($ex) {
    $answer = strtolower($ex['Answer'] ?? '');
    $choices = $ex['Choices'] ?? null;
    if ($choices && count($choices) > 0) return 'choix_multiple';
    if (strpos($answer, 'calcule') !== false || strpos($answer, 'calcul') !== false) return 'calcule';
    if (strpos($answer, 'vrai') !== false || strpos($answer, 'faux') !== false) return 'case_a_cocher';
    return 'texte';
}

function normalize_instruction($ex) {
    if (($ex['Content'] ?? '') === ($ex['Instruction'] ?? '')) {
        if (!empty($ex['Title'])) return $ex['Title'] . ' : ' . $ex['Content'];
        return 'Consigne : ' . ($ex['Content'] ?? '');
    }
    return $ex['Instruction'] ?? '';
}

function build_identifier($ex, $domainIndex) {
    $subject = strtoupper($ex['Subject'] ?? 'GEN');
    $level = strtoupper($ex['Level'] ?? 'GEN');
    $domain = get_domain_keyword($ex['Domain'] ?? 'GEN');
    $num = str_pad($domainIndex[$domain], 3, '0', STR_PAD_LEFT);
    return "$subject-$level-$domain-$num";
}

$exercises = load_json($sourceFile);
$domainIndex = [];
foreach ($exercises as $ex) {
    $domain = get_domain_keyword($ex['Domain'] ?? 'GEN');
    if (!isset($domainIndex[$domain])) $domainIndex[$domain] = 1;
}

foreach ($exercises as &$ex) {
    // 1. Vérification de cohérence
    $fields = ['Subject','Level','Title','Content','Instruction','Answer','AnswerType','Choices'];
    $coherence = true;
    foreach ($fields as $f) {
        if (!isset($ex[$f])) $coherence = false;
    }
    $ex['Coherence'] = $coherence;

    // 2. Correction de l'instruction
    $ex['Instruction'] = normalize_instruction($ex);

    // 3. Création d'identifier si vide
    if (empty($ex['Identifier'])) {
        $domain = get_domain_keyword($ex['Domain'] ?? 'GEN');
        if (!isset($domainIndex[$domain])) $domainIndex[$domain] = 1;
        $ex['Identifier'] = build_identifier($ex, $domainIndex);
        $domainIndex[$domain]++;
    }

    // 4. Calcul du niveau de difficulté
    $ex['Difficulty'] = get_difficulty($ex);

    // 5. Déduction du type de réponse
    $ex['AnswerType'] = get_answertype($ex);
}
unset($ex);

save_json($outputFile, $exercises);
echo "Nettoyage et enrichissement terminés. Export : $outputFile\n";
