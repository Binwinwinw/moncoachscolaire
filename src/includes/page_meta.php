<?php

if (!function_exists('load_page_meta')) {
    /**
     * Extrait les métadonnées de page ($page_css, $page_class, $page_title, $page_theme_level)
     * depuis les premières lignes d'un fichier PHP, sans l'exécuter.
     *
     * @return array{page_css?: string, page_class?: string, page_title?: string, page_theme_level?: string}
     */
    function load_page_meta(string $filePath, int $maxBytes = 8192): array
    {
        if (!is_file($filePath)) {
            return [];
        }

        $snippet = @file_get_contents($filePath, false, null, 0, $maxBytes) ?: '';
        if ($snippet === '') {
            return [];
        }

        $meta = [];
        $keys = ['page_css', 'page_class', 'page_title', 'page_theme_level'];

        foreach ($keys as $key) {
            $pattern = '/\$' . preg_quote($key, '/') . '\s*=\s*(["\'])((?:\\\\.|(?!\\1).)*)\1/s';
            if (preg_match($pattern, $snippet, $m)) {
                $meta[$key] = stripcslashes($m[2]);
            }
        }

        return $meta;
    }

    /**
     * Applique les métadonnées extraites aux variables du routeur (sans écraser si déjà définies).
     */
    function apply_page_meta(array $meta): void
    {
        if (!empty($meta['page_css']) && empty($GLOBALS['page_css'])) {
            $GLOBALS['page_css'] = $meta['page_css'];
        }
        if (!empty($meta['page_class']) && empty($GLOBALS['page_class'])) {
            $GLOBALS['page_class'] = $meta['page_class'];
        }
        if (!empty($meta['page_title']) && empty($GLOBALS['page_title'])) {
            $GLOBALS['page_title'] = $meta['page_title'];
        }
        if (!empty($meta['page_theme_level']) && empty($GLOBALS['page_theme_level'])) {
            $GLOBALS['page_theme_level'] = $meta['page_theme_level'];
        }
    }
}
