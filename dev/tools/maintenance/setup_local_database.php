<?php
/**
 * Script de configuration de la base de données locale
 * Crée la base et les tables si elles n'existent pas
 */

echo "🔧 CONFIGURATION BASE DE DONNÉES LOCALE\n";
echo str_repeat("=", 60) . "\n\n";

// Charger les variables d'environnement locales
if (file_exists(__DIR__ . '/../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../.env');
    foreach (explode("\n", $envContent) as $line) {
        if (empty(trim($line)) || strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$database = getenv('DB_DATABASE') ?: 'moncoachscolaire';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

echo "Configuration détectée:\n";
echo "  Host: $host\n";
echo "  Database: $database\n";
echo "  Username: $username\n";
echo "  Password: " . (empty($password) ? "(vide)" : "***") . "\n\n";

// Étape 1: Se connecter sans spécifier la base (pour pouvoir la créer)
try {
    echo "1️⃣ Connexion au serveur MySQL...\n";
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✓ Connexion réussie\n\n";
} catch (PDOException $e) {
    die("❌ Impossible de se connecter à MySQL: " . $e->getMessage() . "\n" .
        "Assurez-vous que XAMPP/MySQL est démarré.\n");
}

// Étape 2: Créer la base de données si elle n'existe pas
try {
    echo "2️⃣ Vérification/création de la base '$database'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de données prête\n\n";
} catch (PDOException $e) {
    die("❌ Impossible de créer la base: " . $e->getMessage() . "\n");
}

// Étape 3: Se reconnecter avec la base sélectionnée
try {
    echo "3️⃣ Sélection de la base '$database'...\n";
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "✓ Base sélectionnée\n\n";
} catch (PDOException $e) {
    die("❌ Impossible de sélectionner la base: " . $e->getMessage() . "\n");
}

// Étape 4: Créer les tables de base si elles n'existent pas
echo "4️⃣ Vérification des tables essentielles...\n";

// Table Users
$pdo->exec("
CREATE TABLE IF NOT EXISTS `Users` (
    `Id` INT AUTO_INCREMENT PRIMARY KEY,
    `Username` VARCHAR(100) NOT NULL,
    `Email` VARCHAR(255) NOT NULL UNIQUE,
    `PasswordHash` VARCHAR(255) NOT NULL,
    `Role` VARCHAR(50) DEFAULT 'student',
    `UserLevel` VARCHAR(10) DEFAULT '6ème',
    `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "✓ Table Users\n";

// Table Exercises
$pdo->exec("
CREATE TABLE IF NOT EXISTS `Exercises` (
    `Id` INT AUTO_INCREMENT PRIMARY KEY,
    `Subject` VARCHAR(60),
    `Level` VARCHAR(10),
    `Title` VARCHAR(250),
    `Content` LONGTEXT,
    `Answer` LONGTEXT,
    `CreatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "✓ Table Exercises\n";

// Table UserProgress
$pdo->exec("
CREATE TABLE IF NOT EXISTS `UserProgress` (
    `Id` INT AUTO_INCREMENT PRIMARY KEY,
    `UserId` INT NOT NULL,
    `CurrentPosition` INT DEFAULT 1,
    `XP` INT DEFAULT 0,
    `ProgressJson` LONGTEXT,
    `UpdatedAt` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE,
    UNIQUE KEY `UQ_UserProgress_UserId` (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "✓ Table UserProgress\n";

// Table ExerciseResponses
$pdo->exec("
CREATE TABLE IF NOT EXISTS `ExerciseResponses` (
    `Id` INT AUTO_INCREMENT PRIMARY KEY,
    `UserId` INT NOT NULL,
    `ExerciseId` INT NOT NULL,
    `Correct` TINYINT(1) NOT NULL DEFAULT 0,
    `Score` INT DEFAULT 0,
    `SubmittedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE,
    FOREIGN KEY (`ExerciseId`) REFERENCES `Exercises`(`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
echo "✓ Table ExerciseResponses\n\n";

// Étape 5: Appliquer la migration des Courses
echo "5️⃣ Application de la migration Courses...\n";
$migrationFile = __DIR__ . '/../db/migration_courses_mysql.sql';

if (file_exists($migrationFile)) {
    $migrationSQL = file_get_contents($migrationFile);
    $statements = array_filter(array_map('trim', explode(';', $migrationSQL)));
    
    $created = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', trim($statement))) {
            try {
                $pdo->exec($statement);
                $created++;
            } catch (PDOException $e) {
                // Ignorer les erreurs de "already exists"
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    echo "⚠️ Avertissement: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    echo "✓ Migration Courses appliquée ($created objets créés)\n\n";
} else {
    echo "⚠️ Fichier migration non trouvé: $migrationFile\n\n";
}

// Étape 6: Vérifier les tables créées
echo "6️⃣ Tables existantes:\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    // Compter les lignes
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "  ✓ $table ($count lignes)\n";
    } catch (PDOException $e) {
        echo "  ✓ $table\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ BASE DE DONNÉES LOCALE CONFIGURÉE\n";
echo "\nProchaine étape: php tools/import_courses_exercises.php action=import\n";
