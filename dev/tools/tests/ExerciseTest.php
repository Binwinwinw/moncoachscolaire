<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test des fonctionnalités d'exercices
 */
class ExerciseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $exerciseLoaderPath = dirname(__DIR__) . '/includes/exercice_loader.php';
        if (file_exists($exerciseLoaderPath)) {
            require_once $exerciseLoaderPath;
        }
    }

    public function testExerciseFilesExist(): void
    {
        $exercicesDir = dirname(__DIR__) . '/exercices';
        
        $this->assertDirectoryExists($exercicesDir, 'Le dossier exercices devrait exister');
        $this->assertDirectoryIsReadable($exercicesDir);
    }

    public function testExerciseJsonStructure(): void
    {
        $exercicesDir = dirname(__DIR__) . '/exercices';
        
        if (!is_dir($exercicesDir)) {
            $this->markTestSkipped('Dossier exercices non trouvé');
            return;
        }

        $jsonFiles = glob($exercicesDir . '/*.json');
        
        if (empty($jsonFiles)) {
            $this->markTestSkipped('Aucun fichier JSON d\'exercice trouvé');
            return;
        }

        foreach ($jsonFiles as $jsonFile) {
            $content = file_get_contents($jsonFile);
            $data = json_decode($content, true);
            
            $this->assertNotNull($data, "Le fichier {$jsonFile} devrait contenir du JSON valide");
            
            if (is_array($data)) {
                $this->assertNotEmpty($data, "Le fichier {$jsonFile} ne devrait pas être vide");
            }
        }
    }

    public function testExerciseInteractiveGeneratorExists(): void
    {
        $generatorPath = dirname(__DIR__) . '/includes/exercise_interactive_generator.php';
        
        $this->assertFileExists(
            $generatorPath,
            'Le fichier exercise_interactive_generator.php devrait exister'
        );
    }

    public function testExerciseCardExists(): void
    {
        $cardPath = dirname(__DIR__) . '/includes/exercice_card.php';
        
        $this->assertFileExists($cardPath, 'Le fichier exercice_card.php devrait exister');
    }

    public function testQuizGeneratorExists(): void
    {
        $quizPath = dirname(__DIR__) . '/includes/quiz_generator.php';
        
        $this->assertFileExists($quizPath, 'Le fichier quiz_generator.php devrait exister');
    }

    public function testProgressSaveApiExists(): void
    {
        $progressApiPath = dirname(__DIR__) . '/api/save_progress.php';
        
        $this->assertFileExists(
            $progressApiPath,
            'L\'API de sauvegarde de progression devrait exister'
        );
    }

    public function testExerciseProgressApiExists(): void
    {
        $exerciseProgressPath = dirname(__DIR__) . '/api/save_exercise_progress.php';
        
        $this->assertFileExists(
            $exerciseProgressPath,
            'L\'API de sauvegarde de progression d\'exercice devrait exister'
        );
    }

    public function testGetExercisesApiExists(): void
    {
        $getExercisesPath = dirname(__DIR__) . '/api/get_exercises.php';
        
        $this->assertFileExists(
            $getExercisesPath,
            'L\'API de récupération d\'exercices devrait exister'
        );
    }
}
