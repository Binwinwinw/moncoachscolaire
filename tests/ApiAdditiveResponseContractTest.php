<?php

declare(strict_types=1);

$repoRoot = dirname(__DIR__);
$responseFile = $repoRoot . '/src/api/_core/response.php';

if (!is_file($responseFile)) {
    fwrite(STDERR, "response.php introuvable\n");
    exit(1);
}

/**
 * Execute a temporary PHP script that calls an exiting helper and capture JSON output.
 */
$runScript = static function (string $phpCode) use ($repoRoot): string {
    $tmpFile = $repoRoot . '/tmp/additive_response_test.php';
    file_put_contents($tmpFile, $phpCode);

    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open('php -d display_errors=0 ' . escapeshellarg($tmpFile), $descriptorSpec, $pipes);
    if (!is_resource($process)) {
        @unlink($tmpFile);
        throw new RuntimeException('Impossible de lancer le processus de test');
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    @unlink($tmpFile);

    $output = trim($stdout);
    if ($output === '' && trim($stderr) !== '') {
        $output = trim($stderr);
    }

    return $output;
};

$successCode = <<<'PHP'
<?php
require RESPONSE_FILE;
api_additive_response([
    'success' => true,
    'message' => 'ok legacy',
    'count' => 2,
], 200, [
    'count' => 2,
], [
    'saved_at' => '2026-07-03T12:00:00+00:00',
]);
PHP;

$errorCode = <<<'PHP'
<?php
require RESPONSE_FILE;
api_additive_error('invalid payload', 400, [
    'success' => false,
    'message' => 'legacy error',
], [
    'source' => 'test',
]);
PHP;

$successCode = str_replace('RESPONSE_FILE', var_export($responseFile, true), $successCode);
$errorCode = str_replace('RESPONSE_FILE', var_export($responseFile, true), $errorCode);

$successOutput = $runScript($successCode);
$successJson = json_decode($successOutput, true);
if (!is_array($successJson)) {
    fwrite(STDERR, "Sortie succès non JSON\n" . $successOutput . "\n");
    exit(1);
}

if (($successJson['success'] ?? null) !== true) {
    fwrite(STDERR, "Champ success attendu a true en succès\n");
    exit(1);
}

if (($successJson['message'] ?? '') !== 'ok legacy') {
    fwrite(STDERR, "Champ legacy message absent en succès\n");
    exit(1);
}

if (!isset($successJson['data']) || !is_array($successJson['data'])) {
    fwrite(STDERR, "Champ data absent en succès\n");
    exit(1);
}

if (!isset($successJson['meta']['request_id']) || trim((string) $successJson['meta']['request_id']) === '') {
    fwrite(STDERR, "Champ meta.request_id absent en succès\n");
    exit(1);
}

$errorOutput = $runScript($errorCode);
$errorJson = json_decode($errorOutput, true);
if (!is_array($errorJson)) {
    fwrite(STDERR, "Sortie erreur non JSON\n" . $errorOutput . "\n");
    exit(1);
}

if (($errorJson['success'] ?? null) !== false) {
    fwrite(STDERR, "Champ success attendu a false en erreur\n");
    exit(1);
}

if (($errorJson['error'] ?? '') !== 'invalid payload') {
    fwrite(STDERR, "Champ error absent en erreur\n");
    exit(1);
}

if (!isset($errorJson['meta']['request_id']) || trim((string) $errorJson['meta']['request_id']) === '') {
    fwrite(STDERR, "Champ meta.request_id absent en erreur\n");
    exit(1);
}

echo "Api additive response contract test passed\n";
