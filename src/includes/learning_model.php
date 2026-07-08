<?php

class LearningModel
{
    public static function normalizeSchoolLevel(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $map = [
            '6e' => '6e',
            '6eme' => '6e',
            '5e' => '5e',
            '5eme' => '5e',
            '4e' => '4e',
            '4eme' => '4e',
            '3e' => '3e',
            '3eme' => '3e',
            'seconde' => 'seconde',
            'premiere' => 'premiere',
            'terminale' => 'terminale',
            'terminale' => 'terminale',
        ];

        return $map[$normalized] ?? strtolower($normalized);
    }

    public static function normalizeCycle(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['college', 'collège', 'colleg e', 'college'], true)) {
            return 'college';
        }

        if (in_array($normalized, ['lycee', 'lycée', 'lycée'], true)) {
            return 'lycee';
        }

        return null;
    }

    public static function buildRecommendationFilters(array $profile, array $context = []): array
    {
        $filters = [];
        $filters['school_level'] = self::normalizeSchoolLevel($profile['school_level'] ?? null) ?? 'all';
        $filters['cycle'] = self::normalizeCycle($profile['cycle'] ?? null) ?? 'all';
        $filters['objective'] = isset($profile['objective']) ? strtolower(trim((string) $profile['objective'])) : 'all';
        $filters['subject'] = isset($context['subject']) ? strtolower(trim((string) $context['subject'])) : 'all';

        return $filters;
    }
}
