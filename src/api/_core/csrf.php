<?php

/**
 * API Core CSRF Helpers
 */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function require_csrf(?string $token = null): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $token = (string) $token;
    if ($sessionToken === '' || $token === '' || !hash_equals($sessionToken, $token)) {
        json_error('CSRF invalide', 403, 'ERR_CSRF');
    }
}

function require_same_origin(): void
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    if ($origin) {
        $originHost = strtolower((string) (parse_url($origin, PHP_URL_HOST) ?? ''));
        $originPort = parse_url($origin, PHP_URL_PORT);
        $originAuthority = $originHost . ($originPort !== null ? ':' . $originPort : '');
        if ($originAuthority !== $host) {
            json_error('Origin invalide', 403, 'ERR_ORIGIN');
        }
        return;
    }

    if ($referer) {
        $refHost = strtolower((string) (parse_url($referer, PHP_URL_HOST) ?? ''));
        $refPort = parse_url($referer, PHP_URL_PORT);
        $refAuthority = $refHost . ($refPort !== null ? ':' . $refPort : '');
        if ($refAuthority !== $host) {
            json_error('Referer invalide', 403, 'ERR_REFERER');
        }
        return;
    }

    json_error('Origine non fournie', 403, 'ERR_ORIGIN');
}
