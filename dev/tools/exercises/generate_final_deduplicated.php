<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  GÉNÉRATION DU FICHIER FINAL DÉDOUBLONNÉ
 * ═══════════════════════════════════════════════════════════════
 *
 * Applique la stratégie de dédoublonnage intelligente :
 * - Supprime les 851 vrais doublons (garde JSON migré)
 * - Renomme les 5 Identifiers dupliqués
 * - Choisit la meilleure version pour les 3 cas significatifs
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('DB_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('MIGRATED_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('OUTPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_final_deduplicated.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/final_deduplication_report.txt');

require_once PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseNormalizer.php';

// ═══════════════════════════════════════════════════════════════
// CONFIGURATION DES CAS SPÉCIAUX
// ═══════════════════════════════════════════════════════════════

// Identifiers à renommer (JSON migré)
$identifiersToRename = [
    'MATHEMATIQUES-3EME-GEOMETRIE-001' => 'MATHEMATIQUES-3EME-GEOMETRIE-001-ECHELLE',
    'MATHEMATIQUES-3EME-PROBABILITES-001' => 'MATHEMATIQUES-3EME-PROBABILITES-001-CALCULS',
    'ANGLAIS-6EME-QUESTIONS-001' => 'ANGLAIS-6EME-QUESTIONS-001-PRESENTATION',
    'MATHEMATIQUES-PREMIERE-SUITE-001' => 'MATHEMATIQUES-PREMIERE-SUITE-001-ARITHM-GEOM',
    'MATHEMATIQUES-SECONDE-FONCTIONS-004' => 'MATHEMATIQUES-SECONDE-STATISTIQUES-AVANCEES-001'
];

// Cas où on préfère la version BDD (au lieu de JSON migré)
$preferDatabase = [
    'MATHEMATIQUES-3EME-EQUATIONS-001',
    'MATHEMATIQUES-3EME-LECTURE-001',
    'MATHEMATIQUES-3EME-CALCUL-001'
];

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

// ═══════════════════════════════════════════════════════════════
// SCRIPT PRINCIPAL
// ═══════════════════════════════════════════════════════════════

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  GÉNÉRATION FICHIER FINAL DÉDOUBLONNÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

echo "📄 Chargement des sources...\n";
$dbExercises = loadExercises(DB_JSON_FILE, 'database');
$jsonExercises = loadExercises(MIGRATED_JSON_FILE, 'migrated_json');
echo sprintf("✅ %d exercices BDD + %d exercices JSON\n\n", count($dbExercises), count($jsonExercises));

echo "🔧 Application de la stratégie...\n";

$byIdentifier = [];
$stats = [
    'total_db' => count($dbExercises),
    'total_json' => count($jsonExercises),
    'duplicates_removed' => 0,
    'identifiers_renamed' => 0,
    'db_preferred' => 0,
    'json_preferred' => 0,
    'final_count' => 0
];

// Étape 1 : Indexer par Identifier
foreach ($dbExercises as $ex) {
    $id = $ex['Identifier'] ?? null;
    if (empty($id)) continue;

    if (!isset($byIdentifier[$id])) {
        $byIdentifier[$id] = [];
    }
    $byIdentifier[$id][] = $ex;
}

foreach ($jsonExercises as $ex) {
    $id = $ex['Identifier'] ?? null;
    if (empty($id)) continue;

    // Renommer si dans la liste
    if (isset($identifiersToRename[$id])) {
        $newId = $identifiersToRename[$id];
        $ex['Identifier'] = $newId;
        $id = $newId;
        $stats['identifiers_renamed']++;
        echo sprintf("   ✏️  Renommé: %s → %s\n", array_search($newId, $identifiersToRename), $newId);
    }

    if (!isset($byIdentifier[$id])) {
        $byIdentifier[$id] = [];
    }
    $byIdentifier[$id][] = $ex;
}

echo "\n";

// Étape 2 : Choisir la meilleure version
$finalExercises = [];

foreach ($byIdentifier as $id => $group) {
    if (count($group) === 1) {
        // Pas de doublon
        $finalExercises[] = $group[0];
    } else {
        // Doublon détecté : choisir version
        $dbVersion = null;
        $jsonVersion = null;

        foreach ($group as $ex) {
            if ($ex['_source'] === 'database') {
                $dbVersion = $ex;
            } else {
                $jsonVersion = $ex;
            }
        }

        // Décision
        if (in_array($id, $preferDatabase) && $dbVersion) {
            $finalExercises[] = $dbVersion;
            $stats['db_preferred']++;
        } elseif ($jsonVersion) {
            $finalExercises[] = $jsonVersion;
            $stats['json_preferred']++;
        } else {
            $finalExercises[] = $dbVersion;
            $stats['db_preferred']++;
        }

        $stats['duplicates_removed'] += count($group) - 1;
    }
}

$stats['final_count'] = count($finalExercises);

echo "📊 Statistiques:\n";
echo sprintf("   Doublons supprimés:    %4d\n", $stats['duplicates_removed']);
echo sprintf("   Identifiers renommés:  %4d\n", $stats['identifiers_renamed']);
echo sprintf("   Version BDD préférée:  %4d\n", $stats['db_preferred']);
echo sprintf("   Version JSON préférée: %4d\n", $stats['json_preferred']);
echo sprintf("   Total final:           %4d\n\n", $stats['final_count']);

// Étape 3 : Nettoyer et écrire
foreach ($finalExercises as &$ex) {
    unset($ex['_source']);
}
unset($ex);

$outputDir = dirname(OUTPUT_FILE);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

echo "💾 Écriture du fichier final...\n";
$json = json_encode($finalExercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(OUTPUT_FILE, $json);
$size = filesize(OUTPUT_FILE);
echo sprintf("✅ Fichier créé: %s (%.2f MB)\n\n", OUTPUT_FILE, $size / 1048576);

// Rapport
$report = [];
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "  RAPPORT FINAL DE DÉDOUBLONNAGE";
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";
$report[] = "Date: " . date('Y-m-d H:i:s');
$report[] = "";
$report[] = "📊 STATISTIQUES";
$report[] = "───────────────────────────────────────────────────────────────";
$report[] = sprintf("Exercices BDD:            %4d", $stats['total_db']);
$report[] = sprintf("Exercices JSON:           %4d", $stats['total_json']);
$report[] = sprintf("Total avant fusion:       %4d", $stats['total_db'] + $stats['total_json']);
$report[] = "";
$report[] = sprintf("Doublons supprimés:       %4d", $stats['duplicates_removed']);
$report[] = sprintf("Identifiers renommés:     %4d", $stats['identifiers_renamed']);
$report[] = sprintf("Version BDD préférée:     %4d", $stats['db_preferred']);
$report[] = sprintf("Version JSON préférée:    %4d", $stats['json_preferred']);
$report[] = "";
$report[] = sprintf("TOTAL FINAL:              %4d", $stats['final_count']);
$report[] = "";
$report[] = "✅ IDENTIFIERS RENOMMÉS";
$report[] = "───────────────────────────────────────────────────────────────";
foreach ($identifiersToRename as $old => $new) {
    $report[] = sprintf("• %s", $old);
    $report[] = sprintf("  → %s", $new);
}
$report[] = "";
$report[] = "📝 CAS SPÉCIAUX (Version BDD préférée)";
$report[] = "───────────────────────────────────────────────────────────────";
foreach ($preferDatabase as $id) {
    $report[] = sprintf("• %s (version BDD plus complète)", $id);
}
$report[] = "";
$report[] = "═══════════════════════════════════════════════════════════════";

$reportDir = dirname(REPORT_FILE);
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

file_put_contents(REPORT_FILE, implode("\n", $report));

echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ DÉDOUBLONNAGE TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Fichier final:  %s\n", basename(OUTPUT_FILE));
echo sprintf("Taille:         %.2f MB\n", $size / 1048576);
echo sprintf("Exercices:      %d (au lieu de %d)\n", $stats['final_count'], $stats['total_db'] + $stats['total_json']);
echo sprintf("Économie:       %d doublons supprimés\n", $stats['duplicates_removed']);
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "📋 Prochaine étape:\n";
echo "   php dev/tools/exercises/import_unified_exercises_v2.php --dry-run\n";
echo "   (Pointer sur exercises_final_deduplicated.json)\n";
echo "\n";
