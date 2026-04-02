<?php
/**
 * Test mod_rewrite et .htaccess
 * Upload à la racine de public_html/ en prod
 * Accès: https://moncoachscolaire.fr/test_htaccess.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>🔍 Test .htaccess & mod_rewrite</h2>\n<pre>";

// Test 1: .htaccess existe-t-il ?
$htaccessPath = __DIR__ . '/.htaccess';
echo "1️⃣ FICHIER .htaccess:\n";
if (file_exists($htaccessPath)) {
    echo "   ✅ Existe: $htaccessPath\n";
    echo "   Taille: " . filesize($htaccessPath) . " octets\n";
    echo "   Permissions: " . substr(sprintf('%o', fileperms($htaccessPath)), -4) . "\n";
    
    // Lire les 10 premières lignes
    $lines = file($htaccessPath);
    echo "   Premières lignes:\n";
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        echo "      " . htmlspecialchars($lines[$i]);
    }
} else {
    echo "   ❌ INTROUVABLE: $htaccessPath\n";
}

// Test 2: mod_rewrite est-il activé ?
echo "\n2️⃣ MOD_REWRITE:\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "   ✅ mod_rewrite activé\n";
    } else {
        echo "   ❌ mod_rewrite DÉSACTIVÉ\n";
    }
    echo "   Modules chargés: " . implode(', ', $modules) . "\n";
} else {
    echo "   ⚠️  apache_get_modules() indisponible (FastCGI/FPM)\n";
    echo "   Vérification alternative...\n";
    
    // Test indirect via variables d'environnement
    if (isset($_SERVER['REDIRECT_STATUS']) || isset($_ENV['APP_ENV'])) {
        echo "   ✅ mod_rewrite probablement actif (variables détectées)\n";
    } else {
        echo "   ⚠️  Impossible de vérifier\n";
    }
}

// Test 3: Vérifier structure des dossiers
echo "\n3️⃣ STRUCTURE /api/:\n";
$apiPath = __DIR__ . '/api';
$srcApiPath = __DIR__ . '/src/api';

if (is_dir($apiPath)) {
    echo "   ✅ /api/ existe (dossier physique)\n";
    $files = scandir($apiPath);
    echo "   Contenu: " . implode(', ', array_filter($files, fn($f) => $f !== '.' && $f !== '..')) . "\n";
} else {
    echo "   ❌ /api/ n'existe PAS (dossier physique)\n";
    echo "   → Doit être routé par .htaccess vers /src/api/\n";
}

if (is_dir($srcApiPath)) {
    echo "   ✅ /src/api/ existe\n";
    $adminPath = $srcApiPath . '/admin';
    if (is_dir($adminPath)) {
        echo "   ✅ /src/api/admin/ existe\n";
        $statsPath = $adminPath . '/stats.php';
        if (file_exists($statsPath)) {
            echo "   ✅ /src/api/admin/stats.php existe\n";
        } else {
            echo "   ❌ /src/api/admin/stats.php MANQUANT\n";
        }
    } else {
        echo "   ❌ /src/api/admin/ MANQUANT\n";
    }
} else {
    echo "   ❌ /src/api/ n'existe PAS\n";
}

// Test 4: Variables d'environnement du .htaccess
echo "\n4️⃣ VARIABLES ENVIRONNEMENT (.htaccess):\n";
echo "   LOCAL_ENV: " . ($_ENV['LOCAL_ENV'] ?? $_SERVER['LOCAL_ENV'] ?? 'NOT SET') . "\n";
echo "   APP_ENV: " . ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'NOT SET') . "\n";
echo "   REDIRECT_STATUS: " . ($_SERVER['REDIRECT_STATUS'] ?? 'NOT SET') . "\n";

// Test 5: Test direct de réécriture
echo "\n5️⃣ SOLUTION RECOMMANDÉE:\n";
if (!is_dir($apiPath)) {
    echo "   💡 Créer un dossier /api/admin/ physique en prod\n";
    echo "   💡 Copier tous les fichiers de /src/api/admin/*.php vers /api/admin/\n";
    echo "   💡 OU corriger le .htaccess pour que le routage fonctionne\n";
}

echo "\n⚠️  N'oublie pas de supprimer ce fichier après diagnostic\n";
echo "</pre>";
