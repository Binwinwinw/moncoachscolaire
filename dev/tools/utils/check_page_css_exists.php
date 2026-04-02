<?php
// tools/check_page_css_exists.php
// Scans pages/ PHP files for $page_css declarations and verifies the referenced CSS file exists

$root = realpath(__DIR__ . '/..');
$pagesDir = $root . DIRECTORY_SEPARATOR . 'pages';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pagesDir));

$results = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') continue;
    $content = file_get_contents($path);
    if (preg_match('/\$page_css\s*=\s*["\']([^"\']+)["\']/', $content, $m)) {
        $decl = $m[1];
        $found = false;
        $candidates = [];
        // candidate 1: assets/css/<decl>
        $c1 = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $decl;
        $candidates[] = $c1;
        if (is_file($c1)) $found = true;
        // candidate 2: assets/css/pages/<decl>
        $c2 = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . ltrim($decl, '/');
        $candidates[] = $c2;
        if (is_file($c2)) $found = true;
        $results[$path] = ['decl' => $decl, 'found' => $found, 'candidates' => $candidates];
    }
}

if (empty($results)) {
    echo "No \$page_css declarations found under pages/.\n";
    exit(0);
}

$missing = 0;
foreach ($results as $file => $info) {
    if (!$info['found']) {
        $missing++;
        echo "MISSING: {$file}\n  declared: {$info['decl']}\n  checked:\n";
        foreach ($info['candidates'] as $c) echo "    - $c\n";
        echo "\n";
    }
}

if ($missing === 0) {
    echo "All declared per-page CSS files for pages/ exist (checked assets/css/ and assets/css/pages/).\n";
    exit(0);
} else {
    echo "Found $missing pages with missing per-page CSS files.\n";
    exit(1);
}
