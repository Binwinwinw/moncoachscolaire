<?php
/**
 * Test de diagnostic - À uploader en prod quand besoin
 * Accès: https://moncoachscolaire.fr/test_diag.php
 * À SUPPRIMER après diagnostic !
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Détecter la racine du projet (depuis public_html/)
$rootPath = dirname(__FILE__);

echo "<h2>⚡ Diagnostic Prod</h2>\n<pre>";

// Test 1: Session
session_start();
echo "1️⃣ SESSION:\n";
echo "   user_id: " . ($_SESSION['user_id'] ?? 'MISSING') . "\n";
echo "   user_role: " . ($_SESSION['user_role'] ?? 'MISSING') . "\n";
echo "   logged_in: " . ($_SESSION['logged_in'] ?? 'MISSING') . "\n\n";

// Test 2: Chemins critiques
echo "2️⃣ FICHIERS:\n";
$paths = [
    'src/config/config.php',
    'src/database/connection.php',
    'src/includes/admin_auth.php',
    'src/api/admin/stats.php',
    'db/connection.php',
    '.htaccess'
];

foreach ($paths as $file) {
    $fullPath = $rootPath . '/' . $file;
    $exists = file_exists($fullPath) ? '✅' : '❌';
    echo "   $exists $file\n";
}

echo "\n3️⃣ CHARGEMENT CONFIG & DB:\n";

// Charger config
$configPath = $rootPath . '/src/config/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
    echo "   ✅ Config chargée\n";
} else {
    echo "   ❌ Config introuvable\n";
}

// Charger connexion
$connPath = $rootPath . '/src/database/connection.php';
$legacyConnPath = $rootPath . '/db/connection.php';
if (file_exists($connPath)) {
    require_once $connPath;
    echo "   ✅ Connection chargée (src/)\n";
} elseif (file_exists($legacyConnPath)) {
    require_once $legacyConnPath;
    echo "   ✅ Connection chargée (db/ legacy)\n";
} else {
    echo "   ❌ Connection introuvable\n";
}

// Test PDO
echo "\n4️⃣ BASE DE DONNÉES:\n";
if (isset($pdo) && $pdo) {
    echo "   ✅ PDO connecté\n";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM Users");
        $result = $stmt->fetch();
        echo "   ✅ Users table: " . ($result['cnt'] ?? '?') . " utilisateurs\n";
    } catch (Exception $e) {
        echo "   ❌ Query failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ PDO not set\n";
}

// Charger auth
echo "\n5️⃣ AUTHENTIFICATION:\n";
$authPath = $rootPath . '/src/includes/admin_auth.php';
if (file_exists($authPath)) {
    require_once $authPath;
    echo "   ✅ Auth chargée\n";
    
    if (function_exists('isAdmin')) {
        $admin = isAdmin();
        echo "   " . ($admin ? '✅' : '❌') . " isAdmin() = " . ($admin ? 'TRUE' : 'FALSE') . "\n";
    }
} else {
    echo "   ❌ Auth introuvable\n";
}

// Test API réelle
echo "\n6️⃣ TEST API STATS:\n";
$apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/api/admin/stats.php';
echo "   URL: $apiUrl\n";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Cookie: PHPSESSID=' . session_id()
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "   HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    echo "   ✅ API responding\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "   Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "   ❌ API Error (code $httpCode)\n";
    echo "   Response: " . substr($response, 0, 200) . "...\n";
}

echo "\n✅ Diagnostic terminé\n";
echo "⚠️  N'OUBLIE PAS DE SUPPRIMER CE FICHIER\n";
echo "</pre>";
