<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  SCRIPT DE DÉDOUBLONNAGE D'EXERCICES v4 (DEBUG INTENSIF)
 * ═══════════════════════════════════════════════════════════════
 *
 * Version avec debug intensif pour identifier le problème exact.
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('DB_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('MIGRATED_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('OUTPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_deduplicated.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/deduplication_report.txt');
define('DELETE_SQL_FILE', PROJECT_ROOT . '/dev/reports/delete_duplicates.sql');

require_once PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseNormalizer.php';

// ═══════════════════════════════════════════════════════════════
// FONCTIONS
// ═══════════════════════════════════════════════════════════════

function loadAndNormalizeExercises(string $file, string $source): array {
    echo "   📂 Lecture: $file\n";

    if (!file_exists($file)) {
        echo "   ❌ Fichier introuvable\n";
        return [];
    }

    $json = file_get_contents($file);
    $exercises = json_decode($json, true);

    if (!is_array($exercises)) {
        echo "   ❌ JSON invalide\n";
        return [];
    }

    echo "   ✅ " . count($exercises) . " exercices bruts chargés\n";

    $normalized = [];
    $debugCount = 0;

    foreach ($exercises as $index => $ex) {
        // Debug premier exercice uniquement
        if ($debugCount === 0) {
            echo "   🔍 DEBUG premier exercice (avant normalisation):\n";
            echo "      Clés: " . implode(', ', array_keys($ex)) . "\n";
            echo "      Identifier: " . ($ex['Identifier'] ?? 'ABSENT') . "\n";
        }

        $clean = ExerciseNormalizer::normalizeToDbFields($ex);

        if ($debugCount === 0) {
            echo "   🔍 DEBUG premier exercice (après normalisation):\n";
            echo "      Clés: " . implode(', ', array_keys($clean)) . "\n";
            echo "      Identifier: " . ($clean['Identifier'] ?? 'ABSENT') . "\n";
        }

        $clean['_source'] = $source;
        $normalized[] = $clean;
        $debugCount++;
    }

    echo "   ✅ " . count($normalized) . " exercices normalisés\n\n";
    return $normalized;
}

function detectDuplicates(array $exercises): array {
    echo "🔍 Détection des doublons...\n";
    echo "   Total exercices reçus: " . count($exercises) . "\n";

    $byIdentifier = [];
    $unique = [];
    $duplicates = [];
    $noIdCount = 0;

    foreach ($exercises as $index => $ex) {
        // Debug premier exercice
        if ($index === 0) {
            echo "   🔍 DEBUG premier exercice dans detectDuplicates:\n";
            echo "      Clés: " . implode(', ', array_keys($ex)) . "\n";
            echo "      Identifier: " . ($ex['Identifier'] ?? 'ABSENT') . "\n";
            echo "      _source: " . ($ex['_source'] ?? 'ABSENT') . "\n";
        }

        $id = $ex['Identifier'] ?? null;

        if (empty($id)) {
            $noIdCount++;
            $ex['_temp_id'] = 'NO_ID_' . uniqid();
            $unique[] = $ex;
            continue;
        }

        if (!isset($byIdentifier[$id])) {
            $byIdentifier[$id] = $ex;
            $unique[] = $ex;
        } else {
            $duplicates[] = [
                'identifier' => $id,
                'source_kept' => $byIdentifier[$id]['_source'] ?? 'unknown',
                'source_duplicate' => $ex['_source'] ?? 'unknown',
                'title_kept' => $byIdentifier[$id]['Title'] ?? '',
                'title_duplicate' => $ex['Title'] ?? ''
            ];
        }
    }

    echo "   ✅ Exercices uniques: " . count($unique) . "\n";
    echo "   ⚠️  Exercices sans ID: " . $noIdCount . "\n";
    echo "   🔁 Doublons détectés: " . count($duplicates) . "\n\n";

    return [
        'unique' => $unique,
        'duplicates' => $duplicates
    ];
}

function generateReport(array $dbExercises, array $jsonExercises, array $result): string {
    $report = [];
    $report[] = "═══════════════════════════════════════════════════════════════";
    $report[] = "  RAPPORT DE DÉDOUBLONNAGE D'EXERCICES";
    $report[] = "═══════════════════════════════════════════════════════════════";
    $report[] = "";
    $report[] = "Date: " . date('Y-m-d H:i:s');
    $report[] = "";

    $report[] = "📊 STATISTIQUES SOURCES";
    $report[] = "───────────────────────────────────────────────────────────────";
    $report[] = sprintf("Exercices BDD:           %4d", count($dbExercises));
    $report[] = sprintf("Exercices JSON migrés:   %4d", count($jsonExercises));
    $report[] = sprintf("Total avant fusion:      %4d", count($dbExercises) + count($jsonExercises));
    $report[] = "";

    $report[] = "🔍 RÉSULTATS DÉDOUBLONNAGE";
    $report[] = "───────────────────────────────────────────────────────────────";
    $report[] = sprintf("Exercices uniques:       %4d", count($result['unique']));
    $report[] = sprintf("Doublons détectés:       %4d", count($result['duplicates']));
    $report[] = "";

    if (count($result['duplicates']) > 0) {
        $report[] = "📋 DÉTAILS DES DOUBLONS";
        $report[] = "───────────────────────────────────────────────────────────────";
        foreach ($result['duplicates'] as $dup) {
            $report[] = sprintf(
                "• %s - conservé: %s | doublon: %s",
                $dup['identifier'],
                $dup['source_kept'],
                $dup['source_duplicate']
            );
            $report[] = "  Titre conservé: " . substr($dup['title_kept'], 0, 60);
            $report[] = "  Titre doublon:  " . substr($dup['title_duplicate'], 0, 60);
            $report[] = "";
        }
    }

    $bySubject = [];
    foreach ($result['unique'] as $ex) {
        $subj = $ex['Subject'] ?? 'unknown';
        $bySubject[$subj] = ($bySubject[$subj] ?? 0) + 1;
    }
    arsort($bySubject);

    $report[] = "📦 DISTRIBUTION PAR MATIÈRE";
    $report[] = "───────────────────────────────────────────────────────────────";
    foreach ($bySubject as $subj => $count) {
        $report[] = sprintf("  %-30s %4d", $subj, $count);
    }
    $report[] = "";

    $byLevel = [];
    foreach ($result['unique'] as $ex) {
        $lvl = $ex['Level'] ?? 'unknown';
        $byLevel[$lvl] = ($byLevel[$lvl] ?? 0) + 1;
    }
    arsort($byLevel);

    $report[] = "📦 DISTRIBUTION PAR NIVEAU";
    $report[] = "───────────────────────────────────────────────────────────────";
    foreach ($byLevel as $lvl => $count) {
        $report[] = sprintf("  %-30s %4d", $lvl, $count);
    }
    $report[] = "";

    $report[] = "═══════════════════════════════════════════════════════════════";

    return implode("\n", $report);
}

function generateDeleteSQL(array $duplicates): string {
    if (count($duplicates) === 0) {
        return "-- Aucun doublon à supprimer\n";
    }

    $sql = [];
    $sql[] = "-- ═══════════════════════════════════════════════════════════════";
    $sql[] = "-- SCRIPT DE SUPPRESSION DES DOUBLONS";
    $sql[] = "-- ═══════════════════════════════════════════════════════════════";
    $sql[] = "";

    foreach ($duplicates as $dup) {
        if ($dup['source_duplicate'] === 'database') {
            $sql[] = sprintf(
                "DELETE FROM exercises WHERE Identifier = '%s' LIMIT 1;",
                addslashes($dup['identifier'])
            );
        }
    }

    return implode("\n", $sql);
}

// ═══════════════════════════════════════════════════════════════
// SCRIPT PRINCIPAL
// ═══════════════════════════════════════════════════════════════

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  DÉDOUBLONNAGE D'EXERCICES v4 (DEBUG INTENSIF)\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

if (!class_exists('ExerciseNormalizer')) {
    die("❌ Erreur: ExerciseNormalizer introuvable\n");
}
echo "✅ ExerciseNormalizer chargé\n\n";

echo "📄 ÉTAPE 1: Chargement exercices BDD\n";
echo "───────────────────────────────────────────────────────────────\n";
$dbExercises = loadAndNormalizeExercises(DB_JSON_FILE, 'database');

echo "📄 ÉTAPE 2: Chargement exercices JSON migrés\n";
echo "───────────────────────────────────────────────────────────────\n";
$jsonExercises = loadAndNormalizeExercises(MIGRATED_JSON_FILE, 'migrated_json');

echo "🔀 ÉTAPE 3: Fusion des sources\n";
echo "───────────────────────────────────────────────────────────────\n";
$allExercises = array_merge($dbExercises, $jsonExercises);
echo "✅ Total: " . count($allExercises) . " exercices fusionnés\n";
echo "   🔍 DEBUG fusion:\n";
echo "      Premier exercice - Identifier: " . ($allExercises[0]['Identifier'] ?? 'ABSENT') . "\n";
echo "      Premier exercice - _source: " . ($allExercises[0]['_source'] ?? 'ABSENT') . "\n\n";

echo "🔍 ÉTAPE 4: Détection des doublons\n";
echo "───────────────────────────────────────────────────────────────\n";
$result = detectDuplicates($allExercises);

echo "🧹 ÉTAPE 5: Nettoyage des tags temporaires\n";
echo "───────────────────────────────────────────────────────────────\n";
foreach ($result['unique'] as &$ex) {
    unset($ex['_source']);
    unset($ex['_temp_id']);
}
unset($ex);
echo "✅ Tags nettoyés\n\n";

$outputDir = dirname(OUTPUT_FILE);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$reportDir = dirname(REPORT_FILE);
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

echo "💾 ÉTAPE 6: Écriture du fichier dédoublonné\n";
echo "───────────────────────────────────────────────────────────────\n";
$json = json_encode($result['unique'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(OUTPUT_FILE, $json);
$size = filesize(OUTPUT_FILE);
echo sprintf("✅ Fichier créé: %s (%.2f MB)\n\n", OUTPUT_FILE, $size / 1048576);

echo "📝 ÉTAPE 7: Génération du rapport\n";
echo "───────────────────────────────────────────────────────────────\n";
$report = generateReport($dbExercises, $jsonExercises, $result);
file_put_contents(REPORT_FILE, $report);
echo sprintf("✅ Rapport créé: %s\n\n", REPORT_FILE);

if (count($result['duplicates']) > 0) {
    echo "📝 ÉTAPE 8: Génération du script SQL\n";
    echo "───────────────────────────────────────────────────────────────\n";
    $sql = generateDeleteSQL($result['duplicates']);
    file_put_contents(DELETE_SQL_FILE, $sql);
    echo sprintf("✅ Script SQL créé: %s\n\n", DELETE_SQL_FILE);
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ DÉDOUBLONNAGE TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exercices uniques:    %4d\n", count($result['unique']));
echo sprintf("Doublons détectés:          %4d\n", count($result['duplicates']));
echo sprintf("Taille fichier final:       %.2f MB\n", $size / 1048576);
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
