<?php
$page_css = 'lycee/premiere/exercices-premiere.css';
$page_class = 'page-exercices-1ere';
?>

<main class="main-content min-h-screen bg-gray-50">
    <?php
?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4 flex items-center justify-center gap-3">
                <span class="text-6xl">⭐</span>
                Exercices Première - Expert Académique
            </h1>
            <p class="text-xl text-gray-600 mb-6">Programme 2025 | Prépare-toi pour le Bac avec confiance !</p>

            <!-- Navigation Buttons -->
            <div class="flex flex-wrap justify-center gap-4">
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo function_exists('site_url') ? site_url('exercices') : '/exercices'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg border-2 border-blue-700">
                    <span class="mr-2">📝</span>
                    Tous les exercices
                </a>
                <?php endif; ?>
                <a href="<?php echo function_exists('site_url') ? site_url('cours', ['niveau' => '1ere']) : '/cours?niveau=1ere'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                    <span class="mr-2">📚</span>
                    Cours 1ère
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
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                <strong class="text-purple-800">🔍 Aperçu des fonctionnalités disponibles :</strong><br>
                <div class="text-purple-700 mt-2">
                    • Exercices interactifs avec corrections automatiques<br>
                    • Conseils méthodologiques adaptés à la Première<br>
                    • Suivi de progression par matière<br>
                    • Préparation aux évaluations
                </div>
            </div>

            <?php
            if (is_file(__DIR__ . '/../../../../includes/exercice_card.php')) {
                require_once __DIR__ . '/../../../../includes/exercice_card.php';
            }
            if (function_exists('renderExercisePreview')) {
                renderExercisePreview('Première', 3);
            }
            echo '<p class="preview-cta">Pour accéder à tous les exercices et fonctionnalités, <a href="' . site_url('register') . '">créez un compte</a> ou <a href="' . site_url('login') . '">connectez-vous</a>.</p>';
            ?>

            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-8 mb-8 border border-purple-200">
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
                    <a href="index.php?page=register" class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors shadow-lg">
                        <span class="mr-2">✨</span>
                        Créer mon compte gratuit
                    </a>
                    <a href="index.php?page=login" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors shadow-lg">
                        <span class="mr-2">🔑</span>
                        Me connecter
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Aperçu limité des exercices -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
            <strong class="text-purple-800">🔍 Aperçu des fonctionnalités disponibles :</strong><br>
            <div class="text-purple-700 mt-2">
                • Exercices interactifs avec corrections automatiques<br>
                • Conseils méthodologiques adaptés à la Première<br>
                • Suivi de progression par matière<br>
                • Préparation aux évaluations
            </div>
        </div>



        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-6">
            <strong class="text-purple-800">👋 Salut  ! C'est ton Coach Scolaire qui te parle !</strong><br>
            <strong class="text-purple-900">🎭 Thème Narratif : La Préparation à l'Excellence</strong><br>
            <span class="text-purple-700">Bienvenue, Expert Académique ! Tu es en Première, une année cruciale où tu prépares le contrôle continu
            et approfondis tes spécialités. Chaque exercice te rapproche de l'excellence et du Bac !
            Tu progresses vers la maîtrise complète ! ⭐✨</span>
        </div>

        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 mb-8 border border-purple-200">
            <h4 class="text-xl font-bold text-purple-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Ton Objectif : Maîtrise des Spécialités et Préparation au Bac
            </h4>
            <p class="text-purple-700 mb-6">
                Consolide tes spécialités, prépare les épreuves de contrôle continu et développe tes compétences
                d'analyse et d'argumentation. Chaque exercice complété renforce tes acquis !
            </p>
        </div>

    <!-- NOUVEAU SYSTÈME DYNAMIQUE D'EXERCICES -->
    <section class="bg-white rounded-xl shadow-lg p-6" id="dynamic-exercises-section">
        <div class="dynamic-exercises-container" data-dynamic-exercises data-level="Première"></div>
    </section>


    <!-- Anciennes sections (cachées, conservées pour référence) -->
    <style>
        #dynamic-exercises-section ~ section,
        #dynamic-exercises-section ~ .coach-message:last-of-type {
            display: none;
        }
    </style>

    <!-- FRANÇAIS (Ancien système - masqué) -->
    <section id="francais">
        <h2>📚 Français - Dissertation et Commentaire</h2>

        <div class="coach-message">
            <strong>💬 Conseil de coach :</strong> En Première, le français devient un art de l'argumentation.
            Dissertation, commentaire, oral : chaque exercice développe ta capacité à analyser et argumenter.
        </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>📚  exercice(s) de Français disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>

            <!-- Fallback : Contenu statique avec exercices interactifs -->
            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Dissertation - Argumentation Structurée</div>
                        <div class="card-meta">
                            <span class="badge medium">MOYEN</span>
                            <span>Français</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Dissertation">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Rédiger une dissertation argumentative structurée sur un thème d'actualité.</p>

                    <div class="tip-box">
                        <h4>💡 Astuce de Coach</h4>
                        <p>Une bonne dissertation comprend : introduction (problématique), développement (arguments + exemples), contre-arguments, réponse et conclusion. Structure claire = réussite !</p>
                    </div>

                    <div class="qcm-exercise" data-questions='[{"question":"Dans une dissertation, la problématique se trouve :","choices":[{"value":"a","label":"a) Dans l\'introduction"},{"value":"b","label":"b) Dans la conclusion"},{"value":"c","label":"c) Dans le développement"}],"correct":"a"},{"question":"Un bon argument doit être :","choices":[{"value":"a","label":"a) Illustré par un exemple concret"},{"value":"b","label":"b) Sans exemple"},{"value":"c","label":"c) Général seulement"}],"correct":"a"}]'>
                        <div class="qcm-container"><!-- Questions QCM via JS --></div>
                        <div>
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </article>

    </section>

    <!-- MATHÉMATIQUES -->
    <section id="maths">
        <h2>🧮 Mathématiques - Dérivées et Fonctions</h2>

        <div class="coach-message">
            <strong>🧮 Salut, Maître des Nombres !</strong> Les mathématiques en Première introduisent les dérivées,
            les limites et l'analyse de fonctions. Chaque concept maîtrisé est un pas vers l'excellence.
        </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>🧮  exercice(s) de Mathématiques disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>

            <!-- Fallback : Contenu statique -->
            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Dérivées - Calcul et Applications</div>
                        <div class="card-meta">
                            <span class="badge medium">MOYEN</span>
                            <span>Mathématiques</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Dérivées">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Calculer des dérivées et étudier les variations de fonctions.</p>

                    <div class="tip-box">
                        <h4>🔍 Rappel Méthodologique</h4>
                        <p>La dérivée de f(x) = x^n est f'(x) = n*x^(n-1). Pour trouver les extremums, résous f'(x) = 0 et étudie le signe de f'.</p>
                    </div>

                    <div class="conjugation-exercise" data-questions='[{"sentence":"Quelle est la dérivée de f(x) = x^2 ? (Écris ta réponse)","answer":"2x"},{"sentence":"Quelle est la dérivée de f(x) = x^3 - 3x + 2 ? (Écris ta réponse)","answer":"3x^2 - 3"},{"sentence":"Quelle est la dérivée de f(x) = 5x^4 ? (Écris ta réponse)","answer":"20x^3"}]'>
                        <div class="conjugation-container"><!-- Champs via JS --></div>
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
        <h2>🔬 Sciences - Physique-Chimie et SVT</h2>

        <div class="coach-message">
            <strong>🔬 Les sciences, c'est comprendre le monde !</strong> En Première, tu approfondis les lois physiques,
            les réactions chimiques et les mécanismes biologiques. Chaque expérience est une découverte.
                    </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>🔬  exercice(s) de Sciences disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>

            <!-- Fallback : Contenu statique -->
            <div class="exercise-card">
                <div class="exercise-header">
                    <h3>Physique - Bilan Énergétique</h3>
                    <span class="difficulty">⭐⭐ MOYEN</span>
                </div>
                <p><strong>Objectif :</strong> Comprendre la conservation de l'énergie dans un système.</p>

                <div class="tip-box">
                    <h4>🔍 Conseil d'Explorateur</h4>
                    <p>Dans un système isolé, l'énergie totale (cinétique + potentielle) est conservée.
                    Écris le bilan énergétique : E_initiale = E_finale.</p>
                    </div>

                <div class="qcm-exercise"
                     data-questions='[
                         {
                             "question": "Dans un système isolé, l'énergie totale :",
                             "choices": [
                                 {"value": "a", "label": "a) Est conservée"},
                                 {"value": "b", "label": "b) Diminue toujours"},
                                 {"value": "c", "label": "c) Augmente toujours"}
                             ],
                             "correct": "a"
                         },
                         {
                             "question": "L'énergie cinétique dépend de :",
                             "choices": [
                                 {"value": "a", "label": "a) La masse et la vitesse"},
                                 {"value": "b", "label": "b) Uniquement la vitesse"},
                                 {"value": "c", "label": "c) Uniquement la masse"}
                             ],
                             "correct": "a"
                         }
                     ]'>
                    <div class="qcm-container">
                        <!-- Les questions QCM seront générées dynamiquement par JavaScript -->
                    </div>
                    <button class="btn-coach btn-check-qcm">Vérifier mes réponses</button>
                    <div class="qcm-feedback"></div>
                    </div>
                </div>

    </section>

    <!-- HISTOIRE-GÉO -->
    <section id="histoire-geo">
        <h2>🏛️ Histoire-Géographie - Analyse de Documents</h2>

        <div class="coach-message">
            <strong>⏰ L'histoire, c'est comprendre le présent !</strong> En Première, tu analyses des documents,
            tu construis des synthèses argumentées et tu développes ton esprit critique.
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Analyse de Documents - Synthèse Argumentée</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Analyser plusieurs documents et rédiger une synthèse répondant à une question précise.</p>

            <div class="tip-box">
                <h4>📅 Méthode</h4>
                <p>1) Lire attentivement chaque document. 2) Identifier les idées principales.
                3) Mettre en relation les documents. 4) Construire une synthèse structurée.</p>
            </div>

            <p><strong>Exercice :</strong> À partir de 2 documents sur un thème historique, rédige une synthèse de 10 lignes
            qui répond à la question : "Comment ce phénomène a-t-il marqué son époque ?"</p>
            <p><em>💡 Conseil : Structure claire, citations des documents, mise en relation des idées.</em></p>
        </div>
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
                    console.log('🎯 Initialisation du système d\'exercices dynamique pour Première...');
                    const baseUrl = window.baseUrl || '';
                    const apiEndpoint = baseUrl ? baseUrl + '/api/get_exercises.php' : '/api/get_exercises.php';
                    console.log('📡 API Endpoint:', apiEndpoint);

                    try {
                        // Passer le sélecteur (string) au lieu de l'élément directement
                        window.dynamicExerciseSystem = new DynamicExerciseSystem({
                            containerSelector: '[data-dynamic-exercises]', // Sélecteur string
                            level: 'Première',
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

