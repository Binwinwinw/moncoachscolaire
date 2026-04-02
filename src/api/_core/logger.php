<?php

/**
 * API Core Logger (JSON lines)
 */

function api_log_path(): string
{
    $base = dirname(__DIR__, 2) . '/dev/reports/api_logs';
    if (!is_dir($base)) {
        @mkdir($base, 0755, true);
    }
    $date = date('Y-m-d');
    return $base . '/api_' . $date . '.log';
}

function api_redact_value($value)
{
    if (!is_string($value)) {
        return $value;
    }
    $patterns = [
        '/(password|passwd|secret|token|session|csrf|authorization)/i',
        '/([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})/i',
    ];
    $value = preg_replace($patterns[0], '[REDACTED]', $value);
    $value = preg_replace($patterns[1], '[REDACTED_EMAIL]', $value);
    return $value;
}

function api_log(string $level, string $message, array $context = []): void
{
    $safeContext = [];
    foreach ($context as $k => $v) {
        $safeContext[$k] = is_string($v) ? api_redact_value($v) : $v;
    }

    $entry = [
        'ts' => date('c'),
        'level' => $level,
        'message' => $message,
        'request_id' => function_exists('api_request_id') ? api_request_id() : null,
        'context' => $safeContext,
    ];

    $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    @file_put_contents(api_log_path(), $line, FILE_APPEND | LOCK_EX);
}
