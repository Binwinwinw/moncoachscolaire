<?php
/**
 * Login + session helper for HTTP tests
 *
 * Usage:
 *   MCS_USERNAME=... MCS_PASSWORD=... php dev/tools/tests/test_login_session.php https://example.com
 *
 * Output:
 *   MCS_SESSION_ID=...\nCSRF_TOKEN=...
 */

$base = getenv('MCS_BASE_URL') ?: ($argv[1] ?? 'http://localhost/moncoachscolaire/public');
$base = rtrim($base, '/');

$root = dirname(__DIR__, 3);
$envLocalPath = $root . '/.env.local';
if (is_file($envLocalPath)) {
    $lines = file($envLocalPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

$username = getenv('MCS_USERNAME');
$password = getenv('MCS_PASSWORD');
$proofUrlOverride = getenv('MCS_PROOF_URL');

if (!$username || !$password) {
    fwrite(STDERR, "Missing MCS_USERNAME or MCS_PASSWORD\n");
    exit(1);
}

$tmpDir = $root . '/.tmp';
if (!is_dir($tmpDir)) {
    @mkdir($tmpDir, 0755, true);
}

$cookieJar = tempnam($tmpDir, 'mcs_');
if ($cookieJar === false) {
    fwrite(STDERR, "Cannot create cookie jar\n");
    exit(1);
}
$cookieName = getenv('MCS_SESSION_COOKIE_NAME') ?: 'PHPSESSID';
$allowCrossHost = getenv('MCS_ALLOW_CROSS_HOST_REDIRECTS') === '1';
$keepTmp = getenv('MCS_KEEP_TMP') === '1';
$authJson = $tmpDir . '/mcs.auth.json';

register_shutdown_function(function () use ($cookieJar, $keepTmp, $authJson) {
    if (!$keepTmp && is_file($cookieJar)) {
        @unlink($cookieJar);
        $payload = ['cookiesFileDeleted' => true];
        @file_put_contents($authJson, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
});

$loginUrl = $base . '/index.php?page=login';

$origin = $base;
$parts = parse_url($base);
if (!empty($parts['scheme']) && !empty($parts['host'])) {
    $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
}

function parse_redirect_location(string $headers): ?string {
    if (preg_match('/\nLocation:\s*([^\r\n]+)/i', $headers, $m)) {
        return trim($m[1]);
    }
    return null;
}

function resolve_url(string $baseUrl, string $location, string $origin): string {
    if (strpos($location, '//') === 0) {
        $scheme = parse_url($origin, PHP_URL_SCHEME) ?: 'http';
        return $scheme . ':' . $location;
    }
    if (preg_match('/^https?:\/\//i', $location)) {
        return $location;
    }
    if (strpos($location, '?') === 0) {
        $parts = parse_url($baseUrl);
        $path = $parts['path'] ?? '/';
        return $origin . $path . $location;
    }
    if (strpos($location, '/') === 0) {
        return $origin . $location;
    }
    return rtrim($baseUrl, '/') . '/' . ltrim($location, '/');
}

function curl_request(string $url, array $options, string $cookieJar, string $origin, int $maxRedirects = 5, bool $allowCrossHost = false): array {
    $currentUrl = $url;
    $lastBody = '';
    $lastCode = 0;
    $lastHeaders = '';
    $lastError = null;
    $redirectBlockedHost = null;
    $redirectTarget = null;
    $requestOptions = $options;

    for ($i = 0; $i <= $maxRedirects; $i++) {
        $ch = curl_init($currentUrl);
        curl_setopt_array($ch, $requestOptions + [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => $cookieJar,
            CURLOPT_COOKIEFILE => $cookieJar,
            CURLOPT_USERAGENT => 'MCS-Test-Client/1.0',
            CURLOPT_ENCODING => '',
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        $response = curl_exec($ch);
        $lastCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        if ($response === false) {
            $lastError = curl_error($ch);
        }
        curl_close($ch);

        if ($response === false) {
            if ($lastError) {
                fwrite(STDERR, "cURL error: {$lastError}\n");
            }
            break;
        }

        $lastHeaders = substr($response, 0, $headerSize);
        $lastBody = substr($response, $headerSize);

        if (in_array($lastCode, [301, 302, 303, 307, 308], true)) {
            $location = parse_redirect_location($lastHeaders);
            if (!$location) {
                break;
            }
            $nextUrl = resolve_url($currentUrl, $location, $origin);
            $nextHost = parse_url($nextUrl, PHP_URL_HOST);
            $originHost = parse_url($origin, PHP_URL_HOST);
            if (!$allowCrossHost && $nextHost && $originHost && strcasecmp($nextHost, $originHost) !== 0) {
                $redirectBlockedHost = $nextHost;
                $redirectTarget = $nextUrl;
                break;
            }
            $currentUrl = $nextUrl;
            if ($lastCode === 302 || $lastCode === 303) {
                unset($requestOptions[CURLOPT_POST], $requestOptions[CURLOPT_POSTFIELDS]);
                $requestOptions[CURLOPT_HTTPGET] = true;
            }
            continue;
        }

        break;
    }

    return [
        'url' => $currentUrl,
        'code' => $lastCode,
        'headers' => $lastHeaders,
        'body' => $lastBody,
        'redirectBlockedHost' => $redirectBlockedHost,
        'redirectTarget' => $redirectTarget
    ];
}

$loginGet = curl_request($loginUrl, [], $cookieJar, $origin, 5, $allowCrossHost);
$html = $loginGet['body'];
$code = $loginGet['code'];
$effectiveUrl = $loginGet['url'];

if ($code < 200 || $code >= 300 || !$html) {
    fwrite(STDERR, "Login page fetch failed (HTTP $code)\n");
    if (!empty($effectiveUrl)) {
        fwrite(STDERR, "Effective URL: {$effectiveUrl}\n");
    }
    if ($code >= 300 && $code < 400 && !empty($loginGet['redirectTarget'])) {
        fwrite(STDERR, "Redirect blocked to: {$loginGet['redirectTarget']} (host={$loginGet['redirectBlockedHost']})\n");
    }
    exit(1);
}

$csrfToken = null;
$formAction = null;

libxml_use_internal_errors(true);
$dom = new DOMDocument();
if ($dom->loadHTML($html)) {
    $xpath = new DOMXPath($dom);
    $csrfNode = $xpath->query('//input[@name="csrf_token"]')->item(0);
    if ($csrfNode && $csrfNode->hasAttribute('value')) {
        $csrfToken = $csrfNode->getAttribute('value');
    }
    $formNode = $xpath->query('//form')->item(0);
    if ($formNode && $formNode->hasAttribute('action')) {
        $formAction = trim($formNode->getAttribute('action'));
    }
}
libxml_clear_errors();

if (!$csrfToken && preg_match('/name="csrf_token"\s+value="([^"]+)"/i', $html, $m)) {
    $csrfToken = $m[1];
}

if (!$csrfToken) {
    fwrite(STDERR, "CSRF token not found in login page\n");
    exit(1);
}

$postUrl = $loginUrl;
if ($formAction) {
    if (preg_match('/^https?:\/\//i', $formAction)) {
        $postUrl = $formAction;
    } elseif (strpos($formAction, '/') === 0) {
        $postUrl = $origin . $formAction;
    } else {
        $postUrl = $base . '/' . ltrim($formAction, '/');
    }
}

$postFields = http_build_query([
    'username' => $username,
    'password' => $password,
    'csrf_token' => $csrfToken
]);

$loginPost = curl_request($postUrl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields
], $cookieJar, $origin, 5, $allowCrossHost);
$response = $loginPost['body'];
$code = $loginPost['code'];
$effectiveUrl = $loginPost['url'];

if ($code < 200 || $code >= 300) {
    fwrite(STDERR, "Login POST failed or stopped on redirect (HTTP $code)\n");
    if (!empty($effectiveUrl)) {
        fwrite(STDERR, "Effective URL: {$effectiveUrl}\n");
    }
    if ($code >= 300 && $code < 400 && !empty($loginPost['redirectTarget'])) {
        fwrite(STDERR, "Redirect blocked to: {$loginPost['redirectTarget']} (host={$loginPost['redirectBlockedHost']})\n");
    }
    exit(1);
}

$cookieText = @file_get_contents($cookieJar);
$sessionId = null;
    if ($cookieText) {
        foreach (preg_split("/\r\n|\n|\r/", $cookieText) as $line) {
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = explode("\t", $line);
            if (count($parts) >= 7) {
                [$domain, $flag, $path, $secure, $exp, $name, $value] = $parts;
                if ($name === $cookieName) {
                    $sessionId = $value;
                    break;
                }
            }
        }
    }

if (!$sessionId) {
    fwrite(STDERR, "Session ID not found in cookie jar\n");
    exit(1);
}

// Preuve post-login : la page login doit rediriger hors formulaire si session authentifiée.
$protectedUrl = $proofUrlOverride ?: ($base . '/index.php?page=login');
$protected = curl_request($protectedUrl, [], $cookieJar, $origin, 5, $allowCrossHost);
$protectedBody = $protected['body'];
$protectedCode = $protected['code'];
$protectedEffective = $protected['url'];

$loginOk = true;
if ($protectedCode === 401 || $protectedCode === 403) {
    $loginOk = false;
}
if ($protectedEffective && strpos($protectedEffective, 'page=login') !== false) {
    $loginOk = false;
}
if ($protectedBody && preg_match('/name\s*=\s*"username"/i', $protectedBody)) {
    $loginOk = false;
}
if ($protectedBody && stripos($protectedBody, 'login') !== false && $protectedCode >= 300) {
    $loginOk = false;
}
if ($protectedCode >= 300 && $protectedCode < 400) {
    $loginOk = false;
    if (!empty($protected['redirectTarget'])) {
        fwrite(STDERR, "Redirect blocked to: {$protected['redirectTarget']} (host={$protected['redirectBlockedHost']})\n");
    }
}

if ($loginOk && $protectedBody && preg_match('/name\s*=\s*"password"/i', $protectedBody)) {
    $loginOk = false;
    fwrite(STDERR, "Auth proof failed: login form still visible after login\n");
}

$authPayload = [
    'ok' => $loginOk,
    'csrfToken' => $csrfToken,
    'cookiesFile' => $cookieJar,
    'proofUrl' => $protectedEffective ?: $protectedUrl,
    'httpCode' => $protectedCode
];

@file_put_contents($authJson, json_encode($authPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "MCS_SESSION_ID={$sessionId}\n";
echo "CSRF_TOKEN={$csrfToken}\n";

exit($loginOk ? 0 : 1);
