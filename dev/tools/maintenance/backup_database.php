<?php
/**
 * Script de sauvegarde de la base de données MonCoachScolaire
 * Usage: php tools/backup_database.php
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

// Connexion PDO pour la sauvegarde
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Erreur de connexion: " . $e->getMessage() . "\n");
}

// Créer le dossier de sauvegarde s'il n'existe pas
$backupDir = __DIR__ . '/../db/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Nom du fichier de sauvegarde avec timestamp
$timestamp = date('Ymd_His');
$backupFile = $backupDir . '/backup_' . $dbName . '_' . $timestamp . '.sql';

echo "📦 Sauvegarde de la base de données en cours...\n";
echo "Base: $dbName\n";
echo "Fichier: $backupFile\n\n";

try {
    // Récupérer toutes les tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        throw new Exception("Aucune table trouvée dans la base de données");
    }
    
    $backupContent = "-- Backup de la base de données $dbName\n";
    $backupContent .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
    $backupContent .= "-- Généré par tools/backup_database.php\n\n";
    $backupContent .= "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    $backupContent .= "USE `$dbName`;\n\n";
    $backupContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
    
    $totalRows = 0;
    foreach ($tables as $table) {
        try {
            // Structure de la table
            $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
            if ($createTable && isset($createTable['Create Table'])) {
                $backupContent .= "-- Structure de la table `$table`\n";
                $backupContent .= "DROP TABLE IF EXISTS `$table`;\n";
                $backupContent .= $createTable['Create Table'] . ";\n\n";
            }
            
            // Données de la table
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $backupContent .= "-- Données de la table `$table` (" . count($rows) . " ligne(s))\n";
                $columns = array_keys($rows[0]);
                $backupContent .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } elseif (is_numeric($value)) {
                            $rowValues[] = $value;
                        } else {
                            $rowValues[] = $pdo->quote($value);
                        }
                    }
                    $values[] = "(" . implode(', ', $rowValues) . ")";
                    $totalRows++;
                }
                
                $backupContent .= implode(",\n", $values) . ";\n\n";
            }
        } catch (PDOException $e) {
            echo "⚠️  Attention: Erreur lors de la sauvegarde de la table `$table`: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
    $backupContent .= "SET FOREIGN_KEY_CHECKS=1;\n";
    
    file_put_contents($backupFile, $backupContent);
    $fileSize = round(filesize($backupFile) / 1024, 2);
    
    echo "✅ Sauvegarde réussie !\n";
    echo "📁 Fichier: $backupFile\n";
    echo "📊 Taille: {$fileSize} KB\n";
    echo "📋 Tables sauvegardées: " . count($tables) . "\n";
    echo "📝 Lignes de données: $totalRows\n";
    
    // Lister les sauvegardes existantes
    echo "\n📋 Sauvegardes disponibles:\n";
    $backups = glob($backupDir . '/backup_*.sql');
    if (count($backups) > 0) {
        rsort($backups); // Plus récentes en premier
        foreach (array_slice($backups, 0, 10) as $backup) {
            $size = round(filesize($backup) / 1024, 2);
            $date = date('Y-m-d H:i:s', filemtime($backup));
            echo "  - " . basename($backup) . " ({$size} KB, $date)\n";
        }
        if (count($backups) > 10) {
            echo "  ... et " . (count($backups) - 10) . " autre(s)\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la sauvegarde: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Terminé !\n";
?>
