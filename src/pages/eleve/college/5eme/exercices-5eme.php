<?php
$page_css = 'college/5eme/exercices-5eme.css';
$page_class = 'page-exercices-5eme';
?>

<main class="main-content min-h-screen bg-gray-50">
    <?php /* ...existing code... */ ?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4 flex items-center justify-center gap-3">
                <span class="text-6xl">🗺️</span>
                Exercices 5ème - Ton Espace Explorateur
            </h1>
            <p class="text-xl text-gray-600 mb-6">Programme 2025 | Découvre de nouveaux horizons scolaires</p>

            <!-- Navigation Buttons -->
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo function_exists('site_url') ? site_url('cours', ['niveau' => '5eme']) : '/cours?niveau=5eme'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                    <span class="mr-2">📚</span>
                    Cours 5ème
                </a>
                <a href="<?php echo function_exists('site_url') ? site_url('eleve/college/college-accueil') : '/eleve/college/college-accueil'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors shadow-lg">
                    <span class="mr-2">🏠</span>
                    Accueil Collège
                </a>
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo function_exists('site_url') ? site_url('eleve/dashboard') : '/eleve/dashboard'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition-colors shadow-lg">
                    <span class="mr-2">📊</span>
                    Mon Dashboard
                </a>
                <?php endif; ?>
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo function_exists('site_url') ? site_url('exercices') : '/exercices'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg border-2 border-blue-700">
                    <span class="mr-2">📝</span>
                    Tous les exercices
                </a>
                <?php endif; ?>
            </div>
        </div>


<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']); ?>

        <!-- Affichage pour visiteurs : exercice aléatoire -->
        <?php if (empty($is_logged_in)): ?>
            <?php
            if (is_file(__DIR__ . '/../../../../includes/exercice_card.php')) {
                require_once __DIR__ . '/../../../../includes/exercice_card.php';
            }
            if (function_exists('renderExercisePreview')) {
                renderExercisePreview('5ème', 1);
            }
            ?>
        <?php endif; ?>

        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <?php if (!empty($is_logged_in)): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <strong class="text-blue-800">🌟 Salut ! Ton Coach est fier de toi !</strong><br>
            <span class="text-blue-700">La 5ème, c'est l'année des découvertes ! Tu vas explorer de nouveaux territoires scolaires,
            découvrir des matières passionnantes. Chaque défi relevé te rend plus fort.
            Prêt à devenir un explorateur des connaissances ? L'aventure commence ! 🧭</span>
        </div>

        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 mb-8 border border-green-200">
            <h4 class="text-xl font-bold text-green-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Ta Quête du Jour
            </h4>
            <p class="text-green-700 mb-4">
                Approfondir tes connaissances et ouvrir ton esprit à de nouvelles matières !
                Chaque exploration te rapproche du mystère des cartes perdues.
            </p>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                    <div class="bg-green-600 h-3 rounded-full transition-all duration-300" id="globalProgress" style="width: 0%;"></div>
                </div>
                <p class="text-sm text-gray-600">
                    <strong>Progression de l'exploration : <span id="progressText">0%</span></strong>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- NOUVEAU SYSTÈME DYNAMIQUE D'EXERCICES -->
        <section class="bg-white rounded-xl shadow-lg p-6" id="dynamic-exercises-section">
            <div class="dynamic-exercises-container" data-dynamic-exercises data-level="5ème"></div>
        </section>

        <!-- Anciennes sections (cachées, conservées pour référence) -->
    <style>
        #dynamic-exercises-section ~ section,
        #dynamic-exercises-section ~ .coach-message:last-of-type {
            display: none;
        }
    </style>
    <?php if (!empty($is_logged_in)): ?>
    <div class="coach-message">
        <strong>🎉 Mission accomplie, Explorateur !</strong> Tu as découvert de nouveaux territoires
        scolaires aujourd'hui. Chaque réponse correcte est une carte que tu ajoutes à ta collection.
        Continue tes explorations demain pour résoudre le mystère des cartes perdues !
        <br><br>
        <strong>🧭 Ton Coach t'accompagne dans toutes tes aventures !</strong>
    </div>
    <?php endif; ?>

    <!-- CSS pour le système dynamique -->

    <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/dynamic-exercises.css') : 'assets/css/pages/dynamic-exercises.css'; ?>">

    <!-- Script pour les exercices interactifs (charger AVANT le système dynamique) -->
    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : 'assets/js/interactive-exercises.js'; ?>"></script>

    <!-- JavaScript pour le système dynamique -->
    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/dynamic-exercises.js') : 'assets/js/dynamic-exercises.js'; ?>"></script>

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
                    console.log('🎯 Initialisation du système d\'exercices dynamique pour 5ème...');
                    const baseUrl = window.baseUrl || '';
                    const apiEndpoint = baseUrl ? baseUrl + '/api/get_exercises.php' : '/api/get_exercises.php';
                }
            }
        }
    </script>

        <!-- Panneau d'incitation pour les visiteurs (après les exercices) -->
        <?php if (empty($is_logged_in)): ?>
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8 mb-8 border border-blue-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                <span class="text-3xl">🔒</span>
                Débloque ton Coach Scolaire Personnalisé
            </h2>
            <p class="text-gray-700 mb-6">
                Tu vois ici un aperçu des exercices disponibles, mais pour accéder à ton coach personnel,
                à ses conseils adaptés à ton niveau, et à l'accompagnement complet, tu dois créer un compte gratuit !
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span>🏆</span>
                        Récompenses & Badges
                    </h3>
                    <p class="text-sm text-gray-600">Système de gamification pour te motiver et célébrer tes victoires</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo function_exists('site_url') ? site_url('register') : 'index.php?page=register'; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg">
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



        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <strong class="text-blue-800">🌟 Salut ! Ton Coach est fier de toi !</strong><br>
            <span class="text-blue-700">La 5ème, c'est l'année des découvertes ! Tu vas explorer de nouveaux territoires scolaires,
            découvrir des matières passionnantes. Chaque défi relevé te rend plus fort.
            Prêt à devenir un explorateur des connaissances ? L'aventure commence ! 🧭</span>
        </div>

        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 mb-8 border border-green-200">
            <h4 class="text-xl font-bold text-green-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Ta Quête du Jour
            </h4>
            <p class="text-green-700 mb-4">
                Approfondir tes connaissances et ouvrir ton esprit à de nouvelles matières !
                Chaque exploration te rapproche du mystère des cartes perdues.
            </p>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                    <div class="bg-green-600 h-3 rounded-full transition-all duration-300" id="globalProgress" style="width: 0%;"></div>
                </div>
                <p class="text-sm text-gray-600">
                    <strong>Progression de l'exploration : <span id="progressText">0%</span></strong>
                </p>
            </div>
        </div>

        <!-- NOUVEAU SYSTÈME DYNAMIQUE D'EXERCICES -->
        <section class="bg-white rounded-xl shadow-lg p-6" id="dynamic-exercises-section">
            <div class="dynamic-exercises-container" data-dynamic-exercises data-level="5ème"></div>
        </section>


        <!-- Anciennes sections (cachées, conservées pour référence) -->
    <style>
        #dynamic-exercises-section ~ section,
        #dynamic-exercises-section ~ .coach-message:last-of-type {
            display: none;
        }
    </style>
    <div class="coach-message">
        <strong>🎉 Mission accomplie, Explorateur !</strong> Tu as découvert de nouveaux territoires
        scolaires aujourd'hui. Chaque réponse correcte est une carte que tu ajoutes à ta collection.
        Continue tes explorations demain pour résoudre le mystère des cartes perdues !
        <br><br>
        <strong>🧭 Ton Coach t'accompagne dans toutes tes aventures !</strong>
    </div>

    <!-- CSS pour le système dynamique -->
    <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/dynamic-exercises.css') : 'assets/css/pages/dynamic-exercises.css'; ?>">

    <!-- Script pour les exercices interactifs (charger AVANT le système dynamique) -->
    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : 'assets/js/interactive-exercises.js'; ?>"></script>

    <!-- JavaScript pour le système dynamique -->
    <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/dynamic-exercises.js') : 'assets/js/dynamic-exercises.js'; ?>"></script>

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
                    console.log('🎯 Initialisation du système d\'exercices dynamique pour 5ème...');
                    const baseUrl = window.baseUrl || '';
                    const apiEndpoint = baseUrl ? baseUrl + '/api/get_exercises.php' : '/api/get_exercises.php';
                    console.log('📡 API Endpoint:', apiEndpoint);

                    try {
                        // Passer le sélecteur (string) au lieu de l'élément directement
                        window.dynamicExerciseSystem = new DynamicExerciseSystem({
                            containerSelector: '[data-dynamic-exercises]', // Sélecteur string
                            level: '5ème',
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
    </style>
    </div>
</main>
