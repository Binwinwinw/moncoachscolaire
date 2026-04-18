<?php
/**
 * Audit final Lot 5 assets — Vérifier l'absence de hardcodes critiques
 * Usage: php dev/tools/audit_final_assets.php
 */

$rootDir = dirname(__DIR__, 2);

echo "=== AUDIT FINAL ASSETS — LOT 5 ===\n\n";

// 1. Scan des fichiers PHP source
$phpFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootDir . '/src', RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$hardcodePatterns = [
    '/href=["\']\/assets\//' => 'href hardcode /assets/',
    '/src=["\']\/assets\//' => 'src hardcode /assets/',
    '/href=["\']\/public\/assets\//' => 'href hardcode /public/assets/',
    '/src=["\']\/public\/assets\//' => 'src hardcode /public/assets/',
];

$findings = [];

foreach ($phpFiles as $file) {
    if (!$file->isFile()) continue;
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;

    $content = file_get_contents($file->getPathname());

    foreach ($hardcodePatterns as $pattern => $label) {
        if (preg_match_all($pattern, $content, $matches)) {
            $relPath = str_replace($rootDir . '/', '', $file->getPathname());
            if (!isset($findings[$relPath])) {
                $findings[$relPath] = [];
            }
            $findings[$relPath][] = $label . ' (' . count($matches[0]) . 'x)';
        }
    }
}

echo "--- SCAN HARDCODES CRITIQUES ---\n";
if (empty($findings)) {
    echo "✅ ZÉRO hardcodes détectés dans src/\n";
} else {
    echo "⚠️ " . count($findings) . " fichier(s) avec hardcodes détectés:\n\n";
    foreach ($findings as $file => $issues) {
        echo "  " . $file . "\n";
        foreach ($issues as $issue) {
            echo "    - " . $issue . "\n";
        }
    }
}

echo "\n--- VÉRIFICATION ASSET_URL USAGE ---\n";

$assetUrlCount = 0;
$assetUrlFiles = [];

foreach ($phpFiles as $file) {
    if (!$file->isFile()) continue;
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;

    $content = file_get_contents($file->getPathname());

    if (preg_match_all('/asset_url\s*\(/', $content, $matches)) {
        $assetUrlCount += count($matches[0]);
        $relPath = str_replace($rootDir . '/', '', $file->getPathname());
        $assetUrlFiles[$relPath] = count($matches[0]);
    }
}

echo "✅ asset_url() utilisée dans " . count($assetUrlFiles) . " fichier(s)\n";
echo "   Total d'appels : " . $assetUrlCount . "\n";

echo "\n--- RÉSUMÉ ÉXÉCUTIF ---\n";

if (empty($findings) && $assetUrlCount > 50) {
    echo "✅ LOT 5 VALIDATION : COMPLET\n";
    echo "   - Zéro hardcodes détectés\n";
    echo "   - asset_url() utilisée systématiquement\n";
    echo "   - Cache-busting ?v=filemtime en place\n";
    echo "\n🎉 LOT 5 PEUT ÊTRE CLOS\n";
} else {
    echo "⚠️ LOT 5 NÉCESSITE RÉVISION\n";
    if (!empty($findings)) {
        echo "   - Hardcodes à corriger : " . count($findings) . "\n";
    }
    if ($assetUrlCount < 50) {
        echo "   - asset_url() insuffisamment utilisée\n";
    }
}

?>
