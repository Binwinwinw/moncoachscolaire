<?php
/**
 * Script de test de connexion à la base de production
 * Pour diagnostiquer les problèmes de connexion
 */

// Charger le .env.production
$envFile = __DIR__ . '/../.env.production';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

$host = getenv('PROD_DB_HOST') ?: 'localhost';
$user = getenv('PROD_DB_USERNAME');
$pass = getenv('PROD_DB_PASSWORD');
$db = getenv('PROD_DB_DATABASE');

echo "🔍 Test de connexion à la base de production\n";
echo "==========================================\n\n";

echo "Configuration:\n";
echo "  Host: $host\n";
echo "  User: $user\n";
echo "  Password: " . (empty($pass) ? '(vide)' : str_repeat('*', min(strlen($pass), 10))) . "\n";
echo "  Database: $db\n\n";

// Test avec différents hosts possibles pour Hostinger
$possibleHosts = [$host, '127.0.0.1', 'localhost'];
if (strpos($user, 'u936396612_') === 0) {
    // Pour Hostinger, essayer aussi avec l'IP ou le host spécifique
    $possibleHosts[] = 'mysql.hostinger.com';
}

$connected = false;
$workingHost = null;

foreach ($possibleHosts as $testHost) {
    echo "Test avec host: $testHost...\n";
    try {
        $pdo = new PDO("mysql:host=$testHost;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->query("SELECT 1");
        echo "  ✅ Connexion réussie avec host: $testHost\n";
        $connected = true;
        $workingHost = $testHost;
        break;
    } catch (PDOException $e) {
        echo "  ❌ Échec avec $testHost: " . $e->getMessage() . "\n";
    }
}

if (!$connected) {
    echo "\n❌ Aucune connexion réussie avec les hosts testés.\n";
    echo "\n💡 Vérifiez dans votre panneau Hostinger:\n";
    echo "   1. Allez dans 'Bases de données MySQL'\n";
    echo "   2. Vérifiez le 'Host' exact (peut être une IP ou un nom d'hôte spécifique)\n";
    echo "   3. Vérifiez que le mot de passe est correct\n";
    echo "   4. Vérifiez que l'utilisateur existe bien\n\n";
    exit(1);
}

// Test 1: Connexion sans spécifier la base
echo "\nTest 1: Connexion sans spécifier la base de données...\n";
try {
    $pdo = new PDO("mysql:host=$workingHost;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "  ✅ Connexion réussie (sans base)\n";
    
    // Lister les bases disponibles
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    echo "  📋 Bases de données disponibles:\n";
    foreach ($databases as $database) {
        $marker = ($database === $db) ? " ← CIBLE" : "";
        echo "     - $database$marker\n";
    }
    
} catch (PDOException $e) {
    echo "  ❌ Erreur: " . $e->getMessage() . "\n";
    echo "\n  💡 Le problème vient de la connexion MySQL elle-même.\n";
    echo "     Vérifiez:\n";
    echo "     - Le host est correct (peut-être pas 'localhost')\n";
    echo "     - Le username est correct\n";
    echo "     - Le mot de passe est correct\n";
    exit(1);
}

// Test 2: Connexion avec la base spécifiée
echo "\nTest 2: Connexion avec la base de données '$db'...\n";
try {
    $pdo = new PDO("mysql:host=$workingHost;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "  ✅ Connexion réussie (avec base)\n";
    
    // Lister les tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "  📋 Tables trouvées: " . count($tables) . "\n";
    if (!empty($tables)) {
        foreach ($tables as $table) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "     - $table: $count ligne(s)\n";
        }
    }
    
} catch (PDOException $e) {
    echo "  ❌ Erreur: " . $e->getMessage() . "\n";
    echo "\n  💡 Le problème vient de la base de données spécifiée.\n";
    echo "     Vérifiez:\n";
    echo "     - Le nom de la base de données est correct\n";
    echo "     - L'utilisateur a les permissions sur cette base\n";
    exit(1);
}

echo "\n✅ Tous les tests de connexion ont réussi !\n";
?>
