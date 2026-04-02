<?php
/**
 * TEST PRODUCTION - Vérifier l'accessibilité des fichiers
 * À uploader sur le serveur pour diagnostiquer le problème 404
 */

// Informations serveur
echo "═══════════════════════════════════════════════════════════════════\n";
echo "🔍 DIAGNOSTIC 404 - PRODUCTION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📍 INFORMATIONS SERVEUR:\n";
echo "─────────────────────────\n";
echo "  Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "  Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
echo "  Script Filename: " . __FILE__ . "\n";
echo "  Host: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "  PHP Version: " . phpversion() . "\n\n";

// Chemins à vérifier
$basePath = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
$cssPath = $basePath . '/public/assets/css/style.css';

echo "📁 VÉRIFICATION DES CHEMINS:\n";
echo "────────────────────────────\n";
echo "  Base: $basePath\n";
echo "  CSS: $cssPath\n\n";

echo "📄 EXISTENCE DES FICHIERS:\n";
echo "──────────────────────────\n";

$checks = [
    'public/' => $basePath . '/public',
    'public/assets/' => $basePath . '/public/assets',
    'public/assets/css/' => $basePath . '/public/assets/css',
    'public/assets/css/style.css' => $cssPath,
    '.htaccess' => $basePath . '/.htaccess',
];

foreach ($checks as $label => $path) {
    if (is_dir($path)) {
        echo "  ✅ DIR:  $label\n";
        if (is_readable($path)) {
            echo "         → Readable: YES\n";
        } else {
            echo "         → Readable: NO ⚠️\n";
        }
    } elseif (is_file($path)) {
        echo "  ✅ FILE: $label\n";
        $size = filesize($path);
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "         → Size: " . number_format($size) . " bytes\n";
        echo "         → Permissions: $perms\n";
        echo "         → Readable: " . (is_readable($path) ? "YES" : "NO ⚠️") . "\n";
    } else {
        echo "  ❌ MISSING: $label\n";
    }
}

echo "\n";

// Vérifier le contenu du .htaccess
echo "🔧 VÉRIFICATION .htaccess:\n";
echo "─────────────────────────\n";

$htaccessPath = $basePath . '/.htaccess';
if (is_file($htaccessPath)) {
    $content = file_get_contents($htaccessPath);
    
    // Chercher les règles importantes
    $hasPublicRule = strpos($content, 'RewriteRule ^public/assets/') !== false;
    echo "  Règle 'public/assets/': " . ($hasPublicRule ? "✅ PRÉSENTE" : "❌ ABSENTE") . "\n";
    
    $rewriteEngineOn = strpos($content, 'RewriteEngine On') !== false;
    echo "  RewriteEngine On: " . ($rewriteEngineOn ? "✅ OUI" : "❌ NON") . "\n";
    
    $linesCount = substr_count($content, "\n");
    echo "  Lignes totales: $linesCount\n";
} else {
    echo "  ❌ .htaccess NON TROUVÉ\n";
}

echo "\n";

// Test d'URL
echo "🌐 TESTS D'URL:\n";
echo "───────────────\n";

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'moncoachscolaire.fr';

$testUrls = [
    "$protocol://$host/public/assets/css/style.css",
    "$protocol://$host/assets/css/style.css",
];

foreach ($testUrls as $url) {
    echo "  URL: $url\n";
    
    // Test avec file_get_contents (peut échouer si not allowed)
    $headers = @get_headers($url, 1);
    if ($headers && isset($headers[0])) {
        echo "    Status: " . $headers[0] . "\n";
    } else {
        echo "    Status: ⚠️ Impossible de tester (get_headers bloqué)\n";
    }
}

echo "\n";

// Recommandations
echo "═══════════════════════════════════════════════════════════════════\n";
echo "💡 RECOMMANDATIONS:\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

if (!is_file($cssPath)) {
    echo "❌ PROBLÈME: Le fichier CSS n'existe pas!\n";
    echo "   Solution: Uploader le dossier /public/assets/ sur le serveur\n\n";
} elseif (!is_readable($cssPath)) {
    echo "❌ PROBLÈME: Le fichier existe mais n'est pas lisible!\n";
    echo "   Solution: chmod 644 $cssPath\n\n";
} else {
    echo "✅ Le fichier existe et est lisible\n";
    echo "   Le problème vient probablement du .htaccess ou d'Apache\n\n";
    
    if (!$hasPublicRule) {
        echo "⚠️  La règle pour /public/assets/ est absente du .htaccess\n";
        echo "   Solution: Ajouter la règle manquante\n\n";
    }
}

echo "📋 PROCHAINES ÉTAPES:\n";
echo "  1. Vérifier que les fichiers sont bien uploadés\n";
echo "  2. Vérifier les permissions (755 pour dossiers, 644 pour fichiers)\n";
echo "  3. Vérifier que le .htaccess est bien uploadé\n";
echo "  4. Vider le cache du navigateur (Ctrl+F5)\n";
echo "  5. Vérifier les logs Apache si accessible\n\n";

echo "═══════════════════════════════════════════════════════════════════\n";
?>
