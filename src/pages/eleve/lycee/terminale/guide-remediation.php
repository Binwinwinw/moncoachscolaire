<?php
$page_title = 'Guide de remédiation Terminale - MonCoachScolaire';
$page_class = 'remediation-hub-page';
$page_css = 'remediation-guide.css';

$srcRoot = dirname(__DIR__, 4);
require_once $srcRoot . '/includes/remediation_guide_bootstrap.php';
require_once $srcRoot . '/includes/remediation_level_template.php';

$boot = remediation_guide_bootstrap($srcRoot, 'terminale');
$has_access = $boot['has_access'];
$user_name = $boot['user_name'];
$theme = $boot['theme'];

render_remediation_level_template([
    'theme_level' => 'terminale',
    'title' => 'Guide de remédiation Terminale',
    'subtitle' => 'Programmes 2025 – Préparation au Bac et remédiation personnalisée',
    'nav_aria' => 'Navigation rapide Terminale',
    'section_aria' => 'Matières et remédiation Terminale',
    'theme' => $theme,
    'nav_links' => [
        ['href' => site_url('lycee/terminale/exercices-terminale'), 'label' => 'Mes Exercices', 'aria' => 'Mes exercices Terminale', 'tone' => 'primary'],
        ['href' => site_url('cours', ['niveau' => 'terminale']), 'label' => 'Mes Cours', 'aria' => 'Mes cours', 'tone' => 'primary'],
        ['href' => site_url('lycee/lycee-accueil'), 'label' => 'Accueil Lycée', 'aria' => 'Accueil lycee', 'tone' => 'secondary'],
    ],
    'subjects' => [
        ['id' => 'maths', 'label' => 'Maths', 'icon' => '🧮', 'tone_index' => 1, 'open_fn' => 'openTermModal'],
        ['id' => 'francais', 'label' => 'Français', 'icon' => '📖', 'tone_index' => 0, 'open_fn' => 'openTermModal'],
        ['id' => 'sciences', 'label' => 'Sciences', 'icon' => '🔬', 'tone_index' => 3, 'open_fn' => 'openTermModal'],
        ['id' => 'histoire', 'label' => 'Histoire-Géo', 'icon' => '🌍', 'tone_index' => 2, 'open_fn' => 'openTermModal'],
        ['id' => 'organisation', 'label' => 'Organisation', 'icon' => '📅', 'tone_index' => 3, 'open_fn' => 'openTermModal'],
        ['id' => 'epreuve', 'label' => 'Épreuves', 'icon' => '📝', 'tone_index' => 1, 'open_fn' => 'openTermModal'],
        ['id' => 'oral', 'label' => 'Oral', 'icon' => '🎤', 'tone_index' => 0, 'open_fn' => 'openTermModal'],
        ['id' => 'annales', 'label' => 'Annales', 'icon' => '📚', 'tone_index' => 2, 'open_fn' => 'openTermModal'],
    ],
    'modals_include' => __DIR__ . '/guide-remediation.modals.inc.php',
    'action_include' => __DIR__ . '/guide-remediation.action.inc.php',
    'has_access' => $has_access,
    'user_name' => $user_name,
]);
