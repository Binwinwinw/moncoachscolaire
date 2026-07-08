<?php

if (!function_exists('render_exercices_page_header')) {
    /**
     * En-tête standard des pages exercices (couleur via thème niveau).
     *
     * @param array{
     *   icon?: string,
     *   title: string,
     *   subtitle: string,
     *   nav_links?: array<int, array{href: string, label: string, icon?: string, tone?: string}>,
     *   theme?: array
     * } $config
     */
    function render_exercices_page_header(array $config): void
    {
        $theme = $config['theme'] ?? ($GLOBALS['app_theme']['variant'] ?? get_neutral_theme_variant());
        $icon = (string) ($config['icon'] ?? '');
        $title = (string) ($config['title'] ?? 'Exercices');
        $subtitle = (string) ($config['subtitle'] ?? '');
        $nav_links = is_array($config['nav_links'] ?? null) ? $config['nav_links'] : [];

        $tone_map = [
            'primary' => $theme['nav_primary'] ?? 'btn-theme-primary',
            'secondary' => $theme['menu_secondary'] ?? 'btn-theme-secondary',
            'dashboard' => $theme['nav_dashboard'] ?? 'btn-theme-primary',
        ];

        echo '<div class="text-center mb-8">';
        echo '<div class="header rounded-xl p-8 shadow-lg cover-theme">';
        echo '<h1 class="text-4xl font-bold mb-4 flex items-center justify-center gap-3 ' . htmlspecialchars($theme['title'] ?? 'text-theme', ENT_QUOTES, 'UTF-8') . '">';
        if ($icon !== '') {
            echo '<span class="text-6xl" aria-hidden="true">' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '</span>';
        }
        echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        echo '</h1>';
        if ($subtitle !== '') {
            echo '<p class="subtitle text-xl mb-6 ' . htmlspecialchars($theme['subtitle'] ?? 'text-theme-dark', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        if ($nav_links !== []) {
            echo '<div class="flex flex-wrap justify-center gap-4 mt-4">';
            foreach ($nav_links as $link) {
                $tone = (string) ($link['tone'] ?? 'primary');
                $btn = $tone_map[$tone] ?? ($theme['nav_primary'] ?? 'btn-theme-primary');
                $label = (string) ($link['label'] ?? 'Lien');
                $link_icon = (string) ($link['icon'] ?? '');
                $href = (string) ($link['href'] ?? '#');
                $extra = str_contains($btn, 'btn-theme-') ? $btn : $btn . ' text-white px-6 py-3 rounded-lg font-semibold shadow transition inline-flex items-center gap-2';
                echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($extra, ENT_QUOTES, 'UTF-8') . '">';
                if ($link_icon !== '') {
                    echo '<span aria-hidden="true">' . htmlspecialchars($link_icon, ENT_QUOTES, 'UTF-8') . '</span> ';
                }
                echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                echo '</a>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';
    }
}
