<?php

declare(strict_types=1);

$repoRoot = dirname(__DIR__);
$rateLimitFile = $repoRoot . '/dev/reports/api_rate_limits.json';
$endpointPath = $repoRoot . '/src/api/ia/generate_quiz.php';

if (is_file($rateLimitFile)) {
    @unlink($rateLimitFile);
}

$payload = json_encode([
    'level' => 'Seconde',
    'subject' => 'Mathématiques',
    'topic' => 'Fractions',
    'type' => 'quiz',
    'provider' => 'ollama',
    'csrf_token' => 'test-token',
]);

$runEndpoint = function (string $scriptPath, string $payload) use ($repoRoot): array {
    $script = <<<'PHP'
<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTP_ORIGIN'] = 'http://localhost';
$_SERVER['HTTP_X_CSRF_TOKEN'] = 'test-token';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_id('rate-limit-test');
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['logged_in'] = true;
$_SESSION['csrf_token'] = 'test-token';
require %s;
PHP;

    $tmpFile = $repoRoot . '/tmp/rate_limit_test.php';
    $content = sprintf($script, var_export($scriptPath, true));
    file_put_contents($tmpFile, $content);

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open('php -d display_errors=0 ' . escapeshellarg($tmpFile), $descriptorSpec, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], $payload);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
    } else {
        $output = '';
        $errors = '';
        $exitCode = 1;
    }

    @unlink($tmpFile);

    return [
        'output' => trim($output . PHP_EOL . $errors),
        'exitCode' => $exitCode,
    ];
};

$results = [];
for ($i = 0; $i < 11; $i++) {
    $results[] = $runEndpoint($endpointPath, $payload);
}

$firstRun = $results[0];
$limitExceededRun = $results[10];

if (strpos($limitExceededRun['output'], 'ERR_RATE_LIMIT') === false) {
    fwrite(STDERR, "Expected rate-limit response after exceeding the quota.\n");
    fwrite(STDERR, $limitExceededRun['output'] . "\n");
    exit(1);
}

if ($firstRun['exitCode'] !== 0 && $firstRun['exitCode'] !== 1) {
    fwrite(STDERR, "First request should not fail unexpectedly.\n");
    exit(1);
}

echo "Rate limiting test passed for generate_quiz.php\n";
