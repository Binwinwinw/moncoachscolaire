<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test des fonctions helper
 */
class HelperFunctionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__) . '/config.php';
    }

    public function testHtmlspecialcharsWrapperExists(): void
    {
        $this->assertTrue(
            function_exists('e'),
            'La fonction helper e() pour htmlspecialchars devrait exister'
        );
    }

    public function testRedirectExists(): void
    {
        $this->assertTrue(
            function_exists('redirect'),
            'La fonction redirect devrait exister'
        );
    }

    public function testCsrfTokenFunctions(): void
    {
        $this->assertTrue(
            function_exists('generateCsrfToken') || function_exists('csrf_token'),
            'Une fonction de génération de token CSRF devrait exister'
        );
    }

    /**
     * @dataProvider siteUrlProvider
     */
    public function testSiteUrlGeneratesCorrectUrls(string $page, string $expectedContains): void
    {
        $url = site_url($page);
        
        $this->assertIsString($url);
        $this->assertStringContainsString($expectedContains, $url);
    }

    public static function siteUrlProvider(): array
    {
        return [
            'dashboard' => ['dashboard', 'dashboard'],
            'cours' => ['cours', 'cours'],
            'exercices' => ['exercices', 'exercices'],
            'quiz' => ['quiz', 'quiz'],
        ];
    }

    public function testDetectBaseUrlReturnsString(): void
    {
        $baseUrl = detectBaseUrl();
        
        $this->assertIsString($baseUrl);
        $this->assertNotEmpty($baseUrl);
    }

    public function testDetectBaseUrlEndsWithSlash(): void
    {
        $baseUrl = detectBaseUrl();
        
        // La base URL devrait se terminer par un slash ou être vide
        if (!empty($baseUrl)) {
            $this->assertMatchesRegularExpression(
                '/\/$/',
                $baseUrl,
                'detectBaseUrl devrait retourner une URL se terminant par /'
            );
        }
    }

    public function testHtmlEscaping(): void
    {
        $input = '<script>alert("XSS")</script>';
        
        if (function_exists('e')) {
            $escaped = e($input);
            $this->assertStringNotContainsString('<script>', $escaped);
            $this->assertStringContainsString('&lt;script&gt;', $escaped);
        } else {
            $this->markTestSkipped('Fonction e() non disponible');
        }
    }
}
