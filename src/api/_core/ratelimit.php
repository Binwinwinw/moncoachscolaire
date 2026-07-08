<?php

/**
 * API Core Rate Limit (file based)
 */

function api_rate_limit_path(): string
{
    $base = dirname(__DIR__, 3) . '/dev/reports';
    if (!is_dir($base)) {
        @mkdir($base, 0755, true);
    }
    return $base . '/api_rate_limits.json';
}

function rate_limit(string $key, int $limit, int $windowSeconds): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userId = $_SESSION['user_id'] ?? 'guest';
    $bucketKey = $key . '|' . $ip . '|' . $userId;

    $path = api_rate_limit_path();
    $now = time();

    $data = [];
    $fp = @fopen($path, 'c+');
    if ($fp === false) {
        return;
    }

    if (flock($fp, LOCK_EX)) {
        $raw = stream_get_contents($fp);
        if ($raw) {
            $data = json_decode($raw, true) ?: [];
        }

        $bucket = $data[$bucketKey] ?? ['count' => 0, 'start' => $now];
        if ($now - $bucket['start'] >= $windowSeconds) {
            $bucket = ['count' => 0, 'start' => $now];
        }

        $bucket['count']++;
        $data[$bucketKey] = $bucket;

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data));
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);

    if ($data[$bucketKey]['count'] > $limit) {
        json_error('Trop de requêtes', 429, 'ERR_RATE_LIMIT');
    }
}
