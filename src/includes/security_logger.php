<?php

class SecurityLogger
{
    private PDO $pdo;

    private array $sensitiveKeys = [
        'password',
        'passwd',
        'token',
        'csrf',
        'reset_token',
        'auth_header',
        'secret',
        'authorization',
        'session',
        'cookie',
        'bearer',
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function log(string $eventType, array $context = []): void
    {
        $safeContext = $this->sanitizeContext($context);
        $identifier = $safeContext['identifier'] ?? null;
        $identifierHash = null;
        $identifierMasked = null;

        if ($identifier !== null && $identifier !== '') {
            $normalized = strtolower(trim((string) $identifier));
            $identifierHash = hash('sha256', $normalized);
            $identifierMasked = $this->maskIdentifier($normalized);
        }

        $metadata = [];
        foreach ($safeContext as $key => $value) {
            if (!in_array($key, ['identifier', 'password', 'token', 'csrf', 'authorization', 'auth_header', 'secret'], true)) {
                $metadata[$key] = $value;
            }
        }

        $metadataJson = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($metadataJson === false) {
            $metadataJson = null;
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO auth_logs (
                    event_type,
                    scope,
                    result,
                    user_id,
                    identifier_hash,
                    identifier_masked,
                    ip_address,
                    user_agent,
                    failure_reason,
                    request_id,
                    metadata_json
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $eventType,
                $safeContext['scope'] ?? null,
                $safeContext['result'] ?? 'info',
                $safeContext['user_id'] ?? null,
                $identifierHash,
                $identifierMasked,
                $safeContext['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? null),
                substr((string) ($safeContext['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 500),
                $safeContext['failure_reason'] ?? null,
                $safeContext['request_id'] ?? ($_SESSION['request_id'] ?? null),
                $metadataJson,
            ]);
        } catch (Throwable $e) {
            error_log('SecurityLogger failed: ' . $e->getMessage());
        }

        $this->pruneOldRecords();
    }

    public function pruneOldRecords(int $days = 90): void
    {
        try {
            $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            if ($driver === 'sqlite') {
                $this->pdo->exec("DELETE FROM auth_logs WHERE created_at < datetime('now', '-{$days} days')");
                $this->pdo->exec("DELETE FROM AdminLogs WHERE CreatedAt < datetime('now', '-{$days} days')");
                $this->pdo->exec("DELETE FROM login_attempts WHERE updated_at < datetime('now', '-1 day')");
                return;
            }

            $this->pdo->exec("DELETE FROM auth_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            $this->pdo->exec("DELETE FROM AdminLogs WHERE CreatedAt < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
            $this->pdo->exec("DELETE FROM login_attempts WHERE updated_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        } catch (Throwable $e) {
            error_log('SecurityLogger prune failed: ' . $e->getMessage());
        }
    }

    private function sanitizeContext(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
                continue;
            }

            if ($this->containsSensitiveKey($normalizedKey)) {
                $sanitized[$key] = '[redacted]';
                continue;
            }

            if (is_string($value)) {
                $sanitized[$key] = $this->neutralizeControlChars($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    private function containsSensitiveKey(string $key): bool
    {
        foreach ($this->sensitiveKeys as $sensitiveKey) {
            if (strpos($key, $sensitiveKey) !== false) {
                return true;
            }
        }

        return false;
    }

    private function neutralizeControlChars(string $value): string
    {
        return preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value) ?? substr($value, 0, 500);
    }

    private function maskIdentifier(string $identifier): string
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $identifier, 2);
            if (count($parts) === 2) {
                $local = $parts[0];
                $domain = $parts[1];
                if (strlen($local) <= 2) {
                    return str_repeat('*', 2) . '@' . $domain;
                }
                $maskedLocal = substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 1));
                $maskedDomain = substr($domain, 0, 1) . str_repeat('*', max(1, strlen($domain) - 1));
                return $maskedLocal . '@' . $maskedDomain;
            }
        }

        if (preg_match('/^[0-9+\s()-]{4,}$/', $identifier)) {
            return preg_replace('/(\d{2})\d+(\d{2})/', '$1******$2', $identifier) ?? $identifier;
        }

        return substr($identifier, 0, 2) . '***';
    }
}

function ensureSecurityLogger(
    ?PDO $pdo = null,
    ?string $requestId = null
): ?SecurityLogger {
    if ($pdo instanceof PDO) {
        if (!isset($_SESSION['request_id']) || $_SESSION['request_id'] === '') {
            $_SESSION['request_id'] = $requestId ?: bin2hex(random_bytes(8));
        }

        return new SecurityLogger($pdo);
    }

    global $pdo;
    if ($pdo instanceof PDO) {
        if (!isset($_SESSION['request_id']) || $_SESSION['request_id'] === '') {
            $_SESSION['request_id'] = $requestId ?: bin2hex(random_bytes(8));
        }

        return new SecurityLogger($pdo);
    }

    return null;
}
