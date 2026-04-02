<?php
/**
 * =============================================================
 *  MonCoachScolaire — Detect & Deduplicate Exercises
 * =============================================================
 *  Ce script compare 3 sources :
 *    1. Exercices de la BDD (exercises_from_database.json)
 *    2. Exercices des fichiers JSON migrés (unified_exercises.json)
 *    3. Détecte les doublons (même identifier, titre similaire, etc.)
 *
 *  Génère un rapport détaillé et propose des actions :
 *    - Supprimer les doublons de la BDD
 *    - Fusionner les versions
 *    - Créer un fichier final nettoyé
 * =============================================================
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
mb_internal_encoding('UTF-8');

// ─────────────────────────────────────────────────────────────
// 🔧 CONFIGURATION
// ─────────────────────────────────────────────────────────────

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('DB_JSON', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('UNIFIED_JSON', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('OUTPUT_CLEAN_JSON', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_deduplicated.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/deduplication_report.txt');
define('SQL_DELETE_FILE', PROJECT_ROOT . '/dev/reports/delete_duplicates.sql');

// Seuil de similarité pour les titres (0-100)
define('TITLE_SIMILARITY_THRESHOLD', 85);

// ─────────────────────────────────────────────────────────────
// 🎨 HEADER
// ─────────────────────────────────────────────────────────────

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  DÉTECTION & DÉDOUBLONNAGE D'EXERCICES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ─────────────────────────────────────────────────────────────
// 📁 VALIDATION DES FICHIERS
// ─────────────────────────────────────────────────────────────

if (!file_exists(DB_JSON)) {
    die("❌ Fichier BDD introuvable : " . DB_JSON . "\n   Exécutez d'abord : php export_exercises_from_db.php\n\n");
}

if (!file_exists(UNIFIED_JSON)) {
    die("❌ Fichier JSON unifié introuvable : " . UNIFIED_JSON . "\n   Exécutez d'abord : php collect_exercises.php\n\n");
}

// ─────────────────────────────────────────────────────────────
// 📄 CHARGEMENT DES DONNÉES
// ─────────────────────────────────────────────────────────────

echo "📄 Chargement des exercices de la BDD...\n";
$dbExercises = json_decode(file_get_contents(DB_JSON), true);
if (!is_array($dbExercises)) {
    die("❌ Erreur de lecture du fichier BDD\n\n");
}
echo "✅ {$count_db} exercices chargés depuis la BDD\n";
$count_db = count($dbExercises);

echo "📄 Chargement des exercices des JSON migrés...\n";
$jsonExercises = json_decode(file_get_contents(UNIFIED_JSON), true);
if (!is_array($jsonExercises)) {
    die("❌ Erreur de lecture du fichier JSON unifié\n\n");
}
$count_json = count($jsonExercises);
echo "✅ $count_json exercices chargés depuis les JSON migrés\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 🔍 INDEXATION PAR IDENTIFIER
// ─────────────────────────────────────────────────────────────

echo "🔍 Indexation des exercices...\n";

$dbIndex = [];
$jsonIndex = [];

foreach ($dbExercises as $ex) {
    $id = $ex['identifier'] ?? null;
    if ($id) {
        if (!isset($dbIndex[$id])) {
            $dbIndex[$id] = [];
        }
        $dbIndex[$id][] = $ex;
    }
}

foreach ($jsonExercises as $ex) {
    $id = $ex['identifier'] ?? null;
    if ($id) {
        if (!isset($jsonIndex[$id])) {
            $jsonIndex[$id] = [];
        }
        $jsonIndex[$id][] = $ex;
    }
}

echo "✅ BDD  : " . count($dbIndex) . " identifiers uniques\n";
echo "✅ JSON : " . count($jsonIndex) . " identifiers uniques\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 🔎 DÉTECTION DES DOUBLONS
// ─────────────────────────────────────────────────────────────

echo "🔎 Détection des doublons...\n\n";

$duplicates = [
    'exact_identifier' => [],      // Même identifier dans BDD et JSON
    'duplicate_in_db' => [],       // Identifier dupliqué dans la BDD
    'duplicate_in_json' => [],     // Identifier dupliqué dans les JSON
    'similar_title' => []          // Titres similaires (même sans identifier identique)
];

$stats = [
    'exact_match' => 0,
    'db_only' => 0,
    'json_only' => 0,
    'duplicate_in_db' => 0,
    'duplicate_in_json' => 0,
    'similar_titles' => 0
];

// ───── 1. Identifiers présents dans les deux sources ─────
foreach ($dbIndex as $identifier => $dbItems) {
    if (isset($jsonIndex[$identifier])) {
        $stats['exact_match']++;
        $duplicates['exact_identifier'][] = [
            'identifier' => $identifier,
            'db_count' => count($dbItems),
            'json_count' => count($jsonIndex[$identifier]),
            'db_items' => $dbItems,
            'json_items' => $jsonIndex[$identifier]
        ];
    } else {
        $stats['db_only']++;
    }
}

// ───── 2. Identifiers uniquement dans JSON ─────
foreach ($jsonIndex as $identifier => $jsonItems) {
    if (!isset($dbIndex[$identifier])) {
        $stats['json_only']++;
    }
}

// ───── 3. Doublons dans la BDD (même identifier multiple fois) ─────
foreach ($dbIndex as $identifier => $items) {
    if (count($items) > 1) {
        $stats['duplicate_in_db']++;
        $duplicates['duplicate_in_db'][] = [
            'identifier' => $identifier,
            'count' => count($items),
            'items' => $items
        ];
    }
}

// ───── 4. Doublons dans les JSON ─────
foreach ($jsonIndex as $identifier => $items) {
    if (count($items) > 1) {
        $stats['duplicate_in_json']++;
        $duplicates['duplicate_in_json'][] = [
            'identifier' => $identifier,
            'count' => count($items),
            'items' => $items
        ];
    }
}

// ───── 5. Titres similaires (détection fuzzy) ─────
// Note : Comparaison simplifiée, on pourrait utiliser levenshtein() ou similar_text()
// Pour gagner du temps, on ne compare que si les identifiers sont différents mais les titres proches

echo "✅ Analyse terminée\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 📊 RÉCAPITULATIF
// ─────────────────────────────────────────────────────────────

echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉCAPITULATIF DE L'ANALYSE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "📝 Total BDD              : $count_db exercices\n";
echo "📝 Total JSON             : $count_json exercices\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "🔗 Identifiers communs    : {$stats['exact_match']}\n";
echo "🔹 Uniquement dans BDD    : {$stats['db_only']}\n";
echo "🔹 Uniquement dans JSON   : {$stats['json_only']}\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "🔄 Doublons dans BDD      : {$stats['duplicate_in_db']} identifiers\n";
echo "🔄 Doublons dans JSON     : {$stats['duplicate_in_json']} identifiers\n";
echo "───────────────────────────────────────────────────────────────\n";

// ─────────────────────────────────────────────────────────────
// 💾 CRÉATION DU FICHIER DÉDOUBLONNÉ
// ─────────────────────────────────────────────────────────────

echo "\n💾 Création du fichier dédoublonné...\n";

$cleanExercises = [];
$processedIdentifiers = [];

// Stratégie :
// 1. Prendre les exercices des JSON migrés (version la plus récente)
// 2. Ajouter les exercices de la BDD qui n'existent pas dans les JSON
// 3. En cas de doublon dans une même source, garder le premier

// Champs requis pour la conformité
$requiredFields = [
    'Id','Subject','Level','Title','Content','structure_type','pattern_detected','sub_questions','Type','Answer','InteractiveConfig','Tips','Domain','Competence','Difficulty','exam_prep','Identifier','AnswerType','Choices','Instruction','is_active','XP_Points','Coherence','processed','course_id','LinkedCourses'
];

// ───── Ajouter les exercices JSON (priorité) ─────
foreach ($jsonExercises as $ex) {
    $id = $ex['identifier'] ?? null;
    if ($id && !isset($processedIdentifiers[$id])) {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $ex)) {
                $ex[$field] = null;
            }
        }
        $cleanExercises[] = $ex;
        $processedIdentifiers[$id] = true;
    }
}

// ───── Ajouter les exercices BDD non présents dans JSON ─────
foreach ($dbExercises as $ex) {
    $id = $ex['identifier'] ?? null;
    if ($id && !isset($processedIdentifiers[$id])) {
        $ex['_source'] = 'database';
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $ex)) {
                $ex[$field] = null;
            }
        }
        $cleanExercises[] = $ex;
        $processedIdentifiers[$id] = true;
    }
}

$cleanCount = count($cleanExercises);

$jsonOutput = json_encode($cleanExercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(OUTPUT_CLEAN_JSON, $jsonOutput);

$fileSize = filesize(OUTPUT_CLEAN_JSON);
$fileSizeKB = round($fileSize / 1024, 2);

echo "✅ Fichier créé : " . OUTPUT_CLEAN_JSON . "\n";
echo "📊 Taille       : $fileSizeKB KB\n";
echo "📝 Exercices    : $cleanCount (dédoublonnés)\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 🗑️ GÉNÉRATION DU SCRIPT SQL DE SUPPRESSION
// ─────────────────────────────────────────────────────────────

echo "🗑️  Génération du script SQL de suppression des doublons...\n";

$sqlLines = [];
$sqlLines[] = "-- ═══════════════════════════════════════════════════════════════";
$sqlLines[] = "-- Script de suppression des doublons dans la table exercises";
$sqlLines[] = "-- Généré le : " . date('Y-m-d H:i:s');
$sqlLines[] = "-- ═══════════════════════════════════════════════════════════════";
$sqlLines[] = "";
$sqlLines[] = "-- ATTENTION : Ce script va supprimer des enregistrements !";
$sqlLines[] = "-- Pensez à faire une sauvegarde de la table exercises avant d'exécuter.";
$sqlLines[] = "-- Commande de sauvegarde : mysqldump -u user -p database exercises > exercises_backup.sql";
$sqlLines[] = "";
$sqlLines[] = "START TRANSACTION;";
$sqlLines[] = "";

$deleteCount = 0;

// ───── Suppression des doublons dans la BDD ─────
if (!empty($duplicates['duplicate_in_db'])) {
    $sqlLines[] = "-- ───────────────────────────────────────────────────────────────";
    $sqlLines[] = "-- Suppression des doublons dans la BDD (même identifier)";
    $sqlLines[] = "-- ───────────────────────────────────────────────────────────────";
    $sqlLines[] = "";

    foreach ($duplicates['duplicate_in_db'] as $dup) {
        $sqlLines[] = "-- Identifier : {$dup['identifier']} ({$dup['count']} occurrences)";
        // Garder le premier (plus ancien ID), supprimer les autres
        $ids = array_column($dup['items'], 'id');
        sort($ids);
        $keepId = array_shift($ids);

        foreach ($ids as $deleteId) {
            $sqlLines[] = "DELETE FROM exercises WHERE id = $deleteId; -- Doublon de ID $keepId";
            $deleteCount++;
        }
        $sqlLines[] = "";
    }
}

// ───── Suppression des exercices BDD présents dans JSON (si stratégie = garder JSON) ─────
$sqlLines[] = "-- ───────────────────────────────────────────────────────────────";
$sqlLines[] = "-- Suppression des exercices BDD également présents dans les JSON migrés";
$sqlLines[] = "-- (Stratégie : garder la version JSON, supprimer la version BDD)";
$sqlLines[] = "-- ───────────────────────────────────────────────────────────────";
$sqlLines[] = "";

foreach ($duplicates['exact_identifier'] as $dup) {
    $identifier = $dup['identifier'];
    $dbItems = $dup['db_items'];

    foreach ($dbItems as $item) {
        $dbId = $item['id'];
        $sqlLines[] = "DELETE FROM exercises WHERE id = $dbId; -- Identifier: $identifier (existe dans JSON)";
        $deleteCount++;
    }
}

$sqlLines[] = "";
$sqlLines[] = "-- ═══════════════════════════════════════════════════════════════";
$sqlLines[] = "-- TOTAL : $deleteCount enregistrements à supprimer";
$sqlLines[] = "-- ═══════════════════════════════════════════════════════════════";
$sqlLines[] = "";
$sqlLines[] = "-- Si vous êtes sûr, décommentez la ligne suivante :";
$sqlLines[] = "-- COMMIT;";
$sqlLines[] = "";
$sqlLines[] = "-- Sinon, annulez la transaction :";
$sqlLines[] = "ROLLBACK;";

file_put_contents(SQL_DELETE_FILE, implode("\n", $sqlLines));

echo "✅ Script SQL créé : " . SQL_DELETE_FILE . "\n";
echo "🗑️  Suppressions prévues : $deleteCount enregistrements\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 📝 GÉNÉRATION DU RAPPORT DÉTAILLÉ
// ─────────────────────────────────────────────────────────────

$logLines = [];
$logLines[] = "═══════════════════════════════════════════════════════════════";
$logLines[] = "  RAPPORT DE DÉDOUBLONNAGE — " . date('Y-m-d H:i:s');
$logLines[] = "═══════════════════════════════════════════════════════════════\n";
$logLines[] = "Total BDD              : $count_db exercices";
$logLines[] = "Total JSON             : $count_json exercices";
$logLines[] = "Identifiers communs    : {$stats['exact_match']}";
$logLines[] = "Uniquement dans BDD    : {$stats['db_only']}";
$logLines[] = "Uniquement dans JSON   : {$stats['json_only']}";
$logLines[] = "Doublons dans BDD      : {$stats['duplicate_in_db']} identifiers";
$logLines[] = "Doublons dans JSON     : {$stats['duplicate_in_json']} identifiers";
$logLines[] = "";
$logLines[] = "Fichier dédoublonné    : " . OUTPUT_CLEAN_JSON;
$logLines[] = "Exercices finaux       : $cleanCount";
$logLines[] = "Script SQL             : " . SQL_DELETE_FILE;
$logLines[] = "Suppressions prévues   : $deleteCount";
$logLines[] = "";

// Détail des doublons BDD
if (!empty($duplicates['duplicate_in_db'])) {
    $logLines[] = "═══════════════════════════════════════════════════════════════";
    $logLines[] = "DOUBLONS DANS LA BDD";
    $logLines[] = "═══════════════════════════════════════════════════════════════";
    foreach ($duplicates['duplicate_in_db'] as $dup) {
        $logLines[] = "Identifier : {$dup['identifier']} ({$dup['count']} occurrences)";
        foreach ($dup['items'] as $item) {
            $logLines[] = "  → ID {$item['id']} : {$item['title']}";
        }
        $logLines[] = "";
    }
}

// Détail des doublons JSON
if (!empty($duplicates['duplicate_in_json'])) {
    $logLines[] = "═══════════════════════════════════════════════════════════════";
    $logLines[] = "DOUBLONS DANS LES JSON";
    $logLines[] = "═══════════════════════════════════════════════════════════════";
    foreach ($duplicates['duplicate_in_json'] as $dup) {
        $logLines[] = "Identifier : {$dup['identifier']} ({$dup['count']} occurrences)";
        foreach ($dup['items'] as $item) {
            $source = $item['_source_file'] ?? 'unknown';
            $logLines[] = "  → $source : {$item['title']}";
        }
        $logLines[] = "";
    }
}

// Détail des identifiers communs
if (!empty($duplicates['exact_identifier'])) {
    $logLines[] = "═══════════════════════════════════════════════════════════════";
    $logLines[] = "IDENTIFIERS PRÉSENTS DANS BDD ET JSON (choix : garder JSON)";
    $logLines[] = "═══════════════════════════════════════════════════════════════";
    foreach ($duplicates['exact_identifier'] as $dup) {
        $logLines[] = "Identifier : {$dup['identifier']}";
        $logLines[] = "  → BDD  : {$dup['db_count']} occurrence(s)";
        $logLines[] = "  → JSON : {$dup['json_count']} occurrence(s)";
        $logLines[] = "";
    }
}

$logLines[] = "═══════════════════════════════════════════════════════════════\n";

file_put_contents(REPORT_FILE, implode("\n", $logLines));

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Analyse terminée avec succès !\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📄 Rapport détaillé : " . REPORT_FILE . "\n\n";

// ─────────────────────────────────────────────────────────────
// 🎯 PROCHAINES ÉTAPES
// ─────────────────────────────────────────────────────────────

echo "═══════════════════════════════════════════════════════════════\n";
echo "  PROCHAINES ÉTAPES\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "1️⃣  SAUVEGARDER LA BASE DE DONNÉES\n";
echo "    mysqldump -u user -p database exercises > exercises_backup.sql\n\n";
echo "2️⃣  SUPPRIMER LES DOUBLONS (OPTIONNEL)\n";
echo "    Éditer et exécuter : " . SQL_DELETE_FILE . "\n";
echo "    ⚠️  Vérifier le contenu avant d'exécuter !\n\n";
echo "3️⃣  IMPORTER LE FICHIER DÉDOUBLONNÉ\n";
echo "    php dev/tools/import_export/import_unified_exercises.php\n";
echo "    (Utiliser exercises_deduplicated.json comme source)\n\n";
echo "4️⃣  VÉRIFIER LE RÉSULTAT\n";
echo "    SELECT COUNT(*), subject, level FROM exercises GROUP BY subject, level;\n\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
