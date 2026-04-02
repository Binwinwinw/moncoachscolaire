<?php
/**
 * Script de test direct de l'inscription
 * Simule une requête POST pour tester l'inscription
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Test d'inscription directe\n";
echo "============================\n\n";

// Simuler une requête POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['username'] = 'test_' . time();
$_POST['password'] = 'test123456';
$_POST['age'] = 12;
$_POST['classe'] = '6ème';

echo "Données de test:\n";
echo "  Username: " . $_POST['username'] . "\n";
echo "  Password: " . str_repeat('*', strlen($_POST['password'])) . "\n";
echo "  Age: " . $_POST['age'] . "\n";
echo "  Classe: " . $_POST['classe'] . "\n\n";

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger config.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';

echo "1. Vérification de la connexion DB...\n";
if (isset($pdo) && $pdo instanceof PDO) {
    echo "   ✅ Connexion PDO disponible\n";
    echo "   Host: " . (getenv('DB_HOST') ?: 'N/A') . "\n";
    echo "   Database: " . (getenv('DB_DATABASE') ?: 'N/A') . "\n";
    echo "   User: " . (getenv('DB_USERNAME') ?: 'N/A') . "\n";
    echo "   Environment: " . (getenv('APP_ENV') ?: 'N/A') . "\n";
    echo "   Read-only: " . (getenv('DB_READ_ONLY') ?: 'false') . "\n\n";
} else {
    echo "   ❌ Connexion PDO non disponible\n";
    if (isset($dbUnavailable) && $dbUnavailable) {
        echo "   Erreur: " . (isset($dbErrorMessage) ? $dbErrorMessage : 'Inconnue') . "\n";
    }
    exit(1);
}

echo "2. Vérification du mode read-only...\n";
$dbReadOnly = isset($dbReadOnly) ? $dbReadOnly : (filter_var(getenv('DB_READ_ONLY') ?: 'false', FILTER_VALIDATE_BOOLEAN));
if ($dbReadOnly) {
    echo "   ⚠️  Mode read-only activé - l'inscription sera bloquée\n\n";
    exit(1);
} else {
    echo "   ✅ Mode read-only désactivé\n\n";
}

echo "3. Test d'insertion dans la table Users...\n";
try {
    $username = $_POST['username'];
    $email = $username . '@example.com';
    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'student';
    $classe = $_POST['classe'];
    
    // Vérifier si l'utilisateur existe déjà
    $checkStmt = $pdo->prepare("SELECT Id FROM Users WHERE Username = ? OR Email = ? LIMIT 1");
    $checkStmt->execute([$username, $email]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        echo "   ⚠️  Utilisateur existe déjà, suppression...\n";
        $deleteStmt = $pdo->prepare("DELETE FROM Users WHERE Username = ?");
        $deleteStmt->execute([$username]);
    }
    
    // Insérer l'utilisateur
    $insertStmt = $pdo->prepare("
        INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    echo "   Exécution de l'INSERT...\n";
    $result = $insertStmt->execute([$username, $email, $passwordHash, $role, $classe]);
    
    if ($result) {
        $userId = $pdo->lastInsertId();
        echo "   ✅ Insertion réussie ! ID utilisateur: $userId\n\n";
        
        // Vérifier que l'utilisateur a bien été créé
        $verifyStmt = $pdo->prepare("SELECT * FROM Users WHERE Id = ?");
        $verifyStmt->execute([$userId]);
        $user = $verifyStmt->fetch();
        
        if ($user) {
            echo "4. Vérification de l'utilisateur créé...\n";
            echo "   ✅ Utilisateur trouvé:\n";
            echo "      ID: " . $user['Id'] . "\n";
            echo "      Username: " . $user['Username'] . "\n";
            echo "      Email: " . $user['Email'] . "\n";
            echo "      Role: " . $user['Role'] . "\n";
            echo "      Level: " . $user['UserLevel'] . "\n\n";
        }
        
        // Nettoyer : supprimer l'utilisateur de test
        echo "5. Nettoyage (suppression de l'utilisateur de test)...\n";
        $deleteStmt = $pdo->prepare("DELETE FROM Users WHERE Id = ?");
        $deleteStmt->execute([$userId]);
        echo "   ✅ Utilisateur de test supprimé\n\n";
        
        echo "✅ Test d'inscription réussi !\n";
        echo "\n💡 L'inscription devrait fonctionner. Si elle échoue sur le site,\n";
        echo "   vérifiez les logs d'erreur PHP ou activez temporairement display_errors.\n";
        
    } else {
        echo "   ❌ L'INSERT a échoué sans erreur\n";
    }
    
} catch (PDOException $e) {
    echo "   ❌ Erreur PDO: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    echo "   SQL State: " . $e->errorInfo[0] . "\n";
    
    if (isset($e->errorInfo[1])) {
        echo "   MySQL Error Code: " . $e->errorInfo[1] . "\n";
    }
    if (isset($e->errorInfo[2])) {
        echo "   MySQL Error Message: " . $e->errorInfo[2] . "\n";
    }
    
    echo "\n💡 Causes possibles:\n";
    echo "   - L'utilisateur MySQL n'a pas les permissions INSERT\n";
    echo "   - La table Users n'existe pas ou a une structure incorrecte\n";
    echo "   - Contrainte de clé étrangère ou unique violée\n";
    echo "   - Mode read-only activé\n";
}
?>
