<?php
// Test du routing API
$root = dirname(__DIR__, 2);


// Test principal : endpoint critique admin_create_user
$pageParam = 'api/admin_create_user';

echo "=== TEST ROUTING API ===\n\n";

// Test 1 : Regex extraction
if (preg_match('#^api/(.+)$#', $pageParam, $matches)) {
    $apiPath = $matches[1];
    echo "✅ Regex match: api/" . $apiPath . "\n";

    // Test 2 : Nettoyer .php
    $apiPath = preg_replace('#\.php$#', '', $apiPath);
    echo "✅ Cleaned path: " . $apiPath . "\n";

    // Test 3 : Construire chemin fichier
    $apiFile = $root . '/src/api/' . $apiPath . '.php';
    echo "✅ API file path: " . $apiFile . "\n";
    echo "   File exists: " . (file_exists($apiFile) ? 'YES' : 'NO') . "\n";

    // Test 4 : Vérifier realpath
    $realApiFile = realpath($apiFile);
    $realSrcApi = realpath($root . '/src/api');

    echo "✅ realApiFile: " . ($realApiFile ?: 'FALSE') . "\n";
    echo "✅ realSrcApi: " . ($realSrcApi ?: 'FALSE') . "\n";

    if ($realApiFile && $realSrcApi && strpos($realApiFile, $realSrcApi) === 0 && is_file($realApiFile)) {
        echo "\n✅✅✅ ROUTING DEVRAIT FONCTIONNER !\n";
    } else {
        echo "\n❌❌❌ ROUTING BLOQUÉ !\n";
        if (!$realApiFile) echo "   - realApiFile est FALSE\n";
        if (!$realSrcApi) echo "   - realSrcApi est FALSE\n";
        if ($realApiFile && $realSrcApi && strpos($realApiFile, $realSrcApi) !== 0) {
            echo "   - Fichier hors de src/api/\n";
        }
        if ($realApiFile && !is_file($realApiFile)) {
            echo "   - Ce n'est pas un fichier régulier\n";
        }
    }
} else {
    echo "❌ Regex ne match pas !\n";
}

// Test complémentaire : endpoint logs.php (admin)
echo "\n\n=== TEST LOGS.PHP ===\n\n";
$pageParam = 'api/admin/logs';
if (preg_match('#^api/(.+)$#', $pageParam, $matches)) {
    $apiPath = $matches[1];
    $apiPath = preg_replace('#\.php$#', '', $apiPath);
    $apiFile = $root . '/src/api/' . $apiPath . '.php';
    echo "File path: " . $apiFile . "\n";
    echo "Exists: " . (file_exists($apiFile) ? 'YES' : 'NO') . "\n";
    $realApiFile = realpath($apiFile);
    echo "realpath: " . ($realApiFile ?: 'FALSE') . "\n";
}
