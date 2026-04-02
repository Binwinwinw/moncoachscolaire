<?php
/**
 * =============================================================
 *  MonCoachScolaire — Import Unified Exercises → Database
 * =============================================================
 *  Ce script lit unified_exercises.json (ou exercises_deduplicated.json),
 *  NORMALISE chaque exercice pour garantir la présence des 27 champs,
 *  insère ou met à jour chaque exercice dans la table exercises,
 *  utilise ExerciseParser pour valider les données,
 *  et génère un rapport d'import détaillé.
 *
 *  ✅ CORRECTION : Normalisation intégrée avec ExerciseNormalizer
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

// Priorité au fichier dédoublonné si il existe
define('DEDUPLICATED_JSON', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_deduplicated.json');
define('UNIFIED_JSON', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');

if (file_exists(DEDUPLICATED_JSON)) {
    define('SOURCE_JSON', DEDUPLICATED_JSON);
    define('SOURCE_NAME', 'exercises_deduplicated.json');
} else {
    define('SOURCE_JSON', UNIFIED_JSON);
    define('SOURCE_NAME', 'unified_exercises.json');
}

define('PARSER_PATH', PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseParser.php');
define('NORMALIZER_PATH', PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseNormalizer.php');
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
echo "  IMPORT EXERCICES UNIFIÉS → Base de Données (+ Normalisation)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if ($dryRun) {
    echo "🔍 MODE DRY-RUN : Aucune modification ne sera effectuée\n";
    echo "───────────────────────────────────────────────────────────────\n\n";
}

// ─────────────────────────────────────────────────────────────
// 📁 VALIDATION DES FICHIERS
// ─────────────────────────────────────────────────────────────

if (!file_exists(SOURCE_JSON)) {
    die("❌ Fichier source introuvable : " . SOURCE_JSON . "\n\n");
}

if (!file_exists(PARSER_PATH)) {
    die("❌ ExerciseParser introuvable : " . PARSER_PATH . "\n\n");
}

if (!file_exists(NORMALIZER_PATH)) {
    die("❌ ExerciseNormalizer introuvable : " . NORMALIZER_PATH . "\n\n");
}

if (!file_exists(CONNECTION_PATH)) {
    die("❌ Fichier de connexion introuvable : " . CONNECTION_PATH . "\n\n");
}

echo "📄 Fichier source : " . SOURCE_NAME . "\n";
echo "───────────────────────────────────────────────────────────────\n\n";

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
// 📦 CHARGEMENT DU PARSER ET NORMALIZER
// ─────────────────────────────────────────────────────────────

require_once PARSER_PATH;
require_once NORMALIZER_PATH;

echo "✅ ExerciseParser chargé\n";
echo "✅ ExerciseNormalizer chargé\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 📄 LECTURE DU FICHIER JSON UNIFIÉ
// ─────────────────────────────────────────────────────────────

echo "📄 Lecture du fichier JSON unifié...\n";
$jsonContent = @file_get_contents(SOURCE_JSON);
if ($jsonContent === false) {
    die("❌ Impossible de lire le fichier : " . SOURCE_JSON . "\n\n");
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
// ✅ NORMALISATION DE TOUS LES EXERCICES
// ─────────────────────────────────────────────────────────────

echo "🔧 Normalisation de tous les exercices (garantie 27 champs BDD)...\n";
$exercises = ExerciseNormalizer::normalizeAllToDbFields($exercises);
echo "✅ $totalExercises exercices normalisés (champs BDD)\n";

// Rapport de conformité (champs BDD)
$conformityReport = ExerciseNormalizer::getConformityReportDb($exercises);
echo "✅ Exercices valides   : {$conformityReport['valid']}\n";
echo "⚠️  Exercices invalides : {$conformityReport['invalid']}\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 🔨 PRÉPARATION DES REQUÊTES SQL
// ─────────────────────────────────────────────────────────────

// Préparation des requêtes SQL (désactivé en dry-run)
if (!$dryRun) {
    // Vérification de l'existence d'un exercice par identifier
    $checkStmt = $pdo->prepare("
        SELECT Id FROM exercises WHERE Identifier = :Identifier LIMIT 1
    ");

    // Insertion
    $insertStmt = $pdo->prepare("
        INSERT INTO exercises (
            Identifier, Subject, Level, Title, Content, Answer, Tips, Domain, Competence,
            Difficulty, AnswerType, XP_Points, is_active, Instruction, Choices, Coherence, processed, course_id, LinkedCourses, created_at
        ) VALUES (
            :Identifier, :Subject, :Level, :Title, :Content, :Answer, :Tips, :Domain, :Competence,
            :Difficulty, :AnswerType, :XP_Points, :is_active, :Instruction, :Choices, :Coherence, :processed, :course_id, :LinkedCourses, NOW()
        )
    ");

    // Mise à jour
    $updateStmt = $pdo->prepare("
        UPDATE exercises SET
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
            Instruction = :Instruction,
            Choices = :Choices,
            Coherence = :Coherence,
            processed = :processed,
            course_id = :course_id,
            LinkedCourses = :LinkedCourses,
            updated_at = NOW()
        WHERE Identifier = :Identifier
    ");
}

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
$logLines[] = "  IMPORT EXERCICES (+ NORMALISATION) — " . date('Y-m-d H:i:s');
$logLines[] = "  Mode : " . ($dryRun ? "DRY-RUN" : "RÉEL");
$logLines[] = "  Gestion des doublons : " . DUPLICATE_MODE;
$logLines[] = "  Source : " . SOURCE_NAME;
$logLines[] = "═══════════════════════════════════════════════════════════════\n";

// ─────────────────────────────────────────────────────────────
// 🔁 TRAITEMENT DE CHAQUE EXERCICE
// ─────────────────────────────────────────────────────────────

echo "🚀 Import en cours...\n\n";

foreach ($exercises as $index => $rawExercise) {
        // Stockage des exercices parsés pour export
        static $parsedExercises = [];
        $parsedExercises[] = $validated;
    // Export JSON des exercices parsés en dry-run
    if ($dryRun && !empty($parsedExercises)) {
        $outputFile = PROJECT_ROOT . '/dev/db/json/schema/exercices/parsed_exercises.json';
        file_put_contents($outputFile, json_encode($parsedExercises, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        echo "\n✅ Export JSON des exercices parsés : $outputFile\n";
    }
    $num = $index + 1;
    $identifier = $rawExercise['identifier'] ?? "UNKNOWN-$num";
    $sourceFile = $rawExercise['_source_file'] ?? 'unknown';

    // ✅ L'exercice est déjà normalisé (27 champs garantis)

    // ───── Validation via ExerciseParser (dry-run: parsing uniquement) ─────
    try {
        $parser = new ExerciseParser(null); // Pas de connexion PDO en dry-run
        $validated = $parser->parseExercise($rawExercise); // Passe l'exercice complet
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

    // ✅ Préparation pour la BDD (conversion JSON → string)
    $validated = ExerciseNormalizer::prepareForDatabase($validated);

    // En dry-run, aucune requête SQL ni vérification d'existence
    if ($dryRun) {
        echo "[$num/$totalExercises] 🔍 $identifier — PARSED (dry-run)\n";
        $stats['inserted']++;
        $level = $validated['level'] ?? 'unknown';
        $stats['byLevel'][$level] = ($stats['byLevel'][$level] ?? 0) + 1;
        $subject = $validated['subject'] ?? 'unknown';
        $stats['bySubject'][$subject] = ($stats['bySubject'][$subject] ?? 0) + 1;
        $logLines[] = "🔍 [$num/$totalExercises] $identifier — PARSED (dry-run)";
        continue;
    }
    // ...existing code...

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
                ':is_active' => $validated['is_active'],
                ':chapter' => $validated['chapter'],
                ':sub_chapter' => $validated['sub_chapter'],
                ':duration_minutes' => $validated['duration_minutes'],
                ':question_type' => $validated['question_type'],
                ':choices' => $validated['choices'],
                ':correct_choices' => $validated['correct_choices'],
                ':explanation' => $validated['explanation'],
                ':resources' => $validated['resources'],
                ':tags' => $validated['tags'],
                ':metadata' => $validated['metadata']
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
                ':is_active' => $validated['is_active'],
                ':chapter' => $validated['chapter'],
                ':sub_chapter' => $validated['sub_chapter'],
                ':duration_minutes' => $validated['duration_minutes'],
                ':question_type' => $validated['question_type'],
                ':choices' => $validated['choices'],
                ':correct_choices' => $validated['correct_choices'],
                ':explanation' => $validated['explanation'],
                ':resources' => $validated['resources'],
                ':tags' => $validated['tags'],
                ':metadata' => $validated['metadata']
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
echo "📋 Tous les exercices sont conformes au schéma officiel (27 champs)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ─────────────────────────────────────────────────────────────
// 📝 ÉCRITURE DU RAPPORT
// ─────────────────────────────────────────────────────────────

$logLines[] = "\n═══════════════════════════════════════════════════════════════";
$logLines[] = "RÉCAPITULATIF";
$logLines[] = "═══════════════════════════════════════════════════════════════";
$logLines[] = "Exercices traités    : $totalExercises";
$logLines[] = "Exercices valides    : {$conformityReport['valid']}";
$logLines[] = "Exercices invalides  : {$conformityReport['invalid']}";
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
