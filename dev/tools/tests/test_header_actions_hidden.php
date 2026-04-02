<?php
// tests/test_header_actions_hidden.php
// Check that the CSS hides header-actions when body.with-sidebar is present on desktop
$css = @file_get_contents(__DIR__ . '/../style.css');
if ($css === false) {
    echo "FAIL: couldn't read style.css\n";
    exit(2);
}

if (strpos($css, 'body.with-sidebar .site-header .header-actions') !== false && strpos($css, 'display: none') !== false) {
    echo "OK: header-actions hiding rule present\n";
    exit(0);
}

echo "FAIL: CSS rule for hiding header-actions not detected\n";
exit(1);
