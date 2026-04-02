<?php
/**
 * =============================================================
 *  MonCoachScolaire — Collect All Exercises → Unified JSON
 * =============================================================
 *  Ce script scanne récursivement dev/db/json/exercices/,
 *  collecte tous les fichiers *.migrated.json,
 *  fusionne leurs contenus dans un seul tableau,
 *  et écrit le résultat dans unified_exercises.json
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
define('JSON_SOURCE_DIR', PROJECT_ROOT . '/dev/db/json/exercices');
define('OUTPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json');
define('LOG_FILE', PROJECT_ROOT . '/dev/reports/collect_exercises.log');

// ─────────────────────────────────────────────────────────────
// 🎨 HEADER
// ─────────────────────────────────────────────────────────────

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  COLLECTE D'EXERCICES → Fichier Unifié JSON\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ─────────────────────────────────────────────────────────────
// 📁 VALIDATION DES DOSSIERS
// ─────────────────────────────────────────────────────────────

if (!is_dir(JSON_SOURCE_DIR)) {
    die("❌ Dossier source introuvable : " . JSON_SOURCE_DIR . "\n");
}

$outputDir = dirname(OUTPUT_FILE);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "✅ Dossier de sortie créé : $outputDir\n";
}

$logDir = dirname(LOG_FILE);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// ─────────────────────────────────────────────────────────────
// 🔍 FONCTION : Scan récursif pour fichiers *.migrated.json
// ─────────────────────────────────────────────────────────────

function findMigratedFiles(string $dir): array {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.migrated.json')) {
            $files[] = $file->getPathname();
        }
    }

    sort($files);
    return $files;
}

// ─────────────────────────────────────────────────────────────
// 📦 COLLECTE DES EXERCICES
// ─────────────────────────────────────────────────────────────

$migratedFiles = findMigratedFiles(JSON_SOURCE_DIR);
$totalFiles = count($migratedFiles);

echo "📂 Dossier source    : " . JSON_SOURCE_DIR . "\n";
echo "📄 Fichiers trouvés  : $totalFiles fichiers *.migrated.json\n";
echo "📦 Fichier de sortie : " . OUTPUT_FILE . "\n";
echo "───────────────────────────────────────────────────────────────\n\n";

if ($totalFiles === 0) {
    die("⚠️  Aucun fichier *.migrated.json trouvé.\n\n");
}

$allExercises = [];
$stats = [
    'totalFiles' => 0,
    'totalExercises' => 0,
    'errors' => 0,
    'byLevel' => [],
    'bySubject' => []
];

$logLines = [];
$logLines[] = "═══════════════════════════════════════════════════════════════";
$logLines[] = "  COLLECTE D'EXERCICES — " . date('Y-m-d H:i:s');
$logLines[] = "═══════════════════════════════════════════════════════════════\n";

// ─────────────────────────────────────────────────────────────
// 🔁 TRAITEMENT DE CHAQUE FICHIER
// ─────────────────────────────────────────────────────────────

foreach ($migratedFiles as $index => $filePath) {
    $num = $index + 1;
    $relativePath = str_replace(JSON_SOURCE_DIR . DIRECTORY_SEPARATOR, '', $filePath);
    $relativePath = str_replace('\\', '/', $relativePath);

    echo "[$num/$totalFiles] Traitement : $relativePath\n";

    // Lecture du fichier
    $jsonContent = @file_get_contents($filePath);
    if ($jsonContent === false) {
        echo "  ❌ ERREUR : impossible de lire le fichier\n\n";
        $stats['errors']++;
        $logLines[] = "❌ [$num/$totalFiles] $relativePath — Lecture impossible";
        continue;
    }

    // Décodage JSON
    $data = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "  ❌ ERREUR : JSON invalide (" . json_last_error_msg() . ")\n\n";
        $stats['errors']++;
        $logLines[] = "❌ [$num/$totalFiles] $relativePath — JSON invalide : " . json_last_error_msg();
        continue;
    }

    // Normalisation : toujours un tableau
    if (!is_array($data)) {
        echo "  ⚠️  Format inattendu (non-array), ignoré\n\n";
        $stats['errors']++;
        $logLines[] = "⚠️  [$num/$totalFiles] $relativePath — Format non-array";
        continue;
    }

    // Si c'est un tableau d'objets (standard)
    if (isset($data[0]) && is_array($data[0])) {
        $exercises = $data;
    }
    // Si c'est un objet unique, on l'enveloppe
    else if (!isset($data[0])) {
        $exercises = [$data];
    }
    else {
        $exercises = [];
    }

    $count = count($exercises);

    // Ajout de métadonnées et normalisation des champs pour chaque exercice
    $requiredFields = [
        'Id','Subject','Level','Title','Content','structure_type','pattern_detected','sub_questions','Type','Answer','InteractiveConfig','Tips','Domain','Competence','Difficulty','exam_prep','Identifier','AnswerType','Choices','Instruction','is_active','XP_Points','Coherence','processed','course_id','LinkedCourses'
    ];
    foreach ($exercises as &$ex) {
        $ex['_source_file'] = $relativePath;
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $ex)) {
                $ex[$field] = null;
            }
        }
        // Stats par niveau
        $level = $ex['level'] ?? 'unknown';
        $stats['byLevel'][$level] = ($stats['byLevel'][$level] ?? 0) + 1;
        // Stats par matière
        $subject = $ex['subject'] ?? 'unknown';
        $stats['bySubject'][$subject] = ($stats['bySubject'][$subject] ?? 0) + 1;
    }
    unset($ex);

    $allExercises = array_merge($allExercises, $exercises);
    $stats['totalFiles']++;
    $stats['totalExercises'] += $count;

    echo "  ✅ OK : $count exercice(s) collecté(s)\n\n";
    $logLines[] = "✅ [$num/$totalFiles] $relativePath — $count exercices";
}

// ─────────────────────────────────────────────────────────────
// 💾 ÉCRITURE DU FICHIER UNIFIÉ
// ─────────────────────────────────────────────────────────────

echo "───────────────────────────────────────────────────────────────\n";
echo "💾 Écriture du fichier unifié...\n";

$jsonOutput = json_encode($allExercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
echo "📁 Fichiers traités       : {$stats['totalFiles']} / $totalFiles\n";
echo "📝 Exercices collectés    : {$stats['totalExercises']}\n";
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

echo "\n═══════════════════════════════════════════════════════════════\n";
echo "✅ Collecte terminée avec succès !\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ─────────────────────────────────────────────────────────────
// 📝 ÉCRITURE DU LOG
// ─────────────────────────────────────────────────────────────

$logLines[] = "\n═══════════════════════════════════════════════════════════════";
$logLines[] = "RÉCAPITULATIF";
$logLines[] = "═══════════════════════════════════════════════════════════════";
$logLines[] = "Fichiers traités     : {$stats['totalFiles']} / $totalFiles";
$logLines[] = "Exercices collectés  : {$stats['totalExercises']}";
$logLines[] = "Erreurs              : {$stats['errors']}";
$logLines[] = "Fichier de sortie    : " . OUTPUT_FILE;
$logLines[] = "Taille               : $fileSizeKB KB";
$logLines[] = "\n═══════════════════════════════════════════════════════════════\n";

file_put_contents(LOG_FILE, implode("\n", $logLines));
echo "📄 Log détaillé : " . LOG_FILE . "\n\n";
