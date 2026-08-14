<?php
/**
 * Configuration minimale des niveaux scolaires
 * Approche unique : get_student_level_config()
 */

if (!function_exists('get_student_level_config')) {
    function get_student_level_config(string $levelKey): ?array
    {
        $levels = [
            '4eme' => [
                'tier' => 'college',
                'level_key' => '4eme',
                'level_label' => '4ème',
                'theme_level' => '4eme',
                'page_css' => 'college/4eme/exercices-4eme.css',
                'page_class' => 'page-exercices-4eme',
                'dynamic_level' => '4ème',
                'preview_count' => 1,
                'header' => [
                    'icon' => '⚙️',
                    'title' => 'Exercices 4ème - Ingénieur en Herbe',
                    'subtitle' => 'Programme 2025 | Construis tes compétences étape par étape !',
                ],
                'routes' => [
                    'courses' => ['page' => 'cours', 'params' => ['niveau' => '4eme']],
                    'home' => ['page' => 'eleve/college/college-accueil'],
                    'dashboard' => ['page' => 'eleve/dashboard'],
                    'register' => ['page' => 'register'],
                    'login' => ['page' => 'login'],
                ],
                'coach' => [
                    'message' => "La 4ème, c'est l'année de l'ingénieur en herbe ! Tu vas découvrir de nouvelles compétences, explorer des domaines techniques et construire ton savoir étape par étape. Chaque défai relevé te rend plus fort. Prêt à devenir un ingénieur compétent ? L'aventure commence ! ⚙️",
                    'objective_title' => 'Ta Quête d\'Ingénieur',
                    'objective_text' => 'Construis tes compétences et maîtrise les nouveaux concepts ! Chaque objectif atteint est une étape importante dans ton parcours d\'ingénieur.',
                    'progress_label' => "Progression de l'ingénieur",
                    'complete_message' => "Tu as acquis de nouvelles compétences techniques aujourd'hui. Chaque projet réussi est une victoire pour ton avenir d'ingénieur. Continue tes explorations demain pour construire encore plus !",
                ],
            ],
            '5eme' => [
                'tier' => 'college',
                'level_key' => '5eme',
                'level_label' => '5ème',
                'theme_level' => '5eme',
                'page_css' => 'college/5eme/exercices-5eme.css',
                'page_class' => 'page-exercices-5eme',
                'dynamic_level' => '5ème',
                'preview_count' => 1,
                'header' => [
                    'icon' => '🧭',
                    'title' => 'Exercices 5ème - Explorateur des Savoirs',
                    'subtitle' => 'Programme 2025 | Explore et maîtrise de nouveaux territoires !',
                ],
                'routes' => [
                    'courses' => ['page' => 'cours', 'params' => ['niveau' => '5eme']],
                    'home' => ['page' => 'eleve/college/college-accueil'],
                    'dashboard' => ['page' => 'eleve/dashboard'],
                    'register' => ['page' => 'register'],
                    'login' => ['page' => 'login'],
                ],
                'coach' => [
                    'message' => "La 5ème, c'est l'année des découvertes ! Tu vas explorer de nouveaux territoires scolaires, découvrir des matières passionnantes. Chaque défi relevé te rend plus fort. Prêt à devenir un explorateur des connaissances ? L'aventure commence ! 🧭",
                    'objective_title' => 'Ta Quête du Jour',
                    'objective_text' => 'Approfondir tes connaissances et ouvrir ton esprit à de nouvelles matières ! Chaque exploration te rapproche du mystère des cartes perdues.',
                    'progress_label' => "Progression de l'exploration",
                    'complete_message' => "Tu as découvert de nouveaux territoires scolaires aujourd'hui. Chaque réponse correcte est une carte que tu ajoutes à ta collection. Continue tes explorations demain pour résoudre le mystère des cartes perdues!",
                ],
            ],
            '3eme' => [
                'tier' => 'college',
                'level_key' => '3eme',
                'level_label' => '3ème',
                'theme_level' => '3eme',
                'page_css' => 'college/3eme/exercices-3eme.css',
                'page_class' => 'page-exercices-3eme',
                'dynamic_level' => '3ème',
                'preview_count' => 1,
                'header' => [
                    'icon' => '🎓',
                    'title' => 'Exercices 3ème - Préparation Brevet',
                    'subtitle' => 'Programme 2025 | Maîtrise les concepts essentiels !',
                ],
                'routes' => [
                    'courses' => ['page' => 'cours', 'params' => ['niveau' => '3eme']],
                    'home' => ['page' => 'eleve/college/college-accueil'],
                    'dashboard' => ['page' => 'eleve/dashboard'],
                    'register' => ['page' => 'register'],
                    'login' => ['page' => 'login'],
                ],
                'coach' => [
                    'message' => "La 3ème, c'est le dernier pas vers le Brevet ! Tu vas consolider tout ce que tu as appris, préparer tes évaluations et t'assurer d'avoir toutes les clés en main. Chaque séance réussie est une victoire. Prêt à briller au Brevet ? L'aventure commence ! 🎓",
                    'objective_title' => 'Ta Quête du Brevet',
                    'objective_text' => 'Maîtrise les concepts essentiels et prépare-toi au Brevet avec confiance ! Chaque objectif atteint t'approche de ton diplôme.',
                    'progress_label' => "Progression vers le Brevet",
                    'complete_message' => "Tu as travaillé dur aujourd'hui. Chaque compétence acquise est une étape vers ton Brevet. Continue tes efforts demain et tu seras prêt !",
                ],
            ],
            '6eme' => [
                'tier' => 'college',
                'level_key' => '6eme',
                'level_label' => '6ème',
                'theme_level' => '6eme',
                'page_css' => 'college/6eme/exercices-6eme.css',
                'page_class' => 'page-exercices-6eme',
                'dynamic_level' => '6ème',
                'preview_count' => 1,
                'header' => [
                    'icon' => '🌟',
                    'title' => 'Exercices 6ème - Débutant',
                    'subtitle' => 'Programme 2025 | Découvre le monde des connaissances !',
                ],
                'routes' => [
                    'courses' => ['page' => 'cours', 'params' => ['niveau' => '6eme']],
                    'home' => ['page' => 'eleve/college/college-accueil'],
                    'dashboard' => ['page' => 'eleve/dashboard'],
                    'register' => ['page' => 'register'],
                    'login' => ['page' => 'login'],
                ],
                'coach' => [
                    'message' => "Bienvenue en 6ème ! C'est le début d'une grande aventure. Tu vas découvrir de nouvelles matières, rencontrer de nouveaux concepts et apprendre à te débrouiller seul. Chaque petite victoire compte. Tu fais déjà un pas vers la réussite ! 🌟",
                    'objective_title' => 'Ta Première Quête',
                    'objective_text' => 'Découvre le monde des connaissances et apprends à te débrouiller seul ! Chaque nouveau concept est une étape dans ton parcours.',
                    'progress_label' => "Progression initiale",
                    'complete_message' => "Tu as fait de nouveaux progrès aujourd'hui. Chaque nouvelle compétence acquise est un pas de plus vers ta réussite scolaire. Continue d'apprendre demain !",
                ],
            ],
            '1ere' => [
                'tier' => 'lycee',
                'level_key' => '1ere',
                'level_label' => '1ère',
                'theme_level' => '1ere',
                'page_css' => 'lycee/1ere/exercices-1ere.css',
                'page_class' => 'page-exercices-1ere',
                'dynamic_level' => '1ère',
                'preview_count' => 1,
                'header' => [
                    'icon' => '🎯',
                    'title' => 'Exercices 1ère - Orientation',
                    'subtitle' => 'Programme 2025 | Prépare ton orientation !',
                ],
                'routes' => [
                    'courses' => ['page' => 'cours', 'params' => ['niveau' => '1ere']],
                    'home' => ['page' => 'eleve/lycee/lycee-accueil'],
                    'dashboard' => ['page' => 'eleve/dashboard'],
                    'register' => ['page' => 'register'],
                    'login' => ['page' => 'login'],
                ],
                'coach' => [
                    'message' => "La 1ère, c'est l'année de l'orientation ! Tu vas affiner tes projets, choisir ta spécialité et préparer ton avenir. Chaque réflexion sur tes choix est une étape importante. Prêt à définir ton parcours ? L'aventure commence ! 🎯",
                    'objective_title' => 'Ta Quête d\'Orientation',
                    'objective_text' => 'Affine tes projets et choisis ta spécialité avec confiance ! Chaque objectif atteint te rapproche de ton orientation future.',
                    'progress_label' => "Progression vers l'orientation",
                    'complete_message' => "Tu as réfléchi à ton orientation aujourd'hui. Chaque décision prise est un pas vers ton avenir. Continue de travailler sur tes projets demain !",
                ],
            ],
            '2nde' => [
                'tier' => 'lycee',
                'level_key' => '2nde',
                'level_label' => '2nde',
                'theme_level' => '2nde',
                'page_css' => 'lycee/2nde/exercices-2nde.css',
                'page_class' => 'page-exercices-2nde',
                'dynamic_level' => '2nde',
                'preview_count' => 1,
                'header' => [
                    'icon' => '🗺️',
                    'title' => 'Exercices 2nde - Découverte',
                    'subtitle' => 'Programme 2025 | Explore le lycée !',
                ],
                'routes' => [
                    'courses' => ['page' => 'cours', 'params' => ['niveau' => '2nde']],
                    'home' => ['page' => 'eleve/lycee/lycee-accueil'],
                    'dashboard' => ['page' => 'eleve/dashboard'],
                    'register' => ['page' => 'register'],
                    'login' => ['page' => 'login'],
                ],
                'coach' => [
                    'message' => "Bienvenue au lycée ! Tu es en 2nde, l'année de découverte. Tu vas explorer de nouvelles matières, découvrir des nouvelles méthodes de travail et rencontrer des nouveaux camarades. C'est le début de tout un nouveau monde. Plonge dedans avec le sourire ! 🗺️",
                    'objective_title' => 'Ta Première Découverte',
                    'objective_text' => 'Explore le lycée et découvre de nouvelles matières ! Chaque nouveau concept est une opportunité.',
                    'progress_label' => "Progression de découverte",
                    'complete_message' => "Tu as découvert de nouveaux horizons aujourd'hui. Chaque matière explorée est une nouvelle opportunité. Continue d'apprendre demain !",
                ],
            ],
            'terminale' => [
                'tier' => 'lycee',
                'level_key' => 'terminale',
                'level_label' => 'Terminale',
                'theme_level' => 'terminale',
                'page_css' => 'lycee/terminale/exercices-terminale.css',
                'page_class' => 'page-exercices-terminale',
                'dynamic_level' => 'Terminale',
                'preview_count' => 1,
                'header' => [
                    'icon' => '🏆',
                    'title' => 'Exercices Terminale - Baccalauréat',
                    'subtitle' => 'Programme 2025 | Prépare le Baccalauréat !',
                ],
                'routes' => [
                    'courses' => ['page' => 'cours', 'params' => ['niveau' => 'terminale']],
                    'home' => ['page' => 'eleve/lycee/lycee-accueil'],
                    'dashboard' => ['page' => 'eleve/dashboard'],
                    'register' => ['page' => 'register'],
                    'login' => ['page' => 'login'],
                ],
                'coach' => [
                    'message' => "Bienvenue en Terminale ! C'est le dernier marathon avant le Baccalauréat. Tu vas consolider tout ton savoir, faire tes révisions finales et affiner tes connaissances. C'est le moment de mettre tout en œuvre pour briller. Tu es prêt à l'épreuve finale ? 🏆",
                    'objective_title' => 'Ta Quête du Baccalauréat',
                    'objective_text' => 'Révise avec méthode et prépare-toi au Baccalauréat avec confiance ! Chaque objectif atteint est une victoire pour ton diplôme.',
                    'progress_label' => "Progression vers le Baccalauréat",
                    'complete_message' => "Tu as travaillé dur aujourd'hui. Chaque révision effectuée est une étape vers ton Baccalauréat. Continue tes efforts demain !",
                ],
            ],
        ];

        return $levels[$levelKey] ?? null;
    }
}
?>
