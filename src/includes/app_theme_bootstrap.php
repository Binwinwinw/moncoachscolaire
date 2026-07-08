<?php

if (!function_exists('bootstrap_app_theme')) {
    /**
     * Résout et expose le thème applicatif (une fois par requête routeur).
     *
     * Priorité : $page_theme_level explicite > inférence URL > session élève > neutre.
     *
     * @return array{tier: string, level_key: string, variant: array<string, mixed>, page_theme_level: ?string}
     */
    function bootstrap_app_theme(?string $pageRaw = null, ?string $pageThemeLevel = null): array
    {
        if (is_file(dirname(__DIR__) . '/includes/level_normalization.php')) {
            require_once dirname(__DIR__) . '/includes/level_normalization.php';
        }

        if (empty($pageThemeLevel) && $pageRaw !== null && $pageRaw !== '' && function_exists('infer_page_theme_level')) {
            $pageThemeLevel = infer_page_theme_level($pageRaw);
        }

        // Page cours : ?niveau=6eme|4eme|seconde… (visiteurs inclus)
        if (empty($pageThemeLevel) && $pageRaw === 'cours' && !empty($_GET['niveau'])) {
            $pageThemeLevel = function_exists('normalize_level_for_url')
                ? normalize_level_for_url((string) $_GET['niveau'])
                : strtolower(trim((string) $_GET['niveau']));
        }

        // Dashboard élève : couleur depuis la session (pas le slug URL)
        if ($pageRaw === 'eleve/dashboard') {
            $pageThemeLevel = null;
        }

        $GLOBALS['page_theme_level'] = $pageThemeLevel;

        $app_theme = function_exists('resolve_app_theme')
            ? resolve_app_theme($pageThemeLevel)
            : [
                'tier' => 'neutral',
                'level_key' => 'neutral',
                'variant' => function_exists('get_neutral_theme_variant') ? get_neutral_theme_variant() : [],
            ];

        $GLOBALS['app_theme'] = $app_theme;

        return array_merge($app_theme, ['page_theme_level' => $pageThemeLevel]);
    }
}
