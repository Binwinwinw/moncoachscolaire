<?php
// dev/tools/check_headers_sent.php
// Ajoute un test headers_sent() en tout début de chaque fichier PHP de public/ et src/

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

echo "\n--- Ajout temporaire de headers_sent() ---\n";
foreach ($phpFiles as $file) {
    $lines = file($file);
    if (!$lines) continue;
    // Vérifie si déjà présent
    $already = false;
    foreach (array_slice($lines, 0, 5) as $l) {
        if (strpos($l, 'headers_sent(') !== false) $already = true;
    }
    if ($already) continue;
    // Ajoute le test juste après le <?php d'ouverture
    foreach ($lines as $i => $l) {
        if (strpos($l, '<?php') !== false) {
            array_splice($lines, $i+1, 0, "if (headers_sent(\$f, \$ln)) { error_log(\"HEADERS SENT: $file at \$f:\$ln\"); }\n");
            break;
        }
    }
    file_put_contents($file, implode('', $lines));
    echo "[OK] $file\n";
}
echo "--- Fin ---\n";
