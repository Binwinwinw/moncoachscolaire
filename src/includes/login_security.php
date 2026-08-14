<?php

/**
 * Fonctions de sécurité pour le login
 */

function getRateLimitWindowSeconds()
{
    return 900;
}

function getRateLimitMaxAttempts()
{
    return 5;
}

function getRateLimitClientIp()
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function getRateLimitIdentifierKey($identifier, $scope = 'login')
{
    $normalizedIdentifier = strtolower(trim((string) $identifier));
    if ($normalizedIdentifier === '') {
        $normalizedIdentifier = 'unknown';
    }

    return hash('sha256', $scope . ':' . $normalizedIdentifier . ':' . getRateLimitClientIp());
}

function ensureLoginAttemptsTable($pdo)
{
    if (!$pdo instanceof PDO) {
        return false;
    }

    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $sql = $driver === 'sqlite'
        ? "
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                scope VARCHAR(64) NOT NULL,
                identifier_key VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempt_count INT NOT NULL DEFAULT 0,
                first_attempt_at DATETIME NOT NULL,
                last_attempt_at DATETIME NOT NULL,
                blocked_until DATETIME NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE(scope, identifier_key)
            )
        "
        : "
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                scope VARCHAR(64) NOT NULL,
                identifier_key VARCHAR(255) NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                attempt_count INT NOT NULL DEFAULT 0,
                first_attempt_at DATETIME NOT NULL,
                last_attempt_at DATETIME NOT NULL,
                blocked_until DATETIME NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE KEY uniq_login_attempt_scope_identifier (scope, identifier_key),
                KEY idx_login_attempts_scope (scope),
                KEY idx_login_attempts_blocked (blocked_until)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";

    try {
        $pdo->exec($sql);
        return true;
    } catch (Exception $e) {
        error_log('Rate limit table init failed: ' . $e->getMessage());
        return false;
    }
}

function getLoginAttemptsStateFromSession($identifier, $scope = 'login')
{
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    $key = md5($scope . ':' . $identifier . ':' . getRateLimitClientIp());
    $attempts = $_SESSION['login_attempts'][$key] ?? ['count' => 0, 'time' => 0];

    if (time() - $attempts['time'] > getRateLimitWindowSeconds()) {
        $_SESSION['login_attempts'][$key] = ['count' => 0, 'time' => time()];
        $attempts = $_SESSION['login_attempts'][$key];
    }

    return ['key' => $key, 'attempts' => $attempts];
}

/**
 * Limite le nombre de tentatives de connexion (rate limiting)
 */
function checkLoginAttempts($identifier, $scope = 'login')
{
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        if (function_exists('ensure_session_started')) {
            ensure_session_started();
        } elseif (!headers_sent()) {
            session_start();
        }
    }

    if ($pdo instanceof PDO) {
        if (!ensureLoginAttemptsTable($pdo)) {
            goto fallback_session;
        }

        $identifierKey = getRateLimitIdentifierKey($identifier, $scope);
        $now = time();
        $windowSeconds = getRateLimitWindowSeconds();
        $maxAttempts = getRateLimitMaxAttempts();
        $nowString = date('Y-m-d H:i:s', $now);

        try {
            $stmt = $pdo->prepare('SELECT id, attempt_count, first_attempt_at, last_attempt_at, blocked_until FROM login_attempts WHERE scope = ? AND identifier_key = ? LIMIT 1');
            $stmt->execute([$scope, $identifierKey]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $firstAttemptAt = strtotime($row['first_attempt_at'] ?? '');
                $lastAttemptAt = strtotime($row['last_attempt_at'] ?? '');
                $blockedUntil = $row['blocked_until'] ? strtotime($row['blocked_until']) : null;

                if ($blockedUntil && $blockedUntil > $now) {
                    return [
                        'allowed' => false,
                        'remaining' => max(1, $blockedUntil - $now),
                        'message' => 'Trop de tentatives. Réessayez dans ' . ceil(max(1, $blockedUntil - $now) / 60) . ' minute(s).',
                    ];
                }

                if ($firstAttemptAt && ($now - $firstAttemptAt) > $windowSeconds) {
                    $pdo->prepare('DELETE FROM login_attempts WHERE id = ?')->execute([$row['id']]);
                    return ['allowed' => true];
                }

                if ((int) $row['attempt_count'] >= $maxAttempts) {
                    $remaining = max(1, $windowSeconds - ($now - $lastAttemptAt));
                    return [
                        'allowed' => false,
                        'remaining' => $remaining,
                        'message' => 'Trop de tentatives de connexion. Réessayez dans ' . ceil($remaining / 60) . ' minute(s).',
                    ];
                }
            }

            return ['allowed' => true, 'remaining' => 0, 'window' => $windowSeconds, 'maxAttempts' => $maxAttempts];
        } catch (Exception $e) {
            error_log('Rate limit check failed: ' . $e->getMessage());
        }
    }

    fallback_session:
    $state = getLoginAttemptsStateFromSession($identifier, $scope);
    $attempts = $state['attempts'];

    if ($attempts['count'] >= getRateLimitMaxAttempts()) {
        $remaining = getRateLimitWindowSeconds() - (time() - $attempts['time']);
        return [
            'allowed' => false,
            'remaining' => max(1, $remaining),
            'message' => 'Trop de tentatives de connexion. Réessayez dans ' . ceil(max(1, $remaining) / 60) . ' minute(s).',
        ];
    }

    return ['allowed' => true];
}

/**
 * Enregistre une tentative de connexion échouée
 */
function recordFailedAttempt($identifier, $scope = 'login')
{
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        if (function_exists('ensure_session_started')) {
            ensure_session_started();
        } elseif (!headers_sent()) {
            session_start();
        }
    }

    if ($pdo instanceof PDO) {
        if (!ensureLoginAttemptsTable($pdo)) {
            goto fallback_session;
        }

        $identifierKey = getRateLimitIdentifierKey($identifier, $scope);
        $now = time();
        $nowString = date('Y-m-d H:i:s', $now);
        $windowSeconds = getRateLimitWindowSeconds();
        $maxAttempts = getRateLimitMaxAttempts();

        try {
            $stmt = $pdo->prepare('SELECT id, attempt_count, first_attempt_at, last_attempt_at FROM login_attempts WHERE scope = ? AND identifier_key = ? LIMIT 1');
            $stmt->execute([$scope, $identifierKey]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $firstAttemptAt = strtotime($row['first_attempt_at'] ?? '');
                if ($firstAttemptAt && ($now - $firstAttemptAt) > $windowSeconds) {
                    $pdo->prepare('DELETE FROM login_attempts WHERE id = ?')->execute([$row['id']]);
                    $row = null;
                }
            }

            if ($row) {
                $newCount = (int) $row['attempt_count'] + 1;
                $blockedUntil = null;
                if ($newCount >= $maxAttempts) {
                    $blockedUntil = date('Y-m-d H:i:s', $now + $windowSeconds);
                }
                $pdo->prepare('UPDATE login_attempts SET attempt_count = ?, last_attempt_at = ?, blocked_until = ?, updated_at = ? WHERE id = ?')->execute([$newCount, $nowString, $blockedUntil, $nowString, $row['id']]);
            } else {
                $pdo->prepare('INSERT INTO login_attempts (scope, identifier_key, ip_address, attempt_count, first_attempt_at, last_attempt_at, blocked_until, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute([$scope, $identifierKey, getRateLimitClientIp(), 1, $nowString, $nowString, null, $nowString]);
            }
            return;
        } catch (Exception $e) {
            error_log('Rate limit persistence failed: ' . $e->getMessage());
        }
    }

    fallback_session:
    $state = getLoginAttemptsStateFromSession($identifier, $scope);
    $attempts = $state['attempts'];

    if (time() - $attempts['time'] > getRateLimitWindowSeconds()) {
        $attempts = ['count' => 0, 'time' => time()];
    }

    $attempts['count']++;
    $attempts['time'] = time();
    $_SESSION['login_attempts'][$state['key']] = $attempts;
}

/**
 * Réinitialise les tentatives après une connexion réussie
 */
function resetLoginAttempts($identifier, $scope = 'login')
{
    global $pdo;

    if (session_status() === PHP_SESSION_NONE) {
        if (function_exists('ensure_session_started')) {
            ensure_session_started();
        } elseif (!headers_sent()) {
            session_start();
        }
    }

    if ($pdo instanceof PDO) {
        $identifierKey = getRateLimitIdentifierKey($identifier, $scope);
        try {
            $pdo->prepare('DELETE FROM login_attempts WHERE scope = ? AND identifier_key = ?')->execute([$scope, $identifierKey]);
            return;
        } catch (Exception $e) {
            error_log('Rate limit reset failed: ' . $e->getMessage());
        }
    }

    if (!isset($_SESSION['login_attempts'])) {
        return;
    }

    $key = md5($scope . ':' . $identifier . ':' . getRateLimitClientIp());
    unset($_SESSION['login_attempts'][$key]);
}

/**
 * Génère un token CSRF
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le token CSRF
 */
function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valide le format d'un nom d'utilisateur
 */
function validateUsername($username)
{
    // Permet username ou email (car login.php accepte les deux)
    if (empty($username)) {
        return ['valid' => false, 'error' => 'Le nom d\'utilisateur ou l\'email est requis'];
    }

    if (strlen($username) < 3 || strlen($username) > 255) {
        return ['valid' => false, 'error' => 'Le nom d\'utilisateur ou l\'email doit contenir entre 3 et 255 caractères'];
    }

    // Si c'est un email, valider le format email
    if (strpos($username, '@') !== false) {
        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Format d\'email invalide'];
        }
    } else {
        // Si c'est un username, valider qu'il ne contient que des caractères autorisés
        if (!preg_match('/^[a-zA-Z0-9_@.]+$/', $username)) {
            return ['valid' => false, 'error' => 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, underscores, @ et points'];
        }
    }

    return ['valid' => true];
}

/**
 * Valide le format d'un mot de passe
 */
function validatePassword($password)
{
    if (empty($password)) {
        return ['valid' => false, 'error' => 'Le mot de passe est requis'];
    }

    if (strlen($password) < 1) {
        return ['valid' => false, 'error' => 'Le mot de passe ne peut pas être vide'];
    }

    // Exiger minimum 8 caractères pour la sécurité
    if (strlen($password) < 8) {
        return ['valid' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caractères'];
    }

    return ['valid' => true];
}

/**
 * Nettoie les entrées utilisateur
 */
function sanitizeInput($input)
{
    return trim(strip_tags($input));
}

/**
 * Échappe les données pour l'affichage HTML
 */
function escapeOutput($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
