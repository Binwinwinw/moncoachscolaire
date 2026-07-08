<?php
$page_title = 'Guide de remédiation Première - MonCoachScolaire';
$page_class = 'remediation-hub-page';
$page_css = 'remediation-guide.css';

$srcRoot = dirname(__DIR__, 4);
require_once $srcRoot . '/includes/remediation_guide_bootstrap.php';
require_once $srcRoot . '/includes/remediation_level_template.php';

$boot = remediation_guide_bootstrap($srcRoot, 'premiere');
$has_access = $boot['has_access'];
$user_name = $boot['user_name'];
$theme = $boot['theme'];

render_remediation_level_template([
    'theme_level' => 'premiere',
    'title' => 'Guide de remédiation Première',
    'subtitle' => 'Programmes 2025 – Accompagnement personnalisé pour réussir ta Première',
    'nav_aria' => 'Navigation rapide Première',
    'section_aria' => 'Matières et remédiation Première',
    'theme' => $theme,
    'nav_links' => [
        ['href' => site_url('lycee/première/exercices-première'), 'label' => 'Mes Exercices', 'aria' => 'Mes exercices Première', 'tone' => 'primary'],
        ['href' => site_url('cours', ['niveau' => '1ere']), 'label' => 'Mes Cours', 'aria' => 'Mes cours', 'tone' => 'primary'],
        ['href' => site_url('lycee/lycee-accueil'), 'label' => 'Accueil Lycée', 'aria' => 'Accueil lycee', 'tone' => 'secondary'],
    ],
    'subjects' => [
        ['id' => 'francais', 'label' => 'Français', 'icon' => '📖', 'tone_index' => 0, 'open_fn' => 'openMatiereModal'],
        ['id' => 'sciences', 'label' => 'Sciences', 'icon' => '🔬', 'tone_index' => 1, 'open_fn' => 'openMatiereModal'],
        ['id' => 'histoire', 'label' => 'Histoire-Géo', 'icon' => '🌍', 'tone_index' => 2, 'open_fn' => 'openMatiereModal'],
        ['id' => 'methodo', 'label' => 'Méthodologie', 'icon' => '📝', 'tone_index' => 3, 'open_fn' => 'openMatiereModal'],
        ['id' => 'orientation', 'label' => 'Orientation', 'icon' => '🎯', 'tone_index' => 0, 'open_fn' => 'openMatiereModal'],
        ['id' => 'oral', 'label' => 'Oral', 'icon' => '🎤', 'tone_index' => 1, 'open_fn' => 'openMatiereModal'],
    ],
    'modals_include' => __DIR__ . '/guide-remediation.modals.inc.php',
    'action_include' => __DIR__ . '/guide-remediation.action.inc.php',
]);
