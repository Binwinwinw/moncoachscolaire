<?php

/**
 * API Core Response Helpers
 */

function api_request_id(): string
{
    static $requestId = null;
    if ($requestId !== null) {
        return $requestId;
    }
    try {
        $requestId = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $requestId = uniqid('req_', true);
    }
    return $requestId;
}

function json_response(array $data = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    $payload = [
        'success' => true,
        'request_id' => api_request_id(),
        'data' => $data,
    ];

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $status = 400, string $code = 'ERR_GENERIC'): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    $payload = [
        'success' => false,
        'request_id' => api_request_id(),
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ];

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
