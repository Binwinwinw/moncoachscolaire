<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

if (!$pdo) die("Pas de connexion\n");

echo "Vérification tables Courses...\n";

try {
    $result = $pdo->query("SHOW TABLES LIKE 'Courses'")->fetch();
    if ($result) {
        echo "✓ Table Courses existe\n";
    } else {
        echo "❌ Table Courses n'existe pas\n";
        echo "Création en cours...\n";
        
        // Lire et exécuter la migration
        $sql = file_get_contents(__DIR__ . '/../db/migration_courses_mysql.sql');
        $statements = explode(';', $sql);
        
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if (!empty($stmt) && strpos($stmt, '--') !== 0) {
                try {
                    $pdo->exec($stmt);
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "Erreur: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        echo "✓ Tables créées\n";
    }
    
    // Lister toutes les nouvelles tables
    $allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $courseTables = array_filter($allTables, function($t) {
        return stripos($t, 'course') !== false || stripos($t, 'exercise') !== false && stripos($t, 'link') !== false;
    });
    
    echo "\nTables liées aux cours:\n";
    foreach ($courseTables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "  - $table: $count lignes\n";
    }
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
