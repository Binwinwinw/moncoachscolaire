<?php
/**
 * Test CLI de robustesse pour src/api/ia/generate_precise_course.php
 */

declare(strict_types=1);

$options = getopt('', [
    'rootUrl::',
    'csrfPage::',
    'level::',
    'subject::',
    'timeout::',
]);

$rootUrl = rtrim((string) ($options['rootUrl'] ?? 'http://localhost/moncoachscolaire'), '/');
$csrfPage = (string) ($options['csrfPage'] ?? ($rootUrl . '/public/index.php?page=system/exercices'));
$level = (string) ($options['level'] ?? '4eme');
$subject = (string) ($options['subject'] ?? 'Mathématiques');
$timeout = max(5, (int) ($options['timeout'] ?? 20));

$endpoint = $rootUrl . '/src/api/ia/generate_precise_course.php';
$routerEndpoint = $rootUrl . '/index.php?page=api/ia/generate_precise_course';
$cookieFile = tempnam(sys_get_temp_dir(), 'mcs_precise_course_cookie_');
if ($cookieFile === false) {
    fwrite(STDERR, "[ERROR] Impossible de créer un cookie jar temporaire\n");
    exit(1);
}

register_shutdown_function(static function () use ($cookieFile): void {
    if (is_file($cookieFile)) {
        @unlink($cookieFile);
    }
});

echo "=== Precise Course AI Test ===\n";
echo "Root URL : {$rootUrl}\n";
echo "CSRF Page: {$csrfPage}\n";
echo "Endpoint : {$endpoint}\n";
echo "Router   : {$routerEndpoint}\n";

$csrfToken = fetchCsrfToken($csrfPage, $cookieFile, $timeout);
if ($csrfToken === '') {
    fwrite(STDERR, "[ERROR] CSRF token introuvable. Vérifie --csrfPage ou la session.\n");
    exit(1);
}

$scenarios = [
    [
        'name' => 'simulated-fallback',
        'expected_status' => [200],
        'expected_provider_used' => 'groq',
        'payload' => [
            'level' => $level,
            'subject' => $subject,
            'competence' => 'Calcul littéral',
            'official_correction' => 'On regroupe les termes semblables avant de conclure.',
            'provider' => 'openai',
            'incorrect_items' => [
                [
                    'question' => 'Réduis 2x + 3x.',
                    'user_answer' => '6x',
                    'correct_answer' => '5x',
                    'official_correction' => '2x + 3x = 5x car on additionne les coefficients.',
                    'question_type' => 'qcm',
                ],
            ],
            '_debug_provider_overrides' => [
                'openai' => ['mode' => 'invalid_json'],
                'groq' => ['mode' => 'success', 'label' => 'Fallback Groq'],
            ],
        ],
    ],
    [
        'name' => 'invalid-provider',
        'expected_status' => [422],
        'target' => 'direct',
        'payload' => [
            'level' => $level,
            'subject' => $subject,
            'provider' => '__invalid_provider__',
            'incorrect_items' => [
                [
                    'question' => 'Réduis 2x + 3x.',
                    'user_answer' => '6x',
                    'correct_answer' => '5x',
                    'question_type' => 'qcm',
                ],
            ],
        ],
    ],
    [
        'name' => 'router-smoke',
        'expected_status' => [200],
        'expected_provider_used' => 'groq',
        'target' => 'router',
        'payload' => [
            'level' => $level,
            'subject' => $subject,
            'competence' => 'Calcul littéral',
            'official_correction' => 'On regroupe les termes semblables avant de conclure.',
            'provider' => 'openai',
            'incorrect_items' => [
                [
                    'question' => 'Réduis 2x + 3x.',
                    'user_answer' => '6x',
                    'correct_answer' => '5x',
                    'official_correction' => '2x + 3x = 5x car on additionne les coefficients.',
                    'question_type' => 'qcm',
                ],
            ],
            '_debug_provider_overrides' => [
                'openai' => ['mode' => 'invalid_json'],
                'groq' => ['mode' => 'success', 'label' => 'Fallback Groq Routed'],
            ],
        ],
    ],
];

$results = [];

foreach ($scenarios as $scenario) {
    $payload = $scenario['payload'];
    $payload['csrf_token'] = $csrfToken;
    $targetUrl = ($scenario['target'] ?? 'direct') === 'router' ? $routerEndpoint : $endpoint;

    $startedAt = microtime(true);
    [$statusCode, $rawResponse, $curlError] = postJson($targetUrl, $payload, $cookieFile, $timeout, $csrfToken);
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

    $json = null;
    $jsonError = null;
    if ($rawResponse !== '') {
        $json = json_decode($rawResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            $json = null;
        }
    }

    $providerUsed = is_array($json) ? (string) ($json['provider_used'] ?? '') : '';
    $title = is_array($json) ? (string) ($json['data']['title'] ?? '') : '';
    $summary = is_array($json) ? (string) ($json['data']['summary'] ?? '') : '';
    $statusOk = in_array($statusCode, $scenario['expected_status'], true);

    if ($statusOk && isset($scenario['expected_provider_used'])) {
        $statusOk = $providerUsed === $scenario['expected_provider_used'];
    }

    if ($statusOk && $statusCode === 200) {
        $statusOk = $title !== '' && $summary !== '';
    }

    $results[] = [
        'scenario' => $scenario['name'],
        'target' => $scenario['target'] ?? 'direct',
        'http_status' => $statusCode,
        'status_ok' => $statusOk,
        'duration_ms' => $durationMs,
        'provider_used' => $providerUsed,
        'title' => $title,
        'summary' => $summary,
        'curl_error' => $curlError,
        'json_error' => $jsonError,
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
        "[%s] %-20s status=%s provider_used=%s time=%dms\n",
        $flag,
        $result['scenario'] . ' (' . $result['target'] . ')',
        (string) $result['http_status'],
        $result['provider_used'] !== '' ? $result['provider_used'] : '-',
        (int) $result['duration_ms']
    );

    if ($result['title'] !== '') {
        echo '      title: ' . $result['title'] . "\n";
    }
    if ($result['summary'] !== '') {
        echo '      summary: ' . $result['summary'] . "\n";
    }
    if ($result['curl_error'] !== '') {
        echo '      curl_error: ' . $result['curl_error'] . "\n";
    }
    if ($result['json_error'] !== null) {
        echo '      json_error: ' . $result['json_error'] . "\n";
    }
}

echo "\n--- JSON REPORT ---\n";
echo json_encode([
    'endpoint' => $endpoint,
    'router_endpoint' => $routerEndpoint,
    'csrf_page' => $csrfPage,
    'all_status_ok' => $allStatusOk,
    'results' => $results,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";

exit($allStatusOk ? 0 : 2);

function fetchCsrfToken(string $url, string $cookieFile, int $timeout): string
{
    [$statusCode, $body, $curlError] = httpGet($url, $cookieFile, $timeout);

    if ($curlError !== '' || $statusCode < 200 || $statusCode >= 400 || $body === '') {
        return '';
    }

    if (preg_match('/window\\.csrfToken\\s*=\\s*["\']([^"\']+)["\']\\s*;/', $body, $matches) === 1) {
        return (string) ($matches[1] ?? '');
    }

    if (preg_match('/name=["\']csrf_token["\']\\s+value=["\']([^"\']+)["\']/', $body, $matches) === 1) {
        return (string) ($matches[1] ?? '');
    }

    return '';
}

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
    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$statusCode, is_string($response) ? $response : '', $error];
}

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
        CURLOPT_FOLLOWLOCATION => true,
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
    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$statusCode, is_string($response) ? $response : '', $error];
}
