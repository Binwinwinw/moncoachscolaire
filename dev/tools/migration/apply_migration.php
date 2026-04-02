<?php
/**
 * Script d'application de la migration SQL
 * 
 * Usage:
 *   php tools/apply_migration.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

if (!$pdo) {
    die("❌ Erreur de connexion à la base de données.\n");
}

echo "🔧 Application de la migration Courses...\n";
echo str_repeat("=", 60) . "\n\n";

// Lire le fichier migration MySQL
$migrationFile = __DIR__ . '/../db/migration_courses_mysql.sql';

if (!file_exists($migrationFile)) {
    die("❌ Fichier migration non trouvé: $migrationFile\n");
}

$migrationSQL = file_get_contents($migrationFile);

// Exécuter la migration
try {
    // Pour MySQL, il faut exécuter les commandes une par une
    $statements = array_filter(array_map('trim', explode(';', $migrationSQL)));
    
    $count = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', trim($statement))) {
            try {
                echo "Exécution [" . ($count+1) . "/" . count($statements) . "]...\n";
                $pdo->exec($statement);
                echo "✓ OK\n";
                $count++;
            } catch (PDOException $e) {
                // Ignorer les erreurs de "already exists" (table, index, etc.)
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "⊘ Déjà existant\n";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    echo "\n✅ Migration appliquée avec succès! ($count tables/index créés)\n";
} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration: " . $e->getMessage() . "\n";
    die(1);
}

echo "\n✅ Migration terminée.\n";
