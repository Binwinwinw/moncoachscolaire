<?php

/**
 * Système de génération de contenu de cours
 * Génère le contenu pédagogique pour chaque exercice
 */

/**
 * Génère le contenu de cours pour un exercice donné
 *
 * @param array $exercise Données de l'exercice
 * @param string $subject Matière
 * @param string $level Niveau
 * @return array Contenu du cours structuré
 */
function generateCourseContent($exercise, $subject, $level)
{
    $title = $exercise['Title'] ?? 'Cours';
    $content = $exercise['Content'] ?? '';
    $answer = $exercise['Answer'] ?? '';

    // Générer le contenu selon la matière
    $courseContent = [
        'title' => str_replace(['Exercice', 'exercice'], 'Cours', $title),
        'introduction' => generateIntroduction($subject, $level, $title),
        'objectives' => generateObjectives($subject, $title),
        'lessons' => generateLessons($subject, $level, $content, $answer),
        'examples' => generateExamples($subject, $content),
        'summary' => generateSummary($subject, $title),
        'practice' => [
            'description' => 'Maintenant que tu as compris le cours, teste tes connaissances avec l\'exercice associé !',
            'exercise_id' => $exercise['Id'] ?? 0,
        ],
    ];

    return $courseContent;
}

/**
 * Génère l'introduction du cours
 */
function generateIntroduction($subject, $level, $title)
{
    $intros = [
        'Mathématiques' => [
            '6ème' => 'Bienvenue dans ce cours de mathématiques pour la 6ème ! Les mathématiques sont partout autour de nous et te permettront de résoudre de nombreux problèmes du quotidien.',
            '5ème' => 'En 5ème, les mathématiques deviennent plus complexes. Tu vas découvrir de nouveaux concepts qui te serviront tout au long de ta scolarité.',
            '4ème' => 'Les mathématiques en 4ème t\'ouvrent les portes de l\'algèbre et de la géométrie avancée. Chaque concept est un pas vers la maîtrise.',
            '3ème' => 'En 3ème, tu consolides toutes tes connaissances mathématiques. C\'est l\'année de préparation au brevet !',
            'Seconde' => 'Les mathématiques en Seconde introduisent de nouveaux domaines. Tu vas explorer les fonctions, les statistiques et bien plus encore.',
            'Première' => 'En Première, les mathématiques deviennent plus abstraites avec les dérivées et les fonctions. C\'est passionnant !',
            'Terminale' => 'En Terminale, tu maîtrises l\'analyse, les intégrales et les probabilités. Tu es prêt pour le Bac !',
        ],
        'Français' => [
            '6ème' => 'Le français en 6ème, c\'est découvrir la langue, la grammaire et la littérature. Chaque leçon enrichit ta culture.',
            '5ème' => 'En 5ème, tu approfondis ta maîtrise de la langue française et découvres de nouveaux genres littéraires.',
            '4ème' => 'Le français en 4ème développe ton esprit critique et ta capacité à analyser des textes de manière approfondie.',
            '3ème' => 'En 3ème, tu prépares le brevet de français. Tu maîtrises l\'analyse de texte et la rédaction argumentative.',
            'Seconde' => 'Le français en Seconde développe tes compétences d\'analyse et d\'argumentation. Tu prépares le Bac de français.',
            'Première' => 'En Première, tu prépares activement le Bac de français avec l\'étude de textes et la dissertation.',
            'Terminale' => 'En Terminale, tu maîtrises l\'art de l\'argumentation et de l\'analyse littéraire. Tu es prêt pour le Bac !',
        ],
        'Sciences' => [
            '6ème' => 'Les sciences en 6ème te font découvrir le monde qui t\'entoure. Biologie, physique, chimie : chaque domaine est fascinant !',
            '5ème' => 'En 5ème, les sciences deviennent plus expérimentales. Tu vas comprendre les lois de la nature.',
            '4ème' => 'Les sciences en 4ème approfondissent les mécanismes du vivant et de la matière. C\'est passionnant !',
            '3ème' => 'En 3ème, tu consolides tes connaissances scientifiques pour le brevet. Tu comprends mieux le monde.',
            'Seconde' => 'Les sciences en Seconde explorent la biologie, la physique et la chimie en profondeur. Chaque expérience est une découverte.',
            'Première' => 'En Première, tu approfondis tes spécialités scientifiques. Tu prépares le Bac avec rigueur.',
            'Terminale' => 'En Terminale, tu maîtrises les sciences pour le Bac. Tu es prêt à comprendre les enjeux scientifiques du monde moderne.',
        ],
        'Histoire-Géo' => [
            '6ème' => 'L\'histoire-géographie en 6ème te fait voyager dans le temps et l\'espace. Découvre les civilisations anciennes !',
            '5ème' => 'En 5ème, tu explores le Moyen Âge et la Renaissance. Chaque période historique est riche d\'enseignements.',
            '4ème' => 'L\'histoire-géographie en 4ème couvre les Temps modernes et la Révolution. Comprends les enjeux du monde.',
            '3ème' => 'En 3ème, tu étudies le XXe siècle et la géographie de la France. Tu prépares le brevet.',
            'Seconde' => 'L\'histoire-géographie en Seconde explore le monde contemporain et ses enjeux géopolitiques.',
            'Première' => 'En Première, tu approfondis l\'histoire et la géographie pour le Bac. Tu comprends les enjeux du monde actuel.',
            'Terminale' => 'En Terminale, tu maîtrises l\'histoire et la géographie pour le Bac. Tu es prêt à comprendre le monde.',
        ],
        'Anglais' => [
            '6ème' => 'L\'anglais en 6ème, c\'est découvrir une nouvelle langue et une nouvelle culture. Chaque mot est une découverte !',
            '5ème' => 'En 5ème, tu approfondis ta maîtrise de l\'anglais. Tu communiques de plus en plus facilement.',
            '4ème' => 'L\'anglais en 4ème développe ta fluidité et ta compréhension. Tu es de plus en plus à l\'aise.',
            '3ème' => 'En 3ème, tu maîtrises mieux l\'anglais. Tu prépares le brevet avec confiance.',
            'Seconde' => 'L\'anglais en Seconde développe tes compétences de communication. Tu es prêt pour le Bac.',
            'Première' => 'En Première, tu approfondis l\'anglais pour le Bac. Tu communiques avec aisance.',
            'Terminale' => 'En Terminale, tu maîtrises l\'anglais pour le Bac. Tu es prêt pour l\'international !',
        ],
        'Philosophie' => [
            'Seconde' => 'La philosophie en Seconde t\'introduit à la réflexion philosophique. Chaque question est une aventure intellectuelle.',
            'Première' => 'En Première, tu développes ta réflexion philosophique. Tu apprends à penser par toi-même.',
            'Terminale' => 'La philosophie en Terminale est essentielle pour le Bac. Tu développes ton esprit critique et ta capacité à argumenter.',
        ],
    ];

    $intro = $intros[$subject][$level] ?? "Bienvenue dans ce cours de $subject pour le niveau $level !";
    return $intro . " Dans ce cours, tu vas apprendre : " . $title;
}

/**
 * Génère les objectifs d'apprentissage
 */
function generateObjectives($subject, $title)
{
    $objectives = [
        'Comprendre les concepts fondamentaux',
        'Maîtriser les méthodes de résolution',
        'Appliquer les connaissances dans des exercices',
        'Développer son autonomie et sa réflexion',
    ];

    // Ajouter des objectifs spécifiques selon la matière
    if ($subject === 'Mathématiques') {
        $objectives[] = 'Résoudre des problèmes mathématiques avec méthode';
    } elseif ($subject === 'Français') {
        $objectives[] = 'Analyser et comprendre des textes';
    } elseif ($subject === 'Sciences') {
        $objectives[] = 'Comprendre les phénomènes scientifiques';
    }

    return $objectives;
}

/**
 * Génère les leçons détaillées
 */
function generateLessons($subject, $level, $content, $answer)
{
    $lessons = [];

    // Analyser le contenu de l'exercice pour extraire les concepts
    if (!empty($content)) {
        // Détecter les concepts clés dans le contenu
        $concepts = extractConcepts($content, $subject);

        foreach ($concepts as $index => $concept) {
            $lessons[] = [
                'title' => $concept['title'],
                'content' => $concept['explanation'],
                'examples' => $concept['examples'] ?? [],
            ];
        }
    }

    // Si pas de contenu, générer des leçons par défaut
    if (empty($lessons)) {
        $lessons = generateDefaultLessons($subject, $level);
    }

    return $lessons;
}

/**
 * Extrait les concepts d'un contenu d'exercice
 */
function extractConcepts($content, $subject)
{
    $concepts = [];

    // Pour les mathématiques
    if ($subject === 'Mathématiques') {
        if (stripos($content, 'fraction') !== false) {
            $concepts[] = [
                'title' => 'Les Fractions',
                'explanation' => 'Une fraction représente une partie d\'un tout. Elle s\'écrit sous la forme a/b où a est le numérateur et b le dénominateur. Pour simplifier une fraction, on divise le numérateur et le dénominateur par leur plus grand commun diviseur (PGCD).',
                'examples' => [
                    'Exemple : 12/18 = (12÷6)/(18÷6) = 2/3',
                    'Pour additionner deux fractions, on les met au même dénominateur',
                ],
            ];
        }
        if (stripos($content, 'puissance') !== false || stripos($content, '^') !== false) {
            $concepts[] = [
                'title' => 'Les Puissances',
                'explanation' => 'Une puissance est une multiplication répétée. a^n signifie "a multiplié n fois par lui-même". Règles importantes : a^m × a^n = a^(m+n), (a^m)^n = a^(m×n), a^0 = 1.',
                'examples' => [
                    'Exemple : 2^3 = 2 × 2 × 2 = 8',
                    '(2^3)^2 = 2^6 = 64',
                ],
            ];
        }
        if (stripos($content, 'dérivée') !== false || stripos($content, 'dériv') !== false) {
            $concepts[] = [
                'title' => 'Les Dérivées',
                'explanation' => 'La dérivée d\'une fonction f(x) mesure le taux de variation de la fonction. Pour f(x) = x^n, la dérivée f\'(x) = n×x^(n-1). La dérivée permet de trouver les extremums (maximums et minimums) d\'une fonction.',
                'examples' => [
                    'Exemple : Si f(x) = x^2, alors f\'(x) = 2x',
                    'Si f\'(x) = 0, alors x est un extremum potentiel',
                ],
            ];
        }
    }

    // Pour le français
    if ($subject === 'Français') {
        if (stripos($content, 'conjugaison') !== false || stripos($content, 'verbe') !== false) {
            $concepts[] = [
                'title' => 'La Conjugaison',
                'explanation' => 'La conjugaison consiste à accorder un verbe avec son sujet. Il faut connaître les terminaisons de chaque temps et chaque groupe de verbes. Les verbes du premier groupe se terminent en -er (sauf aller), les verbes du deuxième groupe en -ir avec -issant au participe présent.',
                'examples' => [
                    'Exemple : Je mange, tu manges, il/elle mange',
                    'Les verbes irréguliers doivent être appris par cœur',
                ],
            ];
        }
        if (stripos($content, 'dissertation') !== false || stripos($content, 'argument') !== false) {
            $concepts[] = [
                'title' => 'La Dissertation',
                'explanation' => 'Une dissertation est un texte argumentatif structuré. Elle comprend : une introduction (avec problématique), un développement (arguments + exemples), des contre-arguments, et une conclusion. Chaque argument doit être illustré par un exemple concret.',
                'examples' => [
                    'Structure : Introduction → Développement (thèse/antithèse/synthèse) → Conclusion',
                    'Chaque paragraphe développe un argument avec un exemple',
                ],
            ];
        }
    }

    // Pour les sciences
    if ($subject === 'Sciences') {
        if (stripos($content, 'cellule') !== false) {
            $concepts[] = [
                'title' => 'La Cellule',
                'explanation' => 'La cellule est l\'unité de base du vivant. Une cellule eucaryote possède un noyau (contenant l\'ADN) et des organites (mitochondries pour l\'énergie, ribosomes pour les protéines, etc.). Chaque organite a une fonction spécifique.',
                'examples' => [
                    'Le noyau contient l\'ADN et contrôle les activités de la cellule',
                    'Les mitochondries produisent l\'énergie (ATP)',
                ],
            ];
        }
        if (stripos($content, 'énergie') !== false || stripos($content, 'bilan') !== false) {
            $concepts[] = [
                'title' => 'Le Bilan Énergétique',
                'explanation' => 'Dans un système isolé, l\'énergie totale est conservée. L\'énergie peut être cinétique (liée au mouvement) ou potentielle (liée à la position). Le bilan énergétique s\'écrit : E_initiale = E_finale.',
                'examples' => [
                    'Énergie cinétique : Ec = (1/2) × m × v²',
                    'Énergie potentielle : Ep = m × g × h',
                ],
            ];
        }
    }

    // Si aucun concept détecté, retourner un concept générique
    if (empty($concepts)) {
        $concepts[] = [
            'title' => 'Concepts Fondamentaux',
            'explanation' => 'Ce cours te permettra de comprendre les concepts essentiels nécessaires pour réussir les exercices. Prends le temps de bien lire et comprendre chaque partie.',
            'examples' => ['Lis attentivement chaque explication', 'Fais les exemples proposés'],
        ];
    }

    return $concepts;
}

/**
 * Génère des leçons par défaut
 */
function generateDefaultLessons($subject, $level)
{
    return [
        [
            'title' => 'Introduction aux Concepts',
            'content' => 'Dans ce cours, tu vas découvrir les concepts fondamentaux de ' . $subject . ' pour le niveau ' . $level . '. Prends le temps de bien comprendre chaque notion avant de passer à la suite.',
            'examples' => [],
        ],
        [
            'title' => 'Méthodes et Techniques',
            'content' => 'Tu vas apprendre les méthodes et techniques essentielles pour résoudre les exercices. Chaque méthode est importante et te sera utile.',
            'examples' => [],
        ],
        [
            'title' => 'Application Pratique',
            'content' => 'Maintenant que tu as compris les concepts, tu peux les appliquer dans des exercices. La pratique est essentielle pour maîtriser !',
            'examples' => [],
        ],
    ];
}

/**
 * Génère des exemples
 */
function generateExamples($subject, $content)
{
    $examples = [];

    // Générer des exemples selon la matière
    if ($subject === 'Mathématiques') {
        $examples[] = [
            'title' => 'Exemple 1 : Calcul Simple',
            'content' => 'Pour calculer 15 + 27, on additionne : 15 + 27 = 42',
            'step_by_step' => ['Étape 1 : Écrire les nombres', 'Étape 2 : Additionner unités : 5 + 7 = 12', 'Étape 3 : Additionner dizaines : 1 + 2 + 1 = 4', 'Résultat : 42'],
        ];
    } elseif ($subject === 'Français') {
        $examples[] = [
            'title' => 'Exemple 1 : Analyse de Phrase',
            'content' => 'Dans la phrase "Le chat mange la souris", "chat" est le sujet, "mange" est le verbe, "souris" est le complément d\'objet direct.',
            'step_by_step' => ['Identifier le verbe : "mange"', 'Poser la question "Qui mange ?" → "Le chat" (sujet)', 'Poser la question "Mange quoi ?" → "la souris" (COD)'],
        ];
    }

    return $examples;
}

/**
 * Génère le résumé du cours
 */
function generateSummary($subject, $title)
{
    return "Pour réussir cet exercice de $subject sur '$title', retiens ces points clés :\n"
           . "1. Comprends bien les concepts avant de commencer\n"
           . "2. Applique les méthodes apprises étape par étape\n"
           . "3. Vérifie toujours tes réponses\n"
           . "4. N'hésite pas à relire le cours si tu as un doute";
}

/**
 * Génère des cours par défaut pour une matière donnée
 * Utilisé quand il n'y a pas d'exercices dans la base de données
 *
 * @param string $subject Matière
 * @param string $level Niveau
 * @return array Liste de cours par défaut
 */
function generateDefaultCoursesForSubject($subject, $level)
{
    $courses = [];

    // Cours par matière et niveau
    $defaultCourses = [
        'Mathématiques' => [
            '6ème' => [
                ['id' => 'math-6eme-nombres', 'title' => 'Les Nombres et Opérations', 'description' => 'Apprends à maîtriser les nombres entiers, décimaux et les quatre opérations fondamentales.', 'preview' => ['title' => 'Les Nombres Entiers', 'content' => 'Découvre comment lire, écrire et comparer les nombres entiers. Apprends les techniques d\'addition, soustraction, multiplication et division.'], 'objectives' => ['Comprendre les nombres entiers et décimaux', 'Maîtriser les opérations de base', 'Résoudre des problèmes simples']],
                ['id' => 'math-6eme-geometrie', 'title' => 'Géométrie de Base', 'description' => 'Découvre les figures géométriques, les angles, les périmètres et les aires.', 'preview' => ['title' => 'Figures Géométriques', 'content' => 'Apprends à reconnaître et construire les figures géométriques de base : carré, rectangle, triangle, cercle.'], 'objectives' => ['Reconnaître les figures géométriques', 'Calculer périmètres et aires', 'Utiliser les instruments de géométrie']],
                ['id' => 'math-6eme-fractions', 'title' => 'Les Fractions', 'description' => 'Comprends ce qu\'est une fraction, comment la simplifier et l\'utiliser dans les calculs.', 'preview' => ['title' => 'Introduction aux Fractions', 'content' => 'Une fraction représente une partie d\'un tout. Apprends à lire, écrire et comparer les fractions.'], 'objectives' => ['Comprendre le concept de fraction', 'Simplifier une fraction', 'Additionner et soustraire des fractions']],
            ],
            '5ème' => [
                ['id' => 'math-5eme-algebre', 'title' => 'Introduction à l\'Algèbre', 'description' => 'Découvre les expressions littérales, les équations simples et la résolution de problèmes.', 'preview' => ['title' => 'Expressions Littérales', 'content' => 'Apprends à manipuler les lettres en mathématiques et à simplifier des expressions algébriques.'], 'objectives' => ['Comprendre les expressions littérales', 'Résoudre des équations simples', 'Appliquer l\'algèbre aux problèmes']],
                ['id' => 'math-5eme-proportionnalite', 'title' => 'Proportionnalité', 'description' => 'Maîtrise les tableaux de proportionnalité, les pourcentages et les échelles.', 'preview' => ['title' => 'Tableaux de Proportionnalité', 'content' => 'Apprends à reconnaître et utiliser les situations de proportionnalité dans la vie quotidienne.'], 'objectives' => ['Reconnaître la proportionnalité', 'Utiliser les pourcentages', 'Calculer avec les échelles']],
            ],
            'Seconde' => [
                ['id' => 'math-seconde-fonctions', 'title' => 'Les Fonctions', 'description' => 'Découvre le concept de fonction, les fonctions affines et les représentations graphiques.', 'preview' => ['title' => 'Introduction aux Fonctions', 'content' => 'Une fonction associe à chaque nombre un autre nombre. Apprends à les représenter et les analyser.'], 'objectives' => ['Comprendre le concept de fonction', 'Représenter graphiquement', 'Analyser les variations']],
                ['id' => 'math-seconde-statistiques', 'title' => 'Statistiques', 'description' => 'Apprends à analyser des données, calculer des moyennes, médianes et représenter graphiquement.', 'preview' => ['title' => 'Analyse de Données', 'content' => 'Découvre comment organiser, analyser et interpréter des données statistiques.'], 'objectives' => ['Calculer des indicateurs statistiques', 'Représenter des données', 'Interpréter des résultats']],
            ],
        ],
        'Français' => [
            '6ème' => [
                ['id' => 'francais-6eme-grammaire', 'title' => 'Grammaire de Base', 'description' => 'Maîtrise les classes de mots, les fonctions grammaticales et la construction de phrases.', 'preview' => ['title' => 'Classes de Mots', 'content' => 'Apprends à identifier les noms, verbes, adjectifs, déterminants et leurs rôles dans la phrase.'], 'objectives' => ['Identifier les classes de mots', 'Comprendre les fonctions', 'Construire des phrases correctes']],
                ['id' => 'francais-6eme-conjugaison', 'title' => 'Conjugaison', 'description' => 'Apprends à conjuguer les verbes aux différents temps et modes de la langue française.', 'preview' => ['title' => 'Temps de l\'Indicatif', 'content' => 'Maîtrise le présent, l\'imparfait, le futur et le passé composé de l\'indicatif.'], 'objectives' => ['Conjuguer au présent', 'Maîtriser les temps du passé', 'Utiliser les temps correctement']],
            ],
            'Seconde' => [
                ['id' => 'francais-seconde-analyse', 'title' => 'Analyse de Texte', 'description' => 'Développe tes compétences d\'analyse littéraire et de compréhension de texte.', 'preview' => ['title' => 'Méthode d\'Analyse', 'content' => 'Apprends à analyser un texte en identifiant les procédés littéraires et les enjeux.'], 'objectives' => ['Analyser un texte littéraire', 'Identifier les procédés', 'Rédiger un commentaire']],
            ],
        ],
        'Sciences' => [
            '6ème' => [
                ['id' => 'sciences-6eme-vivant', 'title' => 'Le Monde Vivant', 'description' => 'Découvre la classification des êtres vivants, les cellules et les écosystèmes.', 'preview' => ['title' => 'Classification du Vivant', 'content' => 'Apprends à classer les animaux et végétaux selon leurs caractéristiques communes.'], 'objectives' => ['Classer les êtres vivants', 'Comprendre la cellule', 'Étudier les écosystèmes']],
                ['id' => 'sciences-6eme-matiere', 'title' => 'La Matière', 'description' => 'Explore les états de la matière, les mélanges et les transformations physiques et chimiques.', 'preview' => ['title' => 'États de la Matière', 'content' => 'Découvre les trois états de la matière : solide, liquide et gazeux, et leurs propriétés.'], 'objectives' => ['Comprendre les états de la matière', 'Distinguer mélanges et corps purs', 'Observer les transformations']],
            ],
        ],
        'Histoire-Géo' => [
            '6ème' => [
                ['id' => 'histgeo-6eme-antiquite', 'title' => 'L\'Antiquité', 'description' => 'Explore les civilisations de l\'Antiquité : Égypte, Grèce et Rome.', 'preview' => ['title' => 'Civilisations Antiques', 'content' => 'Découvre les grandes civilisations de l\'Antiquité et leur héritage.'], 'objectives' => ['Connaître les civilisations antiques', 'Comprendre leur organisation', 'Identifier leur héritage']],
            ],
        ],
        'Anglais' => [
            '6ème' => [
                ['id' => 'anglais-6eme-bases', 'title' => 'Les Bases de l\'Anglais', 'description' => 'Apprends les bases de la langue anglaise : vocabulaire, grammaire et communication.', 'preview' => ['title' => 'Vocabulaire de Base', 'content' => 'Acquiers le vocabulaire essentiel pour communiquer en anglais au quotidien.'], 'objectives' => ['Acquérir du vocabulaire', 'Maîtriser la grammaire de base', 'Communiquer simplement']],
            ],
        ],
    ];

    // Récupérer les cours pour cette matière et ce niveau
    if (isset($defaultCourses[$subject][$level])) {
        return $defaultCourses[$subject][$level];
    }

    // Si pas de cours spécifiques, créer un cours générique
    return [[
        'id' => strtolower($subject) . '-' . strtolower($level) . '-general',
        'title' => "Cours de $subject - $level",
        'description' => "Cours complet sur les notions essentielles de $subject pour le niveau $level.",
        'preview' => [
            'title' => 'Notions Fondamentales',
            'content' => "Ce cours couvre les concepts essentiels de $subject nécessaires pour réussir au niveau $level.",
        ],
        'objectives' => [
            'Comprendre les concepts fondamentaux',
            'Maîtriser les méthodes de base',
            'Appliquer les connaissances',
        ],
    ]];
}
