<?php
/**
 * Script de synchronisation automatique Local → Production
 * 
 * Ce script se connecte à la base de données de production (via .env.production)
 * et exécute les modifications nécessaires pour synchroniser la structure
 * 
 * Usage: php tools/sync_to_production.php
 * 
 * IMPORTANT: Assurez-vous que .env.production contient les bonnes credentials
 */

// Charger les variables d'environnement de production
$envProdPath = __DIR__ . '/../.env.production';
if (file_exists($envProdPath)) {
    $lines = file($envProdPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Supprimer les guillemets
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }
            // Gérer PROD_ prefix
            if (strpos($key, 'PROD_') === 0) {
                $key = substr($key, 5);
            }
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Récupérer les credentials de production
$dbHost = getenv('DB_HOST') ?: getenv('PROD_DB_HOST') ?: 'localhost';
$dbName = getenv('DB_DATABASE') ?: getenv('PROD_DB_DATABASE') ?: 'moncoachscolaire';
$dbUser = getenv('DB_USERNAME') ?: getenv('PROD_DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: getenv('PROD_DB_PASSWORD') ?: '';

echo "🔧 Script de synchronisation Local → Production\n";
echo "================================================\n\n";

// Demander confirmation
echo "⚠️  ATTENTION : Vous allez modifier la base de données de PRODUCTION\n";
echo "Base de données : $dbName\n";
echo "Hôte : $dbHost\n";
echo "Utilisateur : $dbUser\n\n";

echo "Voulez-vous continuer ? (oui/non) : ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirmation = trim(strtolower($line));
fclose($handle);

if ($confirmation !== 'oui' && $confirmation !== 'o' && $confirmation !== 'yes' && $confirmation !== 'y') {
    echo "❌ Opération annulée.\n";
    exit(0);
}

echo "\n🔌 Connexion à la base de données de production...\n";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion réussie\n\n";
} catch (PDOException $e) {
    die("❌ Erreur de connexion : " . $e->getMessage() . "\n");
}

// Lire le script SQL
$sqlScriptPath = __DIR__ . '/../db/sync_local_to_prod.sql';
if (!file_exists($sqlScriptPath)) {
    die("❌ Fichier SQL non trouvé : $sqlScriptPath\n");
}

echo "📄 Lecture du script SQL...\n";
$sqlScript = file_get_contents($sqlScriptPath);

// Séparer les commandes SQL (séparées par ;)
// On doit être plus intelligent car il y a des procédures préparées
$statements = [];
$currentStatement = '';
$inPreparedStmt = false;

$lines = explode("\n", $sqlScript);
foreach ($lines as $line) {
    $line = trim($line);
    
    // Ignorer les commentaires et lignes vides
    if (empty($line) || strpos($line, '--') === 0) {
        continue;
    }
    
    // Détecter les blocs PREPARE/EXECUTE/DEALLOCATE
    if (preg_match('/^(SET|PREPARE|EXECUTE|DEALLOCATE)/i', $line)) {
        $currentStatement .= $line . "\n";
        if (preg_match('/DEALLOCATE/i', $line)) {
            $statements[] = $currentStatement;
            $currentStatement = '';
        }
        continue;
    }
    
    // Détecter les CREATE TABLE IF NOT EXISTS (une seule ligne ou multi-lignes)
    if (preg_match('/CREATE TABLE/i', $line)) {
        $currentStatement = $line;
        if (strpos($line, ';') !== false) {
            $statements[] = $currentStatement;
            $currentStatement = '';
        }
        continue;
    }
    
    // Continuer la construction de la commande
    if (!empty($currentStatement)) {
        $currentStatement .= " " . $line;
        if (strpos($line, ';') !== false) {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    } else {
        if (strpos($line, ';') !== false) {
            $statements[] = $line;
        }
    }
}

// Exécuter les commandes
echo "🚀 Exécution des modifications...\n\n";

$successCount = 0;
$errorCount = 0;
$skippedCount = 0;

foreach ($statements as $index => $stmt) {
    if (empty(trim($stmt))) continue;
    
    try {
        // Pour les SELECT (messages), on les exécute mais on ne compte pas comme succès
        if (preg_match('/^SELECT/i', trim($stmt))) {
            $result = $pdo->query($stmt);
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $message = reset($row);
                if (strpos($message, 'existe déjà') !== false || 
                    strpos($message, 'accepte déjà') !== false) {
                    echo "  ⏭️  $message\n";
                    $skippedCount++;
                } else {
                    echo "  ℹ️  $message\n";
                }
            }
            continue;
        }
        
        // Exécuter la commande
        $pdo->exec($stmt);
        $successCount++;
        echo "  ✅ Commande " . ($index + 1) . " exécutée\n";
        
    } catch (PDOException $e) {
        // Ignorer les erreurs "already exists" car c'est normal
        if (strpos($e->getMessage(), 'already exists') !== false ||
            strpos($e->getMessage(), 'Duplicate') !== false ||
            strpos($e->getMessage(), 'existe déjà') !== false) {
            echo "  ⏭️  Commande " . ($index + 1) . " ignorée (déjà existant)\n";
            $skippedCount++;
        } else {
            echo "  ❌ Erreur commande " . ($index + 1) . " : " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
}

// Résumé
echo "\n================================================\n";
echo "📊 Résumé de la synchronisation\n";
echo "================================================\n";
echo "✅ Commandes exécutées avec succès : $successCount\n";
echo "⏭️  Commandes ignorées (déjà existantes) : $skippedCount\n";
if ($errorCount > 0) {
    echo "❌ Erreurs : $errorCount\n";
}
echo "\n";

// Afficher la structure finale de Users
echo "📋 Structure finale de la table Users :\n";
try {
    $stmt = $pdo->query("
        SELECT 
            COLUMN_NAME,
            DATA_TYPE,
            IS_NULLABLE,
            COLUMN_DEFAULT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'Users'
        ORDER BY ORDINAL_POSITION
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    printf("%-15s %-20s %-10s %-15s\n", "Colonne", "Type", "Nullable", "Default");
    echo str_repeat("-", 60) . "\n";
    foreach ($columns as $col) {
        printf("%-15s %-20s %-10s %-15s\n", 
            $col['COLUMN_NAME'], 
            $col['DATA_TYPE'], 
            $col['IS_NULLABLE'], 
            $col['COLUMN_DEFAULT'] ?? 'NULL'
        );
    }
} catch (Exception $e) {
    echo "⚠️  Impossible d'afficher la structure : " . $e->getMessage() . "\n";
}

echo "\n✅ Synchronisation terminée !\n";
