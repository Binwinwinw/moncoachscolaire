<?php

use PHPUnit\Framework\TestCase;

class LoginSecurityRateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $GLOBALS['pdo'] = null;
    }

    public function testRateLimitPersistsInDatabase(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('Driver SQLite PDO non disponible dans cet environnement.');
        }

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            scope VARCHAR(64) NOT NULL,
            identifier_key VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempt_count INT NOT NULL DEFAULT 0,
            first_attempt_at DATETIME NOT NULL,
            last_attempt_at DATETIME NOT NULL,
            blocked_until DATETIME NULL,
            updated_at DATETIME NOT NULL
        )');
        $GLOBALS['pdo'] = $pdo;

        require_once __DIR__ . '/../src/includes/login_security.php';

        for ($i = 0; $i < 5; $i++) {
            recordFailedAttempt('alice@example.com', 'login');
        }

        $result = checkLoginAttempts('alice@example.com', 'login');

        $this->assertFalse($result['allowed']);
        $this->assertArrayHasKey('remaining', $result);
        $this->assertGreaterThan(0, $result['remaining']);
    }

    public function testRateLimitFallsBackToSessionWhenTableInitializationFails(): void
    {
        $GLOBALS['pdo'] = new class extends PDO {
            public function __construct() {}

            public function getAttribute(int $attribute): mixed
            {
                return 'mysql';
            }

            public function exec(string $statement): int|false
            {
                throw new PDOException('Database unavailable');
            }
        };

        require_once __DIR__ . '/../src/includes/login_security.php';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            recordFailedAttempt('fallback@example.com', 'login');
        }

        $result = checkLoginAttempts('fallback@example.com', 'login');

        $this->assertFalse($result['allowed']);
        $this->assertGreaterThan(0, $result['remaining']);
    }
}
