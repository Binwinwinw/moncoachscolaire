<?php
$page_title = 'Guide de remédiation 6ème - MonCoachScolaire';
$page_class = 'remediation-hub-page';
$page_css = 'remediation-guide.css';

$srcRoot = dirname(__DIR__, 4);
require_once $srcRoot . '/includes/remediation_guide_bootstrap.php';
require_once $srcRoot . '/includes/remediation_level_template.php';

$boot = remediation_guide_bootstrap($srcRoot, '6eme');
$has_access = $boot['has_access'];
$user_name = $boot['user_name'];
$theme = $boot['theme'];

render_remediation_level_template([
    'theme_level' => '6eme',
    'title' => '📚 Guide de remédiation 6ème',
    'subtitle' => 'Programmes 2025 – Accompagnement personnalisé pour réussir ta 6ème',
    'nav_aria' => 'Navigation rapide 6ème',
    'section_aria' => 'Matières et remédiation 6ème',
    'theme' => $theme,
    'nav_links' => [
        ['href' => site_url('college/6eme/exercices-6eme'), 'label' => '📝 Mes Exercices', 'aria' => 'Mes exercices 6eme', 'tone' => 'primary'],
        ['href' => site_url('cours', ['niveau' => '6eme']), 'label' => '📚 Mes Cours', 'aria' => 'Mes cours', 'tone' => 'primary'],
        ['href' => site_url('college/college-accueil'), 'label' => '🏠 Accueil Collège', 'aria' => 'Accueil college', 'tone' => 'secondary'],
    ],
    'subjects' => [
        ['id' => 'francais', 'label' => 'Français', 'icon' => '📖', 'tone_index' => 0, 'open_fn' => 'openMatiereModal'],
        ['id' => 'maths', 'label' => 'Maths', 'icon' => '🧮', 'tone_index' => 1, 'open_fn' => 'openMatiereModal'],
        ['id' => 'histoire', 'label' => 'Histoire-Géo', 'icon' => '🌍', 'tone_index' => 2, 'open_fn' => 'openMatiereModal'],
        ['id' => 'sciences', 'label' => 'Sciences', 'icon' => '🔬', 'tone_index' => 3, 'open_fn' => 'openMatiereModal'],
        ['id' => 'anglais', 'label' => 'Anglais', 'icon' => '🇬🇧', 'tone_index' => 1, 'open_fn' => 'openMatiereModal'],
        ['id' => 'espagnol', 'label' => 'Espagnol', 'icon' => '🇪🇸', 'tone_index' => 2, 'open_fn' => 'openMatiereModal'],
        ['id' => 'technologie', 'label' => 'Technologie', 'icon' => '🧑‍💻', 'tone_index' => 3, 'open_fn' => 'openMatiereModal'],
        ['id' => 'arts', 'label' => 'Arts', 'icon' => '🎨', 'tone_index' => 0, 'open_fn' => 'openMatiereModal'],
    ],
    'modals_include' => __DIR__ . '/guide-remediation.modals.inc.php',
    'action_include' => __DIR__ . '/guide-remediation.action.inc.php',
    'has_access' => $has_access,
    'user_name' => $user_name,
]);
