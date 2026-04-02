<?php
/**
 * TEST PRODUCTION - Simulation de l'environnement production
 * Vérifie comment asset_url() génère les URLs en production
 */

// Simuler l'environnement production
$_SERVER['HTTP_HOST'] = 'moncoachscolaire.fr';
$_SERVER['SERVER_NAME'] = 'moncoachscolaire.fr';
$_SERVER['HTTPS'] = 'on';

// Charger config
$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/src/config/config.php';

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🌐 SIMULATION ENVIRONNEMENT PRODUCTION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📍 ENVIRONNEMENT SIMULÉ:\n";
echo "─────────────────────────\n";
echo "  Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "  HTTPS: " . ($_SERVER['HTTPS'] ?? 'off') . "\n";
echo "  APP_ENV: " . (getenv('APP_ENV') ?: 'non défini') . "\n";
echo "\n";

// Vérifier la structure des dossiers en production
echo "📁 STRUCTURE ATTENDUE EN PRODUCTION:\n";
echo "────────────────────────────────────\n";
echo "  /moncoachscolaire.fr/\n";
echo "  └── public/\n";
echo "      └── assets/\n";
echo "          ├── css/\n";
echo "          │   └── style.css ← ICI!\n";
echo "          ├── js/\n";
echo "          └── images/\n";
echo "\n";

// Tester asset_url()
echo "🔗 URLS GÉNÉRÉES PAR asset_url():\n";
echo "──────────────────────────────────\n";

if (function_exists('asset_url')) {
    $testAssets = [
        'assets/css/style.css',
        'assets/js/interactive-exercises.js',
        'assets/images/logo.png',
    ];
    
    foreach ($testAssets as $asset) {
        $url = asset_url($asset);
        echo "  Asset: $asset\n";
        echo "  URL générée: $url\n";
        echo "  URL complète: https://moncoachscolaire.fr$url\n";
        echo "\n";
    }
} else {
    echo "  ❌ Fonction asset_url() NON disponible\n\n";
}

// Vérifier detectBaseUrl()
echo "🔧 DÉTECTION BASE URL:\n";
echo "──────────────────────\n";

if (function_exists('detectBaseUrl')) {
    $baseUrl = detectBaseUrl();
    echo "  detectBaseUrl(): $baseUrl\n";
    echo "  Attendu en production: https://moncoachscolaire.fr\n";
} else {
    echo "  ❌ Fonction detectBaseUrl() NON disponible\n";
}

// Vérifier les globales
echo "\n";
echo "🌐 VARIABLES GLOBALES:\n";
echo "──────────────────────\n";
echo "  \$GLOBALS['baseUrl']: " . ($GLOBALS['baseUrl'] ?? 'non défini') . "\n";
echo "  \$GLOBALS['projectRoot']: " . ($GLOBALS['projectRoot'] ?? 'non défini') . "\n";

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "✅ RÉSULTAT ATTENDU EN PRODUCTION:\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "Si la structure est:\n";
echo "  moncoachscolaire.fr/public/assets/css/style.css\n";
echo "\n";
echo "Alors asset_url() DEVRAIT générer:\n";
echo "  → /public/assets/css/style.css ✅\n";
echo "\n";
echo "Et l'URL complète sera:\n";
echo "  → https://moncoachscolaire.fr/public/assets/css/style.css ✅\n";
echo "\n";

echo "🎯 POINT D'ENTRÉE EN PRODUCTION:\n";
echo "─────────────────────────────────\n";
echo "  Si l'appli est servie depuis: /public_html/\n";
echo "  Alors le dossier public/ doit être accessible à:\n";
echo "    https://moncoachscolaire.fr/public/\n";
echo "\n";

echo "⚠️ SI LES CSS NE SE CHARGENT PAS:\n";
echo "──────────────────────────────────\n";
echo "  1. Vérifier que /public/ est accessible via le navigateur\n";
echo "  2. Tester: https://moncoachscolaire.fr/public/assets/css/style.css\n";
echo "  3. Si 404, vérifier les permissions du dossier /public/\n";
echo "  4. Vérifier le .htaccess pour les règles de réécriture\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════════\n";
?>
