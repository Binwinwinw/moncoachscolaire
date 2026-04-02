<?php
/**
 * Vérification que les changements sont en place
 * Upload à la racine de public_html/ en prod
 * Accès: https://moncoachscolaire.fr/verify.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>🔍 Vérification des changements</h2>\n<pre>";

$rootPath = dirname(__FILE__);

// Test 1: Vérifier que public/index.php a le routage API
echo "1️⃣ VÉRIFIER ROUTAGE API DANS public/index.php:\n";
$indexPath = $rootPath . '/public/index.php';
if (file_exists($indexPath)) {
    $content = file_get_contents($indexPath);
    if (strpos($content, 'ROUTAGE API') !== false && strpos($content, 'apiPath') !== false) {
        echo "   ✅ Le code API est présent dans public/index.php\n";
    } else {
        echo "   ❌ Le code API est ABSENT de public/index.php\n";
        echo "   → Besoin de ré-uploader public/index.php\n";
    }
} else {
    echo "   ❌ public/index.php INTROUVABLE\n";
}

// Test 2: Vérifier que .htaccess a la bonne règle
echo "\n2️⃣ VÉRIFIER .htaccess:\n";
$htaccessPath = $rootPath . '/.htaccess';
if (file_exists($htaccessPath)) {
    $content = file_get_contents($htaccessPath);
    if (strpos($content, 'public/index.php?page=api') !== false) {
        echo "   ✅ .htaccess a la bonne règle API\n";
    } else {
        echo "   ❌ .htaccess n'a PAS la bonne règle\n";
        echo "   → Besoin de ré-uploader .htaccess\n";
        // Montrer ce qui est dedans
        if (preg_match('#rewrite.*api.*#i', $content, $m)) {
            echo "   Règle trouvée: " . trim($m[0]) . "\n";
        }
    }
} else {
    echo "   ❌ .htaccess INTROUVABLE\n";
}

// Test 3: Appeler l'API directement
echo "\n3️⃣ TEST APPEL API DIRECT:\n";
$apiUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/api/admin/stats.php';
echo "   URL: $apiUrl\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Cookie: PHPSESSID=' . session_id()
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";
echo "   Final URL: $finalUrl\n";

if ($httpCode === 200) {
    echo "   ✅ API répond en 200 OK\n";
} elseif ($httpCode === 301 || $httpCode === 302) {
    echo "   ⚠️  Redirection $httpCode (mauvais routage)\n";
} elseif ($httpCode === 403) {
    echo "   ❌ 403 Forbidden (authentification ou droits)\n";
} elseif ($httpCode === 404) {
    echo "   ❌ 404 Not Found (fichier introuvable)\n";
} else {
    echo "   ❌ Code $httpCode\n";
}

echo "   Response: " . substr($response, 0, 200) . (strlen($response) > 200 ? '...' : '') . "\n";

// Test 4: Vérifier via le routeur
echo "\n4️⃣ TEST VIA ROUTEUR (public/index.php?page=api/admin/stats):\n";
$routerUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/public/index.php?page=api/admin/stats';
echo "   URL: $routerUrl\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $routerUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Cookie: PHPSESSID=' . session_id()
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    echo "   ✅ Routeur fonctionne\n";
} else {
    echo "   ❌ Code $httpCode\n";
}
echo "   Response: " . substr($response, 0, 200) . (strlen($response) > 200 ? '...' : '') . "\n";

echo "\n⚠️  N'OUBLIE PAS DE SUPPRIMER CE FICHIER\n";
echo "</pre>";
