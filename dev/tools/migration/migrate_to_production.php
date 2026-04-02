<?php
/**
 * Script de migration des données de la base locale vers la base de production
 * 
 * Usage: php tools/migrate_to_production.php
 * 
 * Ce script :
 * 1. Exporte les données de la base locale (XAMPP)
 * 2. Importe les données dans la base de production (Hostinger)
 */

echo "🚀 Migration des données vers la production\n";
echo "==========================================\n\n";

// ============================================
// CONFIGURATION BASE LOCALE (XAMPP)
// ============================================
$localHost = '127.0.0.1';
$localUser = 'root';
$localPass = '';
$localDb = 'moncoachscolaire';

// Essayer de charger depuis les variables d'environnement locales
$envLocalHost = getenv('DB_HOST');
$envLocalUser = getenv('DB_USERNAME');
$envLocalPass = getenv('DB_PASSWORD');
$envLocalDb = getenv('DB_DATABASE');

if ($envLocalHost) $localHost = $envLocalHost;
if ($envLocalUser) $localUser = $envLocalUser;
if ($envLocalPass !== false) $localPass = $envLocalPass;
if ($envLocalDb) $localDb = $envLocalDb;

echo "📦 Configuration base locale:\n";
echo "   Host: $localHost\n";
echo "   User: $localUser\n";
echo "   Database: $localDb\n\n";

// ============================================
// CONFIGURATION BASE PRODUCTION (Hostinger)
// ============================================
echo "🌐 Configuration base de production:\n";
echo "   (Les informations seront demandées ci-dessous)\n\n";

// Demander les informations de production
$prodHost = readline("   Host (ex: localhost ou IP): ");
$prodUser = readline("   Username: ");
$prodPass = readline("   Password: ");
$prodDb = readline("   Database name: ");

if (empty($prodHost) || empty($prodUser) || empty($prodDb)) {
    die("❌ Configuration incomplète. Migration annulée.\n");
}

// Tester la connexion avant de continuer
echo "\n   🔍 Test de connexion à la base de production...\n";
try {
    $testPdo = new PDO("mysql:host=$prodHost;dbname=$prodDb;charset=utf8mb4", $prodUser, $prodPass);
    $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $testPdo->query("SELECT 1"); // Test simple
    echo "   ✅ Connexion réussie !\n\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "\n   💡 Vérifiez :\n";
    echo "      - Le mot de passe est correct\n";
    echo "      - L'utilisateur a les permissions nécessaires\n";
    echo "      - Le host est correct (essayez 'localhost' ou l'IP fournie par Hostinger)\n";
    echo "      - La base de données existe\n\n";
    
    $retry = readline("   Réessayer avec un nouveau mot de passe ? (oui/non): ");
    if (strtolower($retry) === 'oui' || strtolower($retry) === 'o') {
        $prodPass = readline("   Nouveau Password: ");
        // Retester
        try {
            $testPdo = new PDO("mysql:host=$prodHost;dbname=$prodDb;charset=utf8mb4", $prodUser, $prodPass);
            $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $testPdo->query("SELECT 1");
            echo "   ✅ Connexion réussie avec le nouveau mot de passe !\n\n";
        } catch (PDOException $e2) {
            die("❌ Échec de la connexion. Migration annulée.\n");
        }
    } else {
        die("❌ Migration annulée.\n");
    }
}

echo "\n📋 Configuration production:\n";
echo "   Host: $prodHost\n";
echo "   User: $prodUser\n";
echo "   Database: $prodDb\n\n";

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

// Récupérer toutes les tables
$tables = $localPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

if (empty($tables)) {
    die("❌ Aucune table trouvée dans la base locale\n");
}

echo "   ✓ " . count($tables) . " table(s) trouvée(s)\n";

// Créer le contenu SQL d'export
$exportContent = "-- Export de la base de données $localDb\n";
$exportContent .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
$exportContent .= "-- Généré par tools/migrate_to_production.php\n\n";
$exportContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

$totalRows = 0;
foreach ($tables as $table) {
    try {
        // Structure de la table
        $createTable = $localPdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        if ($createTable && isset($createTable['Create Table'])) {
            $exportContent .= "-- Structure de la table `$table`\n";
            $exportContent .= "DROP TABLE IF EXISTS `$table`;\n";
            $exportContent .= $createTable['Create Table'] . ";\n\n";
        }
        
        // Données de la table
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

// La connexion a déjà été testée, on la réutilise
try {
    $prodPdo = new PDO("mysql:host=$prodHost;dbname=$prodDb;charset=utf8mb4", $prodUser, $prodPass);
    $prodPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Erreur de connexion à la base de production: " . $e->getMessage() . "\n");
}

// Demander confirmation avant de supprimer les données existantes
echo "\n⚠️  ATTENTION: Cette opération va:\n";
echo "   - Supprimer toutes les tables existantes dans la base de production\n";
echo "   - Recréer les tables avec les données de la base locale\n";
echo "   - Remplacer toutes les données existantes\n\n";

$confirm = readline("   Continuer ? (oui/non): ");
if (strtolower($confirm) !== 'oui' && strtolower($confirm) !== 'o') {
    die("❌ Migration annulée par l'utilisateur\n");
}

// Désactiver les vérifications de clés étrangères
$prodPdo->exec("SET FOREIGN_KEY_CHECKS=0");

// Supprimer toutes les tables existantes
echo "\n   🗑️  Suppression des tables existantes...\n";
try {
    $prodPdo->exec("USE `$prodDb`");
    $existingTables = $prodPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($existingTables)) {
        foreach ($existingTables as $table) {
            try {
                $prodPdo->exec("DROP TABLE IF EXISTS `$table`");
            } catch (PDOException $e) {
                echo "   ⚠️  Erreur lors de la suppression de `$table`: " . $e->getMessage() . "\n";
            }
        }
        echo "   ✓ " . count($existingTables) . " table(s) supprimée(s)\n";
    }
} catch (PDOException $e) {
    echo "   ⚠️  Note: " . $e->getMessage() . "\n";
}

// Exécuter le script SQL d'import
echo "\n   📥 Importation des données...\n";

// Diviser le SQL en requêtes individuelles
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
        // Ignorer certaines erreurs (comme DROP TABLE IF EXISTS sur une table inexistante)
        if (strpos($e->getMessage(), "doesn't exist") === false && 
            strpos($e->getMessage(), "Unknown database") === false) {
            echo "   ⚠️  Erreur: " . substr($statement, 0, 80) . "... - " . $e->getMessage() . "\n";
            $errors++;
        }
    }
}

// Réactiver les vérifications de clés étrangères
$prodPdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "\n   ✅ Import terminé\n";
echo "   📝 Requêtes exécutées: $executed\n";
if ($errors > 0) {
    echo "   ⚠️  Erreurs rencontrées: $errors\n";
}

// Vérifier les tables importées
echo "\n   📋 Vérification des tables importées:\n";
try {
    $importedTables = $prodPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✓ " . count($importedTables) . " table(s) importée(s)\n";
    foreach ($importedTables as $table) {
        $count = $prodPdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "      - $table: $count ligne(s)\n";
    }
} catch (PDOException $e) {
    echo "   ⚠️  Erreur lors de la vérification: " . $e->getMessage() . "\n";
}

echo "\n✨ Migration terminée avec succès !\n";
echo "\n📝 Résumé:\n";
echo "   - Tables exportées: " . count($tables) . "\n";
echo "   - Lignes de données: $totalRows\n";
echo "   - Requêtes exécutées: $executed\n";
?>
