<?php
require_once dirname(__DIR__, 3) . '/config/site_boot.php';
require_once dirname(__DIR__, 3) . '/includes/remediation_hub_template.php';

$page_title = 'Guide de remediation Lycee - MonCoachScolaire';
$page_class = 'remediation-hub-page';
$page_css = 'remediation-guide.css';

render_remediation_hub_template([
    'theme_level' => 'lycee',
    'title' => 'Guide de Remediation Lycee',
    'subtitle' => 'Seconde - Premiere - Terminale',
    'nav_aria' => 'Navigation rapide Lycee',
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
            'href' => site_url('cours', ['niveau' => 'lycee']),
            'label' => 'Cours Lycee',
            'aria' => 'Acceder aux cours Lycee',
            'tone' => 'secondary',
        ],
        [
            'href' => site_url('lycee/lycee-accueil'),
            'label' => 'Accueil Lycee',
            'aria' => 'Accueil Lycee',
            'tone' => 'tertiary',
        ],
    ],
    'cards' => [
        [
            'href' => site_url('lycee/2nde/guide-remediation'),
            'label' => 'Guide Seconde',
            'aria' => 'Ouvrir le guide Seconde',
            'tone_index' => 0,
        ],
        [
            'href' => site_url('lycee/1ere/guide-remediation'),
            'label' => 'Guide Premiere',
            'aria' => 'Ouvrir le guide Premiere',
            'tone_index' => 1,
        ],
        [
            'href' => site_url('lycee/terminale/guide-remediation'),
            'label' => 'Guide Terminale',
            'aria' => 'Ouvrir le guide Terminale',
            'tone_index' => 2,
        ],
        [
            'href' => site_url('bac/guide-remediation'),
            'label' => 'Guide Bac',
            'aria' => 'Ouvrir le guide Bac',
            'class' => 'rounded-xl bg-violet-100 text-violet-800 font-semibold shadow hover:bg-violet-200 focus:outline-none focus:ring-2 focus:ring-violet-400 transition px-6 py-4 text-center',
        ],
    ],
]);
?>
