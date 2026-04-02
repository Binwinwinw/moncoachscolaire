<?php
/**
 * DIAGNOSTIC DÉTAILLÉ DES CHEMINS CSS/JS
 * Vérifie la structure des répertoires et les chemins utilisés
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$projectRoot = dirname(__DIR__);
$isProduction = strpos($_SERVER['HTTP_HOST'] ?? 'localhost', 'hostinger') !== false || 
                strpos($_SERVER['HTTP_HOST'] ?? 'localhost', 'moncoachscolaire.fr') !== false;

echo "═══════════════════════════════════════════════════════════════════\n";
echo "DIAGNOSTIC DÉTAILLÉ - STRUCTURE RÉPERTOIRES\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📍 ENVIRONNEMENT:\n";
echo "─────────────────\n";
echo "Host: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\n";
echo "Is Production: " . ($isProduction ? 'OUI' : 'NON') . "\n";
echo "Project Root: $projectRoot\n";
echo "PHP Version: " . phpversion() . "\n\n";

// ============================================================================
// VÉRIFIER LA STRUCTURE DES RÉPERTOIRES
// ============================================================================

echo "📁 STRUCTURE DES RÉPERTOIRES:\n";
echo "─────────────────────────────\n\n";

$dirs = [
    'Root' => $projectRoot,
    'public/' => $projectRoot . '/public',
    'public/assets/' => $projectRoot . '/public/assets',
    'public/assets/css/' => $projectRoot . '/public/assets/css',
    'public/assets/js/' => $projectRoot . '/public/assets/js',
    'public/assets/images/' => $projectRoot . '/public/assets/images',
    'assets/' => $projectRoot . '/assets',
];

foreach ($dirs as $label => $path) {
    $exists = is_dir($path) ? '✅' : '❌';
    $status = is_dir($path) ? 'EXISTS' : 'NOT FOUND';
    echo "$exists  $label (" . $status . ")\n";
    
    if (is_dir($path) && in_array($label, ['public/assets/css/', 'public/assets/js/', 'assets/'])) {
        try {
            $files = scandir($path);
            $files = array_filter($files, function($f) { return $f !== '.' && $f !== '..' && !is_dir($path . $f); });
            $fileCount = count($files);
            echo "        → $fileCount fichiers\n";
            
            if ($fileCount > 0) {
                $sample = array_slice(array_values($files), 0, 3);
                foreach ($sample as $file) {
                    echo "           - $file\n";
                }
            }
        } catch (Exception $e) {
            echo "        → Erreur: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n";

// ============================================================================
// VÉRIFIER LES FICHIERS CSS SPÉCIFIQUES
// ============================================================================

echo "📄 VÉRIFICATION DES FICHIERS CSS CRITIQUES:\n";
echo "─────────────────────────────────────────────\n\n";

$criticalFiles = [
    'style.css' => [
        $projectRoot . '/public/assets/css/style.css',
        $projectRoot . '/assets/css/style.css',
    ],
    'session-timeout.css' => [
        $projectRoot . '/public/assets/css/session-timeout.css',
        $projectRoot . '/assets/css/session-timeout.css',
    ],
    'colibri-mascot.css' => [
        $projectRoot . '/public/assets/css/colibri-mascot.css',
        $projectRoot . '/assets/css/colibri-mascot.css',
    ],
];

foreach ($criticalFiles as $name => $paths) {
    echo "Fichier: $name\n";
    $found = false;
    foreach ($paths as $path) {
        $exists = is_file($path) ? '✅' : '❌';
        echo "  $exists  $path\n";
        if (is_file($path)) {
            $found = true;
            $size = filesize($path);
            $modified = date('Y-m-d H:i:s', filemtime($path));
            echo "        Size: $size bytes | Modified: $modified\n";
        }
    }
    if (!$found) {
        echo "  ⚠️  FICHIER NON TROUVÉ!\n";
    }
    echo "\n";
}

// ============================================================================
// TESTER LA FONCTION asset_url()
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "TEST DE LA FONCTION asset_url()\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

require_once $projectRoot . '/src/config/config.php';

if (function_exists('asset_url')) {
    echo "✅ Fonction asset_url() disponible\n\n";
    
    // Tests
    $testAssets = [
        'assets/css/style.css',
        'assets/css/session-timeout.css',
        'assets/js/interactive-exercises.js',
    ];
    
    foreach ($testAssets as $asset) {
        $url = asset_url($asset);
        $pathWithPublic = $projectRoot . '/public/' . $asset;
        $pathWithoutPublic = $projectRoot . '/' . $asset;
        
        $foundWith = is_file($pathWithPublic) ? 'OUI (/public/)' : 'NON';
        $foundWithout = is_file($pathWithoutPublic) ? 'OUI (root)' : 'NON';
        
        echo "Asset: $asset\n";
        echo "  Fichier trouvé avec /public/: $foundWith\n";
        echo "  Fichier trouvé sans /public/: $foundWithout\n";
        echo "  URL générée: $url\n";
        echo "  Expected (local): " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/public/$asset\n";
        echo "  Expected (prod): " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/$asset\n";
        echo "\n";
    }
    
} else {
    echo "❌ Fonction asset_url() NON disponible\n";
}

// ============================================================================
// VÉRIFIER LE CONTENU DU STYLE.CSS
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "VÉRIFICATION DU CONTENU CSS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$stylePath = $projectRoot . '/public/assets/css/style.css';
if (is_file($stylePath)) {
    $content = file_get_contents($stylePath);
    $lineCount = substr_count($content, "\n");
    $isEmpty = trim($content) === '' ? 'OUI (VIDE!)' : 'NON';
    
    echo "File: style.css\n";
    echo "  Path: $stylePath\n";
    echo "  Size: " . filesize($stylePath) . " bytes\n";
    echo "  Lines: $lineCount\n";
    echo "  Est vide?: $isEmpty\n";
    
    if (strlen($content) > 0 && strlen($content) < 200) {
        echo "  Content preview: " . substr($content, 0, 100) . "...\n";
    }
    echo "\n";
}

// ============================================================================
// RÉSUMÉ ET RECOMMANDATIONS
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "RÉSUMÉ ET RECOMMANDATIONS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

if (is_dir($projectRoot . '/public/assets/css')) {
    $cssFiles = count(glob($projectRoot . '/public/assets/css/*.css'));
    echo "✅ CSS files found in /public/assets/css/: $cssFiles\n";
} else {
    echo "❌ Directory /public/assets/css/ not found\n";
}

if (is_dir($projectRoot . '/assets/css')) {
    $cssFiles = count(glob($projectRoot . '/assets/css/*.css'));
    echo "✅ CSS files found in /assets/css/: $cssFiles\n";
} else {
    echo "❌ Directory /assets/css/ not found\n";
}

echo "\n🔧 PROCHAINES ÉTAPES:\n";
echo "1. Exécuter ce script sur LOCAL et vérifier les chemins\n";
echo "2. Exécuter ce script en PRODUCTION et vérifier les chemins\n";
echo "3. Comparer les structures\n";
echo "4. Ajuster la configuration si nécessaire\n";

echo "\n═══════════════════════════════════════════════════════════════════\n";
?>
