<?php
// tools/verify_css_for_page.php
// Usage: php verify_css_for_page.php pages/college/college.php
$page = $argv[1] ?? '';
if (!$page) {
    echo "Usage: php verify_css_for_page.php <path-to-page-php>\n";
    exit(1);
}
$root = realpath(__DIR__ . '/..');
$pagePath = realpath($root . DIRECTORY_SEPARATOR . $page);
if (!$pagePath || !is_file($pagePath)) { echo "Page not found: $page\n"; exit(2); }
$content = file_get_contents($pagePath);

// find classes used in HTML snippet
preg_match_all('/class\s*=\s*"([^"]+)"/i', $content, $m);
$classes = [];
foreach ($m[1] as $c) {
    foreach (preg_split('/\s+/', trim($c)) as $c2) {
        if ($c2 !== '') $classes[$c2] = true;
    }
}

echo "Found classes in $page:\n";
foreach (array_keys($classes) as $c) echo "  - $c\n";

$globalCss = $root . '/assets/css/style.css';
$pageCss = $root . '/assets/css/pages/' . dirname(str_replace('pages/', '', $page)) . '/index.css';
if (!is_file($pageCss)) {
    // try page specific file matching filename
    $pageCssAlt = $root . '/assets/css/pages/' . dirname(str_replace('pages/', '', $page)) . '/' . basename($page, '.php') . '.css';
    if (is_file($pageCssAlt)) $pageCss = $pageCssAlt;
}

echo "\nChecking selectors in global CSS (assets/css/style.css):\n";
$globalText = is_file($globalCss) ? file_get_contents($globalCss) : '';
foreach (array_keys($classes) as $c) {
    $found = (stripos($globalText, ".$c") !== false) || (stripos($globalText, "$c") !== false);
    echo "  $c => " . ($found ? 'FOUND' : 'MISSING') . "\n";
}

echo "\nPage-specific CSS: $pageCss\n";
if (is_file($pageCss)) {
    $pageText = file_get_contents($pageCss);
    foreach (array_keys($classes) as $c) {
        $found = (stripos($pageText, ".$c") !== false) || (stripos($pageText, "$c") !== false);
        echo "  $c => " . ($found ? 'FOUND' : 'MISSING') . "\n";
    }
} else {
    echo "  (no per-page CSS file found)\n";
}

exit(0);
