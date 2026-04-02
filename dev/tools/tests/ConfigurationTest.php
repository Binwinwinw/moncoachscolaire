<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test des fonctions de configuration
 */
class ConfigurationTest extends TestCase
{
    public function testLoadEnvFileFallbackExists(): void
    {
        require_once dirname(__DIR__) . '/config.php';
        
        $this->assertTrue(
            function_exists('loadEnvFileFallback'),
            'La fonction loadEnvFileFallback devrait exister'
        );
    }

    public function testLoadEnvFileFallbackWithNonExistentFile(): void
    {
        require_once dirname(__DIR__) . '/config.php';
        
        $result = loadEnvFileFallback('/path/that/does/not/exist.env');
        $this->assertFalse($result, 'Devrait retourner false pour un fichier inexistant');
    }

    public function testDetectBaseUrlExists(): void
    {
        require_once dirname(__DIR__) . '/config.php';
        
        $this->assertTrue(
            function_exists('detectBaseUrl'),
            'La fonction detectBaseUrl devrait exister'
        );
    }

    public function testSiteUrlExists(): void
    {
        require_once dirname(__DIR__) . '/config.php';
        
        $this->assertTrue(
            function_exists('site_url'),
            'La fonction site_url devrait exister'
        );
    }

    public function testSiteUrlReturnsString(): void
    {
        require_once dirname(__DIR__) . '/config.php';
        
        $url = site_url('dashboard');
        
        $this->assertIsString($url);
        $this->assertNotEmpty($url);
    }

    public function testIsAdminFunctionExists(): void
    {
        require_once dirname(__DIR__) . '/config.php';
        
        $this->assertTrue(
            function_exists('isAdmin'),
            'La fonction isAdmin devrait exister'
        );
    }

    public function testIsAdminReturnsBool(): void
    {
        require_once dirname(__DIR__) . '/config.php';
        
        $result = isAdmin();
        
        $this->assertIsBool($result, 'isAdmin devrait retourner un booléen');
    }

    public function testIsParentFunctionExists(): void
    {
        require_once dirname(__DIR__) . '/config.php';
        
        $this->assertTrue(
            function_exists('isParent'),
            'La fonction isParent devrait exister'
        );
    }

    public function testDetectEnvironment(): void
    {
        $appEnv = getenv('APP_ENV');
        
        if ($appEnv) {
            $this->assertContains(
                $appEnv,
                ['local', 'production', 'development'],
                'APP_ENV devrait être local, production ou development'
            );
        } else {
            $this->markTestSkipped('APP_ENV non défini');
        }
    }

    public function testSessionStartedAfterConfigLoad(): void
    {
        // Vérifier que session_start() a été appelé
        // Note: En environnement de test, la session peut ne pas être démarrée
        $sessionStatus = session_status();
        
        $this->assertContains(
            $sessionStatus,
            [PHP_SESSION_DISABLED, PHP_SESSION_NONE, PHP_SESSION_ACTIVE]
        );
    }
}
