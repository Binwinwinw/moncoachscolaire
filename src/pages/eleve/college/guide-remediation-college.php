<?php
require_once dirname(__DIR__, 3) . '/config/site_boot.php';
require_once dirname(__DIR__, 3) . '/includes/remediation_hub_template.php';

$page_title = 'Guide de remediation College - MonCoachScolaire';
$page_class = 'remediation-hub-page';
$page_css = 'remediation-guide.css';

render_remediation_hub_template([
    'theme_level' => 'college',
    'title' => 'Guide de Remediation College',
    'subtitle' => '6eme - 5eme - 4eme - 3eme',
    'nav_aria' => 'Navigation rapide College',
    'section_aria' => 'Acces aux guides par classe',
    'cards_grid_class' => 'grid grid-cols-2 md:grid-cols-4 gap-4 w-full',
    'nav_links' => [
        [
            'href' => site_url('exercices'),
            'label' => 'Exercices',
            'aria' => 'Acceder aux exercices',
            'tone' => 'primary',
        ],
        [
            'href' => site_url('cours', ['niveau' => 'college']),
            'label' => 'Cours College',
            'aria' => 'Acceder aux cours College',
            'tone' => 'secondary',
        ],
        [
            'href' => site_url('college/college-accueil'),
            'label' => 'Accueil College',
            'aria' => 'Accueil College',
            'tone' => 'tertiary',
        ],
    ],
    'cards' => [
        [
            'href' => site_url('college/6eme/guide-remediation'),
            'label' => 'Guide 6eme',
            'aria' => 'Ouvrir le guide 6eme',
            'tone_index' => 0,
        ],
        [
            'href' => site_url('college/5eme/guide-remediation'),
            'label' => 'Guide 5eme',
            'aria' => 'Ouvrir le guide 5eme',
            'tone_index' => 1,
        ],
        [
            'href' => site_url('college/4eme/guide-remediation'),
            'label' => 'Guide 4eme',
            'aria' => 'Ouvrir le guide 4eme',
            'tone_index' => 2,
        ],
        [
            'href' => site_url('college/3eme/guide-remediation'),
            'label' => 'Guide 3eme',
            'aria' => 'Ouvrir le guide 3eme',
            'tone_index' => 3,
        ],
    ],
]);
?>
