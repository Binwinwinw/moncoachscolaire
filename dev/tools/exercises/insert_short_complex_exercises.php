<?php
/**
 * Script : insert_short_complex_exercises.php
 * Objectif : Insérer 2 exercices multi-parties courts (edge cases) dans la BDD pour tests et validation.
 * Usage : php dev/tools/exercises/insert_short_complex_exercises.php
 */

$pdo = null;
require_once __DIR__ . '/../../../db/connection.php';
if (!$pdo) {
    fwrite(STDERR, "[ERREUR] Connexion à la base de données impossible.\n");
    exit(1);
}

$exercices = [
    [
        "Identifier" => "FRANCAIS-6EME-TYPES-PHRASES-COURT-001",
        "Subject" => "Français",
        "Level" => "6ème",
        "Title" => "Identifier les types de phrases",
        "Content" => "Lis attentivement les deux phrases suivantes :\n\n1. Quelle heure est-il ?\n2. Ferme la porte, s'il te plaît.",
        "Instruction" => "Pour chaque phrase, indique son type (déclarative, interrogative, exclamative ou impérative).",
        "structure_type" => "multi-parties",
        "pattern_detected" => "1)",
        "sub_questions" => json_encode([
            [
                "id" => 1,
                "question" => "La phrase n°1 (\"Quelle heure est-il ?\") est de type :",
                "type" => "qcm",
                "choices" => ["Déclarative", "Interrogative", "Exclamative", "Impérative"],
                "answer" => "Interrogative"
            ],
            [
                "id" => 2,
                "question" => "La phrase n°2 (\"Ferme la porte, s'il te plaît.\") est de type :",
                "type" => "qcm",
                "choices" => ["Déclarative", "Interrogative", "Exclamative", "Impérative"],
                "answer" => "Impérative"
            ]
        ], JSON_UNESCAPED_UNICODE),
        "Difficulty" => "facile",
        "AnswerType" => "qcm",
        "XP_Points" => 5,
        "Tips" => "Rappel : Une phrase interrogative pose une question (?) et une phrase impérative donne un ordre ou une consigne.",
        "Domain" => "Grammaire",
        "Competence" => "Identifier les types de phrases",
        "is_active" => 1
    ],
    [
        "Identifier" => "MATHEMATIQUES-6EME-CALCUL-MENTAL-COURT-001",
        "Subject" => "Mathématiques",
        "Level" => "6ème",
        "Title" => "Calculs simples - Entraînement rapide",
        "Content" => "Effectue les calculs suivants mentalement ou en posant les opérations.",
        "Instruction" => "Donne le résultat exact de chaque calcul (nombre entier ou décimal).",
        "structure_type" => "multi-parties",
        "pattern_detected" => "1)",
        "sub_questions" => json_encode([
            [
                "id" => 1,
                "question" => "15 + 27 = ?",
                "type" => "texte",
                "choices" => null,
                "answer" => "42"
            ],
            [
                "id" => 2,
                "question" => "8 × 7 = ?",
                "type" => "texte",
                "choices" => null,
                "answer" => "56"
            ],
            [
                "id" => 3,
                "question" => "100 - 35 = ?",
                "type" => "texte",
                "choices" => null,
                "answer" => "65"
            ]
        ], JSON_UNESCAPED_UNICODE),
        "Difficulty" => "facile",
        "AnswerType" => "texte",
        "XP_Points" => 5,
        "Tips" => "Pour les additions et soustractions, tu peux décomposer les nombres (par exemple : 15 + 27 = 15 + 20 + 7 = 42).",
        "Domain" => "Nombres et calculs",
        "Competence" => "Effectuer des calculs mentaux",
        "is_active" => 1
    ]
];

$inserted = [];
foreach ($exercices as $exo) {
    $fields = array_keys($exo);
    $placeholders = array_map(function($k){ return ":$k"; }, $fields);
    $sql = "INSERT INTO exercises (" . implode(",", $fields) . ") VALUES (" . implode(",", $placeholders) . ")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($exo);
    $id = $pdo->lastInsertId();
    $inserted[] = [
        'id' => $id,
        'identifier' => $exo['Identifier'],
        'nb_questions' => count(json_decode($exo['sub_questions'], true)),
        'validation' => ($id ? 'OK' : 'ERREUR')
    ];
}

// Affichage console
foreach ($inserted as $i => $row) {
    echo "Exercice #" . ($i+1) . " : {$row['identifier']}\n";
    echo "  Id inséré : {$row['id']}\n";
    echo "  Nombre de questions : {$row['nb_questions']}\n";
    echo "  Validation : {$row['validation']}\n";
    echo "-----------------------------\n";
}

// Génération du rapport
$txt = "Récapitulatif insertion exercices courts (" . date('Y-m-d H:i:s') . ")\n\n";
foreach ($inserted as $i => $row) {
    $txt .= "Exercice #" . ($i+1) . " : {$row['identifier']}\n";
    $txt .= "  Id inséré : {$row['id']}\n";
    $txt .= "  Nombre de questions : {$row['nb_questions']}\n";
    $txt .= "  Validation : {$row['validation']}\n";
    $txt .= "-----------------------------\n";
}
file_put_contents(__DIR__ . '/../../../dev/reports/short_exercises_inserted.txt', $txt);

exit(0);
