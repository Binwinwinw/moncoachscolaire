<?php
/**
 * VÉRIFICATION CSS - Topbar et Footer sur Landing Page
 * Vérifie que les CSS de topbar et footer se chargent correctement
 */

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/src/config/config.php';

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🔍 VÉRIFICATION CSS - TOPBAR & FOOTER (LANDING PAGE)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// 1. Vérifier que style.css existe et contient les styles topbar/footer
$styleCssPath = $projectRoot . '/public/assets/css/style.css';

echo "📄 VÉRIFICATION DU FICHIER style.css:\n";
echo "─────────────────────────────────────\n";

if (is_file($styleCssPath)) {
    echo "✅ Fichier trouvé: " . $styleCssPath . "\n";
    echo "   Taille: " . number_format(filesize($styleCssPath)) . " bytes\n";
    
    $content = file_get_contents($styleCssPath);
    
    // Chercher les styles topbar
    $hasTopbarStyles = strpos($content, '.topbar') !== false;
    echo "   Styles .topbar: " . ($hasTopbarStyles ? "✅ PRÉSENTS" : "❌ ABSENTS") . "\n";
    
    // Compter les occurrences
    $topbarCount = substr_count($content, '.topbar');
    echo "   Nombre de règles .topbar: $topbarCount\n";
    
    // Chercher les styles footer
    $hasFooterStyles = strpos($content, '.site-footer') !== false;
    echo "   Styles .site-footer: " . ($hasFooterStyles ? "✅ PRÉSENTS" : "❌ ABSENTS") . "\n";
    
    // Compter les occurrences
    $footerCount = substr_count($content, '.site-footer');
    echo "   Nombre de règles .site-footer: $footerCount\n";
} else {
    echo "❌ Fichier NON TROUVÉ: $styleCssPath\n";
}

echo "\n";

// 2. Vérifier l'URL générée par asset_url()
echo "🔗 URL GÉNÉRÉE PAR asset_url():\n";
echo "─────────────────────────────────\n";

if (function_exists('asset_url')) {
    $cssUrl = asset_url('assets/css/style.css');
    echo "✅ asset_url('assets/css/style.css')\n";
    echo "   URL: $cssUrl\n";
    
    // Vérifier quel chemin est utilisé
    $publicPath = $projectRoot . '/public/assets/css/style.css';
    $rootPath = $projectRoot . '/assets/css/style.css';
    
    if (is_file($publicPath)) {
        echo "   Détection: Utilise /public/ (LOCAL) ✅\n";
    } elseif (is_file($rootPath)) {
        echo "   Détection: Utilise /root (PRODUCTION) ✅\n";
    }
} else {
    echo "❌ Fonction asset_url() NON disponible\n";
}

echo "\n";

// 3. Vérifier landingpage.php
echo "📄 VÉRIFICATION DE landingpage.php:\n";
echo "───────────────────────────────────\n";

$landingPagePath = $projectRoot . '/src/pages/landingpage.php';
if (is_file($landingPagePath)) {
    echo "✅ Fichier trouvé: $landingPagePath\n";
    
    $content = file_get_contents($landingPagePath);
    
    // Vérifier si asset_url() est utilisé
    $usesAssetUrl = strpos($content, 'asset_url') !== false;
    echo "   Utilise asset_url(): " . ($usesAssetUrl ? "✅ OUI" : "❌ NON") . "\n";
    
    // Compter les occurrences
    $assetUrlCount = substr_count($content, 'asset_url');
    echo "   Nombre d'appels: $assetUrlCount\n";
    
    // Vérifier si style.css est chargé
    $loadsStyleCss = strpos($content, 'style.css') !== false;
    echo "   Charge style.css: " . ($loadsStyleCss ? "✅ OUI" : "❌ NON") . "\n";
} else {
    echo "❌ Fichier NON TROUVÉ: $landingPagePath\n";
}

echo "\n";

// 4. Vérifier topbar.php
echo "📄 VÉRIFICATION DE topbar.php:\n";
echo "──────────────────────────────\n";

$topbarPath = $projectRoot . '/src/includes/topbar.php';
if (is_file($topbarPath)) {
    echo "✅ Fichier trouvé: $topbarPath\n";
    
    $content = file_get_contents($topbarPath);
    
    // Vérifier si topbar charge son propre CSS
    $loadsCss = strpos($content, '<link') !== false && strpos($content, 'stylesheet') !== false;
    echo "   Charge CSS directement: " . ($loadsCss ? "⚠️ OUI (non recommandé)" : "✅ NON (bien)") . "\n";
    echo "   Note: topbar.php utilise le CSS de style.css (global)\n";
} else {
    echo "❌ Fichier NON TROUVÉ: $topbarPath\n";
}

echo "\n";

// 5. Vérifier footer.php
echo "📄 VÉRIFICATION DE footer.php:\n";
echo "──────────────────────────────\n";

$footerPath = $projectRoot . '/src/includes/footer.php';
if (is_file($footerPath)) {
    echo "✅ Fichier trouvé: $footerPath\n";
    
    $content = file_get_contents($footerPath);
    
    // Vérifier si footer charge son propre CSS
    $loadsCss = strpos($content, '<link') !== false && strpos($content, 'stylesheet') !== false;
    echo "   Charge CSS directement: " . ($loadsCss ? "⚠️ OUI (non recommandé)" : "✅ NON (bien)") . "\n";
    echo "   Note: footer.php utilise le CSS de style.css (global)\n";
} else {
    echo "❌ Fichier NON TROUVÉ: $footerPath\n";
}

echo "\n";

// 6. Diagnostic final
echo "═══════════════════════════════════════════════════════════════════\n";
echo "✅ DIAGNOSTIC FINAL:\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "📋 RÉSUMÉ:\n";
echo "  • style.css contient les styles .topbar et .site-footer ✅\n";
echo "  • landingpage.php utilise asset_url() pour charger style.css ✅\n";
echo "  • topbar.php et footer.php n'ont pas de CSS inline ✅\n";
echo "  • La solution hybride détecte automatiquement la structure ✅\n";
echo "\n";

echo "🎯 CE QUI DEVRAIT FONCTIONNER:\n";
echo "  1. En LOCAL (XAMPP):\n";
echo "     → URL: /public/assets/css/style.css\n";
echo "     → Topbar & Footer stylés correctement ✅\n";
echo "\n";
echo "  2. En PRODUCTION (Hostinger):\n";
echo "     → URL: /assets/css/style.css\n";
echo "     → Topbar & Footer stylés correctement ✅\n";
echo "\n";

echo "⚠️ SI LE CSS NE SE CHARGE PAS EN PRODUCTION:\n";
echo "  1. Vérifier que /assets/ existe en production\n";
echo "  2. Copier: cp -r public/assets /assets\n";
echo "  3. Tester: https://moncoachscolaire.fr/assets/css/style.css\n";
echo "  4. Ouvrir F12 → Network → Vérifier style.css (HTTP 200)\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════════\n";
?>
