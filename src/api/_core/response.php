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

function api_additive_response(
    array $legacyPayload,
    int $status = 200,
    array $data = [],
    array $meta = []
): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    if (!array_key_exists('data', $legacyPayload)) {
        $legacyPayload['data'] = $data;
    }

    $payloadMeta = [];
    if (isset($legacyPayload['meta']) && is_array($legacyPayload['meta'])) {
        $payloadMeta = $legacyPayload['meta'];
    }
    $payloadMeta = array_merge($payloadMeta, $meta);
    if (!isset($payloadMeta['request_id']) || trim((string) $payloadMeta['request_id']) === '') {
        $payloadMeta['request_id'] = api_request_id();
    }
    $legacyPayload['meta'] = $payloadMeta;

    echo json_encode($legacyPayload, JSON_UNESCAPED_UNICODE);
    exit;
}

function api_additive_error(
    string $message,
    int $status = 400,
    array $legacyPayload = [],
    array $meta = []
): void {
    $payload = array_merge([
        'success' => false,
        'error' => $message,
    ], $legacyPayload);

    api_additive_response($payload, $status, [], $meta);
}
