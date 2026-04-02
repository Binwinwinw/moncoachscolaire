<?php
/**
 * Script de diagnostic pour identifier les problèmes de connexion DB
 * À exécuter sur le serveur pour diagnostiquer l'erreur 400
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔍 Diagnostic Base de Données\n";
echo "=============================\n\n";

// 1. Vérifier le fichier .env
echo "1. Vérification du fichier .env...\n";
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    echo "   ✅ Fichier .env trouvé\n";
    $envContent = file_get_contents($envFile);
    
    // Extraire les valeurs
    preg_match('/DB_HOST=(.+)/', $envContent, $hostMatch);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $userMatch);
    preg_match('/DB_PASSWORD=(.+)/', $envContent, $passMatch);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbMatch);
    
    $host = trim($hostMatch[1] ?? 'localhost');
    $user = trim($userMatch[1] ?? '');
    $pass = trim($passMatch[1] ?? '');
    $db = trim($dbMatch[1] ?? '');
    
    echo "   Host: $host\n";
    echo "   User: $user\n";
    echo "   Database: $db\n";
    echo "   Password: " . (empty($pass) ? '(vide)' : '***' . substr($pass, -2)) . "\n\n";
} else {
    echo "   ❌ Fichier .env non trouvé !\n\n";
    exit(1);
}

// 2. Charger config.php pour voir comment il charge le .env
echo "2. Test de chargement via config.php...\n";
require_once __DIR__ . '/../config.php';

if (isset($pdo) && $pdo instanceof PDO) {
    echo "   ✅ Connexion PDO créée via config.php\n\n";
} else {
    echo "   ❌ Pas de connexion PDO dans config.php\n";
    if (isset($dbUnavailable) && $dbUnavailable) {
        echo "   ⚠️  Base de données marquée comme indisponible\n";
        if (isset($dbErrorMessage)) {
            echo "   Erreur: " . $dbErrorMessage . "\n";
        }
    }
    echo "\n";
}

// 3. Tester la connexion directement
echo "3. Test de connexion directe...\n";
try {
    $testPdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $testPdo->query("SELECT 1");
    echo "   ✅ Connexion directe réussie\n\n";
    
    // 4. Vérifier les tables
    echo "4. Vérification des tables...\n";
    $tables = $testPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "   Tables trouvées: " . count($tables) . "\n";
    
    if (empty($tables)) {
        echo "   ❌ Aucune table dans la base de données !\n";
        echo "   💡 La base de données est vide. Il faut importer les données.\n\n";
    } else {
        echo "   Tables:\n";
        foreach ($tables as $table) {
            $count = $testPdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "     - $table: $count ligne(s)\n";
        }
        echo "\n";
        
        // 5. Vérifier spécifiquement la table Exercises
        if (in_array('Exercises', $tables)) {
            echo "5. Vérification de la table Exercises...\n";
            $exerciseCount = $testPdo->query("SELECT COUNT(*) FROM Exercises")->fetchColumn();
            echo "   Nombre d'exercices: $exerciseCount\n";
            
            if ($exerciseCount == 0) {
                echo "   ❌ La table Exercises est vide !\n";
                echo "   💡 C'est probablement la cause de l'erreur 400.\n";
            } else {
                // Vérifier les matières disponibles
                $subjects = $testPdo->query("SELECT DISTINCT Subject FROM Exercises")->fetchAll(PDO::FETCH_COLUMN);
                echo "   Matières trouvées: " . count($subjects) . "\n";
                foreach ($subjects as $subject) {
                    $count = $testPdo->query("SELECT COUNT(*) FROM Exercises WHERE Subject = ?", [$subject])->fetchColumn();
                    echo "     - $subject: $count exercice(s)\n";
                }
            }
            echo "\n";
        } else {
            echo "   ❌ Table 'Exercises' non trouvée !\n\n";
        }
    }
    
    // 6. Tester l'API get_exercises.php
    echo "6. Test de l'API get_exercises.php...\n";
    $_GET['action'] = 'subjects';
    $_GET['level'] = '6ème';
    
    ob_start();
    try {
        include __DIR__ . '/../api/get_exercises.php';
        $output = ob_get_clean();
        echo "   Réponse API:\n";
        echo "   $output\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "   ❌ Erreur API: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "   ❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "\n   💡 Vérifiez:\n";
    echo "      - Le fichier .env contient les bonnes informations\n";
    echo "      - L'utilisateur MySQL a les permissions\n";
    echo "      - La base de données existe\n";
}

echo "\n✅ Diagnostic terminé\n";
?>
