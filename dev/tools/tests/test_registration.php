<?php
/**
 * Script de test pour vérifier si l'inscription fonctionne
 * À exécuter sur le serveur
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test d'inscription\n";
echo "====================\n\n";

// Charger config.php
require_once __DIR__ . '/../config.php';

echo "1. Vérification de la connexion à la base de données...\n";
if (isset($pdo) && $pdo instanceof PDO) {
    echo "   ✅ Connexion PDO disponible\n\n";
} else {
    echo "   ❌ Connexion PDO non disponible\n";
    if (isset($dbUnavailable) && $dbUnavailable) {
        echo "   ⚠️  Base de données marquée comme indisponible\n";
        if (isset($dbErrorMessage)) {
            echo "   Erreur: " . $dbErrorMessage . "\n";
        }
    }
    echo "\n";
    exit(1);
}

echo "2. Vérification du mode read-only...\n";
$dbReadOnly = isset($dbReadOnly) ? $dbReadOnly : (filter_var(getenv('DB_READ_ONLY') ?: 'false', FILTER_VALIDATE_BOOLEAN));
if ($dbReadOnly) {
    echo "   ⚠️  Mode read-only activé - l'inscription sera bloquée\n\n";
} else {
    echo "   ✅ Mode read-only désactivé\n\n";
}

echo "3. Vérification de la table Users...\n";
try {
    $tables = $pdo->query("SHOW TABLES LIKE 'Users'")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "   ❌ Table 'Users' n'existe pas !\n\n";
        exit(1);
    }
    echo "   ✅ Table 'Users' existe\n";
    
    // Vérifier la structure de la table
    $columns = $pdo->query("SHOW COLUMNS FROM Users")->fetchAll(PDO::FETCH_COLUMN);
    $requiredColumns = ['Id', 'Username', 'Email', 'PasswordHash', 'Role', 'UserLevel'];
    $missingColumns = array_diff($requiredColumns, $columns);
    if (!empty($missingColumns)) {
        echo "   ⚠️  Colonnes manquantes: " . implode(', ', $missingColumns) . "\n";
    } else {
        echo "   ✅ Toutes les colonnes requises sont présentes\n";
    }
    echo "\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "4. Test d'insertion d'un utilisateur de test...\n";
try {
    $testUsername = 'test_' . time();
    $testEmail = $testUsername . '@example.com';
    $testPasswordHash = password_hash('test123', PASSWORD_DEFAULT);
    $testRole = 'student';
    $testLevel = '6ème';
    
    // Vérifier si l'utilisateur de test existe déjà
    $checkStmt = $pdo->prepare("SELECT Id FROM Users WHERE Username = ? OR Email = ? LIMIT 1");
    $checkStmt->execute([$testUsername, $testEmail]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        echo "   ⚠️  Utilisateur de test existe déjà, suppression...\n";
        $deleteStmt = $pdo->prepare("DELETE FROM Users WHERE Username = ?");
        $deleteStmt->execute([$testUsername]);
    }
    
    // Insérer l'utilisateur de test
    $insertStmt = $pdo->prepare("
        INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $insertStmt->execute([$testUsername, $testEmail, $testPasswordHash, $testRole, $testLevel]);
    
    $userId = $pdo->lastInsertId();
    if ($userId) {
        echo "   ✅ Utilisateur de test créé avec succès (ID: $userId)\n";
        
        // Nettoyer : supprimer l'utilisateur de test
        $deleteStmt = $pdo->prepare("DELETE FROM Users WHERE Id = ?");
        $deleteStmt->execute([$userId]);
        echo "   ✅ Utilisateur de test supprimé\n\n";
    } else {
        echo "   ❌ Impossible de récupérer l'ID de l'utilisateur créé\n\n";
    }
    
} catch (PDOException $e) {
    echo "   ❌ Erreur lors de l'insertion: " . $e->getMessage() . "\n";
    echo "   Code erreur: " . $e->getCode() . "\n\n";
    
    // Vérifier les permissions
    if ($e->getCode() == 42000 || strpos($e->getMessage(), 'INSERT') !== false) {
        echo "   💡 Problème de permissions : l'utilisateur MySQL n'a peut-être pas les droits INSERT\n";
    }
}

echo "5. Vérification de la table UserProgress...\n";
try {
    $tables = $pdo->query("SHOW TABLES LIKE 'UserProgress'")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "   ⚠️  Table 'UserProgress' n'existe pas (non bloquant)\n\n";
    } else {
        echo "   ✅ Table 'UserProgress' existe\n\n";
    }
} catch (PDOException $e) {
    echo "   ⚠️  Erreur: " . $e->getMessage() . " (non bloquant)\n\n";
}

echo "✅ Test terminé\n";
echo "\n💡 Si tous les tests passent, l'inscription devrait fonctionner.\n";
echo "   Si l'inscription échoue toujours, vérifiez les logs d'erreur PHP.\n";
?>
