<?php
/**
 * Script de restauration FORCÉE de la base de données
 * Supprime tout et restaure depuis mysql_schema.sql
 * Usage: php tools/force_restore.php
 */

// Paramètres de connexion XAMPP par défaut
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'moncoachscolaire';

// Essayer de charger depuis les variables d'environnement
$envHost = getenv('DB_HOST');
$envUser = getenv('DB_USERNAME');
$envPass = getenv('DB_PASSWORD');
$envDb = getenv('DB_DATABASE');

if ($envHost) $dbHost = $envHost;
if ($envUser) $dbUser = $envUser;
if ($envPass !== false) $dbPass = $envPass;
if ($envDb) $dbName = $envDb;

$schemaFile = __DIR__ . '/../db/mysql_schema.sql';

if (!file_exists($schemaFile)) {
    die("❌ Fichier de schéma introuvable: $schemaFile\n");
}

echo "🚨 RESTAURATION FORCÉE DE LA BASE DE DONNÉES\n";
echo "=============================================\n\n";
echo "⚠️  Cette opération va SUPPRIMER toutes les données existantes !\n";
echo "Base: $dbName\n";
echo "Schéma: $schemaFile\n\n";

// Attendre confirmation (mode non-interactif pour l'instant)
echo "📥 Démarrage de la restauration...\n\n";

try {
    // Se connecter SANS la base pour pouvoir la supprimer
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Étape 1: Supprimer toutes les tables si elles existent
    echo "📋 Étape 1: Vérification des tables existantes...\n";
    try {
        $pdo->exec("USE `$dbName`");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($tables)) {
            echo "   Suppression de " . count($tables) . " table(s)...\n";
            foreach ($tables as $table) {
                try {
                    $pdo->exec("DROP TABLE IF EXISTS `$table`");
                } catch (PDOException $e) {
                    // Ignorer les erreurs
                }
            }
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    } catch (PDOException $e) {
        // La base n'existe peut-être pas encore, c'est normal
    }
    
    // Étape 2: Supprimer et recréer la base
    echo "🗑️  Étape 2: Suppression de la base de données...\n";
    try {
        $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
        echo "   ✓ Base supprimée\n";
    } catch (PDOException $e) {
        echo "   ⚠️  Impossible de supprimer (peut être normal): " . $e->getMessage() . "\n";
        echo "   ℹ️  Continuons quand même...\n";
    }
    
    echo "📦 Étape 3: Création de la base de données...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Base créée\n";
    
    // Étape 4: Se connecter à la nouvelle base
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Étape 5: Lire et exécuter le schéma
    echo "📥 Étape 4: Importation du schéma SQL...\n";
    $sql = file_get_contents($schemaFile);
    
    // Exécuter le script SQL en le divisant en requêtes
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    
    // Nettoyer le SQL : supprimer les commentaires et lignes vides, puis diviser par point-virgule
    $lines = explode("\n", $sql);
    $cleanSql = '';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        // Ignorer les lignes de commentaire qui sont seules
        if (!empty($trimmed) && !preg_match('/^--/', $trimmed)) {
            $cleanSql .= $line . "\n";
        }
    }
    
    // Diviser en requêtes
    $statements = [];
    $current = '';
    foreach (explode(';', $cleanSql) as $part) {
        $part = trim($part);
        if (!empty($part)) {
            $statements[] = $part . ';';
        }
    }
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strlen($statement) < 5) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Ignorer certaines erreurs bénignes
            $msg = $e->getMessage();
            if (strpos($msg, 'already exists') === false && 
                strpos($msg, 'Unknown database') === false &&
                strpos($msg, 'doesn\'t exist') === false) {
                $errors++;
                echo "   ⚠️  " . substr($statement, 0, 60) . "... - " . $msg . "\n";
            }
        }
    }
    
    echo "   ✓ Requêtes exécutées: $executed\n";
    if ($errors > 0) {
        echo "   ⚠️  Erreurs ignorées: $errors\n";
    }
    
    // Étape 6: Vérification
    echo "\n✅ Étape 5: Vérification...\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "   ⚠️  Aucune table créée ! Problème lors de la restauration.\n";
        exit(1);
    }
    
    echo "   ✓ Tables créées: " . count($tables) . "\n";
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "      - `$table`: $count ligne(s)\n";
        } catch (PDOException $e) {
            echo "      - `$table`: (structure créée)\n";
        }
    }
    
    echo "\n✅ RESTAURATION RÉUSSIE !\n";
    echo "La base de données a été restaurée depuis $schemaFile\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERREUR lors de la restauration: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Terminé !\n";
?>

