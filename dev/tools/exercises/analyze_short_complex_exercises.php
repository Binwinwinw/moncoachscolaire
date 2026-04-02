<?php
/**
 * Script : analyze_short_complex_exercises.php
 * Objectif : Identifier les exercices multi-parties courts (<4 questions), analyser leur potentiel d'enrichissement,
 * proposer des stratégies, et générer rapports détaillés (console, JSON, SQL, guide MD).
 * Usage : php dev/tools/exercises/analyze_short_complex_exercises.php [--min-questions=4] [--verbose] [--generate-sql]
 */

// Chargement DB
$pdo = null;
require_once __DIR__ . '/../../../db/connection.php';
if (!$pdo) {
    fwrite(STDERR, "[ERREUR] Connexion à la base de données impossible.\n");
    exit(1);
}

// Parsing options CLI
$minQuestions = 4;
$verbose = false;
$generateSql = false;
foreach ($argv as $arg) {
    if (preg_match('/--min-questions=(\d+)/', $arg, $m)) $minQuestions = (int)$m[1];
    if ($arg === '--verbose') $verbose = true;
    if ($arg === '--generate-sql') $generateSql = true;
}
$date = date('Y-m-d H:i:s');

// PHASE 1 : IDENTIFICATION DES EXERCICES COURTS
$sqlShort = "SELECT Id, Identifier, Subject, Level, Title, JSON_LENGTH(sub_questions) as nb_questions, LEFT(Content, 150) as content_preview, LEFT(Instruction, 100) as instruction_preview, pattern_detected, Difficulty, Content, Instruction, sub_questions, Tips FROM exercises WHERE structure_type = 'multi-parties' AND JSON_LENGTH(sub_questions) < :minq ORDER BY JSON_LENGTH(sub_questions), Id;";
$stmt = $pdo->prepare($sqlShort);
$stmt->execute(['minq' => $minQuestions]);
$short = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PHASE 2 : STATISTIQUES GLOBALES
$sqlDist = "SELECT CASE WHEN JSON_LENGTH(sub_questions) <= 3 THEN '1-3 questions (court)' WHEN JSON_LENGTH(sub_questions) BETWEEN 4 AND 6 THEN '4-6 questions (moyen)' WHEN JSON_LENGTH(sub_questions) BETWEEN 7 AND 15 THEN '7-15 questions (long)' WHEN JSON_LENGTH(sub_questions) BETWEEN 16 AND 30 THEN '16-30 questions (très long)' ELSE '30+ questions (exceptionnel)' END as categorie, COUNT(*) as count, ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM exercises WHERE structure_type = 'multi-parties'), 1) as pourcentage, MIN(JSON_LENGTH(sub_questions)) as min_questions, MAX(JSON_LENGTH(sub_questions)) as max_questions, GROUP_CONCAT(Identifier ORDER BY JSON_LENGTH(sub_questions) LIMIT 3) as exemples FROM exercises WHERE structure_type = 'multi-parties' GROUP BY categorie ORDER BY min_questions;";
$dist = $pdo->query($sqlDist)->fetchAll(PDO::FETCH_ASSOC);

// PHASE 3 : ANALYSE QUALITATIVE + PHASE 4 : PLAN D'ENRICHISSEMENT
$actions = [];
$recap = [
    'enrichir' => 0,
    'simplifier' => 0,
    'fusionner' => 0,
    'creer' => 0
];
$details = [];
foreach ($short as $i => $ex) {
    $nbq = (int)$ex['nb_questions'];
    $contentLen = mb_strlen($ex['Content']);
    $tips = trim($ex['Tips'] ?? '');
    $types = [];
    $subq = json_decode($ex['sub_questions'], true);
    if (is_array($subq)) {
        foreach ($subq as $q) {
            if (isset($q['type'])) $types[] = $q['type'];
        }
    }
    $types = array_count_values($types);
    $hasTips = ($tips !== '' && strtolower($tips) !== 'null');
    $strat = '';
    $suggestions = [];
    $sql = null;
    // Stratégie
    if ($contentLen > 500 && $nbq < $minQuestions) {
        $strat = '🟢 ENRICHIR';
        $recap['enrichir']++;
        $suggestions[] = 'Ajouter 2-3 questions de compréhension ou d’analyse.';
    } elseif ($contentLen < 200 && $nbq < $minQuestions) {
        $strat = '🟡 DIVISER';
        $recap['simplifier']++;
        $sql = "UPDATE exercises SET structure_type='simple' WHERE Id=" . $ex['Id'] . ";";
    } elseif (count($types) === 1 && $nbq < $minQuestions) {
        $strat = '🔵 FUSIONNER';
        $recap['fusionner']++;
        $suggestions[] = 'Fusionner avec un exercice similaire.';
    } else {
        $strat = '✅ AUCUNE ACTION';
    }
    $details[] = [
        'id' => $ex['Id'],
        'identifier' => $ex['Identifier'],
        'subject' => $ex['Subject'],
        'level' => $ex['Level'],
        'title' => $ex['Title'],
        'nb_questions' => $nbq,
        'content_length' => $contentLen,
        'types' => $types,
        'has_tips' => $hasTips,
        'difficulty' => $ex['Difficulty'],
        'strategie' => $strat,
        'suggestions' => $suggestions,
        'sql' => $sql
    ];
    if ($sql) $actions[] = $sql;
}

// PHASE 4 BONUS : Proposer la création d’exercices courts si aucun n’existe
if (count($short) == 0) {
    $recap['creer'] = 2;
}

// SORTIE CONSOLE
echo "\n════════════════════════════════════════════════════════\n";
echo "   📊 ANALYSE DES EXERCICES MULTI-PARTIES COURTS\n";
echo "════════════════════════════════════════════════════════\n";
echo "📅 Date : $date\n\n";
echo "📊 DISTRIBUTION GLOBALE :\n";
echo "────────────────────────────────────────────────────────\n";
echo "Catégorie              | Nombre | %     | Min-Max\n";
echo "───────────────────────┼────────┼───────┼──────────\n";
foreach ($dist as $row) {
    printf("%-23s | %-6s | %-5s | %s-%s\n", $row['categorie'], $row['count'], $row['pourcentage'], $row['min_questions'], $row['max_questions']);
}
echo "────────────────────────────────────────────────────────\n\n";

$nShort = count($short);
echo "🔍 EXERCICES COURTS IDENTIFIÉS : $nShort exercice(s)\n";
echo "════════════════════════════════════════════════════════\n\n";
if ($nShort > 0) {
    foreach ($details as $i => $ex) {
        echo "#" . ($i+1) . " - Id {$ex['id']} : {$ex['identifier']}\n";
        echo "────────────────────────────────────────────────────────\n";
        echo "Questions    : {$ex['nb_questions']}\n";
        echo "Subject      : {$ex['subject']} | Level : {$ex['level']}\n";
        echo "Content      : {$ex['content_length']} caractères\n";
        echo "Types        : ";
        foreach ($ex['types'] as $type => $count) echo "$type ($count) ";
        echo "\n";
        echo "Stratégie    : {$ex['strategie']}\n";
        if ($ex['suggestions']) {
            echo "Suggestions  :\n";
            foreach ($ex['suggestions'] as $s) echo "  - $s\n";
        }
        if ($ex['sql']) echo "Action SQL   : {$ex['sql']}\n";
        echo "────────────────────────────────────────────────────────\n\n";
    }
} else {
    echo "Aucun exercice court trouvé (<$minQuestions questions).\n";
}

// RÉSUMÉ DES RECOMMANDATIONS
$recapStr = "\n════════════════════════════════════════════════════════\n📌 RÉSUMÉ DES RECOMMANDATIONS :\n════════════════════════════════════════════════════════\n";
$recapStr .= "🟢 À enrichir        : {$recap['enrichir']}\n";
$recapStr .= "🟡 À simplifier      : {$recap['simplifier']}\n";
$recapStr .= "🔵 À fusionner       : {$recap['fusionner']}\n";
$recapStr .= "🟣 À créer           : {$recap['creer']}\n";
echo $recapStr;
echo "────────────────────────────────────────────────────────\n";

// FICHIERS DE SORTIE
$json_path = __DIR__ . '/../../../dev/reports/short_complex_exercises_analysis.json';
$sql_path = __DIR__ . '/../../../dev/reports/short_complex_exercises_actions.sql';
$guide_path = __DIR__ . '/../../../dev/reports/short_complex_exercises_creation_guide.md';

// A) JSON
file_put_contents($json_path, json_encode([
    'date' => $date,
    'min_questions' => $minQuestions,
    'exercices_courts' => $details,
    'distribution' => $dist,
    'recapitulatif' => $recap
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// B) SQL
if ($generateSql && count($actions)) {
    file_put_contents($sql_path, implode("\n", $actions));
}

// C) GUIDE DE CRÉATION
if ($recap['creer'] > 0) {
    $guide = "# Guide de création d'exercices multi-parties courts (edge cases)\n\n";
    $guide .= "## Exemples à créer :\n";
    $guide .= "- 1 exercice avec 2 questions (QCM)\n";
    $guide .= "- 1 exercice avec 3 questions (Texte + QCM)\n\n";
    $guide .= "## Template :\n";
    $guide .= "Titre : 'Identifier les types de phrases'\n";
    $guide .= "Subject : Français | Level : 6ème | Difficulté : facile\n\n";
    $guide .= "Content :\n";
    $guide .= "Lis les phrases suivantes :\n\nQuelle heure est-il ?\nFerme la porte.\nLe chat dort sur le canapé.\n\n";
    $guide .= "Instruction : Pour chaque phrase, indique son type.\n\n";
    $guide .= "Sub-questions :\n";
    $guide .= "- Q1 : Phrase 1 est de type ? (QCM)\n";
    $guide .= "- Q2 : Phrase 2 est de type ? (QCM)\n";
    $guide .= "\n";
    $guide .= "## Consignes pédagogiques :\n";
    $guide .= "- Veiller à la clarté des consignes\n- Varier les types de questions\n- Proposer des feedbacks pour chaque réponse\n";
    file_put_contents($guide_path, $guide);
}

exit(0);
