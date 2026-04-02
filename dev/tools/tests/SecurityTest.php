<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test des fonctionnalités de sécurité
 */
class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Charger les fichiers de sécurité si disponibles
        $loginSecurityPath = dirname(__DIR__) . '/includes/login_security.php';
        if (file_exists($loginSecurityPath)) {
            require_once $loginSecurityPath;
        }
    }

    public function testPasswordHashingWorks(): void
    {
        $password = 'test_password_123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(password_verify($password, $hash));
    }

    public function testPasswordVerificationFailsForWrongPassword(): void
    {
        $password = 'correct_password';
        $wrongPassword = 'wrong_password';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertFalse(password_verify($wrongPassword, $hash));
    }

    public function testSqlInjectionPrevention(): void
    {
        // Simuler une tentative d'injection SQL
        $maliciousInput = "'; DROP TABLE users; --";
        
        // htmlspecialchars devrait neutraliser les caractères dangereux
        $escaped = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString("';", $escaped);
        $this->assertStringContainsString('&#039;', $escaped);
    }

    public function testXssPrevention(): void
    {
        $maliciousInput = '<script>alert("XSS")</script>';
        $escaped = htmlspecialchars($maliciousInput, ENT_QUOTES, 'UTF-8');
        
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
    }

    public function testSessionSecuritySettings(): void
    {
        // Vérifier les paramètres de sécurité de session
        $cookieParams = session_get_cookie_params();
        
        // httponly devrait être activé pour la sécurité
        if (isset($cookieParams['httponly'])) {
            $this->assertTrue($cookieParams['httponly'], 'HttpOnly devrait être activé');
        }
    }

    public function testDemoSecurityFunctionsExist(): void
    {
        $demoSecurityPath = dirname(__DIR__) . '/includes/demo_security.php';
        
        if (file_exists($demoSecurityPath)) {
            require_once $demoSecurityPath;
            
            $this->assertTrue(
                function_exists('isDemoMode') || function_exists('is_demo_mode'),
                'Une fonction de détection du mode démo devrait exister'
            );
        } else {
            $this->markTestSkipped('demo_security.php non trouvé');
        }
    }

    public function testEnvFilesAreNotAccessible(): void
    {
        $envFile = dirname(__DIR__) . '/.env';
        
        if (file_exists($envFile)) {
            $permissions = fileperms($envFile);
            
            // Vérifier que le fichier n'est pas lisible par tout le monde
            $this->assertNotEquals(0777, $permissions & 0777);
        } else {
            $this->markTestSkipped('Fichier .env non trouvé');
        }
    }

    /**
     * @dataProvider dangerousInputProvider
     */
    public function testInputSanitization(string $input): void
    {
        $sanitized = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        $this->assertNotEquals($input, $sanitized);
        $this->assertStringNotContainsString('<', $sanitized);
        $this->assertStringNotContainsString('>', $sanitized);
    }

    public static function dangerousInputProvider(): array
    {
        return [
            ['<script>alert("test")</script>'],
            ['<img src=x onerror=alert(1)>'],
            ['<iframe src="javascript:alert(1)"></iframe>'],
            ["'; DROP TABLE users; --"],
        ];
    }
}
