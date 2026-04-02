<?php
// Suppression des exercices de Philosophie dans la BDD et le JSON
// Usage : php dev/tools/delete_philosophie_exercises.php

require_once __DIR__ . '/../../db/connection.php';

$jsonFile = __DIR__ . '/../../db/json/all_exercises_clean.json';
$jsonOut = __DIR__ . '/../../db/json/all_exercises_clean_no_philo.json';
if (!file_exists($jsonFile)) {
    die("Fichier JSON introuvable : $jsonFile\n");
}

$json = file_get_contents($jsonFile);
$exJson = json_decode($json, true);
if (!is_array($exJson)) {
    die("Erreur de parsing JSON\n");
}

// Suppression dans la BDD
$sql = "DELETE FROM Exercises WHERE LOWER(Subject) = 'philosophie' OR LOWER(Subject) = 'philo'";
$count = $pdo->exec($sql);
echo "Exercices supprimés de la BDD (Philosophie/Philo) : $count\n";

// Suppression dans le JSON
$filtered = array_filter($exJson, function($ex) {
    if (!isset($ex['Subject'])) return true;
    $s = mb_strtolower(trim($ex['Subject']));
    return $s !== 'philosophie' && $s !== 'philo';
});
file_put_contents($jsonOut, json_encode(array_values($filtered), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
echo "Nouveau JSON sans Philosophie généré : $jsonOut\n";
