<?php
// scripts/check_headers.php
$pages = [
    '/landingpage.php',
    '/dashboard.php',
    '/parents.php',
    '/suivi_enfant.php',
    '/login.php',
    '/register.php',
    '/college/index.php',
    '/lycee/index.php'
];
$root = realpath(__DIR__ . '/..');
foreach ($pages as $p) {
    echo "Checking $p => ";
    $_SERVER['REQUEST_URI'] = $p;
    ob_start();
    $path = $root . $p;
    if (file_exists($path)) {
        include $path;
    } else {
        include $root . '/header.php';
        $basename = basename($p);
        if (file_exists($root . '/' . $basename)) include $root . '/' . $basename;
    }
    $html = ob_get_clean();
    $topbars = substr_count($html, 'class="topbar"') + substr_count($html, "class='topbar'");
    $siteHeaders = substr_count($html, 'class="site-header"') + substr_count($html, "class='site-header'");
    echo "topbar: $topbars, site-header: $siteHeaders\n";
}
exit(0);
