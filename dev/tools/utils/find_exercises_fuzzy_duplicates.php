<?php
// Script de détection de quasi-doublons entre la table Exercises et le JSON all_exercises_clean.json
// Usage : php dev/tools/find_exercises_fuzzy_duplicates.php

require_once __DIR__ . '/../../db/connection.php';

$jsonFile = __DIR__ . '/../../db/json/all_exercises_clean.json';
if (!file_exists($jsonFile)) {
    die("Fichier JSON introuvable : $jsonFile\n");
}

$json = file_get_contents($jsonFile);
$exJson = json_decode($json, true);
if (!is_array($exJson)) {
    die("Erreur de parsing JSON\n");
}

// Récupérer tous les exercices de la base
$sql = "SELECT Id, Subject, Level, Title, Content FROM Exercises";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

function normalize($str) {
    $str = mb_strtolower(trim($str));
    $str = preg_replace('/[\s\p{P}]+/u', ' ', $str); // retire ponctuation et espaces multiples
    return $str;
}

function similarity($a, $b) {
    similar_text($a, $b, $percent);
    return $percent;
}

$thresholdTitle = 85; // % de similarité pour le titre
$thresholdContent = 80; // % de similarité pour le contenu

$potential = [];
foreach ($exJson as $ex) {
    if (!isset($ex['Subject'], $ex['Level'], $ex['Title'], $ex['Content'])) continue;
    $nSub = normalize($ex['Subject']);
    $nLvl = normalize($ex['Level']);
    $nTitle = normalize($ex['Title']);
    $nContent = normalize($ex['Content']);
    foreach ($rows as $row) {
        if (normalize($row['Subject']) !== $nSub || normalize($row['Level']) !== $nLvl) continue;
        $titleSim = similarity($nTitle, normalize($row['Title']));
        $contentSim = similarity($nContent, normalize($row['Content']));
        if ($titleSim >= $thresholdTitle && $contentSim >= $thresholdContent) {
            $potential[] = [
                'db_id' => $row['Id'],
                'json_title' => $ex['Title'],
                'db_title' => $row['Title'],
                'title_sim' => $titleSim,
                'content_sim' => $contentSim
            ];
        }
    }
}

if (empty($potential)) {
    echo "Aucun quasi-doublon détecté (titres/contenus très proches).\n";
    exit(0);
}
echo "Quasi-doublons détectés (titres/contenus très proches) : " . count($potential) . "\n";
foreach ($potential as $p) {
    echo "- DB Id: {$p['db_id']} | Titre JSON: {$p['json_title']} | Titre DB: {$p['db_title']} | Sim titre: {$p['title_sim']}% | Sim contenu: {$p['content_sim']}%\n";
}
