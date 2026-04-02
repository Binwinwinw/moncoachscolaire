<?php
/**
 * Exploration de la structure prod
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

$rootPath = dirname(__DIR__);

echo "<h2>🗂️ Exploration du chemin: $rootPath</h2>\n<pre>";

// Lister le contenu de la racine
echo "\n📁 Contenu de: $rootPath\n";
echo str_repeat("-", 60) . "\n";
$files = scandir($rootPath);
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $path = $rootPath . '/' . $file;
    $type = is_dir($path) ? '📂' : '📄';
    echo "$type $file\n";
}

// Chercher les fichiers importants
echo "\n\n🔍 Recherche des fichiers critiques:\n";
echo str_repeat("-", 60) . "\n";

$criticalFiles = [
    'src/config/config.php',
    'src/database/connection.php',
    'src/includes/admin_auth.php',
    'src/api/admin/stats.php',
    'db/connection.php',
    'public/index.php',
    'public_html/index.php',
    'public_html/src/config/config.php',
];

foreach ($criticalFiles as $file) {
    $path = $rootPath . '/' . $file;
    $exists = file_exists($path) ? '✅' : '❌';
    echo "$exists $file\n";
}

// Chercher src/ à différents niveaux
echo "\n\n🔎 Recherche récursive de dossier 'src':\n";
echo str_repeat("-", 60) . "\n";

function findDirs($path, $name, $maxDepth = 3, $depth = 0) {
    if ($depth > $maxDepth) return;
    try {
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $path . '/' . $item;
            if (basename($fullPath) === $name) {
                echo "✅ Trouvé: $fullPath\n";
            }
            if (is_dir($fullPath) && !is_link($fullPath)) {
                findDirs($fullPath, $name, $maxDepth, $depth + 1);
            }
        }
    } catch (Exception $e) {
        // Ignorer les erreurs d'accès
    }
}

findDirs($rootPath, 'src');
findDirs($rootPath, 'public_html');

echo "\n\n📊 Info système:\n";
echo str_repeat("-", 60) . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current User: " . get_current_user() . "\n";
echo "Working Directory: " . getcwd() . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

echo "</pre>";
