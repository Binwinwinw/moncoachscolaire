<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class AiEndpointAuthContractTest extends TestCase
{
    private string $runnerPath;

    protected function setUp(): void
    {
        $this->runnerPath = dirname(__DIR__) . '/tmp/ai_auth_contract_runner.php';
        $root = dirname(__DIR__);
        $script = <<<'PHP'
<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_HOST'] = 'localhost:8081';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$mode = $argv[1] ?? 'guest';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_id('ai-auth-' . preg_replace('/[^a-z0-9-]/i', '', $mode));
    session_start();
}

$_SESSION = [];
if (in_array($mode, ['session-no-csrf', 'session-with-csrf'], true)) {
    $_SESSION['user_id'] = 1;
    $_SESSION['logged_in'] = true;
}
if ($mode === 'session-with-csrf') {
    $_SESSION['csrf_token'] = 'csrf-test';
    $_SERVER['HTTP_ORIGIN'] = 'http://localhost:8081';
    $_SERVER['HTTP_X_CSRF_TOKEN'] = 'csrf-test';
}
if ($mode === 'cli-valid') {
    $_SERVER['HTTP_X_CLI_TOKEN'] = 'cli-secret';
}
if ($mode === 'cli-invalid') {
    $_SERVER['HTTP_X_CLI_TOKEN'] = 'wrong-secret';
}

require %s;
require %s;
require %s;
require %s;

api_require([
    'method' => 'POST',
    'auth_or_cli' => true,
    'csrf' => true,
]);

echo json_encode(['success' => true]);
PHP;

        file_put_contents($this->runnerPath, sprintf(
            $script,
            var_export($root . '/src/api/_core/response.php', true),
            var_export($root . '/src/api/_core/auth.php', true),
            var_export($root . '/src/api/_core/csrf.php', true),
            var_export($root . '/src/api/_core/middleware.php', true)
        ));
    }

    protected function tearDown(): void
    {
        if (is_file($this->runnerPath)) {
            unlink($this->runnerPath);
        }
    }

    #[DataProvider('accessCases')]
    public function testSessionOrCliAccessContract(string $mode, bool $expectedSuccess, ?string $expectedCode): void
    {
        $command = escapeshellarg(PHP_BINARY)
            . ' ' . escapeshellarg($this->runnerPath)
            . ' ' . escapeshellarg($mode);
        $environment = array_merge($_ENV, ['MCSPHP_CLI_API_TOKEN' => 'cli-secret']);
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $environment);

        self::assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        self::assertSame('', trim($errors));
        $payload = json_decode((string) $output, true);
        self::assertIsArray($payload, $output);
        self::assertSame($expectedSuccess, $payload['success'] ?? null);

        if ($expectedCode !== null) {
            self::assertSame($expectedCode, $payload['error']['code'] ?? null);
        }
    }

    public static function accessCases(): array
    {
        return [
            'invité' => ['guest', false, 'ERR_AUTH'],
            'token CLI invalide' => ['cli-invalid', false, 'ERR_AUTH'],
            'token CLI valide' => ['cli-valid', true, null],
            'session sans CSRF' => ['session-no-csrf', false, 'ERR_ORIGIN'],
            'session avec CSRF' => ['session-with-csrf', true, null],
        ];
    }

    public function testAllAiGeneratorsUseTheSharedContract(): void
    {
        $root = dirname(__DIR__);
        $endpoints = [
            'generate_quiz.php',
            'generate_cours.php',
            'generate_exercise_explanation.php',
            'generate_precise_course.php',
        ];

        foreach ($endpoints as $endpoint) {
            $source = file_get_contents($root . '/src/api/ia/' . $endpoint);
            self::assertIsString($source);
            self::assertStringContainsString("'auth_or_cli' => true", $source, $endpoint);
            self::assertStringContainsString("'csrf' => true", $source, $endpoint);
        }
    }
}
