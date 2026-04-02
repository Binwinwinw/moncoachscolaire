<?php
/**
 * =============================================================
 *  MonCoachScolaire — Export Exercises from Database → JSON
 * =============================================================
 *  VERSION SIMPLIFIÉE : Utilise directement les noms de colonnes BDD
 *  (Subject, AnswerType, XP_Points, etc.)
 * =============================================================
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
mb_internal_encoding('UTF-8');

define('PROJECT_ROOT', dirname(__DIR__, 3));
define('CONNECTION_PATH', PROJECT_ROOT . '/src/database/connection.php');
define('OUTPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json');
define('REPORT_FILE', PROJECT_ROOT . '/dev/reports/export_exercises_from_db.txt');

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "  EXPORT EXERCICES — Base de Données → JSON\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

if (!file_exists(CONNECTION_PATH)) {
    die("❌ Fichier de connexion introuvable\n\n");
}

echo "🔌 Connexion à la base de données...\n";
require_once CONNECTION_PATH;

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("❌ Variable \$pdo non disponible\n\n");
}

echo "✅ Connexion établie\n";
echo "───────────────────────────────────────────────────────────────\n\n";

echo "📊 Extraction des exercices de la table 'exercises'...\n";

try {
    // Utiliser les VRAIS noms de colonnes de votre BDD
    $stmt = $pdo->query("
        SELECT 
            Id,
            Subject,
            Level,
            Title,
            Content,
            structure_type,
            pattern_detected,
            sub_questions,
            Type,
            Answer,
            InteractiveConfig,
            Tips,
            Domain,
            Competence,
            Difficulty,
            exam_prep,
            Identifier,
            AnswerType,
            Choices,
            Instruction,
            is_active,
            XP_Points,
            Coherence,
            processed,
            course_id,
            LinkedCourses
        FROM exercises
        ORDER BY Id ASC
    ");
    
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalExercises = count($exercises);
    
    echo "✅ $totalExercises exercices extraits\n";
    echo "───────────────────────────────────────────────────────────────\n\n";
    
} catch (PDOException $e) {
    die("❌ Erreur SQL : {$e->getMessage()}\n\n");
}

// ─────────────────────────────────────────────────────────────
// 📈 ANALYSE DES DONNÉES
// ─────────────────────────────────────────────────────────────

$stats = [
    'total' => $totalExercises,
    'active' => 0,
    'inactive' => 0,
    'byLevel' => [],
    'bySubject' => [],
    'byDifficulty' => [],
    'byAnswerType' => [],
    'withIdentifier' => 0,
    'withoutIdentifier' => 0,
    'duplicateIdentifiers' => []
];

$identifierCounts = [];

foreach ($exercises as &$exercise) {
    // Normalisation des types
    $exercise['Id'] = (int) $exercise['Id'];
    $exercise['XP_Points'] = (int) ($exercise['XP_Points'] ?? 0);
    $exercise['is_active'] = (int) ($exercise['is_active'] ?? 1);
    
    // Stats is_active
    if ($exercise['is_active'] === 1) {
        $stats['active']++;
    } else {
        $stats['inactive']++;
    }
    
    // Stats par niveau
    $level = $exercise['Level'] ?? 'unknown';
    $stats['byLevel'][$level] = ($stats['byLevel'][$level] ?? 0) + 1;
    
    // Stats par matière
    $subject = $exercise['Subject'] ?? 'unknown';
    $stats['bySubject'][$subject] = ($stats['bySubject'][$subject] ?? 0) + 1;
    
    // Stats par difficulté
    $difficulty = $exercise['Difficulty'] ?? 'unknown';
    $stats['byDifficulty'][$difficulty] = ($stats['byDifficulty'][$difficulty] ?? 0) + 1;
    
    // Stats par type de réponse
    $answerType = $exercise['AnswerType'] ?? 'unknown';
    $stats['byAnswerType'][$answerType] = ($stats['byAnswerType'][$answerType] ?? 0) + 1;
    
    // Stats identifiers
    if (!empty($exercise['Identifier'])) {
        $stats['withIdentifier']++;
        $identifier = $exercise['Identifier'];
        $identifierCounts[$identifier] = ($identifierCounts[$identifier] ?? 0) + 1;
    } else {
        $stats['withoutIdentifier']++;
    }
}
unset($exercise);

// Détection des doublons d'identifiers
foreach ($identifierCounts as $identifier => $count) {
    if ($count > 1) {
        $stats['duplicateIdentifiers'][$identifier] = $count;
    }
}

// ─────────────────────────────────────────────────────────────
// 💾 ÉCRITURE DU FICHIER JSON
// ─────────────────────────────────────────────────────────────

echo "💾 Écriture du fichier JSON...\n";

$jsonOutput = json_encode($exercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($jsonOutput === false) {
    die("❌ Erreur lors de l'encodage JSON : " . json_last_error_msg() . "\n\n");
}

$written = @file_put_contents(OUTPUT_FILE, $jsonOutput);
if ($written === false) {
    die("❌ Erreur lors de l'écriture du fichier : " . OUTPUT_FILE . "\n\n");
}

$fileSize = filesize(OUTPUT_FILE);
$fileSizeKB = round($fileSize / 1024, 2);

echo "✅ Fichier créé : " . OUTPUT_FILE . "\n";
echo "📊 Taille       : $fileSizeKB KB ($fileSize octets)\n";
echo "───────────────────────────────────────────────────────────────\n\n";

// ─────────────────────────────────────────────────────────────
// 📊 RÉCAPITULATIF
// ─────────────────────────────────────────────────────────────

echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉCAPITULATIF FINAL\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "📝 Exercices extraits     : {$stats['total']}\n";
echo "✅ Actifs                 : {$stats['active']}\n";
echo "❌ Inactifs               : {$stats['inactive']}\n";
echo "🔑 Avec identifier        : {$stats['withIdentifier']}\n";
echo "⚠️  Sans identifier       : {$stats['withoutIdentifier']}\n";
echo "🔄 Identifiers dupliqués  : " . count($stats['duplicateIdentifiers']) . "\n";
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

// Stats par difficulté
if (!empty($stats['byDifficulty'])) {
    echo "\n🎯 Répartition par difficulté :\n";
    arsort($stats['byDifficulty']);
    foreach ($stats['byDifficulty'] as $diff => $count) {
        echo sprintf("  • %-20s : %4d exercices\n", $diff, $count);
    }
}

// Stats par type de réponse
if (!empty($stats['byAnswerType'])) {
    echo "\n💬 Répartition par type de réponse :\n";
    arsort($stats['byAnswerType']);
    foreach ($stats['byAnswerType'] as $type => $count) {
        echo sprintf("  • %-20s : %4d exercices\n", $type, $count);
    }
}

// Détail des doublons
if (!empty($stats['duplicateIdentifiers'])) {
    echo "\n⚠️  IDENTIFIERS DUPLIQUÉS DÉTECTÉS :\n";
    echo "───────────────────────────────────────────────────────────────\n";
    arsort($stats['duplicateIdentifiers']);
    foreach ($stats['duplicateIdentifiers'] as $identifier => $count) {
        echo "  • $identifier : $count occurrences\n";
    }
    echo "\n💡 Action recommandée : Utiliser le script de dédoublonnage\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "✅ Export terminé avec succès !\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ─────────────────────────────────────────────────────────────
// 📝 ÉCRITURE DU RAPPORT
// ─────────────────────────────────────────────────────────────

$logLines = [];
$logLines[] = "═══════════════════════════════════════════════════════════════";
$logLines[] = "  EXPORT EXERCICES BDD → JSON — " . date('Y-m-d H:i:s');
$logLines[] = "═══════════════════════════════════════════════════════════════\n";
$logLines[] = "Exercices extraits     : {$stats['total']}";
$logLines[] = "Actifs                 : {$stats['active']}";
$logLines[] = "Inactifs               : {$stats['inactive']}";
$logLines[] = "Avec identifier        : {$stats['withIdentifier']}";
$logLines[] = "Sans identifier        : {$stats['withoutIdentifier']}";
$logLines[] = "Identifiers dupliqués  : " . count($stats['duplicateIdentifiers']);
$logLines[] = "\nFichier de sortie      : " . OUTPUT_FILE;
$logLines[] = "Taille                 : $fileSizeKB KB";

if (!empty($stats['duplicateIdentifiers'])) {
    $logLines[] = "\n═══════════════════════════════════════════════════════════════";
    $logLines[] = "IDENTIFIERS DUPLIQUÉS";
    $logLines[] = "═══════════════════════════════════════════════════════════════";
    foreach ($stats['duplicateIdentifiers'] as $identifier => $count) {
        $logLines[] = "$identifier : $count occurrences";
    }
}

$logLines[] = "\n═══════════════════════════════════════════════════════════════\n";

file_put_contents(REPORT_FILE, implode("\n", $logLines));
echo "📄 Rapport détaillé : " . REPORT_FILE . "\n\n";
