<?php
/**
 * Script pour adapter automatiquement la table Users pour gérer les parents
 * Exécutez ce script une seule fois via : http://votre-site/tools/setup_users_for_parents.php
 * OU via CLI : php tools/setup_users_for_parents.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🔧 Adaptation de la table Users pour gérer les parents...\n\n";

try {
    // 1. Modifier UserLevel pour permettre NULL
    echo "1. Modification de UserLevel pour accepter NULL...\n";
    try {
        $pdo->exec("ALTER TABLE `Users` MODIFY COLUMN `UserLevel` VARCHAR(10) NULL DEFAULT NULL");
        echo "   ✅ UserLevel modifié avec succès\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') === false) {
            echo "   ⚠️ " . $e->getMessage() . "\n";
        }
    }
    
    // 2. Ajouter la colonne Nom
    echo "2. Ajout de la colonne Nom...\n";
    try {
        $pdo->exec("ALTER TABLE `Users` ADD COLUMN `Nom` VARCHAR(100) NULL DEFAULT NULL AFTER `UserLevel`");
        echo "   ✅ Colonne Nom ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ️ Colonne Nom existe déjà\n";
        } else {
            echo "   ⚠️ " . $e->getMessage() . "\n";
        }
    }
    
    // 3. Ajouter la colonne Prenom
    echo "3. Ajout de la colonne Prenom...\n";
    try {
        $pdo->exec("ALTER TABLE `Users` ADD COLUMN `Prenom` VARCHAR(100) NULL DEFAULT NULL AFTER `Nom`");
        echo "   ✅ Colonne Prenom ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ️ Colonne Prenom existe déjà\n";
        } else {
            echo "   ⚠️ " . $e->getMessage() . "\n";
        }
    }
    
    // 4. Ajouter la colonne Telephone
    echo "4. Ajout de la colonne Telephone...\n";
    try {
        $pdo->exec("ALTER TABLE `Users` ADD COLUMN `Telephone` VARCHAR(20) NULL DEFAULT NULL AFTER `Prenom`");
        echo "   ✅ Colonne Telephone ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ️ Colonne Telephone existe déjà\n";
        } else {
            echo "   ⚠️ " . $e->getMessage() . "\n";
        }
    }
    
    // 5. Ajouter la colonne ParentId
    echo "5. Ajout de la colonne ParentId...\n";
    try {
        $pdo->exec("ALTER TABLE `Users` ADD COLUMN `ParentId` INT NULL DEFAULT NULL AFTER `Telephone`");
        echo "   ✅ Colonne ParentId ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "   ℹ️ Colonne ParentId existe déjà\n";
        } else {
            echo "   ⚠️ " . $e->getMessage() . "\n";
        }
    }
    
    // 6. Ajouter l'index sur ParentId
    echo "6. Ajout de l'index sur ParentId...\n";
    try {
        $pdo->exec("ALTER TABLE `Users` ADD INDEX `idx_parent_id` (`ParentId`)");
        echo "   ✅ Index idx_parent_id ajouté\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "   ℹ️ Index idx_parent_id existe déjà\n";
        } else {
            echo "   ⚠️ " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Migration terminée avec succès !\n";
    echo "La table Users est maintenant prête à gérer les parents et les enfants.\n";
    
} catch (Exception $e) {
    echo "\n❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
