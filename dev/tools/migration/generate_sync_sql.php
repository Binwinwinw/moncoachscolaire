<?php
/**
 * Script pour générer un script SQL de synchronisation Local → Production
 * 
 * Ce script analyse un fichier SQL d'export local et génère un script SQL
 * qui ajoute uniquement les colonnes/tables/index manquants en production
 * 
 * Usage: php tools/generate_sync_sql.php [chemin_vers_export_local.sql]
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

// Vérifier la connexion à la base de données de production
if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données de production.\n");
}

echo "🔍 Analyse de la structure de la base de données de production...\n\n";

// Récupérer le fichier SQL local si fourni
$localSqlFile = $argv[1] ?? null;

if ($localSqlFile && file_exists($localSqlFile)) {
    echo "📄 Fichier SQL local détecté : $localSqlFile\n";
    echo "⚠️  Note : Ce script analyse directement la structure de production.\n";
    echo "   Pour une comparaison complète, utilisez le script sync_local_to_prod.sql\n\n";
}

// Fonction pour vérifier si une colonne existe
function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Fonction pour vérifier si un index existe
function indexExists($pdo, $table, $indexName) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ");
        $stmt->execute([$table, $indexName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Fonction pour vérifier si une table existe
function tableExists($pdo, $table) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ?
        ");
        $stmt->execute([$table]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Générer le script SQL
$sqlScript = "-- ============================================================================\n";
$sqlScript .= "-- Script de synchronisation généré automatiquement\n";
$sqlScript .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
$sqlScript .= "-- Base de données: " . (getenv('DB_DATABASE') ?: 'moncoachscolaire') . "\n";
$sqlScript .= "-- ============================================================================\n\n";
$sqlScript .= "USE " . (getenv('DB_DATABASE') ?: 'moncoachscolaire') . ";\n\n";

$changes = [];

// 1. Vérifier et ajouter les colonnes pour Users
echo "📋 Vérification de la table Users...\n";

$usersColumns = [
    'Nom' => ['type' => 'VARCHAR(100)', 'null' => 'NULL', 'after' => 'UserLevel'],
    'Prenom' => ['type' => 'VARCHAR(100)', 'null' => 'NULL', 'after' => 'Nom'],
    'Telephone' => ['type' => 'VARCHAR(20)', 'null' => 'NULL', 'after' => 'Prenom'],
    'ParentId' => ['type' => 'INT', 'null' => 'NULL', 'after' => 'Telephone']
];

foreach ($usersColumns as $column => $def) {
    if (!columnExists($pdo, 'Users', $column)) {
        $sqlScript .= "-- Ajouter la colonne $column\n";
        $sqlScript .= "ALTER TABLE `Users` ADD COLUMN `$column` {$def['type']} {$def['null']} DEFAULT NULL AFTER `{$def['after']}`;\n\n";
        $changes[] = "✅ Colonne Users.$column ajoutée";
        echo "  ➕ Colonne $column manquante - sera ajoutée\n";
    } else {
        echo "  ✓ Colonne $column existe déjà\n";
    }
}

// Vérifier si UserLevel accepte NULL
try {
    $stmt = $pdo->prepare("
        SELECT IS_NULLABLE 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'Users' 
        AND COLUMN_NAME = 'UserLevel'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && $result['IS_NULLABLE'] === 'NO') {
        $sqlScript .= "-- Modifier UserLevel pour accepter NULL\n";
        $sqlScript .= "ALTER TABLE `Users` MODIFY COLUMN `UserLevel` VARCHAR(10) NULL DEFAULT NULL;\n\n";
        $changes[] = "✅ UserLevel modifié pour accepter NULL";
        echo "  ➕ UserLevel sera modifié pour accepter NULL\n";
    } else {
        echo "  ✓ UserLevel accepte déjà NULL\n";
    }
} catch (Exception $e) {
    echo "  ⚠️  Erreur lors de la vérification de UserLevel\n";
}

// Vérifier l'index sur ParentId
if (!indexExists($pdo, 'Users', 'idx_parent_id')) {
    if (columnExists($pdo, 'Users', 'ParentId')) {
        $sqlScript .= "-- Ajouter l'index sur ParentId\n";
        $sqlScript .= "ALTER TABLE `Users` ADD INDEX `idx_parent_id` (`ParentId`);\n\n";
        $changes[] = "✅ Index idx_parent_id ajouté";
        echo "  ➕ Index idx_parent_id manquant - sera ajouté\n";
    }
} else {
    echo "  ✓ Index idx_parent_id existe déjà\n";
}

// 2. Vérifier et créer AdminLogs
echo "\n📋 Vérification de la table AdminLogs...\n";
if (!tableExists($pdo, 'AdminLogs')) {
    $sqlScript .= "-- Créer la table AdminLogs\n";
    $sqlScript .= "CREATE TABLE IF NOT EXISTS `AdminLogs` (\n";
    $sqlScript .= "    `Id` INT NOT NULL AUTO_INCREMENT,\n";
    $sqlScript .= "    `AdminId` INT NOT NULL,\n";
    $sqlScript .= "    `Action` VARCHAR(100) NOT NULL,\n";
    $sqlScript .= "    `Details` TEXT NULL,\n";
    $sqlScript .= "    `TargetUserId` INT NULL,\n";
    $sqlScript .= "    `IpAddress` VARCHAR(45) NULL,\n";
    $sqlScript .= "    `UserAgent` TEXT NULL,\n";
    $sqlScript .= "    `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,\n";
    $sqlScript .= "    PRIMARY KEY (`Id`),\n";
    $sqlScript .= "    INDEX (`AdminId`),\n";
    $sqlScript .= "    INDEX (`CreatedAt`),\n";
    $sqlScript .= "    CONSTRAINT `FK_AdminLogs_Admin` FOREIGN KEY (`AdminId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE\n";
    $sqlScript .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
    $changes[] = "✅ Table AdminLogs créée";
    echo "  ➕ Table AdminLogs manquante - sera créée\n";
} else {
    echo "  ✓ Table AdminLogs existe déjà\n";
}

// 3. Vérifier les tables étendues
$extendedTables = [
    'UserDailyGoals' => [
        'columns' => [
            'Id' => 'INT NOT NULL AUTO_INCREMENT',
            'UserId' => 'INT NOT NULL',
            'Date' => 'DATE NOT NULL',
            'GoalType' => 'VARCHAR(50) NOT NULL DEFAULT \'exercises\'',
            'Target' => 'INT NOT NULL DEFAULT 3',
            'Completed' => 'INT NOT NULL DEFAULT 0',
            'CreatedAt' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        ],
        'primary' => 'Id',
        'indexes' => [
            'unique_user_date_type' => 'UNIQUE KEY (`UserId`, `Date`, `GoalType`)',
            'idx_userid' => 'INDEX (`UserId`)',
            'idx_date' => 'INDEX (`Date`)'
        ],
        'foreign' => 'CONSTRAINT `FK_UserDailyGoals_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE'
    ],
    'UserWeeklyGoals' => [
        'columns' => [
            'Id' => 'INT NOT NULL AUTO_INCREMENT',
            'UserId' => 'INT NOT NULL',
            'WeekStart' => 'DATE NOT NULL',
            'GoalType' => 'VARCHAR(50) NOT NULL DEFAULT \'exercises\'',
            'Target' => 'INT NOT NULL DEFAULT 15',
            'Completed' => 'INT NOT NULL DEFAULT 0',
            'CreatedAt' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'UpdatedAt' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ],
        'primary' => 'Id',
        'indexes' => [
            'unique_user_week_type' => 'UNIQUE KEY (`UserId`, `WeekStart`, `GoalType`)',
            'idx_userid' => 'INDEX (`UserId`)',
            'idx_weekstart' => 'INDEX (`WeekStart`)'
        ],
        'foreign' => 'CONSTRAINT `FK_UserWeeklyGoals_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE'
    ],
    'UserStreak' => [
        'columns' => [
            'Id' => 'INT NOT NULL AUTO_INCREMENT',
            'UserId' => 'INT NOT NULL',
            'CurrentStreak' => 'INT NOT NULL DEFAULT 0',
            'LongestStreak' => 'INT NOT NULL DEFAULT 0',
            'LastLoginDate' => 'DATE NULL',
            'UpdatedAt' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ],
        'primary' => 'Id',
        'indexes' => [
            'unique_user' => 'UNIQUE KEY (`UserId`)',
            'idx_userid' => 'INDEX (`UserId`)'
        ],
        'foreign' => 'CONSTRAINT `FK_UserStreak_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE'
    ],
    'UserProgressHistory' => [
        'columns' => [
            'Id' => 'INT NOT NULL AUTO_INCREMENT',
            'UserId' => 'INT NOT NULL',
            'Date' => 'DATE NOT NULL',
            'XP' => 'INT NOT NULL DEFAULT 0',
            'ExercisesCompleted' => 'INT NOT NULL DEFAULT 0',
            'Cristaux' => 'INT NOT NULL DEFAULT 0',
            'CreatedAt' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        ],
        'primary' => 'Id',
        'indexes' => [
            'unique_user_date' => 'UNIQUE KEY (`UserId`, `Date`)',
            'idx_userid' => 'INDEX (`UserId`)',
            'idx_date' => 'INDEX (`Date`)'
        ],
        'foreign' => 'CONSTRAINT `FK_UserProgressHistory_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE'
    ],
    'UserNotifications' => [
        'columns' => [
            'Id' => 'INT NOT NULL AUTO_INCREMENT',
            'UserId' => 'INT NOT NULL',
            'Type' => 'VARCHAR(50) NOT NULL DEFAULT \'info\'',
            'Title' => 'VARCHAR(200) NOT NULL',
            'Message' => 'TEXT NULL',
            'Icon' => 'VARCHAR(10) NULL',
            'Link' => 'VARCHAR(255) NULL',
            'Read' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'CreatedAt' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
        ],
        'primary' => 'Id',
        'indexes' => [
            'idx_userid' => 'INDEX (`UserId`)',
            'idx_read' => 'INDEX (`Read`)',
            'idx_createdat' => 'INDEX (`CreatedAt`)'
        ],
        'foreign' => 'CONSTRAINT `FK_UserNotifications_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE'
    ],
    'UserLoginHistory' => [
        'columns' => [
            'Id' => 'INT NOT NULL AUTO_INCREMENT',
            'UserId' => 'INT NOT NULL',
            'LoginDate' => 'DATE NOT NULL',
            'LoginTime' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'IpAddress' => 'VARCHAR(45) NULL',
            'UserAgent' => 'TEXT NULL'
        ],
        'primary' => 'Id',
        'indexes' => [
            'unique_user_login_date' => 'UNIQUE KEY (`UserId`, `LoginDate`)',
            'idx_userid' => 'INDEX (`UserId`)',
            'idx_logindate' => 'INDEX (`LoginDate`)'
        ],
        'foreign' => 'CONSTRAINT `FK_UserLoginHistory_User` FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE'
    ]
];

foreach ($extendedTables as $tableName => $tableDef) {
    echo "\n📋 Vérification de la table $tableName...\n";
    if (!tableExists($pdo, $tableName)) {
        $sqlScript .= "-- Créer la table $tableName\n";
        $sqlScript .= "CREATE TABLE IF NOT EXISTS `$tableName` (\n";
        
        foreach ($tableDef['columns'] as $colName => $colDef) {
            $sqlScript .= "    `$colName` $colDef";
            if ($colName === $tableDef['primary']) {
                $sqlScript .= " PRIMARY KEY";
            }
            $sqlScript .= ",\n";
        }
        
        // Ajouter les index
        foreach ($tableDef['indexes'] as $idxName => $idxDef) {
            $sqlScript .= "    $idxDef,\n";
        }
        
        // Ajouter la clé étrangère
        if (isset($tableDef['foreign'])) {
            $sqlScript .= "    {$tableDef['foreign']}\n";
        } else {
            // Enlever la virgule de la dernière ligne
            $sqlScript = rtrim($sqlScript, ",\n") . "\n";
        }
        
        $sqlScript .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
        $changes[] = "✅ Table $tableName créée";
        echo "  ➕ Table $tableName manquante - sera créée\n";
    } else {
        echo "  ✓ Table $tableName existe déjà\n";
        
        // Vérifier les colonnes manquantes dans cette table
        foreach ($tableDef['columns'] as $colName => $colDef) {
            if (!columnExists($pdo, $tableName, $colName)) {
                // Déterminer la position (après quelle colonne)
                $afterCol = 'Id';
                $cols = array_keys($tableDef['columns']);
                $pos = array_search($colName, $cols);
                if ($pos > 0) {
                    $afterCol = $cols[$pos - 1];
                }
                
                $sqlScript .= "-- Ajouter la colonne $colName dans $tableName\n";
                $sqlScript .= "ALTER TABLE `$tableName` ADD COLUMN `$colName` $colDef AFTER `$afterCol`;\n\n";
                $changes[] = "✅ Colonne $tableName.$colName ajoutée";
                echo "    ➕ Colonne $colName manquante - sera ajoutée\n";
            }
        }
    }
}

// Ajouter un résumé final
$sqlScript .= "-- ============================================================================\n";
$sqlScript .= "-- Résumé de la synchronisation\n";
$sqlScript .= "-- ============================================================================\n";
$sqlScript .= "SELECT '=== SYNCHRONISATION TERMINÉE ===' AS message;\n";
$sqlScript .= "SELECT CONCAT('Base de données: ', DATABASE()) AS message;\n\n";

// Sauvegarder le script
$outputFile = __DIR__ . '/../db/sync_local_to_prod_generated.sql';
file_put_contents($outputFile, $sqlScript);

echo "\n✅ Script SQL généré avec succès !\n";
echo "📄 Fichier : $outputFile\n";
echo "\n📊 Résumé des modifications :\n";
if (empty($changes)) {
    echo "   ✓ Aucune modification nécessaire - la base est à jour\n";
} else {
    foreach ($changes as $change) {
        echo "   $change\n";
    }
}

echo "\n⚠️  IMPORTANT :\n";
echo "   1. Vérifiez le script généré avant de l'exécuter\n";
echo "   2. Faites une sauvegarde de la base de production\n";
echo "   3. Exécutez le script sur la base de PRODUCTION uniquement\n";
echo "\n";
