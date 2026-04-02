<?php
// tests/test_debug_toggle.php
// Quick CLI test script: sets APP_ENV=production and DEBUG_SECRET and calls header.php
// Usage: php tests/test_debug_toggle.php

putenv('APP_ENV=production');
putenv('DB_USERNAME=');
putenv('DB_DATABASE=');
putenv('DEBUG_SECRET=secret123');

// Simulate request to enable debug with secret
$_GET['force_debug'] = 'secret123';
$_SERVER['REQUEST_URI'] = '/';

// Start session and consult config helper directly (avoid including pages that may exit)
$root = realpath(__DIR__ . '/../../');
session_start();
require_once $root . '/config.php';

if (should_show_db_notice()) {
    echo "OK: debug banner shown\n";
    exit(0);
} else {
    echo "FAIL: debug banner not found\n";
    exit(2);
}
