<?php
// Démarrer la session avant toute sortie pour éviter "headers already sent"
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_start();
    } else {
        // Tentative de démarrage même si des headers ont été envoyés (fallback)
        @session_start();
    }
}

$page_css = 'college/6eme/exercices-6eme.css';
$page_class = 'page-exercices-6eme';

require_once dirname(__DIR__, 4) . '/includes/exercices_page_header.php';

// Détecter l'état de connexion de l'utilisateur pour usage dans la page
$is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);
?>

<main class="main-content min-h-screen">
    <?php /* ...existing code... */ ?>

    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 lg:px-8">
        <?php
        $header_nav = [
            ['href' => site_url('cours', ['niveau' => '6eme']), 'label' => 'Cours 6ème', 'icon' => '📚', 'tone' => 'primary'],
            ['href' => site_url('eleve/college/college-accueil'), 'label' => 'Accueil Collège', 'icon' => '🏠', 'tone' => 'secondary'],
        ];
        if (!empty($is_logged_in)) {
            $header_nav[] = ['href' => site_url('eleve/dashboard'), 'label' => 'Mon Dashboard', 'icon' => '📊', 'tone' => 'dashboard'];
        }
        render_exercices_page_header([
            'icon' => '⚔️',
            'title' => 'Exercices 6ème - Quête du Sceptre Unificateur',
            'subtitle' => 'Programme 2025 | Deviens l\'Aventurier qui maîtrise tous les savoirs !',
            'nav_links' => $header_nav,
        ]);
        ?>


        <?php // session initialisée en haut du fichier
        ?>

        <!-- Contenu pour les utilisateurs NON connectés -->
        <?php if (empty($is_logged_in)): ?>
            <!-- Aperçu exercices AVANT le panneau coach -->
            <?php
            if (is_file(__DIR__ . '/../../../../includes/exercice_card.php')) {
                require_once __DIR__ . '/../../../../includes/exercice_card.php';
            }
            if (function_exists('renderExercisePreview')) {
                renderExercisePreview('6ème', 3);
            }
            echo '<p class="text-center mt-6"><a href="' . site_url('register') . '" class="text-blue-600 hover:text-blue-800 font-medium">Créez un compte</a> ou <a href="' . site_url('login') . '" class="text-blue-600 hover:text-blue-800 font-medium">connectez-vous</a> pour accéder à tous les exercices.</p>';
            ?>
            <div class="banner-theme rounded-xl p-8 mb-8">
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
                            <span>🏆</span>
                            Récompenses & Badges
                        </h3>
                        <p class="text-sm text-gray-600">Système de gamification pour te motiver et célébrer tes victoires</p>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-sm md:col-span-2 lg:col-span-1">
                        <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            <span>🎯</span>
                            Exercices Adaptés
                        </h3>
                        <p class="text-sm text-gray-600">Contenu qui s'ajuste à tes forces et faiblesses pour maximiser tes progrès</p>
                    </div>
                </div>
                <p class="text-center text-gray-700 font-medium mb-6">
                    💡 Le coach s'adapte à ton cursus scolaire et te donne des conseils pédagogiques personnalisés !
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="index.php?page=register" class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 transition-all shadow-lg">
                        <span class="mr-2">✨</span>
                        Créer mon compte gratuit
                    </a>
                    <a href="index.php?page=login" class="inline-flex items-center justify-center px-6 py-3 bg-white text-green-700 font-semibold rounded-lg border border-green-300 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-300 transition-colors shadow-lg">
                        <span class="mr-2">🔑</span>
                        Me connecter
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <div class="rounded-[1.75rem] border border-sky-200/70 bg-white/85 p-6 shadow-[0_20px_60px_-24px_rgba(59,130,246,0.25)] backdrop-blur">
            <strong class="text-blue-800">👋 Salut ! C'est ton Coach Scolaire qui te parle !</strong><br>
            <strong class="text-blue-900">🎭 Thème Narratif : La Quête du Sceptre Unificateur</strong><br>
            <span class="text-blue-700">Bienvenue, jeune Aventurier ! Tu es au début de ta grande quête pour réunir les fragments du Sceptre Unificateur,
                cet artefact légendaire qui symbolise la maîtrise de toutes les connaissances de la 6ème.<br>
                Chaque matière que tu étudies est un royaume à conquérir, chaque exercice une épreuve à surmonter.
                Tes progrès te rapprochent du titre d'Aventurier accompli ! ⚔️✨</span>
        </div>

        <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 mb-8 border border-purple-200">
            <h4 class="text-xl font-bold text-purple-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Ton Objectif du Jour : Premier Fragment du Sceptre
            </h4>
            <p class="text-purple-700 mb-6">
                Maîtrise les bases fondamentales de chaque matière pour obtenir le premier fragment du Sceptre Unificateur.
                Chaque victoire te donne plus de puissance pour affronter les défis à venir !
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span>📊</span>
                        Progression Globale des Exercices
                    </h5>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                        <div class="progress-theme-fill h-3 rounded-full transition-all duration-300" id="globalProgress"></div>
                    </div>
                    <p class="text-sm text-gray-600">
                        <strong>Exercices terminés : <span id="progressText">0%</span></strong>
                    </p>
                </div>

                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span>💎</span>
                        Collection de Cristaux Mathématiques
                    </h5>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-2xl">💎</span>
                        <span class="text-lg font-bold text-purple-600" id="crystal-count">0</span>
                        <span class="text-sm text-gray-600">/ 15 cristaux</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                        <div class="bg-purple-600 h-3 rounded-full transition-all duration-300" id="crystal-progress"></div>
                    </div>
                    <p class="text-xs text-gray-500 italic">
                        Objectif : 15 cristaux pour un indice du Sceptre Unificateur !
                    </p>
                </div>
            </div>

            <div class="bg-gradient-to-r from-yellow-100 to-orange-100 rounded-lg p-4 mt-4 border border-yellow-300" id="scepter-hint">
                <div class="text-center">
                    <h4 class="text-lg font-bold text-yellow-800 mb-2">🎭 Indice du Sceptre Débloqué !</h4>
                    <p class="text-yellow-700 italic mb-2">
                        "Le premier fragment du Sceptre repose dans les profondeurs de la Forêt des Nombres Mystérieux.
                        Cherche la clairière où les chiffres s'alignent parfaitement, et où les opérations forment un cercle vertueux..."
                    </p>
                    <p class="text-sm text-yellow-800 font-medium">
                        💡 Conseil : Continue tes exercices de mathématiques pour découvrir le prochain indice !
                    </p>
                </div>
            </div>
        </div>

        <!-- NOUVEAU SYSTÈME DYNAMIQUE D'EXERCICES -->
        <section class="rounded-[1.75rem] border border-slate-200/70 bg-white/90 p-6 shadow-[0_20px_60px_-24px_rgba(15,23,42,0.2)] backdrop-blur" id="dynamic-exercises-section">
            <div class="dynamic-exercises-container" data-dynamic-exercises data-level="6ème"></div>
        </section>


        <!-- Anciennes sections (cachées, conservées pour référence) -->
        <style>
            #dynamic-exercises-section~section,
            #dynamic-exercises-section~.coach-message:last-of-type {
                display: none;
            }
        </style>

        <!-- FRANÇAIS (Ancien système - masqué) -->
        <section id="francais">
            <h2>📚 Français - Maître des Mots</h2>

            <div class="coach-message">
                <strong>💬 Petit conseil de coach :</strong> Le français, c'est comme un jeu vidéo : plus tu pratiques les règles,
                plus tu deviens fort ! On commence par les bases pour que tu sois inarrêtable.
            </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>📚 exercice(s) de Français disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>


            <!-- Message pour utilisateurs non connectés -->
            <p><em>Connecte-toi pour voir tous les exercices disponibles !</em></p>

            <!-- Message si DB non disponible -->
            <div class="coach-message">
                <strong>⚠️ Base de données non disponible</strong><br>
                Affichage du contenu statique. Les exercices de la base de données seront disponibles une fois la connexion rétablie.
            </div>

            <!-- Aucun exercice trouvé -->
            <div class="coach-message">
                <strong>ℹ️ Aucun exercice trouvé dans la base de données</strong><br>
                Veuillez importer les exercices avec : <code>php tools/import_exercices_to_db.php</code>
            </div>

            <!-- Fallback : Contenu statique -->
            <div class="exercise-card">
                <div class="exercise-header">
                    <h3>Les Classes de Mots - Mission Débutant</h3>
                    <span class="difficulty">⭐ FACILE</span>
                </div>
                <p><strong>Objectif :</strong> Identifier les noms, verbes et adjectifs dans une phrase simple.</p>

                <div class="tip-box">
                    <h4>💡 Astuce de Coach</h4>
                    <p>Souviens-toi : les noms désignent des personnes, lieux ou choses. Les verbes expriment des actions.
                        Les adjectifs décrivent les noms. Vas-y pas à pas !</p>
                </div>

                <p><strong>Phrase à analyser :</strong></p>
                <div class="word-coloring-exercise"
                    data-sentence="Le petit chat noir mange sa pâtée."
                    data-correct='{"Le":"determinant","petit":"adjectif","chat":"nom","noir":"adjectif","mange":"verbe","sa":"determinant","pâtée":"nom"}'>
                    <div class="word-coloring-instructions">
                        <p><strong>Ta mission :</strong> Clique sur chaque mot pour le colorier :</p>
                        <ul>
                            <li><span class="text-blue">🔵 Bleu</span> = Nom</li>
                            <li><span class="text-green">🟢 Vert</span> = Verbe</li>
                            <li><span class="text-red">🔴 Rouge</span> = Adjectif</li>
                        </ul>
                        <p><em>💡 Clique plusieurs fois sur un mot pour changer de couleur !</em></p>
                    </div>
                    <div class="word-coloring-container">
                        <!-- Les mots seront générés dynamiquement par JavaScript -->
                    </div>
                    <button class="btn-coach btn-check-coloring">Vérifier mes réponses</button>
                    <div class="coloring-feedback"></div>
                </div>
            </div>

            <div class="exercise-card">
                <div class="exercise-header">
                    <h3>Conjugaison - Présent de l'Indicatif</h3>
                    <span class="difficulty">⭐⭐ MOYEN</span>
                </div>
                <p><strong>Objectif :</strong> Conjuguer correctement des verbes réguliers au présent.</p>

                <div class="tip-box">
                    <h4>🔍 Rappel Méthodologique</h4>
                    <p>Pour les verbes en -er : je/tu/il/elle/on + e, nous + ons, vous + ez, ils/elles + ent.
                        Pour les verbes en -ir : je/tu/il/elle/on + is, nous + issons, vous + issez, ils/elles + issent.</p>
                </div>

                <div class="conjugation-exercise"
                    data-questions='[
                     {"sentence": "Je (chanter) à la chorale du collège.", "answer": "chante"},
                     {"sentence": "Tu (finir) tes devoirs avant le dîner.", "answer": "finis"},
                     {"sentence": "Il (manger) des fruits tous les jours.", "answer": "mange"}
                 ]'>
                    <div class="conjugation-container">
                        <!-- Les champs seront générés dynamiquement par JavaScript -->
                    </div>
                    <button class="btn-coach btn-check-conjugation">Vérifier mes réponses</button>
                    <div class="conjugation-feedback"></div>
                </div>
            </div>

        </section>

        <!-- MATHÉMATIQUES -->
        <section id="maths">
            <h2>🌲 Mathématiques - Forêt des Nombres Mystérieux</h2>

            <div class="immersive-environment">
                <div class="environment-header">
                    <h3>🗺️ Environnement Immersif : Forêt des Nombres Mystérieux</h3>
                    <p><em>"Au cœur de la Forêt des Nombres Mystérieux, d'anciens arbres centenaires murmurent des secrets mathématiques.
                            Chaque branche cache un trésor numérique, chaque clairière révèle une énigme à résoudre.
                            Les chiffres dansent dans la brise, attendant qu'un esprit curieux les capture."</em></p>
                </div>

                <div class="game-mechanics">
                    <div class="mechanic-card">
                        <h4>🎯 Mécanique : Chasse aux Trésors Numériques</h4>
                        <p><strong>Objectif :</strong> Collecter des "cristaux mathématiques" en résolvant des énigmes numériques.</p>
                        <p><strong>Règles :</strong> Chaque bonne réponse révèle un cristal caché dans la forêt. 5 cristaux = un indice pour le fragment du Sceptre !</p>
                    </div>

                    <div class="mechanic-card">
                        <h4>🧩 Mécanique : Énigmes des Arbres Ancestraux</h4>
                        <p><strong>Concept :</strong> Les arbres de la forêt posent des énigmes progressives de difficulté croissante.</p>
                        <p><strong>Progression :</strong> Facile → Moyen → Difficile (débloque des pouvoirs mathématiques spéciaux)</p>
                    </div>
                </div>
            </div>

            <div class="coach-message">
                <strong>🧮 Salut, Chasseur de Nombres !</strong> Bienvenue dans la Forêt des Nombres Mystérieux !
                Ici, les mathématiques ne sont pas des leçons ennuyeuses, mais une grande aventure pleine de découvertes.
                Chaque opération résolue te donne un cristal magique, chaque énigme maîtrisée te rapproche du Sceptre Unificateur.
                Prêt à explorer cette forêt enchantée ? 🌟
            </div>


            <!-- Afficher les exercices depuis la DB -->
            <p><em>🧮 exercice(s) de Mathématiques disponible(s)</em></p>
            <div class="exercise-subject-group">
                <div class="exercise-grid">

                </div>
            </div>


            <!-- Message pour utilisateurs non connectés -->
            <p><em>Connecte-toi pour voir tous les exercices disponibles !</em></p>

            <!-- Message si DB non disponible -->
            <div class="coach-message">
                <strong>⚠️ Base de données non disponible</strong><br>
                Affichage du contenu statique.
            </div>

            <!-- Aucun exercice trouvé -->
            <div class="coach-message">
                <strong>ℹ️ Aucun exercice trouvé dans la base de données</strong><br>
                Veuillez importer les exercices avec : <code>php tools/import_exercices_to_db.php</code>
            </div>

            <!-- Fallback : Contenu statique -->
            <div class="exercise-card">
                <div class="exercise-header">
                    <h3>🗝️ Énigme de l'Arbre Ancestral - Addition et Soustraction</h3>
                    <span class="difficulty">⭐ FACILE</span>
                    <span class="reward">💎 Récompense : 3 Cristaux Mathématiques</span>
                </div>

                <div class="narrative-context">
                    <p><em>"Au pied du Grand Chêne Centenaire, trois énigmes numériques sont gravées dans l'écorce.
                            Résous-les toutes pour obtenir les cristaux cachés dans ses racines !"</em></p>
                </div>

                <p><strong>Objectif :</strong> Maîtriser les opérations de base avec des nombres entiers et collecter tes premiers cristaux.</p>

                <div class="game-mechanics-exercise">
                    <div class="mechanic-detail">
                        <h4>🎮 Mécanique : Collection de Cristaux</h4>
                        <p>• Chaque bonne réponse = 1 cristal mathématique</p>
                        <p>• 3 cristaux = accès à l'indice pour le Fragment du Sceptre</p>
                        <p>• Bonus : Réponse parfaite en moins de 2 minutes = cristal bonus doré ✨</p>
                    </div>
                </div>

                <div class="tip-box">
                    <h4>🎯 Stratégie de Coach</h4>
                    <p>Pour additionner : aligne les chiffres par la droite. Pour soustraire : commence par la droite,
                        emprunte si nécessaire. Respire et vas-y étape par étape !</p>
                </div>

                <div class="math-exercise"
                    data-questions='[
                     {"question": "456 + 123 = ? <span class=\"hint\">(Le chemin des marchands)</span>", "answer": "579"},
                     {"question": "789 - 234 = ? <span class=\"hint\">(Le trésor caché)</span>", "answer": "555"},
                     {"question": "567 + 89 = ? <span class=\"hint\">(La clairière secrète)</span>", "answer": "656"}
                 ]'>
                    <div class="exercise-content">
                        <p><strong>Énigmes du Grand Chêne :</strong></p>
                        <div class="math-container">
                            <!-- Les champs seront générés dynamiquement par JavaScript -->
                        </div>
                    </div>

                    <button class="btn-coach btn-check-math">🔮 Révéler les Cristaux</button>
                    <div class="math-feedback"></div>
                </div>
            </div>

            <div class="exercise-card">
                <div class="exercise-header">
                    <h3>Multiplication - Tables de Base</h3>
                    <span class="difficulty">⭐⭐ MOYEN</span>
                </div>
                <p><strong>Objectif :</strong> Automatiser les tables de multiplication jusqu'à 10.</p>

                <div class="tip-box">
                    <h4>🧠 Méthode Mémorisation</h4>
                    <p>Répète-toi les tables tous les jours. Utilise des rimes ou des chansons pour retenir.
                        Par exemple : "6x6=36, les petits poissons dans l'eau".</p>
                </div>

                <div class="math-exercise"
                    data-questions='[
                     {"question": "7 × 8 = ?", "answer": "56"},
                     {"question": "9 × 6 = ?", "answer": "54"},
                     {"question": "4 × 9 = ?", "answer": "36"}
                 ]'>
                    <div class="exercise-content">
                        <p><strong>Test rapide : complète ces multiplications</strong></p>
                        <div class="math-container">
                            <!-- Les champs seront générés dynamiquement par JavaScript -->
                        </div>
                    </div>

                    <button class="btn-coach btn-check-math">Vérifier mes réponses</button>
                    <div class="math-feedback"></div>
                </div>
            </div>

        </section>

        <!-- SCIENCES -->
        <section id="sciences">
            <h2>🧬 Sciences - Explorateur du Vivant</h2>

            <div class="coach-message">
                <strong>🔬 Wow ! Les sciences, c'est l'aventure ultime !</strong> Tu vas découvrir comment fonctionne
                notre monde. Chaque observation est une victoire. Sois curieux, pose des questions !
            </div>

            <div class="exercise-card">
                <div class="exercise-header">
                    <h3>Le Corps Humain - Organes Vitaux</h3>
                    <span class="difficulty">⭐ FACILE</span>
                </div>
                <p><strong>Objectif :</strong> Identifier les principales fonctions des organes du corps humain.</p>

                <div class="tip-box">
                    <h4>🔍 Conseil d'Explorateur</h4>
                    <p>Le cœur pompe le sang, les poumons permettent la respiration, le cerveau contrôle tout.
                        Pense à ton corps comme une équipe où chaque organe a son rôle !</p>
                </div>

                <div class="qcm-exercise"
                    data-questions='[
                     {
                         "question": "Associe chaque organe à sa fonction principale : Cœur",
                         "choices": [
                             {"value": "a", "label": "a) Digestion"},
                             {"value": "b", "label": "b) Circulation sanguine"},
                             {"value": "c", "label": "c) Respiration"}
                         ],
                         "correct": "b"
                     },
                     {
                         "question": "Associe chaque organe à sa fonction principale : Poumons",
                         "choices": [
                             {"value": "a", "label": "a) Digestion"},
                             {"value": "b", "label": "b) Circulation sanguine"},
                             {"value": "c", "label": "c) Respiration"}
                         ],
                         "correct": "c"
                     },
                     {
                         "question": "Associe chaque organe à sa fonction principale : Cerveau",
                         "choices": [
                             {"value": "a", "label": "a) Contrôle des fonctions"},
                             {"value": "b", "label": "b) Digestion"},
                             {"value": "c", "label": "c) Respiration"}
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
            <h2>🏛️ Histoire-Géo - Voyageur du Temps</h2>

            <div class="coach-message">
                <strong>⏰ Prêt pour un voyage dans le temps ?</strong> L'histoire, c'est comme un grand roman.
                Les personnages sont réels et les aventures ont vraiment eu lieu. Accroche-toi !
            </div>

            <div class="exercise-card">
                <div class="exercise-header">
                    <h3>L'Antiquité - Civilisations Fondatrices</h3>
                    <span class="difficulty">⭐ FACILE</span>
                </div>
                <p><strong>Objectif :</strong> Situer chronologiquement les grandes civilisations antiques.</p>

                <div class="tip-box">
                    <h4>📅 Astuce Chronologique</h4>
                    <p>Mémorise cette séquence : Égypte ancienne → Grèce antique → Rome antique.
                        Pense aux pyramides, aux Jeux Olympiques, aux gladiateurs !</p>
                </div>

                <div class="chronology-exercise"
                    data-events='[
                     {"text": "Construction des pyramides d&#39;Égypte", "order": 1, "date": "vers 2500 av. J.-C."},
                     {"text": "Jeux Olympiques en Grèce", "order": 2, "date": "à partir de 776 av. J.-C."},
                     {"text": "Chute de l&#39;Empire romain", "order": 3, "date": "476 ap. J.-C."}
                 ]'>
                    <div class="chronology-container">
                        <!-- L'exercice de classement sera généré dynamiquement par JavaScript -->
                    </div>
                    <button class="btn-coach btn-check-chronology">Vérifier mon classement</button>
                    <div class="chronology-feedback"></div>
                </div>
            </div>
        </section>

        <div class="coach-message">
            <strong>🎉 Félicitations champion !</strong> Tu as terminé ta première session d'exercices.
            Chaque réponse correcte te rend plus fort. Souviens-toi : l'important n'est pas la perfection,
            mais le progrès constant. Reviens demain pour de nouveaux défis !
            <br><br>
            <strong>💪 Ton coach croit en toi !</strong>
        </div>

        <!-- CSS pour le système dynamique -->
        <link rel="stylesheet" href="<?php echo function_exists('asset_url') ? asset_url('assets/css/pages/dynamic-exercises.css') : 'assets/css/pages/dynamic-exercises.css'; ?>">

        <!-- Script pour les exercices interactifs (charger AVANT le système dynamique) -->
        <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : 'assets/js/interactive-exercises.js'; ?>"></script>

        <!-- JavaScript pour le système dynamique -->
        <script src="<?php echo function_exists('asset_url') ? asset_url('assets/js/dynamic-exercises.js') : 'assets/js/dynamic-exercises.js'; ?>"></script>

        <script>
            // Initialiser le système dynamique après chargement
            function initDynamicExercises() {
                // Attendre que DynamicExerciseSystem soit disponible
                if (typeof DynamicExerciseSystem !== 'undefined') {
                    const container = document.querySelector('[data-dynamic-exercises]');
                    if (container) {
                        console.log('🎯 Initialisation du système d\'exercices dynamique...');
                        const apiEndpoint = '<?php echo function_exists('site_url') ? site_url('api/get_exercises') : '/index.php?page=api/get_exercises'; ?>';
                        console.log('📡 API Endpoint:', apiEndpoint);

                        try {
                            // Passer le sélecteur (string) au lieu de l'élément directement
                            window.dynamicExerciseSystem = new DynamicExerciseSystem({
                                containerSelector: '[data-dynamic-exercises]', // Sélecteur string
                                level: '6ème',
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

            let completedExercises = 0;
            const totalExercises = 6;
            let collectedCrystals = 0;
            const crystalsNeeded = 15; // Pour obtenir un indice du Sceptre

            function checkExercise(exerciseId) {
                const successDiv = document.getElementById('success-' + exerciseId);
                successDiv.style.display = 'block';
                updateProgress();
            }

            function showCorrection(exerciseId) {
                const correctionDiv = document.getElementById('correction-' + exerciseId);
                correctionDiv.style.display = 'block';

                // Mécanique de récompense pour les maths
                if (exerciseId === 'maths-base') {
                    awardCrystals(3);
                }

                updateProgress();
            }

            // Fonction globale pour attribuer des cristaux (appelée depuis interactive-exercises.js)
            window.awardCrystals = function(amount) {
                collectedCrystals += amount;
                updateCrystalDisplay();

                // Sauvegarder dans localStorage
                if (typeof Storage !== 'undefined') {
                    localStorage.setItem('collectedCrystals_6eme', collectedCrystals.toString());
                }

                // Animation de récompense
                showRewardAnimation(amount);

                // Vérifier si l'objectif est atteint
                if (collectedCrystals >= crystalsNeeded) {
                    unlockScepterHint();
                }

                // Optionnel : sauvegarder sur le serveur via AJAX
                saveProgressToServer();
            };

            // Fonction pour sauvegarder la progression sur le serveur (optionnel)
            function saveProgressToServer() {
                // Cette fonction peut être implémentée pour sauvegarder dans la base de données
                // Pour l'instant, on utilise localStorage
                console.log('Progression sauvegardée :', collectedCrystals, 'cristaux');
            }

            // Récupérer les cristaux depuis le localStorage si disponibles
            if (typeof Storage !== 'undefined') {
                const savedCrystals = localStorage.getItem('collectedCrystals_6eme');
                if (savedCrystals !== null) {
                    collectedCrystals = parseInt(savedCrystals) || 0;
                    updateCrystalDisplay();
                }
            }

            // S'assurer que la fonction est disponible immédiatement
            // (déjà définie ci-dessus comme window.awardCrystals)

            function updateCrystalDisplay() {
                const crystalDisplay = document.getElementById('crystal-count');
                if (crystalDisplay) {
                    crystalDisplay.textContent = collectedCrystals;
                }

                // Mettre à jour la barre de progression des cristaux
                const crystalProgress = (collectedCrystals / crystalsNeeded) * 100;
                const crystalBar = document.getElementById('crystal-progress');
                if (crystalBar) {
                    crystalBar.style.width = Math.min(crystalProgress, 100) + '%';
                }
            }

            function showRewardAnimation(amount) {
                // Créer une animation de cristaux flottants
                const animationDiv = document.createElement('div');
                animationDiv.className = 'crystal-animation';
                animationDiv.innerHTML = `💎 +${amount}`;
                animationDiv.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 2em;
                color: #fbbf24;
                z-index: 1000;
                pointer-events: none;
                animation: crystalFloat 2s ease-out forwards;
            `;

                document.body.appendChild(animationDiv);

                setTimeout(() => {
                    document.body.removeChild(animationDiv);
                }, 2000);
            }

            function unlockScepterHint() {
                const hintDiv = document.getElementById('scepter-hint');
                if (hintDiv) {
                    hintDiv.style.display = 'block';
                    // Animation d'apparition
                    hintDiv.style.animation = 'hintAppear 1s ease-out';
                }
            }

            function updateProgress() {
                completedExercises++;
                const percentage = Math.round((completedExercises / totalExercises) * 100);
                document.getElementById('globalProgress').style.width = percentage + '%';
                document.getElementById('progressText').textContent = percentage + '%';
            }

            // Animation CSS pour les cristaux
            const crystalAnimationStyle = document.createElement('style');
            crystalAnimationStyle.textContent = `
            @keyframes crystalFloat {
                0% { transform: translate(-50%, -50%) scale(0.5); opacity: 1; }
                50% { transform: translate(-50%, -60%) scale(1.2); opacity: 1; }
                100% { transform: translate(-50%, -100%) scale(1); opacity: 0; }
            }
            @keyframes hintAppear {
                0% { transform: scale(0.8); opacity: 0; }
                100% { transform: scale(1); opacity: 1; }
            }
        `;
            document.head.appendChild(crystalAnimationStyle);
        </script>

        <!-- Colibri désactivé (remplacé par Coach WebM) -->
        <!-- ancien: colibri-mascot.css -->

        <!-- ancien: colibri-mascot.js (désactivé) -->




        <!-- Coach WebM -->
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
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (max-width: 480px) {
                .coach-overlay {
                    width: 180px;
                    bottom: 10px;
                    right: 10px;
                }
            }

            /* Désactiver toute ancienne mascotte Colibri si présente */
            .colibri-mascot-container,
            .colibri-mascot-global,
            [data-colibri],
            [data-colibri-global] {
                display: none !important;
            }

            </main><?php include __DIR__ . '/../../../../includes/footer.php'; ?>