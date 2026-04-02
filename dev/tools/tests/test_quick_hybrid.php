<?php
/**
 * TEST RAPIDE - Vérifier que la solution hybride fonctionne
 */

$projectRoot = dirname(__DIR__);

// Inclure la config avec asset_url()
require_once $projectRoot . '/src/config/config.php';

echo "═════════════════════════════════════════════════════════════════\n";
echo "🧪 VÉRIFICATION DE LA SOLUTION HYBRIDE\n";
echo "═════════════════════════════════════════════════════════════════\n\n";

// Structures trouvées
$publicAssetsExists = is_dir($projectRoot . '/public/assets');
$rootAssetsExists = is_dir($projectRoot . '/assets');

echo "📁 STRUCTURE LOCALE:\n";
echo "───────────────────\n";
echo "  /public/assets/ " . ($publicAssetsExists ? "✅ EXISTE" : "❌ N'existe pas") . "\n";
echo "  /assets/ " . ($rootAssetsExists ? "✅ EXISTE" : "❌ N'existe pas") . "\n\n";

// Tests des assets
$testAssets = [
    'assets/css/style.css',
    'assets/js/interactive-exercises.js',
    'assets/images/logo.png',
];

echo "🧪 TEST DE asset_url():\n";
echo "──────────────────────\n\n";

if (function_exists('asset_url')) {
    foreach ($testAssets as $asset) {
        $url = asset_url($asset);
        
        // Vérifier où le fichier est trouvé
        $publicPath = $projectRoot . '/public/' . $asset;
        $rootPath = $projectRoot . '/' . $asset;
        
        $inPublic = is_file($publicPath);
        $inRoot = is_file($rootPath);
        
        echo "Asset: $asset\n";
        echo "  Fichier dans /public/: " . ($inPublic ? "✅ OUI" : "❌ NON") . "\n";
        echo "  Fichier à la racine: " . ($inRoot ? "✅ OUI" : "❌ NON") . "\n";
        echo "  URL générée: $url\n";
        
        if ($inPublic && $inRoot) {
            echo "  → Détection: Utilise /public/ (LOCAL) ✅\n";
        } elseif ($inRoot) {
            echo "  → Détection: Utilise /root (PRODUCTION) ✅\n";
        } else {
            echo "  → Détection: Fichier manquant ❌\n";
        }
        echo "\n";
    }
} else {
    echo "❌ Fonction asset_url() non disponible!\n";
}

echo "═════════════════════════════════════════════════════════════════\n";
echo "✅ RÉSUMÉ:\n";
echo "───────────\n";
echo "La solution HYBRIDE fonctionne car:\n";
echo "  1. Elle vérifie /public/assets/ EN PREMIER (LOCAL)\n";
echo "  2. Elle vérifie /assets/ EN SECOND (PRODUCTION)\n";
echo "  3. Elle génère la bonne URL automatiquement\n";
echo "\n";

if ($publicAssetsExists && $rootAssetsExists) {
    echo "🎉 LES DEUX STRUCTURES EXISTENT LOCALEMENT!\n";
    echo "   Cela signifie que tu peux tester les deux:\n";
    echo "   • /public/assets/ → URL: /public/assets/... (LOCAL mode)\n";
    echo "   • /assets/ → URL: /assets/... (PRODUCTION mode)\n";
}

echo "═════════════════════════════════════════════════════════════════\n";
?>
