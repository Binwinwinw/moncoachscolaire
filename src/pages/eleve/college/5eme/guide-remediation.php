<?php
$page_title = 'Guide de remédiation 5ème - MonCoachScolaire';
$page_class = 'remediation-hub-page';
$page_css = 'remediation-guide.css';

$srcRoot = dirname(__DIR__, 4);
require_once $srcRoot . '/includes/remediation_guide_bootstrap.php';
require_once $srcRoot . '/includes/remediation_level_template.php';

$boot = remediation_guide_bootstrap($srcRoot, '5eme');
$has_access = $boot['has_access'];
$user_name = $boot['user_name'];
$theme = $boot['theme'];

render_remediation_level_template([
    'theme_level' => '5eme',
    'title' => '📚 Guide de remédiation 5ème',
    'subtitle' => 'Programmes 2025 – Accompagnement personnalisé pour réussir ta 5ème',
    'nav_aria' => 'Navigation rapide 5ème',
    'section_aria' => 'Matières et remédiation 5ème',
    'theme' => $theme,
    'nav_links' => [
        ['href' => site_url('college/5eme/exercices-5eme'), 'label' => '📝 Mes Exercices', 'aria' => 'Mes exercices 5eme', 'tone' => 'primary'],
        ['href' => site_url('cours', ['niveau' => '5eme']), 'label' => '📚 Mes Cours', 'aria' => 'Mes cours', 'tone' => 'primary'],
        ['href' => site_url('college/college-accueil'), 'label' => '🏠 Accueil Collège', 'aria' => 'Accueil college', 'tone' => 'secondary'],
    ],
    'subjects' => [
        ['id' => 'francais', 'label' => 'Français', 'icon' => '📖', 'tone_index' => 0, 'open_fn' => 'openMatiereModal'],
        ['id' => 'maths', 'label' => 'Maths', 'icon' => '🧮', 'tone_index' => 1, 'open_fn' => 'openMatiereModal'],
        ['id' => 'histoire', 'label' => 'Histoire-Géo', 'icon' => '🌍', 'tone_index' => 2, 'open_fn' => 'openMatiereModal'],
        ['id' => 'svt', 'label' => 'SVT', 'icon' => '🌱', 'tone_index' => 3, 'open_fn' => 'openMatiereModal'],
        ['id' => 'physique', 'label' => 'Physique-Chimie', 'icon' => '⚗️', 'tone_index' => 1, 'open_fn' => 'openMatiereModal'],
        ['id' => 'technologie', 'label' => 'Technologie', 'icon' => '🔧', 'tone_index' => 3, 'open_fn' => 'openMatiereModal'],
        ['id' => 'anglais', 'label' => 'Anglais', 'icon' => '🌐', 'tone_index' => 2, 'open_fn' => 'openMatiereModal'],
        ['id' => 'arts', 'label' => 'Arts', 'icon' => '🎨', 'tone_index' => 0, 'open_fn' => 'openMatiereModal'],
    ],
    'modals_include' => __DIR__ . '/guide-remediation.modals.inc.php',
    'action_include' => __DIR__ . '/guide-remediation.action.inc.php',
]);
