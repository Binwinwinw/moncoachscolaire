<?php
/**
 * Script de réparation de la base de données (supprime les tables corrompues)
 * Usage: php tools/fix_database.php
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

echo "🔧 Réparation de la base de données...\n";
echo "Base: $dbName\n\n";

try {
    // Se connecter SANS spécifier la base pour pouvoir la supprimer complètement
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🗑️  Suppression complète de la base de données...\n";
    // Supprimer toutes les tables d'abord
    try {
        $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
        echo "   ✓ Base de données supprimée\n";
    } catch (PDOException $e) {
        echo "   ⚠️  Impossible de supprimer la base: " . $e->getMessage() . "\n";
        echo "   ℹ️  Cela peut être normal si des fichiers sont verrouillés\n";
    }
    
    // Recréer la base de données
    echo "📦 Création de la base de données...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "   ✓ Base de données créée\n\n";
    
    // Maintenant se connecter à la base nouvellement créée
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    echo "\n✅ Réparation terminée !\n";
    echo "Vous pouvez maintenant restaurer la base avec:\n";
    echo "  php tools/restore_from_schema.php mysql_schema.sql\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✨ Terminé !\n";
?>

