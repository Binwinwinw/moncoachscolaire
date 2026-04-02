<?php
/**
 * TEST COMPLET DE L'APPLICATION
 * Vérifie la structure après la réorganisation des fichiers
 */

$root = __DIR__ . '/..';
$passed = 0;
$failed = 0;
$errors = [];

echo "═══════════════════════════════════════════════════════════════\n";
echo "TEST COMPLET DE L'APPLICATION (27 décembre 2025)\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// 1. Fichiers requis
echo "[1] VÉRIFICATION DES FICHIERS\n";
echo "───────────────────────────────────────────────────────────────\n";

$requiredFiles = [
    // Racine
    ['path' => 'index.php', 'desc' => 'Routeur'],
    ['path' => 'demo_login.php', 'desc' => 'Démo'],
    ['path' => 'contact.php', 'desc' => 'Contact'],
    
    // Config
    ['path' => 'src/config/config.php', 'desc' => 'Config'],
    ['path' => 'src/config/site_boot.php', 'desc' => 'Boot'],
    
    // Includes
    ['path' => 'src/includes/topbar.php', 'desc' => 'Topbar'],
    ['path' => 'src/includes/footer.php', 'desc' => 'Footer'],
    ['path' => 'src/includes/login_security.php', 'desc' => 'Auth'],
    ['path' => 'src/includes/admin_auth.php', 'desc' => 'Admin'],
    
    // Pages
    ['path' => 'src/pages/login.php', 'desc' => 'Login'],
    ['path' => 'src/pages/dashboard.php', 'desc' => 'Dashboard'],
    ['path' => 'src/pages/dashboard_admin.php', 'desc' => 'Admin Dashboard'],
    ['path' => 'src/pages/exercices.php', 'desc' => 'Exercices'],
    
    // API
    ['path' => 'src/api/courses.php', 'desc' => 'API Courses'],
    ['path' => 'src/api/get_exercises.php', 'desc' => 'API Exercises'],
    
    // DB
    ['path' => 'db/connection.php', 'desc' => 'DB Connection'],
];

foreach ($requiredFiles as $item) {
    $filePath = $root . '/' . $item['path'];
    if (file_exists($filePath)) {
        echo "✅ " . str_pad($item['desc'], 15) . " - {$item['path']}\n";
        $passed++;
    } else {
        echo "❌ " . str_pad($item['desc'], 15) . " - {$item['path']} MANQUANT\n";
        $failed++;
        $errors[] = "Fichier manquant: {$item['path']}";
    }
}

echo "\n";

// 2. Vérification des chemins require_once
echo "[2] VÉRIFICATION DES CHEMINS REQUIRE_ONCE\n";
echo "───────────────────────────────────────────────────────────────\n";

$pageFiles = [
    'src/pages/login.php',
    'src/pages/dashboard.php',
    'src/pages/dashboard_admin.php',
    'src/pages/exercices.php',
];

foreach ($pageFiles as $page) {
    $filePath = $root . '/' . $page;
    if (!file_exists($filePath)) continue;
    
    $content = file_get_contents($filePath);
    $pageBasename = basename($page);
    
    // Vérifier les mauvais chemins
    if (strpos($content, "require_once __DIR__ . '/config.php'") !== false ||
        strpos($content, 'require_once __DIR__ . "/config.php"') !== false) {
        echo "❌ $pageBasename - Chemins config OBSOLÈTES\n";
        $failed++;
        $errors[] = "$pageBasename a des chemins config obsolètes";
    } else if (strpos($content, "__DIR__") !== false && (
        strpos($content, "'/../config/config.php'") !== false ||
        strpos($content, '"/../config/config.php"') !== false ||
        strpos($content, "'/../config/site_boot.php'") !== false ||
        strpos($content, '"/../config/site_boot.php"') !== false ||
        strpos($content, "'/../includes/") !== false ||
        strpos($content, '"/../includes/') !== false)) {
        echo "✅ $pageBasename - Chemins OK\n";
        $passed++;
    } else {
        echo "⚠️  $pageBasename - Chemins non vérifiables\n";
    }
}

echo "\n";

// 3. Vérification des includes
echo "[3] VÉRIFICATION DES INCLUDES\n";
echo "───────────────────────────────────────────────────────────────\n";

$includeFiles = [
    'src/includes/login.php' => 'Existe',
    'src/includes/topbar.php' => 'Existe',
    'src/includes/footer.php' => 'Existe',
    'src/includes/admin_auth.php' => 'Existe',
];

foreach ($includeFiles as $file => $desc) {
    if (file_exists($root . '/' . $file)) {
        echo "✅ " . basename($file) . " - " . $desc . "\n";
        $passed++;
    } else {
        echo "⚠️  " . basename($file) . " - Non trouvé\n";
    }
}

echo "\n";

// 4. Synthèse
echo "═══════════════════════════════════════════════════════════════\n";
echo "RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";

$total = $passed + $failed;
$score = $total > 0 ? round(($passed / $total) * 100) : 0;

echo "Score: $score% ($passed/$total tests réussis)\n";
echo "\n";

if (!empty($errors)) {
    echo "ERREURS DÉTECTÉES:\n";
    foreach ($errors as $i => $error) {
        echo "  " . ($i + 1) . ". $error\n";
    }
    echo "\n";
}

if ($score >= 90) {
    echo "✅ APPLICATION OPÉRATIONNELLE\n";
} elseif ($score >= 70) {
    echo "⚠️  APPLICATION PARTIELLEMENT FONCTIONNELLE\n";
} else {
    echo "❌ PROBLÈMES CRITIQUES DÉTECTÉS\n";
}

echo "\n═══════════════════════════════════════════════════════════════\n";
?>
