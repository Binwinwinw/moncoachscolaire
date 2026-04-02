<?php
/**
 * Diagnostic pour erreurs prod
 * Accès: http://localhost/tests/test_prod_debug.php
 * En prod: copier vers tests/ sur le serveur
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>🔍 Diagnostic Prod</h1>\n<pre>";

// Déterminer le chemin vers la racine (depuis tests/)
$rootPath = dirname(__DIR__);

// Test 1: Session
session_start();
echo "Session ID: " . session_id() . "\n";
echo "Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Session role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "Session data: " . json_encode($_SESSION, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Fichiers config
$configPath = $rootPath . '/src/config/config.php';
$connPath = $rootPath . '/src/database/connection.php';
$legacyConnPath = $rootPath . '/db/connection.php';
$authPath = $rootPath . '/src/includes/admin_auth.php';

echo "Root path: $rootPath\n";
echo "Config exists: " . (file_exists($configPath) ? '✅' : '❌') . " $configPath\n";
echo "Connection exists: " . (file_exists($connPath) ? '✅' : '❌') . " $connPath\n";
echo "Legacy connection exists: " . (file_exists($legacyConnPath) ? '✅' : '❌') . " $legacyConnPath\n";
echo "Auth exists: " . (file_exists($authPath) ? '✅' : '❌') . " $authPath\n\n";

// Test 3: Charger config et connexion
if (file_exists($configPath)) {
    require_once $configPath;
    echo "Config loaded ✅\n";
} else {
    echo "Config NOT FOUND ❌\n";
}

if (file_exists($connPath)) {
    require_once $connPath;
    echo "Connection loaded ✅\n";
} elseif (file_exists($legacyConnPath)) {
    require_once $legacyConnPath;
    echo "Legacy Connection loaded ✅\n";
} else {
    echo "Connection NOT FOUND ❌\n";
}

// Test 4: Vérifier PDO
if (isset($pdo) && $pdo) {
    echo "PDO connection: ✅ OK\n";
    try {
        $test = $pdo->query("SELECT COUNT(*) FROM Users");
        $count = $test->fetchColumn();
        echo "Users table accessible: ✅ ($count users)\n";
    } catch (Exception $e) {
        echo "Users table ERROR: ❌ " . $e->getMessage() . "\n";
    }
} else {
    echo "PDO connection: ❌ NOT SET OR NULL\n";
}

// Test 5: Auth function
if (file_exists($authPath)) {
    require_once $authPath;
    if (function_exists('isAdmin')) {
        echo "isAdmin function: ✅ EXISTS\n";
        echo "isAdmin() result: " . (isAdmin() ? 'TRUE (admin)' : 'FALSE (not admin)') . "\n";
    } else {
        echo "isAdmin function: ❌ NOT FOUND\n";
    }
}

// Test 6: ENV files
$envFile = $rootPath . '/.env';
$envProdFile = $rootPath . '/.env.production';
echo "\n.env exists: " . (file_exists($envFile) ? '✅' : '❌') . "\n";
echo ".env.production exists: " . (file_exists($envProdFile) ? '✅' : '❌') . "\n";

echo "</pre>";
