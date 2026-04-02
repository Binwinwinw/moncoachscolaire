<?php
/**
 * IMPORT DES EXERCICES NETTOYÉS AVEC TRUNCATE SÉCURISÉ
 *
 * Gère les contraintes de clés étrangères
 *
 * Usage :
 *   php import_cleaned_exercises_safe.php --dry-run
 *   php import_cleaned_exercises_safe.php --truncate
 */

// Configuration
define('PROJECT_ROOT', dirname(__DIR__, 3));
define('INPUT_FILE', PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_stage2_cleaned.json');
define('REPORT_DIR', PROJECT_ROOT . '/dev/reports');
define('LOG_FILE', REPORT_DIR . '/import_cleaned.log');

require_once PROJECT_ROOT . '/src/database/connection.php';

if (!is_dir(REPORT_DIR)) {
    mkdir(REPORT_DIR, 0755, true);
}

$dryRun = in_array('--dry-run', $argv);
$truncate = in_array('--truncate', $argv);

echo "═══════════════════════════════════════════════════════════════\n";
echo "  IMPORT EXERCICES NETTOYÉS" . ($dryRun ? ' (DRY-RUN)' : '') . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. CHARGEMENT
echo "📄 Chargement du fichier...\n";
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

// 3. TRUNCATE SÉCURISÉ (avec gestion des FK)
if ($truncate && !$dryRun) {
    echo "⚠️  MODE TRUNCATE : Vidage sécurisé des tables...\n\n";

    try {
        // Désactiver les vérifications de clés étrangères
        echo "   1. Désactivation des contraintes FK...\n";
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        // Vider les tables dépendantes d'abord
        echo "   2. Vidage de exercisecourselinks...\n";
        $pdo->exec("TRUNCATE TABLE exercisecourselinks");

        // Vider la table exercises
        echo "   3. Vidage de exercises...\n";
        $pdo->exec("TRUNCATE TABLE exercises");

        // Réactiver les vérifications
        echo "   4. Réactivation des contraintes FK...\n";
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        echo "✅ Tables vidées avec succès\n\n";

    } catch (PDOException $e) {
        // Toujours réactiver les FK en cas d'erreur
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        die("❌ ERREUR TRUNCATE : " . $e->getMessage() . "\n");
    }
}

// 4. STATISTIQUES
$stats = [
    'total' => $total,
    'inserted' => 0,
    'updated' => 0,
    'ignored' => 0,
    'errors' => 0,
    'null_answers' => 0,
];

$actions = [];
$errors = [];

// Préparer les requêtes
if (!$truncate) {
    $checkStmt = $pdo->prepare("SELECT Id FROM exercises WHERE Identifier = :identifier");
}

$insertStmt = $pdo->prepare("
    INSERT INTO exercises (
        Identifier, Subject, Level, Title, Content, Answer, Difficulty, AnswerType,
        XP_Points, is_active, Tips, Domain, Competence, Choices, Instruction
    ) VALUES (
        :identifier, :subject, :level, :title, :content, :answer, :difficulty, :answer_type,
        :xp_points, :is_active, :tips, :domain, :competence, :choices, :instruction
    )
");

if (!$truncate) {
    $updateStmt = $pdo->prepare("
        UPDATE exercises SET
            Subject = :subject,
            Level = :level,
            Title = :title,
            Content = :content,
            Answer = :answer,
            Difficulty = :difficulty,
            AnswerType = :answer_type,
            XP_Points = :xp_points,
            is_active = :is_active,
            Tips = :tips,
            Domain = :domain,
            Competence = :competence,
            Choices = :choices,
            Instruction = :instruction
        WHERE Identifier = :identifier
    ");
}

// 5. IMPORT
echo "📥 Import en cours...\n";

foreach ($exercises as $index => $ex) {
    $identifier = $ex['Identifier'] ?? "UNKNOWN-$index";

    try {
        if (is_null($ex['Answer'])) {
            $stats['null_answers']++;
        }

        $params = [
            'identifier' => $identifier,
            'subject' => $ex['Subject'] ?? null,
            'level' => $ex['Level'] ?? null,
            'title' => $ex['Title'] ?? null,
            'content' => $ex['Content'] ?? null,
            'answer' => $ex['Answer'] ?? null,
            'difficulty' => $ex['Difficulty'] ?? null,
            'answer_type' => $ex['AnswerType'] ?? null,
            'xp_points' => $ex['XP_Points'] ?? 10,
            'is_active' => $ex['is_active'] ?? 1,
            'tips' => $ex['Tips'] ?? null,
            'domain' => $ex['Domain'] ?? null,
            'competence' => $ex['Competence'] ?? null,
            'choices' => $ex['Choices'] ?? null,
            'instruction' => $ex['Instruction'] ?? null,
        ];

        if (!$dryRun) {
            if ($truncate) {
                // Mode TRUNCATE : toujours INSERT
                $insertStmt->execute($params);
                $stats['inserted']++;
                $action = 'INSERT';
            } else {
                // Mode UPDATE : vérifier existence
                $checkStmt->execute(['identifier' => $identifier]);
                $exists = $checkStmt->fetch();

                if ($exists) {
                    $updateStmt->execute($params);
                    $stats['updated']++;
                    $action = 'UPDATE';
                } else {
                    $insertStmt->execute($params);
                    $stats['inserted']++;
                    $action = 'INSERT';
                }
            }
        } else {
            // Dry-run
            if ($truncate) {
                $stats['inserted']++;
                $action = 'INSERT';
            } else {
                $checkStmt->execute(['identifier' => $identifier]);
                $exists = $checkStmt->fetch();

                if ($exists) {
                    $stats['updated']++;
                    $action = 'UPDATE';
                } else {
                    $stats['inserted']++;
                    $action = 'INSERT';
                }
            }
        }

        if (count($actions) < 50) {
            $actions[] = [
                'identifier' => $identifier,
                'action' => $action,
                'title' => substr($ex['Title'] ?? '', 0, 60),
                'has_answer' => !is_null($ex['Answer']),
            ];
        }

    } catch (Exception $e) {
        $stats['errors']++;
        $errors[] = [
            'identifier' => $identifier,
            'error' => $e->getMessage(),
        ];

        $logMsg = "[" . date('Y-m-d H:i:s') . "] ERREUR: $identifier - " . $e->getMessage() . "\n";
        file_put_contents(LOG_FILE, $logMsg, FILE_APPEND);
    }

    if (($index + 1) % 100 === 0) {
        echo "   Traité: " . ($index + 1) . "/$total\n";
    }
}

echo "\n";

// 6. RAPPORT
echo "📊 Génération du rapport...\n";

$reportData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'mode' => $dryRun ? 'DRY-RUN' : ($truncate ? 'TRUNCATE' : 'UPDATE'),
    'input_file' => basename(INPUT_FILE),
    'statistics' => $stats,
    'actions_sample' => $actions,
    'errors' => $errors,
];

file_put_contents(
    REPORT_DIR . '/import_cleaned_report.json',
    json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

$txt = "═══════════════════════════════════════════════════════════════\n";
$txt .= "  RAPPORT D'IMPORT EXERCICES NETTOYÉS\n";
$txt .= "═══════════════════════════════════════════════════════════════\n\n";
$txt .= "Date : " . date('Y-m-d H:i:s') . "\n";
$txt .= "Mode : " . ($dryRun ? 'DRY-RUN' : ($truncate ? 'TRUNCATE' : 'UPDATE')) . "\n";
$txt .= "Fichier : " . basename(INPUT_FILE) . "\n\n";

$txt .= "STATISTIQUES\n";
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("Total actions:           %4d\n", $stats['total']);
$txt .= sprintf("Insérés:                 %4d\n", $stats['inserted']);
$txt .= sprintf("Mis à jour:              %4d\n", $stats['updated']);
$txt .= sprintf("Erreurs:                 %4d\n", $stats['errors']);
$txt .= "───────────────────────────────────────────────────────────────\n";
$txt .= sprintf("⚠️ Exercices sans réponse: %4d (%.1f%%)\n\n",
    $stats['null_answers'],
    ($stats['null_answers'] / $stats['total']) * 100
);

if (count($actions) > 0) {
    $txt .= "ACTIONS (50 premiers)\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($actions as $i => $action) {
        $answerIcon = $action['has_answer'] ? '✅' : '⚠️';
        $txt .= sprintf("%3d. [%s] %s %s - %s\n",
            $i + 1,
            $action['action'],
            $answerIcon,
            $action['identifier'],
            $action['title']
        );
    }
    $txt .= "\n";
}

if (count($errors) > 0) {
    $txt .= "ERREURS\n";
    $txt .= "───────────────────────────────────────────────────────────────\n";
    foreach ($errors as $error) {
        $txt .= $error['identifier'] . " : " . $error['error'] . "\n";
    }
    $txt .= "\n";
}

file_put_contents(REPORT_DIR . '/import_cleaned_report.txt', $txt);

// 7. RÉSUMÉ
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ " . ($dryRun ? 'SIMULATION' : 'IMPORT') . " TERMINÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total actions:           %4d\n", $stats['total']);
echo sprintf("Insérés:                 %4d\n", $stats['inserted']);
echo sprintf("Mis à jour:              %4d\n", $stats['updated']);
echo sprintf("Erreurs:                 %4d\n", $stats['errors']);
echo "───────────────────────────────────────────────────────────────\n";
echo sprintf("⚠️  Exercices sans réponse: %4d (%.1f%%)\n",
    $stats['null_answers'],
    ($stats['null_answers'] / $stats['total']) * 100
);
echo "═══════════════════════════════════════════════════════════════\n\n";

if ($dryRun) {
    echo "📋 Pour l'import réel:\n";
    echo "   php " . basename(__FILE__) . ($truncate ? ' --truncate' : '') . "\n\n";
} else {
    echo "📄 Rapports: " . REPORT_DIR . "/import_cleaned_report.txt\n\n";

    if ($stats['null_answers'] > 0) {
        echo "⚠️  ATTENTION : " . $stats['null_answers'] . " exercices sans réponse.\n";
        echo "   Pour les désactiver :\n";
        echo "   UPDATE exercises SET is_active = 0 WHERE Answer IS NULL;\n\n";
    }
}
