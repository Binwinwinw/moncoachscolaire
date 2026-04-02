<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  IMPORT EXERCICES DÉDOUBLONNÉS v3 (Final)
 * ═══════════════════════════════════════════════════════════════
 *
 * Importe le fichier exercises_final_deduplicated.json dans la BDD.
 *
 * Usage:
 *   php dev/tools/exercises/import_final_exercises.php --dry-run
 *   php dev/tools/exercises/import_final_exercises.php
 *
 * Options:
 *   --dry-run : Simulation sans modification BDD
 *   --truncate : Vider la table avant import (import complet)
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('INPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_final_deduplicated.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/import_final_report.txt');
define('LOG_FILE', PROJECT_ROOT . '/dev/reports/import_final.log');

// Mode UPDATE par défaut (mettre à jour si existe, insérer sinon)
define('DUPLICATE_MODE', 'UPDATE'); // UPDATE, INSERT, SKIP

require_once PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseNormalizer.php';
require_once PROJECT_ROOT . '/src/database/connection.php';

// ═══════════════════════════════════════════════════════════════
// CONFIGURATION
// ═══════════════════════════════════════════════════════════════

$dryRun = in_array('--dry-run', $argv);
$truncate = in_array('--truncate', $argv);

// ═══════════════════════════════════════════════════════════════
// FONCTIONS
// ═══════════════════════════════════════════════════════════════

function logMessage($message, $logFile = LOG_FILE) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function prepareExerciseForInsert($ex): array {
    // Convertir les champs JSON en chaînes
    $jsonFields = ['Choices', 'InteractiveConfig', 'LinkedCourses'];

    foreach ($jsonFields as $field) {
        if (isset($ex[$field])) {
            if (is_array($ex[$field])) {
                $ex[$field] = json_encode($ex[$field], JSON_UNESCAPED_UNICODE);
            } elseif (is_string($ex[$field]) && $ex[$field] === 'null') {
                $ex[$field] = null;
            }
        }
    }

    return $ex;
}

function exerciseExists($pdo, $identifier): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM exercises WHERE Identifier = ?");
    $stmt->execute([$identifier]);
    return $stmt->fetchColumn() > 0;
}

function insertExercise($pdo, $ex, $dryRun): bool {
    if ($dryRun) return true;

    $sql = "INSERT INTO exercises (
        Subject, Level, Title, Content, Answer, Tips, Domain, Competence,
        Difficulty, Identifier, AnswerType, XP_Points, is_active,
        Choices, InteractiveConfig, LinkedCourses
    ) VALUES (
        :Subject, :Level, :Title, :Content, :Answer, :Tips, :Domain, :Competence,
        :Difficulty, :Identifier, :AnswerType, :XP_Points, :is_active,
        :Choices, :InteractiveConfig, :LinkedCourses
    )";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':Subject' => $ex['Subject'] ?? null,
        ':Level' => $ex['Level'] ?? null,
        ':Title' => $ex['Title'] ?? null,
        ':Content' => $ex['Content'] ?? null,
        ':Answer' => $ex['Answer'] ?? null,
        ':Tips' => $ex['Tips'] ?? null,
        ':Domain' => $ex['Domain'] ?? null,
        ':Competence' => $ex['Competence'] ?? null,
        ':Difficulty' => $ex['Difficulty'] ?? null,
        ':Identifier' => $ex['Identifier'] ?? null,
        ':AnswerType' => $ex['AnswerType'] ?? null,
        ':XP_Points' => $ex['XP_Points'] ?? 0,
        ':is_active' => $ex['is_active'] ?? 1,
        ':Choices' => $ex['Choices'] ?? null,
        ':InteractiveConfig' => $ex['InteractiveConfig'] ?? null,
        ':LinkedCourses' => $ex['LinkedCourses'] ?? null
    ]);
}

function updateExercise($pdo, $ex, $dryRun): bool {
    if ($dryRun) return true;

    $sql = "UPDATE exercises SET
        Subject = :Subject,
        Level = :Level,
        Title = :Title,
        Content = :Content,
        Answer = :Answer,
        Tips = :Tips,
        Domain = :Domain,
        Competence = :Competence,
        Difficulty = :Difficulty,
        AnswerType = :AnswerType,
        XP_Points = :XP_Points,
        is_active = :is_active,
        Choices = :Choices,
        InteractiveConfig = :InteractiveConfig,
        LinkedCourses = :LinkedCourses
    WHERE Identifier = :Identifier";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':Subject' => $ex['Subject'] ?? null,
        ':Level' => $ex['Level'] ?? null,
        ':Title' => $ex['Title'] ?? null,
        ':Content' => $ex['Content'] ?? null,
        ':Answer' => $ex['Answer'] ?? null,
        ':Tips' => $ex['Tips'] ?? null,
        ':Domain' => $ex['Domain'] ?? null,
        ':Competence' => $ex['Competence'] ?? null,
        ':Difficulty' => $ex['Difficulty'] ?? null,
        ':AnswerType' => $ex['AnswerType'] ?? null,
        ':XP_Points' => $ex['XP_Points'] ?? 0,
        ':is_active' => $ex['is_active'] ?? 1,
        ':Choices' => $ex['Choices'] ?? null,
        ':InteractiveConfig' => $ex['InteractiveConfig'] ?? null,
        ':LinkedCourses' => $ex['LinkedCourses'] ?? null,
        ':Identifier' => $ex['Identifier']
    ]);
}

// ═══════════════════════════════════════════════════════════════
// SCRIPT PRINCIPAL
// ═══════════════════════════════════════════════════════════════

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  IMPORT EXERCICES DÉDOUBLONNÉS";
if ($dryRun) echo " (DRY-RUN)";
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// Vérifier le fichier
if (!file_exists(INPUT_FILE)) {
    die("❌ Fichier introuvable: " . INPUT_FILE . "\n");
}

echo "📄 Chargement du fichier...\n";
$json = file_get_contents(INPUT_FILE);
$exercises = json_decode($json, true);

if (!is_array($exercises)) {
    die("❌ Fichier JSON invalide\n");
}

echo sprintf("✅ %d exercices chargés\n\n", count($exercises));

// Connexion BDD
echo "🔌 Connexion à la base de données...\n";
try {
    $pdo = getConnection();
    echo "✅ Connecté\n\n";
} catch (Exception $e) {
    die("❌ Erreur de connexion: " . $e->getMessage() . "\n");
}

// Truncate si demandé
if ($truncate) {
    echo "⚠️  MODE TRUNCATE : Suppression de tous les exercices...\n";
    if ($dryRun) {
        echo "   [DRY-RUN] TRUNCATE TABLE exercises;\n";
    } else {
        $pdo->exec("TRUNCATE TABLE exercises");
        echo "✅ Table vidée\n";
    }
    echo "\n";
}

// Import
echo "📊 Import en cours...\n";
echo "   Mode: " . DUPLICATE_MODE . "\n";
if ($dryRun) {
    echo "   ⚠️  DRY-RUN : Aucune modification réelle\n";
}
echo "\n";

$stats = [
    'total' => count($exercises),
    'inserted' => 0,
    'updated' => 0,
    'skipped' => 0,
    'errors' => 0
];

$actions = [];

foreach ($exercises as $index => $ex) {
    $identifier = $ex['Identifier'] ?? 'NO_ID_' . $index;

    try {
        $ex = prepareExerciseForInsert($ex);

        $exists = exerciseExists($pdo, $identifier);

        if ($exists) {
            // Exercice existe déjà
            if (DUPLICATE_MODE === 'UPDATE') {
                updateExercise($pdo, $ex, $dryRun);
                $stats['updated']++;
                $actions[] = "UPDATE: $identifier";
            } elseif (DUPLICATE_MODE === 'SKIP') {
                $stats['skipped']++;
                $actions[] = "SKIP: $identifier (existe déjà)";
            } else {
                // INSERT mode : ignore
                $stats['skipped']++;
                $actions[] = "SKIP: $identifier (existe déjà, mode INSERT)";
            }
        } else {
            // Nouvel exercice
            insertExercise($pdo, $ex, $dryRun);
            $stats['inserted']++;
            $actions[] = "INSERT: $identifier";
        }

        // Progression
        if (($index + 1) % 100 === 0) {
            echo sprintf("   Traité: %d/%d\r", $index + 1, $stats['total']);
        }

    } catch (Exception $e) {
        $stats['errors']++;
        $error = "ERREUR: $identifier - " . $e->getMessage();
        $actions[] = $error;
        logMessage($error);
    }
}

echo "\n\n";

// Résumé
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ";
if ($dryRun) echo " (DRY-RUN)";
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exercices:        %4d\n", $stats['total']);
echo sprintf("  ✅ Insérés:           %4d\n", $stats['inserted']);
echo sprintf("  🔄 Mis à jour:        %4d\n", $stats['updated']);
echo sprintf("  ⏭️  Ignorés:           %4d\n", $stats['skipped']);
echo sprintf("  ❌ Erreurs:           %4d\n", $stats['errors']);
echo "═══════════════════════════════════════════════════════════════\n";

// Rapport détaillé
$report = [];
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "  RAPPORT D'IMPORT EXERCICES DÉDOUBLONNÉS";
if ($dryRun) $report[] = "  (DRY-RUN - SIMULATION)";
$report[] = "═══════════════════════════════════════════════════════════════";
$report[] = "";
$report[] = "Date: " . date('Y-m-d H:i:s');
$report[] = "Fichier: exercises_final_deduplicated.json";
$report[] = "Mode: " . DUPLICATE_MODE;
if ($truncate) $report[] = "Truncate: OUI";
$report[] = "";
$report[] = "📊 STATISTIQUES";
$report[] = "───────────────────────────────────────────────────────────────";
$report[] = sprintf("Total:           %4d", $stats['total']);
$report[] = sprintf("Insérés:         %4d", $stats['inserted']);
$report[] = sprintf("Mis à jour:      %4d", $stats['updated']);
$report[] = sprintf("Ignorés:         %4d", $stats['skipped']);
$report[] = sprintf("Erreurs:         %4d", $stats['errors']);
$report[] = "";
$report[] = "📋 DÉTAILS (50 premières actions)";
$report[] = "───────────────────────────────────────────────────────────────";
foreach (array_slice($actions, 0, 50) as $action) {
    $report[] = $action;
}
if (count($actions) > 50) {
    $report[] = sprintf("... et %d autres actions", count($actions) - 50);
}
$report[] = "";
$report[] = "═══════════════════════════════════════════════════════════════";

$reportDir = dirname(REPORT_FILE);
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0755, true);
}

file_put_contents(REPORT_FILE, implode("\n", $report));

echo "\n";
echo "📄 Rapport: dev/reports/import_final_report.txt\n";

if ($dryRun) {
    echo "\n";
    echo "💡 Pour lancer l'import réel:\n";
    echo "   php dev/tools/exercises/import_final_exercises.php\n";
    echo "\n";
    echo "💡 Pour un import complet (vider la table d'abord):\n";
    echo "   php dev/tools/exercises/import_final_exercises.php --truncate\n";
}

echo "\n";
