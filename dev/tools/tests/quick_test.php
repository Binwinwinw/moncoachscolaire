<?php
/**
 * Test rapide: vérifier que tout fonctionne
 * Accès: https://moncoachscolaire.fr/tests/quick_test.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$rootPath = __DIR__ . '/..';

echo "<h2>⚡ Quick Test</h2>\n<pre>";

// Test de chargement complet comme une API
session_start();

echo "1️⃣ Vérifier session:\n";
echo "   user_id: " . ($_SESSION['user_id'] ?? 'MISSING') . "\n";
echo "   user_role: " . ($_SESSION['user_role'] ?? 'MISSING') . "\n";

echo "\n2️⃣ Charger config:\n";
$configPath = $rootPath . '/src/config/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
    echo "   ✅ Config loaded\n";
} else {
    echo "   ❌ Config NOT found: $configPath\n";
}

echo "\n3️⃣ Charger connexion DB:\n";
$connPath = $rootPath . '/src/database/connection.php';
$legacyConnPath = $rootPath . '/db/connection.php';

if (file_exists($connPath)) {
    require_once $connPath;
    echo "   ✅ Connection loaded from src/\n";
} elseif (file_exists($legacyConnPath)) {
    require_once $legacyConnPath;
    echo "   ✅ Connection loaded from db/ (legacy)\n";
} else {
    echo "   ❌ Connection NOT found\n";
    echo "      Checked: $connPath\n";
    echo "      Checked: $legacyConnPath\n";
}

echo "\n4️⃣ Vérifier PDO:\n";
if (isset($pdo) && $pdo) {
    echo "   ✅ PDO is connected\n";
    try {
        $test = $pdo->query("SELECT 1");
        echo "   ✅ DB query successful\n";
    } catch (Exception $e) {
        echo "   ❌ DB query failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ PDO is NOT set\n";
}

echo "\n5️⃣ Charger admin_auth:\n";
$authPath = $rootPath . '/src/includes/admin_auth.php';
if (file_exists($authPath)) {
    require_once $authPath;
    echo "   ✅ Auth loaded\n";
    
    echo "\n6️⃣ Vérifier isAdmin():\n";
    if (function_exists('isAdmin')) {
        $isAdmin = isAdmin();
        echo "   isAdmin() returned: " . ($isAdmin ? 'TRUE ✅' : 'FALSE ❌') . "\n";
    } else {
        echo "   ❌ isAdmin() function not found\n";
    }
} else {
    echo "   ❌ Auth NOT found: $authPath\n";
}

echo "\n✅ Test complete\n";
echo "</pre>";
