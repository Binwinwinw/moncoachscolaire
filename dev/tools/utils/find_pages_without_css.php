<?php
// tools/find_pages_without_css.php
// Lists PHP files under pages/ that do NOT declare $page_css

$root = realpath(__DIR__ . '/..');
$pagesDir = $root . DIRECTORY_SEPARATOR . 'pages';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pagesDir));
$missing = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') continue;
    $content = file_get_contents($path);
    if (!preg_match('/\$page_css\s*=\s*["\']([^"\']+)["\']/', $content)) {
        $missing[] = $path;
    }
}

if (empty($missing)) {
    echo "All PHP pages under pages/ declare \$page_css\n";
    exit(0);
}

echo "Found " . count($missing) . " pages without \$page_css:\n";
foreach ($missing as $m) echo " - $m\n";
exit(1);
