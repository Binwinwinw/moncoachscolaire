<?php
/**
 * Script de migration des données de la base locale vers la base de production
 * Inspiré de l'approche getEnvironmentConfig() pour la gestion des environnements
 * 
 * Usage: php tools/migrate_to_production_env.php
 * 
 * Ce script :
 * 1. Charge la config locale depuis .env ou variables d'environnement
 * 2. Charge la config production depuis .env.production ou variables d'environnement
 * 3. Exporte les données de la base locale
 * 4. Importe les données dans la base de production
 */

// Fonction helper pour charger le .env
function loadEnvFile($path = null) {
    if ($path === null) {
        $path = __DIR__ . '/../.env';
    }
    
    if (!file_exists($path)) {
        return false;
    }
    
    // Charger via Dotenv si disponible
    $composerAutoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($composerAutoload)) {
        require_once $composerAutoload;
        if (class_exists('Dotenv\Dotenv')) {
            try {
                $dotenv = Dotenv\Dotenv::createImmutable(dirname($path), basename($path));
                $dotenv->safeLoad();
                return true;
            } catch (Exception $e) {
                // Ignorer les erreurs silencieusement
            }
        }
    }
    
    // Fallback: parser manuellement le .env
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0) continue; // Commentaire
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Enlever les guillemets si présents
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
    
    return true;
}

// Fonction helper pour lire les variables d'environnement
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
    return $value;
}

// Fonction pour détecter l'environnement
function detectEnvironment() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        return 'local';
    }
    return 'production';
}

echo "🚀 Migration des données vers la production\n";
echo "==========================================\n\n";

// ============================================
// CONFIGURATION BASE LOCALE
// ============================================
echo "📦 Configuration base locale:\n";

// Charger le .env local
loadEnvFile(__DIR__ . '/../.env');

// Si APP_ENV n'est pas défini, détecter automatiquement
$localEnv = env('APP_ENV') ?: detectEnvironment();

if ($localEnv === 'local') {
    $localHost = env('DB_HOST', '127.0.0.1');
    $localUser = env('DB_USERNAME', 'root');
    $localPass = env('DB_PASSWORD', '');
    $localDb = env('DB_DATABASE', 'moncoachscolaire');
} else {
    // Si on est déjà en production, utiliser les valeurs de production
    $localHost = env('DB_HOST', 'localhost');
    $localUser = env('DB_USERNAME', 'root');
    $localPass = env('DB_PASSWORD', '');
    $localDb = env('DB_DATABASE', 'moncoachscolaire');
}

echo "   Host: $localHost\n";
echo "   User: $localUser\n";
echo "   Database: $localDb\n";
echo "   Environment: $localEnv\n\n";

// ============================================
// CONFIGURATION BASE PRODUCTION
// ============================================
echo "🌐 Configuration base de production:\n";

$prodEnvFile = __DIR__ . '/../.env.production';
$prodHost = null;
$prodUser = null;
$prodPass = null;
$prodDb = null;

// Essayer de charger .env.production d'abord
if (file_exists($prodEnvFile)) {
    echo "   📄 Chargement depuis .env.production\n";
    loadEnvFile($prodEnvFile);
    $prodHost = env('PROD_DB_HOST');
    $prodUser = env('PROD_DB_USERNAME');
    $prodPass = env('PROD_DB_PASSWORD');
    $prodDb = env('PROD_DB_DATABASE');
}

// Si .env.production n'existe pas ou est incomplet, utiliser les valeurs du .env actuel
if (empty($prodUser) || empty($prodDb)) {
    echo "   📄 .env.production non trouvé ou incomplet\n";
    echo "   🔍 Détection depuis le .env actuel...\n\n";
    
    // Lire les valeurs depuis le .env actuel (qui devrait contenir les infos de production)
    $prodHost = env('DB_HOST', 'localhost');
    $prodUser = env('DB_USERNAME');
    $prodPass = env('DB_PASSWORD');
    $prodDb = env('DB_DATABASE');
    
    // Si toujours vide, demander interactivement
    if (empty($prodUser) || empty($prodDb)) {
        echo "   ⚠️  Configuration production non trouvée dans .env\n";
        echo "   (Les informations seront demandées ci-dessous)\n\n";
        
        $prodHost = readline("   Host (ex: localhost): ") ?: 'localhost';
        $prodUser = readline("   Username: ");
        $prodPass = readline("   Password: ");
        $prodDb = readline("   Database name: ");
        
        if (empty($prodUser) || empty($prodDb)) {
            die("❌ Configuration incomplète. Migration annulée.\n");
        }
    } else {
        // Créer automatiquement le .env.production avec les valeurs du .env actuel
        echo "   💾 Création du fichier .env.production avec les valeurs du .env actuel...\n";
        
        // Si le nom de la base est "moncoachscolaire", le remplacer par la version production
        if ($prodDb === 'moncoachscolaire' && strpos($prodUser, 'u936396612_') === 0) {
            // Détecter le nom de base de production depuis le username
            $prodDb = 'u936396612_mcoachscolaire';
            echo "   🔄 Correction du nom de base: moncoachscolaire → $prodDb\n";
        }
        
        $envProductionContent = "# Configuration pour la base de données de production\n";
        $envProductionContent .= "# Généré automatiquement par tools/migrate_to_production_env.php\n";
        $envProductionContent .= "# Date: " . date('Y-m-d H:i:s') . "\n\n";
        $envProductionContent .= "PROD_DB_HOST=$prodHost\n";
        $envProductionContent .= "PROD_DB_USERNAME=$prodUser\n";
        $envProductionContent .= "PROD_DB_PASSWORD=$prodPass\n";
        $envProductionContent .= "PROD_DB_DATABASE=$prodDb\n";
        
        file_put_contents($prodEnvFile, $envProductionContent);
        echo "   ✅ Fichier .env.production créé avec succès !\n";
        
        // Recharger le fichier pour que les variables soient disponibles
        loadEnvFile($prodEnvFile);
    }
} else {
    echo "   ✓ Configuration chargée depuis .env.production\n";
    // S'assurer que les variables sont bien chargées
    $prodHost = env('PROD_DB_HOST') ?: $prodHost;
    $prodUser = env('PROD_DB_USERNAME') ?: $prodUser;
    $prodPass = env('PROD_DB_PASSWORD') ?: $prodPass;
    $prodDb = env('PROD_DB_DATABASE') ?: $prodDb;
}

echo "\n📋 Configuration production:\n";
echo "   Host: $prodHost\n";
echo "   User: $prodUser\n";
echo "   Database: $prodDb\n\n";

// Tester la connexion à la production
echo "   🔍 Test de connexion à la base de production...\n";
try {
    $testPdo = new PDO("mysql:host=$prodHost;dbname=$prodDb;charset=utf8mb4", $prodUser, $prodPass);
    $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $testPdo->query("SELECT 1");
    echo "   ✅ Connexion réussie !\n\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "\n   💡 Vérifiez :\n";
    echo "      - Le mot de passe est correct\n";
    echo "      - L'utilisateur a les permissions nécessaires\n";
    echo "      - Le host est correct (essayez 'localhost' ou l'IP fournie par Hostinger)\n";
    echo "      - La base de données existe\n\n";
    
    // Suggestion: créer un .env.production
    echo "   💡 Astuce: Créez un fichier .env.production avec :\n";
    echo "      PROD_DB_HOST=localhost\n";
    echo "      PROD_DB_USERNAME=u936396612_moncoachadmin\n";
    echo "      PROD_DB_PASSWORD=votre_mot_de_passe\n";
    echo "      PROD_DB_DATABASE=u936396612_mcoachscolaire\n\n";
    
    die("❌ Migration annulée.\n");
}

// ============================================
// ÉTAPE 1: EXPORT DE LA BASE LOCALE
// ============================================
echo "📤 ÉTAPE 1: Export de la base locale...\n";

try {
    $localPdo = new PDO("mysql:host=$localHost;dbname=$localDb;charset=utf8mb4", $localUser, $localPass);
    $localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Erreur de connexion à la base locale: " . $e->getMessage() . "\n");
}

$tables = $localPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

if (empty($tables)) {
    die("❌ Aucune table trouvée dans la base locale\n");
}

echo "   ✓ " . count($tables) . " table(s) trouvée(s)\n";

$exportContent = "-- Export de la base de données $localDb\n";
$exportContent .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
$exportContent .= "-- Généré par tools/migrate_to_production_env.php\n\n";
$exportContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

$totalRows = 0;
foreach ($tables as $table) {
    try {
        $createTable = $localPdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        if ($createTable && isset($createTable['Create Table'])) {
            $exportContent .= "-- Structure de la table `$table`\n";
            $exportContent .= "DROP TABLE IF EXISTS `$table`;\n";
            $exportContent .= $createTable['Create Table'] . ";\n\n";
        }
        
        $rows = $localPdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $exportContent .= "-- Données de la table `$table` (" . count($rows) . " ligne(s))\n";
            $columns = array_keys($rows[0]);
            $exportContent .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } elseif (is_numeric($value)) {
                        $rowValues[] = $value;
                    } else {
                        $rowValues[] = $localPdo->quote($value);
                    }
                }
                $values[] = "(" . implode(', ', $rowValues) . ")";
                $totalRows++;
            }
            
            $exportContent .= implode(",\n", $values) . ";\n\n";
            echo "   ✓ Table `$table`: " . count($rows) . " ligne(s)\n";
        } else {
            echo "   ⚠ Table `$table`: vide\n";
        }
    } catch (PDOException $e) {
        echo "   ⚠️  Erreur lors de l'export de la table `$table`: " . $e->getMessage() . "\n";
        continue;
    }
}

$exportContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

echo "   ✅ Export terminé: $totalRows ligne(s) de données\n\n";

// ============================================
// ÉTAPE 2: IMPORT DANS LA BASE PRODUCTION
// ============================================
echo "📥 ÉTAPE 2: Import dans la base de production...\n";

$prodPdo = new PDO("mysql:host=$prodHost;dbname=$prodDb;charset=utf8mb4", $prodUser, $prodPass);
$prodPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "\n⚠️  ATTENTION: Cette opération va remplacer toutes les données existantes.\n";
$confirm = readline("   Continuer ? (oui/non): ");
if (strtolower($confirm) !== 'oui' && strtolower($confirm) !== 'o') {
    die("❌ Migration annulée.\n");
}

$prodPdo->exec("SET FOREIGN_KEY_CHECKS=0");

echo "\n   🗑️  Suppression des tables existantes...\n";
try {
    $prodPdo->exec("USE `$prodDb`");
    $existingTables = $prodPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($existingTables)) {
        foreach ($existingTables as $table) {
            try {
                $prodPdo->exec("DROP TABLE IF EXISTS `$table`");
            } catch (PDOException $e) {
                // Ignorer
            }
        }
        echo "   ✓ " . count($existingTables) . " table(s) supprimée(s)\n";
    }
} catch (PDOException $e) {
    // Ignorer
}

echo "\n   📥 Importation des données...\n";

$statements = array_filter(
    array_map('trim', explode(';', $exportContent)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt) && strlen($stmt) > 5;
    }
);

$executed = 0;
$errors = 0;
foreach ($statements as $statement) {
    try {
        $prodPdo->exec($statement);
        $executed++;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "doesn't exist") === false && 
            strpos($e->getMessage(), "Unknown database") === false) {
            $errors++;
        }
    }
}

$prodPdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "\n   ✅ Import terminé\n";
echo "   📝 Requêtes exécutées: $executed\n";
if ($errors > 0) {
    echo "   ⚠️  Erreurs: $errors\n";
}

echo "\n   📋 Vérification:\n";
try {
    $importedTables = $prodPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✓ " . count($importedTables) . " table(s) importée(s)\n";
    foreach ($importedTables as $table) {
        $count = $prodPdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "      - $table: $count ligne(s)\n";
    }
} catch (PDOException $e) {
    // Ignorer
}

echo "\n✨ Migration terminée avec succès !\n";
?>
