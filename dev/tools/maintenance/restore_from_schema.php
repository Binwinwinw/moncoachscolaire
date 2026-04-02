<?php
/**
 * Script de restauration de la base de données depuis le schéma SQL
 * Usage: php tools/restore_from_schema.php [schema_file.sql]
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

// Fichier de schéma par défaut
$schemaFile = isset($argv[1]) ? __DIR__ . '/../db/' . $argv[1] : __DIR__ . '/../db/mysql_schema.sql';

if (!file_exists($schemaFile)) {
    die("❌ Fichier de schéma introuvable: $schemaFile\n");
}

echo "🔄 Restauration de la base de données depuis le schéma...\n";
echo "Base: $dbName\n";
echo "Fichier: $schemaFile\n\n";

// Connexion PDO
try {
    // D'abord se connecter sans spécifier la base
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lire le contenu du fichier SQL
    $sql = file_get_contents($schemaFile);
    if ($sql === false) {
        throw new Exception("Impossible de lire le fichier de schéma");
    }
    
    echo "📥 Exécution du script SQL...\n";
    
    // Exécuter le script SQL directement
    // MySQL peut exécuter plusieurs requêtes séparées par des points-virgules
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    
    // Diviser le SQL en requêtes individuelles en préservant les CREATE DATABASE et USE
    $statements = [];
    $currentStatement = '';
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Ignorer les commentaires vides
        if (empty($trimmed) || preg_match('/^--/', $trimmed)) {
            continue;
        }
        
        $currentStatement .= $line . "\n";
        
        // Si la ligne se termine par un point-virgule, c'est la fin d'une requête
        if (substr(rtrim($line), -1) === ';') {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    // Ajouter la dernière requête si elle n'a pas de point-virgule
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Ignorer certaines erreurs bénignes
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, "already exists") === false && 
                strpos($errorMsg, "Unknown database") === false) {
                $errors++;
                echo "⚠️  Erreur: " . substr($statement, 0, 80) . "...\n";
                echo "   " . $errorMsg . "\n";
            }
        }
    }
    
    echo "✅ Restauration terminée !\n";
    echo "📝 Requêtes exécutées: $executed\n";
    if ($errors > 0) {
        echo "⚠️  Erreurs ignorées: $errors\n";
    }
    
    // Se connecter à la base restaurée pour vérifier
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier les tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tables créées: " . count($tables) . "\n";
    if (!empty($tables)) {
        foreach ($tables as $table) {
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                echo "   - $table: $count ligne(s)\n";
            } catch (PDOException $e) {
                echo "   - $table: (structure créée)\n";
            }
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

