<?php
/**
 * =============================================================
 *  MonCoachScolaire — Import Unified Exercises → Database
 * =============================================================
 *  Ce script lit unified_exercises.json,
 *  insère ou met à jour chaque exercice dans la table exercises,
 *  utilise ExerciseParser pour valider les données,
 *  et génère un rapport d'import détaillé.
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
define('UNIFIED_JSON', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('PARSER_PATH', PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseParser.php');
define('CONNECTION_PATH', PROJECT_ROOT . '/src/database/connection.php');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/import_exercises_report.txt');

// Mode de gestion des doublons
define('DUPLICATE_MODE', 'UPDATE'); // 'INSERT', 'UPDATE', 'SKIP'

// Dry-run : si true, ne modifie pas la base
$dryRun = in_array('--dry-run', $argv);

// ─────────────────────────────────────────────────────────────
// 🎨 HEADER
// ─────────────────────────────────────────────────────────────

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  IMPORT EXERCICES UNIFIÉS → Base de Données\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if ($dryRun) {
    echo "🔍 MODE DRY-RUN : Aucune modification ne sera effectuée\n";
    echo "───────────────────────────────────────────────────────────────\n\n";
}

// ─────────────────────────────────────────────────────────────
// 📁 VALIDATION DES FICHIERS
// ─────────────────────────────────────────────────────────────

if (!file_exists(UNIFIED_JSON)) {
    die("❌ Fichier source introuvable : " . UNIFIED_JSON . "\n\n");
}

if (!file_exists(PARSER_PATH)) {
    die("❌ ExerciseParser introuvable : " . PARSER_PATH . "\n\n");
}

if (!file_exists(CONNECTION_PATH)) {
    die("❌ Fichier de connexion introuvable : " . CONNECTION_PATH . "\n\n");
}

// ─────────────────────────────────────────────────────────────
// 🔌 CONNEXION À LA BASE
// ─────────────────────────────────────────────────────────────

echo "🔌 Connexion à la base de données...\n";
require_once CONNECTION_PATH;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("❌ Variable \$pdo non disponible après inclusion de connection.php\n\n");
}

echo "✅ Connexion établie\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 📦 CHARGEMENT DU PARSER
// ─────────────────────────────────────────────────────────────

require_once PARSER_PATH;

// ─────────────────────────────────────────────────────────────
// 📄 LECTURE DU FICHIER JSON UNIFIÉ
// ─────────────────────────────────────────────────────────────

echo "📄 Lecture du fichier JSON unifié...\n";
$jsonContent = @file_get_contents(UNIFIED_JSON);
if ($jsonContent === false) {
    die("❌ Impossible de lire le fichier : " . UNIFIED_JSON . "\n\n");
}

$exercises = json_decode($jsonContent, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ JSON invalide : " . json_last_error_msg() . "\n\n");
}

if (!is_array($exercises)) {
    die("❌ Le fichier JSON ne contient pas un tableau d'exercices\n\n");
}

$totalExercises = count($exercises);
echo "✅ $totalExercises exercices chargés\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 🔨 PRÉPARATION DES REQUÊTES SQL
// ─────────────────────────────────────────────────────────────

// Vérification de l'existence d'un exercice par identifier
$checkStmt = $pdo->prepare("
    SELECT id FROM exercises WHERE identifier = :identifier LIMIT 1
");

// Insertion
$insertStmt = $pdo->prepare("
    INSERT INTO exercises (
        identifier, subject, level, title, content, answer, tips, domain, competence,
        difficulty, answer_type, xp_points, is_active, created_at
    ) VALUES (
        :identifier, :subject, :level, :title, :content, :answer, :tips, :domain, :competence,
        :difficulty, :answer_type, :xp_points, :is_active, NOW()
    )
");

// Mise à jour
$updateStmt = $pdo->prepare("
    UPDATE exercises SET
        subject = :subject,
        level = :level,
        title = :title,
        content = :content,
        answer = :answer,
        tips = :tips,
        domain = :domain,
        competence = :competence,
        difficulty = :difficulty,
        answer_type = :answer_type,
        xp_points = :xp_points,
        is_active = :is_active,
        updated_at = NOW()
    WHERE identifier = :identifier
");

// ─────────────────────────────────────────────────────────────
// 📊 STATISTIQUES
// ─────────────────────────────────────────────────────────────

$stats = [
    'inserted' => 0,
    'updated' => 0,
    'skipped' => 0,
    'errors' => 0,
    'byLevel' => [],
    'bySubject' => [],
    'errorDetails' => []
];

$logLines = [];
$logLines[] = "═══════════════════════════════════════════════════════════════";
$logLines[] = "  IMPORT EXERCICES — " . date('Y-m-d H:i:s');
$logLines[] = "  Mode : " . ($dryRun ? "DRY-RUN" : "RÉEL");
$logLines[] = "  Gestion des doublons : " . DUPLICATE_MODE;
$logLines[] = "═══════════════════════════════════════════════════════════════\n";

// ─────────────────────────────────────────────────────────────
// 🔁 TRAITEMENT DE CHAQUE EXERCICE
// ─────────────────────────────────────────────────────────────

echo "🚀 Import en cours...\n\n";

foreach ($exercises as $index => $rawExercise) {
    $num = $index + 1;
    $identifier = $rawExercise['identifier'] ?? "UNKNOWN-$num";
    $sourceFile = $rawExercise['_source_file'] ?? 'unknown';

    // Normalisation des champs pour conformité
    $requiredFields = [
        'Id','Subject','Level','Title','Content','structure_type','pattern_detected','sub_questions','Type','Answer','InteractiveConfig','Tips','Domain','Competence','Difficulty','exam_prep','Identifier','AnswerType','Choices','Instruction','is_active','XP_Points','Coherence','processed','course_id','LinkedCourses'
    ];
    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $rawExercise)) {
            $rawExercise[$field] = null;
        }
    }

    // ───── Validation via ExerciseParser ─────
    try {
        $parser = new ExerciseParser($rawExercise);
        $validated = $parser->parse();
    } catch (Exception $e) {
        echo "[$num/$totalExercises] ❌ $identifier — Erreur de validation : {$e->getMessage()}\n";
        $stats['errors']++;
        $stats['errorDetails'][] = [
            'num' => $num,
            'identifier' => $identifier,
            'file' => $sourceFile,
            'error' => $e->getMessage()
        ];
        $logLines[] = "❌ [$num/$totalExercises] $identifier — Validation échouée : {$e->getMessage()}";
        continue;
    }

    // ───── Vérification de l'existence ─────
    $exists = false;
    if (!$dryRun) {
        try {
            $checkStmt->execute(['identifier' => $validated['identifier']]);
            $exists = $checkStmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            echo "[$num/$totalExercises] ❌ $identifier — Erreur SQL (check) : {$e->getMessage()}\n";
            $stats['errors']++;
            $stats['errorDetails'][] = [
                'num' => $num,
                'identifier' => $identifier,
                'file' => $sourceFile,
                'error' => "SQL check error: {$e->getMessage()}"
            ];
            $logLines[] = "❌ [$num/$totalExercises] $identifier — Erreur SQL (check)";
            continue;
        }
    }

    // ───── Décision INSERT/UPDATE/SKIP ─────
    $action = 'INSERT';
    if ($exists) {
        if (DUPLICATE_MODE === 'UPDATE') {
            $action = 'UPDATE';
        } else if (DUPLICATE_MODE === 'SKIP') {
            $action = 'SKIP';
        }
    }

    // ───── Exécution de l'action ─────
    if ($dryRun) {
        $action = 'DRY-' . $action;
    }

    if ($action === 'SKIP') {
        echo "[$num/$totalExercises] ⏭️  $identifier — Ignoré (existe déjà)\n";
        $stats['skipped']++;
        $logLines[] = "⏭️  [$num/$totalExercises] $identifier — Ignoré";
        continue;
    }

    if ($action === 'DRY-INSERT' || $action === 'DRY-UPDATE') {
        echo "[$num/$totalExercises] 🔍 $identifier — " . ($exists ? 'UPDATE' : 'INSERT') . " (dry-run)\n";
        if ($exists) {
            $stats['updated']++;
        } else {
            $stats['inserted']++;
        }
        $logLines[] = "🔍 [$num/$totalExercises] $identifier — " . ($exists ? 'UPDATE' : 'INSERT') . " (dry-run)";

        // Stats
        $level = $validated['level'] ?? 'unknown';
        $stats['byLevel'][$level] = ($stats['byLevel'][$level] ?? 0) + 1;
        $subject = $validated['subject'] ?? 'unknown';
        $stats['bySubject'][$subject] = ($stats['bySubject'][$subject] ?? 0) + 1;

        continue;
    }

    // ───── INSERT réel ─────
    if ($action === 'INSERT') {
        try {
            $insertStmt->execute([
                ':identifier' => $validated['identifier'],
                ':subject' => $validated['subject'],
                ':level' => $validated['level'],
                ':title' => $validated['title'],
                ':content' => $validated['content'],
                ':answer' => $validated['answer'],
                ':tips' => $validated['tips'],
                ':domain' => $validated['domain'],
                ':competence' => $validated['competence'],
                ':difficulty' => $validated['difficulty'],
                ':answer_type' => $validated['answer_type'],
                ':xp_points' => $validated['xp_points'],
                ':is_active' => $validated['is_active']
            ]);
            echo "[$num/$totalExercises] ✅ $identifier — Inséré\n";
            $stats['inserted']++;
            $logLines[] = "✅ [$num/$totalExercises] $identifier — Inséré";
        } catch (PDOException $e) {
            echo "[$num/$totalExercises] ❌ $identifier — Erreur SQL (insert) : {$e->getMessage()}\n";
            $stats['errors']++;
            $stats['errorDetails'][] = [
                'num' => $num,
                'identifier' => $identifier,
                'file' => $sourceFile,
                'error' => "SQL insert error: {$e->getMessage()}"
            ];
            $logLines[] = "❌ [$num/$totalExercises] $identifier — Erreur SQL (insert)";
            continue;
        }
    }

    // ───── UPDATE réel ─────
    if ($action === 'UPDATE') {
        try {
            $updateStmt->execute([
                ':identifier' => $validated['identifier'],
                ':subject' => $validated['subject'],
                ':level' => $validated['level'],
                ':title' => $validated['title'],
                ':content' => $validated['content'],
                ':answer' => $validated['answer'],
                ':tips' => $validated['tips'],
                ':domain' => $validated['domain'],
                ':competence' => $validated['competence'],
                ':difficulty' => $validated['difficulty'],
                ':answer_type' => $validated['answer_type'],
                ':xp_points' => $validated['xp_points'],
                ':is_active' => $validated['is_active']
            ]);
            echo "[$num/$totalExercises] 🔄 $identifier — Mis à jour\n";
            $stats['updated']++;
            $logLines[] = "🔄 [$num/$totalExercises] $identifier — Mis à jour";
        } catch (PDOException $e) {
            echo "[$num/$totalExercises] ❌ $identifier — Erreur SQL (update) : {$e->getMessage()}\n";
            $stats['errors']++;
            $stats['errorDetails'][] = [
                'num' => $num,
                'identifier' => $identifier,
                'file' => $sourceFile,
                'error' => "SQL update error: {$e->getMessage()}"
            ];
            $logLines[] = "❌ [$num/$totalExercises] $identifier — Erreur SQL (update)";
            continue;
        }
    }

    // Stats
    $level = $validated['level'] ?? 'unknown';
    $stats['byLevel'][$level] = ($stats['byLevel'][$level] ?? 0) + 1;
    $subject = $validated['subject'] ?? 'unknown';
    $stats['bySubject'][$subject] = ($stats['bySubject'][$subject] ?? 0) + 1;
}

// ─────────────────────────────────────────────────────────────
// 📊 RÉCAPITULATIF
// ─────────────────────────────────────────────────────────────

echo "\n───────────────────────────────────────────────────────────────\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉCAPITULATIF FINAL\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "📝 Exercices traités      : $totalExercises\n";
echo "✅ Insertions             : {$stats['inserted']}\n";
echo "🔄 Mises à jour           : {$stats['updated']}\n";
echo "⏭️  Ignorés               : {$stats['skipped']}\n";
echo "❌ Erreurs                : {$stats['errors']}\n";
echo "───────────────────────────────────────────────────────────────\n";

// Stats par niveau
if (!empty($stats['byLevel'])) {
    echo "\n📚 Répartition par niveau :\n";
    arsort($stats['byLevel']);
    foreach ($stats['byLevel'] as $level => $count) {
        echo sprintf("  • %-20s : %4d exercices\n", $level, $count);
    }
}

// Stats par matière
if (!empty($stats['bySubject'])) {
    echo "\n📖 Répartition par matière :\n";
    arsort($stats['bySubject']);
    foreach ($stats['bySubject'] as $subject => $count) {
        echo sprintf("  • %-20s : %4d exercices\n", $subject, $count);
    }
}

// Détails des erreurs
if (!empty($stats['errorDetails'])) {
    echo "\n❌ DÉTAIL DES ERREURS :\n";
    echo "───────────────────────────────────────────────────────────────\n";
    foreach ($stats['errorDetails'] as $err) {
        echo "[{$err['num']}] {$err['identifier']} ({$err['file']})\n";
        echo "    → {$err['error']}\n\n";
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
if ($dryRun) {
    echo "🔍 Dry-run terminé — Aucune modification n'a été effectuée\n";
} else {
    echo "✅ Import terminé avec succès !\n";
}
echo "═══════════════════════════════════════════════════════════════\n\n";

// ─────────────────────────────────────────────────────────────
// 📝 ÉCRITURE DU RAPPORT
// ─────────────────────────────────────────────────────────────

$logLines[] = "\n═══════════════════════════════════════════════════════════════";
$logLines[] = "RÉCAPITULATIF";
$logLines[] = "═══════════════════════════════════════════════════════════════";
$logLines[] = "Exercices traités    : $totalExercises";
$logLines[] = "Insertions           : {$stats['inserted']}";
$logLines[] = "Mises à jour         : {$stats['updated']}";
$logLines[] = "Ignorés              : {$stats['skipped']}";
$logLines[] = "Erreurs              : {$stats['errors']}";
$logLines[] = "\n═══════════════════════════════════════════════════════════════\n";

if (!empty($stats['errorDetails'])) {
    $logLines[] = "\nDÉTAIL DES ERREURS";
    $logLines[] = "═══════════════════════════════════════════════════════════════";
    foreach ($stats['errorDetails'] as $err) {
        $logLines[] = "[{$err['num']}] {$err['identifier']} ({$err['file']})";
        $logLines[] = "    → {$err['error']}";
    }
    $logLines[] = "\n═══════════════════════════════════════════════════════════════\n";
}

file_put_contents(REPORT_FILE, implode("\n", $logLines));
echo "📄 Rapport détaillé : " . REPORT_FILE . "\n\n";
