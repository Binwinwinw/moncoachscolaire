<?php
// dev/tools/check_bom.php
// Script pour détecter la présence d'un BOM UTF-8 dans tous les fichiers PHP du projet

function has_bom($filename) {
    $fh = fopen($filename, 'rb');
    if (!$fh) return false;
    $bytes = fread($fh, 3);
    fclose($fh);
    return $bytes === "\xEF\xBB\xBF";
}

function scan_dir_recursive($dir, &$results = []) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            scan_dir_recursive($path, $results);
        } elseif (is_file($path) && preg_match('/\.php$/i', $file)) {
            $results[] = $path;
        }
    }
    return $results;
}


$roots = [
    dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public',
    dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src'
];
$phpFiles = [];
foreach ($roots as $root) {
    if (is_dir($root)) {
        scan_dir_recursive($root, $phpFiles);
    }
}

$found = false;
echo "\n--- Vérification des BOM UTF-8 dans les fichiers PHP ---\n";
foreach ($phpFiles as $file) {
    if (has_bom($file)) {
        echo "[BOM] $file\n";
        $found = true;
    }
}
if (!$found) {
    echo "Aucun BOM détecté dans les fichiers PHP.\n";
}
echo "--- Fin de la vérification ---\n";
