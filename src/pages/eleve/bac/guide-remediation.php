<?php
require_once dirname(__DIR__, 3) . '/config/site_boot.php';
require_once dirname(__DIR__, 3) . '/includes/remediation_hub_template.php';

$page_title = 'Guide de remediation Bac - MonCoachScolaire';
$page_class = 'remediation-hub-page';
$page_css = 'remediation-guide.css';
$page_theme_level = 'bac';

render_remediation_hub_template([
    'theme_level' => 'bac',
    'title' => 'Guide de Remediation Bac',
    'subtitle' => 'Preparation ecrite et orale',
    'nav_aria' => 'Navigation rapide Bac',
    'section_aria' => 'Raccourcis remediation Bac',
    'cards_grid_class' => 'grid grid-cols-1 md:grid-cols-3 gap-4 w-full',
    'nav_links' => [
        [
            'href' => site_url('exercices'),
            'label' => 'Exercices',
            'aria' => 'Acceder aux exercices',
            'tone' => 'primary',
        ],
        [
            'href' => site_url('cours', ['niveau' => 'bac']),
            'label' => 'Cours Bac',
            'aria' => 'Acceder aux cours Bac',
            'tone' => 'secondary',
        ],
        [
            'href' => site_url('bac/preparation-orale'),
            'label' => 'Preparation Orale',
            'aria' => 'Preparation a l oral',
            'tone' => 'tertiary',
        ],
        [
            'href' => site_url('bac/bac-accueil'),
            'label' => 'Accueil Bac',
            'aria' => 'Accueil Bac',
            'tone' => 'tertiary',
        ],
    ],
    'cards' => [
        [
            'label' => 'Organisation hebdomadaire',
            'class' => 'rounded-2xl bg-amber-50 border border-amber-200 p-6 shadow-sm text-amber-900',
            'text' => 'Planifie 5 seances courtes par semaine: 2 epreuves ecrites, 2 revisions actives, 1 simulation orale.',
        ],
        [
            'label' => 'Methodes de revision',
            'class' => 'rounded-2xl bg-amber-100 border border-amber-200 p-6 shadow-sm text-amber-900',
            'text' => 'Alterne fiches de synthese, annales corrigees et auto-evaluation chronometree pour renforcer la regularite.',
        ],
        [
            'label' => 'Preparation de l oral',
            'class' => 'rounded-2xl bg-yellow-50 border border-yellow-200 p-6 shadow-sm text-yellow-900',
            'text' => 'Entraine-toi avec un plan en 3 parties, des transitions claires, et une gestion stricte du temps de parole.',
        ],
    ],
]);
?>
