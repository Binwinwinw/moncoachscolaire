<?php
/**
 * Script : identify_invalid_complex_exercises.php
 * Objectif : Identifier les exercices multi-parties invalides (Content/Instruction vides), analyser la distribution des sous-questions,
 * et générer un rapport console, CSV et JSON pour audit/correction rapide.
 * Usage : php dev/tools/exercises/identify_invalid_complex_exercises.php
 */

// Paramètres DB (adapter si besoin)
$pdo = null;
require_once __DIR__ . '/../../../db/connection.php';
if (!$pdo) {
    fwrite(STDERR, "[ERREUR] Connexion à la base de données impossible. Vérifiez la configuration.\n");
    exit(1);
}
$date = date('Y-m-d H:i:s');

// 1️⃣ DÉTECTION exercices invalides
$sql_invalid = "SELECT Id, Identifier, Subject, Level,
  CASE WHEN Content IS NULL OR Content = '' THEN '❌ Content vide' ELSE '✅ Content OK' END as content_status,
  CASE WHEN Instruction IS NULL OR Instruction = '' THEN '❌ Instruction vide' ELSE '✅ Instruction OK' END as instruction_status,
  JSON_LENGTH(sub_questions) as nb_questions
FROM exercises
WHERE structure_type = 'multi-parties'
  AND (Content IS NULL OR Content = '' OR Instruction IS NULL OR Instruction = '');";
$invalid = $pdo->query($sql_invalid)->fetchAll(PDO::FETCH_ASSOC);

// 2️⃣ ANALYSE distribution des questions
$sql_dist = "SELECT JSON_LENGTH(sub_questions) as nb_questions, COUNT(*) as count, GROUP_CONCAT(Identifier SEPARATOR ', ') as exercices
FROM exercises
WHERE structure_type = 'multi-parties'
GROUP BY nb_questions
ORDER BY nb_questions;";
$dist = $pdo->query($sql_dist)->fetchAll(PDO::FETCH_ASSOC);

// 3️⃣ GÉNÉRATION FICHIERS
$csv_path = __DIR__ . '/../../../dev/reports/invalid_complex_exercises.csv';
$json_path = __DIR__ . '/../../../dev/reports/invalid_complex_exercises.json';

// A) Console
$total_invalid = count($invalid);

// Récupérer le nombre total d'exercices multi-parties
$total_mp = $pdo->query("SELECT COUNT(*) FROM exercises WHERE structure_type = 'multi-parties'")->fetchColumn();

// Affichage console
echo "\n════════════════════════════════════════════════════════\n";
echo "   🔍 IDENTIFICATION DES EXERCICES INVALIDES\n";
echo "════════════════════════════════════════════════════════\n\n";
echo "❌ EXERCICES AVEC CONTENT/INSTRUCTION VIDES : $total_invalid exercices\n";
echo "────────────────────────────────────────────────────────\n";
echo "Id   | Identifier              | Problème\n";
echo "─────┼─────────────────────────┼──────────────────────\n";
foreach ($invalid as $row) {
    $problems = [];
    if ($row['content_status'] !== '✅ Content OK') $problems[] = '❌ Content vide';
    if ($row['instruction_status'] !== '✅ Instruction OK') $problems[] = '❌ Instruction vide';
    printf("%-4s | %-23s | %s\n", $row['Id'], $row['Identifier'], implode(', ', $problems));
}
echo "────────────────────────────────────────────────────────\n\n";

// Distribution
$dist_map = [
    '2-3_questions' => 0,
    '4-10_questions' => 0,
    '11-20_questions' => 0,
    '21-50_questions' => 0,
    '50+_questions' => 0
];
foreach ($dist as $row) {
    $n = (int)$row['nb_questions'];
    if ($n >= 2 && $n <= 3) $dist_map['2-3_questions'] += $row['count'];
    elseif ($n >= 4 && $n <= 10) $dist_map['4-10_questions'] += $row['count'];
    elseif ($n >= 11 && $n <= 20) $dist_map['11-20_questions'] += $row['count'];
    elseif ($n >= 21 && $n <= 50) $dist_map['21-50_questions'] += $row['count'];
    elseif ($n > 50) $dist_map['50+_questions'] += $row['count'];
}
echo "📊 DISTRIBUTION DES QUESTIONS :\n";
echo "────────────────────────────────────────────────────────\n";
echo "Questions | Nombre | Exercices\n";
echo "──────────┼────────┼──────────────────────────────────\n";
foreach ($dist as $row) {
    $n = (int)$row['nb_questions'];
    $label = $n;
    if ($n >= 2 && $n <= 3) $label = '2-3';
    elseif ($n >= 4 && $n <= 10) $label = '4-10';
    elseif ($n >= 11 && $n <= 20) $label = '11-20';
    elseif ($n >= 21 && $n <= 50) $label = '21-50';
    elseif ($n > 50) $label = '50+';
    printf("%-9s | %-6s | %s\n", $label, $row['count'], $row['exercices']);
}
echo "────────────────────────────────────────────────────────\n\n";

// Recommandations
$reco = [];
if ($total_invalid > 0) $reco[] = "Corriger $total_invalid exercices avec Content/Instruction vides";
if ($dist_map['2-3_questions'] == 0) $reco[] = "Ajouter 1-2 exercices courts (2-3 questions)";
echo "📌 RECOMMANDATIONS :\n";
foreach ($reco as $r) echo "- $r\n";
echo "────────────────────────────────────────────────────────\n";

// B) CSV
$csv = fopen($csv_path, 'w');
fputcsv($csv, ['Id','Identifier','Subject','Level','Content_Status','Instruction_Status','Nb_Questions']);
foreach ($invalid as $row) {
    fputcsv($csv, [
        $row['Id'],
        $row['Identifier'],
        $row['Subject'],
        $row['Level'],
        ($row['content_status'] === '✅ Content OK' ? 'OK' : 'VIDE'),
        ($row['instruction_status'] === '✅ Instruction OK' ? 'OK' : 'VIDE'),
        $row['nb_questions']
    ]);
}
fclose($csv);

// C) JSON
$json = [
    'analysis_date' => $date,
    'total_multi_parties' => (int)$total_mp,
    'invalid_count' => $total_invalid,
    'invalid_exercises' => [],
    'distribution' => $dist_map,
    'recommendations' => $reco
];
foreach ($invalid as $row) {
    $issues = [];
    if ($row['content_status'] !== '✅ Content OK') $issues[] = 'content_empty';
    if ($row['instruction_status'] !== '✅ Instruction OK') $issues[] = 'instruction_empty';
    $json['invalid_exercises'][] = [
        'id' => (int)$row['Id'],
        'identifier' => $row['Identifier'],
        'subject' => $row['Subject'],
        'level' => $row['Level'],
        'issues' => $issues,
        'nb_questions' => (int)$row['nb_questions']
    ];
}
file_put_contents($json_path, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Fin
exit(0);
