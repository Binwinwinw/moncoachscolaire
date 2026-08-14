<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/src/includes/level_access.php';

final class LevelAccessSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testUnknownLevelIsDeniedForAdministrator(): void
    {
        $_SESSION = [
            'user_id' => 1,
            'logged_in' => true,
            'user_role' => 'admin',
        ];

        self::assertFalse(can_current_user_access_level('niveau-inconnu'));
    }

    public function testUnknownLevelIsDeniedForDemoUser(): void
    {
        $_SESSION = [
            'user_id' => 0,
            'is_demo' => true,
        ];

        self::assertFalse(can_current_user_access_level('niveau-inconnu'));
    }

    public function testUnknownLevelIsDeniedForVisitor(): void
    {
        self::assertFalse(can_current_user_access_level('niveau-inconnu'));
    }

    public function testCoursesDetailGeneralListRequiresLevelAuthorization(): void
    {
        $source = file_get_contents(
            dirname(__DIR__) . '/src/api/cours/courses_detail.php'
        );

        self::assertIsString($source);
        $generalBranchPosition = strpos($source, '// Tous les cours');
        self::assertIsInt($generalBranchPosition);

        $generalBranch = substr($source, $generalBranchPosition);
        self::assertIsString($generalBranch);
        self::assertStringContainsString('SELECT * FROM courses', $generalBranch);

        $generalQueryPosition = strpos($generalBranch, 'SELECT * FROM courses');
        $authorizationPosition = strpos($generalBranch, "json_error('Accès refusé");

        self::assertIsInt($generalQueryPosition);
        self::assertIsInt($authorizationPosition);
        self::assertLessThan(
            $generalQueryPosition,
            $authorizationPosition,
            'La liste générale doit refuser l’accès avant la requête SQL.'
        );
    }

    public function testCoursesDetailFailsClosedWhenLevelHelperIsUnavailable(): void
    {
        $source = file_get_contents(
            dirname(__DIR__) . '/src/api/cours/courses_detail.php'
        );

        self::assertIsString($source);
        self::assertStringContainsString('ERR_AUTH_MISSING', $source);
    }
}
