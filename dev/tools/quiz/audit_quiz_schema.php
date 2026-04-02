<?php
/**
 * Script d'audit BDD MonCoachScolaire - 28/02/2026
 * Chemins : src/config/config.php et src/database/connection.php
 * Usage: php dev/tools/quiz/audit_quiz_schema.php (depuis RACINE)
 */

// Remonte à racine /moncoachscolaire depuis dev/tools/quiz/
$root = dirname(__DIR__, 3);  // dev/tools/quiz → racine
$config_path = $root . '/src/config/config.php';
$db_path = $root . '/src/database/connection.php';

echo "🔍 Chemins absolus:\n";
echo "  Root: $root\n";
echo "  Config: $config_path\n";
echo "  DB: $db_path\n\n";

require_once $config_path;
require_once $db_path;

echo "✅ Connexion BDD OK\n\n";

$tables = ['contents', 'quiz', 'exercisenotion', 'exerciseresponses', 'exercises'];

foreach ($tables as $table) {
    echo "📋 TABLE: `$table`\n" . str_repeat('-', 60) . "\n";

    // Structure
    try {
        $stmt = $pdo->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($columns)) {
            echo "  ❌ TABLE INEXISTANTE\n";
        } else {
            echo "Structure:\n";
            foreach ($columns as $col) {
                $null = $col['Null'] === 'NO' ? 'NOT NULL' : 'NULL';
                $key = $col['Key'] === 'PRI' ? 'PK' : ($col['Key'] ? $col['Key'] : '');
                printf("  %-25s %-12s %-8s %s\n", $col['Field'], substr($col['Type'], 0, 11), $null, $key);
            }
        }
    } catch (PDOException $e) {
        echo "  ❌ DESCRIBE KO\n";
    }

    // Count + exemple
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "  Lignes: $count\n";

        if (in_array($table, ['contents', 'quiz', 'exercises']) && $count > 0) {
            $stmt = $pdo->query("SELECT * FROM `$table` LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "  JSON exemple:\n" . json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    } catch (PDOException $e) {
        echo "  ❌ COUNT KO\n";
    }
    echo "\n";
}

// Mapping levels/subjects
echo "=== NIVEAUX & MATIÈRES DISPONIBLES ===\n";
try {
    $stmt = $pdo->query("
        SELECT DISTINCT `Level`, `Subject`, COUNT(*) as exercices
        FROM `exercises`
        WHERE `is_active` = 1
        GROUP BY `Level`, `Subject`
        ORDER BY FIELD(`Level`, '6eme','5eme','4eme','3eme','2nde','1ere','terminale'), `Subject`
    ");
    echo str_repeat('=', 50) . "\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("  %-8s %-20s (%d ex.)\n", $row['Level'], $row['Subject'], $row['exercices']);
    }
} catch (PDOException $e) {
    echo "❌ MAPPING KO\n";
}

echo "\n✅ AUDIT TERMINÉ - Copie tout pour Perplexity !\n";
?>
