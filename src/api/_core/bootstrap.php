<?php

/**
 * API Core Bootstrap
 */

if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (strtolower((string) getenv('APP_ENV')) === 'production');

    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'cookie_secure' => $isSecure,
        'use_strict_mode' => true,
    ]);
}

// Sécurité headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

if (strtolower((string) getenv('APP_ENV')) === 'production' && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
}

// Chargement config + DB
$root = dirname(__DIR__, 2);
if (is_file($root . '/config/config.php')) {
    require_once $root . '/config/config.php';
}
if (is_file($root . '/database/connection.php')) {
    require_once $root . '/database/connection.php';
}

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/ratelimit.php';
require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/middleware.php';
