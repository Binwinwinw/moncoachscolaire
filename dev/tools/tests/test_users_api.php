<?php
/**
 * Script de test pour diagnostiquer le problème de chargement des utilisateurs
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/admin_auth.php';

echo "=== Test de l'API Users ===\n\n";

// 1. Vérifier la connexion PDO
if (!isset($pdo) || !$pdo) {
    echo "❌ PDO non disponible\n";
    exit(1);
}
echo "✅ PDO disponible\n";

// 2. Vérifier l'authentification admin
if (!function_exists('isAdmin')) {
    echo "❌ Fonction isAdmin() non disponible\n";
    exit(1);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isAdmin()) {
    echo "⚠️  Vous n'êtes pas connecté en tant qu'admin\n";
    echo "   (Ce script nécessite une session admin active)\n";
} else {
    echo "✅ Authentification admin OK\n";
}

// 3. Vérifier l'existence de la table Users
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'Users'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        // Essayer avec minuscule
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        $tableExists = $stmt->rowCount() > 0;
        if ($tableExists) {
            echo "⚠️  Table 'users' (minuscule) trouvée au lieu de 'Users'\n";
        } else {
            echo "❌ Aucune table Users/users trouvée\n";
            exit(1);
        }
    } else {
        echo "✅ Table 'Users' trouvée\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification de la table: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Compter les utilisateurs
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = (int)($result['total'] ?? 0);
    echo "✅ Nombre d'utilisateurs dans Users: $total\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du comptage: " . $e->getMessage() . "\n";
    exit(1);
}

// 5. Tester la requête complète (comme dans l'API)
try {
    $stmt = $pdo->prepare("
        SELECT 
            Id, 
            Username, 
            Email, 
            Role, 
            COALESCE(UserLevel, '') as UserLevel, 
            CreatedAt,
            COALESCE((SELECT COUNT(*) FROM UserProgress WHERE UserId = Users.Id), 0) as ProgressCount,
            COALESCE((SELECT SUM(XP) FROM UserProgress WHERE UserId = Users.Id), 0) as TotalXP
        FROM Users
        ORDER BY CreatedAt DESC
        LIMIT 10
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n✅ Requête réussie - " . count($users) . " utilisateurs récupérés\n\n";
    
    if (count($users) > 0) {
        echo "Exemple de données:\n";
        echo "ID | Username | Email | Role | UserLevel\n";
        echo str_repeat("-", 60) . "\n";
        foreach (array_slice($users, 0, 5) as $user) {
            echo sprintf(
                "%d | %s | %s | %s | %s\n",
                $user['Id'] ?? 'N/A',
                $user['Username'] ?? 'N/A',
                substr($user['Email'] ?? 'N/A', 0, 20),
                $user['Role'] ?? 'N/A',
                $user['UserLevel'] ?? 'N/A'
            );
        }
    } else {
        echo "⚠️  Aucun utilisateur trouvé dans la table\n";
    }
} catch (PDOException $e) {
    echo "❌ Erreur PDO lors de la requête: " . $e->getMessage() . "\n";
    echo "   Code: " . $e->getCode() . "\n";
    
    // Essayer avec une requête simplifiée
    echo "\n🔄 Tentative avec requête simplifiée...\n";
    try {
        $stmt = $pdo->prepare("
            SELECT 
                Id, 
                Username, 
                Email, 
                Role, 
                COALESCE(UserLevel, '') as UserLevel, 
                CreatedAt
            FROM Users
            ORDER BY CreatedAt DESC
            LIMIT 10
        ");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✅ Requête simplifiée réussie - " . count($users) . " utilisateurs\n";
    } catch (Exception $e2) {
        echo "❌ Erreur même avec requête simplifiée: " . $e2->getMessage() . "\n";
    }
}

// 6. Tester la structure JSON (comme l'API)
echo "\n=== Test format JSON ===\n";
$testData = [
    'success' => true,
    'data' => $users ?? [],
    'pagination' => [
        'page' => 1,
        'limit' => 20,
        'total' => $total,
        'pages' => $total > 0 ? ceil($total / 20) : 0
    ]
];

$json = json_encode($testData, JSON_UNESCAPED_UNICODE);
if ($json === false) {
    echo "❌ Erreur lors de l'encodage JSON: " . json_last_error_msg() . "\n";
} else {
    echo "✅ JSON valide (" . strlen($json) . " caractères)\n";
    echo "   Aperçu: " . substr($json, 0, 100) . "...\n";
}

echo "\n=== Test terminé ===\n";

