<?php
/**
 * Script pour nettoyer les tablespaces orphelins
 * Crée temporairement les tables puis les supprime pour nettoyer les fichiers .ibd
 */

// Paramètres de connexion XAMPP par défaut
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'moncoachscolaire';

$envHost = getenv('DB_HOST');
$envUser = getenv('DB_USERNAME');
$envPass = getenv('DB_PASSWORD');
$envDb = getenv('DB_DATABASE');

if ($envHost) $dbHost = $envHost;
if ($envUser) $dbUser = $envUser;
if ($envPass !== false) $dbPass = $envPass;
if ($envDb) $dbName = $envDb;

echo "🧹 Nettoyage des tablespaces orphelins...\n";
echo "Base: $dbName\n\n";

try {
    // Se connecter à la base
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Liste des tables qui ont probablement des tablespaces orphelins
    $tables = [
        'users',
        'userprogress', 
        'powers',
        'userpowers',
        'achievements',
        'userachievements',
        'exercises',
        'exerciseresponses'
    ];
    
    echo "📋 Nettoyage des tablespaces...\n";
    
    // Désactiver les contraintes
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    foreach ($tables as $table) {
        try {
            // Essayer de créer la table temporairement (cela va "rattacher" le tablespace s'il existe)
            $pdo->exec("CREATE TABLE IF NOT EXISTS `$table` (id INT PRIMARY KEY) ENGINE=InnoDB");
            echo "   ✓ Table `$table` créée temporairement\n";
            
            // Maintenant on peut la supprimer proprement
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
            echo "   ✓ Table `$table` supprimée (tablespace nettoyé)\n";
        } catch (PDOException $e) {
            // Si erreur "Tablespace exists", essayer de créer puis supprimer autrement
            if (strpos($e->getMessage(), 'Tablespace') !== false) {
                try {
                    // Essayer de créer avec la même structure que prévu
                    echo "   ⚠️  Tentative alternative pour `$table`...\n";
                    // Créer avec une structure minimale
                    $pdo->exec("CREATE TABLE `$table` (id INT PRIMARY KEY) ENGINE=InnoDB");
                    $pdo->exec("ALTER TABLE `$table` DISCARD TABLESPACE");
                    $pdo->exec("DROP TABLE `$table`");
                    echo "   ✓ Table `$table` nettoyée\n";
                } catch (PDOException $e2) {
                    echo "   ❌ Impossible de nettoyer `$table`: " . $e2->getMessage() . "\n";
                }
            } else {
                echo "   ⚠️  `$table`: " . $e->getMessage() . "\n";
            }
        }
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    
    echo "\n✅ Nettoyage terminé !\n";
    echo "Vous pouvez maintenant restaurer avec:\n";
    echo "  php tools/restore_from_schema.php mysql_schema.sql\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Terminé !\n";
?>

