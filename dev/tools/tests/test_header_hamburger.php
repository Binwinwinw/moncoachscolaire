<?php
// tests/test_header_hamburger.php
// Verify header hamburger toggle exists in header.php output and js has toggle logic

// Simulate a request to build the topbar + sidebar
$_SERVER['REQUEST_URI'] = '/';
ob_start();
require_once __DIR__ . '/../../topbar.php';
$topHtml = ob_get_clean();

// Capture sidebar output as well (sidebar formerly included from header.php)
ob_start();
require_once __DIR__ . '/../../sidebar.php';
$asideHtml = ob_get_clean();

$ok = true;
if (strpos($topHtml, 'id="headerSidebarToggle"') === false) {
    echo "FAIL: header button id headerSidebarToggle not found\n";
    $ok = false;
}
if (strpos($asideHtml, 'id="site-sidebar"') === false) {
    echo "FAIL: sidebar id site-sidebar not found\n";
    $ok = false;
}

$js = @file_get_contents(__DIR__ . '/../../js/sidebar.js');
if ($js === false || strpos($js, 'sidebar-hidden') === false) {
    echo "FAIL: sidebar.js missing sidebar-hidden logic\n";
    $ok = false;
}

if ($ok) {
    echo "OK: header hamburger and JS toggle present\n";
    exit(0);
}
exit(2);
