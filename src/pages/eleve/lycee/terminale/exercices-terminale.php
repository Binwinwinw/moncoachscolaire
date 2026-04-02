<?php
$page_css = 'lycee/terminale/exercices-terminale.css';
$page_class = 'page-exercices-terminale';
?>

<main class="main-content min-h-screen bg-gray-50">
    <?php
?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4 flex items-center justify-center gap-3">
                <span class="text-6xl">👑</span>
                Exercices Terminale - Maître du Savoir
            </h1>
            <p class="text-xl text-gray-600 mb-6">Programme 2025 | Dernière ligne droite vers le Bac !</p>

            <!-- Navigation Buttons -->
            <div class="flex flex-wrap justify-center gap-4">
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo function_exists('site_url') ? site_url('exercices') : '/exercices'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg border-2 border-blue-700">
                    <span class="mr-2">📝</span>
                    Tous les exercices
                </a>
                <?php endif; ?>
                <a href="<?php echo function_exists('site_url') ? site_url('cours', ['niveau' => 'terminale']) : '/cours?niveau=terminale'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                    <span class="mr-2">📚</span>
                    Cours Terminale
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

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
?>


        <!-- Pour les visiteurs : aperçu exercices PUIS panneau coach -->
        <?php if (empty($is_logged_in)): ?>
            <!-- Aperçu exercices AVANT le panneau coach -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <strong class="text-yellow-800">🔍 Aperçu des fonctionnalités disponibles :</strong><br>
                <div class="text-yellow-700 mt-2">
                    • Exercices interactifs avec corrections automatiques<br>
                    • Conseils méthodologiques adaptés à la Terminale<br>
                    • Suivi de progression par matière<br>
                    • Préparation aux évaluations
                </div>
            </div>

            <?php
            if (is_file(__DIR__ . '/../../../../includes/exercice_card.php')) {
                require_once __DIR__ . '/../../../../includes/exercice_card.php';
            }
            if (function_exists('renderExercisePreview')) {
                renderExercisePreview('Terminale', 3);
            }
            echo '<p class="preview-cta">Pour accéder à tous les exercices et fonctionnalités, <a href="' . (function_exists('site_url') ? site_url('register') : '/public/pages/register.php') . '">créez un compte</a> ou <a href="' . (function_exists('site_url') ? site_url('login') : '/public/pages/login.php') . '">connectez-vous</a>.</p>';
            ?>

            <div class="bg-gradient-to-r from-yellow-50 to-red-50 rounded-xl p-8 mb-8 border border-yellow-200">
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
                    <a href="<?php echo function_exists('site_url') ? site_url('register') : '/public/pages/register.php'; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition-colors shadow-lg">
                        <span class="mr-2">✨</span>
                        Créer mon compte gratuit
                    </a>
                    <a href="<?php echo function_exists('site_url') ? site_url('login') : '/public/pages/login.php'; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors shadow-lg">
                        <span class="mr-2">🔑</span>
                        Me connecter
                    </a>
                </div>
            </div>
        <?php endif; ?>



        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <strong class="text-yellow-800">👋 Salut  ! C'est ton Coach Scolaire qui te parle !</strong><br>
            <strong class="text-yellow-900">🎭 Thème Narratif : La Conquête du Bac</strong><br>
            <span class="text-yellow-700">Bienvenue, Maître du Savoir ! Tu es en Terminale, l'année de la consécration. Tous tes efforts passés
            t'ont préparé pour cette dernière étape. Chaque exercice te rapproche du Bac et de la réussite !
            Tu es prêt(e) à conquérir ton diplôme ! 👑✨</span>
        </div>

        <div class="bg-gradient-to-r from-yellow-50 to-red-50 rounded-xl p-6 mb-8 border border-yellow-200">
            <h4 class="text-xl font-bold text-yellow-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Ton Objectif : Réussir le Bac avec Excellence
            </h4>
            <p class="text-yellow-700 mb-6">
                Annales, sujets types, révisions ciblées : chaque exercice complété renforce ta préparation.
                Le Bac approche, mais tu es prêt(e) à le réussir haut la main !
            </p>
        </div>

        <!-- NOUVEAU SYSTÈME DYNAMIQUE D'EXERCICES -->
        <section class="bg-white rounded-xl shadow-lg p-6" id="dynamic-exercises-section">
            <div class="dynamic-exercises-container" data-dynamic-exercises data-level="Terminale"></div>
        </section>


        <!-- Anciennes sections (cachées, conservées pour référence) -->
        <style>
            #dynamic-exercises-section ~ section,
            #dynamic-exercises-section ~ .coach-message:last-of-type {
                display: none;
            }
        </style>

        <!-- MATHÉMATIQUES (Ancien système - masqué) -->
        <section id="maths" style="display: none;">
            <h2>🧮 Mathématiques - Annales Bac</h2>

            <div class="coach-message">
                <strong>🧮 Salut, Champion des Mathématiques !</strong> En Terminale, tu maîtrises l'analyse,
                les intégrales, les probabilités. Les annales te préparent aux épreuves du Bac. À toi de jouer !
            </div>


                <!-- Afficher les exercices depuis la DB -->
                <p><em>🧮  exercice(s) de Mathématiques disponible(s)</em></p>
                <div class="exercise-subject-group">
                    <div class="exercise-grid">

                    </div>
                </div>

            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Annales - Étude de Fonctions</div>
                        <div class="card-meta">
                            <span class="badge hard">DIFFICILE</span>
                            <span>Mathématiques</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Annales - Étude de Fonctions">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Maîtriser l'étude complète de fonctions pour le Bac.</p>

                    <div class="tip-box">
                        <h4>🔍 Méthode pour le Bac</h4>
                        <p>Pour étudier une fonction : 1) Domaine de définition, 2) Dérivée et variations,
                        3) Limites aux bornes, 4) Tableau de variations, 5) Courbe représentative.</p>
                    </div>

                    <div class="conjugation-exercise"
                         data-questions='[
                             {"sentence": "Si f(x) = x^3 - 3x + 2, quelle est f'(x) ? (Écris ta réponse)", "answer": "3x^2 - 3"},
                             {"sentence": "Pour quelle(s) valeur(s) de x a-t-on f'(x) = 0 ? (Répondre avec x=...)", "answer": "x=1 ou x=-1"},
                             {"sentence": "Quelle est la limite de f(x) quand x tend vers +∞ ?", "answer": "+∞"}
                         ]'>
                        <div class="conjugation-container"><!-- Champs dynamiques via JS --></div>
                        <div style="display:flex;gap:.5rem;margin-top:.5rem;">
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                        <div class="conjugation-feedback"></div>
                    </div>
                </div>
            </article>

            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Intégrales - Approfondissement</div>
                        <div class="card-meta">
                            <span class="badge hard">DIFFICILE</span>
                            <span>Mathématiques</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Intégrales - Approfondissement">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Maîtriser le calcul d'intégrales et leurs applications aux aires et volumes.</p>

                    <div class="tip-box">
                        <h4>💡 Astuce de Coach</h4>
                        <p>Révise les primitives, les substitutions et les méthodes d'intégration par parties. Vérifie les bornes et la cohérence des unités lors des applications.</p>
                    </div>

                    <div class="math-exercise" data-questions='[{"question":"Calcule ∫_0^1 x^2 dx","answer":"1/3"}]'>
                        <div class="exercise-content"><div class="math-container"><!-- Champs dynamiques via JS --></div></div>
                        <div style="display:flex;gap:.5rem;margin-top:.5rem;">
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </article>

        </section>

        <!-- PHILOSOPHIE / FRANÇAIS -->
        <section id="philo">
            <h2>📖 Philosophie - Dissertation et Explication de Texte</h2>

            <div class="coach-message">
                <strong>🤔 La philosophie, c'est penser par soi-même !</strong> En Terminale, tu développes
                ta réflexion philosophique à travers la dissertation et l'explication de texte. Chaque sujet est un défi à relever.
            </div>


                <!-- Afficher les exercices depuis la DB -->
                <p><em>📖  exercice(s) disponible(s)</em></p>
                <div class="exercise-subject-group">
                    <div class="exercise-grid">

                    </div>
                </div>

            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Dissertation Philosophique - Sujet Type Bac</div>
                        <div class="card-meta">
                            <span class="badge hard">DIFFICILE</span>
                            <span>Philosophie</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Dissertation Philosophique">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Rédiger une dissertation philosophique structurée sur un sujet d'actualité.</p>

                    <div class="tip-box">
                        <h4>💡 Méthode de la Dissertation</h4>
                        <p>1) Analyser le sujet et problématiser, 2) Construire un plan dialectique (thèse/antithèse/synthèse),
                        3) Développer chaque partie avec arguments et exemples, 4) Conclusion qui ouvre sur de nouveaux questionnements.</p>
                    </div>

                    <div class="qcm-exercise" data-questions='[]'>
                        <div class="qcm-container"><!-- Contenu additionnel à venir --></div>
                        <div style="display:flex;gap:.5rem;margin-top:.5rem;">
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </article>

        </section>

        <!-- SCIENCES (SVT / Physique-Chimie) -->
        <section id="sciences">
            <h2>🔬 Sciences - SVT & Physique-Chimie</h2>

            <div class="coach-message">
                <strong>🔬 En sciences, l'observation et la rigueur sont tes meilleurs alliés.</strong>
                Tu travailles sur les phénomènes naturels, les réactions chimiques et les modèles
                scientifiques. Chaque exercice te prépare aux épreuves du Bac. Sois rigoureux(se) et précis(e) !
            </div>


                <!-- Afficher les exercices depuis la DB -->
                <p><em>🔬  exercice(s) de Sciences disponible(s)</em></p>
                <div class="exercise-subject-group">
                    <div class="exercise-grid">

                    </div>
                </div>

            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">Annales - Bilan Énergétique et Réactions</div>
                        <div class="card-meta">
                            <span class="badge hard">DIFFICILE</span>
                            <span>Sciences</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button class="card-toggle" aria-expanded="false" aria-controls="card-content-<?php echo $uid; ?>" aria-label="Afficher le contenu de l'exercice: Bilan Énergétique et Réactions">Voir</button>
                        <button class="btn-exercise" data-action="start-exercise" data-id="">Commencer</button>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="true">
                    <p><strong>Objectif :</strong> Maîtriser les bilans énergétiques et les équations de réactions pour le Bac.</p>

                    <div class="tip-box">
                        <h4>🔍 Méthode pour le Bac</h4>
                        <p>Pour un bilan énergétique : 1) Définir le système, 2) Écrire la conservation de l'énergie,
                        3) Calculer les énergies initiale et finale, 4) Appliquer le principe de conservation.</p>
                    </div>

                    <div class="qcm-exercise" data-questions='[{"question":"Un bilan énergétique permet de :","choices":[{"value":"a","label":"a) Vérifier la conservation de l'énergie"},{"value":"b","label":"b) Mesurer la température seulement"},{"value":"c","label":"c) Compter les atomes"}],"correct":"a"}]'>
                        <div class="qcm-container"><!-- Les questions QCM seront générées dynamiquement par JavaScript --></div>
                        <div style="display:flex;gap:.5rem;margin-top:.5rem;">
                            <button class="btn-outline" data-action="verify-exercise">Vérifier mes réponses</button>
                            <a href="#" class="btn-outline">Ouvrir</a>
                        </div>
                    </div>
                </div>
            </article>

        </section>

        <!-- CONSEILS PRATIQUES -->
        <section id="conseils">
            <h2>💡 Conseils Pratiques pour le Bac</h2>

            <?php $uid = 'fx-' . uniqid(); ?>
            <article class="exercise-card" id="<?php echo $uid; ?>">
                <div class="card-head">
                    <div>
                        <div class="card-title" id="card-title-<?php echo $uid; ?>">🎯 Plan de Révision sur 6 Semaines</div>
                        <div class="card-meta">
                            <span class="badge info">CONSEIL</span>
                            <span>Organisation</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a class="btn-outline" href="#">Voir le guide</a>
                    </div>
                </div>
                <div class="card-content" id="card-content-<?php echo $uid; ?>" role="region" aria-labelledby="card-title-<?php echo $uid; ?>" aria-hidden="false">
                    <div class="tip-box">
                        <h4>📅 Organisation Temporelle</h4>
                        <p><strong>Semaines 1-2 :</strong> Révisions actives (fiches + exercices ciblés)<br>
                        <strong>Semaines 3-4 :</strong> Annales chronométrées avec correction complète<br>
                        <strong>Semaines 5-6 :</strong> Simulations d'épreuves complètes, révision des points faibles</p>
                    </div>

                    <h4>✅ Bonnes Pratiques en Examen</h4>
                    <ul>
                        <li>Faire un brouillon soigné et structuré</li>
                        <li>Respecter le barème : assurer les points faciles avant les parties longues</li>
                        <li>Gérer son temps : répartir le temps selon les coefficients</li>
                        <li>Relire attentivement sa copie avant de rendre</li>
                        <li>Simuler les conditions (temps limité) au moins 2 fois par semaine</li>
                    </ul>
                </div>
            </article>
        </section>


    <!-- Colibri désactivé (remplacé par Coach WebM) -->
    <!-- ancien: colibri-mascot.css -->

    <!-- CSS pour le système dynamique -->
    <?php if (function_exists('asset_url')): ?>
        <link rel="stylesheet" href="<?php echo asset_url('assets/css/pages/dynamic-exercises.css'); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="/assets/css/pages/dynamic-exercises.css">
    <?php endif; ?>

    <!-- Script pour les exercices interactifs (charger AVANT le système dynamique) -->
    <?php if (function_exists('asset_url')): ?>
        <script src="<?php echo asset_url('assets/js/interactive-exercises.js'); ?>"></script>
    <?php else: ?>
        <script src="/assets/js/interactive-exercises.js"></script>
    <?php endif; ?>

    <!-- JavaScript pour le système dynamique -->
    <?php if (function_exists('asset_url')): ?>
        <script src="<?php echo asset_url('assets/js/dynamic-exercises.js'); ?>"></script>
    <?php else: ?>
        <script src="/assets/js/dynamic-exercises.js"></script>
    <?php endif; ?>

    <?php if (function_exists('asset_url')): ?>
        <script src="<?php echo asset_url('assets/js/exercises.js'); ?>"></script>
    <?php else: ?>
        <script src="/assets/js/exercises.js"></script>
    <?php endif; ?>

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
                    console.log('🎯 Initialisation du système d\'exercices dynamique pour Terminale...');
                    const baseUrl = window.baseUrl || '';
                    const apiEndpoint = baseUrl ? baseUrl + '/api/get_exercises.php' : '/api/get_exercises.php';
                    console.log('📡 API Endpoint:', apiEndpoint);

                    try {
                        // Passer le sélecteur (string) au lieu de l'élément directement
                        window.dynamicExerciseSystem = new DynamicExerciseSystem({
                            containerSelector: '[data-dynamic-exercises]', // Sélecteur string
                            level: 'Terminale',
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
<?php
// Fin du fichier exercices-terminale.php
