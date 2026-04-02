<?php
/**
 * OUTIL D'ANALYSE DES LIENS CSS/JS
 * Vérifie si les deux environnements (local et production) fonctionnent correctement
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================================
// 1. CONFIGURATION DES ENVIRONNEMENTS
// ============================================================================

$environments = [
    'LOCAL' => [
        'name' => 'Local (XAMPP)',
        'baseUrl' => 'http://localhost/moncoachscolaire',
        'projectRoot' => dirname(__DIR__),
        'publicFolder' => 'http://localhost/moncoachscolaire/public',
    ],
    'PRODUCTION' => [
        'name' => 'Production (Hostinger)',
        'baseUrl' => 'https://moncoachscolaire.fr',
        'projectRoot' => '/home/u936396612/domains/moncoachscolaire.fr/public_html',
        'publicFolder' => 'https://moncoachscolaire.fr/public',
    ],
];

// ============================================================================
// 2. STRUCTURE LOCALE RÉELLE
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "ANALYSE DES LIENS CSS/JS - ENVIRONNEMENTS HYBRIDES\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📁 STRUCTURE ACTUELLE DU PROJET LOCAL:\n";
echo "───────────────────────────────────────\n";

$projectRoot = dirname(__DIR__);
$structure = [
    'projectRoot' => $projectRoot,
    'public/assets/css' => $projectRoot . '/public/assets/css',
    'public/assets/js' => $projectRoot . '/public/assets/js',
    'public/assets/images' => $projectRoot . '/public/assets/images',
    'public/assets/img' => $projectRoot . '/public/assets/img',
];

foreach ($structure as $label => $path) {
    $exists = is_dir($path) ? '✅' : '❌';
    echo "  $exists  $label\n";
    
    if (is_dir($path)) {
        $files = array_slice(scandir($path), 2, 5); // Premiers 5 fichiers
        foreach ($files as $file) {
            echo "        - $file\n";
        }
        $total = count(array_slice(scandir($path), 2));
        if ($total > 5) {
            echo "        ... et " . ($total - 5) . " autres fichiers\n";
        }
    }
}

// ============================================================================
// 3. ANALYSE DE LA FONCTION asset_url()
// ============================================================================

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "FONCTIONNEMENT ACTUEL DE asset_url()\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$testPaths = [
    'assets/css/style.css',
    'assets/css/pages/exercices.css',
    'assets/js/interactive-exercises.js',
    'assets/images/logo.png',
];

// Inclure config pour avoir asset_url()
require_once $projectRoot . '/src/config/config.php';

if (function_exists('asset_url')) {
    echo "✅ Fonction asset_url() disponible\n\n";
    
    // Test 1: Asset dans /public/
    echo "TEST 1: Assets dans /public/assets/\n";
    echo "──────────────────────────────────\n";
    
    foreach ($testPaths as $path) {
        $publicPath = $projectRoot . '/public/' . $path;
        $fileExists = is_file($publicPath) ? '✅' : '❌';
        
        // Appel de asset_url
        $url = asset_url($path);
        
        echo "  Path: $path\n";
        echo "    Full path: $publicPath\n";
        echo "    File exists: $fileExists\n";
        echo "    URL générée: $url\n";
        echo "    Expected (local): /public/$path\n";
        echo "\n";
    }
    
} else {
    echo "❌ Fonction asset_url() NON disponible\n";
}

// ============================================================================
// 4. PROBLÈMES IDENTIFIÉS
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "PROBLÈMES IDENTIFIÉS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "🔴 PROBLÈME PRINCIPAL:\n";
echo "────────────────────────\n";
echo "La fonction asset_url() cherche les fichiers à '/public/assets/'\n";
echo "mais en production sur Hostinger, l'URL correcte est probablement:\n";
echo "  - /assets/... (à la racine public_html)\n";
echo "  - OU /public/assets/... si réacheminé via .htaccess\n\n";

echo "📋 RAISONS DU PROBLÈME:\n";
echo "1. En local: /moncoachscolaire/public/assets/css/style.css ✅\n";
echo "2. En production: Assets peuvent être à différents niveaux:\n";
echo "   a) /home/u936396612/domains/moncoachscolaire.fr/public_html/public/assets/css/\n";
echo "   b) /home/u936396612/domains/moncoachscolaire.fr/public_html/assets/css/\n";
echo "   c) Servi via un réacheminement .htaccess\n\n";

// ============================================================================
// 5. PAGES QUI CHARGENT LES CSS
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "PAGES QUI CHARGENT CSS/JS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$cssPatterns = [
    'asset_url(\'assets/css/style.css\')',
    'asset_url(\'assets/css/pages/...',
    'function_exists(\'asset_url\') ? asset_url(...)',
    'rtrim($baseUrl, \'/\') . \'/assets/css/...',
];

echo "❗ PATTERNS TROUVÉS:\n";
foreach ($cssPatterns as $pattern) {
    echo "  • $pattern\n";
}

echo "\n📄 FICHIERS QUI UTILISENT asset_url():\n";

$files = [
    'public/index.php',
    'src/pages/dashboard_admin.php',
    'src/pages/dashboard_parent.php',
    'src/pages/maintenance.php',
    'src/pages/landingpage.php',
    'src/pages/view_course.php',
];

foreach ($files as $file) {
    $fullPath = $projectRoot . '/' . $file;
    if (is_file($fullPath)) {
        echo "  ✅ $file\n";
    } else {
        echo "  ❌ $file\n";
    }
}

// ============================================================================
// 6. RECOMMANDATIONS
// ============================================================================

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "RECOMMANDATIONS POUR UNE APPLICATION HYBRIDE\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "SOLUTION 1: Détection automatique du chemin d'assets\n";
echo "───────────────────────────────────────────────────\n";
echo "Modifier asset_url() pour:\n";
echo "1. Vérifier d'abord si /public/assets/ existe\n";
echo "2. Sinon, utiliser /assets/ (racine)\n";
echo "3. Gérer les deux structures avec un fallback\n\n";

echo "SOLUTION 2: Utiliser une variable de configuration\n";
echo "──────────────────────────────────────────────────\n";
echo "Créer .env ou constante:\n";
echo "  ASSETS_PATH=public/assets  (local)\n";
echo "  ASSETS_PATH=assets         (production)\n\n";

echo "SOLUTION 3: Symlink ou Copie\n";
echo "────────────────────────────\n";
echo "En production:\n";
echo "  ln -s /home/u936396612/domains/.../public/assets /home/.../assets\n";
echo "Ou copier les assets à la racine\n\n";

// ============================================================================
// 7. DIAGNOSTIC DÉTAILLÉ
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "DIAGNOSTIC DÉTAILLÉ DES CHEMINS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "Fichier config.php:\n";
echo "  Location: " . str_replace('\\', '/', $projectRoot . '/src/config/config.php') . "\n";
echo "  asset_url() uses:\n";
echo "    - \$GLOBALS['baseUrl']\n";
echo "    - \$GLOBALS['projectRoot']\n";
echo "    - detectBaseUrl() (if defined)\n";
echo "    - is_file() check for /public/ folder\n\n";

echo "Variables globales attendues:\n";
echo "  \$GLOBALS['baseUrl'] = http://localhost/moncoachscolaire (local)\n";
echo "  \$GLOBALS['baseUrl'] = https://moncoachscolaire.fr (production)\n";
echo "  \$GLOBALS['projectRoot'] = chemin absolu du projet\n\n";

// Vérifier si ces globales sont définies
if (!empty($GLOBALS['baseUrl'])) {
    echo "✅ \$GLOBALS['baseUrl'] is SET: " . $GLOBALS['baseUrl'] . "\n";
} else {
    echo "❌ \$GLOBALS['baseUrl'] is NOT SET\n";
}

if (!empty($GLOBALS['projectRoot'])) {
    echo "✅ \$GLOBALS['projectRoot'] is SET: " . $GLOBALS['projectRoot'] . "\n";
} else {
    echo "❌ \$GLOBALS['projectRoot'] is NOT SET\n";
}

// ============================================================================
// 8. SIMULATION DES DEUX ENVIRONNEMENTS
// ============================================================================

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "RÉSULTATS ATTENDUS PAR ENVIRONNEMENT\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$testAssets = [
    'assets/css/style.css',
    'assets/js/interactive-exercises.js',
];

echo "LOCAL (XAMPP):\n";
echo "─────────────\n";
echo "BaseUrl: http://localhost/moncoachscolaire\n";
echo "Assets défiés à: /moncoachscolaire/public/assets/\n\n";

foreach ($testAssets as $asset) {
    echo "Asset: $asset\n";
    echo "  URL générée: http://localhost/moncoachscolaire/public/$asset\n";
    echo "  Chemin phys: D:\\xampp\\htdocs\\moncoachscolaire\\public\\$asset\n";
    echo "  Status: ";
    
    $physPath = 'd:\\xampp\\htdocs\\moncoachscolaire\\public\\' . $asset;
    if (is_file(str_replace('\\', '/', $physPath))) {
        echo "✅ Existe\n";
    } else {
        echo "❓ À vérifier\n";
    }
    echo "\n";
}

echo "\nPRODUCTION (Hostinger):\n";
echo "──────────────────────\n";
echo "BaseUrl: https://moncoachscolaire.fr\n";
echo "Assets seront cherchés à: /public/assets/\n\n";

foreach ($testAssets as $asset) {
    echo "Asset: $asset\n";
    echo "  URL générée: https://moncoachscolaire.fr/public/$asset\n";
    echo "  ⚠️ MAIS: Est-ce le bon chemin en production?\n";
    echo "       Chemin réel peut être: https://moncoachscolaire.fr/$asset\n";
    echo "       Ou: https://moncoachscolaire.fr/public_html/$asset\n";
    echo "\n";
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "FIN DE L'ANALYSE\n";
echo "═══════════════════════════════════════════════════════════════════\n";
?>
