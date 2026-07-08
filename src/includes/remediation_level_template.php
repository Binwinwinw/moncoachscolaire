<?php

if (!function_exists('render_remediation_level_template')) {
    /**
     * @param array{
     *   theme_level: string,
     *   title: string,
     *   subtitle: string,
     *   nav_links: array<int, array{href: string, label: string, aria?: string, tone?: string}>,
     *   subjects: array<int, array{id: string, label: string, icon?: string, tone_index?: int, open_fn?: string}>,
     *   modals_include?: string,
     *   action_include?: string,
     *   nav_aria?: string,
     *   section_aria?: string,
     *   theme?: array,
     *   has_access?: bool,
     *   user_name?: string
     * } $config
     */
    function render_remediation_level_template(array $config): void
    {
        $theme = $config['theme'] ?? get_theme_variant_by_level($config['theme_level'] ?? 'college');
        $title = (string) ($config['title'] ?? 'Guide de remédiation');
        $subtitle = (string) ($config['subtitle'] ?? 'Programmes 2025-2026');
        $nav_aria = (string) ($config['nav_aria'] ?? 'Navigation rapide');
        $section_aria = (string) ($config['section_aria'] ?? 'Matières et remédiation');
        $nav_links = is_array($config['nav_links'] ?? null) ? $config['nav_links'] : [];
        $subjects = is_array($config['subjects'] ?? null) ? $config['subjects'] : [];
        $soft_buttons = is_array($theme['soft_buttons'] ?? null) ? $theme['soft_buttons'] : [];
        $is_logged_in = !empty($_SESSION['logged_in']) || !empty($_SESSION['user_id']);
        $is_admin = function_exists('isAdmin') && isAdmin();
        $has_access = array_key_exists('has_access', $config)
            ? (bool) $config['has_access']
            : ($is_logged_in || $is_admin);
        $user_name = (string) ($config['user_name'] ?? $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Élève');

        $tone_to_class = [
            'primary' => $theme['nav_primary'],
            'secondary' => $theme['nav_secondary'],
            'tertiary' => $theme['nav_tertiary'],
            'dashboard' => $theme['nav_dashboard'],
        ];

        echo '<main class="remediation-level flex flex-col w-full">';
        echo '<header class="cover-page ' . htmlspecialchars($theme['cover'], ENT_QUOTES, 'UTF-8') . ' rounded-2xl p-8 shadow-lg mb-8 mt-6 max-w-4xl mx-auto w-full">';
        echo '<h1 class="text-3xl md:text-4xl font-extrabold ' . htmlspecialchars($theme['title'], ENT_QUOTES, 'UTF-8') . ' mb-2 text-center">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<p class="text-lg md:text-xl ' . htmlspecialchars($theme['subtitle'], ENT_QUOTES, 'UTF-8') . ' text-center mb-1">' . htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p class="text-base ' . htmlspecialchars($theme['title'], ENT_QUOTES, 'UTF-8') . ' text-center">Programmes 2025-2026</p>';
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

        echo '<section class="max-w-4xl mx-auto px-2 md:px-0 w-full mb-6 text-center" aria-label="' . htmlspecialchars($section_aria, ENT_QUOTES, 'UTF-8') . '">';
        echo '<div class="remediation-subjects-panel">';
        echo '<div class="remediation-subjects-grid">';

        foreach ($subjects as $subject) {
            $id = (string) ($subject['id'] ?? '');
            $label = (string) ($subject['label'] ?? '');
            $icon = (string) ($subject['icon'] ?? '');
            $open_fn = (string) ($subject['open_fn'] ?? 'openMatiereModal');
            $tone_index = isset($subject['tone_index']) ? (int) $subject['tone_index'] : 0;
            $btn_class = 'px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2';
            if (isset($soft_buttons[$tone_index])) {
                $btn_class .= ' ' . $soft_buttons[$tone_index];
            }
            echo '<button type="button" onclick="' . htmlspecialchars($open_fn, ENT_QUOTES) . '(\'' . htmlspecialchars($id, ENT_QUOTES) . '\')" class="' . htmlspecialchars($btn_class, ENT_QUOTES, 'UTF-8') . '">';
            if ($icon !== '') {
                echo '<span class="text-2xl" aria-hidden="true">' . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '</span> ';
            }
            echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            echo '</button>';
        }

        echo '</div>';
        echo '</div>';

        if (!empty($config['modals_include']) && is_file($config['modals_include'])) {
            $subject_tones = $soft_buttons;
            $modal_close_hover = $theme['modal_close_hover'] ?? '';
            $modal_title = $theme['modal_title'] ?? '';
            $modal_heading = $theme['modal_heading'] ?? '';
            $modal_advice = $theme['modal_advice'] ?? '';
            echo '<div class="remediation-modals-root">';
            include $config['modals_include'];
            echo '</div>';
        }

        if (!empty($config['action_include']) && is_file($config['action_include'])) {
            include $config['action_include'];
        }

        echo '</section>';

        if (function_exists('asset_url')) {
            $jsPath = asset_url('assets/js/remediation-modals.js');
        } else {
            $jsPath = '/assets/js/remediation-modals.js';
        }
        echo '<script src="' . htmlspecialchars($jsPath, ENT_QUOTES) . '" defer></script>';
        echo '</main>';
    }
}
