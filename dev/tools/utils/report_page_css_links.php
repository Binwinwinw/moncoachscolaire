<?php
// tools/report_page_css_links.php
// For each PHP page under pages/, read $page_css and report the final href (as head.php would generate)

$root = realpath(__DIR__ . '/..');
require_once $root . '/config.php'; // sets $baseUrl

$pagesDir = $root . DIRECTORY_SEPARATOR . 'pages';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pagesDir));

$report = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') continue;
    $content = file_get_contents($path);
    if (preg_match('/\$page_css\s*=\s*["\']([^"\']+)["\']/', $content, $m)) {
        $decl = $m[1];
        // compute candidate like head.php
        if (strpos($decl, '/') !== false) {
            $candidate1 = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . ltrim($decl, '/');
            $candidate2 = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . ltrim($decl, '/');
            if (is_file($candidate1)) $chosen = $candidate1;
            elseif (is_file($candidate2)) $chosen = $candidate2;
            else $chosen = $candidate2; // choose pages/ fallback
        } else {
            $chosen = $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . ltrim($decl, '/');
        }
        // compute href with baseUrl
        $cssRel = str_replace($root . DIRECTORY_SEPARATOR, '', $chosen);
        $cssRel = str_replace(DIRECTORY_SEPARATOR, '/', $cssRel);
        $href = rtrim($baseUrl, '/') . '/' . $cssRel;
        $report[$path] = ['decl'=>$decl, 'href'=>$href, 'exists'=>is_file($chosen), 'path'=>$chosen];
    } else {
        $report[$path] = ['decl'=>null];
    }
}

foreach ($report as $file => $info) {
    echo "Page: $file\n";
    if ($info['decl'] === null) {
        echo "  Declared: (none)\n\n";
        continue;
    }
    echo "  Declared: {$info['decl']}\n";
    echo "  Resolved href: {$info['href']}\n";
    echo "  File exists: " . ($info['exists'] ? 'yes' : 'no') . "\n";
    echo "  Local path checked: {$info['path']}\n\n";
}

exit(0);
