<?php
/**
 * Test Router Logs
 * Simule ce que le routeur fait
 */

echo "=== TEST ROUTEUR API LOGS ===\n\n";

// Simuler $_GET['page']
$_GET['page'] = 'api/admin/logs';
$_SERVER['REQUEST_URI'] = '/api/admin/logs';

// Root du projet
$root = dirname(dirname(__FILE__)); // va à la racine

echo "1. Chemins:\n";
echo "   root = $root\n";
echo "   page = " . $_GET['page'] . "\n\n";

// Chercher le fichier API
$pageParam = $_GET['page'];

// Regex pour détecter API
if (preg_match('#^api/(.+)$#', $pageParam, $matches)) {
    $apiPath = $matches[1];
    echo "2. Détection API:\n";
    echo "   apiPath = $apiPath\n";
    
    // Nettoyer l'extension
    $apiPath = preg_replace('#\.php$#', '', $apiPath);
    echo "   apiPath (sans .php) = $apiPath\n";
    
    // Construire le chemin
    $apiFile = $root . '/src/api/' . $apiPath . '.php';
    echo "   Chemin cherché: $apiFile\n";
    
    // Vérifier existence
    echo "\n3. Vérifications:\n";
    echo "   file_exists() = " . (file_exists($apiFile) ? "✅ OUI" : "❌ NON") . "\n";
    echo "   is_file() = " . (is_file($apiFile) ? "✅ OUI" : "❌ NON") . "\n";
    
    if (file_exists($apiFile)) {
        echo "   Taille = " . filesize($apiFile) . " bytes\n";
        echo "   Contenu (1ères 100 chars):\n";
        echo "   " . substr(file_get_contents($apiFile), 0, 100) . "\n";
    }
    
    // Vérifier sécurité
    $realApiFile = realpath($apiFile);
    $realSrcApi = realpath($root . '/src/api');
    
    echo "\n4. Vérification sécurité:\n";
    echo "   realpath(apiFile) = " . ($realApiFile ? $realApiFile : "NULL") . "\n";
    echo "   realpath(root/src/api) = " . ($realSrcApi ? $realSrcApi : "NULL") . "\n";
    
    if ($realApiFile && $realSrcApi) {
        $isInSrcApi = strpos($realApiFile, $realSrcApi) === 0;
        echo "   Est dans src/api = " . ($isInSrcApi ? "✅ OUI" : "❌ NON") . "\n";
    }
    
} else {
    echo "❌ Pas détecté comme API\n";
}

echo "\n=== FIN TEST ===\n";
?>
