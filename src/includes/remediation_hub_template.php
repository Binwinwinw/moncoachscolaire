<?php

if (!function_exists('render_remediation_hub_template')) {
    function render_remediation_hub_template(array $config)
    {
        $theme = get_theme_variant_by_level($config['theme_level'] ?? 'college');
        $title = (string) ($config['title'] ?? 'Guide de Remediation');
        $subtitle = (string) ($config['subtitle'] ?? 'Programmes 2025-2026');
        $nav_aria = (string) ($config['nav_aria'] ?? 'Navigation rapide');
        $section_aria = (string) ($config['section_aria'] ?? 'Acces rapides');
        $cards_grid_class = (string) ($config['cards_grid_class'] ?? 'grid grid-cols-1 md:grid-cols-3 gap-4 w-full');
        $nav_links = is_array($config['nav_links'] ?? null) ? $config['nav_links'] : [];
        $cards = is_array($config['cards'] ?? null) ? $config['cards'] : [];
        $is_logged_in = !empty($_SESSION['logged_in']) || !empty($_SESSION['user_id']);
        $soft_buttons = is_array($theme['soft_buttons'] ?? null) ? $theme['soft_buttons'] : [];

        $tone_to_class = [
            'primary' => $theme['nav_primary'],
            'secondary' => $theme['nav_secondary'],
            'tertiary' => $theme['nav_tertiary'],
            'dashboard' => $theme['nav_dashboard'],
        ];

        echo '<main class="remediation-hub flex flex-col w-full max-w-6xl mx-auto px-4 pb-8">';
        echo '<header class="cover-page ' . htmlspecialchars($theme['cover'], ENT_QUOTES, 'UTF-8') . ' rounded-2xl p-8 shadow-lg mb-8 mt-6 max-w-4xl mx-auto w-full">';
        echo '<h1 class="text-4xl md:text-5xl font-extrabold ' . htmlspecialchars($theme['title'], ENT_QUOTES, 'UTF-8') . ' mb-2 text-center">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<p class="text-xl ' . htmlspecialchars($theme['subtitle'], ENT_QUOTES, 'UTF-8') . ' text-center mb-1">' . htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p class="text-lg ' . htmlspecialchars($theme['title'], ENT_QUOTES, 'UTF-8') . ' text-center">Programmes 2025-2026</p>';
        echo '</header>';

        echo '<nav class="guide-navigation flex flex-wrap justify-center gap-4 mb-8" aria-label="' . htmlspecialchars($nav_aria, ENT_QUOTES, 'UTF-8') . '">';
        foreach ($nav_links as $link) {
            $tone = (string) ($link['tone'] ?? 'primary');
            $btn_class = $tone_to_class[$tone] ?? $theme['nav_primary'];
            echo '<a href="' . htmlspecialchars((string) ($link['href'] ?? '#'), ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($btn_class, ENT_QUOTES, 'UTF-8') . ' text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="' . htmlspecialchars((string) ($link['aria'] ?? $link['label'] ?? 'Lien'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) ($link['label'] ?? 'Lien'), ENT_QUOTES, 'UTF-8') . '</a>';
        }

        if ($is_logged_in) {
            echo '<a href="' . htmlspecialchars(site_url('eleve/dashboard'), ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($theme['nav_dashboard'], ENT_QUOTES, 'UTF-8') . ' text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mon Dashboard">Mon Dashboard</a>';
        }

        echo '</nav>';

        echo '<section class="max-w-4xl mx-auto px-2 md:px-0 w-full mb-10" aria-label="' . htmlspecialchars($section_aria, ENT_QUOTES, 'UTF-8') . '">';
        echo '<div class="' . htmlspecialchars($cards_grid_class, ENT_QUOTES, 'UTF-8') . '">';

        foreach ($cards as $card) {
            $tag = !empty($card['href']) ? 'a' : 'article';
            $tone_index = isset($card['tone_index']) ? (int) $card['tone_index'] : null;
            $default_class = 'rounded-xl bg-gray-100 text-gray-800 font-semibold shadow px-6 py-4 text-center';
            if ($tone_index !== null && isset($soft_buttons[$tone_index])) {
                $default_class = 'rounded-xl font-semibold shadow transition focus:outline-none focus:ring-2 px-6 py-4 text-center ' . $soft_buttons[$tone_index];
            }
            $class = (string) ($card['class'] ?? $default_class);
            $label = (string) ($card['label'] ?? 'Element');
            $aria = (string) ($card['aria'] ?? $label);

            if ($tag === 'a') {
                echo '<a href="' . htmlspecialchars((string) $card['href'], ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" aria-label="' . htmlspecialchars($aria, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
            } else {
                echo '<article class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';
                echo '<h2 class="text-xl font-bold mb-2">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</h2>';
                echo '<p>' . htmlspecialchars((string) ($card['text'] ?? ''), ENT_QUOTES, 'UTF-8') . '</p>';
                echo '</article>';
            }
        }

        echo '</div>';
        echo '</section>';
        echo '</main>';
    }
}
