<?php
// tests/test_readonly.php — check DB_READ_ONLY and APP_ENV read correctly
$root = dirname(__DIR__);
require_once $root . '/config.php';

$appEnv = isset($appEnv) ? $appEnv : (getenv('APP_ENV') ?: 'local');
$dbReadOnly = isset($dbReadOnly) ? $dbReadOnly : filter_var(getenv('DB_READ_ONLY') ?: 'false', FILTER_VALIDATE_BOOLEAN);

echo "APP_ENV = $appEnv\n";
echo "DB_READ_ONLY = " . ($dbReadOnly ? 'true' : 'false') . "\n";

if ($appEnv === 'local' && $dbReadOnly) {
    echo "⚠️ Inconsistent: local environment should not be read-only by default\n";
}

if ($appEnv === 'production' && !$dbReadOnly) {
    echo "⚠️ Consider setting DB_READ_ONLY=true in production to avoid accidental writes from dev flows\n";
}

exit(0);
