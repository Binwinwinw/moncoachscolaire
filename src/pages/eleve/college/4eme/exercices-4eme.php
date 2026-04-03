<?php
$page_css = 'college/4eme/exercices-4eme.css';
$page_class = 'page-exercices-4eme';
?>

<main class="main-content min-h-screen bg-gray-50">
    <?php /* ...existing code... */ ?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4 flex items-center justify-center gap-3">
                <span class="text-6xl">⚙️</span>
                Exercices 4ème - Ton Atelier d'Ingénieur
            </h1>
            <p class="text-xl text-gray-600 mb-6">Programme 2025 | Répare la Machine Temporelle</p>

            <!-- Navigation Buttons -->
            <div class="flex flex-wrap justify-center gap-4">
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo function_exists('site_url') ? site_url('exercices') : '/exercices'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg border-2 border-blue-700">
                    <span class="mr-2">📝</span>
                    Tous les exercices
                </a>
                <?php endif; ?>
                <a href="<?php echo function_exists('site_url') ? site_url('cours', ['niveau' => '4eme']) : '/cours?niveau=4eme'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                    <span class="mr-2">📚</span>
                    Cours 4ème
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
            </div>
        </div>


<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
} $is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']); ?>

        <!-- Pour les visiteurs : aperçu exercices PUIS panneau coach -->
        <?php if (empty($is_logged_in)): ?>
            <!-- Aperçu exercices AVANT le panneau coach -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <strong class="text-yellow-800">🔍 Aperçu des fonctionnalités disponibles :</strong><br>
                <div class="text-yellow-700 mt-2">
                    • Exercices interactifs avec corrections automatiques<br>
                    • Conseils méthodologiques adaptés à la 4ème<br>
                    • Suivi de progression par matière<br>
                    • Préparation aux évaluations
                </div>
            </div>

            <?php
            if (is_file(__DIR__ . '/../../../../includes/exercice_card.php')) {
                require_once __DIR__ . '/../../../../includes/exercice_card.php';
            }
            if (function_exists('renderExercisePreview')) {
                renderExercisePreview('4ème', 3);
            }
            echo '<p class="text-center mt-6"><a href="' . site_url('register') . '" class="text-blue-600 hover:text-blue-800 font-medium">Créez un compte</a> ou <a href="' . site_url('login') . '" class="text-blue-600 hover:text-blue-800 font-medium">connectez-vous</a> pour accéder à tous les exercices.</p>';
            ?>

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
        <?php return; endif; ?>

        <!-- Aperçu limité des exercices -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <strong class="text-yellow-800">🔍 Aperçu des fonctionnalités disponibles :</strong><br>
            <div class="text-yellow-700 mt-2">
                • Exercices interactifs avec corrections automatiques<br>
                • Conseils méthodologiques adaptés à la 4ème<br>
                • Suivi de progression par matière<br>
                • Préparation aux évaluations
            </div>
        </div>

<?php
if (empty($is_logged_in)) {
    if (is_file(__DIR__ . '/../../../../includes/exercice_card.php')) {
        require_once __DIR__ . '/../../../../includes/exercice_card.php';
    }
    if (function_exists('renderExercisePreview')) {
        renderExercisePreview('4ème', 3);
    }
    echo '<p class="text-center mt-6"><a href="' . site_url('register') . '" class="text-blue-600 hover:text-blue-800 font-medium">Créez un compte</a> ou <a href="' . site_url('login') . '" class="text-blue-600 hover:text-blue-800 font-medium">connectez-vous</a> pour accéder à tous les exercices.</p>';
    echo '</main>';
    include __DIR__ . '/../../../../includes/footer.php';
    exit;
}
?>

        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <strong class="text-blue-800">🔧 Bienvenue dans ton atelier d'Ingénieur ! Ton Coach est impressionné !</strong><br>
            <span class="text-blue-700">En 4ème, tu vas devenir un véritable ingénieur ! Complexité, analyse, esprit critique...
            Chaque problème résolu est une pièce de la machine temporelle que tu répares.
            Prêt à voyager dans le temps grâce à tes connaissances ? L'aventure commence ! ⏰</span>
        </div>

        <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-6 mb-8 border border-orange-200">
            <h4 class="text-xl font-bold text-orange-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Ta Mission d'Ingénieur
            </h4>
            <p class="text-orange-700 mb-4">
                Réparer la Machine Temporelle en maîtrisant la complexité et l'analyse !
                Chaque compétence acquise est un composant essentiel.
            </p>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                    <div class="bg-orange-600 h-3 rounded-full transition-all duration-300" id="globalProgress"></div>
                </div>
                <p class="text-sm text-gray-600">
                    <strong>Progression de la réparation : <span id="progressText">0%</span></strong>
                </p>
            </div>
        </div>

        <!-- NOUVEAU SYSTÈME DYNAMIQUE D'EXERCICES -->
        <section class="bg-white rounded-xl shadow-lg p-6" id="dynamic-exercises-section">
            <div class="dynamic-exercises-container" data-dynamic-exercises data-level="4ème"></div>
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
        <h2>📚 Français - Ingénieur Grammatical</h2>

        <div class="coach-message">
            <strong>🔍 La grammaire, c'est ton microscope analytique !</strong> En 4ème, tu vas disséquer
            les textes comme un vrai scientifique. Chaque règle grammaticale est un outil
            pour comprendre les mécanismes du langage.
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Analyse Syntaxique - Phrases Complexes</h3>
                <span class="difficulty">⭐⭐⭐ DIFFICILE</span>
            </div>
            <p><strong>Objectif :</strong> Analyser la structure des phrases complexes avec propositions subordonnées.</p>

            <div class="tip-box">
                <h4>🔬 Méthode d'Analyse</h4>
                <p>Proposition principale = base de la phrase<br>
                Proposition subordonnée = apporte un complément d'information<br>
                Conjonctions : que, quand, si, comme, puisque...</p>
            </div>

            <p>Analyse : "Quand il pleut, les enfants restent à la maison parce qu'ils n'aiment pas se mouiller."</p>
            <p>• Combien de propositions ?</p>
            <p>• Quelle est la proposition principale ?</p>
            <p>• Identifie les subordonnées et leur type.</p>

            <button class="btn-coach" onclick="showCorrection('analyse-syntaxique')">Voir l'analyse complète</button>
            <div class="success-message" id="correction-analyse-syntaxique">
                <strong>✅ Analyse détaillée :</strong><br>
                • 3 propositions<br>
                • Principale : "les enfants restent à la maison"<br>
                • Subordonnée de temps : "Quand il pleut"<br>
                • Subordonnée de cause : "parce qu'ils n'aiment pas se mouiller"<br>
                <strong>🔬 Excellent ! Tu es un analyste grammatical hors pair.</strong>
            </div>
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Figures de Style - Compréhension et Analyse</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Identifier et analyser les figures de style dans un texte.</p>

            <div class="tip-box">
                <h4>🎭 Figures Courantes</h4>
                <p>Métaphore : comparaison implicite (il est un lion)<br>
                Comparaison : avec comme/que (courageux comme un lion)<br>
                Personnification : objet animé (le vent hurle)</p>
            </div>

            <p>Dans cette phrase : "Le soleil, roi flamboyant du ciel bleu, souriait aux champs dorés."</p>
            <p>• Quelle figure emploie "roi flamboyant" ?</p>
            <p>• Quelle figure emploie "souriait" ?</p>
            <p>• Quel effet produit cette accumulation de figures ?</p>

            <button class="btn-coach" onclick="showCorrection('figures-style')">Analyser les figures</button>
            <div class="success-message" id="correction-figures-style">
                <strong>✅ Analyse des figures :</strong><br>
                • "roi flamboyant" : métaphore<br>
                • "souriait" : personnification<br>
                • Effet : crée une atmosphère poétique et vivante<br>
                <strong>🎭 Bravo ! Tu maîtrises l'art des figures de style.</strong>
            </div>
        </div>
    </section>

    <!-- MATHÉMATIQUES -->
    <section id="maths">
        <h2>🔢 Mathématiques - Architecte Temporel</h2>

        <div class="coach-message">
            <strong>🏗️ Les maths en 4ème, c'est de l'ingénierie pure !</strong>
            Le théorème de Pythagore, les fonctions, les statistiques...
            Chaque démonstration est une pièce de ta machine temporelle.
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Théorème de Pythagore - Applications Pratiques</h3>
                <span class="difficulty">⭐⭐⭐ DIFFICILE</span>
            </div>
            <p><strong>Objectif :</strong> Appliquer le théorème de Pythagore dans des situations concrètes.</p>

            <div class="tip-box">
                <h4>📐 Formule Magique</h4>
                <p>Dans un triangle rectangle : a² + b² = c²<br>
                où c est l'hypoténuse (côté opposé à l'angle droit)</p>
            </div>

            <p>Un escalier a des marches de 3m de large et 4m de hauteur. Quelle est la longueur de la rampe ?</p>
            <p>Un bateau se trouve à 5km de la côte nord et 12km de la côte ouest. Quelle distance le sépare du port ?</p>

            <button class="btn-coach" onclick="showCorrection('pythagore')">Calculer avec Pythagore</button>
            <div class="success-message" id="correction-pythagore">
                <strong>✅ Solutions :</strong><br>
                • Rampe : √(3² + 4²) = √(9 + 16) = √25 = 5m<br>
                • Distance au port : √(5² + 12²) = √(25 + 144) = √169 = 13km<br>
                <strong>📐 Génial ! Tu es un maître du théorème de Pythagore.</strong>
            </div>
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Fonctions - Représentation Graphique</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Comprendre et tracer des fonctions affines simples.</p>

            <div class="tip-box">
                <h4>📈 Fonction Affine</h4>
                <p>Forme : y = ax + b<br>
                a = coefficient directeur (pente)<br>
                b = ordonnée à l'origine (intersection avec y)</p>
            </div>

            <p>Trace la fonction y = 2x + 1 pour x allant de -2 à 2.</p>
            <p>Quels sont les points d'intersection avec les axes ?</p>
            <p>La fonction est-elle croissante ou décroissante ?</p>

            <button class="btn-coach" onclick="showCorrection('fonctions')">Analyser la fonction</button>
            <div class="success-message" id="correction-fonctions">
                <strong>✅ Analyse de y = 2x + 1 :</strong><br>
                • Points : (-2,-3), (-1,-1), (0,1), (1,3), (2,5)<br>
                • Intersection x : (0,1), intersection y : (-0.5,0)<br>
                • Fonction croissante (a = 2 > 0)<br>
                <strong>📈 Excellent ! Tu maîtrises les fonctions affines.</strong>
            </div>
        </div>
    </section>

    <!-- SCIENCES -->
    <section id="sciences">
        <h2>🧬 Sciences - Génie de la Vie et de l'Énergie</h2>

        <div class="coach-message">
            <strong>⚡ Bienvenue dans le laboratoire de l'Ingénieur !</strong> En 4ème, tu vas découvrir
            le code de la vie et les centrales énergétiques. Chaque molécule, chaque cellule
            est une pièce de ta machine temporelle.
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>SVT - Le Code Génétique et la Reproduction</h3>
                <span class="difficulty">⭐⭐⭐ DIFFICILE</span>
            </div>
            <p><strong>Objectif :</strong> Comprendre les mécanismes de la reproduction et de l'hérédité.</p>

            <div class="tip-box">
                <h4>🧬 Lois de Mendel</h4>
                <p>Loi de ségrégation : séparation des allèles<br>
                Loi de dominance : un allèle masque l'autre<br>
                Loi d'indépendance : gènes indépendants</p>
            </div>

            <p>Chez les pois, la couleur verte (V) est dominante sur la jaune (v).</p>
            <p>Si on croise un pois hétérozygote Vv avec un pois homozygote recessif vv :</p>
            <p>• Quel est le génotype des parents ?</p>
            <p>• Quelle sera la proportion de pois verts dans la descendance ?</p>

            <button class="btn-coach" onclick="showCorrection('genetique')">Résoudre le croisement</button>
            <div class="success-message" id="correction-genetique">
                <strong>✅ Analyse génétique :</strong><br>
                • Parents : Vv × vv<br>
                • Descendance : 50% Vv (verts), 50% vv (jaunes)<br>
                • Proportion pois verts : 50%<br>
                <strong>🧬 Bravo ! Tu déchiffres le code de la vie.</strong>
            </div>
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Physique-Chimie - Circuits Électriques et Énergie</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Comprendre les principes des circuits électriques et des transformations énergétiques.</p>

            <div class="tip-box">
                <h4>⚡ Lois Électriques</h4>
                <p>Loi d'Ohm : U = R × I<br>
                Puissance : P = U × I<br>
                Énergie : E = P × t</p>
            </div>

            <p>Un circuit contient une lampe de 12V et 2A.</p>
            <p>• Quelle est la résistance de la lampe ?</p>
            <p>• Quelle puissance électrique développe-t-elle ?</p>
            <p>• Quelle énergie consomme-t-elle en 1 heure ?</p>

            <button class="btn-coach" onclick="showCorrection('electricite')">Calculer les grandeurs</button>
            <div class="success-message" id="correction-electricite">
                <strong>✅ Calculs électriques :</strong><br>
                • Résistance : R = U/I = 12V/2A = 6Ω<br>
                • Puissance : P = U×I = 12V×2A = 24W<br>
                • Énergie : E = P×t = 24W×3600s = 86400J = 86,4kJ<br>
                <strong>⚡ Excellent ! Tu es un électricien hors pair.</strong>
            </div>
        </div>
    </section>

    <!-- HISTOIRE-GÉO -->
    <section id="histoire-geo">
        <h2>🌍 Histoire-Géo - Navigateur Temporel</h2>

        <div class="coach-message">
            <strong>⏰ Le passé éclaire l'avenir !</strong> En 4ème, tu vas analyser les grandes révolutions
            et comprendre comment elles ont façonné notre monde. Chaque événement historique
            est une leçon pour réparer notre machine temporelle.
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Histoire - La Révolution Française (1789-1799)</h3>
                <span class="difficulty">⭐⭐⭐ DIFFICILE</span>
            </div>
            <p><strong>Objectif :</strong> Analyser les causes et conséquences de la Révolution Française.</p>

            <div class="tip-box">
                <h4>⚖️ Causes de la Révolution</h4>
                <p>Crise financière du royaume<br>
                Inégalités sociales (privilèges de la noblesse)<br>
                Influence des Lumières (Voltaire, Rousseau)<br>
                Famine et disette</p>
            </div>

            <p>Explique le rôle de chacun dans le déclenchement de la Révolution :</p>
            <p>• Les États Généraux de 1789</p>
            <p>• Le Serment du Jeu de Paume</p>
            <p>• La prise de la Bastille</p>

            <button class="btn-coach" onclick="showCorrection('revolution')">Analyser les événements</button>
            <div class="success-message" id="correction-revolution">
                <strong>✅ Analyse historique :</strong><br>
                • États Généraux : réunion des trois ordres pour résoudre la crise<br>
                • Serment du Jeu de Paume : députés du Tiers jurent de ne pas se séparer<br>
                • Prise de la Bastille : symbole de la victoire populaire sur l'absolutisme<br>
                <strong>⚖️ Bravo ! Tu comprends les mécanismes révolutionnaires.</strong>
            </div>
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Géographie - Analyse de Cartes et Documents</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Analyser des cartes pour comprendre des phénomènes géographiques.</p>

            <div class="tip-box">
                <h4>🗺️ Lecture de Carte</h4>
                <p>Identifier la légende et l'échelle<br>
                Repérer les symboles et couleurs<br>
                Analyser les répartitions spatiales</p>
            </div>

            <p>Sur une carte de densité de population :</p>
            <p>• Que représentent les zones rouges foncées ?</p>
            <p>• Quels facteurs expliquent ces concentrations ?</p>
            <p>• Quelles conséquences sur l'aménagement du territoire ?</p>

            <button class="btn-coach" onclick="showCorrection('cartes')">Analyser la carte</button>
            <div class="success-message" id="correction-cartes">
                <strong>✅ Analyse cartographique :</strong><br>
                • Zones rouges : fortes densités de population<br>
                • Facteurs : ressources, emplois, transports, climats favorables<br>
                • Conséquences : urbanisation, pression foncière, migrations<br>
                <strong>🗺️ Excellent ! Tu es un cartographe accompli.</strong>
            </div>
        </div>
    </section>

    <div class="coach-message">
        <strong>🎉 Mission accomplie, Ingénieur Temporel !</strong> Tu as réparé plusieurs composants
        de la machine temporelle aujourd'hui. Complexité maîtrisée, analyse affûtée, esprit critique aiguisé !
        Continue tes réparations demain pour voyager pleinement dans le temps.
        <br><br>
        <strong>⚙️ Ton Coach est fier de ton génie créatif !</strong>
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
                    console.log('🎯 Initialisation du système d\'exercices dynamique pour 4ème...');
                    const baseUrl = window.baseUrl || '';
                    const apiEndpoint = baseUrl ? baseUrl + '/api/get_exercises.php' : '/api/get_exercises.php';
                    console.log('📡 API Endpoint:', apiEndpoint);

                    try {
                        // Passer le sélecteur (string) au lieu de l'élément directement
                        window.dynamicExerciseSystem = new DynamicExerciseSystem({
                            containerSelector: '[data-dynamic-exercises]', // Sélecteur string
                            level: '4ème',
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

        // Ancien code pour la progression (conservé pour compatibilité)
        let completedExercises = 0;
        const totalExercises = 8;

        function showCorrection(exerciseId) {
            const correctionDiv = document.getElementById('correction-' + exerciseId);
            if (correctionDiv) {
                correctionDiv.style.display = 'block';
                updateProgress();
            }
        }

        function updateProgress() {
            completedExercises++;
            const percentage = Math.round((completedExercises / totalExercises) * 100);
            const progressBar = document.getElementById('globalProgress');
            const progressText = document.getElementById('progressText');
            if (progressBar) {
                progressBar.style.width = percentage + '%';
            }
            if (progressText) {
                progressText.textContent = percentage + '%';
            }
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

