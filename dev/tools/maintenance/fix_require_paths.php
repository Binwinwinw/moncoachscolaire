<?php
/**
 * Script de correction des chemins require_once/include dans src/pages/
 * Corrige après le déplacement des fichiers vers src/
 */

$pagesDir = __DIR__ . '/../src/pages';
if (!is_dir($pagesDir)) {
    die("❌ Dossier $pagesDir non trouvé\n");
}

$files = glob($pagesDir . '/*.php');
$fixed = 0;
$errors = [];

// Mappage des corrections
$replacements = [
    // Config paths
    [
        'old' => "require_once __DIR__ . '/config.php'",
        'new' => "require_once __DIR__ . '/../config/config.php'"
    ],
    [
        'old' => 'require_once __DIR__ . "/config.php"',
        'new' => 'require_once __DIR__ . "/../config/config.php"'
    ],
    
    // Site boot paths
    [
        'old' => "require_once __DIR__ . '/site_boot.php'",
        'new' => "require_once __DIR__ . '/../config/site_boot.php'"
    ],
    [
        'old' => 'require_once __DIR__ . "/site_boot.php"',
        'new' => 'require_once __DIR__ . "/../config/site_boot.php"'
    ],
    [
        'old' => "if (is_file(__DIR__ . '/site_boot.php'))",
        'new' => "if (is_file(__DIR__ . '/../config/site_boot.php'))"
    ],
    
    // Includes paths
    [
        'old' => "require_once __DIR__ . '/includes/",
        'new' => "require_once __DIR__ . '/../includes/"
    ],
    [
        'old' => 'require_once __DIR__ . "/includes/',
        'new' => 'require_once __DIR__ . "/../includes/'
    ],
    [
        'old' => "if (is_file(__DIR__ . '/includes/",
        'new' => "if (is_file(__DIR__ . '/../includes/"
    ],
    [
        'old' => "include __DIR__ . '/topbar.php'",
        'new' => "include __DIR__ . '/../includes/topbar.php'"
    ],
    [
        'old' => 'include __DIR__ . \'/topbar.php\'',
        'new' => 'include __DIR__ . \'/../includes/topbar.php\''
    ],
    [
        'old' => "include __DIR__ . '/footer.php'",
        'new' => "include __DIR__ . '/../includes/footer.php'"
    ],
    [
        'old' => 'include __DIR__ . \'/footer.php\'',
        'new' => 'include __DIR__ . \'/../includes/footer.php\''
    ],
    [
        'old' => "include_once __DIR__ . '/topbar.php'",
        'new' => "include_once __DIR__ . '/../includes/topbar.php'"
    ],
    [
        'old' => "include_once __DIR__ . '/footer.php'",
        'new' => "include_once __DIR__ . '/../includes/footer.php'"
    ],
    [
        'old' => "if (is_file(\$root . '/config.php'))",
        'new' => "if (is_file(\$root . '/src/config/config.php'))"
    ],
];

foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    $original = $content;
    $changed = false;
    
    foreach ($replacements as $repl) {
        if (strpos($content, $repl['old']) !== false) {
            $content = str_replace($repl['old'], $repl['new'], $content);
            $changed = true;
        }
    }
    
    if ($changed) {
        if (file_put_contents($file, $content)) {
            echo "✅ $filename\n";
            $fixed++;
        } else {
            $errors[] = "❌ Impossible d'écrire: $filename";
        }
    }
}

echo "\n✅ Total: $fixed fichier(s) corrigé(s)\n";
if (!empty($errors)) {
    echo "\n❌ Erreurs:\n";
    foreach ($errors as $err) {
        echo "$err\n";
    }
}
?>
