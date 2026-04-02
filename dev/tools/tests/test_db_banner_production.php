<?php
// tests/test_db_banner_production.php
// Verify DB banner is NOT shown in production unless forced with DEBUG_SECRET

// Simulate production without debug
putenv('APP_ENV=production');
putenv('APP_DEBUG=false');
putenv('DEBUG_SECRET=');
// Ensure DB is not configured
putenv('DB_USERNAME=');
putenv('DB_DATABASE=');

// Use config.php helper to test whether the DB notice would be allowed.
$root = realpath(__DIR__ . '/../../');
@session_start();
require_once $root . '/config.php';

if (should_show_db_notice()) {
    echo "FAIL: banner shown in production without debug\n";
    exit(2);
}

// Now simulate production but using secret override
putenv('APP_ENV=production');
putenv('APP_DEBUG=false');
putenv('DEBUG_SECRET=letsdebug');
putenv('DB_USERNAME=');
putenv('DB_DATABASE=');
$_GET['force_debug'] = 'letsdebug';
putenv('DEBUG_SECRET=letsdebug');
// reload config so session override handling is evaluated
require_once $root . '/config.php';

if (should_show_db_notice()) {
    echo "OK: banner shown when DEBUG_SECRET matched\n";
    exit(0);
}

echo "FAIL: banner not shown even when DEBUG_SECRET matched\n";
exit(2);
