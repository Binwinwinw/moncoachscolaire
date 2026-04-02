<?php
// Script de détection de doublons entre la table Exercises et le JSON all_exercises_clean.json
// Usage : php dev/tools/find_exercises_duplicates.php

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

// Indexation rapide DB par clé composite
$dbIndex = [];
foreach ($rows as $row) {
    $key = strtolower(trim($row['Subject'])) . '|' . strtolower(trim($row['Level'])) . '|' . strtolower(trim($row['Title'])) . '|' . md5(trim($row['Content']));
    $dbIndex[$key][] = $row['Id'];
}

$duplicates = [];
foreach ($exJson as $ex) {
    if (!isset($ex['Subject'], $ex['Level'], $ex['Title'], $ex['Content'])) continue;
    $key = strtolower(trim($ex['Subject'])) . '|' . strtolower(trim($ex['Level'])) . '|' . strtolower(trim($ex['Title'])) . '|' . md5(trim($ex['Content']));
    if (isset($dbIndex[$key])) {
        $duplicates[] = [
            'json' => $ex,
            'db_ids' => $dbIndex[$key]
        ];
    }
}

// Rapport
if (empty($duplicates)) {
    echo "Aucun doublon exact trouvé entre la base et le JSON.\n";
    exit(0);
}
echo "Doublons exacts trouvés : " . count($duplicates) . "\n";
foreach ($duplicates as $dup) {
    echo "- DB Id(s): " . implode(',', $dup['db_ids']) . " | Sujet: {$dup['json']['Subject']} | Niveau: {$dup['json']['Level']} | Titre: {$dup['json']['Title']}\n";
}
