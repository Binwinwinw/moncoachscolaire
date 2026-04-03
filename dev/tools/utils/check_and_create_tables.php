<?php
/**
 * Script pour vérifier et créer les tables manquantes
 * Accessible via : https://moncoachscolaire.fr/index.php?page=check_and_create_tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$directAccess = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));

if ($directAccess) {
    header('Content-Type: text/plain; charset=utf-8');
} else {
    $page_title = 'Vérification Tables';
    echo '<main class="main-content"><section><pre>';
}

echo "🔍 Vérification et création des tables\n";
echo "======================================\n\n";

$root = __DIR__;
require_once $root . '/config.php';
require_once $root . '/db/connection.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo "❌ Connexion PDO non disponible\n";
    if (isset($dbUnavailable) && $dbUnavailable) {
        echo "Erreur: " . (isset($dbErrorMessage) ? $dbErrorMessage : 'Inconnue') . "\n";
    }
    if (!$directAccess) {
        echo '</pre></section></main>';
    }
    exit(1);
}

echo "1. Liste des tables existantes...\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Tables trouvées: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "      - $table\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    if (!$directAccess) {
        echo '</pre></section></main>';
    }
    exit(1);
}
echo "\n";

echo "2. Vérification de la table Users...\n";
$usersTableExists = in_array('Users', $tables) || in_array('users', $tables);

if (!$usersTableExists) {
    echo "   ❌ Table Users n'existe pas\n";
    echo "   🔧 Création de la table Users...\n";
    
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `Users` (
              `Id` INT NOT NULL AUTO_INCREMENT,
              `Username` VARCHAR(100) NOT NULL,
              `Email` VARCHAR(255) NOT NULL UNIQUE,
              `PasswordHash` VARCHAR(255) NOT NULL,
              `Role` VARCHAR(50) NOT NULL DEFAULT 'student',
              `UserLevel` VARCHAR(10) NOT NULL DEFAULT '6eme',
              `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`Id`),
              UNIQUE KEY `Email` (`Email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "   ✅ Table Users créée avec succès\n";
        
        // Vérifier si on doit créer l'utilisateur demo
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM Users");
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            echo "   🔧 Création de l'utilisateur demo...\n";
            $stmt = $pdo->prepare("
                INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute(['demo', 'demo@example.com', 'demo-hash', 'student', '6eme']);
            echo "   ✅ Utilisateur demo créé\n";
        }
    } catch (PDOException $e) {
        echo "   ❌ Erreur lors de la création: " . $e->getMessage() . "\n";
    }
} else {
    $tableName = in_array('Users', $tables) ? 'Users' : 'users';
    echo "   ✅ Table $tableName existe\n";
    
    // Vérifier le nombre d'utilisateurs
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$tableName`");
        $count = $stmt->fetch()['count'];
        echo "   Nombre d'utilisateurs: $count\n";
    } catch (Exception $e) {
        echo "   ⚠️  Erreur lors du comptage: " . $e->getMessage() . "\n";
    }
}
echo "\n";

echo "3. Vérification de la table UserProgress...\n";
$userProgressExists = in_array('UserProgress', $tables) || in_array('userprogress', $tables);

if (!$userProgressExists) {
    echo "   ❌ Table UserProgress n'existe pas\n";
    echo "   🔧 Création de la table UserProgress...\n";
    
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `UserProgress` (
              `Id` INT NOT NULL AUTO_INCREMENT,
              `UserId` INT NOT NULL,
              `CurrentPosition` INT NOT NULL DEFAULT 1,
              `XP` INT NOT NULL DEFAULT 0,
              `ProgressJson` JSON NULL,
              `UpdatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`Id`),
              INDEX (`UserId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "   ✅ Table UserProgress créée avec succès\n";
    } catch (PDOException $e) {
        echo "   ❌ Erreur lors de la création: " . $e->getMessage() . "\n";
    }
} else {
    $tableName = in_array('UserProgress', $tables) ? 'UserProgress' : 'userprogress';
    echo "   ✅ Table $tableName existe\n";
}
echo "\n";

echo "4. Test d'insertion...\n";
try {
    $testUsername = 'test_' . time();
    $stmt = $pdo->prepare("
        INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$testUsername, $testUsername . '@test.com', password_hash('test', PASSWORD_DEFAULT), 'student', '6eme']);
    $userId = $pdo->lastInsertId();
    echo "   ✅ Insertion test réussie (ID: $userId)\n";
    
    // Nettoyer
    $stmt = $pdo->prepare("DELETE FROM Users WHERE Id = ?");
    $stmt->execute([$userId]);
    echo "   ✅ Utilisateur de test supprimé\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur lors du test: " . $e->getMessage() . "\n";
}
echo "\n";

echo "✅ Vérification terminée !\n";
echo "\n💡 Si toutes les tables existent maintenant, vous pouvez :\n";
echo "   1. Tester la création de compte via le formulaire d'inscription\n";
echo "   2. Tester la connexion avec les utilisateurs existants\n";

echo "\n⚠️  SUPPRIMEZ CE FICHIER après les tests pour des raisons de sécurité !\n";

if (!$directAccess) {
    echo '</pre></section></main>';
}
?>

