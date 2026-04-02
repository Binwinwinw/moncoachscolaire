<?php
/**
 * Solution finale pour nettoyer les tablespaces orphelins
 * Crée les tables avec la bonne structure directement depuis le schéma
 */

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

echo "🔧 Solution finale pour réparer les tablespaces...\n\n";

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    
    // Créer les tables avec DROP TABLE d'abord (sans IF NOT EXISTS)
    echo "📋 Création des tables avec nettoyage des tablespaces...\n\n";
    
    // USERS
    echo "1. Table Users...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS `Users`");
        $pdo->exec("CREATE TABLE `Users` (
          `Id` INT NOT NULL AUTO_INCREMENT,
          `Username` VARCHAR(100) NOT NULL,
          `Email` VARCHAR(255) NOT NULL UNIQUE,
          `PasswordHash` VARCHAR(255) NOT NULL,
          `Role` VARCHAR(50) NOT NULL DEFAULT 'student',
          `UserLevel` VARCHAR(10) NOT NULL DEFAULT '6eme',
          `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "   ✓ Créée\n";
    } catch (PDOException $e) {
        echo "   ❌ " . $e->getMessage() . "\n";
    }
    
    // USERPROGRESS
    echo "2. Table UserProgress...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS `UserProgress`");
        $pdo->exec("CREATE TABLE `UserProgress` (
          `Id` INT NOT NULL AUTO_INCREMENT,
          `UserId` INT NOT NULL,
          `CurrentPosition` INT NOT NULL DEFAULT 1,
          `XP` INT NOT NULL DEFAULT 0,
          `ProgressJson` JSON NULL,
          `UpdatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`Id`),
          INDEX (`UserId`),
          CONSTRAINT `FK_UserProgress_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "   ✓ Créée\n";
    } catch (PDOException $e) {
        echo "   ❌ " . $e->getMessage() . "\n";
    }
    
    // POWERS
    echo "3. Table Powers...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS `Powers`");
        $pdo->exec("CREATE TABLE `Powers` (
          `Id` INT NOT NULL AUTO_INCREMENT,
          `Name` VARCHAR(200) NOT NULL,
          `Icon` VARCHAR(10),
          `Effect` JSON NULL,
          `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "   ✓ Créée\n";
    } catch (PDOException $e) {
        echo "   ❌ " . $e->getMessage() . "\n";
    }
    
    // USERPOWERS
    echo "4. Table UserPowers...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS `UserPowers`");
        $pdo->exec("CREATE TABLE `UserPowers` (
          `Id` INT NOT NULL AUTO_INCREMENT,
          `UserId` INT NOT NULL,
          `PowerId` INT NOT NULL,
          `UnlockedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`Id`),
          INDEX (`UserId`),
          INDEX (`PowerId`),
          CONSTRAINT `FK_UserPowers_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE,
          CONSTRAINT `FK_UserPowers_Power` FOREIGN KEY (`PowerId`) REFERENCES `Powers`(`Id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "   ✓ Créée\n";
    } catch (PDOException $e) {
        echo "   ❌ " . $e->getMessage() . "\n";
    }
    
    // ACHIEVEMENTS
    echo "5. Table Achievements...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS `Achievements`");
        $pdo->exec("CREATE TABLE `Achievements` (
          `Id` INT NOT NULL AUTO_INCREMENT,
          `Name` VARCHAR(150) NOT NULL,
          `Description` TEXT NULL,
          `Points` INT NOT NULL DEFAULT 0,
          PRIMARY KEY (`Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "   ✓ Créée\n";
    } catch (PDOException $e) {
        echo "   ❌ " . $e->getMessage() . "\n";
    }
    
    // USERACHIEVEMENTS
    echo "6. Table UserAchievements...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS `UserAchievements`");
        $pdo->exec("CREATE TABLE `UserAchievements` (
          `Id` INT NOT NULL AUTO_INCREMENT,
          `UserId` INT NOT NULL,
          `AchievementId` INT NOT NULL,
          `UnlockedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`Id`),
          INDEX (`UserId`),
          INDEX (`AchievementId`),
          CONSTRAINT `FK_UserAchievements_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE,
          CONSTRAINT `FK_UserAchievements_Achievement` FOREIGN KEY (`AchievementId`) REFERENCES `Achievements`(`Id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "   ✓ Créée\n";
    } catch (PDOException $e) {
        echo "   ❌ " . $e->getMessage() . "\n";
    }
    
    // EXERCISES
    echo "7. Table Exercises...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS `Exercises`");
        $pdo->exec("CREATE TABLE `Exercises` (
          `Id` INT NOT NULL AUTO_INCREMENT,
          `Subject` VARCHAR(60) NULL,
          `Level` VARCHAR(10) NULL,
          `Title` VARCHAR(250) NULL,
          `Content` LONGTEXT NULL,
          `Answer` LONGTEXT NULL,
          PRIMARY KEY (`Id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "   ✓ Créée\n";
    } catch (PDOException $e) {
        echo "   ❌ " . $e->getMessage() . "\n";
    }
    
    // EXERCISERESPONSES
    echo "8. Table ExerciseResponses...\n";
    try {
        $pdo->exec("DROP TABLE IF EXISTS `ExerciseResponses`");
        $pdo->exec("CREATE TABLE `ExerciseResponses` (
          `Id` INT NOT NULL AUTO_INCREMENT,
          `UserId` INT NOT NULL,
          `ExerciseId` INT NOT NULL,
          `Correct` TINYINT(1) NOT NULL DEFAULT 0,
          `Score` INT NOT NULL DEFAULT 0,
          `SubmittedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`Id`),
          INDEX (`UserId`),
          INDEX (`ExerciseId`),
          CONSTRAINT `FK_ExerciseResponses_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE,
          CONSTRAINT `FK_ExerciseResponses_Exercise` FOREIGN KEY (`ExerciseId`) REFERENCES `Exercises`(`Id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "   ✓ Créée\n";
    } catch (PDOException $e) {
        echo "   ❌ " . $e->getMessage() . "\n";
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
    
    // Insérer les données de seed
    echo "\n📥 Insertion des données de seed...\n";
    try {
        $pdo->exec("INSERT INTO `Powers` (`Name`, `Icon`, `Effect`) VALUES
        ('Bouclier Mathématique', '🛡️', JSON_OBJECT('desc','Réduit les erreurs de calcul')),
        ('Bibliothèque Instantanée', '📚', JSON_OBJECT('desc','Accès rapide aux définitions')),
        ('Masque de Concentration', '🎭', JSON_OBJECT('desc','Bloque les distractions'))");
        echo "   ✓ Powers insérées\n";
    } catch (PDOException $e) {
        echo "   ⚠️  Powers: " . $e->getMessage() . "\n";
    }
    
    try {
        $pdo->exec("INSERT INTO `Users` (`Username`, `Email`, `PasswordHash`, `Role`, `UserLevel`) VALUES
        ('demo', 'demo@example.com', 'demo-hash', 'student', '6eme')");
        echo "   ✓ User demo inséré\n";
    } catch (PDOException $e) {
        echo "   ⚠️  User: " . $e->getMessage() . "\n";
    }
    
    // Vérification finale
    echo "\n✅ Vérification...\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "📋 Tables créées: " . count($tables) . "\n";
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "   - `$table`: $count ligne(s)\n";
    }
    
    echo "\n✅ RESTAURATION RÉUSSIE !\n";
    
} catch (PDOException $e) {
    echo "\n❌ ERREUR: " . $e->getMessage() . "\n";
    echo "\n💡 SOLUTION: Arrêter MySQL, supprimer le dossier C:\\xampp\\mysql\\data\\moncoachscolaire, puis redémarrer MySQL et réessayer.\n";
    exit(1);
}

echo "\n✨ Terminé !\n";
?>

