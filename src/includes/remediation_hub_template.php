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

        echo '<main class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.16),_transparent_40%),linear-gradient(135deg,_#fffdf8_0%,_#f7f9ff_100%)] px-4 py-8 sm:px-6 lg:px-8">';
        echo '<div class="mx-auto flex w-full max-w-6xl flex-col">';
        echo '<header class="mx-auto mb-8 w-full max-w-4xl rounded-[2rem] border border-slate-200/70 bg-white/85 p-8 text-center shadow-[0_25px_70px_-28px_rgba(15,23,42,0.25)] backdrop-blur">';
        echo '<p class="mb-3 inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Accompagnement scolaire</p>';
        echo '<h1 class="mb-2 text-4xl font-extrabold tracking-tight md:text-5xl ' . htmlspecialchars($theme['title'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<p class="mb-1 text-lg font-medium ' . htmlspecialchars($theme['subtitle'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p class="text-base text-slate-600">Programmes 2025-2026 · suivi simple, clair et progressif</p>';
        echo '</header>';

        echo '<nav class="mb-8 flex flex-wrap justify-center gap-3" aria-label="' . htmlspecialchars($nav_aria, ENT_QUOTES, 'UTF-8') . '">';
        foreach ($nav_links as $link) {
            $tone = (string) ($link['tone'] ?? 'primary');
            $btn_class = $tone_to_class[$tone] ?? $theme['nav_primary'];
            echo '<a href="' . htmlspecialchars((string) ($link['href'] ?? '#'), ENT_QUOTES, 'UTF-8') . '" class="inline-flex items-center rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 ' . htmlspecialchars($btn_class, ENT_QUOTES, 'UTF-8') . '" aria-label="' . htmlspecialchars((string) ($link['aria'] ?? $link['label'] ?? 'Lien'), ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) ($link['label'] ?? 'Lien'), ENT_QUOTES, 'UTF-8') . '</a>';
        }

        if ($is_logged_in) {
            echo '<a href="' . htmlspecialchars(site_url('eleve/dashboard'), ENT_QUOTES, 'UTF-8') . '" class="inline-flex items-center rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 ' . htmlspecialchars($theme['nav_dashboard'], ENT_QUOTES, 'UTF-8') . '" aria-label="Mon Dashboard">Mon Dashboard</a>';
        }

        echo '</nav>';

        echo '<section class="mx-auto mb-10 w-full max-w-4xl rounded-[1.75rem] border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur sm:p-6" aria-label="' . htmlspecialchars($section_aria, ENT_QUOTES, 'UTF-8') . '">';
        echo '<div class="' . htmlspecialchars($cards_grid_class, ENT_QUOTES, 'UTF-8') . '">';

        foreach ($cards as $card) {
            $tag = !empty($card['href']) ? 'a' : 'article';
            $tone_index = isset($card['tone_index']) ? (int) $card['tone_index'] : null;
            $default_class = 'group flex min-h-[88px] items-center justify-center rounded-2xl border border-slate-200 bg-white/90 px-5 py-4 text-center font-semibold text-slate-700 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-400';
            if ($tone_index !== null && isset($soft_buttons[$tone_index])) {
                $default_class = 'group flex min-h-[88px] items-center justify-center rounded-2xl border border-slate-200 px-5 py-4 text-center font-semibold shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-md focus:outline-none focus:ring-2 ' . $soft_buttons[$tone_index];
            }
            $class = (string) ($card['class'] ?? $default_class);
            $label = (string) ($card['label'] ?? 'Element');
            $aria = (string) ($card['aria'] ?? $label);

            if ($tag === 'a') {
                echo '<a href="' . htmlspecialchars((string) $card['href'], ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" aria-label="' . htmlspecialchars($aria, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
            } else {
                echo '<article class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';
                echo '<h2 class="text-lg font-bold">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</h2>';
                echo '<p class="mt-2 text-sm text-slate-600">' . htmlspecialchars((string) ($card['text'] ?? ''), ENT_QUOTES, 'UTF-8') . '</p>';
                echo '</article>';
            }
        }

        echo '</div>';
        echo '</section>';
        echo '</div>';
        echo '</main>';
    }
}
