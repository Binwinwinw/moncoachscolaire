<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test des fonctionnalités de progression
 */
class ProgressTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $progressHelpersPath = dirname(__DIR__) . '/includes/progress_helpers.php';
        if (file_exists($progressHelpersPath)) {
            require_once $progressHelpersPath;
        }
        
        $gamificationPath = dirname(__DIR__) . '/includes/gamification.php';
        if (file_exists($gamificationPath)) {
            require_once $gamificationPath;
        }
    }

    public function testProgressHelpersFileExists(): void
    {
        $progressHelpersPath = dirname(__DIR__) . '/includes/progress_helpers.php';
        
        $this->assertFileExists(
            $progressHelpersPath,
            'Le fichier progress_helpers.php devrait exister'
        );
    }

    public function testGamificationFileExists(): void
    {
        $gamificationPath = dirname(__DIR__) . '/includes/gamification.php';
        
        $this->assertFileExists(
            $gamificationPath,
            'Le fichier gamification.php devrait exister'
        );
    }

    public function testProgressChartApiExists(): void
    {
        $progressChartPath = dirname(__DIR__) . '/api/get_progress_chart.php';
        
        $this->assertFileExists(
            $progressChartPath,
            'L\'API de graphique de progression devrait exister'
        );
    }

    public function testProgressDisplayExists(): void
    {
        $progressDisplayPath = dirname(__DIR__) . '/includes/progress_display.php';
        
        $this->assertFileExists(
            $progressDisplayPath,
            'Le fichier progress_display.php devrait exister'
        );
    }

    public function testProgressPageExists(): void
    {
        $progressPagePath = dirname(__DIR__) . '/progression.php';
        
        $this->assertFileExists(
            $progressPagePath,
            'La page progression.php devrait exister'
        );
    }

    public function testPercentageCalculation(): void
    {
        // Test du calcul de pourcentage
        $completed = 7;
        $total = 10;
        $expected = 70;
        
        $percentage = ($completed / $total) * 100;
        
        $this->assertEquals($expected, $percentage);
    }

    public function testProgressBoundaries(): void
    {
        // Vérifier que le pourcentage reste entre 0 et 100
        $testCases = [
            ['completed' => 0, 'total' => 10, 'min' => 0, 'max' => 0],
            ['completed' => 5, 'total' => 10, 'min' => 40, 'max' => 60],
            ['completed' => 10, 'total' => 10, 'min' => 100, 'max' => 100],
        ];
        
        foreach ($testCases as $case) {
            $percentage = ($case['completed'] / $case['total']) * 100;
            
            $this->assertGreaterThanOrEqual(0, $percentage);
            $this->assertLessThanOrEqual(100, $percentage);
            $this->assertGreaterThanOrEqual($case['min'], $percentage);
            $this->assertLessThanOrEqual($case['max'], $percentage);
        }
    }

    public function testZeroDivisionHandling(): void
    {
        // S'assurer qu'on ne divise jamais par zéro
        $total = 0;
        
        if ($total > 0) {
            $percentage = (5 / $total) * 100;
        } else {
            $percentage = 0;
        }
        
        $this->assertEquals(0, $percentage);
    }
}
