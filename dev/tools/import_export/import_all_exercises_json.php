<?php
// Script d'import de tous les exercices JSON dans la table 'exercises'
// À placer à la racine du projet ou à adapter selon vos besoins

$folders = [
    'db/json/bac',
    'db/json/college/3eme/exercices',
    'db/json/college/4eme/exercices',
    'db/json/college/5eme/exercices',
    'db/json/college/6eme/exercices',
    'db/json/lycee/1ere/exercices',
    'db/json/lycee/2nd/exercices',
    'db/json/lycee/terminale/exercices',
];

// Connexion à la base de données
require_once __DIR__ . '/../db/connection.php';
global $pdo;

function insertExercise($pdo, $exercise) {
    $sql = "INSERT INTO exercises (Subject, Level, Title, Content, Instruction, Answer, AnswerType, Choices, Tips, Domain, Competence, Difficulty, Identifier, is_active, XP_Points, Coherence)
            VALUES (:Subject, :Level, :Title, :Content, :Instruction, :Answer, :AnswerType, :Choices, :Tips, :Domain, :Competence, :Difficulty, :Identifier, :is_active, :XP_Points, :Coherence)
            ON DUPLICATE KEY UPDATE
                Subject=VALUES(Subject), Level=VALUES(Level), Title=VALUES(Title), Content=VALUES(Content),
                Instruction=VALUES(Instruction), Answer=VALUES(Answer), AnswerType=VALUES(AnswerType),
                Choices=VALUES(Choices), Tips=VALUES(Tips), Domain=VALUES(Domain), Competence=VALUES(Competence),
                Difficulty=VALUES(Difficulty), is_active=VALUES(is_active), XP_Points=VALUES(XP_Points), Coherence=VALUES(Coherence)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':Subject' => $exercise['Subject'] ?? null,
        ':Level' => $exercise['Level'] ?? null,
        ':Title' => $exercise['Title'] ?? null,
        ':Content' => $exercise['Content'] ?? null,
        ':Instruction' => $exercise['Instruction'] ?? null,
        ':Answer' => $exercise['Answer'] ?? null,
        ':AnswerType' => $exercise['AnswerType'] ?? null,
        ':Choices' => isset($exercise['Choices']) ? json_encode($exercise['Choices']) : null,
        ':Tips' => $exercise['Tips'] ?? null,
        ':Domain' => $exercise['Domain'] ?? null,
        ':Competence' => $exercise['Competence'] ?? null,
        ':Difficulty' => $exercise['Difficulty'] ?? null,
        ':Identifier' => $exercise['Identifier'] ?? null,
        ':is_active' => isset($exercise['is_active']) ? (int)$exercise['is_active'] : 1,
        ':XP_Points' => $exercise['XP_Points'] ?? 0,
        ':Coherence' => isset($exercise['Coherence']) ? (int)$exercise['Coherence'] : 0,
    ]);
}

$total = 0;
foreach ($folders as $folder) {
    $files = glob($folder . '/*.json');
    foreach ($files as $file) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        if (!is_array($data)) continue;
        foreach ($data as $exercise) {
            // Correction : encoder Choices si c'est un tableau
            if (isset($exercise['Choices']) && is_array($exercise['Choices'])) {
                $exercise['Choices'] = json_encode($exercise['Choices']);
            }
            insertExercise($pdo, $exercise);
            $total++;
        }
    }
}
echo "Import terminé. $total exercices importés.\n";
