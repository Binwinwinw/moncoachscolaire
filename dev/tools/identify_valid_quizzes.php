<?php
/**
 * Identificateur des ~120 Quiz Valides
 *
 * Parcourt les 1598 quiz pour isoler ceux qui contiennent du VRAI contenu
 * (pas de placeholders, corrections complètes, notions documentées)
 *
 * Usage: php dev/tools/identify_valid_quizzes.php
 */

$projectRoot = dirname(dirname(dirname(__FILE__)));
chdir($projectRoot);

echo "\n=== IDENTIFICATION DES QUIZ VALIDES ===\n";
echo "(Scan complet des 1598 pour isoler les ~120 VRAIS)\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$quizDir = $projectRoot . '/src/data/quiz';
$answersDir = $projectRoot . '/src/data/quiz_answers';

// Critères qualité minimale
$QUALITY_THRESHOLDS = [
    'min_question_length' => 15,      // Question ≥ 15 chars
    'min_correction_length' => 30,    // Correction ≥ 30 chars
    'min_questions_count' => 3,       // Au moins 3 questions
    'require_notions' => true,        // Doit avoir des notions documentées
];

echo "[1/2] Scanning 1598 quiz pour qualité...\n\n";

$validQuizzes = [];
$invalidQuizzes = [];
$quizFiles = array_filter(scandir($quizDir), fn($f) => pathinfo($f, PATHINFO_EXTENSION) === 'json');

$processedCount = 0;
foreach ($quizFiles as $filename) {
    $processedCount++;

    if ($processedCount % 200 === 0) {
        echo "   ... traité $processedCount\n";
    }

    $quizId = pathinfo($filename, PATHINFO_FILENAME);

    // Lire le quiz et réponses
    $quizContent = @file_get_contents($quizDir . '/' . $filename);
    $answersContent = @file_get_contents($answersDir . '/' . $filename);

    if (!$quizContent || !$answersContent) {
        $invalidQuizzes[$quizId] = 'fichier manquant';
        continue;
    }

    $quiz = json_decode($quizContent, true);
    $answers = json_decode($answersContent, true);

    if (!$quiz || !$answers) {
        $invalidQuizzes[$quizId] = 'JSON invalide';
        continue;
    }

    // Extraire les données
    $questions = $quiz['quiz']['questions'] ?? [];
    $answersList = $answers['quiz']['answers'] ?? [];
    $notions = $quiz['exercisenotion'] ?? [];
    $level = $quiz['contents']['level'] ?? null;
    $subject = $quiz['contents']['subject'] ?? null;

    $isValid = true;
    $issues = [];

    // Critère 1 : nombre de questions
    if (count($questions) < $QUALITY_THRESHOLDS['min_questions_count']) {
        $isValid = false;
        $issues[] = "questions < 3 (" . count($questions) . ")";
    }

    // Critère 2 : qualité des questions et réponses
    if ($isValid) {
        foreach ($questions as $idx => $q) {
            $qText = trim($q['question'] ?? '');
            $aData = $answersList[$idx] ?? [];
            $correction = trim($aData['correction'] ?? '');

            // Question trop courte ou placeholder
            if (strlen($qText) < $QUALITY_THRESHOLDS['min_question_length']) {
                $issues[] = "Q" . ($idx+1) . " trop courte (" . strlen($qText) . " chars)";
                $isValid = false;
                break;
            }

            // Placeholder classique
            if (stripos($qText, 'TODO') !== false || stripos($qText, 'PLACEHOLDER') !== false) {
                $issues[] = "Q" . ($idx+1) . " = placeholder";
                $isValid = false;
                break;
            }

            // Correction trop courte ou vide
            if (strlen($correction) < $QUALITY_THRESHOLDS['min_correction_length']) {
                $issues[] = "A" . ($idx+1) . " correction courte (" . strlen($correction) . " chars)";
                $isValid = false;
                break;
            }

            // Correction = placeholder
            if (stripos($correction, 'TODO') !== false || stripos($correction, 'TBD') !== false) {
                $issues[] = "A" . ($idx+1) . " = placeholder";
                $isValid = false;
                break;
            }
        }
    }

    // Critère 3 : notions documentées
    if ($isValid && $QUALITY_THRESHOLDS['require_notions']) {
        $notionCount = count(array_filter($notions, fn($n) => !empty($n['notion'])));
        if ($notionCount === 0) {
            $isValid = false;
            $issues[] = "aucune notion";
        }
    }

    // Enregistrer
    if ($isValid) {
        $validQuizzes[$quizId] = [
            'level' => $level,
            'subject' => $subject,
            'questions' => count($questions),
            'notions' => count(array_filter($notions, fn($n) => !empty($n['notion']))),
        ];
    } else {
        $invalidQuizzes[$quizId] = $issues;
    }
}

echo "\n✅ Scan complet : $processedCount quiz\n\n";

echo "=== RÉSULTAT ===\n";
echo "✅ VALIDES: " . count($validQuizzes) . " quiz\n";
echo "❌ INVALIDES: " . count($invalidQuizzes) . " quiz\n";
echo "\n";

// Affichage des valides groupés par level/subject
echo "=== QUIZ VALIDES GROUPÉS ===\n";
$byCompetence = [];
foreach ($validQuizzes as $qId => $qData) {
    $key = ($qData['level'] ?? 'UNKNOWN') . '|' . ($qData['subject'] ?? 'UNKNOWN');
    if (!isset($byCompetence[$key])) {
        $byCompetence[$key] = [];
    }
    $byCompetence[$key][] = $qId;
}

ksort($byCompetence);
foreach ($byCompetence as $key => $ids) {
    list($level, $subject) = explode('|', $key);
    echo "\n[$level] $subject : " . count($ids) . " quiz\n";
    echo "  IDs: " . implode(', ', array_slice($ids, 0, 10));
    if (count($ids) > 10) {
        echo ", ... (" . (count($ids) - 10) . " autres)";
    }
    echo "\n";
}

// Sauvegarder rapport
$reportDir = $projectRoot . '/dev/tmp/quiz_quality_audit';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

// JSON avec détails
$report = [
    'timestamp' => date('c'),
    'total_quizzes' => $processedCount,
    'valid_quizzes' => count($validQuizzes),
    'invalid_quizzes' => count($invalidQuizzes),
    'valid_quiz_ids' => array_keys($validQuizzes),
    'by_competence' => array_map(function($ids) {
        return array_values($ids);
    }, $byCompetence),
];

file_put_contents(
    $reportDir . '/valid_quizzes_list.json',
    json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

// CSV pour import
$csvFile = $reportDir . '/valid_quizzes_summary.csv';
$csvHandle = fopen($csvFile, 'w');
fputcsv($csvHandle, ['Quiz ID', 'Level', 'Subject', 'Questions', 'Notions'], ',');

foreach ($validQuizzes as $qId => $qData) {
    fputcsv($csvHandle, [
        $qId,
        $qData['level'],
        $qData['subject'],
        $qData['questions'],
        $qData['notions'],
    ], ',');
}
fclose($csvHandle);

echo "\n[2/2] Rapports sauvegardés :\n";
echo "  📋 JSON: $reportDir/valid_quizzes_list.json\n";
echo "  📊 CSV : $csvFile\n";
echo "\n";

echo "=== PROCHAINES ÉTAPES ===\n";
echo "1. Examiner les " . count($validQuizzes) . " quiz valides\n";
echo "2. Extraire le template-modèle de ces " . count($validQuizzes) . " \n";
echo "3. Créer une checklist stricte basée sur ce qui fonctionne\n";
echo "4. Utiliser pour enrichir progressivement avec Groq\n";
echo "\n✅ Audit qualité terminé!\n\n";
