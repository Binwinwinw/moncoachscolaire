<?php
/**
 * Diagnostic avancé pour trouver où sont les fichiers .env
 */

echo "═══════════════════════════════════════════════════════════════════\n";
echo "🔍 DIAGNOSTIC AVANCÉ - LOCALISATION DES FICHIERS .env\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// 1. Afficher les répertoires
echo "1️⃣ RÉPERTOIRES:\n";
echo "───────────────\n";

$scriptDir = __DIR__;
$parentDir = dirname(__DIR__);
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '/home/u936396612/domains/moncoachscolaire.fr/public_html';

echo "  Script location: " . __FILE__ . "\n";
echo "  __DIR__: $scriptDir\n";
echo "  parent dir: $parentDir\n";
echo "  DOCUMENT_ROOT: $docRoot\n\n";

// 2. Lister les fichiers à la racine (parent dir)
echo "2️⃣ FICHIERS DANS LE RÉPERTOIRE PARENT:\n";
echo "────────────────────────────────────\n";

if (is_dir($parentDir)) {
    $files = @scandir($parentDir);
    if ($files) {
        $envFiles = array_filter($files, fn($f) => stripos($f, '.env') !== false || stripos($f, '.htaccess') !== false);
        
        if (count($envFiles) > 0) {
            echo "  Fichiers trouvés:\n";
            foreach ($envFiles as $file) {
                $path = $parentDir . '/' . $file;
                if (is_file($path)) {
                    $size = filesize($path);
                    $perms = substr(sprintf('%o', fileperms($path)), -4);
                    echo "    ✅ $file (size: $size bytes, perms: $perms)\n";
                } else {
                    echo "    📁 $file/ (dossier)\n";
                }
            }
        } else {
            echo "  ❌ Aucun fichier .env* trouvé\n";
        }
        echo "\n  Tous les fichiers du répertoire:\n";
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $parentDir . '/' . $file;
                echo "    " . (is_dir($path) ? "📁" : "📄") . " $file\n";
            }
        }
    } else {
        echo "  ❌ Impossible de lire le répertoire: $parentDir\n";
    }
} else {
    echo "  ❌ Répertoire parent n'existe pas: $parentDir\n";
}

echo "\n";

// 3. Chercher récursivement les fichiers .env
echo "3️⃣ RECHERCHE RÉCURSIVE DES FICHIERS .env:\n";
echo "───────────────────────────────────────\n";

function findEnvFiles($dir, $depth = 0, $maxDepth = 3) {
    if ($depth > $maxDepth || !is_dir($dir)) return;
    
    $files = @scandir($dir);
    if (!$files) return;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (stripos($file, '.env') === 0) {
            $size = filesize($path);
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            echo "    ✅ $path (size: $size bytes, perms: $perms)\n";
        }
        
        if (is_dir($path) && $depth < $maxDepth && strpos($path, 'vendor') === false) {
            findEnvFiles($path, $depth + 1, $maxDepth);
        }
    }
}

findEnvFiles($_SERVER['DOCUMENT_ROOT']);

echo "\n";

// 4. Tester si on peut lire les fichiers
echo "4️⃣ TEST DE LECTURE DES FICHIERS:\n";
echo "────────────────────────────────\n";

$envPaths = [
    '.env' => $parentDir . '/.env',
    '.env.production' => $parentDir . '/.env.production',
    '.env (docroot)' => $docRoot . '/.env',
    '.env.production (docroot)' => $docRoot . '/.env.production',
];

foreach ($envPaths as $label => $path) {
    if (is_file($path)) {
        echo "  ✅ $label\n";
        echo "     Path: $path\n";
        
        // Lire le contenu
        $content = @file_get_contents($path);
        if ($content !== false) {
            // Afficher les lignes (sans les valeurs sensibles)
            $lines = array_filter(explode("\n", $content), fn($l) => trim($l) && !str_starts_with(trim($l), '#'));
            foreach ($lines as $line) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    echo "       $key = ✓ (présent)\n";
                }
            }
        } else {
            echo "     ❌ Impossible de lire le fichier\n";
        }
    } else {
        echo "  ❌ $label NOT FOUND\n";
        echo "     Expected: $path\n";
    }
    echo "\n";
}

// 5. Afficher où le diagnostic s'attend à trouver les fichiers
echo "5️⃣ RÉSUMÉ - OÙ CHERCHE LE SCRIPT:\n";
echo "──────────────────────────────────\n";
echo "  diagnose_db.php cherche les fichiers .env dans:\n";
echo "    dirname(__DIR__) = $parentDir\n";
echo "  \n";
echo "  Si les fichiers sont dans DOCUMENT_ROOT ($docRoot),\n";
echo "  il faut les mettre dans le répertoire parent!\n";
echo "\n";
echo "  Chemin correct pour .env.production:\n";
echo "    $parentDir/.env.production\n";

echo "\n═══════════════════════════════════════════════════════════════════\n";
?>
