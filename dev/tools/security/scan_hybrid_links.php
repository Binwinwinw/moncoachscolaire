<?php
// scripts/scan_hybrid_links.php
// Find potentially problematic links that use root-absolute paths (/...) or common patterns
$root = realpath(__DIR__ . '/..');
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$files = [];
$exts = ['php','html','htm'];
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getPathname();
    // skip vendor, node_modules, backups
    if (stripos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
    if (stripos($path, DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR) !== false) continue;
    if (stripos($path, DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR) !== false) continue;
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), $exts)) continue;
    $contents = @file_get_contents($path);
    if ($contents === false) continue;

    // Find href or src attributes that start with a root slash ("/something")
    if (preg_match_all('/(?:href|src)=["\']\/[^"\']*/i', $contents, $m)) {
        $matches = $m[0];
        foreach ($matches as $match) {
            $files[] = [ 'file' => $path, 'match' => $match ];
        }
    }
}

if (empty($files)) {
    echo "No root-absolute href/src occurrences found outside sidebar/vendor/backups.\n";
    exit(0);
}

// Group by file
$grouped = [];
foreach ($files as $f) {
    $grouped[$f['file']][] = $f['match'];
}

foreach ($grouped as $file => $matches) {
    echo "\n$file\n";
    foreach ($matches as $m) echo "  - $m\n";
}

exit(0);
