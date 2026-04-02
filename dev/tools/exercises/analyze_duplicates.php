<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  ANALYSE APPROFONDIE DES DOUBLONS
 * ═══════════════════════════════════════════════════════════════
 *
 * Compare les doublons pour déterminer s'ils sont vraiment identiques
 * ou s'il s'agit de versions différentes du même exercice.
 *
 * Usage:
 *   php dev/tools/exercises/analyze_duplicates.php
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('DB_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('MIGRATED_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/duplicate_analysis_detailed.txt');

require_once PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseNormalizer.php';

// ═══════════════════════════════════════════════════════════════
// FONCTIONS
// ═══════════════════════════════════════════════════════════════

function loadExercises(string $file, string $source): array {
    $json = file_get_contents($file);
    $exercises = json_decode($json, true);

    $normalized = [];
    foreach ($exercises as $ex) {
        $clean = ExerciseNormalizer::normalizeToDbFields($ex);
        $clean['_source'] = $source;
        $normalized[] = $clean;
    }

    return $normalized;
}

/**
 * Compare deux exercices et retourne les différences
 */
function compareExercises($ex1, $ex2): array {
    $fieldsToCompare = ['Title', 'Content', 'Answer', 'Tips', 'Difficulty',
                        'AnswerType', 'XP_Points', 'Domain', 'Competence',
                        'Choices', 'InteractiveConfig'];

    $differences = [];
    $identicalCount = 0;

    foreach ($fieldsToCompare as $field) {
        $val1 = $ex1[$field] ?? null;
        $val2 = $ex2[$field] ?? null;

        // Normaliser pour comparaison
        if (is_string($val1)) $val1 = trim($val1);
        if (is_string($val2)) $val2 = trim($val2);

        if ($val1 === $val2) {
            $identicalCount++;
        } else {
            $differences[$field] = [
                'source1' => $ex1['_source'],
                'value1' => is_string($val1) ? substr($val1, 0, 100) : $val1,
                'source2' => $ex2['_source'],
                'value2' => is_string($val2) ? substr($val2, 0, 100) : $val2
            ];
        }
    }

    return [
        'identical_fields' => $identicalCount,
        'total_fields' => count($fieldsToCompare),
        'similarity_percent' => round(($identicalCount / count($fieldsToCompare)) * 100, 2),
        'differences' => $differences
    ];
}

/**
 * Analyse tous les doublons
 */
function analyzeDuplicates(array $exercises): array {
    $byIdentifier = [];

    // Grouper par Identifier
    foreach ($exercises as $ex) {
        $id = $ex['Identifier'] ?? null;
        if (empty($id)) continue;

        if (!isset($byIdentifier[$id])) {
            $byIdentifier[$id] = [];
        }
        $byIdentifier[$id][] = $ex;
    }

    // Trouver les doublons
    $duplicates = [];
    foreach ($byIdentifier as $id => $group) {
        if (count($group) > 1) {
            $duplicates[$id] = $group;
        }
    }

    return $duplicates;
}

// ═══════════════════════════════════════════════════════════════
// SCRIPT PRINCIPAL
// ═══════════════════════════════════════════════════════════════

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ANALYSE APPROFONDIE DES DOUBLONS\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

echo "📄 Chargement des exercices...\n";
$dbExercises = loadExercises(DB_JSON_FILE, 'database');
$jsonExercises = loadExercises(MIGRATED_JSON_FILE, 'migrated_json');
$allExercises = array_merge($dbExercises, $jsonExercises);
echo sprintf("✅ %d exercices chargés\n\n", count($allExercises));

echo "🔍 Détection et analyse des doublons...\n";
$duplicates = analyzeDuplicates($allExercises);
echo sprintf("✅ %d identifiers dupliqués trouvés\n\n", count($duplicates));

// Analyse détaillée
$stats = [
    'total_duplicates' => count($duplicates),
    'identical_100' => 0,      // 100% identiques
    'identical_90_99' => 0,    // 90-99% similaires
    'identical_70_89' => 0,    // 70-89% similaires
    'identical_below_70' => 0, // < 70% similaires
    'total_duplicate_exercises' => 0
];

$report = [];
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "  ANALYSE DÉTAILLÉE DES DOUBLONS";
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";
$report[] = "Date: " . date('Y-m-d H:i:s');
$report[] = "";
$report[] = "📊 STATISTIQUES GLOBALES";
$report[] = "───────────────────────────────────────────────────────────────";
$report[] = sprintf("Total exercices analysés:     %4d", count($allExercises));
$report[] = sprintf("Identifiers uniques:          %4d", count($duplicates) + (count($allExercises) - array_sum(array_map('count', $duplicates))));
$report[] = sprintf("Identifiers dupliqués:        %4d", count($duplicates));
$report[] = "";

echo "📊 Analyse de similarité...\n";

$detailedExamples = [];
$exampleCount = 0;

foreach ($duplicates as $id => $group) {
    $stats['total_duplicate_exercises'] += count($group) - 1;

    // Comparer le premier avec les autres
    $base = $group[0];

    foreach (array_slice($group, 1) as $duplicate) {
        $comparison = compareExercises($base, $duplicate);
        $similarity = $comparison['similarity_percent'];

        // Catégoriser
        if ($similarity === 100.0) {
            $stats['identical_100']++;
        } elseif ($similarity >= 90) {
            $stats['identical_90_99']++;
        } elseif ($similarity >= 70) {
            $stats['identical_70_89']++;
        } else {
            $stats['identical_below_70']++;
        }

        // Garder des exemples détaillés (max 20)
        if ($exampleCount < 20) {
            $detailedExamples[] = [
                'identifier' => $id,
                'title' => $base['Title'] ?? 'Sans titre',
                'similarity' => $similarity,
                'comparison' => $comparison,
                'sources' => [
                    $base['_source'],
                    $duplicate['_source']
                ]
            ];
            $exampleCount++;
        }
    }
}

$report[] = "🔍 RÉPARTITION PAR SIMILARITÉ";
$report[] = "───────────────────────────────────────────────────────────────";
$report[] = sprintf("Doublons 100%% identiques:     %4d (%.1f%%)",
    $stats['identical_100'],
    ($stats['identical_100'] / max($stats['total_duplicate_exercises'], 1)) * 100);
$report[] = sprintf("Doublons 90-99%% similaires:   %4d (%.1f%%)",
    $stats['identical_90_99'],
    ($stats['identical_90_99'] / max($stats['total_duplicate_exercises'], 1)) * 100);
$report[] = sprintf("Doublons 70-89%% similaires:   %4d (%.1f%%)",
    $stats['identical_70_89'],
    ($stats['identical_70_89'] / max($stats['total_duplicate_exercises'], 1)) * 100);
$report[] = sprintf("Doublons < 70%% similaires:    %4d (%.1f%%)",
    $stats['identical_below_70'],
    ($stats['identical_below_70'] / max($stats['total_duplicate_exercises'], 1)) * 100);
$report[] = "";

$report[] = "📋 EXEMPLES DÉTAILLÉS (20 premiers)";
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";

foreach ($detailedExamples as $idx => $example) {
    $report[] = sprintf("━━━ EXEMPLE %d ━━━", $idx + 1);
    $report[] = sprintf("Identifier:  %s", $example['identifier']);
    $report[] = sprintf("Titre:       %s", substr($example['title'], 0, 70));
    $report[] = sprintf("Similarité:  %.2f%%", $example['similarity']);
    $report[] = sprintf("Sources:     %s vs %s", $example['sources'][0], $example['sources'][1]);

    if (count($example['comparison']['differences']) > 0) {
        $report[] = "Différences détectées:";
        foreach ($example['comparison']['differences'] as $field => $diff) {
            $report[] = sprintf("  • %s:", $field);
            $report[] = sprintf("    [%s] %s", $diff['source1'],
                is_scalar($diff['value1']) ? substr((string)$diff['value1'], 0, 80) : json_encode($diff['value1']));
            $report[] = sprintf("    [%s] %s", $diff['source2'],
                is_scalar($diff['value2']) ? substr((string)$diff['value2'], 0, 80) : json_encode($diff['value2']));
        }
    } else {
        $report[] = "✅ Exercice identique à 100%";
    }
    $report[] = "";
}

$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";
$report[] = "💡 RECOMMANDATIONS";
$report[] = "───────────────────────────────────────────────────────────────";

$percentIdentical = ($stats['identical_100'] / max($stats['total_duplicate_exercises'], 1)) * 100;

if ($percentIdentical > 80) {
    $report[] = "✅ Plus de 80% des doublons sont identiques à 100%.";
    $report[] = "   → Suppression sûre : les doublons sont de vraies copies.";
} elseif ($percentIdentical > 50) {
    $report[] = "⚠️  50-80% des doublons sont identiques.";
    $report[] = "   → Vérifier les doublons non-identiques avant suppression.";
} else {
    $report[] = "❌ Moins de 50% des doublons sont vraiment identiques.";
    $report[] = "   → ATTENTION : beaucoup de doublons sont des versions différentes.";
    $report[] = "   → Nécessite une revue manuelle ou une stratégie de merge.";
}

$report[] = "";
$report[] = "═══════════════════════════════════════════════════════════════";

$reportText = implode("\n", $report);

// Écrire le rapport
$reportDir = dirname(REPORT_FILE);
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}
file_put_contents(REPORT_FILE, $reportText);

echo "✅ Analyse terminée\n\n";

// Afficher résumé console
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ DE L'ANALYSE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total doublons:              %4d\n", $stats['total_duplicate_exercises']);
echo sprintf("  • 100%% identiques:         %4d (%.1f%%)\n",
    $stats['identical_100'],
    ($stats['identical_100'] / max($stats['total_duplicate_exercises'], 1)) * 100);
echo sprintf("  • 90-99%% similaires:       %4d (%.1f%%)\n",
    $stats['identical_90_99'],
    ($stats['identical_90_99'] / max($stats['total_duplicate_exercises'], 1)) * 100);
echo sprintf("  • 70-89%% similaires:       %4d (%.1f%%)\n",
    $stats['identical_70_89'],
    ($stats['identical_70_89'] / max($stats['total_duplicate_exercises'], 1)) * 100);
echo sprintf("  • < 70%% similaires:        %4d (%.1f%%)\n",
    $stats['identical_below_70'],
    ($stats['identical_below_70'] / max($stats['total_duplicate_exercises'], 1)) * 100);
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "📄 Rapport détaillé: dev/reports/duplicate_analysis_detailed.txt\n";
echo "\n";
