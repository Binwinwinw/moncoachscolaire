<?php
/**
 * parse_complex_exercises.php — MonCoachScolaire
 * Parse et structure les exercices complexes détectés
 * Entrée : dev/reports/complex_exercises_detected.json
 * Sortie :
 *   - dev/db/json/schema/exercices/complex_exercises_parsed.json
 *   - dev/reports/parsing_report.txt
 *   - dev/reports/parsing_failures.json
 * Usage : php parse_complex_exercises.php [--debug]
 */

ini_set('memory_limit', '512M');
$debug = in_array('--debug', $argv);

$inputPath = __DIR__ . '/../../../dev/reports/complex_exercises_detected.json';
$outputPath = __DIR__ . '/../../../dev/db/json/schema/exercices/complex_exercises_parsed.json';
$reportPath = __DIR__ . '/../../../dev/reports/parsing_report.txt';
$failuresPath = __DIR__ . '/../../../dev/reports/parsing_failures.json';

if (!is_file($inputPath)) {
    fwrite(STDERR, "❌ Fichier d'entrée introuvable : $inputPath\n");
    exit(1);
}

$data = json_decode(file_get_contents($inputPath), true);
if (!$data || !isset($data['exercises'])) {
    fwrite(STDERR, "❌ Fichier JSON d'entrée invalide.\n");
    exit(1);
}
$exercises = $data['exercises'];
$total = count($exercises);

$parsed = [];
$failures = [];
$patternStats = [
    'Question X :' => 0,
    'X)' => 0,
    'X.' => 0
];
$success = $partial = $failed = 0;

function cleanSupport($text) {
    // Nettoie les marqueurs et balises HTML
    $text = preg_replace('/<(.*?)>/', '', $text); // retire HTML
    $text = preg_replace('/(Texte\s*:|Consigne\s*:|Contexte\s*:|Document\s*:|Support\s*:|Chapitre\s*:|Exercice\s+porte\s+sur|^ee |^e |^\s+)/iu', '', $text);
    $text = trim($text);
    return $text;
}

function detectQuestionPatterns($content) {
    // Retourne un tableau de [pattern, match, pos]
    $patterns = [
        ['Question X :', '/Question ?(\d+)[\s:]/i'],
        ['X)', '/(\d+)[\)]/'],
        ['X.', '/(\d+)[\.]/'],
        ['a)', '/([a-z])[\)]/i']
    ];
    $matches = [];
    foreach ($patterns as [$label, $regex]) {
        if (preg_match_all($regex, $content, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as $i => $found) {
                $matches[] = [
                    'pattern' => $label,
                    'match' => $found[0],
                    'pos' => $found[1]
                ];
            }
        }
    }
    // Tri par position
    usort($matches, function($a, $b) { return $a['pos'] <=> $b['pos']; });
    return $matches;
}

function extractChoices($text) {
    // Cherche les choix QCM (A), B), ...)
    if (preg_match_all('/([A-Z]\))/', $text, $m)) {
        return array_unique($m[0]);
    }
    return null;
}

function detectType($qText, $qBlock) {
    if (preg_match('/(A\)|B\)|C\)|D\))/', $qBlock)) return 'qcm';
    if (preg_match('/(Vrai\s*\/\s*Faux|→ Vrai → Faux|Vrai\s*[-–] ?Faux)/iu', $qBlock)) return 'vrai_faux';
    if (preg_match('/(Associe|Relie|Correspondance|Tableau)/iu', $qText)) return 'association';
    return 'texte';
}

function extractAnswer($block) {
    // Cherche "Réponse :" ou "Correction :"
    if (preg_match('/(Réponse|Correction)\s*:?\s*(.+)/iu', $block, $m)) {
        return trim($m[2]);
    }
    return null;
}

foreach ($exercises as $idx => $ex) {
    $content = $ex['detected_structure']['support_text'] . "\n";
    foreach ($ex['detected_structure']['questions'] as $q) {
        $content .= $q['text'] . "\n";
    }
    $matches = detectQuestionPatterns($content);
    // Statistiques patterns
    foreach ($matches as $m) {
        if (isset($patternStats[$m['pattern']])) $patternStats[$m['pattern']]++;
    }
    // Extraction support
    $support = isset($ex['detected_structure']['support_text']) ? cleanSupport($ex['detected_structure']['support_text']) : '';
    $questions = $ex['detected_structure']['questions'];
    $sub_questions = [];
    $parsing_status = 'success';
    $needs_review = false;
    $lastPos = 0;
    foreach ($questions as $i => $q) {
        $qText = trim($q['text']);
        $qBlock = $qText;
        $type = detectType($qText, $qBlock);
        $choices = $type === 'qcm' ? extractChoices($qBlock) : null;
        $answer = extractAnswer($qBlock);
        if ($type === 'qcm' && !$choices) $needs_review = true;
        $sub_questions[] = [
            'id' => $i+1,
            'question' => $qText,
            'type' => $type,
            'choices' => $choices,
            'answer' => $answer
        ];
    }
    // Stratégie par score
    if ($ex['confidence_score'] >= 90) {
        $parsing_status = 'success';
    } elseif ($ex['confidence_score'] >= 70) {
        $parsing_status = $needs_review ? 'partial' : 'success';
    } elseif ($ex['confidence_score'] >= 60) {
        $parsing_status = 'partial';
    } else {
        $parsing_status = 'failed';
    }
    if ($parsing_status === 'success') $success++;
    elseif ($parsing_status === 'partial') $partial++;
    else $failed++;
    $parsed[] = [
        'id' => $ex['id'],
        'identifier' => $ex['identifier'],
        'confidence_score' => $ex['confidence_score'],
        'parsing_status' => $parsing_status,
        'support_text' => $support,
        'instruction' => '',
        'sub_questions' => $sub_questions
    ];
    if ($parsing_status === 'failed' || $needs_review) {
        $failures[] = [
            'id' => $ex['id'],
            'identifier' => $ex['identifier'],
            'reason' => $needs_review ? 'Parsing incomplet ou choix QCM manquants' : 'Parsing échoué',
            'content_excerpt' => mb_substr($content,0,300)
        ];
    }
    if ($debug && $idx < 3) {
        fwrite(STDERR, "[DEBUG] Exercice #$idx | Id: {$ex['id']} | Score: {$ex['confidence_score']} | Status: $parsing_status\n");
        fwrite(STDERR, "[DEBUG] Support: $support\n");
        foreach ($sub_questions as $sq) {
            fwrite(STDERR, "[DEBUG] Q{$sq['id']} [{$sq['type']}] : ".mb_substr($sq['question'],0,120)."\n");
        }
    }
}

// Génération JSON
$out = [
    'parsing_date' => date('Y-m-d H:i:s'),
    'total_parsed' => count($parsed),
    'success_rate' => $total ? round($success*100/$total,1).'%' : '0%',
    'exercises' => $parsed
];
if (!is_dir(dirname($outputPath))) mkdir(dirname($outputPath), 0777, true);
file_put_contents($outputPath, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Rapport TXT
$txt = "Parsing des exercices complexes — ".date('Y-m-d H:i:s')."\n";
$txt .= "Total à parser : $total\n";
$txt .= "Parsés avec succès : $success (".($total?round($success*100/$total,1):0)."%)\n";
$txt .= "Parsés partiellement : $partial (".($total?round($partial*100/$total,1):0)."%)\n";
$txt .= "Échecs : $failed (".($total?round($failed*100/$total,1):0)."%)\n\n";
$txt .= "Top 3 patterns de questions détectés :\n";
foreach ($patternStats as $pat => $nb) {
    $txt .= "- $pat : $nb occurrences\n";
}
$txt .= "\nExemples (5 premiers) :\n";
foreach (array_slice($parsed,0,5) as $ex) {
    $txt .= "ID: {$ex['id']} | Identifier: {$ex['identifier']} | Status: {$ex['parsing_status']}\n";
    $txt .= "Support: ".mb_substr($ex['support_text'],0,120)."...\n";
    $txt .= "Questions: ".count($ex['sub_questions'])."\n";
    foreach ($ex['sub_questions'] as $sq) {
        $txt .= "  - [{$sq['type']}] ".mb_substr($sq['question'],0,80)."\n";
    }
    $txt .= str_repeat('-',30)."\n";
}
file_put_contents($reportPath, $txt);

// Fichier des échecs
file_put_contents($failuresPath, json_encode($failures, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Console
printf("✅ Total exercices à parser : %d\n", $total);
printf("✅ Parsés avec succès : %d (%s)\n", $success, $total?round($success*100/$total,1).'%':'0%');
printf("⚠️ Parsés partiellement : %d (%s)\n", $partial, $total?round($partial*100/$total,1).'%':'0%');
printf("❌ Échecs : %d (%s)\n", $failed, $total?round($failed*100/$total,1).'%':'0%');
printf("\nTop 3 patterns de questions détectés :\n");
foreach ($patternStats as $pat => $nb) {
    printf("- %s : %d occurrences\n", $pat, $nb);
}
printf("\nFichiers générés :\n- %s\n- %s\n- %s\n", $outputPath, $reportPath, $failuresPath);
