<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/includes/learning_model.php';

final class LearningModelTest extends TestCase
{
    public function testNormalizeSchoolLevelHandlesCanonicalValues(): void
    {
        $this->assertSame('6e', LearningModel::normalizeSchoolLevel('6e'));
        $this->assertSame('5e', LearningModel::normalizeSchoolLevel('5eme'));
        $this->assertSame('4e', LearningModel::normalizeSchoolLevel('4eme'));
        $this->assertSame('3e', LearningModel::normalizeSchoolLevel('3e'));
        $this->assertSame('seconde', LearningModel::normalizeSchoolLevel('seconde'));
        $this->assertSame('premiere', LearningModel::normalizeSchoolLevel('premiere'));
        $this->assertSame('terminale', LearningModel::normalizeSchoolLevel('terminale'));
        $this->assertSame('bac', LearningModel::normalizeSchoolLevel('bac'));
    }

    public function testNormalizeCycleUsesExpectedValues(): void
    {
        $this->assertSame('college', LearningModel::normalizeCycle('Collège'));
        $this->assertSame('lycee', LearningModel::normalizeCycle('lycée'));
        $this->assertNull(LearningModel::normalizeCycle('bac'));
    }

    public function testBuildRecommendationFiltersUseStableValues(): void
    {
        $filters = LearningModel::buildRecommendationFilters(
            ['school_level' => 'seconde', 'cycle' => 'lycee', 'objective' => 'exam'],
            ['subject' => 'histoire-geographie']
        );

        $this->assertSame('seconde', $filters['school_level']);
        $this->assertSame('lycee', $filters['cycle']);
        $this->assertSame('exam', $filters['objective']);
        $this->assertSame('histoire-geographie', $filters['subject']);
    }
}
