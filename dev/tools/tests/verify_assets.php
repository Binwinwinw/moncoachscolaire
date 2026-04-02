<?php
/**
 * Vérifier l'intégrité des assets uploadés
 * À uploader sur Hostinger et accéder à: https://moncoachscolaire.fr/verify_assets.php
 */

$baseDir = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
$assetsDir = $baseDir . '/public/assets';

// Fichiers attendus (structure locale)
$expectedFiles = [
    // CSS
    'css/style.css',
    'css/colibri-mascot.css',
    'css/session-timeout.css',
    
    // JS
    'js/script.js',
    'js/app.js',
    
    // Images
    'images/logo.png',
    'img/colibri.png',
];

echo "═══════════════════════════════════════════════════════════════════\n";
echo "✅ VÉRIFICATION DES ASSETS UPLOADÉS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📁 Répertoire: $assetsDir\n\n";

$missing = [];
$present = [];

// Vérifier les fichiers attendus
foreach ($expectedFiles as $file) {
    $fullPath = $assetsDir . '/' . $file;
    if (is_file($fullPath)) {
        $size = filesize($fullPath);
        echo "✅ $file (" . number_format($size) . " bytes)\n";
        $present[] = $file;
    } else {
        echo "❌ MANQUANT: $file\n";
        $missing[] = $file;
    }
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ:\n";
echo "───────────────────────────────────────────────────────────────────\n";
echo "Fichiers présents: " . count($present) . "\n";
echo "Fichiers manquants: " . count($missing) . "\n";

if (count($missing) > 0) {
    echo "\n❌ FICHIERS À UPLOADER:\n";
    foreach ($missing as $file) {
        echo "   - $file\n";
    }
} else {
    echo "\n✅ TOUS LES FICHIERS CRITIQUES SONT PRÉSENTS!\n";
}

echo "\n═══════════════════════════════════════════════════════════════════\n";
echo "📂 STRUCTURE COMPLÈTE DU DOSSIER /public/assets/:\n";
echo "───────────────────────────────────────────────────────────────────\n";

function listDir($dir, $indent = '') {
    if (!is_dir($dir)) return;
    
    $files = @scandir($dir);
    if (!$files) return;
    
    $files = array_filter($files, fn($f) => $f !== '.' && $f !== '..');
    sort($files);
    
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            echo "$indent📁 $file/\n";
            listDir($path, $indent . "  ");
        } else {
            $size = filesize($path);
            echo "$indent📄 $file (" . number_format($size) . "b)\n";
        }
    }
}

listDir($assetsDir);

echo "\n═══════════════════════════════════════════════════════════════════\n";
?>
