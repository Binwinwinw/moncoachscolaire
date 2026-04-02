<?php
// tests/test_zindex.php
// Verify z-index ordering in style.css: header should be above sidebar
$css = @file_get_contents(__DIR__ . '/../style.css');
if ($css === false) {
    echo "FAIL: cannot read style.css\n";
    exit(2);
}
$headerOk = strpos($css, '.site-header') !== false && strpos($css, 'z-index: 1300') !== false;
$sidebarOk = strpos($css, '.sidebar') !== false && (strpos($css, 'z-index: 1200') !== false || strpos($css, 'z-index:1200') !== false);
if (!$headerOk) echo "WARN: site-header z-index 1300 not found\n";
if (!$sidebarOk) echo "WARN: sidebar z-index 1200 not found\n";
if ($headerOk && $sidebarOk) {
    echo "OK: z-index ordering looks correct\n";
    exit(0);
}
exit(1);
