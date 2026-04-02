<?php
/**
 * Script de restauration de la base de données MonCoachScolaire
 * Usage: php tools/restore_database.php [nom_du_fichier.sql]
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

// Dossier des sauvegardes
$backupDir = __DIR__ . '/../db/backups';

// Si --latest est spécifié, utiliser la plus récente
$backupFile = null;
if (isset($argv[1]) && $argv[1] === '--latest') {
    $backups = glob($backupDir . '/backup_*.sql');
    if (empty($backups)) {
        die("❌ Aucune sauvegarde trouvée\n");
    }
    rsort($backups);
    $backupFile = $backups[0];
    echo "📌 Utilisation de la sauvegarde la plus récente: " . basename($backupFile) . "\n\n";
} elseif (isset($argv[1])) {
    $backupFile = $backupDir . '/' . $argv[1];
    if (!file_exists($backupFile)) {
        die("❌ Fichier de sauvegarde introuvable: $backupFile\n");
    }
} else {
    // Lister les sauvegardes disponibles
    $backups = glob($backupDir . '/backup_*.sql');
    if (empty($backups)) {
        die("❌ Aucune sauvegarde trouvée dans $backupDir\n");
    }
    
    rsort($backups); // Plus récentes en premier
    echo "📋 Sauvegardes disponibles:\n\n";
    foreach ($backups as $idx => $backup) {
        $size = round(filesize($backup) / 1024, 2);
        $date = date('Y-m-d H:i:s', filemtime($backup));
        echo "  " . ($idx + 1) . ". " . basename($backup) . " ({$size} KB, $date)\n";
    }
    
    echo "\n⚠️  Aucun fichier spécifié. Utilisez:\n";
    echo "   php tools/restore_database.php " . basename($backups[0]) . "\n\n";
    echo "Ou pour restaurer la plus récente automatiquement:\n";
    echo "   php tools/restore_database.php --latest\n\n";
    exit(0);
}


echo "🔄 Restauration de la base de données...\n";
echo "Base: $dbName\n";
echo "Fichier: $backupFile\n\n";

// Connexion PDO
try {
    // D'abord se connecter sans spécifier la base pour pouvoir la créer/supprimer
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lire le contenu du fichier SQL
    $sql = file_get_contents($backupFile);
    if ($sql === false) {
        throw new Exception("Impossible de lire le fichier de sauvegarde");
    }
    
    // Supprimer la base de données existante si elle existe
    echo "🗑️  Suppression de la base de données existante (si elle existe)...\n";
    $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
    
    // Créer la base de données
    echo "📦 Création de la base de données...\n";
    $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");
    
    // Exécuter le script SQL
    echo "📥 Exécution du script SQL...\n";
    
    // Diviser le SQL en requêtes individuelles
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt) && strlen($stmt) > 5;
        }
    );
    
    $executed = 0;
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Ignorer certaines erreurs (comme DROP TABLE IF EXISTS sur une table inexistante)
            if (strpos($e->getMessage(), "doesn't exist") === false) {
                echo "⚠️  Erreur: " . substr($statement, 0, 50) . "... - " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "✅ Restauration réussie !\n";
    echo "📝 Requêtes exécutées: $executed\n";
    
    // Vérifier les tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tables restaurées: " . count($tables) . "\n";
    if (!empty($tables)) {
        foreach ($tables as $table) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "   - $table: $count ligne(s)\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la restauration: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Terminé !\n";
?>

