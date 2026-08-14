<?php

/**
 * API Core Middleware
 */

function api_get_raw_body(): string
{
    static $raw = null;
    if ($raw !== null) {
        return $raw;
    }
    $raw = file_get_contents('php://input') ?: '';
    return $raw;
}

function api_get_json_body(bool $required = true): array
{
    $raw = api_get_raw_body();
    if ($raw === '') {
        return $required ? [] : [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        if ($required) {
            json_error('JSON invalide', 400, 'ERR_JSON');
        }
        return [];
    }
    return $data;
}

function api_get_header(string $name): ?string
{
    $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$serverKey])) {
        return (string) $_SERVER[$serverKey];
    }

    if (function_exists('getallheaders')) {
        foreach (getallheaders() as $headerName => $value) {
            if (strcasecmp((string) $headerName, $name) === 0) {
                return (string) $value;
            }
        }
    }

    return null;
}

function require_authenticated_session_or_cli(): bool
{
    $json = api_get_json_body(false);
    $cliToken = api_get_header('X-CLI-Token') ?? ($json['cli_token'] ?? null);

    if (has_valid_cli_token(is_string($cliToken) ? $cliToken : null)) {
        return true;
    }

    if (!has_authenticated_session()) {
        json_error('Non authentifié', 401, 'ERR_AUTH');
    }

    return false;
}

function api_require(array $rules): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if (!empty($rules['method'])) {
        $allowed = (array) $rules['method'];
        if (!in_array($method, $allowed, true)) {
            json_error('Méthode non autorisée', 405, 'ERR_METHOD');
        }
    }

    $isCliRequest = false;
    if (!empty($rules['auth_or_cli'])) {
        $isCliRequest = require_authenticated_session_or_cli();
    } elseif (!empty($rules['auth'])) {
        require_auth();
    }

    if (!empty($rules['roles'])) {
        require_role((array) $rules['roles']);
    }

    if (!empty($rules['csrf']) && !$isCliRequest) {
        require_same_origin();
        $token = api_get_header('X-CSRF-Token');
        if (!$token) {
            $json = api_get_json_body(false);
            $token = $json['csrf_token'] ?? ($_POST['csrf_token'] ?? null);
        }
        require_csrf($token);
    }

    if (!empty($rules['rate'])) {
        $rate = $rules['rate'];
        $key = $rate['key'] ?? 'default';
        $limit = (int) ($rate['limit'] ?? 60);
        $window = (int) ($rate['window'] ?? 60);
        rate_limit($key, $limit, $window);
    }
}
