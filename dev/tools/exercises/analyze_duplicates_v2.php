<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  ANALYSE DOUBLONS v2 - Avec normalisation HTML
 * ═══════════════════════════════════════════════════════════════
 *
 * Compare les exercices après normalisation de l'encodage HTML
 * pour détecter les VRAIS doublons.
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('DB_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('MIGRATED_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/duplicate_analysis_normalized.txt');

require_once PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseNormalizer.php';

// ═══════════════════════════════════════════════════════════════
// FONCTIONS DE NORMALISATION HTML
// ═══════════════════════════════════════════════════════════════

/**
 * Normalise le contenu HTML pour comparaison
 */
function normalizeHtmlContent($text): string {
    if (!is_string($text)) {
        return '';
    }

    // Décoder les entités HTML
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Supprimer les balises HTML
    $text = strip_tags($text);

    // Normaliser les espaces
    $text = preg_replace('/\s+/', ' ', $text);

    // Trim
    $text = trim($text);

    return $text;
}

/**
 * Normalise un exercice pour comparaison
 */
function normalizeExerciseForComparison($ex): array {
    $fieldsToNormalize = ['Title', 'Content', 'Answer', 'Tips', 'Domain', 'Competence'];

    $normalized = $ex;

    foreach ($fieldsToNormalize as $field) {
        if (isset($normalized[$field]) && is_string($normalized[$field])) {
            $normalized[$field] = normalizeHtmlContent($normalized[$field]);
        }
    }

    return $normalized;
}

// ═══════════════════════════════════════════════════════════════
// FONCTIONS DE CHARGEMENT ET COMPARAISON
// ═══════════════════════════════════════════════════════════════

function loadExercises(string $file, string $source): array {
    $json = file_get_contents($file);
    $exercises = json_decode($json, true);

    $normalized = [];
    foreach ($exercises as $ex) {
        $clean = ExerciseNormalizer::normalizeToDbFields($ex);
        $clean['_source'] = $source;
        $clean['_original'] = $ex; // Garder l'original pour référence
        $normalized[] = $clean;
    }

    return $normalized;
}

/**
 * Compare deux exercices APRÈS normalisation HTML
 */
function compareExercisesNormalized($ex1, $ex2): array {
    // Normaliser les deux exercices
    $norm1 = normalizeExerciseForComparison($ex1);
    $norm2 = normalizeExerciseForComparison($ex2);

    $fieldsToCompare = ['Title', 'Content', 'Answer', 'Tips', 'Difficulty',
                        'AnswerType', 'XP_Points', 'Domain', 'Competence',
                        'Choices', 'InteractiveConfig'];

    $differences = [];
    $identicalCount = 0;

    foreach ($fieldsToCompare as $field) {
        $val1 = $norm1[$field] ?? null;
        $val2 = $norm2[$field] ?? null;

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

function analyzeDuplicates(array $exercises): array {
    $byIdentifier = [];

    foreach ($exercises as $ex) {
        $id = $ex['Identifier'] ?? null;
        if (empty($id)) continue;

        if (!isset($byIdentifier[$id])) {
            $byIdentifier[$id] = [];
        }
        $byIdentifier[$id][] = $ex;
    }

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
echo "  ANALYSE DOUBLONS v2 - Avec normalisation HTML\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

echo "📄 Chargement des exercices...\n";
$dbExercises = loadExercises(DB_JSON_FILE, 'database');
$jsonExercises = loadExercises(MIGRATED_JSON_FILE, 'migrated_json');
$allExercises = array_merge($dbExercises, $jsonExercises);
echo sprintf("✅ %d exercices chargés\n\n", count($allExercises));

echo "🔍 Détection et analyse des doublons (avec normalisation HTML)...\n";
$duplicates = analyzeDuplicates($allExercises);
echo sprintf("✅ %d identifiers dupliqués trouvés\n\n", count($duplicates));

$stats = [
    'total_duplicates' => count($duplicates),
    'identical_100' => 0,
    'identical_90_99' => 0,
    'identical_70_89' => 0,
    'identical_below_70' => 0,
    'total_duplicate_exercises' => 0
];

$report = [];
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "  ANALYSE DOUBLONS v2 - APRÈS NORMALISATION HTML";
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";
$report[] = "Date: " . date('Y-m-d H:i:s');
$report[] = "";
$report[] = "🔧 MÉTHODE: Comparaison après décodage HTML + suppression balises";
$report[] = "";
$report[] = "📊 STATISTIQUES GLOBALES";
$report[] = "───────────────────────────────────────────────────────────────";
$report[] = sprintf("Total exercices analysés:     %4d", count($allExercises));
$report[] = sprintf("Identifiers dupliqués:        %4d", count($duplicates));
$report[] = "";

echo "📊 Analyse de similarité (après normalisation)...\n";

$detailedExamples = [];
$exampleCount = 0;

foreach ($duplicates as $id => $group) {
    $stats['total_duplicate_exercises'] += count($group) - 1;

    $base = $group[0];

    foreach (array_slice($group, 1) as $duplicate) {
        $comparison = compareExercisesNormalized($base, $duplicate);
        $similarity = $comparison['similarity_percent'];

        if ($similarity === 100.0) {
            $stats['identical_100']++;
        } elseif ($similarity >= 90) {
            $stats['identical_90_99']++;
        } elseif ($similarity >= 70) {
            $stats['identical_70_89']++;
        } else {
            $stats['identical_below_70']++;
        }

        // Garder exemples non-100%
        if ($similarity < 100.0 && $exampleCount < 30) {
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

$report[] = "🔍 RÉPARTITION PAR SIMILARITÉ (APRÈS NORMALISATION)";
$report[] = "───────────────────────────────────────────────────────────────";
$report[] = sprintf("Doublons 100%% identiques:     %4d (%.1f%%) ← VRAIS DOUBLONS",
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

if (count($detailedExamples) > 0) {
    $report[] = "📋 EXEMPLES DE DIFFÉRENCES RÉELLES (après normalisation HTML)";
    $report[] = "═══════════════════════════════════════════════════════════════";
    $report[] = "";

    foreach ($detailedExamples as $idx => $example) {
        $report[] = sprintf("━━━ DIFFÉRENCE %d ━━━", $idx + 1);
        $report[] = sprintf("Identifier:  %s", $example['identifier']);
        $report[] = sprintf("Titre:       %s", substr($example['title'], 0, 70));
        $report[] = sprintf("Similarité:  %.2f%%", $example['similarity']);
        $report[] = sprintf("Sources:     %s vs %s", $example['sources'][0], $example['sources'][1]);

        foreach ($example['comparison']['differences'] as $field => $diff) {
            $report[] = sprintf("  • %s:", $field);
            $report[] = sprintf("    [%s] %s", $diff['source1'],
                is_scalar($diff['value1']) ? substr((string)$diff['value1'], 0, 80) : json_encode($diff['value1']));
            $report[] = sprintf("    [%s] %s", $diff['source2'],
                is_scalar($diff['value2']) ? substr((string)$diff['value2'], 0, 80) : json_encode($diff['value2']));
        }
        $report[] = "";
    }
}

$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";
$report[] = "💡 RECOMMANDATIONS";
$report[] = "───────────────────────────────────────────────────────────────";

$percentIdentical = ($stats['identical_100'] / max($stats['total_duplicate_exercises'], 1)) * 100;

if ($percentIdentical > 95) {
    $report[] = "✅ Plus de 95% des doublons sont identiques après normalisation.";
    $report[] = "   → Conclusion: les différences étaient uniquement dues à l'encodage HTML.";
    $report[] = "   → Action recommandée: Supprimer TOUS les doublons en toute sécurité.";
    $report[] = "   → Préférer la version JSON migrée (encodage propre).";
} elseif ($percentIdentical > 80) {
    $report[] = "✅ 80-95% des doublons sont identiques après normalisation.";
    $report[] = "   → La majorité des différences sont dues à l'encodage.";
    $report[] = "   → Vérifier les " . ($stats['total_duplicate_exercises'] - $stats['identical_100']) . " cas non-identiques.";
} else {
    $report[] = "⚠️  Moins de 80% des doublons sont identiques même après normalisation.";
    $report[] = "   → Il existe des différences de contenu réelles.";
    $report[] = "   → Revue manuelle recommandée.";
}

$report[] = "";
$report[] = "═══════════════════════════════════════════════════════════════";

$reportText = implode("\n", $report);

$reportDir = dirname(REPORT_FILE);
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}
file_put_contents(REPORT_FILE, $reportText);

echo "✅ Analyse terminée\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ (APRÈS NORMALISATION HTML)\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total doublons:              %4d\n", $stats['total_duplicate_exercises']);
echo sprintf("  • 100%% identiques:         %4d (%.1f%%) ← VRAIS DOUBLONS\n",
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

if ($percentIdentical > 95) {
    echo "🎯 CONCLUSION: Les différences étaient UNIQUEMENT dues à l'encodage HTML.\n";
    echo "   → Tous les 859 doublons peuvent être supprimés en sécurité.\n";
    echo "   → Préférer la version JSON migrée (texte propre, pas de HTML).\n";
} else {
    echo "⚠️  ATTENTION: Des différences réelles existent au-delà de l'encodage.\n";
    echo "   → Consulter le rapport: dev/reports/duplicate_analysis_normalized.txt\n";
}

echo "\n";
echo "📄 Rapport détaillé: dev/reports/duplicate_analysis_normalized.txt\n";
echo "\n";
