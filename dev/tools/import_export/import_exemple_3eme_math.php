<?php
// Script d'import d'exercices enrichis depuis un fichier JSON (exemple 3ème maths)
// Usage : php dev/import_exemple_3eme_math.php

require_once __DIR__ . '/../src/database/connection.php';

$jsonFile = __DIR__ . '/exemple_exercice_3eme_math.json';
if (!file_exists($jsonFile)) {
    die("Fichier JSON introuvable : $jsonFile\n");
}

$data = json_decode(file_get_contents($jsonFile), true);
if (!$data) {
    die("Erreur de lecture ou de décodage du JSON\n");
}

$insertSql = "INSERT INTO Exercises
    (Subject, Level, Title, Content, Instruction, Answer, AnswerType, Choices, Tips, Domain, Competence, Difficulty, Identifier)
    VALUES
    (:Subject, :Level, :Title, :Content, :Instruction, :Answer, :AnswerType, :Choices, :Tips, :Domain, :Competence, :Difficulty, :Identifier)
    ON DUPLICATE KEY UPDATE
    Content=VALUES(Content), Instruction=VALUES(Instruction), Answer=VALUES(Answer), AnswerType=VALUES(AnswerType), Choices=VALUES(Choices), Tips=VALUES(Tips), Domain=VALUES(Domain), Competence=VALUES(Competence), Difficulty=VALUES(Difficulty)";

$imported = 0;
foreach ($data as $exo) {
    $exo['Choices'] = isset($exo['Choices']) && $exo['Choices'] !== null ? json_encode($exo['Choices']) : null;
    $stmt = $pdo->prepare($insertSql);
    $stmt->execute($exo);
    $imported++;
}
echo "Import terminé : $imported exercices insérés ou mis à jour.\n";
