<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  SCRIPT DE DÉDOUBLONNAGE D'EXERCICES v3 (CORRIGÉ)
 * ═══════════════════════════════════════════════════════════════
 *
 * Charge les exercices de la BDD et des JSON migrés, normalise,
 * détecte les doublons par Identifier, fusionne intelligemment,
 * et génère un fichier JSON propre + rapport détaillé.
 *
 * CORRECTIONS v3 :
 * - Fusion correcte BDD + JSON (au lieu de tableau vide)
 * - Gestion des champs JSON (Choices, InteractiveConfig, etc.)
 * - Détection précise des doublons par Identifier
 * - Conservation de tous les exercices uniques
 *
 * Usage:
 *   php dev/tools/exercises/deduplicate_exercises_v3.php
 *
 * Fichiers d'entrée:
 *   - dev/db/json/schema/exercices/exercises_from_database.json
 *   - dev/db/json/schema/exercices/unified_exercises.json
 *
 * Fichiers de sortie:
 *   - dev/db/json/schema/exercices/exercises_deduplicated.json
 *   - dev/reports/deduplication_report.txt
 *   - dev/reports/delete_duplicates.sql (si doublons détectés)
 */

// ═══════════════════════════════════════════════════════════════
// CONFIGURATION
// ═══════════════════════════════════════════════════════════════

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('DB_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('MIGRATED_JSON_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('OUTPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_deduplicated.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/deduplication_report.txt');
define('DELETE_SQL_FILE', PROJECT_ROOT . '/dev/reports/delete_duplicates.sql');

require_once __DIR__ . '/ExerciseNormalizer.php';

// ═══════════════════════════════════════════════════════════════
// FONCTIONS UTILITAIRES
// ═══════════════════════════════════════════════════════════════

/**
 * Charge et normalise un fichier JSON d'exercices
 */
function loadAndNormalizeExercises(string $file, string $source): array {
    if (!file_exists($file)) {
        return [];
    }

    $json = file_get_contents($file);
    $exercises = json_decode($json, true);

    if (!is_array($exercises)) {
        return [];
    }

    $normalized = [];
    foreach ($exercises as $ex) {
        // Normalisation vers les noms de colonnes BDD
        $clean = ExerciseNormalizer::normalizeToDbFields($ex);
        $clean['_source'] = $source; // Tag source
        $normalized[] = $clean;
    }

    return $normalized;
}

/**
 * Détecte les doublons par Identifier
 * Retourne: ['unique' => [...], 'duplicates' => [...]]
 */
function detectDuplicates(array $exercises): array {
    $byIdentifier = [];
    $unique = [];
    $duplicates = [];

    foreach ($exercises as $ex) {
        $id = $ex['Identifier'] ?? null;

        if (empty($id)) {
            // Pas d'identifiant → exercice unique (on génère un ID temporaire)
            $ex['_temp_id'] = 'NO_ID_' . uniqid();
            $unique[] = $ex;
            continue;
        }

        if (!isset($byIdentifier[$id])) {
            // Premier exercice avec cet Identifier
            $byIdentifier[$id] = $ex;
            $unique[] = $ex;
        } else {
            // Doublon détecté
            $duplicates[] = [
                'identifier' => $id,
                'source_kept' => $byIdentifier[$id]['_source'] ?? 'unknown',
                'source_duplicate' => $ex['_source'] ?? 'unknown',
                'title_kept' => $byIdentifier[$id]['Title'] ?? '',
                'title_duplicate' => $ex['Title'] ?? ''
            ];
        }
    }

    return [
        'unique' => $unique,
        'duplicates' => $duplicates
    ];
}

/**
 * Génère le rapport de dédoublonnage
 */
function generateReport(array $dbExercises, array $jsonExercises, array $result): string {
    $report = [];
    $report[] = "═══════════════════════════════════════════════════════════════";
    $report[] = "  RAPPORT DE DÉDOUBLONNAGE D'EXERCICES";
    $report[] = "═══════════════════════════════════════════════════════════════";
    $report[] = "";
    $report[] = "Date: " . date('Y-m-d H:i:s');
    $report[] = "";

    // Statistiques sources
    $report[] = "📊 STATISTIQUES SOURCES";
    $report[] = "───────────────────────────────────────────────────────────────";
    $report[] = sprintf("Exercices BDD:           %4d", count($dbExercises));
    $report[] = sprintf("Exercices JSON migrés:   %4d", count($jsonExercises));
    $report[] = sprintf("Total avant fusion:      %4d", count($dbExercises) + count($jsonExercises));
    $report[] = "";

    // Résultats dédoublonnage
    $report[] = "🔍 RÉSULTATS DÉDOUBLONNAGE";
    $report[] = "───────────────────────────────────────────────────────────────";
    $report[] = sprintf("Exercices uniques:       %4d", count($result['unique']));
    $report[] = sprintf("Doublons détectés:       %4d", count($result['duplicates']));
    $report[] = "";

    // Détails doublons
    if (count($result['duplicates']) > 0) {
        $report[] = "📋 DÉTAILS DES DOUBLONS";
        $report[] = "───────────────────────────────────────────────────────────────";
        foreach ($result['duplicates'] as $dup) {
            $report[] = sprintf(
                "• %s (%s) - conservé: %s | doublon: %s",
                $dup['identifier'],
                substr($dup['title_kept'], 0, 40),
                $dup['source_kept'],
                $dup['source_duplicate']
            );
        }
        $report[] = "";
    }

    // Distribution finale
    $report[] = "📦 DISTRIBUTION FINALE";
    $report[] = "───────────────────────────────────────────────────────────────";

    // Par matière
    $bySubject = [];
    foreach ($result['unique'] as $ex) {
        $subj = $ex['Subject'] ?? 'unknown';
        $bySubject[$subj] = ($bySubject[$subj] ?? 0) + 1;
    }
    arsort($bySubject);
    $report[] = "Par matière:";
    foreach ($bySubject as $subj => $count) {
        $report[] = sprintf("  %-30s %4d", $subj, $count);
    }
    $report[] = "";

    // Par niveau
    $byLevel = [];
    foreach ($result['unique'] as $ex) {
        $lvl = $ex['Level'] ?? 'unknown';
        $byLevel[$lvl] = ($byLevel[$lvl] ?? 0) + 1;
    }
    arsort($byLevel);
    $report[] = "Par niveau:";
    foreach ($byLevel as $lvl => $count) {
        $report[] = sprintf("  %-30s %4d", $lvl, $count);
    }
    $report[] = "";

    $report[] = "═══════════════════════════════════════════════════════════════";

    return implode("\n", $report);
}

/**
 * Génère le script SQL de suppression des doublons BDD
 */
function generateDeleteSQL(array $duplicates): string {
    if (count($duplicates) === 0) {
        return "-- Aucun doublon à supprimer\n";
    }

    $sql = [];
    $sql[] = "-- ═══════════════════════════════════════════════════════════════";
    $sql[] = "-- SCRIPT DE SUPPRESSION DES DOUBLONS";
    $sql[] = "-- ═══════════════════════════════════════════════════════════════";
    $sql[] = "-- ATTENTION: Sauvegardez la BDD avant d'exécuter ce script !";
    $sql[] = "-- mysqldump -u root -p moncoachscolaire exercises > backup.sql";
    $sql[] = "-- ═══════════════════════════════════════════════════════════════";
    $sql[] = "";

    foreach ($duplicates as $dup) {
        if ($dup['source_duplicate'] === 'database') {
            $sql[] = sprintf(
                "-- Doublon: %s - %s",
                $dup['identifier'],
                substr($dup['title_duplicate'], 0, 50)
            );
            $sql[] = sprintf(
                "DELETE FROM exercises WHERE Identifier = '%s' LIMIT 1;",
                addslashes($dup['identifier'])
            );
            $sql[] = "";
        }
    }

    return implode("\n", $sql);
}

// ═══════════════════════════════════════════════════════════════
// SCRIPT PRINCIPAL
// ═══════════════════════════════════════════════════════════════

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  DÉDOUBLONNAGE D'EXERCICES v3 (Fusion BDD + JSON)\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// 1. Vérifier ExerciseNormalizer
if (!class_exists('ExerciseNormalizer')) {
    die("❌ Erreur: ExerciseNormalizer introuvable\n");
}
echo "✅ ExerciseNormalizer chargé\n";

// 2. Charger exercices BDD
echo "📄 Chargement exercices BDD...\n";
$dbExercises = loadAndNormalizeExercises(DB_JSON_FILE, 'database');
echo sprintf("✅ %d exercices BDD chargés\n", count($dbExercises));

// 3. Charger exercices JSON migrés
echo "📄 Chargement exercices JSON migrés...\n";
$jsonExercises = loadAndNormalizeExercises(MIGRATED_JSON_FILE, 'migrated_json');
echo sprintf("✅ %d exercices JSON chargés\n", count($jsonExercises));

// 4. Fusionner les deux sources
echo "🔀 Fusion des sources...\n";
$allExercises = array_merge($dbExercises, $jsonExercises);
echo sprintf("✅ %d exercices au total\n", count($allExercises));

// 5. Détecter doublons
echo "🔍 Détection des doublons...\n";
$result = detectDuplicates($allExercises);
echo sprintf("✅ %d exercices uniques, %d doublons\n",
    count($result['unique']),
    count($result['duplicates'])
);

// 6. Nettoyer les tags temporaires
foreach ($result['unique'] as &$ex) {
    unset($ex['_source']);
    unset($ex['_temp_id']);
}
unset($ex);

// 7. Créer le dossier de sortie
$outputDir = dirname(OUTPUT_FILE);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$reportDir = dirname(REPORT_FILE);
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

// 8. Écrire le JSON dédoublonné
echo "💾 Écriture du fichier dédoublonné...\n";
$json = json_encode($result['unique'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(OUTPUT_FILE, $json);
$size = filesize(OUTPUT_FILE);
echo sprintf("✅ Fichier créé: %s (%.2f MB)\n", OUTPUT_FILE, $size / 1048576);

// 9. Générer le rapport
echo "📝 Génération du rapport...\n";
$report = generateReport($dbExercises, $jsonExercises, $result);
file_put_contents(REPORT_FILE, $report);
echo sprintf("✅ Rapport créé: %s\n", REPORT_FILE);

// 10. Générer le script SQL
if (count($result['duplicates']) > 0) {
    echo "📝 Génération du script SQL...\n";
    $sql = generateDeleteSQL($result['duplicates']);
    file_put_contents(DELETE_SQL_FILE, $sql);
    echo sprintf("✅ Script SQL créé: %s\n", DELETE_SQL_FILE);
} else {
    echo "ℹ️  Pas de doublons BDD → pas de script SQL\n";
}

// 11. Résumé final
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ DÉDOUBLONNAGE TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exercices uniques:    %4d\n", count($result['unique']));
echo sprintf("Doublons détectés:          %4d\n", count($result['duplicates']));
echo sprintf("Fichier de sortie:          %s\n", basename(OUTPUT_FILE));
echo sprintf("Taille:                     %.2f MB\n", $size / 1048576);
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "📋 Prochaines étapes:\n";
echo "1. Vérifier le rapport: dev/reports/deduplication_report.txt\n";
echo "2. Sauvegarder la BDD: mysqldump -u root -p moncoachscolaire > backup.sql\n";
if (count($result['duplicates']) > 0) {
    echo "3. (Optionnel) Supprimer doublons BDD: mysql -u root -p < dev/reports/delete_duplicates.sql\n";
    echo "4. Importer: php dev/tools/exercises/import_unified_exercises_v2.php\n";
} else {
    echo "3. Importer: php dev/tools/exercises/import_unified_exercises_v2.php\n";
}
