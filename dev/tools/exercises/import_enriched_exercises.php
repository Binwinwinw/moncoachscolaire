<?php
/**
 * IMPORT DES EXERCICES ENRICHIS
 *
 * Met à jour uniquement le champ Answer des exercices enrichis
 *
 * Modes :
 * - --dry-run : Simulation
 * - (défaut)  : Import réel
 *
 * Usage :
 *   php import_enriched_exercises.php --dry-run
 *   php import_enriched_exercises.php
 */

// Configuration
define('PROJECT_ROOT', dirname(__DIR__, 3));
define('INPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_enriched.json');
define('REPORT_DIR', PROJECT_ROOT . '/dev/reports');

require_once PROJECT_ROOT . '/src/database/connection.php';

$dryRun = in_array('--dry-run', $argv);

if (!is_dir(REPORT_DIR)) {
    mkdir(REPORT_DIR, 0755, true);
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "  IMPORT DES EXERCICES ENRICHIS" . ($dryRun ? ' (DRY-RUN)' : '') . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. CHARGEMENT
echo "📄 Chargement du fichier enrichi...\n";
if (!file_exists(INPUT_FILE)) {
    die("❌ ERREUR : Fichier introuvable : " . INPUT_FILE . "\n");
}

$json = file_get_contents(INPUT_FILE);
$exercises = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ ERREUR JSON : " . json_last_error_msg() . "\n");
}

$total = count($exercises);
echo "✅ $total exercices chargés\n\n";

// 2. CONNEXION BDD
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

// 3. STATISTIQUES
$stats = [
    'total' => $total,
    'updated' => 0,
    'skipped_null' => 0,
    'skipped_not_found' => 0,
    'errors' => 0,
    'activated' => 0,
];

$examples = [];
$errors = [];

// Préparer la requête UPDATE
$updateStmt = $pdo->prepare("
    UPDATE exercises
    SET Answer = :answer,
        is_active = 1
    WHERE Identifier = :identifier
");

$checkStmt = $pdo->prepare("
    SELECT Id, Answer
    FROM exercises
    WHERE Identifier = :identifier
");

// 4. IMPORT
echo "📥 Import en cours...\n";

foreach ($exercises as $index => $ex) {
    $identifier = $ex['Identifier'] ?? "UNKNOWN-$index";
    $newAnswer = $ex['Answer'] ?? null;

    try {
        // Vérifier si l'exercice existe
        $checkStmt->execute(['identifier' => $identifier]);
        $existing = $checkStmt->fetch();

        if (!$existing) {
            $stats['skipped_not_found']++;
            continue;
        }

        // Ignorer si Answer est toujours NULL
        if (is_null($newAnswer) || trim($newAnswer) === '') {
            $stats['skipped_null']++;
            continue;
        }

        // Mettre à jour
        if (!$dryRun) {
            $updateStmt->execute([
                'identifier' => $identifier,
                'answer' => $newAnswer,
            ]);
            $stats['updated']++;

            // Si l'exercice était inactif, on l'active
            if ($existing['Answer'] === null) {
                $stats['activated']++;
            }
        } else {
            $stats['updated']++;
            if ($existing['Answer'] === null) {
                $stats['activated']++;
            }
        }

        // Garder les 20 premiers exemples
        if (count($examples) < 20) {
            $examples[] = [
                'identifier' => $identifier,
                'old_answer' => $existing['Answer'],
                'new_answer' => substr($newAnswer, 0, 80),
            ];
        }

    } catch (Exception $e) {
        $stats['errors']++;
        $errors[] = [
            'identifier' => $identifier,
            'error' => $e->getMessage(),
        ];
    }

    // Progression
    if (($index + 1) % 50 === 0) {
        echo "   Traité: " . ($index + 1) . "/$total\n";
    }
}

echo "\n";

// 5. RAPPORT
echo "📊 Génération du rapport...\n";

$txt = "═══════════════════════════════════════════════════════════════\n";
$txt .= "  RAPPORT D'IMPORT DES EXERCICES ENRICHIS\n";
$txt .= "═══════════════════════════════════════════════════════════════\n\n";
$txt .= "Date : " . date('Y-m-d H:i:s') . "\n";
$txt .= "Mode : " . ($dryRun ? 'DRY-RUN (simulation)' : 'RÉEL') . "\n";
$txt .= "Fichier : " . basename(INPUT_FILE) . "\n\n";

$txt .= "STATISTIQUES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("Total exercices:              %4d\n", $stats['total']);
$txt .= sprintf("✅ Mis à jour:                %4d (%.1f%%)\n",
    $stats['updated'],
    ($stats['updated'] / $stats['total']) * 100
);
$txt .= sprintf("   dont activés (NULL→valide): %4d\n", $stats['activated']);
$txt .= sprintf("⏭️  Ignorés (Answer NULL):     %4d\n", $stats['skipped_null']);
$txt .= sprintf("⏭️  Ignorés (non trouvés):     %4d\n", $stats['skipped_not_found']);
$txt .= sprintf("❌ Erreurs:                    %4d\n\n", $stats['errors']);

if (count($examples) > 0) {
    $txt .= "EXEMPLES DE MISES À JOUR (20 premiers)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($examples as $i => $ex) {
        $txt .= sprintf("%2d. %s\n", $i + 1, $ex['identifier']);
        $txt .= "    AVANT: " . ($ex['old_answer'] ?? 'NULL') . "\n";
        $txt .= "    APRÈS: " . $ex['new_answer'] . "\n\n";
    }
}

if (count($errors) > 0) {
    $txt .= "ERREURS\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($errors as $error) {
        $txt .= $error['identifier'] . " : " . $error['error'] . "\n";
    }
    $txt .= "\n";
}

file_put_contents(REPORT_DIR . '/import_enriched_report.txt', $txt);

// 6. RÉSUMÉ
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ " . ($dryRun ? 'SIMULATION' : 'IMPORT') . " TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exercices:              %4d\n", $stats['total']);
echo sprintf("✅ Mis à jour:                %4d (%.1f%%)\n",
    $stats['updated'],
    ($stats['updated'] / $stats['total']) * 100
);
echo sprintf("   dont activés (NULL→valide): %4d\n", $stats['activated']);
echo sprintf("⏭️  Ignorés (Answer NULL):     %4d\n", $stats['skipped_null']);
echo sprintf("⏭️  Ignorés (non trouvés):     %4d\n", $stats['skipped_not_found']);
echo sprintf("❌ Erreurs:                    %4d\n", $stats['errors']);
echo "═══════════════════════════════════════════════════════════════\n\n";

if ($dryRun) {
    echo "📋 Pour l'import réel:\n";
    echo "   php " . basename(__FILE__) . "\n\n";
} else {
    echo "📄 Rapport: " . REPORT_DIR . "/import_enriched_report.txt\n\n";

    // Statistiques finales BDD
    $countStmt = $pdo->query("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN Answer IS NOT NULL THEN 1 ELSE 0 END) as with_answer,
            SUM(CASE WHEN Answer IS NULL THEN 1 ELSE 0 END) as without_answer,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
        FROM exercises
    ");
    $finalStats = $countStmt->fetch();

    echo "📊 ÉTAT FINAL DE LA BASE DE DONNÉES\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo sprintf("Total exercices:              %4d\n", $finalStats['total']);
    echo sprintf("✅ Avec réponse:              %4d (%.1f%%)\n",
        $finalStats['with_answer'],
        ($finalStats['with_answer'] / $finalStats['total']) * 100
    );
    echo sprintf("⚠️  Sans réponse:              %4d (%.1f%%)\n",
        $finalStats['without_answer'],
        ($finalStats['without_answer'] / $finalStats['total']) * 100
    );
    echo sprintf("🟢 Actifs:                    %4d (%.1f%%)\n",
        $finalStats['active'],
        ($finalStats['active'] / $finalStats['total']) * 100
    );
    echo "═══════════════════════════════════════════════════════════════\n\n";
}
