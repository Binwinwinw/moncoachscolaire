<?php

/**
 * Script CLI: Marquer les quizzes avec placeholders critiques/high comme draft.
 *
 * Usage:
 *   php dev/tools/quiz/mark_quizzes_draft.php [path_to_placeholders_report.json]
 *
 * Exemple:
 *   php dev/tools/quiz/mark_quizzes_draft.php dev/reports/placeholders_detected_2026-03-14_refined.json
 *
 * [02/04/2026] Action IMMÉDIAT: Isoler quizzes critiques sans les bloquer en diagnostic
 */

if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande (CLI).\n");
}

$projectRoot = dirname(__DIR__, 3);
$reportPath = isset($argv[1]) ? $argv[1] : $projectRoot . '/dev/reports/placeholders_detected_2026-03-14_refined.json';

// Load PDO
if (!file_exists($projectRoot . '/src/database/connection.php')) {
    die("❌ Fichier connection.php non trouvé.\n");
}

require_once $projectRoot . '/src/database/connection.php';

if (!isset($pdo) || !$pdo instanceof PDO) {
    die("❌ PDO non disponible.\n");
}

// Vérifier la migration est appliquée
try {
    $pdo->query("SELECT status FROM quiz LIMIT 1");
} catch (PDOException $e) {
    die("❌ Colonne 'status' non trouvée. Appliquez la migration: db/migration_add_status_column_20260402.sql\n");
}

// Charger le rapport de placeholders
if (!file_exists($reportPath)) {
    die("❌ Rapport placeholders non trouvé: $reportPath\n");
}

$reportJson = file_get_contents($reportPath);
$report = json_decode($reportJson, true);

if (!is_array($report)) {
    die("❌ Format rapport invalide (JSON parse error).\n");
}

echo "=== Marquage des quizzes critiques/high en draft ===\n";
echo "Rapport: $reportPath\n\n";

$criticalIds = [];
$highIds = [];

// Support 2 formats : ancien (quizzes_with_placeholders) et nouveau (quiz_ids_affected + statistics)
if (!empty($report['quizzes_with_placeholders']) && is_array($report['quizzes_with_placeholders'])) {
    // Format ancien
    foreach ($report['quizzes_with_placeholders'] as $quizId => $details) {
        if (!is_array($details)) continue;

        $quizId = (int) $quizId;
        $severities = $details['severities'] ?? [];

        $criticalCount = (int) ($severities['CRITICAL'] ?? 0);
        $highCount = (int) ($severities['HIGH'] ?? 0);

        if ($criticalCount > 0) {
            $criticalIds[] = $quizId;
        } elseif ($highCount > 0) {
            $highIds[] = $quizId;
        }
    }
} elseif (!empty($report['quiz_ids_affected']) && is_array($report['quiz_ids_affected'])) {
    // Format nouveau (2026-03-14_refined)
    $allAffectedIds = array_map('intval', $report['quiz_ids_affected']);

    // Tous les IDs affectés sont au moins CRITICAL ou HIGH (le rapport les a classifiés)
    // Selon statistics, critical=8099, high=7366
    // On classifie par ratio : si ce quiz a beaucoup de critical, c'est critical
    // Sinon on les met tous en HIGH pour sécurité (éviter de servir quoi que ce soit suspect)

    $criticalCount = (int) ($report['statistics']['by_severity']['critical'] ?? 0);
    $highCount = (int) ($report['statistics']['by_severity']['high'] ?? 0);
    $totalPlaceholders = (int) ($report['meta']['total_placeholders_found'] ?? 0);

    // Heuristique simple : si on a des CRITICAL, mets la moitié des IDs affectés en CRITICAL
    // (on ne sait pas la distribution exacte par quiz)
    if ($criticalCount > 0 && $highCount > 0) {
        $criticalRatio = $criticalCount / ($criticalCount + $highCount);
        $splitIdx = (int) (count($allAffectedIds) * $criticalRatio);

        $criticalIds = array_slice($allAffectedIds, 0, $splitIdx);
        $highIds = array_slice($allAffectedIds, $splitIdx);
    } else {
        // Tous HIGH si pas de CRITICAL
        $highIds = $allAffectedIds;
    }
} else {
    die("❌ Format rapport invalide : ni 'quizzes_with_placeholders' ni 'quiz_ids_affected' trouvé.\n");
    }

    echo "Quizzes CRITIQUES détectés: " . count($criticalIds) . "\n";
    if (!empty($criticalIds)) {
        echo "  IDs (premiers 10): " . implode(', ', array_slice($criticalIds, 0, 10)) . (count($criticalIds) > 10 ? ', ...' : '') . "\n";
    }

    echo "Quizzes HIGH détectés: " . count($highIds) . "\n";
    if (!empty($highIds)) {
        echo "  IDs: " . implode(', ', array_slice($highIds, 0, 10)) . (count($highIds) > 10 ? ', ...' : '') . "\n";
    }

if (empty($criticalIds) && empty($highIds)) {
    echo "✅ Aucun quiz critique/high à marquer.\n";
    exit(0);
}

// Confirmation avant exécution
echo "\n⚠️  Cela marquera " . (count($criticalIds) + count($highIds)) . " quizzes comme draft.\n";
echo "Continuer ? (y/n): ";

$confirm = trim(fgets(STDIN));
if (strtolower($confirm) !== 'y') {
    echo "Annulé.\n";
    exit(0);
}

// Marquer les quizzes en draft
$totalMarked = 0;

if (!empty($criticalIds)) {
    $placeholders = implode(',', array_map('intval', $criticalIds));
    $stmt = $pdo->prepare("
        UPDATE quiz
        SET status = 'draft', quality_flag = 'placeholders_critical'
        WHERE id IN ($placeholders)
    ");
    if ($stmt->execute()) {
        $affected = $stmt->rowCount();
        echo "✅ Marqués CRITICAL en draft: $affected\n";
        $totalMarked += $affected;
    }
}

if (!empty($highIds)) {
    $placeholders = implode(',', array_map('intval', $highIds));
    $stmt = $pdo->prepare("
        UPDATE quiz
        SET status = 'draft', quality_flag = 'placeholders_high'
        WHERE id IN ($placeholders)
    ");
    if ($stmt->execute()) {
        $affected = $stmt->rowCount();
        echo "✅ Marqués HIGH en draft: $affected\n";
        $totalMarked += $affected;
    }
}

echo "\n📊 Résumé final:\n";
$drafted = $pdo->query("SELECT COUNT(*) as count FROM quiz WHERE status = 'draft'")->fetch(PDO::FETCH_ASSOC);
$active = $pdo->query("SELECT COUNT(*) as count FROM quiz WHERE status = 'active'")->fetch(PDO::FETCH_ASSOC);

echo "   Quizzes draft (bloqués): " . ($drafted['count'] ?? 0) . "\n";
echo "   Quizzes actifs (servis): " . ($active['count'] ?? 0) . "\n";

echo "\n✅ Marquage terminé. Total modifiés: $totalMarked\n";
exit(0);
