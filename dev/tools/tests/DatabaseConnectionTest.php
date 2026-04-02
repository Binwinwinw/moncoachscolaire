<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test de la connexion à la base de données
 */
class DatabaseConnectionTest extends TestCase
{
    private $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Charger les variables d'environnement pour les tests
        $this->loadEnvironment();
    }

    protected function tearDown(): void
    {
        $this->pdo = null;
        parent::tearDown();
    }

    private function loadEnvironment(): void
    {
        $envFile = dirname(__DIR__) . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    public function testDatabaseConnectionSucceeds(): void
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_DATABASE') ?: 'moncoachscolaire';
        $username = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        try {
            $this->pdo = new \PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $username,
                $password,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
            
            $this->assertInstanceOf(\PDO::class, $this->pdo);
            $this->assertNotNull($this->pdo);
        } catch (\PDOException $e) {
            $this->fail('Database connection failed: ' . $e->getMessage());
        }
    }

    public function testDatabaseTablesExist(): void
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_DATABASE') ?: 'moncoachscolaire';
        $username = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        try {
            $this->pdo = new \PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $username,
                $password,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            // Vérifier que la table users existe
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'users'");
            $result = $stmt->fetch();
            
            $this->assertNotEmpty($result, 'La table users devrait exister');
        } catch (\PDOException $e) {
            $this->markTestSkipped('Cannot connect to database: ' . $e->getMessage());
        }
    }

    public function testReadOnlyModeFunction(): void
    {
        require_once dirname(__DIR__) . '/db/connection.php';
        
        $this->assertTrue(function_exists('db_is_read_only'));
        
        // Par défaut, le mode lecture seule devrait être false
        $isReadOnly = db_is_read_only();
        $this->assertIsBool($isReadOnly);
    }

    public function testEnvironmentVariablesAreSet(): void
    {
        $dbHost = getenv('DB_HOST');
        $dbDatabase = getenv('DB_DATABASE');
        $dbUsername = getenv('DB_USERNAME');
        
        // Au moins les variables de base devraient être définies
        $this->assertNotEmpty($dbHost, 'DB_HOST devrait être défini');
        $this->assertNotEmpty($dbDatabase, 'DB_DATABASE devrait être défini');
        $this->assertNotEmpty($dbUsername, 'DB_USERNAME devrait être défini');
    }
}
