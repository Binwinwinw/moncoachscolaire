<?php
$page_title = 'Guide de remédiation Seconde - MonCoachScolaire';
$page_class = 'remediation-hub-page';
$page_css = 'remediation-guide.css';

$srcRoot = dirname(__DIR__, 4);
require_once $srcRoot . '/includes/remediation_guide_bootstrap.php';
require_once $srcRoot . '/includes/remediation_level_template.php';

$boot = remediation_guide_bootstrap($srcRoot, '2nde');
$has_access = $boot['has_access'];
$user_name = $boot['user_name'];
$theme = $boot['theme'];

render_remediation_level_template([
    'theme_level' => '2nde',
    'title' => 'Guide de remédiation Seconde',
    'subtitle' => 'Programmes 2025 – Accompagnement personnalisé pour réussir ta Seconde',
    'nav_aria' => 'Navigation rapide Seconde',
    'section_aria' => 'Matières et remédiation Seconde',
    'theme' => $theme,
    'nav_links' => [
        ['href' => site_url('lycee/seconde/exercices-seconde'), 'label' => 'Mes Exercices', 'aria' => 'Mes exercices Seconde', 'tone' => 'primary'],
        ['href' => site_url('cours', ['niveau' => '2nde']), 'label' => 'Mes Cours', 'aria' => 'Mes cours', 'tone' => 'primary'],
        ['href' => site_url('lycee/lycee-accueil'), 'label' => 'Accueil Lycée', 'aria' => 'Accueil lycee', 'tone' => 'secondary'],
    ],
    'subjects' => [
        ['id' => 'francais', 'label' => 'Français', 'icon' => '📖', 'tone_index' => 0, 'open_fn' => 'openMatiereModal'],
        ['id' => 'maths', 'label' => 'Maths', 'icon' => '🧮', 'tone_index' => 1, 'open_fn' => 'openMatiereModal'],
        ['id' => 'histoire', 'label' => 'Histoire-Géo', 'icon' => '🌍', 'tone_index' => 2, 'open_fn' => 'openMatiereModal'],
        ['id' => 'svt', 'label' => 'SVT', 'icon' => '🌱', 'tone_index' => 3, 'open_fn' => 'openMatiereModal'],
        ['id' => 'physique', 'label' => 'Physique-Chimie', 'icon' => '⚗️', 'tone_index' => 1, 'open_fn' => 'openMatiereModal'],
        ['id' => 'langues', 'label' => 'Langues', 'icon' => '🌐', 'tone_index' => 2, 'open_fn' => 'openMatiereModal'],
        ['id' => 'methodo', 'label' => 'Méthodologie', 'icon' => '📝', 'tone_index' => 3, 'open_fn' => 'openMatiereModal'],
        ['id' => 'orientation', 'label' => 'Orientation', 'icon' => '🎯', 'tone_index' => 0, 'open_fn' => 'openMatiereModal'],
    ],
    'modals_include' => __DIR__ . '/guide-remediation.modals.inc.php',
    'action_include' => __DIR__ . '/guide-remediation.action.inc.php',
]);
