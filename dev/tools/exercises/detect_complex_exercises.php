<?php
/**
 * detect_complex_exercises.php — MonCoachScolaire
 * Détecte les exercices complexes (multi-parties, texte long, questions multiples)
 * Génère 3 rapports : JSON, résumé TXT, exemples détaillés
 * NE MODIFIE PAS la BDD
 */

require_once __DIR__ . '/../../../db/connection.php';
if (!isset($pdo) || !$pdo) {
    fwrite(STDERR, "❌ Connexion à la base de données impossible.\n");
    exit(1);
}

function detectQuestions($content) {
    $patterns = [
        '/(\d+)[\)\.]/', // 1) ou 1.
        '/[a-zA-Z][\)]/', // a) b) c)
        '/Question ?\d+/i',
    ];
    $matches = [];
    foreach ($patterns as $pat) {
        if (preg_match_all($pat, $content, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as $found) {
                $matches[] = $found;
            }
        }
    }
    // Tri par position d'apparition
    usort($matches, function($a, $b) { return $a[1] <=> $b[1]; });
    return $matches;
}

function detectQCMBlocks($content) {
    // Compte les blocs de QCM (A), B), C))
    if (preg_match_all('/([A-Z]\))/', $content, $m)) {
        return count($m[0]);
    }
    return 0;
}

function detectMarkers($content) {
    $patterns = [
        '/texte\s*:/i',
        '/lis\s+(le\s+)?texte/i',
        '/document\s*:/i',
        '/extrait\s*:/i',
        '/contexte\s*:/i',
        '/consigne/i',
        '/partie\s+\d+/i',
        '/chapitre/i',
        '/exercice\s+porte\s+sur/i'
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            return true;
        }
    }
    return false;
}

function extractSupportText($content, $questions) {
    if (count($questions)) {
        $firstPos = $questions[0][1];
        return trim(substr($content, 0, $firstPos));
    }
    return '';
}

function extractQuestions($content, $questions) {
    $result = [];
    for ($i = 0; $i < count($questions); $i++) {
        $start = $questions[$i][1];
        $end = isset($questions[$i+1]) ? $questions[$i+1][1] : strlen($content);
        $text = trim(substr($content, $start, $end - $start));
        $result[] = [
            'number' => $questions[$i][0],
            'text' => $text,
            'type' => null, // Non détecté ici
            'choices' => null // Non détecté ici
        ];
    }
    return $result;
}

// --- DEBUG LOGS ---
$stmt = $pdo->prepare("SELECT Id, Identifier, Subject, Level, Content FROM exercises WHERE is_active = 1");
$stmt->execute();
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($exercises);
fwrite(STDERR, "[DEBUG] Exercices actifs chargés : $total\n");
// Log des 10 premiers exercices
for ($i = 0; $i < min(10, $total); $i++) {
    $ex = $exercises[$i];
    $content = $ex['Content'];
    $len = mb_strlen($content);
    $hasMarkers = detectMarkers($content);
    $qMatches = detectQuestions($content);
    $qcmBlocks = detectQCMBlocks($content);
    fwrite(STDERR, "[DEBUG] Exercice #$i — Id: {$ex['Id']} | Longueur: $len | Markers: ".($hasMarkers?'OUI':'non')." | Questions: ".count($qMatches)." | QCM: $qcmBlocks\n");
}

// DEBUG : Cas spécifiques Id 268 et 199
$idsToCheck = [268, 199];
foreach ($idsToCheck as $idCheck) {
    foreach ($exercises as $ex) {
        if ($ex['Id'] == $idCheck) {
            $content = $ex['Content'];
            $len = mb_strlen($content);
            $hasMarkers = detectMarkers($content);
            $qMatches = detectQuestions($content);
            $qcmBlocks = detectQCMBlocks($content);
            fwrite(STDERR, "[DEBUG] [MANUEL] Exercice Id $idCheck | Longueur: $len | Markers: ".($hasMarkers?'OUI':'non')." | Questions: ".count($qMatches)." | QCM: $qcmBlocks\n");
            // Log les 3 premiers matches de questions
            for ($j=0; $j < min(3, count($qMatches)); $j++) {
                fwrite(STDERR, "[DEBUG]   Question match #$j : ".$qMatches[$j][0]." @".$qMatches[$j][1]."\n");
            }
        }
    }
}


$complex = [];
$confidenceStats = [
    '90+' => 0,
    '70-89' => 0,
    '60-69' => 0,
    '<60' => 0
];
$subjects = [];
$levels = [];

foreach ($exercises as $ex) {
    $content = $ex['Content'];
    $len = mb_strlen($content);
    $hasMarkers = detectMarkers($content);
    $qMatches = detectQuestions($content);
    $qcmBlocks = detectQCMBlocks($content);
    $score = 0;
    $reasons = [];
    // Score longueur
    if ($len > 1000) {
        $score += 40;
        $reasons[] = 'Content > 1000 chars (+40)';
    } elseif ($len > 500) {
        $score += 20;
        $reasons[] = 'Content > 500 chars (+20)';
    }
    // Score questions
    if (count($qMatches) >= 5) {
        $score += 30;
        $reasons[] = 'Questions numérotées >= 5 (+30)';
    } elseif (count($qMatches) >= 3) {
        $score += 20;
        $reasons[] = 'Questions numérotées >= 3 (+20)';
    } elseif (count($qMatches) >= 2) {
        $score += 10;
        $reasons[] = 'Questions numérotées >= 2 (+10)';
    }
    // Markers
    if ($hasMarkers) {
        $score += 20;
        $reasons[] = 'Markers détectés (+20)';
    }
    // QCM
    if ($qcmBlocks >= 2) {
        $score += 20;
        $reasons[] = 'QCM multiples (>=2) (+20)';
    }
    // Attribution des tranches
    if ($score >= 90) $confidenceStats['90+']++;
    elseif ($score >= 70) $confidenceStats['70-89']++;
    elseif ($score >= 60) $confidenceStats['60-69']++;
    else $confidenceStats['<60']++;
    // Détection complexe si score >= 60
    if ($score >= 60) {
        $support = extractSupportText($content, $qMatches);
        $questions = extractQuestions($content, $qMatches);
        $complex[] = [
            'id' => $ex['Id'],
            'identifier' => $ex['Identifier'],
            'subject' => $ex['Subject'],
            'level' => $ex['Level'],
            'confidence_score' => $score,
            'detected_structure' => [
                'support_text' => $support,
                'questions_count' => count($questions),
                'questions' => $questions
            ],
            'reason' => $reasons
        ];
        $subjects[$ex['Subject']] = ($subjects[$ex['Subject']] ?? 0) + 1;
        $levels[$ex['Level']] = ($levels[$ex['Level']] ?? 0) + 1;
    }
}


// Génération JSON
$date = date('Y-m-d H:i:s');
$outJson = [
    'detection_date' => $date,
    'total_analyzed' => $total,
    'complex_detected' => count($complex),
    'detection_rate' => $total ? round(count($complex)*100/$total,1).'%' : '0%',
    'exercises' => $complex
];
$jsonPath = __DIR__ . '/../../../dev/reports/complex_exercises_detected.json';
file_put_contents($jsonPath, json_encode($outJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Génération TXT résumé
$txtPath = __DIR__ . '/../../../dev/reports/complex_exercises_summary.txt';
$txt = "Détection exercices complexes — $date\n";
$txt .= "Total analysés : $total\nComplexes détectés : ".count($complex)." (".$outJson['detection_rate'].")\n";
$txt .= "Répartition scores : >=90=".$confidenceStats['90+'].", 70-89=".$confidenceStats['70-89'].", 60-69=".$confidenceStats['60-69'].", <60=".$confidenceStats['<60']."\n";
// Top 5 sujets/niveaux (par volume)
arsort($subjects); $topSubjects = array_slice(array_keys($subjects),0,5);
arsort($levels); $topLevels = array_slice(array_keys($levels),0,5);
$txt .= "Top 5 sujets : ".implode(', ', $topSubjects)."\n";
$txt .= "Top 5 niveaux : ".implode(', ', $topLevels)."\n";
file_put_contents($txtPath, $txt);

// Génération TXT par score décroissant
$byScorePath = __DIR__ . '/../../../dev/reports/complex_exercises_by_score.txt';
$complexSorted = $complex;
usort($complexSorted, function($a, $b) { return $b['confidence_score'] <=> $a['confidence_score']; });
$sTxt = "Exercices complexes classés par score décroissant :\n";
foreach ($complexSorted as $ex) {
    $sTxt .= "ID: {$ex['id']} | Identifier: {$ex['identifier']} | Score: {$ex['confidence_score']}\n";
    $sTxt .= "Sujet: {$ex['subject']} | Niveau: {$ex['level']}\n";
    $sTxt .= "Raisons: ".implode(' | ', $ex['reason'])."\n";
    $sTxt .= "Support: ".mb_substr($ex['detected_structure']['support_text'],0,200)."...\n";
    $sTxt .= "Questions: {$ex['detected_structure']['questions_count']}\n";
    foreach ($ex['detected_structure']['questions'] as $q) {
        $sTxt .= "  - {$q['number']} : ".mb_substr($q['text'],0,100)."...\n";
    }
    $sTxt .= str_repeat('-',40)."\n";
}
file_put_contents($byScorePath, $sTxt);


// Console
printf("✅ Total exercices analysés : %d\n", $total);
printf("✅ Exercices complexes détectés : %d (%.1f%%)\n", count($complex), $total ? count($complex)*100/$total : 0);
printf("✅ Scores : >=90=%d, 70-89=%d, 60-69=%d, <60=%d\n", $confidenceStats['90+'], $confidenceStats['70-89'], $confidenceStats['60-69'], $confidenceStats['<60']);
printf("✅ Top 5 sujets : %s\n", implode(', ', $topSubjects));
printf("✅ Top 5 niveaux : %s\n", implode(', ', $topLevels));
printf("✅ Fichiers générés :\n- %s\n- %s\n- %s\n", $jsonPath, $txtPath, $byScorePath);
