<?php
/**
 * Script pour corriger les noms de tables (users -> Users, etc.)
 * Accessible via : https://moncoachscolaire.fr/index.php?page=fix_table_names
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$directAccess = (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__));

if ($directAccess) {
    header('Content-Type: text/plain; charset=utf-8');
} else {
    $page_title = 'Correction Noms Tables';
    echo '<main class="main-content"><section><pre style="background: #f5f5f5; padding: 20px; border-radius: 8px; overflow-x: auto; font-family: monospace; white-space: pre-wrap;">';
}

echo "🔧 Correction des noms de tables\n";
echo "================================\n\n";

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

// Mapping des tables : nom actuel (minuscules) -> nom attendu (majuscules)
$tableMappings = [
    'users' => 'Users',
    'userprogress' => 'UserProgress',
    'userpowers' => 'UserPowers',
    'userachievements' => 'UserAchievements',
    'exerciseresponses' => 'ExerciseResponses',
    'exercises' => 'Exercises',
    'powers' => 'Powers',
    'achievements' => 'Achievements'
];

echo "1. Liste des tables actuelles...\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    if (!$directAccess) {
        echo '</pre></section></main>';
    }
    exit(1);
}
echo "\n";

echo "2. Renommage des tables...\n";
$renamed = 0;
$skipped = 0;

foreach ($tableMappings as $oldName => $newName) {
    // Vérifier si la table en minuscules existe
    $oldExists = in_array($oldName, $tables);
    $newExists = in_array($newName, $tables);
    
    if ($oldExists && !$newExists) {
        echo "   🔧 Renommage: $oldName → $newName\n";
        try {
            $pdo->exec("RENAME TABLE `$oldName` TO `$newName`");
            echo "      ✅ Succès\n";
            $renamed++;
        } catch (PDOException $e) {
            echo "      ❌ Erreur: " . $e->getMessage() . "\n";
        }
    } elseif ($newExists) {
        echo "   ✅ $newName existe déjà (pas de renommage nécessaire)\n";
        $skipped++;
    } elseif (!$oldExists && !$newExists) {
        echo "   ⚠️  $oldName n'existe pas (table manquante)\n";
    }
}
echo "\n";

echo "3. Vérification finale...\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Tables après renommage: " . count($tables) . "\n";
    
    $requiredTables = ['Users', 'UserProgress', 'Exercises'];
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "   ✅ $table existe\n";
        } else {
            echo "   ❌ $table MANQUANTE\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}
echo "\n";

echo "4. Test d'insertion dans Users...\n";
try {
    $testUsername = 'test_' . time();
    $stmt = $pdo->prepare("
        INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$testUsername, $testUsername . '@test.com', password_hash('test', PASSWORD_DEFAULT), 'student', '6eme']);
    $userId = $pdo->lastInsertId();
    echo "   ✅ Insertion réussie (ID: $userId)\n";
    
    // Nettoyer
    $stmt = $pdo->prepare("DELETE FROM Users WHERE Id = ?");
    $stmt->execute([$userId]);
    echo "   ✅ Utilisateur de test supprimé\n";
} catch (PDOException $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}
echo "\n";

if ($renamed > 0) {
    echo "✅ $renamed table(s) renommée(s) avec succès !\n";
    echo "💡 Vous pouvez maintenant tester l'inscription et la connexion.\n";
} else {
    echo "✅ Toutes les tables ont déjà les bons noms.\n";
}

echo "\n⚠️  SUPPRIMEZ CE FICHIER après les tests pour des raisons de sécurité !\n";

if (!$directAccess) {
    echo '</pre></section></main>';
}
?>
