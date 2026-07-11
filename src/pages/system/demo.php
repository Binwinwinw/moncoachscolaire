<?php
/**
 * Page de démonstration - MonCoachScolaire
 * Permet aux visiteurs de tester les fonctionnalités sans créer de compte
 */

$page_title = 'Démonstration - MonCoachScolaire';
$page_css = 'pages/demo.css';

// ====== VÉRIFICATIONS & REDIRECTIONS EN PREMIER (AVANT site_boot.php) ======
// IMPORTANT: Les redirections avec header() doivent se faire AVANT tout output


// ====== À PARTIR D'ICI, ON PEUT INCLURE LES DÉPENDANCES ======

// Charger les dépendances
require_once dirname(__DIR__, 2) . '/config/site_boot.php';
require_once __DIR__ . '/../../includes/exercice_loader.php';
require_once __DIR__ . '/../../includes/quiz_generator.php';
require_once __DIR__ . '/../../includes/gamification.php';
require_once __DIR__ . '/../../includes/progress_helpers.php';
require_once __DIR__ . '/../../includes/exercice_card.php';

$demoTrackingUrl = '/api/track-demo-action.php';
if (function_exists('site_url')) {
    $demoTrackingUrl = site_url('api/track-demo-action');
} elseif (!empty($baseUrl)) {
    $demoTrackingUrl = rtrim((string) $baseUrl, '/') . '/api/track-demo-action.php';
}

// Activer le mode démo si accès via ?demo=1 ou si l'utilisateur n'est pas connecté
if ((!empty($_GET['demo']) && $_GET['demo'] === '1') || empty($_SESSION['logged_in'])) {
    // Forcer le mode démo à chaque requête

    $_SESSION['is_demo'] = true;
    if (empty($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 0; // ID virtuel pour le mode démo
        $_SESSION['user_name'] = 'Visiteur Démo';
        $_SESSION['user_level'] = '6ème';
    }
    // Définir logged_in pour que les scripts soient chargés
    if (empty($_SESSION['logged_in'])) {
        $_SESSION['logged_in'] = true;
    }
} elseif (empty($_SESSION['is_demo'])) {
    // Si pas en mode démo et pas de paramètre ?demo=1, garder is_demo = false
    $_SESSION['is_demo'] = false;
}

// Gérer la sélection du niveau
$selected_level = $_GET['level'] ?? $_SESSION['demo_level'] ?? '6ème';
$_SESSION['demo_level'] = $selected_level;

// Niveaux disponibles
$available_levels = [
    'Collège' => [
        '6ème' => '6ème',
        '5ème' => '5ème',
        '4ème' => '4ème',
        '3ème' => '3ème',
    ],
    'Lycée' => [
        'Seconde' => 'Seconde',
        'Première' => 'Première',
        'Terminale' => 'Terminale',
        'Bac' => 'Terminale', // Ajout du niveau Bac, mappé sur Terminale
    ],
];

// Note: La fonction normalizeLevelForDB est définie dans includes/exercice_loader.php
// Si elle n'existe pas encore (peut arriver si exercice_loader.php n'est pas chargé),
// on utilise une version simplifiée ici
if (!function_exists('normalizeLevelForDB')) {
    function normalizeLevelForDB($level)
    {
        if (empty($level)) {
            return null;
        }

        $levelMapping = [
            '6ème' => '6ème', '6eme' => '6ème', '6EME' => '6ème',
            '5ème' => '5ème', '5eme' => '5ème', '5EME' => '5ème',
            '4ème' => '4ème', '4eme' => '4ème', '4EME' => '4ème',
            '3ème' => '3ème', '3eme' => '3ème', '3EME' => '3ème',
            'Seconde' => 'Seconde', 'seconde' => 'Seconde', '2nde' => 'Seconde',
            'Première' => 'Première', 'Premiere' => 'Première', 'première' => 'Première', '1ère' => 'Première',
            'Terminale' => 'Terminale', 'terminale' => 'Terminale', 'BAC' => 'Terminale', 'Bac' => 'Terminale', 'bac' => 'Terminale',
        ];

        $levelClean = trim($level);
        return $levelMapping[$levelClean] ?? $levelClean;
    }
}

// Charger les exercices réels depuis la base de données
$sampleExercises = [];
if (isset($pdo) && $pdo) {
    try {
        // Normaliser le niveau et charger les exercices
        // getExercisesByLevel() gère déjà la normalisation et les variantes en interne
        $normalizedLevel = normalizeLevelForDB($selected_level);
        $allExercises = getExercisesByLevelSmart($normalizedLevel, null, 50); // Charger plus pour avoir le choix


        // VÉRIFICATION DE SÉCURITÉ : S'assurer que tous les exercices sont du bon niveau
        $filteredExercises = [];
        foreach ($allExercises as $ex) {
            $exLevel = normalizeLevelForDB($ex['Level'] ?? '');
            if ($exLevel === $normalizedLevel) {
                $filteredExercises[] = $ex;
            } else {
                // Log l'erreur pour debug
                error_log("DEMO: Exercice de niveau incorrect filtré - ID: {$ex['Id']}, Titre: {$ex['Title']}, Niveau attendu: $normalizedLevel, Niveau trouvé: {$ex['Level']}");
            }
        }

        // Sélectionner exactement 3 exercices aléatoires
        if (count($filteredExercises) > 3) {
            shuffle($filteredExercises);
            $sampleExercises = array_slice($filteredExercises, 0, 3);
        } else {
            $sampleExercises = $filteredExercises;
        }

        // Si aucun exercice trouvé après filtrage, essayer avec 6ème par défaut
        if (empty($sampleExercises) && $normalizedLevel !== '6ème') {
            $fallbackExercises = getExercisesByLevel('6ème', null, 50);
            $filteredFallback = [];
            foreach ($fallbackExercises as $ex) {
                $exLevel = normalizeLevelForDB($ex['Level'] ?? '');
                if ($exLevel === '6ème') {
                    $filteredFallback[] = $ex;
                }
            }
            if (!empty($filteredFallback)) {
                shuffle($filteredFallback);
                $sampleExercises = array_slice($filteredFallback, 0, 3);
                $selected_level = '6ème';
                $_SESSION['demo_level'] = '6ème';
            }
        }
    } catch (Exception $e) {
        error_log("Erreur chargement exercices démo: " . $e->getMessage());
    }
}

// Si aucun exercice n'est chargé, créer des exercices statiques de démo
if (empty($sampleExercises)) {
    // Afficher un avertissement en haut de page (à intégrer dans le HTML plus bas)
    $demo_warning = '⚠️ Aucun exercice réel trouvé pour ce niveau. Les exercices affichés sont fictifs.';
    $sampleExercises = [
        [
            'Id' => 'demo-1',
            'Title' => 'Addition et soustraction',
            'Subject' => 'Mathématiques',
            'Level' => '6ème',
            'Content' => 'Calcule les opérations suivantes :\n\nQ1: Combien font 15 + 27 = ?\n\nQ2: Combien font 45 - 18 = ?\n\nQ3: Combien font 12 + 34 - 8 = ?',
            'Answer' => 'Q1: 42\nQ2: 27\nQ3: 38',
        ],
        [
            'Id' => 'demo-2',
            'Title' => 'Conjugaison au présent',
            'Subject' => 'Français',
            'Level' => '6ème',
            'Content' => 'Conjuguez le verbe "aller" au présent de l\'indicatif :\n\n1. Je ____ à l\'école.\n2. Tu ____ au parc.\n3. Il ____ au cinéma.\n4. Nous ____ en vacances.\n5. Vous ____ au marché.\n6. Ils ____ à la plage.',
            'Answer' => '1. vais\n2. vas\n3. va\n4. allons\n5. allez\n6. vont',
        ],
        [
            'Id' => 'demo-3',
            'Title' => 'Les planètes du système solaire',
            'Subject' => 'Sciences',
            'Level' => '6ème',
            'Content' => 'Question 1 : Quelle est la planète la plus proche du Soleil ?\nA) Vénus\nB) Mercure\nC) Terre\nD) Mars\n\nQuestion 2 : Quelle est la plus grande planète du système solaire ?\nA) Saturne\nB) Jupiter\nC) Neptune\nD) Uranus',
            'Answer' => 'Question 1 : B) Mercure\nQuestion 2 : B) Jupiter',
        ],
    ];
}

// Charger un cours aléatoire d'une matière du niveau choisi
$sampleCourse = null;
$sampleCourseSubject = null;
if (isset($pdo) && $pdo && !empty($sampleExercises)) {
    try {
        require_once __DIR__ . '/../../includes/course_content.php';

        // Déterminer les matières disponibles pour ce niveau
        $is_college = in_array($selected_level, ['6ème', '5ème', '4ème', '3ème']);
        $availableSubjects = $is_college
            ? ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais']
            : ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie'];

        // Sélectionner une matière aléatoire
        shuffle($availableSubjects);
        $sampleCourseSubject = $availableSubjects[0];

        // Charger un exercice de cette matière pour générer un cours
        $normalizedLevel = normalizeLevelForDB($selected_level);
        $courseExercises = getExercisesByLevel($normalizedLevel, $sampleCourseSubject, 10);

        if (!empty($courseExercises)) {
            // Sélectionner un exercice aléatoire
            shuffle($courseExercises);
            $randomExercise = $courseExercises[0];

            // Générer le contenu du cours
            $courseContent = generateCourseContent($randomExercise, $sampleCourseSubject, $selected_level);

            $sampleCourse = [
                'title' => $courseContent['title'],
                'subject' => $sampleCourseSubject,
                'level' => $selected_level,
                'introduction' => $courseContent['introduction'],
                'objectives' => $courseContent['objectives'],
                'lessons' => $courseContent['lessons'],
                'examples' => $courseContent['examples'],
                'summary' => $courseContent['summary'],
                'exercise_id' => $randomExercise['Id'] ?? 0,
            ];
        }
    } catch (Exception $e) {
        error_log("Erreur chargement cours démo: " . $e->getMessage());
    }
}

// Fallback si aucun cours n'a été chargé
if (!$sampleCourse) {
    $sampleCourse = [
        'title' => 'Les Fractions - Niveau ' . $selected_level,
        'subject' => 'Mathématiques',
        'level' => $selected_level,
        'introduction' => 'Bienvenue dans ce cours de mathématiques ! Les fractions représentent une partie d\'un tout. Par exemple, 1/2 signifie "une partie sur deux".',
        'objectives' => ['Comprendre le concept de fraction', 'Savoir lire et écrire des fractions', 'Effectuer des opérations avec des fractions'],
        'lessons' => [
            [
                'title' => 'Introduction aux fractions',
                'content' => 'Une fraction est un nombre qui représente une partie d\'un tout. Elle s\'écrit sous la forme a/b où a est le numérateur et b le dénominateur.',
            ],
        ],
        'examples' => [],
        'summary' => 'Les fractions sont essentielles en mathématiques et te permettront de résoudre de nombreux problèmes.',
    ];
    $sampleCourseSubject = 'Mathématiques';
}

// Générer un quiz aléatoire adapté au niveau choisi
$sampleQuiz = null;
$sampleQuizSubject = null;
if (isset($pdo) && $pdo) {
    try {
        require_once __DIR__ . '/../../includes/quiz_generator.php';

        // Déterminer les matières disponibles pour ce niveau
        $is_college = in_array($selected_level, ['6ème', '5ème', '4ème', '3ème']);
        $availableSubjects = $is_college
            ? ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais']
            : ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie'];

        // Sélectionner une matière aléatoire
        shuffle($availableSubjects);
        $sampleQuizSubject = $availableSubjects[0];

        if (function_exists('getQuizQuestions')) {
            $quizQuestions = getQuizQuestions($sampleQuizSubject, $selected_level, $pdo, 5);
            if (!empty($quizQuestions)) {
                $sampleQuiz = $quizQuestions;
            }
        }

        // Si aucun quiz trouvé pour cette matière, essayer avec une autre
        if (empty($sampleQuiz)) {
            foreach ($availableSubjects as $subject) {
                if ($subject === $sampleQuizSubject) {
                    continue;
                }
                $quizQuestions = getQuizQuestions($subject, $selected_level, $pdo, 5);
                if (!empty($quizQuestions)) {
                    $sampleQuiz = $quizQuestions;
                    $sampleQuizSubject = $subject;
                    break;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Erreur génération quiz démo: " . $e->getMessage());
    }
}

// Si aucun quiz n'est chargé, créer un quiz statique cohérent
if (empty($sampleQuiz)) {
    $sampleQuiz = [
        [
            'question' => 'Quel est le résultat de 15 + 27 ?',
            'choices' => ['40', '42', '44', '45'],
            'correct' => '42',
            'subject' => 'Mathématiques',
            'explanation' => '15 + 27 = 42. Pour additionner, on aligne les unités et les dizaines.',
        ],
        [
            'question' => 'Quel est le résultat de 8 × 7 ?',
            'choices' => ['54', '56', '58', '64'],
            'correct' => '56',
            'subject' => 'Mathématiques',
            'explanation' => '8 × 7 = 56. C\'est une des tables de multiplication à connaître par cœur.',
        ],
        [
            'question' => 'Quelle est la conjugaison correcte de "aller" à la 1ère personne du singulier au présent ?',
            'choices' => ['Je vas', 'Je va', 'Je vais', 'Je aller'],
            'correct' => 'Je vais',
            'subject' => 'Français',
            'explanation' => 'Le verbe "aller" se conjugue "je vais" au présent de l\'indicatif.',
        ],
        [
            'question' => 'Quelle est la planète la plus proche du Soleil ?',
            'choices' => ['Vénus', 'Mercure', 'Terre', 'Mars'],
            'correct' => 'Mercure',
            'subject' => 'Sciences',
            'explanation' => 'Mercure est la planète la plus proche du Soleil dans notre système solaire.',
        ],
        [
            'question' => 'Quel est le plus grand océan de la planète ?',
            'choices' => ['Océan Atlantique', 'Océan Pacifique', 'Océan Indien', 'Océan Arctique'],
            'correct' => 'Océan Pacifique',
            'subject' => 'Géographie',
            'explanation' => 'L\'océan Pacifique est le plus grand océan du monde, couvrant environ un tiers de la surface terrestre.',
        ],
    ];
}

// Données pré-remplies pour Le Labo des Génies (aperçu)
$demoProgress = [
    'xp' => 250,
    'level' => 3,
    'levelName' => 'Explorateur',
    'position' => 12,
    'cristaux' => 45,
    'exercisesCompleted' => 8,
    'badgesCount' => 2,
    'powersCount' => 1,
    'totalPowers' => 8,
    'progressPercentage' => 35,
    'bySubject' => [
        'Mathématiques' => ['completed' => 3, 'total' => 10],
        'Français' => ['completed' => 2, 'total' => 10],
        'Sciences' => ['completed' => 1, 'total' => 8],
    ],
    'badges' => [
        ['name' => 'Premier Pas', 'icon' => '👣'],
        ['name' => 'Mathématicien', 'icon' => '🧮'],
    ],
];

// Compter les actions de l'utilisateur (pour la limitation)
$actionCount = 0;
if (isset($_SESSION['demo_action_count'])) {
    $actionCount = (int) $_SESSION['demo_action_count'];
}
?>

<main class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 md:px-6 lg:px-8">
    <!-- Bannière Déconnexion Démo -->
    <div class="demo-logout-banner rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm backdrop-blur-sm" id="demo-logout-banner">
        <div class="logout-banner-content flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <span class="logout-icon text-xl">ℹ️</span>
            <div class="logout-text flex-1">
                <strong class="block text-sm font-semibold text-slate-900">Mode Démonstration actif</strong>
                <span class="mt-1 block text-sm text-slate-600">Vous testez l'application en mode démo. Pour créer un compte ou vous connecter, quittez d'abord le mode démo.</span>
            </div>
            <div class="logout-actions flex flex-wrap gap-2">
                <a href="<?php echo site_url('logout'); ?>" class="logout-button inline-flex items-center rounded-full border border-slate-300 bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">🚪 Quitter le mode démo</a>
                <a href="<?php echo site_url('landingpage'); ?>" class="logout-button-secondary inline-flex items-center rounded-full border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">🏠 Retour à l'accueil</a>
            </div>
            <button type="button" class="logout-banner-close rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500" onclick="document.getElementById('demo-logout-banner').style.display='none'" title="Fermer cette bannière" aria-label="Fermer cette bannière">×</button>
        </div>
    </div>

    <!-- Bannière CTA -->
    <div class="demo-cta-banner rounded-2xl border border-emerald-200 bg-emerald-50/90 p-4 shadow-sm" id="demo-cta-banner">
        <div class="cta-content flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <span class="cta-icon text-xl">🎯</span>
            <div class="cta-text flex-1">
                <strong class="block text-sm font-semibold text-emerald-900">Mode Démonstration</strong>
                <span class="mt-1 block text-sm text-emerald-800">Créez un compte gratuit pour sauvegarder votre progression et accéder à tous les contenus !</span>
            </div>
            <a href="<?php echo site_url('register'); ?>" class="cta-button inline-flex items-center rounded-full bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">✨ Créer mon compte</a>
            <button type="button" class="cta-close rounded-full p-2 text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500" onclick="document.getElementById('demo-cta-banner').style.display='none'" aria-label="Fermer la bannière">×</button>
        </div>
    </div>

    <!-- En-tête de la démo -->
    <div class="demo-header rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-700 p-8 text-white shadow-sm">
        <h1 class="text-3xl font-bold sm:text-4xl">🎮 Découvrez MonCoachScolaire</h1>
        <p class="demo-subtitle mt-3 max-w-2xl text-lg text-slate-100">Testez gratuitement nos exercices interactifs, cours et quiz avant de créer votre compte</p>
    </div>

    <!-- Aperçu Le Labo des Génies -->
    <section class="demo-section rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="labo">
        <h2 class="text-2xl font-semibold text-slate-900">🔬 Le Labo des Génies - Aperçu</h2>
        <p class="section-description mt-2 text-sm text-slate-600">Découvrez le système de gamification scientifique qui récompense vos progrès</p>

        <div class="odyssey-preview mt-6 rounded-3xl border border-slate-200 bg-slate-50/80 p-6 shadow-inner">
            <div class="odyssey-stats grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="odyssey-stat rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $demoProgress['xp']; ?> XP</div>
                        <div class="stat-label">Points d'expérience</div>
                    </div>
                </div>
                <div class="odyssey-stat">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-info">
                        <div class="stat-value">Niveau <?php echo $demoProgress['level']; ?></div>
                        <div class="stat-label"><?php echo htmlspecialchars($demoProgress['levelName']); ?></div>
                    </div>
                </div>
                <div class="odyssey-stat">
                    <div class="stat-icon">⚗️</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $demoProgress['cristaux']; ?></div>
                        <div class="stat-label">Éléments collectés</div>
                    </div>
                </div>
                <div class="odyssey-stat">
                    <div class="stat-icon">🎖️</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $demoProgress['badgesCount']; ?></div>
                        <div class="stat-label">Badges débloqués</div>
                    </div>
                </div>
            </div>

            <div class="odyssey-progress mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Progression par matière</h3>
                <?php foreach ($demoProgress['bySubject'] as $subject => $data): ?>
                    <div class="subject-progress">
                        <div class="subject-header">
                            <span class="subject-name"><?php echo htmlspecialchars($subject); ?></span>
                            <span class="subject-stats"><?php echo $data['completed']; ?>/<?php echo $data['total']; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="odyssey-badges mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-900">Badges débloqués</h3>
                <div class="badges-list">
                    <?php foreach ($demoProgress['badges'] as $badge): ?>
                        <div class="badge-item">
                            <span class="badge-icon"><?php echo $badge['icon']; ?></span>
                            <span class="badge-name"><?php echo htmlspecialchars($badge['name']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="odyssey-cta mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                <p>🚀 <strong>Créez un compte</strong> pour commencer votre propre aventure dans Le Labo des Génies !</p>
                <a href="<?php echo site_url('register'); ?>" class="demo-cta-link large mt-3 inline-flex rounded-full bg-emerald-700 px-4 py-2 font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">✨ Créer mon compte gratuit</a>
            </div>
        </div>
    </section>

    <!-- Sélecteur de niveau - Carte séparée -->
    <section class="demo-section demo-level-card rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="level-selector">
        <h2 class="text-2xl font-semibold text-slate-900">📚 Choisissez votre niveau scolaire</h2>
        <p class="section-description mt-2 text-sm text-slate-600">Sélectionnez votre niveau pour voir les exercices adaptés</p>

        <div class="demo-level-selector mt-6 rounded-3xl border border-slate-200 bg-slate-50/70 p-5 shadow-inner">
            <div class="level-columns-wrapper">
                <!-- Colonne Collège -->
                <div class="level-column">
                    <h3 class="level-column-title">🏫 Collège</h3>
                    <div class="level-buttons">
                        <button type="button"
                                class="level-btn demo-level-button <?php echo ($selected_level === '6ème') ? 'active' : ''; ?>"
                                data-level="6ème">
                            6ème
                        </button>
                        <button type="button"
                                class="level-btn demo-level-button <?php echo ($selected_level === '5ème') ? 'active' : ''; ?>"
                                data-level="5ème">
                            5ème
                        </button>
                        <button type="button"
                                class="level-btn demo-level-button <?php echo ($selected_level === '4ème') ? 'active' : ''; ?>"
                                data-level="4ème">
                            4ème
                        </button>
                        <button type="button"
                                class="level-btn demo-level-button <?php echo ($selected_level === '3ème') ? 'active' : ''; ?>"
                                data-level="3ème">
                            3ème
                        </button>
                    </div>
                </div>

                <!-- Colonne Lycée -->
                <div class="level-column">
                    <h3 class="level-column-title">🎓 Lycée</h3>
                    <div class="level-buttons">
                        <button type="button"
                                class="level-btn demo-level-button <?php echo ($selected_level === 'Seconde') ? 'active' : ''; ?>"
                                data-level="Seconde">
                            Seconde
                        </button>
                        <button type="button"
                                class="level-btn demo-level-button <?php echo ($selected_level === 'Première') ? 'active' : ''; ?>"
                                data-level="Première">
                            Première
                        </button>
                        <button type="button"
                                class="level-btn demo-level-button <?php echo ($selected_level === 'Terminale') ? 'active' : ''; ?>"
                                data-level="Terminale">
                            Terminale
                        </button>
                        <button type="button"
                                class="level-btn demo-level-button <?php echo ($selected_level === 'Bac') ? 'active' : ''; ?>"
                                data-level="Bac">
                            Bac
                        </button>
                    </div>
                </div>
            </div>
            <p class="level-info" id="level-info-message">
                <?php if (!empty($sampleExercises)): ?>
                    ✅ <?php echo count($sampleExercises); ?> exercice<?php echo count($sampleExercises) > 1 ? 's' : ''; ?> disponible<?php echo count($sampleExercises) > 1 ? 's' : ''; ?> pour le niveau <?php echo htmlspecialchars($selected_level); ?>
                <?php else: ?>
                    ℹ️ Aucun exercice trouvé pour ce niveau. Essayez un autre niveau ou créez un compte pour accéder à tous les contenus.
                <?php endif; ?>
            </p>
        </div>

            <div class="demo-stats mt-6 grid gap-3 md:grid-cols-3">
            <div class="stat-item rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="stat-number"><?php echo count($sampleExercises); ?></span>
                <span class="stat-label">Exercice<?php echo count($sampleExercises) > 1 ? 's' : ''; ?> interactif<?php echo count($sampleExercises) > 1 ? 's' : ''; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo !empty($sampleQuiz) ? count($sampleQuiz) : '0'; ?></span>
                <span class="stat-label">Question<?php echo (!empty($sampleQuiz) && count($sampleQuiz) > 1) ? 's' : ''; ?> de quiz</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">1</span>
                <span class="stat-label">Cours complet</span>
            </div>
        </div>
    </section>

    <!-- Section Exercices -->
    <section class="demo-section rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="exercices">
        <h2 class="text-2xl font-semibold text-slate-900">📝 Exercices Interactifs</h2>
        <p class="section-description mt-2 text-sm text-slate-600">Testez nos exercices interactifs avec feedback immédiat et système de récompenses</p>

        <?php if (!empty($sampleExercises)): ?>
            <div class="exercises-grid demo-exercises">
                <?php foreach ($sampleExercises as $index => $exercise): ?>
                    <div class="demo-exercise-wrapper" data-exercise-index="<?php echo $index; ?>">
                        <?php
                        renderExerciseCard($exercise, [
                            'showAnswer' => false, // Toujours false en mode démo
                            'showDetails' => true,
                            'interactive' => true,
                            'demoMode' => true, // Nouvel option pour le mode démo
                            'cardClass' => 'demo-exercise-card',
                        ]);
                    ?>
                        <!-- Message de blocage des réponses -->
                        <div class="demo-answer-lock" id="demo-answer-lock-<?php echo $index; ?>">
                            <div class="lock-content">
                                <span class="lock-icon">🔒</span>
                                <h4>Inscrivez-vous pour voir vos résultats !</h4>
                                <p>Vous avez terminé l'exercice. Créez un compte gratuit pour :</p>
                                <ul>
                                    <li>✅ Voir si vos réponses sont correctes</li>
                                    <li>📊 Obtenir votre score détaillé</li>
                                    <li>🏆 Gagner des points et des badges</li>
                                    <li>📈 Suivre votre progression</li>
                                </ul>
                                <a href="<?php echo site_url('register'); ?>" class="demo-unlock-button">✨ Créer mon compte gratuit</a>
                                <button class="demo-continue-demo" onclick="continueDemo(<?php echo $index; ?>)">Continuer la démo</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="demo-exercises-cta mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
                <p>💡 <strong>Vous avez testé <?php echo count($sampleExercises); ?> exercice<?php echo count($sampleExercises) > 1 ? 's' : ''; ?> interactif<?php echo count($sampleExercises) > 1 ? 's' : ''; ?> du niveau <?php echo htmlspecialchars($selected_level); ?> !</strong></p>
                <p class="mt-2">Créez un compte pour accéder à tous les exercices interactifs, voir vos résultats et sauvegarder votre progression !</p>
                <a href="<?php echo site_url('register'); ?>" class="demo-cta-link large mt-3 inline-flex rounded-full bg-slate-900 px-4 py-2 font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">✨ Créer mon compte gratuit</a>
            </div>
        <?php else: ?>
            <div class="demo-placeholder">
                <p>📚 Des exercices interactifs seront affichés ici</p>
                <p>Créez un compte pour accéder à tous les exercices !</p>
            </div>
        <?php endif; ?>

        <div class="demo-limit-message" id="exercise-limit">
            <p>⚠️ <strong>Limite atteinte</strong></p>
            <p>Vous avez testé 5 exercices en mode démo. Créez un compte pour continuer !</p>
            <a href="<?php echo site_url('register'); ?>" class="demo-cta-link">✨ Créer mon compte gratuit</a>
        </div>
    </section>

    <!-- Section Cours -->
    <section class="demo-section rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="cours">
        <h2 class="text-2xl font-semibold text-slate-900">📚 Cours Complet</h2>
        <p class="section-description mt-2 text-sm text-slate-600">Découvrez un cours complet avec navigation et explications détaillées</p>

        <?php if ($sampleCourse): ?>
        <div class="course-preview">
            <div class="course-header">
                <h3><?php echo htmlspecialchars($sampleCourse['title']); ?></h3>
                <span class="course-subject"><?php echo htmlspecialchars($sampleCourse['subject'] ?? 'Général'); ?> - <?php echo htmlspecialchars($sampleCourse['level'] ?? $selected_level); ?></span>
            </div>
            <div class="course-content">
                <div class="course-introduction">
                    <p><?php echo htmlspecialchars($sampleCourse['introduction'] ?? ''); ?></p>
                </div>

                <?php if (!empty($sampleCourse['objectives'])): ?>
                <div class="course-objectives">
                    <h4>🎯 Objectifs d'apprentissage</h4>
                    <ul>
                        <?php foreach (array_slice($sampleCourse['objectives'], 0, 5) as $objective): ?>
                            <li><?php echo htmlspecialchars($objective); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($sampleCourse['lessons']) && count($sampleCourse['lessons']) > 0): ?>
                <div class="course-lessons">
                    <h4>📖 Contenu du cours</h4>
                    <?php foreach (array_slice($sampleCourse['lessons'], 0, 2) as $lesson): ?>
                        <div class="course-lesson">
                            <h5><?php echo htmlspecialchars($lesson['title'] ?? 'Leçon'); ?></h5>
                            <p><?php echo htmlspecialchars(substr($lesson['content'] ?? '', 0, 300)); ?><?php echo strlen($lesson['content'] ?? '') > 300 ? '...' : ''; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($sampleCourse['summary'])): ?>
                <div class="course-summary">
                    <h4>📝 Résumé</h4>
                    <p><?php echo htmlspecialchars($sampleCourse['summary']); ?></p>
                </div>
                <?php endif; ?>

                <div class="course-actions">
                    <?php if (!empty($sampleCourse['exercise_id'])): ?>
                        <span class="btn-cours disabled-btn" title="Créez un compte pour accéder aux cours complets">📖 Voir le cours complet</span>
                        <p>✨ Créez un compte pour accéder aux <strong>cours complets</strong> !</p>
                    <?php else: ?>
                        <span class="btn-cours disabled-btn" title="Créez un compte pour accéder à tous les cours">📖 Voir tous les cours</span>
                        <p>✨ Créez un compte pour accéder à <strong>tous</strong> les cours de ce niveau !</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="demo-placeholder">
            <p>📚 Un cours complet sera affiché ici</p>
            <p>Créez un compte pour accéder à tous les cours !</p>
        </div>
        <?php endif; ?>

        <div class="demo-cta-box mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
            <p>💡 <strong>Créez un compte</strong> pour accéder à tous les cours adaptés à votre niveau !</p>
            <a href="<?php echo site_url('register'); ?>" class="demo-cta-link mt-3 inline-flex rounded-full bg-slate-900 px-4 py-2 font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">✨ Créer mon compte</a>
        </div>
    </section>

    <!-- Section Quiz -->
    <section class="demo-section rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="quiz">
        <h2 class="text-2xl font-semibold text-slate-900">🎯 Quiz Interactif</h2>
        <p class="section-description mt-2 text-sm text-slate-600">Testez vos connaissances avec un quiz dynamique basé sur les cours et exercices</p>

        <?php if (!empty($sampleQuiz)): ?>
            <div class="quiz-preview demo-quiz-preview">
                <div class="quiz-header">
                    <h3>Quiz <?php echo htmlspecialchars($sampleQuizSubject ?? 'Multi-matières'); ?> - Niveau <?php echo htmlspecialchars($selected_level); ?></h3>
                    <p class="quiz-info"><?php echo count($sampleQuiz); ?> question<?php echo count($sampleQuiz) > 1 ? 's' : ''; ?> pour tester vos connaissances</p>
                </div>
                <div class="quiz-questions">
                    <?php foreach (array_slice($sampleQuiz, 0, 5) as $index => $question): ?>
                        <div class="quiz-question-preview demo-quiz-question" data-question-index="<?php echo $index; ?>">
                            <div class="question-number">Question <?php echo($index + 1); ?> / <?php echo count($sampleQuiz); ?></div>
                            <p class="question-text">
                                <span class="question-subject"><?php echo htmlspecialchars($question['subject'] ?? 'Général'); ?> :</span>
                                <?php echo htmlspecialchars($question['question']); ?>
                            </p>
                            <div class="question-choices">
                                <?php
                            $choices = $question['choices'] ?? [];
                        $correctAnswer = $question['correct'] ?? '';
                        foreach ($choices as $choiceIndex => $choice):
                            $isCorrect = (trim($choice) === trim($correctAnswer));
                            ?>
                                    <label class="choice-label demo-choice">
                                        <input type="radio" name="demo_quiz_<?php echo $index; ?>" value="<?php echo htmlspecialchars($choice); ?>" disabled>
                                        <span class="choice-text"><?php echo htmlspecialchars($choice); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="quiz-cta">
                    <p>🔒 <strong>Créez un compte gratuit</strong> pour :</p>
                    <ul class="quiz-benefits">
                        <li>✅ Répondre à toutes les questions interactivement</li>
                        <li>📊 Obtenir votre score détaillé</li>
                        <li>💡 Voir les explications pour chaque réponse</li>
                        <li>🏆 Gagner des points et des badges</li>
                        <li>📈 Accéder à des centaines d'autres quiz</li>
                    </ul>
                    <a href="<?php echo site_url('register'); ?>" class="demo-cta-link large">✨ Créer mon compte gratuit</a>
                </div>
            </div>
        <?php else: ?>
            <div class="demo-placeholder">
                <p>🎯 Des quiz interactifs seront affichés ici</p>
                <p>Créez un compte pour accéder à tous les quiz !</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Footer de la démo -->
    <div class="demo-footer rounded-3xl border border-slate-200 bg-slate-900 p-8 text-white shadow-sm">
        <h2 class="text-2xl font-semibold">Prêt à commencer votre aventure ?</h2>
        <p class="mt-3 max-w-2xl text-sm text-slate-300">Créez un compte gratuit et accédez à tous les contenus, suivez votre progression et débloquez des récompenses !</p>
        <div class="demo-footer-actions mt-6 flex flex-wrap gap-3">
            <a href="<?php echo site_url('register'); ?>" class="demo-cta-button primary inline-flex items-center rounded-full bg-emerald-600 px-4 py-2 font-semibold text-white transition hover:bg-emerald-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900">✨ Créer mon compte gratuit</a>
            <?php if (empty($is_demo_mode)): // Masquer le bouton de connexion en mode démo?>
            <a href="<?php echo site_url('login'); ?>" class="demo-cta-button secondary inline-flex items-center rounded-full border border-slate-700 px-4 py-2 font-semibold text-slate-100 transition hover:bg-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900">🔑 J'ai déjà un compte</a>
            <?php else: ?>
            <a href="<?php echo site_url('logout'); ?>" class="demo-cta-button secondary inline-flex items-center rounded-full border border-slate-700 px-4 py-2 font-semibold text-slate-100 transition hover:bg-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900">🚪 Quitter le mode démo</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Système de tracking des actions pour la limitation
(function() {
    let actionCount = <?php echo $actionCount; ?>;
    const MAX_ACTIONS = 5;

    // Écouter les événements de complétion d'exercices
    document.addEventListener('exerciseCompleted', function(e) {
        actionCount++;
        updateActionCount(actionCount);

        // Sauvegarder dans la session (via AJAX)
        fetch('<?php echo htmlspecialchars($demoTrackingUrl, ENT_QUOTES, 'UTF-8'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.csrfToken || ''
            },
            body: JSON.stringify({
                action: 'exercise_completed',
                count: actionCount,
                csrf_token: window.csrfToken || ''
            })
        }).catch(err => console.error('Erreur tracking:', err));

        // Afficher le message de limitation si nécessaire
        if (actionCount >= MAX_ACTIONS) {
            showLimitMessage();
            showSignupPopup();
        } else if (actionCount === 3) {
            // Afficher le popup après 3 actions
            showSignupPopup();
        }
    });

    function updateActionCount(count) {
        // Mettre à jour l'affichage si nécessaire
        const limitMsg = document.getElementById('exercise-limit');
        if (limitMsg && count >= MAX_ACTIONS) {
            limitMsg.style.display = 'block';
        }
    }

    function showLimitMessage() {
        const limitMsg = document.getElementById('exercise-limit');
        if (limitMsg) {
            limitMsg.style.display = 'block';
            limitMsg.scrollIntoView({behavior: 'smooth', block: 'center'});
        }
    }

    function showSignupPopup() {
        // Créer un popup modal pour inciter à l'inscription
        const popup = document.createElement('div');
        popup.className = 'demo-signup-popup';
        popup.innerHTML = `
            <div class="popup-content">
                <button class="popup-close" onclick="this.closest('.demo-signup-popup').remove()">×</button>
                <h3>🎉 Excellent travail !</h3>
                <p>Vous avez complété ${actionCount} exercice${actionCount > 1 ? 's' : ''} en mode démo.</p>
                <p><strong>Créez un compte gratuit</strong> pour :</p>
                <ul>
                    <li>✨ Sauvegarder votre progression</li>
                    <li>📚 Accéder à tous les cours et exercices</li>
                    <li>🏆 Débloquer des badges et récompenses</li>
                    <li>📊 Suivre vos statistiques détaillées</li>
                </ul>
                <div class="popup-actions">
                    <a href="<?php echo site_url('register'); ?>" class="popup-cta primary">✨ Créer mon compte</a>
                    <button class="popup-cta secondary" onclick="this.closest('.demo-signup-popup').remove()">Continuer la démo</button>
                </div>
            </div>
        `;
        document.body.appendChild(popup);

        // Fermer après 10 secondes si pas d'action
        setTimeout(() => {
            if (document.body.contains(popup)) {
                popup.style.opacity = '0';
                setTimeout(() => popup.remove(), 300);
            }
        }, 10000);
    }

    // Initialiser le compteur depuis localStorage si disponible
    const storedCount = localStorage.getItem('demo_action_count');
    if (storedCount) {
        actionCount = parseInt(storedCount, 10);
        updateActionCount(actionCount);
    }

    // Sauvegarder dans localStorage
    function saveActionCount(count) {
        localStorage.setItem('demo_action_count', count.toString());
    }

    // Intercepter les événements d'exercices interactifs en mode démo
    document.addEventListener('DOMContentLoaded', function() {
        // Attendre un peu pour que les scripts d'exercices soient chargés
        setTimeout(function() {
            // setupDemoMode(); // DÉSACTIVÉ: Permet de tester les corrections en mode démo
        }, 500);
    });

    function setupDemoMode() {
        // Masquer tous les feedbacks et réponses
        document.querySelectorAll('.demo-exercise-card .qcm-feedback, .demo-exercise-card .math-feedback, .demo-exercise-card .conjugation-feedback').forEach(el => {
            el.style.display = 'none';
        });

        // Intercepter les clics sur les boutons de vérification AVANT que les scripts normaux ne les gèrent
        document.querySelectorAll('.demo-exercise-card .btn-check-qcm, .demo-exercise-card .btn-check-math, .demo-exercise-card .btn-check-conjugation').forEach(btn => {
            // Supprimer les anciens listeners si existants
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            // Ajouter notre propre listener qui bloque l'exécution
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                // Trouver l'index de l'exercice
                const exerciseWrapper = this.closest('.demo-exercise-wrapper');
                if (!exerciseWrapper) return;

                const exerciseIndex = exerciseWrapper.dataset.exerciseIndex;
                if (exerciseIndex !== null && exerciseIndex !== undefined) {
                    showAnswerLock(parseInt(exerciseIndex));

                    // Track l'action
                    setTimeout(() => {
                        const event = new CustomEvent('exerciseCompleted', {detail: {type: 'interactive'}});
                        document.dispatchEvent(event);
                    }, 500);
                }

                return false;
            }, true); // Utiliser capture phase pour intercepter avant les autres listeners
        });
    }

    function showAnswerLock(exerciseIndex) {
        const lockElement = document.getElementById('demo-answer-lock-' + exerciseIndex);
        if (lockElement) {
            lockElement.style.display = 'flex';
            lockElement.scrollIntoView({behavior: 'smooth', block: 'center'});
        }
    }

    // Exposer la fonction globalement pour le bouton "Continuer la démo"
    window.continueDemo = function(exerciseIndex) {
        const lockElement = document.getElementById('demo-answer-lock-' + exerciseIndex);
        if (lockElement) {
            lockElement.style.display = 'none';
        }
    };
})();
</script>

<!-- Colibri désactivé (remplacé par Coach WebM) -->

<!-- Charger le script des exercices interactifs -->
<script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : (isset($baseUrl) ? rtrim($baseUrl, '/') : '') . '/assets/js/interactive-exercises.js'; ?>"></script>

<!-- ancien: colibri-mascot.js -->

<?php
// Charger le script JavaScript des exercices après que tout le contenu soit prêt
if (function_exists('renderExerciseCardScript')) {
    renderExerciseCardScript();
}
?>

<script>
// S'assurer que les exercices interactifs sont initialisés
(function() {
    'use strict';

    let initAttempts = 0;
    const MAX_INIT_ATTEMPTS = 10;

    function initializeExercises() {
        initAttempts++;

        // Attendre que le script soit chargé
        if (window.InteractiveExercises && typeof window.InteractiveExercises.initAll === 'function') {
            console.log('✅ Initialisation des exercices interactifs (tentative ' + initAttempts + ')...');
            try {
                window.InteractiveExercises.initAll();
                console.log('✅ Exercices initialisés avec succès');

                // Vérifier que les conteneurs sont remplis après un délai
                setTimeout(function() {
                    const qcmContainers = document.querySelectorAll('.qcm-container');
                    const mathContainers = document.querySelectorAll('.math-container');
                    const conjugationContainers = document.querySelectorAll('.conjugation-container');

                    console.log('📊 Conteneurs trouvés:', {
                        qcm: qcmContainers.length,
                        math: mathContainers.length,
                        conjugation: conjugationContainers.length
                    });

                    // Vérifier le contenu des conteneurs
                    let hasContent = false;
                    qcmContainers.forEach(c => {
                        if (c.innerHTML.trim()) {
                            hasContent = true;
                            console.log('✅ QCM container rempli:', c.innerHTML.substring(0, 100));
                        }
                    });
                    mathContainers.forEach(c => {
                        if (c.innerHTML.trim()) {
                            hasContent = true;
                            console.log('✅ Math container rempli:', c.innerHTML.substring(0, 100));
                        }
                    });
                    conjugationContainers.forEach(c => {
                        if (c.innerHTML.trim()) {
                            hasContent = true;
                            console.log('✅ Conjugation container rempli:', c.innerHTML.substring(0, 100));
                        }
                    });

                    // Si les conteneurs existent mais sont vides, réessayer
                    if (!hasContent && (qcmContainers.length > 0 || mathContainers.length > 0 || conjugationContainers.length > 0)) {
                        console.warn('⚠️ Conteneurs vides détectés, réinitialisation...');
                        if (initAttempts < MAX_INIT_ATTEMPTS) {
                            setTimeout(initializeExercises, 500);
                        } else {
                            console.error('❌ Impossible d\'initialiser les exercices après ' + MAX_INIT_ATTEMPTS + ' tentatives');
                        }
                    } else if (hasContent) {
                        console.log('✅ Tous les exercices sont correctement initialisés');
                    }
                }, 800);
            } catch (error) {
                console.error('❌ Erreur lors de l\'initialisation des exercices:', error);
                if (initAttempts < MAX_INIT_ATTEMPTS) {
                    setTimeout(initializeExercises, 500);
                }
            }
        } else {
            // Réessayer après un court délai
            if (initAttempts < MAX_INIT_ATTEMPTS) {
                console.log('⏳ Attente du chargement du script interactive-exercises.js (tentative ' + initAttempts + ')...');
                setTimeout(initializeExercises, 300);
            } else {
                console.error('❌ Le script interactive-exercises.js n\'a pas pu être chargé après ' + MAX_INIT_ATTEMPTS + ' tentatives');
            }
        }
    }

    // Initialiser immédiatement si possible
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeExercises, 200);
        });
    } else {
        // DOM déjà chargé, initialiser après un court délai pour laisser le temps au script de se charger
        setTimeout(initializeExercises, 300);
    }
})();

// Surcharger les fonctions d'affichage des réponses pour les exercices en mode démo
(function() {
    'use strict';

    // Attendre que le script interactive-exercises.js soit chargé
    function initDemoBlocking() {
        // Empêcher l'affichage des feedbacks pour tous les exercices démo
        const demoCards = document.querySelectorAll('.demo-exercise-card');

        // Observer pour masquer automatiquement les feedbacks qui apparaissent
        const observer = new MutationObserver(function(mutations) {
            demoCards.forEach(card => {
                const feedbacks = card.querySelectorAll('.qcm-feedback, .math-feedback, .conjugation-feedback');
                feedbacks.forEach(fb => {
                    if (fb.style.display !== 'none') {
                        fb.style.display = 'none';
                    }
                });
            });
        });

        // Observer chaque carte d'exercice démo
        demoCards.forEach(card => {
            observer.observe(card, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class']
            });
        });
    }

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        // document.addEventListener('DOMContentLoaded', initDemoBlocking); // DÉSACTIVÉ: Permet de tester les corrections en mode démo
    } else {
        // Si déjà chargé, attendre un peu pour que interactive-exercises.js soit chargé
        // setTimeout(initDemoBlocking, 1000); // DÉSACTIVÉ: Permet de tester les corrections en mode démo
    }
})();

// Fonction pour changer le niveau de la démo (mise à jour dynamique)
function changeDemoLevel(level) {
    if (!level) return;

    console.log('🔄 Changement de niveau démo vers:', level);

    // Réinitialiser le flag d'erreur
    errorDisplayed = false;

    // Retirer toutes les erreurs existantes
    const existingErrors = document.querySelectorAll('.demo-error-message');
    existingErrors.forEach(err => err.remove());

    // Mettre à jour le bouton actif
    document.querySelectorAll('.level-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.trim() === level) {
            btn.classList.add('active');
        }
    });

    // Afficher un indicateur de chargement
    const sections = ['exercices', 'cours', 'quiz'];
    sections.forEach(sectionId => {
        const section = document.getElementById(sectionId);
        if (section) {
            // Sauvegarder le contenu original pour le restaurer en cas d'erreur
            if (!section.dataset.originalContent) {
                section.dataset.originalContent = section.innerHTML;
            }

            section.style.opacity = '0.5';
            section.style.pointerEvents = 'none';
            section.style.transition = 'opacity 0.3s ease';

            // Ajouter un spinner de chargement
            const existingLoading = section.querySelector('.demo-loading');
            if (existingLoading) {
                existingLoading.remove();
            }

            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'demo-loading';
            loadingDiv.id = 'loading-' + sectionId;
            loadingDiv.innerHTML = '<div class="spinner"></div><p>Chargement...</p>';
            section.appendChild(loadingDiv);
        }
    });

    // Récupérer le baseUrl pour l'API
    const baseUrl = window.baseUrl || '';
    // Utiliser ?page=api/... pour le router - Correction du chemin vers api/demo/get_demo_content
    const apiUrl = baseUrl + '/index.php?page=api/demo/get_demo_content&level=' + encodeURIComponent(level);

    console.log('🔍 DEBUG API Call:', {
        baseUrl: baseUrl,
        apiUrl: apiUrl,
        level: level
    });

    // Faire la requête AJAX
    fetch(apiUrl, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            // Essayer de lire le message d'erreur si disponible
            return response.text().then(text => {
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.error || 'Erreur HTTP ' + response.status);
                } catch (e) {
                    throw new Error('Erreur HTTP ' + response.status + ': ' + text.substring(0, 100));
                }
            });
        }
        // Vérifier que la réponse est bien du JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('❌ Réponse non-JSON reçue:', text.substring(0, 200));
                throw new Error('La réponse du serveur n\'est pas au format JSON');
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Données reçues:', data);

        // Vérifier que data est un objet valide
        if (!data || typeof data !== 'object') {
            console.error('❌ Données invalides reçues:', data);
            showError('Format de données invalide reçu du serveur');
            return;
        }

        if (data.success) {
            try {
                // Mettre à jour les exercices
                updateExercisesSection(data.exercises || [], level);

                // Mettre à jour le cours
                updateCourseSection(data.course, level);

                // Mettre à jour le quiz
                updateQuizSection(data.quiz, level);

                // Mettre à jour les statistiques
                updateStats(data.stats || {}, level);

                // Mettre à jour le message d'info du niveau
                updateLevelInfo(data.stats?.exercises_count || 0, level);
            } catch (updateError) {
                console.error('❌ Erreur lors de la mise à jour:', updateError);
                showError('Erreur lors de la mise à jour de l\'affichage: ' + updateError.message);
            }
        } else {
            console.error('❌ Erreur:', data.error);
            showError(data.error || 'Erreur lors du chargement du contenu');

            // Restaurer le contenu original en cas d'erreur
            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section && section.dataset.originalContent) {
                    section.innerHTML = section.dataset.originalContent;
                }
            });
        }
    })
    .catch(error => {
        console.error('❌ Erreur fetch:', error);
        console.error('❌ Détails de l\'erreur:', error.message, error.stack);

        // Afficher l'erreur seulement une fois
        if (!errorDisplayed) {
            showError('Erreur de connexion: ' + (error.message || 'Veuillez réessayer.'));
        }

        // Restaurer le contenu original en cas d'erreur
        sections.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            if (section && section.dataset.originalContent) {
                section.innerHTML = section.dataset.originalContent;
            }
        });
    })
    .finally(() => {
        // Retirer les indicateurs de chargement
        sections.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            if (section) {
                section.style.opacity = '1';
                section.style.pointerEvents = 'auto';
                section.style.transition = 'opacity 0.3s ease';
                const loadingDiv = document.getElementById('loading-' + sectionId);
                if (loadingDiv) {
                    loadingDiv.remove();
                }
            }
        });
    });
}

// Fonction pour mettre à jour la section des exercices
function updateExercisesSection(exercises, level) {
    const section = document.getElementById('exercices');
    if (!section) return;

    let exercisesGrid = section.querySelector('.exercises-grid, .demo-exercises');
    if (!exercisesGrid) {
        // Créer la grille si elle n'existe pas
        const newGrid = document.createElement('div');
        newGrid.className = 'exercises-grid demo-exercises';
        const firstChild = section.firstElementChild;
        if (firstChild && firstChild.nextSibling) {
            section.insertBefore(newGrid, firstChild.nextSibling);
        } else {
            section.appendChild(newGrid);
        }
        exercisesGrid = newGrid;
    }

    if (exercises.length === 0) {
        exercisesGrid.innerHTML = `
            <div class="demo-placeholder">
                <p>📚 Aucun exercice disponible pour ce niveau</p>
                <p>Créez un compte pour accéder à tous les exercices !</p>
            </div>
        `;

        // Mettre à jour le message CTA
        const ctaDiv = section.querySelector('.demo-exercises-cta');
        if (ctaDiv) {
            ctaDiv.style.display = 'none';
        }
        return;
    }

    // Utiliser le HTML rendu côté serveur si disponible, sinon générer un HTML simple
    let exercisesHTML = '';
    exercises.forEach((exercise, index) => {
        const exerciseIndex = exercise.index !== undefined ? exercise.index : index;

        if (exercise.html) {
            // Utiliser le HTML rendu côté serveur (déjà avec wrapper et lock)
            exercisesHTML += exercise.html;
        } else {
            // Fallback : générer un HTML simple
            exercisesHTML += `
                <div class="demo-exercise-wrapper" data-exercise-index="${exerciseIndex}">
                    <div class="demo-exercise-card exercise-card" data-exercise-id="${exercise.Id}">
                        <div class="exercise-header">
                            <h4>${escapeHtml(exercise.Title)}</h4>
                            <span class="exercise-subject">${escapeHtml(exercise.Subject)}</span>
                        </div>
                        <div class="exercise-content">
                            <p>${escapeHtml((exercise.Content || '').substring(0, 200))}${(exercise.Content || '').length > 200 ? '...' : ''}</p>
                        </div>
                    </div>
                    <div class="demo-answer-lock" id="demo-answer-lock-${exerciseIndex}">
                        <div class="lock-content">
                            <span class="lock-icon">🔒</span>
                            <h4>Inscrivez-vous pour voir vos résultats !</h4>
                            <p>Vous avez terminé l'exercice. Créez un compte gratuit pour :</p>
                            <ul>
                                <li>✅ Voir si vos réponses sont correctes</li>
                                <li>📊 Obtenir votre score détaillé</li>
                                <li>🏆 Gagner des points et des badges</li>
                                <li>📈 Suivre votre progression</li>
                            </ul>
                            <a href="${getRegisterUrl()}" class="demo-unlock-button">✨ Créer mon compte gratuit</a>
                            <button class="demo-continue-demo" onclick="continueDemo(${exerciseIndex})">Continuer la démo</button>
                        </div>
                    </div>
                </div>
            `;
        }
    });

    exercisesGrid.innerHTML = exercisesHTML;

    // Réinitialiser les scripts interactifs pour les nouveaux exercices
    // Attendre un peu pour que le DOM soit mis à jour
    setTimeout(() => {
        // Réinitialiser interactive-exercises.js si disponible
        if (typeof window.initInteractiveExercises === 'function') {
            window.initInteractiveExercises();
        } else if (typeof initInteractiveExercises === 'function') {
            initInteractiveExercises();
        }

        // Réinitialiser les exercices QCM si disponible
        if (typeof window.__MCS_QCM !== 'undefined' && window.__MCS_QCM.initQcm) {
            window.__MCS_QCM.initQcm(exercisesGrid);
        }

        // Réinitialiser les exercices dynamiques si disponible
        if (typeof window.initDynamicExercises === 'function') {
            window.initDynamicExercises();
        }

        // Déclencher un événement personnalisé pour notifier les autres scripts
        const event = new CustomEvent('demoExercisesUpdated', {
            detail: { exercises: exercises, level: level }
        });
        document.dispatchEvent(event);
    }, 200);

    // Mettre à jour le message CTA
    let ctaDiv = section.querySelector('.demo-exercises-cta');
    if (!ctaDiv) {
        ctaDiv = document.createElement('div');
        ctaDiv.className = 'demo-exercises-cta';
        section.appendChild(ctaDiv);
    }
    ctaDiv.style.display = 'block';
    ctaDiv.innerHTML = `
        <p>💡 <strong>Vous avez testé ${exercises.length} exercice${exercises.length > 1 ? 's' : ''} interactif${exercises.length > 1 ? 's' : ''} du niveau ${escapeHtml(level)} !</strong></p>
        <p>Créez un compte pour accéder à tous les exercices interactifs, voir vos résultats et sauvegarder votre progression !</p>
        <a href="${getRegisterUrl()}" class="demo-cta-link large">✨ Créer mon compte gratuit</a>
    `;
}

// Fonction pour mettre à jour la section du cours
function updateCourseSection(course, level) {
    const section = document.getElementById('cours');
    if (!section) return;

    if (!course) {
        section.innerHTML = `
            <h2>📚 Cours Complet</h2>
            <p class="section-description">Découvrez un cours complet avec navigation et explications détaillées</p>
            <div class="demo-placeholder">
                <p>📚 Aucun cours disponible pour ce niveau</p>
                <p>Créez un compte pour accéder à tous les cours !</p>
            </div>
            <div class="demo-cta-box">
                <p>💡 <strong>Créez un compte</strong> pour accéder à tous les cours adaptés à votre niveau !</p>
                <a href="${getRegisterUrl()}" class="demo-cta-link">✨ Créer mon compte</a>
            </div>
        `;
        return;
    }

    const objectivesHTML = course.objectives && course.objectives.length > 0
        ? `<div class="course-objectives">
            <h4>🎯 Objectifs d'apprentissage</h4>
            <ul>
                ${course.objectives.slice(0, 5).map(obj => `<li>${escapeHtml(obj)}</li>`).join('')}
            </ul>
        </div>`
        : '';

    const lessonsHTML = course.lessons && course.lessons.length > 0
        ? `<div class="course-lessons">
            <h4>📖 Contenu du cours</h4>
            ${course.lessons.map(lesson => `
                <div class="course-lesson">
                    <h5>${escapeHtml(lesson.title || 'Leçon')}</h5>
                    <p>${escapeHtml((lesson.content || '').substring(0, 300))}${(lesson.content || '').length > 300 ? '...' : ''}</p>
                </div>
            `).join('')}
        </div>`
        : '';

    const summaryHTML = course.summary
        ? `<div class="course-summary">
            <h4>📝 Résumé</h4>
            <p>${escapeHtml(course.summary)}</p>
        </div>`
        : '';

    // Fonction pour normaliser le niveau pour l'URL
    function normalizeLevelForUrl(level) {
        const mapping = {
            '6ème': '6eme',
            '5ème': '5eme',
            '4ème': '4eme',
            '3ème': '3eme',
            'Seconde': 'seconde',
            'Première': 'premiere',
            'Terminale': 'terminale'
        };
        return mapping[level] || level.toLowerCase().replace('ème', 'eme').replace('è', 'e');
    }

    // Fonction pour normaliser le niveau pour l'URL (définie localement)
    function normalizeLevelForUrl(level) {
        const mapping = {
            '6ème': '6eme',
            '5ème': '5eme',
            '4ème': '4eme',
            '3ème': '3eme',
            'Seconde': 'seconde',
            'Première': 'premiere',
            'Terminale': 'terminale'
        };
        return mapping[level] || level.toLowerCase().replace('ème', 'eme').replace('è', 'e');
    }

    const courseUrl = course.exercise_id
        ? `${getBaseUrl()}/index.php?page=cours-detail&id=${course.exercise_id}&matiere=${encodeURIComponent(course.subject)}`
        : `${getBaseUrl()}/index.php?page=cours&niveau=${encodeURIComponent(normalizeLevelForUrl(level))}`;

    // Pour "Voir tous les cours", afficher un bouton désactivé au lieu d'un lien
    const courseActionButton = `<span class="btn-cours disabled-btn" title="Créez un compte pour accéder aux cours complets">📖 Voir le cours complet</span><p>✨ Créez un compte pour accéder aux <strong>cours complets</strong> !</p>`;
    // Sauvegarder le titre et la description avant de remplacer
    const title = section.querySelector('h2')?.textContent || '📚 Cours Complet';
    const description = section.querySelector('.section-description')?.textContent || 'Découvrez un cours complet avec navigation et explications détaillées';

    section.innerHTML = `
        <h2>${title}</h2>
        <p class="section-description">${description}</p>
        <div class="course-preview">
            <div class="course-header">
                <h3>${escapeHtml(course.title)}</h3>
                <span class="course-subject">${escapeHtml(course.subject || 'Général')} - ${escapeHtml(course.level || level)}</span>
            </div>
            <div class="course-content">
                <div class="course-introduction">
                    <p>${escapeHtml(course.introduction || '')}</p>
                </div>
                ${objectivesHTML}
                ${lessonsHTML}
                ${summaryHTML}
                <div class="course-actions">
                    ${courseActionButton}
                </div>
            </div>
        </div>
        <div class="demo-cta-box">
            <p>💡 <strong>Créez un compte</strong> pour accéder à tous les cours adaptés à votre niveau !</p>
            <a href="${getRegisterUrl()}" class="demo-cta-link">✨ Créer mon compte</a>
        </div>
    `;
}

// Fonction pour mettre à jour la section du quiz
function updateQuizSection(quiz, level) {
    const section = document.getElementById('quiz');
    if (!section) return;

    // Sauvegarder le titre et la description
    const title = section.querySelector('h2')?.textContent || '🎯 Quiz Interactif';
    const description = section.querySelector('.section-description')?.textContent || 'Testez vos connaissances avec un quiz dynamique basé sur les cours et exercices';

    if (!quiz || !quiz.questions || !Array.isArray(quiz.questions) || quiz.questions.length === 0) {
        section.innerHTML = `
            <h2>${title}</h2>
            <p class="section-description">${description}</p>
            <div class="demo-placeholder">
                <p>🎯 Aucun quiz disponible pour ce niveau</p>
                <p>Créez un compte pour accéder à tous les quiz !</p>
            </div>
        `;
        return;
    }

    // Filtrer les questions valides (qui ont au moins une question et des choix)
    const validQuestions = quiz.questions.filter(q => q && (q.question || q.choices));

    if (validQuestions.length === 0) {
        section.innerHTML = `
            <h2>${title}</h2>
            <p class="section-description">${description}</p>
            <div class="demo-placeholder">
                <p>🎯 Aucun quiz valide disponible pour ce niveau</p>
                <p>Créez un compte pour accéder à tous les quiz !</p>
            </div>
        `;
        return;
    }

    const questionsHTML = validQuestions.map((question, index) => {
        // Gérer les deux formats possibles de choices :
        // 1. Tableau associatif { 'A': 'choix1', 'B': 'choix2', ... }
        // 2. Tableau indexé ['choix1', 'choix2', ...]
        let choicesArray = [];
        if (question.choices) {
            if (Array.isArray(question.choices)) {
                // Si c'est un tableau, vérifier s'il contient des objets ou des strings
                choicesArray = question.choices.map(choice => {
                    if (typeof choice === 'string') {
                        return choice;
                    } else if (choice && typeof choice === 'object' && choice.label) {
                        return choice.label;
                    } else if (choice && typeof choice === 'object' && choice.value) {
                        return choice.value;
                    }
                    return String(choice);
                });
            } else if (typeof question.choices === 'object') {
                // Si c'est un objet associatif, convertir en tableau
                choicesArray = Object.values(question.choices);
            }
        }

        const choicesHTML = choicesArray.length > 0
            ? choicesArray.map((choice, choiceIndex) => {
                // S'assurer que choice est une string
                const choiceLabel = typeof choice === 'string'
                    ? choice
                    : (choice && typeof choice === 'object'
                        ? (choice.label || choice.value || String(choice))
                        : String(choice));
                const choiceValue = (choice && typeof choice === 'object' && choice.value) || choiceLabel;
                return `
                    <label class="choice-label demo-choice">
                        <input type="radio" name="demo_quiz_${index}" value="${escapeHtml(choiceValue)}" disabled>
                        <span class="choice-text">${escapeHtml(choiceLabel)}</span>
                    </label>
                `;
            }).join('')
            : '<p>Aucun choix disponible pour cette question</p>';

        return `
            <div class="quiz-question-preview demo-quiz-question" data-question-index="${index}">
                <div class="question-number">Question ${index + 1} / ${validQuestions.length}</div>
                <p class="question-text">
                    <span class="question-subject">${escapeHtml(question.subject || quiz.subject || 'Général')} :</span>
                    ${escapeHtml(question.question || '')}
                </p>
                <div class="question-choices">
                    ${choicesHTML || '<p>Aucun choix disponible</p>'}
                </div>
            </div>
        `;
    }).join('');

    section.innerHTML = `
        <h2>${title}</h2>
        <p class="section-description">${description}</p>
        <div class="quiz-preview demo-quiz-preview">
            <div class="quiz-header">
                <h3>Quiz ${escapeHtml(quiz.subject || 'Multi-matières')} - Niveau ${escapeHtml(level)}</h3>
                <p class="quiz-info">${validQuestions.length} question${validQuestions.length > 1 ? 's' : ''} pour tester vos connaissances</p>
            </div>
            <div class="quiz-questions">
                ${questionsHTML}
            </div>
            <div class="quiz-cta">
                <p>🔒 <strong>Créez un compte gratuit</strong> pour :</p>
                <ul class="quiz-benefits">
                    <li>✅ Répondre à toutes les questions interactivement</li>
                    <li>📊 Obtenir votre score détaillé</li>
                    <li>💡 Voir les explications pour chaque réponse</li>
                    <li>🏆 Gagner des points et des badges</li>
                    <li>📈 Accéder à des centaines d'autres quiz</li>
                </ul>
                <a href="${getRegisterUrl()}" class="demo-cta-link large">✨ Créer mon compte gratuit</a>
            </div>
        </div>
    `;
}

// Fonction pour mettre à jour les statistiques
function updateStats(stats, level) {
    const statsSection = document.querySelector('.demo-stats');
    if (!statsSection) return;

    statsSection.innerHTML = `
        <div class="stat-item">
            <span class="stat-number">${stats.exercises_count || 0}</span>
            <span class="stat-label">Exercice${(stats.exercises_count || 0) > 1 ? 's' : ''} interactif${(stats.exercises_count || 0) > 1 ? 's' : ''}</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">${stats.quiz_count || 0}</span>
            <span class="stat-label">Question${(stats.quiz_count || 0) > 1 ? 's' : ''} de quiz</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">${stats.has_course ? '1' : '0'}</span>
            <span class="stat-label">Cours complet</span>
        </div>
    `;
}

// Fonction pour mettre à jour le message d'info du niveau
function updateLevelInfo(exercisesCount, level) {
    const levelInfo = document.querySelector('.level-info');
    if (!levelInfo) return;

    if (exercisesCount > 0) {
        levelInfo.className = 'level-info';
        levelInfo.innerHTML = `✅ ${exercisesCount} exercice${exercisesCount > 1 ? 's' : ''} disponible${exercisesCount > 1 ? 's' : ''} pour le niveau ${escapeHtml(level)}`;
    } else {
        levelInfo.className = 'level-info level-warning';
        levelInfo.innerHTML = `ℹ️ Aucun exercice trouvé pour ce niveau. Essayez un autre niveau ou créez un compte pour accéder à tous les contenus.`;
    }
}

// Fonction pour afficher une erreur (évite les doublons)
let errorDisplayed = false;
function showError(message) {
    // Éviter d'afficher plusieurs fois la même erreur
    if (errorDisplayed) {
        return;
    }

    // Retirer les erreurs existantes
    const existingErrors = document.querySelectorAll('.demo-error-message');
    existingErrors.forEach(err => err.remove());

    const errorDiv = document.createElement('div');
    errorDiv.className = 'demo-error-message';
    errorDiv.style.cssText = 'background: #fee; border: 2px solid #fcc; padding: 1rem; margin: 1rem 0; border-radius: 8px; color: #c33;';
    errorDiv.textContent = message;

    const levelSelector = document.getElementById('level-selector');
    if (levelSelector) {
        levelSelector.appendChild(errorDiv);
        errorDisplayed = true;
        setTimeout(() => {
            errorDiv.remove();
            errorDisplayed = false;
        }, 5000);
    }
}

// Fonctions utilitaires
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getBaseUrl() {
    return window.baseUrl || '';
}

function getRegisterUrl() {
    const baseUrl = getBaseUrl();
    return baseUrl ? `${baseUrl}/index.php?page=register` : '/index.php?page=register';
}

function startDemoExercise(exerciseId, index) {
    // Cette fonction sera gérée par interactive-exercises.js
    console.log('Démarrage exercice démo:', exerciseId, index);
}

// Initialiser les event listeners pour les boutons de changement de niveau
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Initialisation des event listeners pour les boutons de niveau...');

    // Sélectionner tous les boutons de niveau
    const levelButtons = document.querySelectorAll('.demo-level-button');
    console.log('📊 Boutons de niveau trouvés:', levelButtons.length);

    levelButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const level = this.getAttribute('data-level');
            console.log('🔄 Clic sur le bouton niveau:', level);
            if (typeof changeDemoLevel === 'function') {
                changeDemoLevel(level);
            } else {
                console.error('❌ La fonction changeDemoLevel n\'est pas disponible');
            }
        });
    });

    console.log('✅ Event listeners attachés avec succès');
});
</script>


