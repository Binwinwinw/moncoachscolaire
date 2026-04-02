<?php
/**
 * EXPORT DES EXERCICES SANS RÉPONSE
 *
 * Exporte les exercices avec Answer = NULL pour enrichissement
 *
 * Format de sortie : JSON structuré pour enrichissement IA
 *
 * Usage :
 *   php export_exercises_without_answers.php
 */

// Configuration
define('PROJECT_ROOT', dirname(__DIR__, 3));
define('OUTPUT_DIR', PROJECT_ROOT . '/dev/db/json/schema/exercices');
define('OUTPUT_FILE', OUTPUT_DIR . '/exercises_without_answers.json');
define('REPORT_DIR', PROJECT_ROOT . '/dev/reports');

require_once PROJECT_ROOT . '/src/database/connection.php';

if (!is_dir(OUTPUT_DIR)) {
    mkdir(OUTPUT_DIR, 0755, true);
}

if (!is_dir(REPORT_DIR)) {
    mkdir(REPORT_DIR, 0755, true);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  EXPORT DES EXERCICES SANS RÉPONSE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. CONNEXION BDD
echo "🔌 Connexion à la base de données...\n";

try {
    global $pdo;

    if (!isset($pdo)) {
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'moncoachscolaire';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    echo "✅ Connecté\n\n";
} catch (PDOException $e) {
    die("❌ ERREUR BDD : " . $e->getMessage() . "\n");
}

// 2. EXTRACTION
echo "📤 Extraction des exercices sans réponse...\n";

$stmt = $pdo->query("
    SELECT
        Id,
        Identifier,
        Subject,
        Level,
        Title,
        Content,
        Answer,
        Difficulty,
        AnswerType,
        XP_Points,
        is_active,
        Tips,
        Domain,
        Competence,
        Choices,
        Instruction
    FROM exercises
    WHERE Answer IS NULL
    ORDER BY Subject, Level, Identifier
");

$exercises = $stmt->fetchAll();
$total = count($exercises);

echo "✅ $total exercices extraits\n\n";

// 3. STATISTIQUES
echo "📊 Analyse des exercices...\n";

$stats = [
    'total' => $total,
    'by_subject' => [],
    'by_level' => [],
    'by_answer_type' => [],
    'with_tips' => 0,
    'without_tips' => 0,
];

foreach ($exercises as $ex) {
    // Par matière
    $subject = $ex['Subject'] ?? 'Unknown';
    $stats['by_subject'][$subject] = ($stats['by_subject'][$subject] ?? 0) + 1;

    // Par niveau
    $level = $ex['Level'] ?? 'Unknown';
    $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;

    // Par type de réponse
    $answerType = $ex['AnswerType'] ?? 'Unknown';
    $stats['by_answer_type'][$answerType] = ($stats['by_answer_type'][$answerType] ?? 0) + 1;

    // Avec/sans Tips
    if (!empty($ex['Tips'])) {
        $stats['with_tips']++;
    } else {
        $stats['without_tips']++;
    }
}

// Trier les stats
arsort($stats['by_subject']);
arsort($stats['by_level']);
arsort($stats['by_answer_type']);

echo "✅ Analyse terminée\n\n";

// 4. SAUVEGARDE JSON
echo "💾 Sauvegarde du fichier JSON...\n";

$output = json_encode($exercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(OUTPUT_FILE, $output);

$fileSize = filesize(OUTPUT_FILE);
echo "✅ Fichier créé : " . basename(OUTPUT_FILE) . " (" . round($fileSize / 1024, 2) . " KB)\n\n";

// 5. RAPPORT DÉTAILLÉ
echo "📊 Génération du rapport...\n";

$txt = "═══════════════════════════════════════════════════════════════\n";
$txt .= "  RAPPORT D'EXPORT - EXERCICES SANS RÉPONSE\n";
$txt .= "═══════════════════════════════════════════════════════════════\n\n";
$txt .= "Date : " . date('Y-m-d H:i:s') . "\n";
$txt .= "Fichier : " . basename(OUTPUT_FILE) . "\n";
$txt .= "Taille : " . round($fileSize / 1024, 2) . " KB\n\n";

$txt .= "STATISTIQUES GLOBALES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("Total exercices sans réponse: %4d\n", $stats['total']);
$txt .= sprintf("Avec Tips (indices):          %4d (%.1f%%)\n",
    $stats['with_tips'],
    ($stats['with_tips'] / $stats['total']) * 100
);
$txt .= sprintf("Sans Tips:                    %4d (%.1f%%)\n\n",
    $stats['without_tips'],
    ($stats['without_tips'] / $stats['total']) * 100
);

$txt .= "RÉPARTITION PAR MATIÈRE\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
foreach ($stats['by_subject'] as $subject => $count) {
    $txt .= sprintf("%-30s %4d (%.1f%%)\n",
        $subject,
        $count,
        ($count / $stats['total']) * 100
    );
}
$txt .= "\n";

$txt .= "RÉPARTITION PAR NIVEAU\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
foreach ($stats['by_level'] as $level => $count) {
    $txt .= sprintf("%-30s %4d (%.1f%%)\n",
        $level,
        $count,
        ($count / $stats['total']) * 100
    );
}
$txt .= "\n";

$txt .= "RÉPARTITION PAR TYPE DE RÉPONSE\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
foreach ($stats['by_answer_type'] as $type => $count) {
    $txt .= sprintf("%-30s %4d (%.1f%%)\n",
        $type,
        $count,
        ($count / $stats['total']) * 100
    );
}
$txt .= "\n";

$txt .= "EXEMPLES (10 premiers)\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
foreach (array_slice($exercises, 0, 10) as $i => $ex) {
    $txt .= sprintf("%2d. %s\n", $i + 1, $ex['Identifier']);
    $txt .= "    Matière: " . ($ex['Subject'] ?? 'N/A') . " | Niveau: " . ($ex['Level'] ?? 'N/A') . "\n";
    $txt .= "    Titre: " . substr($ex['Title'] ?? '', 0, 60) . "\n";
    $txt .= "    Type réponse: " . ($ex['AnswerType'] ?? 'N/A') . "\n";
    $txt .= "    Tips: " . (empty($ex['Tips']) ? '❌ Non' : '✅ Oui') . "\n\n";
}

$txt .= "PROCHAINES ÉTAPES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= "1. Ouvrir le fichier dans VS Code :\n";
$txt .= "   " . basename(OUTPUT_FILE) . "\n\n";
$txt .= "2. Utiliser Copilot pour enrichir les réponses :\n";
$txt .= "   - Script d'enrichissement automatique\n";
$txt .= "   - Génération des réponses via IA\n";
$txt .= "   - Validation manuelle si nécessaire\n\n";
$txt .= "3. Réimporter les exercices enrichis :\n";
$txt .= "   php dev/tools/exercises/import_enriched_exercises.php\n\n";

file_put_contents(REPORT_DIR . '/export_without_answers_report.txt', $txt);

// Sauvegarde stats JSON
$statsJson = [
    'timestamp' => date('Y-m-d H:i:s'),
    'output_file' => basename(OUTPUT_FILE),
    'file_size_kb' => round($fileSize / 1024, 2),
    'statistics' => $stats,
];

file_put_contents(
    REPORT_DIR . '/export_without_answers_stats.json',
    json_encode($statsJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "✅ Rapport généré\n\n";

// 6. RÉSUMÉ
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ EXPORT TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exporté:               %4d exercices\n", $stats['total']);
echo sprintf("Avec Tips:                   %4d (%.1f%%)\n",
    $stats['with_tips'],
    ($stats['with_tips'] / $stats['total']) * 100
);
echo sprintf("Sans Tips:                   %4d (%.1f%%)\n\n",
    $stats['without_tips'],
    ($stats['without_tips'] / $stats['total']) * 100
);

echo "TOP 3 MATIÈRES\n";
echo "───────────────────────────────────────────────────────────────\n";
$i = 0;
foreach ($stats['by_subject'] as $subject => $count) {
    if ($i++ >= 3) break;
    echo sprintf("%-30s %4d\n", $subject, $count);
}
echo "\n";

echo "TOP 3 NIVEAUX\n";
echo "───────────────────────────────────────────────────────────────\n";
$i = 0;
foreach ($stats['by_level'] as $level => $count) {
    if ($i++ >= 3) break;
    echo sprintf("%-30s %4d\n", $level, $count);
}
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📄 Fichiers générés:\n";
echo "   • " . OUTPUT_FILE . "\n";
echo "   • " . REPORT_DIR . "/export_without_answers_report.txt\n";
echo "   • " . REPORT_DIR . "/export_without_answers_stats.json\n\n";

echo "📋 Prochaine étape:\n";
echo "   Ouvrir VS Code et utiliser Copilot pour enrichir les réponses\n";
echo "   Fichier à enrichir : " . basename(OUTPUT_FILE) . "\n\n";
