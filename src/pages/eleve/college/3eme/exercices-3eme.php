<?php
$page_css = 'college/3eme/exercices-3eme.css';
$page_class = 'page-exercices-3eme';
?>

<main class="main-content min-h-screen bg-gray-50">
    <?php /* ...existing code... */ ?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4 flex items-center justify-center gap-3">
                <span class="text-6xl">🏰</span>
                Exercices 3ème - Préparation Brevet
            </h1>
            <p class="text-xl text-gray-600 mb-6">Programme 2025 | L'Expert gravit la Tour de Préparation</p>

            <!-- Navigation Buttons -->
            <div class="flex flex-wrap justify-center gap-4">
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo function_exists('site_url') ? site_url('exercices') : '/exercices'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg border-2 border-blue-700">
                    <span class="mr-2">📝</span>
                    Tous les exercices
                </a>
                <?php endif; ?>
                <a href="<?php echo function_exists('site_url') ? site_url('cours', ['niveau' => '3eme']) : '/cours?niveau=3eme'; ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                    <span class="mr-2">📚</span>
                    Cours 3ème
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
        <?php if (function_exists('asset_url')): ?>
            <link rel="stylesheet" href="<?php echo asset_url('assets/css/pages/dynamic-exercises.css'); ?>">
        <?php else: ?>
            <link rel="stylesheet" href="/assets/css/pages/dynamic-exercises.css">
        <?php endif; ?>

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
                renderExercisePreview('3ème', 1);
            }
            ?>
        <?php endif; ?>

        <!-- Aperçu limité des exercices -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <strong class="text-blue-800">🔍 Aperçu des fonctionnalités disponibles :</strong><br>
            <div class="text-blue-700 mt-2">
                • Exercices interactifs avec corrections automatiques<br>
                • Conseils méthodologiques adaptés à la 3ème<br>
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
        renderExercisePreview('3ème', 3);
    }
    echo '<p class="preview-cta">Pour accéder à tous les exercices et fonctionnalités, <a href="' . site_url('register') . '">créez un compte</a> ou <a href="' . site_url('login') . '">connectez-vous</a>.</p>';
    // Suppression du return/exit pour afficher le panneau coach après l’aperçu
}
?>

        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <strong class="text-blue-800">🎯 EXAMEN DU BREVET - 2026</strong><br>
            <strong class="text-blue-900">🏰 Thème Narratif : La Tour de Préparation</strong><br>
            <span class="text-blue-700">Bienvenue, Expert ! Tu es au pied de la Tour de Préparation, cette structure majestueuse qui symbolise
            ta montée vers le Brevet. Chaque matière que tu maîtrises est un étage gravi, chaque exercice une marche
            vers le sommet. Tes progrès te rapprochent du titre d'Expert accompli ! 📚✨</span>
        </div>

        <div class="coach-message">
            <strong>👑 Bienvenue Expert  ! Ton Coach est à tes côtés pour le Brevet !</strong><br>
            La 3ème, c'est l'année décisive ! Maîtrise, synthèse, préparation examen...
            Chaque exercice te rapproche du sommet de la tour. Tu vas devenir un expert
            dans toutes les matières. L'examen du Brevet n'aura plus de secrets pour toi !
            Montre-moi de quoi tu es capable ! 📚
        </div>

        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 mb-8 border border-blue-200">
            <h4 class="text-xl font-bold text-blue-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Ton Ascension vers le Brevet
            </h4>
            <p class="text-blue-700 mb-6">
                Gravis les étages de la tour en maîtrisant chaque matière !
                Chaque compétence acquise est une marche vers le diplôme.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span>📊</span>
                        Progression Globale des Exercices
                    </h5>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                        <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" id="globalProgress" style="width: 0%;"></div>
                    </div>
                    <p class="text-sm text-gray-600">
                        <strong>Exercices terminés : <span id="progressText">0%</span></strong>
                    </p>
                </div>
            </div>
        </div>

    <!-- NOUVEAU SYSTÈME DYNAMIQUE D'EXERCICES -->
    <section class="bg-white rounded-xl shadow-lg p-6" id="dynamic-exercises-section">
        <div class="dynamic-exercises-container" data-dynamic-exercises data-level="3ème"></div>
    </section>


    <!-- Anciennes sections (cachées, conservées pour référence) -->
    <style>
        #dynamic-exercises-section ~ section,
        #dynamic-exercises-section ~ .coach-message:last-of-type {
            display: none;
        }
    </style>

    <!-- FRANÇAIS (Ancien système - masqué) -->
    <section id="francais" style="display: none;">
        <h2>📖 Français - Épreuve d'Analyse Textuelle</h2>

        <div class="coach-message">
            <strong>🔬 L'analyse textuelle, c'est ta clé pour le Brevet !</strong> En 3ème, tu dois
            maîtriser l'analyse de textes complexes. Chaque commentaire est une marche
            supplémentaire vers le sommet de ta tour.
        </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>📚  exercice(s) de Français disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>


                <!-- Message pour utilisateurs non connectés -->
                <p><em>Connecte-toi pour voir tous les exercices disponibles !</em></p>

                <!-- Message si DB non disponible -->
                <div class="coach-message" style="background: #fef3c7; border-color: #f59e0b;">
                    <strong>⚠️ Base de données non disponible</strong><br>
                    Affichage du contenu statique. Les exercices de la base de données seront disponibles une fois la connexion rétablie.
                </div>

                <!-- Aucun exercice trouvé -->
                <div class="coach-message" style="background: #fef3c7; border-color: #f59e0b;">
                    <strong>ℹ️ Aucun exercice trouvé dans la base de données</strong><br>
                    Veuillez importer les exercices avec : <code>php tools/import_exercices_to_db.php</code>
                </div>

            <!-- Fallback : Contenu statique pour Français -->
        <div class="exercise-card" data-exercise-id="0" data-difficulty="difficile" data-subject="Français">
            <div class="exercise-header">
                <h3>Analyse de Texte Littéraire - Niveau Brevet</h3>
                <span class="difficulty">⭐⭐⭐ DIFFICILE</span>
            </div>
            <p><strong>Objectif :</strong> Analyser un texte littéraire selon les critères du Brevet (mouvement, procédés, effets).</p>

            <div class="tip-box">
                <h4>📝 Méthode d'Analyse Brevet</h4>
                <p>1. Identifier le mouvement du texte<br>
                2. Repérer les procédés stylistiques<br>
                3. Analyser les effets produits<br>
                4. Citer précisément le texte</p>
            </div>

            <p><strong>Texte à analyser :</strong> "Le vieil homme contemplait la mer depuis sa fenêtre. Les vagues, infatigables messagères de l'océan, venaient mourir sur le sable doré. Dans ce ballet éternel, il retrouvait une paix que les tempêtes de la vie lui avaient arrachée."</p>

            <div class="qcm-exercise"
                 data-questions='[
                     {
                         "question": "Quel est le mouvement dominant de ce texte ?",
                         "choices": [
                             {"value": "a", "label": "a) Épique et héroïque"},
                             {"value": "b", "label": "b) Méditatif et contemplatif"},
                             {"value": "c", "label": "c) Dramatique et tragique"},
                             {"value": "d", "label": "d) Comique et léger"}
                         ],
                         "correct": "b"
                     },
                     {
                         "question": "Identifiez une métaphore dans le texte :",
                         "choices": [
                             {"value": "a", "label": "a) \"vieil homme contemplait\""},
                             {"value": "b", "label": "b) \"vagues, infatigables messagères\""},
                             {"value": "c", "label": "c) \"sable doré\""},
                             {"value": "d", "label": "d) \"depuis sa fenêtre\""}
                         ],
                         "correct": "b"
                     },
                     {
                         "question": "Comment l\'auteur crée-t-il une atmosphère apaisée ?",
                         "choices": [
                             {"value": "a", "label": "a) Par un lexique apaisant et un rythme lent"},
                             {"value": "b", "label": "b) Par des actions violentes"},
                             {"value": "c", "label": "c) Par un vocabulaire technique"},
                             {"value": "d", "label": "d) Par des descriptions précises de la météo"}
                         ],
                         "correct": "a"
                     }
                 ]'>
                <div class="qcm-container"></div>
                <button class="btn-check-qcm">✅ Vérifier mes réponses</button>
                <div class="qcm-feedback" style="display:none;"></div>
            </div>
        </div>

        <div class="exercise-card" data-exercise-id="0" data-difficulty="moyen" data-subject="Français">
            <div class="exercise-header">
                <h3>Réécriture - Transformation de Texte</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Transformer un texte selon des consignes précises (exercice type Brevet).</p>

            <div class="tip-box">
                <h4>✍️ Techniques de Réécriture</h4>
                <p>Changer la personne : je → il/elle<br>
                Changer le temps : présent → passé<br>
                Changer le type : affirmatif → interrogatif<br>
                Respecter la ponctuation et la grammaire</p>
            </div>

            <p><strong>Consigne :</strong> Réécris ce texte à la 3ème personne du pluriel, au passé composé :</p>
            <p>"Je visite le musée et j'admire les tableaux anciens. Je prends des photos et je note les détails importants."</p>

            <div class="conjugation-exercise"
                 data-questions='[
                     {"sentence": "Réécris la première phrase à la 3ème personne du pluriel, au passé composé : \"Je visite le musée et j\'admire les tableaux anciens.\"", "answer": "Ils ont visité le musée et ils ont admiré les tableaux anciens."},
                     {"sentence": "Réécris la deuxième phrase à la 3ème personne du pluriel, au passé composé : \"Je prends des photos et je note les détails importants.\"", "answer": "Ils ont pris des photos et ils ont noté les détails importants."}
                 ]'>
                <div class="conjugation-container"></div>
                <button class="btn-check-conjugation">✅ Vérifier ma réécriture</button>
                <div class="conjugation-feedback" style="display:none;"></div>
            </div>
        </div>

    </section>

    <!-- MATHÉMATIQUES -->
    <section id="maths">
        <h2>🔢 Mathématiques - Arène du Brevet</h2>

        <div class="coach-message">
            <strong>⚔️ L'Arène du Brevet t'attend !</strong> Les maths au Brevet, c'est un combat
            stratégique. Chaque problème résolu est une victoire. Tu vas affronter
            des calculs littéraux, des géométrie dans l'espace, des statistiques...
            Prépare tes armes mathématiques !
        </div>

        <div class="exercise-card" data-exercise-id="0" data-difficulty="difficile" data-subject="Mathématiques">
            <div class="exercise-header">
                <h3>Problème Complexe - Calcul Littéral et Géométrie</h3>
                <span class="difficulty">⭐⭐⭐ DIFFICILE</span>
            </div>
            <p><strong>Objectif :</strong> Résoudre un problème combinant plusieurs notions (format Brevet).</p>

            <div class="tip-box">
                <h4>🧮 Stratégie de Résolution</h4>
                <p>1. Lire attentivement l'énoncé<br>
                2. Identifier les données et l'inconnue<br>
                3. Choisir la méthode appropriée<br>
                4. Vérifier la cohérence des résultats</p>
            </div>

            <p>Un rectangle a pour dimensions (2x+3) et (x+5). Son périmètre mesure 50 cm.</p>
            <p><strong>Questions :</strong></p>

            <div class="math-exercise"
                 data-questions='[
                     {"question": "Écris l\'équation traduisant cette situation (périmètre = 2×(longueur + largeur))", "answer": "2×(2x+3 + x+5) = 50 ou 2×(3x+8) = 50 ou 6x+16 = 50"},
                     {"question": "Simplifie l\'équation : 2×(3x+8) = 50", "answer": "6x+16 = 50"},
                     {"question": "Résous l\'équation 6x+16 = 50. Quelle est la valeur de x ? (arrondi à 2 décimales)", "answer": "5.67"},
                     {"question": "Quelle est la longueur du rectangle ? (2x+3 avec x=5.67, arrondi à 2 décimales)", "answer": "14.33"},
                     {"question": "Quelle est la largeur du rectangle ? (x+5 avec x=5.67, arrondi à 2 décimales)", "answer": "10.67"},
                     {"question": "Calcule l\'aire du rectangle (longueur × largeur, arrondi à l\'unité)", "answer": "153"}
                 ]'>
                <div class="math-container"></div>
                <button class="btn-check-math">✅ Vérifier mes calculs</button>
                <div class="math-feedback" style="display:none;"></div>
            </div>
        </div>

        <div class="exercise-card" data-exercise-id="0" data-difficulty="moyen" data-subject="Mathématiques">
            <div class="exercise-header">
                <h3>Statistiques - Lecture de Graphiques</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Analyser des données statistiques présentées sous forme graphique.</p>

            <div class="tip-box">
                <h4>📊 Lecture de Graphiques</h4>
                <p>Identifier le type de graphique<br>
                Lire précisément les valeurs<br>
                Calculer des pourcentages<br>
                Formuler des conclusions</p>
            </div>

            <p>Un graphique montre la répartition des loisirs des collégiens :</p>
            <p>• Sport : 35%</p>
            <p>• Musique : 25%</p>
            <p>• Jeux vidéo : 20%</p>
            <p>• Lecture : 20%</p>

            <div class="qcm-exercise"
                 data-questions='[
                     {
                         "question": "Quel loisir est le plus pratiqué ?",
                         "choices": [
                             {"value": "a", "label": "a) Musique"},
                             {"value": "b", "label": "b) Sport"},
                             {"value": "c", "label": "c) Jeux vidéo"},
                             {"value": "d", "label": "d) Lecture"}
                         ],
                         "correct": "b"
                     },
                     {
                         "question": "Calcule la différence entre sport et musique (en %)",
                         "choices": [
                             {"value": "a", "label": "a) 5%"},
                             {"value": "b", "label": "b) 10%"},
                             {"value": "c", "label": "c) 15%"},
                             {"value": "d", "label": "d) 20%"}
                         ],
                         "correct": "b"
                     },
                     {
                         "question": "Que représente la catégorie \"Jeux vidéo + Lecture\" ?",
                         "choices": [
                             {"value": "a", "label": "a) 25%"},
                             {"value": "b", "label": "b) 35%"},
                             {"value": "c", "label": "c) 40%"},
                             {"value": "d", "label": "d) 45%"}
                         ],
                         "correct": "c"
                     }
                 ]'>
                <div class="qcm-container"></div>
                <button class="btn-check-qcm">✅ Vérifier mes réponses</button>
                <div class="qcm-feedback" style="display:none;"></div>
            </div>
        </div>
    </section>

    <!-- SCIENCES -->
    <section id="sciences">
        <h2>🧬 Sciences - Enjeux Planétaires</h2>

        <div class="coach-message">
            <strong>🌍 Les sciences au Brevet, c'est comprendre notre monde !</strong>
            De l'évolution des espèces aux enjeux environnementaux, tu vas maîtriser
            les grands mécanismes de la vie et de l'univers. Chaque concept compris
            est une clé pour l'avenir.
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>SVT - Évolution et Biodiversité</h3>
                <span class="difficulty">⭐⭐⭐ DIFFICILE</span>
            </div>
            <p><strong>Objectif :</strong> Comprendre les mécanismes de l'évolution et leurs conséquences.</p>

            <div class="tip-box">
                <h4>🧬 Théories Évolutionnistes</h4>
                <p>Charles Darwin : sélection naturelle<br>
                Jean-Baptiste Lamarck : transmission des caractères acquis<br>
                Mécanismes : mutation, dérive génétique, sélection naturelle</p>
            </div>

            <p>Explique comment la sélection naturelle peut favoriser l'apparition d'une nouvelle espèce :</p>

            <div class="qcm-exercise"
                 data-questions='[
                     {
                         "question": "Quels sont les mécanismes en jeu dans la sélection naturelle ?",
                         "choices": [
                             {"value": "a", "label": "a) Mutations génétiques + sélection naturelle"},
                             {"value": "b", "label": "b) Transmission directe des caractères acquis"},
                             {"value": "c", "label": "c) Croisements forcés entre espèces"},
                             {"value": "d", "label": "d) Adaptation culturelle uniquement"}
                         ],
                         "correct": "a"
                     },
                     {
                         "question": "Donne un exemple concret d\'adaptation par sélection naturelle :",
                         "choices": [
                             {"value": "a", "label": "a) Phalènes du bouleau (coloration protectrice)"},
                             {"value": "b", "label": "b) Apprentissage du langage"},
                             {"value": "c", "label": "c) Utilisation d\'outils"},
                             {"value": "d", "label": "d) Migration saisonnière"}
                         ],
                         "correct": "a"
                     },
                     {
                         "question": "Quelles sont les conséquences de la sélection naturelle sur la biodiversité ?",
                         "choices": [
                             {"value": "a", "label": "a) Réduction de la diversité"},
                             {"value": "b", "label": "b) Augmentation de la biodiversité, adaptation à l\'environnement"},
                             {"value": "c", "label": "c) Stagnation de l\'évolution"},
                             {"value": "d", "label": "d) Disparition de toutes les espèces"}
                         ],
                         "correct": "b"
                     }
                 ]'>
                <div class="qcm-container"></div>
                <button class="btn-check-qcm">✅ Vérifier mes réponses</button>
                <div class="qcm-feedback" style="display:none;"></div>
            </div>
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Physique-Chimie - Énergie et Environnement</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Analyser les enjeux énergétiques et environnementaux contemporains.</p>

            <div class="tip-box">
                <h4>⚡ Sources d'Énergie</h4>
                <p>Renouvelables : solaire, éolien, hydraulique<br>
                Non-renouvelables : pétrole, charbon, nucléaire<br>
                Critères : impact environnemental, disponibilité, coût</p>
            </div>

            <p>Compare deux sources d'énergie : l'éolien et le charbon.</p>

            <div class="qcm-exercise"
                 data-questions='[
                     {
                         "question": "Quels sont les avantages de l\'énergie éolienne ?",
                         "choices": [
                             {"value": "a", "label": "a) Renouvelable, propre, intermittent"},
                             {"value": "b", "label": "b) Polluante et épuisable"},
                             {"value": "c", "label": "c) Abondante et constante"},
                             {"value": "d", "label": "d) Coûteuse uniquement"}
                         ],
                         "correct": "a"
                     },
                     {
                         "question": "Quels sont les inconvénients du charbon ?",
                         "choices": [
                             {"value": "a", "label": "a) Renouvelable et propre"},
                             {"value": "b", "label": "b) Polluant (CO2, particules), non renouvelable"},
                             {"value": "c", "label": "c) Intermittent"},
                             {"value": "d", "label": "d) Coûteux uniquement"}
                         ],
                         "correct": "b"
                     },
                     {
                         "question": "Quelle source d\'énergie privilégier pour l\'avenir ?",
                         "choices": [
                             {"value": "a", "label": "a) Le charbon pour sa constance"},
                             {"value": "b", "label": "b) L\'éolien pour la préservation de l\'environnement"},
                             {"value": "c", "label": "c) Les deux équitablement"},
                             {"value": "d", "label": "d) Aucune des deux"}
                         ],
                         "correct": "b"
                     }
                 ]'>
                <div class="qcm-container"></div>
                <button class="btn-check-qcm">✅ Vérifier mes réponses</button>
                <div class="qcm-feedback" style="display:none;"></div>
            </div>
        </div>
    </section>

    <!-- HISTOIRE-GÉO -->
    <section id="histoire-geo">
        <h2>🌍 Histoire-Géo - Révision 1914 → Aujourd'hui</h2>

        <div class="coach-message">
            <strong>⏰ Le Brevet d'Histoire-Géo, c'est maîtriser le 20ème siècle !</strong>
            Des tranchées de 1914 aux défis contemporains, tu vas comprendre
            comment notre monde s'est construit. Chaque date, chaque événement
            est une pièce du puzzle historique.
        </div>

        <div class="exercise-card">
            <div class="exercise-header">
                <h3>Histoire - La Première Guerre Mondiale</h3>
                <span class="difficulty">⭐⭐⭐ DIFFICILE</span>
            </div>
            <p><strong>Objectif :</strong> Analyser les causes, déroulement et conséquences de la Grande Guerre.</p>

            <div class="tip-box">
                <h4>⚔️ Causes de 1914</h4>
                <p>Nationalismes exacerbés<br>
                Course aux armements<br>
                Alliances militaires (Triple Alliance vs Triple Entente)<br>
                Attentat de Sarajevo (28 juin 1914)</p>
            </div>

            <p>Explique pourquoi la Première Guerre mondiale a éclaté en 1914 :</p>

            <div class="qcm-exercise"
                 data-questions='[
                     {
                         "question": "Quelles sont les causes profondes de la Première Guerre mondiale ?",
                         "choices": [
                             {"value": "a", "label": "a) Nationalismes, alliances, course aux armements"},
                             {"value": "b", "label": "b) Conflits économiques uniquement"},
                             {"value": "c", "label": "c) Différences culturelles"},
                             {"value": "d", "label": "d) Problèmes climatiques"}
                         ],
                         "correct": "a"
                     },
                     {
                         "question": "Quel événement a déclenché le conflit en 1914 ?",
                         "choices": [
                             {"value": "a", "label": "a) Assassinat de l\'archiduc François-Ferdinand à Sarajevo"},
                             {"value": "b", "label": "b) Bataille de Verdun"},
                             {"value": "c", "label": "c) Révolution russe"},
                             {"value": "d", "label": "d) Traité de Versailles"}
                         ],
                         "correct": "a"
                     },
                     {
                         "question": "Quelles ont été les conséquences immédiates de la Grande Guerre ?",
                         "choices": [
                             {"value": "a", "label": "a) 10 millions de morts, traité de Versailles, révolution russe"},
                             {"value": "b", "label": "b) Paix durable en Europe"},
                             {"value": "c", "label": "c) Expansion économique"},
                             {"value": "d", "label": "d) Aucune conséquence"}
                         ],
                         "correct": "a"
                     }
                 ]'>
                <div class="qcm-container"></div>
                <button class="btn-check-qcm">✅ Vérifier mes réponses</button>
                <div class="qcm-feedback" style="display:none;"></div>
            </div>
        </div>

        <div class="exercise-card" data-exercise-id="0" data-difficulty="moyen" data-subject="Géographie">
            <div class="exercise-header">
                <h3>Géographie - Aménagement du Territoire</h3>
                <span class="difficulty">⭐⭐ MOYEN</span>
            </div>
            <p><strong>Objectif :</strong> Analyser les dynamiques territoriales contemporaines.</p>

            <div class="tip-box">
                <h4>🏙️ Facteurs d'Aménagement</h4>
                <p>Économiques : emplois, transports<br>
                Sociaux : qualité de vie, services<br>
                Environnementaux : préservation, développement durable<br>
                Politiques : décisions gouvernementales</p>
            </div>

            <p>Explique les conséquences de la métropolisation en France :</p>

            <div class="qcm-exercise"
                 data-questions='[
                     {
                         "question": "Quels sont les avantages de la métropolisation pour les métropoles ?",
                         "choices": [
                             {"value": "a", "label": "a) Concentration économique, culturelle, innovation"},
                             {"value": "b", "label": "b) Diminution de la population"},
                             {"value": "c", "label": "c) Réduction des services"},
                             {"value": "d", "label": "d) Économie en déclin"}
                         ],
                         "correct": "a"
                     },
                     {
                         "question": "Quels problèmes la métropolisation pose-t-elle ?",
                         "choices": [
                             {"value": "a", "label": "a) Étalement urbain, congestion, fracture territoriale"},
                             {"value": "b", "label": "b) Manque de main-d'œuvre"},
                             {"value": "c", "label": "c) Surpopulation rurale"},
                             {"value": "d", "label": "d) Absence de transports"}
                         ],
                         "correct": "a"
                     },
                     {
                         "question": "Quelles solutions peuvent être envisagées ?",
                         "choices": [
                             {"value": "a", "label": "a) Transports collectifs, pôles secondaires, ruralité dynamique"},
                             {"value": "b", "label": "b) Interdire les métropoles"},
                             {"value": "c", "label": "c) Limiter les transports"},
                             {"value": "d", "label": "d) Désurbaniser complètement"}
                         ],
                         "correct": "a"
                     }
                 ]'>
                <div class="qcm-container"></div>
                <button class="btn-check-qcm">✅ Analyser l'aménagement</button>
                <div class="qcm-feedback" style="display:none;"></div>
            </div>
        </div>
    </section>

    <div class="coach-message">
        <strong>🎉 Félicitations Expert ! Tu as gravi un étage de plus !</strong> Ces exercices
        de préparation au Brevet t'ont permis de consolider tes connaissances.
        Continue ton ascension, le sommet n'est plus très loin. Le diplôme du Brevet
        sera ta récompense ultime !
        <br><br>
        <strong>🏰 Ton Coach croit en tes capacités d'expert !</strong>
    </div>

    <!-- CSS pour le système dynamique -->
    <?php if (function_exists('asset_url')): ?>
        <link rel="stylesheet" href="<?php echo asset_url('assets/css/pages/dynamic-exercises.css'); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="/assets/css/pages/dynamic-exercises.css">
    <?php endif; ?>

    <!-- Colibri désactivé (remplacé par Coach WebM) -->
    <!-- ancien: colibri-mascot.css + colibri-mascot.js -->

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
                    console.log('🎯 Initialisation du système d\'exercices dynamique pour 3ème...');
                    const baseUrl = window.baseUrl || '';
                    const apiEndpoint = baseUrl ? baseUrl + '/api/get_exercises.php' : '/api/get_exercises.php';
                    console.log('📡 API Endpoint:', apiEndpoint);

                    try {
                        // Passer le sélecteur (string) au lieu de l'élément directement
                        window.dynamicExerciseSystem = new DynamicExerciseSystem({
                            containerSelector: '[data-dynamic-exercises]', // Sélecteur string
                            level: '3ème',
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
