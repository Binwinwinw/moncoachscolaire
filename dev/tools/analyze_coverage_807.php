<?php
/**
 * ANALYSE DE COUVERTURE — 807 Quiz Valides
 *
 * 3 questions :
 * 1. Quels niveaux scolaires sont couverts ? (en ordre de complétude)
 * 2. Quelles matières sont couvertes ?
 * 3. Tableau croisé matière × niveau (couverture détaillée)
 *
 * Usage: php dev/tools/analyze_coverage_807.php
 */

$projectRoot = dirname(dirname(dirname(__FILE__)));
chdir($projectRoot);

echo "\n=== ANALYSE COUVERTURE DES 807 QUIZ VALIDES ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Charger la liste des 807 valides
$csvFile = $projectRoot . '/dev/tmp/quiz_quality_audit/valid_quizzes_summary.csv';
if (!is_file($csvFile)) {
    echo "❌ Fichier $csvFile non trouvé\n";
    exit(1);
}

$handle = fopen($csvFile, 'r');
$headers = fgetcsv($handle); // Skip header

$quizzes = [];
while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 4) continue;

    $quizzes[] = [
        'id' => $row[0],
        'level' => trim($row[1]),
        'subject' => trim($row[2]),
        'questions' => (int)$row[3],
        'notions' => (int)$row[4],
    ];
}
fclose($handle);

echo "✅ Chargé " . count($quizzes) . " quiz valides\n\n";

// ========== QUESTION 1 : Niveaux couverts ==========
echo "========== 1. NIVEAUX SCOLAIRES COUVERTS ==========\n\n";

$byLevel = [];
foreach ($quizzes as $q) {
    $level = $q['level'];
    if (!isset($byLevel[$level])) {
        $byLevel[$level] = 0;
    }
    $byLevel[$level]++;
}

// Ordre logique scolaire
$levelOrder = ['6eme', '5eme', '4eme', '3eme', 'seconde', '2nde', '1ere', '1ère', 'terminale', 'Terminale', 'bac', 'BAC', 'UNKNOWN'];
usort(
    array_keys($byLevel),
    fn($a, $b) => (array_search($a, $levelOrder) ?? 999) - (array_search($b, $levelOrder) ?? 999)
);

$levelsSorted = [];
foreach ($byLevel as $level => $count) {
    $levelsSorted[$level] = $count;
}

$maxCount = max($levelsSorted);
foreach ($levelsSorted as $level => $count) {
    $pct = round(($count / count($quizzes)) * 100);
    $bar = str_repeat('█', ceil($count / $maxCount * 40));
    printf("  %-15s : %4d quiz (%2d%%) %s\n", $level, $count, $pct, $bar);
}

echo "\n" . count($levelsSorted) . " niveaux identifiés\n";

// ========== QUESTION 2 : Matières couvertes ==========
echo "\n========== 2. MATIÈRES COUVERTES ==========\n\n";

$bySubject = [];
foreach ($quizzes as $q) {
    $subject = $q['subject'];
    if (!isset($bySubject[$subject])) {
        $bySubject[$subject] = 0;
    }
    $bySubject[$subject]++;
}

arsort($bySubject);
$maxCount = max($bySubject);
foreach ($bySubject as $subject => $count) {
    $pct = round(($count / count($quizzes)) * 100);
    $bar = str_repeat('█', ceil($count / $maxCount * 40));
    printf("  %-25s : %4d quiz (%2d%%) %s\n", $subject, $count, $pct, $bar);
}

echo "\n" . count($bySubject) . " matières identifiées\n";

// ========== QUESTION 3 : Tableau croisé ==========
echo "\n========== 3. COUVERTURE PAR NIVEAU × MATIÈRE ==========\n\n";

// Préparer données croisées
$matrix = [];
$allLevels = [];
$allSubjects = [];

foreach ($quizzes as $q) {
    $level = $q['level'];
    $subject = $q['subject'];

    if (!in_array($level, $allLevels)) $allLevels[] = $level;
    if (!in_array($subject, $allSubjects)) $allSubjects[] = $subject;

    $key = "$level|$subject";
    if (!isset($matrix[$key])) {
        $matrix[$key] = 0;
    }
    $matrix[$key]++;
}

// Trier levels et subjects
usort($allLevels, fn($a, $b) => (array_search($a, $levelOrder) ?? 999) - (array_search($b, $levelOrder) ?? 999));
sort($allSubjects);

// Afficher tableau
echo "Format: Nombre de quiz pour chaque [Niveau] × [Matière]\n\n";

// En-têtes colonnes
echo str_pad("Level", 15) . " | ";
foreach ($allSubjects as $subject) {
    echo str_pad(substr($subject, 0, 8), 9) . " | ";
}
echo "\n";
echo str_repeat("-", 15 + (count($allSubjects) * 11) + 2) . "\n";

// Lignes données
foreach ($allLevels as $level) {
    echo str_pad($level, 14) . " | ";

    $levelTotal = 0;
    foreach ($allSubjects as $subject) {
        $key = "$level|$subject";
        $count = $matrix[$key] ?? 0;
        $levelTotal += $count;
        echo str_pad((string)($count ?? '-'), 8) . " | ";
    }

    echo "Total: $levelTotal\n";
}

echo "\n";

// Statistiques finales
echo "========== STATISTIQUES SYNTHÉTIQUES ==========\n\n";

$totalCoverage = count($levelsSorted) * count($bySubject);
$actualCoverage = count($matrix);
$coveragePct = round(($actualCoverage / $totalCoverage) * 100);

echo "Couverture théorique max : " . count($levelsSorted) . " niveaux × " . count($bySubject) . " matières = $totalCoverage combinaisons\n";
echo "Couverture réelle : $actualCoverage combinaisons (${coveragePct}%)\n";
echo "Quiz orphelins (niveau/matière sans quiz) : " . ($totalCoverage - $actualCoverage) . "\n";
echo "\n";

// Identifier les gaps
echo "========== GAPS DE COUVERTURE (Niveau/Matière = 0 quiz) ==========\n\n";
$gaps = [];
foreach ($allLevels as $level) {
    foreach ($allSubjects as $subject) {
        $key = "$level|$subject";
        if (!isset($matrix[$key]) || $matrix[$key] === 0) {
            $gaps[] = "$level + $subject";
        }
    }
}

if (count($gaps) > 0) {
    echo "Combinaisons manquantes (" . count($gaps) . ") :\n";
    foreach (array_slice($gaps, 0, 20) as $gap) {
        echo "  ❌ $gap\n";
    }
    if (count($gaps) > 20) {
        echo "  ... et " . (count($gaps) - 20) . " autres\n";
    }
} else {
    echo "✅ Couverture complète !\n";
}

echo "\n";

// Sauvegarder rapport
$reportDir = $projectRoot . '/dev/tmp/quiz_coverage_analysis';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

// JSON structural
$coverageReport = [
    'timestamp' => date('c'),
    'total_valid_quizzes' => count($quizzes),
    'levels' => array_map(fn($l) => ['level' => $l, 'count' => $byLevel[$l]], array_keys($levelsSorted)),
    'subjects' => array_map(fn($s) => ['subject' => $s, 'count' => $bySubject[$s]], array_keys($bySubject)),
    'coverage_matrix' => $matrix,
    'coverage_stats' => [
        'expected_combinations' => $totalCoverage,
        'actual_combinations' => $actualCoverage,
        'coverage_percentage' => $coveragePct,
        'gaps_count' => count($gaps),
    ],
];

file_put_contents(
    $reportDir . '/coverage_analysis.json',
    json_encode($coverageReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "📊 Rapport détaillé sauvegardé : $reportDir/coverage_analysis.json\n\n";

echo "✅ ANALYSE COMPLÈTE\n\n";
