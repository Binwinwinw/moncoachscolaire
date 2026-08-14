<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AiPrivateDraftContractTest extends TestCase
{
    public function testRevisionLookupRequiresBothRevisionAndOwnerIdentifiers(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/includes/ai_course_generator.php');

        self::assertIsString($source);
        self::assertStringContainsString(
            'WHERE Id = :revision_id AND UserId = :user_id LIMIT 1',
            $source
        );
        self::assertStringContainsString("'revision_id' => \$revisionId", $source);
        self::assertStringContainsString("'user_id' => \$userId", $source);
    }

    public function testCourseSaveCreatesOnlyAPrivateRevision(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/api/ia/save_generated_cours.php');

        self::assertIsString($source);
        self::assertStringContainsString('saveAiRevision(', $source);
        self::assertStringContainsString("'revision_url' => \$revisionUrl", $source);
        self::assertStringNotContainsString('saveCourseToDatabase(', $source);
        self::assertStringNotContainsString("'course_url'", $source);
    }

    public function testQuizSaveDoesNotPublishExercisesOrRuntimeFiles(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/src/api/ia/save_generated_quiz.php');

        self::assertIsString($source);
        self::assertStringContainsString('saveAiRevision(', $source);
        self::assertStringContainsString("'revision_url' => \$revisionUrl", $source);
        self::assertStringContainsString('ne correspond à aucun choix', $source);
        self::assertStringNotContainsString('INSERT INTO `exercises`', $source);
        self::assertStringNotContainsString('file_put_contents(', $source);
        self::assertStringNotContainsString('findNextDiagnosticQuizId(', $source);
    }

    public function testMigrationAddsDraftColumnsWithoutChangingPrimaryKeys(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/db/migration_add_ai_revision_drafts_20260725.sql');

        self::assertIsString($source);
        self::assertStringContainsString('ADD COLUMN IF NOT EXISTS `Content` LONGTEXT', $source);
        self::assertStringContainsString('ADD COLUMN IF NOT EXISTS `Status` VARCHAR(20)', $source);
        self::assertStringContainsString('ADD COLUMN IF NOT EXISTS `UpdatedAt` DATETIME', $source);
        self::assertStringNotContainsString('MODIFY COLUMN `Id`', $source);
    }
}
