<?php
/**
 * Test toutes les API admin
 * Upload en prod et accède via: https://moncoachscolaire.fr/test_all_apis.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

echo "<h2>🔍 Test de toutes les API Admin</h2>\n<pre>";

// Liste des API critiques
$apis = [
    'stats.php',
    'users.php?limit=10',
    'logs.php?type=admin&limit=10',
    'parents.php?type=parents',
    'exercises_quality.php',
    'maintenance.php',
];

echo "Session: user_id=" . ($_SESSION['user_id'] ?? 'NONE') . ", role=" . ($_SESSION['user_role'] ?? 'NONE') . "\n\n";

foreach ($apis as $api) {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . '/api/admin/' . $api;
    
    echo "🔄 Testing: /api/admin/$api\n";
    echo "   URL: $url\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Cookie: PHPSESSID=' . session_id()
        ],
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ HTTP $httpCode - OK\n";
        $json = json_decode($response, true);
        if ($json && isset($json['success'])) {
            echo "   Success: " . ($json['success'] ? 'true' : 'false') . "\n";
            if (!$json['success'] && isset($json['error'])) {
                echo "   Error: " . $json['error'] . "\n";
            }
        }
    } else {
        echo "   ❌ HTTP $httpCode - FAILED\n";
        echo "   Content-Type: $contentType\n";
        echo "   Response: " . substr($response, 0, 200) . "\n";
    }
    echo "\n";
}

echo "⚠️  Supprime ce fichier après diagnostic\n";
echo "</pre>";
