<?php
/**
 * Script : fix_and_complete_validation.php
 * Objectif :
 * 1. Corriger les exercices multi-parties avec Instruction vide (génération automatique)
 * 2. Insérer les 2 exercices courts edge cases si absents
 * Usage : php dev/tools/exercises/fix_and_complete_validation.php
 */

$pdo = null;
require_once __DIR__ . '/../../../db/connection.php';
if (!$pdo) {
    fwrite(STDERR, "[ERREUR] Connexion à la base de données impossible.\n");
    exit(1);
}

$report = [];

// ACTION 1 : Correction des Instructions vides
$sql_select = "SELECT Id, Content, sub_questions FROM exercises WHERE structure_type = 'multi-parties' AND (Instruction IS NULL OR Instruction = '')";
$rows = $pdo->query($sql_select)->fetchAll(PDO::FETCH_ASSOC);
$nb_corrige = 0;
$ids_corriges = [];
foreach ($rows as $row) {
    $id = $row['Id'];
    $content = $row['Content'] ?? '';
    $subq = json_decode($row['sub_questions'] ?? '[]', true);
    $types = [];
    if (is_array($subq)) {
        foreach ($subq as $q) {
            if (isset($q['type'])) $types[] = $q['type'];
        }
    }
    $major = null;
    if (count($types)) {
        $counts = array_count_values($types);
        arsort($counts);
        $major = array_key_first($counts);
    }
    // Génération de l'instruction
    if (stripos($content, 'Texte :') !== false || stripos($content, 'Document') !== false) {
        $instr = "Lis attentivement le texte suivant, puis réponds aux questions.";
    } elseif ($major === 'qcm') {
        $instr = "Choisis la bonne réponse pour chaque question.";
    } elseif ($major === 'texte') {
        $instr = "Réponds aux questions suivantes en rédigeant des phrases complètes.";
    } elseif ($major) {
        $instr = "Réponds aux questions suivantes.";
    } else {
        $instr = "Réponds aux questions suivantes.";
    }
    $sql_upd = "UPDATE exercises SET Instruction = :instr, updated_at = NOW() WHERE Id = :id";
    $stmt = $pdo->prepare($sql_upd);
    $stmt->execute([':instr' => $instr, ':id' => $id]);
    $nb_corrige++;
    $ids_corriges[] = $id;
}
$report[] = "Exercices corrigés (Instruction vide) : $nb_corrige";
if ($nb_corrige) $report[] = "IDs corrigés : " . implode(', ', $ids_corriges);

// ACTION 2 : Insertion des 2 exercices courts
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
$ids_inserted = [];
foreach ($exercices as $exo) {
    // Vérifier existence
    $sql_check = "SELECT Id FROM exercises WHERE Identifier = :ident";
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([':ident' => $exo['Identifier']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['Id']) {
        $ids_inserted[] = $row['Id'];
        $report[] = "Exercice déjà présent : {$exo['Identifier']} (Id {$row['Id']})";
        continue;
    }
    // Insertion
    try {
        $fields = array_keys($exo);
        $placeholders = array_map(function($k){ return ":$k"; }, $fields);
        $sql = "INSERT INTO exercises (" . implode(",", $fields) . ") VALUES (" . implode(",", $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($exo);
        $id = $pdo->lastInsertId();
        $ids_inserted[] = $id;
        $report[] = "Exercice inséré : {$exo['Identifier']} (Id $id)";
    } catch (Exception $e) {
        $report[] = "[ERREUR] Insertion exercice {$exo['Identifier']} : " . $e->getMessage();
    }
    // Validation JSON
    $valid_json = json_decode($exo['sub_questions'], true);
    $report[] = "Validation JSON sub_questions : " . (is_array($valid_json) ? 'OK' : 'ERREUR');
}

// Résumé console
foreach ($report as $line) echo $line . "\n";

// Rapport fichier
file_put_contents(__DIR__ . '/../../../dev/reports/fix_and_complete_report.txt', implode("\n", $report));

exit(0);
