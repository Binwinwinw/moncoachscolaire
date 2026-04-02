<?php
/**
 * =============================================================
 *  MonCoachScolaire — Export Exercises from Database → JSON
 * =============================================================
 *  ✅ VERSION CORRIGÉE avec SchemaMapper
 * =============================================================
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
mb_internal_encoding('UTF-8');

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('CONNECTION_PATH', PROJECT_ROOT . '/src/database/connection.php');
define('MAPPER_PATH', PROJECT_ROOT . '/dev/db/json/schema/exercices/SchemaMapper.php');
define('OUTPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/export_exercises_from_db.txt');

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  EXPORT EXERCICES — Base de Données → JSON (+ Mapping)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if (!file_exists(CONNECTION_PATH)) {
    die("❌ Fichier de connexion introuvable\n\n");
}

if (!file_exists(MAPPER_PATH)) {
    die("❌ SchemaMapper introuvable : " . MAPPER_PATH . "\n\n");
}

echo "🔌 Connexion à la base de données...\n";
require_once CONNECTION_PATH;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("❌ Variable \$pdo non disponible\n\n");
}

echo "✅ Connexion établie\n";
require_once MAPPER_PATH;
echo "✅ SchemaMapper chargé\n";
echo "───────────────────────────────────────────────────────────────\n\n";

echo "📊 Extraction des exercices...\n";

try {
    $columns = SchemaMapper::getSelectColumns();
    $stmt = $pdo->query("SELECT $columns FROM exercises ORDER BY Id ASC");
    $rawExercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalExercises = count($rawExercises);
    
    echo "✅ $totalExercises exercices extraits\n";
    echo "🔄 Conversion vers le schéma normalisé...\n";
    
    $exercises = [];
    foreach ($rawExercises as $rawEx) {
        $exercises[] = SchemaMapper::fromDatabase($rawEx);
    }
    
    echo "✅ $totalExercises exercices mappés\n\n";
    
} catch (PDOException $e) {
    die("❌ Erreur SQL : {$e->getMessage()}\n\n");
}

echo "💾 Écriture du fichier JSON...\n";
$jsonOutput = json_encode($exercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(OUTPUT_FILE, $jsonOutput);

$fileSize = filesize(OUTPUT_FILE);
$fileSizeKB = round($fileSize / 1024, 2);

echo "✅ Fichier créé : " . OUTPUT_FILE . "\n";
echo "📊 Taille : $fileSizeKB KB\n\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ Export terminé avec succès !\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
