<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  ANALYSE DOUBLONS v3 - Normalisation HTML améliorée
 * ═══════════════════════════════════════════════════════════════
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('DB_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('MIGRATED_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/duplicate_final_analysis.txt');
define('STRATEGY_FILE', PROJECT_ROOT . '/dev/reports/deduplication_strategy.json');

require_once PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseNormalizer.php';

// ═══════════════════════════════════════════════════════════════
// NORMALISATION AMÉLIORÉE
// ═══════════════════════════════════════════════════════════════

function normalizeHtmlContentImproved($text): string {
    if (!is_string($text)) {
        return '';
    }

    // Décoder TOUTES les entités HTML (plusieurs passes si nécessaire)
    $maxPasses = 5;
    for ($i = 0; $i < $maxPasses; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) break; // Plus rien à décoder
        $text = $decoded;
    }

    // Supprimer balises HTML
    $text = strip_tags($text);

    // Normaliser Unicode (± etc.)
    $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

    // Normaliser espaces
    $text = preg_replace('/\s+/', ' ', $text);

    // Trim
    $text = trim($text);

    return $text;
}

function normalizeJsonField($value): string {
    if (is_array($value)) {
        // Normaliser les tableaux pour comparaison
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    if (is_string($value)) {
        return normalizeHtmlContentImproved($value);
    }
    return (string)$value;
}

function normalizeExerciseForComparison($ex): array {
    $fieldsToNormalize = ['Title', 'Content', 'Answer', 'Tips', 'Domain', 'Competence'];
    $jsonFields = ['Choices', 'InteractiveConfig'];

    $normalized = $ex;

    foreach ($fieldsToNormalize as $field) {
        if (isset($normalized[$field])) {
            $normalized[$field] = normalizeHtmlContentImproved($normalized[$field]);
        }
    }

    foreach ($jsonFields as $field) {
        if (isset($normalized[$field])) {
            $normalized[$field] = normalizeJsonField($normalized[$field]);
        }
    }

    return $normalized;
}

// ═══════════════════════════════════════════════════════════════
// ANALYSE ET STRATÉGIE
// ═══════════════════════════════════════════════════════════════

function loadExercises(string $file, string $source): array {
    $json = file_get_contents($file);
    $exercises = json_decode($json, true);

    $normalized = [];
    foreach ($exercises as $ex) {
        $clean = ExerciseNormalizer::normalizeToDbFields($ex);
        $clean['_source'] = $source;
        $clean['_original'] = $ex;
        $normalized[] = $clean;
    }

    return $normalized;
}

function compareExercisesImproved($ex1, $ex2): array {
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
                'value1' => $val1,
                'source2' => $ex2['_source'],
                'value2' => $val2
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

function categorizeExercisePair($ex1, $ex2, $comparison): string {
    $similarity = $comparison['similarity_percent'];
    $diffs = $comparison['differences'];

    // 100% identique après normalisation
    if ($similarity === 100.0) {
        return 'identical';
    }

    // Vérifier si version incomplète
    $hasNullInDb = false;
    $hasValueInJson = false;

    foreach ($diffs as $field => $diff) {
        if ($diff['source1'] === 'database' && empty($diff['value1']) && !empty($diff['value2'])) {
            $hasNullInDb = true;
            $hasValueInJson = true;
        }
    }

    if ($hasNullInDb && $hasValueInJson && $similarity >= 70) {
        return 'incomplete_vs_complete';
    }

    // Vérifier si contenu très différent
    if (isset($diffs['Content']) || isset($diffs['Answer'])) {
        $contentSimilar = true;
        if (isset($diffs['Content'])) {
            $v1 = $diffs['Content']['value1'] ?? '';
            $v2 = $diffs['Content']['value2'] ?? '';
            similar_text($v1, $v2, $percent);
            if ($percent < 50) {
                $contentSimilar = false;
            }
        }

        if (!$contentSimilar && $similarity < 50) {
            return 'different_content';
        }
    }

    // Différences mineures
    if ($similarity >= 80) {
        return 'minor_differences';
    }

    return 'significant_differences';
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
echo "  ANALYSE FINALE + STRATÉGIE DE DÉDOUBLONNAGE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

echo "📄 Chargement...\n";
$dbExercises = loadExercises(DB_JSON_FILE, 'database');
$jsonExercises = loadExercises(MIGRATED_JSON_FILE, 'migrated_json');
$allExercises = array_merge($dbExercises, $jsonExercises);
echo sprintf("✅ %d exercices chargés\n\n", count($allExercises));

echo "🔍 Analyse avec normalisation améliorée...\n";
$duplicates = analyzeDuplicates($allExercises);
echo sprintf("✅ %d identifiers dupliqués\n\n", count($duplicates));

$categories = [
    'identical' => [],
    'incomplete_vs_complete' => [],
    'minor_differences' => [],
    'different_content' => [],
    'significant_differences' => []
];

$stats = [
    'identical' => 0,
    'incomplete_vs_complete' => 0,
    'minor_differences' => 0,
    'different_content' => 0,
    'significant_differences' => 0
];

echo "📊 Catégorisation...\n";

foreach ($duplicates as $id => $group) {
    $base = $group[0];

    foreach (array_slice($group, 1) as $duplicate) {
        $comparison = compareExercisesImproved($base, $duplicate);
        $category = categorizeExercisePair($base, $duplicate, $comparison);

        $stats[$category]++;
        $categories[$category][] = [
            'identifier' => $id,
            'title' => $base['Title'] ?? 'Sans titre',
            'similarity' => $comparison['similarity_percent'],
            'comparison' => $comparison
        ];
    }
}

$total = array_sum($stats);

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉSULTATS FINAUX\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("✅ Identiques à 100%%:          %4d (%.1f%%) → Supprimer doublon BDD\n",
    $stats['identical'], ($stats['identical'] / max($total, 1)) * 100);
echo sprintf("📝 Version incomplète vs complète: %4d (%.1f%%) → Garder JSON migré\n",
    $stats['incomplete_vs_complete'], ($stats['incomplete_vs_complete'] / max($total, 1)) * 100);
echo sprintf("🔧 Différences mineures:       %4d (%.1f%%) → Garder JSON migré\n",
    $stats['minor_differences'], ($stats['minor_differences'] / max($total, 1)) * 100);
echo sprintf("❌ Contenu différent:           %4d (%.1f%%) → RENOMMER l'Identifier\n",
    $stats['different_content'], ($stats['different_content'] / max($total, 1)) * 100);
echo sprintf("⚠️  Différences significatives: %4d (%.1f%%) → Revue manuelle\n",
    $stats['significant_differences'], ($stats['significant_differences'] / max($total, 1)) * 100);
echo "═══════════════════════════════════════════════════════════════\n";

// Sauvegarder la stratégie
$strategy = [
    'stats' => $stats,
    'categories' => [
        'identical' => array_slice($categories['identical'], 0, 10),
        'incomplete_vs_complete' => array_slice($categories['incomplete_vs_complete'], 0, 10),
        'minor_differences' => array_slice($categories['minor_differences'], 0, 10),
        'different_content' => $categories['different_content'],
        'significant_differences' => $categories['significant_differences']
    ]
];

$reportDir = dirname(STRATEGY_FILE);
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

file_put_contents(STRATEGY_FILE, json_encode($strategy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n📄 Stratégie sauvegardée: dev/reports/deduplication_strategy.json\n\n";
