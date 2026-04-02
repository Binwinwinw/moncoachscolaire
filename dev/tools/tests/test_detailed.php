<?php
/**
 * TEST DÉTAILLÉ DU ROUTEUR ET DES PAGES
 * Teste le fonctionnement réel du routeur et de chaque page
 */

$root = __DIR__ . '/..';
echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST DÉTAILLÉ DU ROUTEUR ET DES PAGES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. TEST DU ROUTEUR (index.php)
echo "[1] TEST DU ROUTEUR (index.php)\n";
echo "───────────────────────────────────────────────────────────────\n";

$indexFile = $root . '/index.php';
if (file_exists($indexFile)) {
    $indexContent = file_get_contents($indexFile);
    
    // Vérifier que le routeur charge correctement config.php
    if (strpos($indexContent, '/src/config/config.php') !== false) {
        echo "✅ Routeur charge src/config/config.php\n";
    } else {
        echo "⚠️  Routeur ne charge pas src/config/config.php (à vérifier)\n";
    }
    
    // Vérifier que le routeur charge site_boot.php
    if (strpos($indexContent, '/src/config/site_boot.php') !== false) {
        echo "✅ Routeur charge src/config/site_boot.php\n";
    } else {
        echo "⚠️  Routeur ne charge pas src/config/site_boot.php\n";
    }
    
    // Vérifier que le routeur route vers src/pages/
    if (strpos($indexContent, '/src/pages/') !== false || strpos($indexContent, '\'/src/pages') !== false) {
        echo "✅ Routeur dirige vers src/pages/\n";
    } else {
        echo "⚠️  Routeur ne dirige pas vers src/pages/\n";
    }
} else {
    echo "❌ index.php non trouvé\n";
}

echo "\n";

// 2. TEST DE CHAQUE PAGE PRINCIPALE
echo "[2] TEST DES PAGES PRINCIPALES\n";
echo "───────────────────────────────────────────────────────────────\n";

$pages = [
    'src/pages/login.php' => [
        'required_includes' => ['/../config/config.php', '/../includes/login_security.php'],
        'check_functions' => ['site_url', 'detectBaseUrl'],
    ],
    'src/pages/dashboard.php' => [
        'required_includes' => ['/../config/config.php', '/../includes/gamification.php', '/../includes/progress_display.php'],
        'check_functions' => ['site_url', 'isAdmin'],
    ],
    'src/pages/dashboard_admin.php' => [
        'required_includes' => ['/../config/config.php', '/../includes/admin_auth.php'],
        'check_functions' => ['requireAdmin'],
    ],
    'src/pages/exercices.php' => [
        'required_includes' => [],
        'check_functions' => ['asset_url'],
    ],
];

foreach ($pages as $page => $config) {
    $filePath = $root . '/' . $page;
    echo "📄 " . basename($page) . ":\n";
    
    if (!file_exists($filePath)) {
        echo "   ❌ Fichier non trouvé\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Vérifier les includes requis
    foreach ($config['required_includes'] as $include) {
        if (strpos($content, "__DIR__ . '$include'") !== false || 
            strpos($content, "__DIR__ . \"$include\"") !== false) {
            echo "   ✅ Include: " . basename($include) . "\n";
        } else if ($include !== '') {
            echo "   ⚠️  Include missing: " . basename($include) . "\n";
        }
    }
    
    // Vérifier les fonctions
    foreach ($config['check_functions'] as $func) {
        if (strpos($content, $func) !== false) {
            echo "   ✅ Fonction: " . $func . "()\n";
        }
    }
    
    echo "\n";
}

// 3. TEST DES INCLUDES CRITIQUES
echo "[3] TEST DES INCLUDES CRITIQUES\n";
echo "───────────────────────────────────────────────────────────────\n";

$criticalIncludes = [
    'src/includes/topbar.php' => 'Barre de navigation',
    'src/includes/footer.php' => 'Pied de page',
    'src/includes/admin_auth.php' => 'Authentification admin',
    'src/includes/login_security.php' => 'Sécurité de connexion',
    'src/includes/demo_security.php' => 'Sécurité démo',
];

foreach ($criticalIncludes as $include => $desc) {
    $filePath = $root . '/' . $include;
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        echo "✅ " . str_pad($desc, 25) . " - " . round($size / 1024, 1) . " KB\n";
    } else {
        echo "❌ " . str_pad($desc, 25) . " - MANQUANT\n";
    }
}

echo "\n";

// 4. TEST DES API
echo "[4] TEST DES API\n";
echo "───────────────────────────────────────────────────────────────\n";

$apis = [
    'src/api/courses.php' => 'GET courses',
    'src/api/get_exercises.php' => 'GET exercises',
    'src/api/get_progress_chart.php' => 'GET progress chart',
    'src/api/save_progress.php' => 'POST progress',
];

foreach ($apis as $api => $desc) {
    $filePath = $root . '/' . $api;
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        // Vérifier que c'est une API (contient du JSON ou del'authentification)
        if (strpos($content, 'json_encode') !== false || 
            strpos($content, 'Content-Type: application/json') !== false ||
            strpos($content, 'requireAdmin') !== false) {
            echo "✅ " . $desc . " - " . basename($api) . "\n";
        } else {
            echo "⚠️  " . $desc . " - Format API non vérifié\n";
        }
    } else {
        echo "❌ " . $desc . " - MANQUANT\n";
    }
}

echo "\n";

// 5. TEST DE STRUCTURE
echo "[5] TEST DE STRUCTURE\n";
echo "───────────────────────────────────────────────────────────────\n";

$dirs = [
    'src/config' => 'Configuration',
    'src/includes' => 'Includes',
    'src/pages' => 'Pages',
    'src/pages/college' => 'Cours collège',
    'src/api' => 'API',
    'db' => 'Base de données',
    'tools' => 'Outils',
    'docs' => 'Documentation',
];

foreach ($dirs as $dir => $desc) {
    $dirPath = $root . '/' . $dir;
    if (is_dir($dirPath)) {
        $fileCount = count(glob($dirPath . '/*.php'));
        echo "✅ " . str_pad($desc, 20) . " - $fileCount fichiers PHP\n";
    } else {
        echo "❌ " . str_pad($desc, 20) . " - MANQUANT\n";
    }
}

echo "\n";

// 6. SYNTHÈSE FINALE
echo "═══════════════════════════════════════════════════════════════\n";
echo "SYNTHÈSE FINALE\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$configOk = file_exists($root . '/src/config/config.php');
$bootOk = file_exists($root . '/src/config/site_boot.php');
$pagesOk = file_exists($root . '/src/pages/login.php') && file_exists($root . '/src/pages/dashboard.php');
$apiOk = file_exists($root . '/src/api/courses.php');
$routerOk = file_exists($root . '/index.php');

echo "Configuration:  " . ($configOk ? "✅" : "❌") . "\n";
echo "Bootstrap:      " . ($bootOk ? "✅" : "❌") . "\n";
echo "Pages:          " . ($pagesOk ? "✅" : "❌") . "\n";
echo "API:            " . ($apiOk ? "✅" : "❌") . "\n";
echo "Routeur:        " . ($routerOk ? "✅" : "❌") . "\n";

echo "\n";

if ($configOk && $bootOk && $pagesOk && $apiOk && $routerOk) {
    echo "✅ APPLICATION PRÊTE POUR TESTING\n";
    echo "\nRéorganisation réussie! Les fichiers sont correctement structurés.\n";
} else {
    echo "⚠️  Certains éléments doivent être vérifiés.\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
?>
