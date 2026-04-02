<?php
/**
 * Script de correction des chemins require_once dans src/pages/
 * Corrige les chemins relatifs après le déplacement des fichiers
 */

$pagesDir = __DIR__ . '/../src/pages';
$files = glob($pagesDir . '/*.php');

$replacements = [
    // Config
    "require_once __DIR__ . '/config.php'" => "require_once __DIR__ . '/../config/config.php'",
    'require_once __DIR__ . "/config.php"' => 'require_once __DIR__ . "/../config/config.php"',
    
    // Site boot
    "require_once __DIR__ . '/site_boot.php'" => "require_once __DIR__ . '/../config/site_boot.php'",
    'require_once __DIR__ . "/site_boot.php"' => 'require_once __DIR__ . "/../config/site_boot.php"',
    "if (is_file(__DIR__ . '/site_boot.php'))" => "if (is_file(__DIR__ . '/../config/site_boot.php'))",
    'if (is_file(__DIR__ . "/site_boot.php"))' => 'if (is_file(__DIR__ . "/../config/site_boot.php"))',
    
    // Includes
    "require_once __DIR__ . '/includes/" => "require_once __DIR__ . '/../includes/",
    'require_once __DIR__ . "/includes/' => 'require_once __DIR__ . "/../includes/',
    'is_file(__DIR__ . \'/topbar.php\')' => 'is_file(__DIR__ . \'/../includes/topbar.php\')',
    'is_file(__DIR__ . \'/ footer.php\')' => 'is_file(__DIR__ . \'/../includes/footer.php\')',
    "include __DIR__ . '/topbar.php'" => "include __DIR__ . '/../includes/topbar.php'",
    "include __DIR__ . '/footer.php'" => "include __DIR__ . '/../includes/footer.php'",
    'include_once __DIR__ . \'/topbar.php\'' => 'include_once __DIR__ . \'/../includes/topbar.php\'',
    'include_once __DIR__ . \'/footer.php\'' => 'include_once __DIR__ . \'/../includes/footer.php\'',
];

$fixed = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    foreach ($replacements as $old => $new) {
        $content = str_replace($old, $new, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo " " . basename($file) . "\n";
        $fixed++;
    }
}

echo "\n Total: $fixed fichier(s) corrigé(s)\n";
?>
