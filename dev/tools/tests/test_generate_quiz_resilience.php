<?php
/**
 * Test CLI de robustesse pour src/api/ia/generate_quiz.php
 *
 * Usage:
 *   php dev/tools/tests/test_generate_quiz_resilience.php --rootUrl=http://localhost/moncoachscolaire
 *
 * Options:
 *   --rootUrl=...                  URL racine du projet (sans /src), défaut: http://localhost/moncoachscolaire
 *   --csrfPage=...                 Page HTML pour récupérer window.csrfToken
 *   --level=...                    Niveau, défaut: 4eme
 *   --subject=...                  Matière, défaut: Mathématiques
 *   --type=...                     Type de quiz, défaut: qcm
 *   --providers=groq,openai,...    Providers à forcer (scénarios dédiés)
 *   --timeout=20                   Timeout HTTP client en secondes
 *   --skipInvalidProvider=1        Désactive le scénario provider invalide
 */

declare(strict_types=1);

$options = getopt('', [
    'rootUrl::',
    'csrfPage::',
    'level::',
    'subject::',
    'type::',
    'providers::',
    'runSimulatedFallback::',
    'timeout::',
    'skipInvalidProvider::',
]);

$rootUrl = rtrim((string)($options['rootUrl'] ?? 'http://localhost/moncoachscolaire'), '/');
$csrfPage = (string)($options['csrfPage'] ?? ($rootUrl . '/public/index.php?page=system/exercices'));
$level = (string)($options['level'] ?? '4eme');
$subject = (string)($options['subject'] ?? 'Mathématiques');
$type = (string)($options['type'] ?? 'qcm');
$runSimulatedFallback = ((string)($options['runSimulatedFallback'] ?? '1')) === '1';
$timeout = max(5, (int)($options['timeout'] ?? 20));
$skipInvalidProvider = ((string)($options['skipInvalidProvider'] ?? '0')) === '1';
$providersArg = trim((string)($options['providers'] ?? ''));

$providers = [];
if ($providersArg !== '') {
    $providers = array_values(array_filter(array_map('trim', explode(',', $providersArg)), static fn(string $v): bool => $v !== ''));
}

$endpoint = $rootUrl . '/src/api/ia/generate_quiz.php';
$cookieFile = tempnam(sys_get_temp_dir(), 'mcs_quiz_cookie_');
if ($cookieFile === false) {
    fwrite(STDERR, "[ERROR] Impossible de créer un cookie jar temporaire\n");
    exit(1);
}

register_shutdown_function(static function () use ($cookieFile): void {
    if (is_file($cookieFile)) {
        @unlink($cookieFile);
    }
});

echo "=== Quiz IA Resilience Test ===\n";
echo "Root URL : {$rootUrl}\n";
echo "CSRF Page: {$csrfPage}\n";
echo "Endpoint : {$endpoint}\n";

$csrfToken = fetchCsrfToken($csrfPage, $cookieFile, $timeout);
if ($csrfToken === '') {
    fwrite(STDERR, "[ERROR] CSRF token introuvable. Vérifie --csrfPage ou la session.\n");
    exit(1);
}

$scenarios = [];
$scenarios[] = [
    'name' => 'default-order',
    'provider' => null,
    'expected_status' => [200, 502],
];

if ($runSimulatedFallback) {
    $scenarios[] = [
        'name' => 'simulated-invalid-json-fallback',
        'provider' => 'openai',
        'expected_status' => [200],
        'expected_provider_used' => 'groq',
        'expected_success' => true,
        'min_questions' => 5,
        'debug_provider_overrides' => [
            'openai' => ['mode' => 'invalid_json'],
            'groq' => ['mode' => 'success', 'label' => 'Fallback Groq'],
        ],
    ];
    $scenarios[] = [
        'name' => 'simulated-timeout-fallback',
        'provider' => 'openai',
        'expected_status' => [200],
        'expected_provider_used' => 'groq',
        'expected_success' => true,
        'min_questions' => 5,
        'debug_provider_overrides' => [
            'openai' => ['mode' => 'timeout'],
            'groq' => ['mode' => 'success', 'label' => 'Timeout Recovery'],
        ],
    ];
}

foreach ($providers as $providerName) {
    $scenarios[] = [
        'name' => 'force-' . $providerName,
        'provider' => $providerName,
        'expected_status' => [200, 502, 422],
    ];
}

if (!$skipInvalidProvider) {
    $scenarios[] = [
        'name' => 'invalid-provider',
        'provider' => '__invalid_provider__',
        'expected_status' => [422],
    ];
}

$results = [];
foreach ($scenarios as $scenario) {
    $payload = [
        'level' => $level,
        'subject' => $subject,
        'type' => $type,
        'csrf_token' => $csrfToken,
    ];

    if ($scenario['provider'] !== null) {
        $payload['provider'] = $scenario['provider'];
    }

    if (isset($scenario['debug_provider_overrides'])) {
        $payload['_debug_provider_overrides'] = $scenario['debug_provider_overrides'];
    }

    $startedAt = microtime(true);
    [$statusCode, $rawResponse, $curlError] = postJson($endpoint, $payload, $cookieFile, $timeout, $csrfToken);
    $durationMs = (int)round((microtime(true) - $startedAt) * 1000);

    $json = null;
    $jsonError = null;
    if ($rawResponse !== '') {
        $json = json_decode($rawResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            $json = null;
        }
    }

    $providerUsed = is_array($json) ? (string)($json['provider_used'] ?? '') : '';
    $questionsCount = is_array($json) && isset($json['questions']) && is_array($json['questions']) ? count($json['questions']) : 0;
    $success = is_array($json) ? (bool)($json['success'] ?? false) : false;
    $errorMsg = is_array($json) ? (string)($json['error'] ?? '') : '';

    $statusOk = in_array($statusCode, $scenario['expected_status'], true);
    if ($statusOk && array_key_exists('expected_provider_used', $scenario)) {
        $statusOk = $providerUsed === (string)$scenario['expected_provider_used'];
    }
    if ($statusOk && array_key_exists('expected_success', $scenario)) {
        $statusOk = $success === (bool)$scenario['expected_success'];
    }
    if ($statusOk && array_key_exists('min_questions', $scenario)) {
        $statusOk = $questionsCount >= (int)$scenario['min_questions'];
    }

    $results[] = [
        'scenario' => $scenario['name'],
        'forced_provider' => $scenario['provider'],
        'debug_provider_overrides' => $scenario['debug_provider_overrides'] ?? null,
        'http_status' => $statusCode,
        'status_ok' => $statusOk,
        'duration_ms' => $durationMs,
        'curl_error' => $curlError,
        'json_error' => $jsonError,
        'success' => $success,
        'provider_used' => $providerUsed,
        'questions_count' => $questionsCount,
        'error' => $errorMsg,
        'raw_preview' => mb_substr($rawResponse, 0, 220),
    ];
}

$allStatusOk = true;
foreach ($results as $result) {
    if (!$result['status_ok']) {
        $allStatusOk = false;
        break;
    }
}

foreach ($results as $result) {
    $flag = $result['status_ok'] ? 'OK' : 'KO';
    echo sprintf(
        "[%s] %-18s status=%s provider_used=%s q=%d time=%dms\n",
        $flag,
        $result['scenario'],
        (string)$result['http_status'],
        $result['provider_used'] !== '' ? $result['provider_used'] : '-',
        (int)$result['questions_count'],
        (int)$result['duration_ms']
    );

    if ($result['curl_error'] !== '') {
        echo "      curl_error: {$result['curl_error']}\n";
    }
    if ($result['json_error'] !== null) {
        echo "      json_error: {$result['json_error']}\n";
    }
    if ($result['error'] !== '') {
        echo "      api_error: {$result['error']}\n";
    }
}

echo "\n--- JSON REPORT ---\n";
echo json_encode(
    [
        'endpoint' => $endpoint,
        'csrf_page' => $csrfPage,
        'level' => $level,
        'subject' => $subject,
        'all_status_ok' => $allStatusOk,
        'results' => $results,
    ],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
) . "\n";

exit($allStatusOk ? 0 : 2);

function fetchCsrfToken(string $url, string $cookieFile, int $timeout): string
{
    [$statusCode, $body, $curlError] = httpGet($url, $cookieFile, $timeout);

    if ($curlError !== '' || $statusCode < 200 || $statusCode >= 400 || $body === '') {
        return '';
    }

    if (preg_match('/window\\.csrfToken\\s*=\\s*["\']([^"\']+)["\']\\s*;/', $body, $matches) === 1) {
        return (string)($matches[1] ?? '');
    }

    if (preg_match('/name=["\']csrf_token["\']\\s+value=["\']([^"\']+)["\']/', $body, $matches) === 1) {
        return (string)($matches[1] ?? '');
    }

    return '';
}

/**
 * @return array{0:int,1:string,2:string}
 */
function httpGet(string $url, string $cookieFile, int $timeout): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/json;q=0.9,*/*;q=0.8',
        ],
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$statusCode, is_string($response) ? $response : '', $error];
}

/**
 * @return array{0:int,1:string,2:string}
 */
function postJson(string $url, array $payload, string $cookieFile, int $timeout, string $csrfToken): array
{
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonPayload === false) {
        return [0, '', 'Payload JSON invalide'];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-CSRF-Token: ' . $csrfToken,
        ],
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$statusCode, is_string($response) ? $response : '', $error];
}
