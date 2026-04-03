<?php
$page_css = 'lycee/seconde/exercices-seconde.css';
$page_class = 'page-exercices-2nde';
?>

<main class="main-content min-h-screen bg-gray-50">
    <?php
?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4 flex items-center justify-center gap-3">
                <span class="text-6xl">🌌</span>
                Exercices Seconde - Maître en Formation
            </h1>
            <p class="text-xl text-gray-600 mb-6">Programme 2025 | Explore les nouveaux horizons du savoir !</p>

            <!-- Navigation Buttons -->
            <div class="flex flex-wrap justify-center gap-4">
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo function_exists('site_url') ? site_url('exercices') : '/exercices'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg border-2 border-blue-700">
                    <span class="mr-2">📝</span>
                    Tous les exercices
                </a>
                <?php endif; ?>
                <a href="<?php echo function_exists('site_url') ? site_url('cours', ['niveau' => '2nde']) : '/cours?niveau=2nde'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                    <span class="mr-2">📚</span>
                    Cours 2nde
                </a>
                <a href="<?php echo function_exists('site_url') ? site_url('eleve/lycee/lycee-accueil') : '/eleve/lycee/lycee-accueil'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors shadow-lg">
                    <span class="mr-2">🏠</span>
                    Accueil Lycée
                </a>
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo function_exists('site_url') ? site_url('eleve/dashboard') : '/eleve/dashboard'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition-colors shadow-lg">
                    <span class="mr-2">📊</span>
                    Mon Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>

<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']); ?>


        <!-- Pour les visiteurs : aperçu exercices PUIS panneau coach -->
        <?php if (empty($is_logged_in)): ?>
            <!-- Aperçu exercices AVANT le panneau coach -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
                <strong class="text-emerald-800">🔍 Aperçu des fonctionnalités disponibles :</strong><br>
                <div class="text-emerald-700 mt-2">
                    • Exercices interactifs avec corrections automatiques<br>
                    • Conseils méthodologiques adaptés à la Seconde<br>
                    • Suivi de progression par matière<br>
                    • Préparation aux évaluations
                </div>
            </div>

            <?php
            if (is_file(__DIR__ . '/../../../../includes/exercice_card.php')) {
                require_once __DIR__ . '/../../../../includes/exercice_card.php';
            }
            if (function_exists('renderExercisePreview')) {
                renderExercisePreview('Seconde', 3);
            }
            echo '<p class="preview-cta">Pour accéder à tous les exercices et fonctionnalités, <a href="' . site_url('register') . '">créez un compte</a> ou <a href="' . site_url('login') . '">connectez-vous</a>.</p>';
            ?>

            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-8 mb-8 border border-emerald-200">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                    <span class="text-3xl">🔒</span>
                    Débloque ton Coach Scolaire Personnalisé
                </h2>
                <p class="text-gray-700 mb-6">
                    Tu vois ici un aperçu des exercices disponibles, mais pour accéder à ton coach personnel,
                    à ses conseils adaptés à ton niveau, et à l'accompagnement complet, tu dois créer un compte gratuit !
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            <span>👨‍🏫</span>
                            Coach Personnel
                        </h3>
                        <p class="text-sm text-gray-600">Messages motivants et conseils adaptés à TON niveau et TES besoins spécifiques</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            <span>📊</span>
                            Suivi Personnalisé
                        </h3>
                        <p class="text-sm text-gray-600">Dashboard avec tes progrès, statistiques, et recommandations sur mesure</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            <span>🎯</span>
                            Exercices Adaptés
                        </h3>
                        <p class="text-sm text-gray-600">Contenu qui s'ajuste à tes forces et faiblesses pour maximiser tes progrès</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm md:col-span-2 lg:col-span-1">
                        <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            <span>🏆</span>
                            Récompenses & Badges
                        </h3>
                        <p class="text-sm text-gray-600">Système de gamification pour te motiver et célébrer tes victoires</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo function_exists('site_url') ? site_url('register') : 'index.php?page=register'; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-emerald-600 text-white font-semibold rounded-lg hover:bg-emerald-700 transition-colors shadow-lg">
                        <span class="mr-2">✨</span>
                        Créer mon compte gratuit
                    </a>
                    <a href="<?php echo function_exists('site_url') ? site_url('login') : 'index.php?page=login'; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors shadow-lg">
                        <span class="mr-2">🔑</span>
                        Me connecter
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Aperçu limité des exercices -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-6">
            <strong class="text-emerald-800">🔍 Aperçu des fonctionnalités disponibles :</strong><br>
            <div class="text-emerald-700 mt-2">
                • Exercices interactifs avec corrections automatiques<br>
                • Conseils méthodologiques adaptés à la Seconde<br>
                • Suivi de progression par matière<br>
                • Préparation aux évaluations
            </div>
        </div>



        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-6 mb-6">
            <strong class="text-emerald-800">👋 Salut  ! C'est ton Coach Scolaire qui te parle !</strong><br>
            <strong class="text-emerald-900">🎭 Thème Narratif : L'Exploration des Horizons du Savoir</strong><br>
            <span class="text-emerald-700">Bienvenue, jeune Maître en Formation ! Tu entres dans une nouvelle dimension d'apprentissage.
            Le lycée t'ouvre les portes de la connaissance approfondie, où chaque matière révèle ses secrets.
            Tu progresses vers l'excellence académique ! 🌌✨</span>
        </div>

        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-6 mb-8 border border-emerald-200">
            <h4 class="text-xl font-bold text-emerald-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Ton Objectif : Maîtrise des Fondamentaux Lycéens
            </h4>
            <p class="text-emerald-700 mb-6">
                Consolide tes bases et développe tes compétences analytiques pour réussir ta transition vers le lycée.
                Chaque exercice complété renforce tes acquis et te prépare aux défis à venir !
            </p>
        </div>

    <!-- NOUVEAU SYSTÈME DYNAMIQUE D'EXERCICES -->
    <section class="bg-white rounded-xl shadow-lg p-6" id="dynamic-exercises-section">
        <div class="dynamic-exercises-container" data-dynamic-exercises data-level="Seconde"></div>
    </section>


    <!-- Anciennes sections (cachées, conservées pour référence) -->
    <style>
        #dynamic-exercises-section ~ section,
        #dynamic-exercises-section ~ .coach-message:last-of-type {
            display: none;
        }
    </style>

    <!-- MATHÉMATIQUES (Ancien système - masqué) -->
    <section id="maths">
        <h2>🧮 Mathématiques - Algèbre et Géométrie</h2>

        <div class="coach-message">
            <strong>🧮 Salut, Explorateur des Nombres !</strong> Les mathématiques au lycée ouvrent de nouveaux horizons :
            fonctions, algèbre avancée, géométrie analytique. Chaque concept maîtrisé est un pas vers l'excellence.
        </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>🧮  exercice(s) de Mathématiques disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>

            <!-- Fallback : Contenu statique avec exercices interactifs -->
            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Calcul - Simplification de Fractions</div>
                        <div class="card-meta">
                            <span class="badge easy">FACILE</span>
                            <span>Mathématiques</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Simplification de Fractions">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Simplifier des fractions en utilisant le PGCD.</p>

                    <div class="tip-box">
                        <h4>💡 Astuce de Coach</h4>
                        <p>Pour simplifier une fraction, trouve le PGCD du numérateur et du dénominateur, puis divise les deux par ce nombre. Vérifie toujours en multipliant le résultat !</p>
                    </div>

                    <div class="math-exercise" data-questions='[{"question":"Simplifier la fraction 56/98","answer":"4/7"},{"question":"Simplifier la fraction 45/60","answer":"3/4"},{"question":"Simplifier la fraction 84/126","answer":"2/3"}]'>
                        <div class="exercise-content">
                            <p><strong>Simplifie ces fractions :</strong></p>
                            <div class="math-container"><!-- Champs dynamiques via JS --></div>
                        </div>
                        <div>
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </article>

            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Géométrie - Théorème de Pythagore</div>
                        <div class="card-meta">
                            <span class="badge medium">MOYEN</span>
                            <span>Mathématiques</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Théorème de Pythagore">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Appliquer le théorème de Pythagore dans des triangles rectangles.</p>

                    <div class="tip-box">
                        <h4>🔍 Rappel Méthodologique</h4>
                        <p>Dans un triangle rectangle : a² + b² = c² où c est l'hypoténuse. Vérifie toujours que le triangle est bien rectangle avant d'appliquer le théorème !</p>
                    </div>

                    <div class="math-exercise" data-questions='[{"question":"Dans un triangle rectangle, si a=5 et b=12, quelle est l&#39;hypoténuse c ?","answer":"13"},{"question":"Si l&#39;hypoténuse c=10 et un côté a=6, quelle est la longueur de l&#39;autre côté b ?","answer":"8"},{"question":"Un triangle a les côtés 9, 12 et 15. Est-ce un triangle rectangle ? (Répondre: Oui ou Non)","answer":"Oui"}]'>
                        <div class="exercise-content">
                            <p><strong>Résous ces problèmes de géométrie :</strong></p>
                            <div class="math-container"><!-- Champs dynamiques via JS --></div>
                        </div>
                        <div>
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </article>

    </section>

    <!-- FRANÇAIS -->
    <section id="francais">
        <h2>📚 Français - Expression et Analyse</h2>

        <div class="coach-message">
            <strong>💬 Conseil de coach :</strong> Le français au lycée, c'est la maîtrise de l'argumentation et de l'analyse.
            Chaque texte étudié enrichit ta compréhension du monde. Pratique régulièrement !
        </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>📚  exercice(s) de Français disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>

            <!-- Fallback : Contenu statique -->
            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Analyse de Texte - Identification de la Thèse</div>
                        <div class="card-meta">
                            <span class="badge easy">FACILE</span>
                            <span>Français</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Identification de la Thèse">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Identifier la thèse principale d'un texte argumentatif et repérer les arguments.</p>

                    <div class="tip-box">
                        <h4>💡 Astuce de Coach</h4>
                        <p>La thèse est l'idée principale que l'auteur défend. Les arguments sont les preuves ou raisons qui soutiennent cette thèse. Cherche les mots-clés comme "donc", "par conséquent", "car", etc.</p>
                    </div>

                    <div class="qcm-exercise" data-questions='[{"question":"Dans un texte argumentatif, la thèse est :","choices":[{"value":"a","label":"a) L\'idée principale que l\'auteur défend"},{"value":"b","label":"b) Le résumé du texte"},{"value":"c","label":"c) L\'introduction uniquement"}],"correct":"a"},{"question":"Les arguments servent à :","choices":[{"value":"a","label":"a) Illustrer le texte"},{"value":"b","label":"b) Soutenir et justifier la thèse"},{"value":"c","label":"c) Décorer le texte"}],"correct":"b"}]'>
                        <div class="qcm-container"><!-- Questions QCM via JS --></div>
                        <div>
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </article>

    </section>

    <!-- SCIENCES -->
    <section id="sciences">
        <h2>🔬 Sciences - SVT et Physique-Chimie</h2>

        <div class="coach-message">
            <strong>🔬 Les sciences, c'est l'aventure de la découverte !</strong> Comprendre le monde qui t'entoure,
            des cellules aux lois physiques. Chaque expérience est une nouvelle révélation.
        </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>🔬  exercice(s) de Sciences disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>

            <!-- Fallback : Contenu statique -->
            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">SVT - Les Cellules Eucaryotes</div>
                        <div class="card-meta">
                            <span class="badge easy">FACILE</span>
                            <span>SVT</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Les Cellules Eucaryotes">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Connaître les organites cellulaires et leurs fonctions.</p>

                    <div class="tip-box">
                        <h4>🔍 Conseil d'Explorateur</h4>
                        <p>Chaque organite a une fonction spécifique dans la cellule. Le noyau contient l'information génétique, les mitochondries produisent l'énergie, les ribosomes synthétisent les protéines.</p>
                    </div>

                    <div class="qcm-exercise" data-questions='[{"question":"Quel organite contient l\'ADN ?","choices":[{"value":"a","label":"a) Mitochondrie"},{"value":"b","label":"b) Noyau"},{"value":"c","label":"c) Ribosome"}],"correct":"b"},{"question":"Quel organite produit l\'énergie cellulaire ?","choices":[{"value":"a","label":"a) Noyau"},{"value":"b","label":"b) Mitochondrie"},{"value":"c","label":"c) Ribosome"}],"correct":"b"}]'>
                        <div class="qcm-container"><!-- Questions QCM via JS --></div>
                        <div>
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </article>

        </section>


    <!-- CSS pour le système dynamique -->
    <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/dynamic-exercises.css') : 'assets/css/pages/dynamic-exercises.css'; ?>">

    <!-- Colibri désactivé (remplacé par Coach WebM) -->
    <!-- ancien: colibri-mascot.css + colibri-mascot.js -->

    <!-- Script pour les exercices interactifs (charger AVANT le système dynamique) -->
    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : 'assets/js/interactive-exercises.js'; ?>"></script>

    <!-- JavaScript pour le système dynamique -->
    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/dynamic-exercises.js') : 'assets/js/dynamic-exercises.js'; ?>"></script>

    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/exercises.js') : 'assets/js/exercises.js'; ?>"></script>

    <script>
        // S'assurer que baseUrl est défini (ne pas écraser s'il existe déjà depuis index.php)
        if (typeof window.baseUrl === 'undefined') {
            window.baseUrl = '';
        }
        console.log('🔧 baseUrl détecté:', window.baseUrl);

        // Initialiser le système dynamique après chargement
        function initDynamicExercises() {
            // Attendre que DynamicExerciseSystem soit disponible
            if (typeof DynamicExerciseSystem !== 'undefined') {
                const container = document.querySelector('[data-dynamic-exercises]');
                if (container) {
                    console.log('🎯 Initialisation du système d\'exercices dynamique pour Seconde...');
                    const baseUrl = window.baseUrl || '';
                    const apiEndpoint = baseUrl ? baseUrl + '/api/get_exercises.php' : '/api/get_exercises.php';
                    console.log('📡 API Endpoint:', apiEndpoint);

                    try {
                        // Passer le sélecteur (string) au lieu de l'élément directement
                        window.dynamicExerciseSystem = new DynamicExerciseSystem({
                            containerSelector: '[data-dynamic-exercises]', // Sélecteur string
                            level: 'Seconde',
                            apiEndpoint: apiEndpoint
                        });
                        console.log('✅ Système initialisé avec succès');
                    } catch (error) {
                        console.error('❌ Erreur:', error);
                        container.innerHTML = '<div class="exercise-error">Erreur: ' + error.message + '</div>';
                    }
                } else {
                    console.warn('⚠️ Container non trouvé');
                }
            } else {
                // Réessayer après un court délai
                console.log('⏳ Attente de DynamicExerciseSystem...');
                setTimeout(initDynamicExercises, 200);
            }
        }

        // Initialiser quand le DOM est prêt
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initDynamicExercises, 300);
            });
        } else {
            setTimeout(initDynamicExercises, 300);
        }
    </script>



    <!-- Coach WebM -->
    <script>
      window.baseUrl = '';
    </script>
    <script src=""></script>
    <style>
      .coach-overlay {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 250px;
        height: auto;
        z-index: 9999;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.5s ease-out;
      }
      .coach-overlay.active {
        opacity: 1;
        animation: slideInUp 0.6s ease-out;
      }
      .coach-overlay video {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        background: transparent;
      }
      @keyframes slideInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
      }
      @media (max-width: 480px) {
        .coach-overlay { width: 180px; bottom: 10px; right: 10px; }
      }
            /* Désactiver toute ancienne mascotte Colibri si présente */
            .colibri-mascot-container,
            .colibri-mascot-global,
            [data-colibri],
            [data-colibri-global] { display: none !important; }
    </style>
</div>
</main>

