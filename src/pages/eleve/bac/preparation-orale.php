<?php
$page_title = 'Préparation aux Épreuves Orales - MonCoachScolaire';
$page_theme_level = 'bac';
$page_css = 'bac/preparation-orale.css';

// Charger les fichiers nécessaires
if (!isset($pdo)) {
    require_once __DIR__ . '/../../../config/config.php';
}
if (is_file(__DIR__ . '/../../../config/site_boot.php')) {
    require_once __DIR__ . '/../../../config/site_boot.php';
}

// Charger la fonction de navigation entre niveaux
if (is_file(__DIR__ . '/../../../includes/level_navigation.php')) {
    require_once __DIR__ . '/../../../includes/level_navigation.php';
}

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(__DIR__ . '/../../../includes/admin_auth.php')) {
    require_once __DIR__ . '/../../../includes/admin_auth.php';
}

// Charger les fonctions de sécurité pour le compte démo
if (is_file(__DIR__ . '/../../../includes/demo_security.php')) {
    require_once __DIR__ . '/../../../includes/demo_security.php';
}

// Vérifier si l'utilisateur est connecté OU s'il est admin
// Le compte démo a aussi accès mais avec limitation (voir plus bas)
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$is_logged_in = !empty($_SESSION['user_id']) && !empty($_SESSION['logged_in']);

// Exclure le compte démo de l'accès complet : le mode démo doit rester une démo (aperçu seulement)
// Si l'utilisateur est en mode démo, on le considère comme non connecté pour l'accès complet.
$has_access = ((!empty($is_logged_in) && !$is_demo) || $is_admin);

$user_level = 'BAC';
$level_normalized = normalize_level_for_url($user_level);
?>

<main class="min-h-screen bg-gray-50">
    <?php

?>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4 flex items-center justify-center gap-3">
                <span class="text-6xl">🎤</span>
                Préparation aux Épreuves Orales
            </h1>
            <p class="text-xl text-gray-600 mb-6">Tous les conseils pour réussir le Grand Oral et l'oral de français !</p>

            <!-- Navigation Buttons -->
            <div class="flex flex-wrap justify-center gap-4">
                <a href="<?php echo site_url('bac/exercices-bac'); ?>"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                    <span class="mr-2">📝</span>
                    Exercices BAC
                </a>
                <a href="<?php echo site_url('cours', ['niveau' => 'bac']); ?>"
                   class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors shadow-lg">
                    <span class="mr-2">📚</span>
                    Cours BAC
                </a>
                <a href="<?php echo site_url('bac/bac-accueil'); ?>"
                   class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition-colors shadow-lg">
                    <span class="mr-2">🏠</span>
                    Accueil BAC
                </a>
                <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>"
                   class="inline-flex items-center px-6 py-3 bg-yellow-600 text-white font-semibold rounded-lg hover:bg-yellow-700 transition-colors shadow-lg">
                    <span class="mr-2">📊</span>
                    Mon Dashboard
                </a>
            </div>
        </div>

    <?php if (!$has_access): ?>
        <!-- Contenu pour les utilisateurs NON connectés -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-8 mb-8 border border-indigo-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                <span class="text-3xl">🔒</span>
                Débloque l'accès complet à la préparation orale
            </h2>
            <p class="text-gray-700 mb-6">
                Tu peux voir ci-dessous un aperçu de nos ressources pour la préparation aux épreuves orales,
                mais pour accéder à l'accompagnement personnalisé avec ton coach, tu dois créer un compte gratuit !
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span>👨‍🏫</span>
                        Coach Personnel
                    </h3>
                    <p class="text-sm text-gray-600">Accompagnement personnalisé pour ta préparation orale avec conseils adaptés</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span>🎤</span>
                        Simulations Guidées
                    </h3>
                    <p class="text-sm text-gray-600">Entraîne-toi avec des simulations d'oraux et reçois des retours constructifs</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span>📊</span>
                        Suivi de ta Progression
                    </h3>
                    <p class="text-sm text-gray-600">Dashboard pour suivre ta progression et tes points d'amélioration</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm md:col-span-2 lg:col-span-1">
                    <h3 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                        <span>🎯</span>
                        Ressources Exclusives
                    </h3>
                    <p class="text-sm text-gray-600">Accès à des annales, des stratégies éprouvées et des techniques de maîtres</p>
                </div>
            </div>

            <p class="text-center text-gray-700 font-semibold mb-6">
                <span class="text-lg">💡</span> La clé pour réussir les épreuves orales : une préparation structurée et un accompagnement personnalisé !
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo site_url('register'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors shadow-lg">
                    <span class="mr-2">✨</span>
                    Créer mon compte gratuit
                </a>
                <a href="<?php echo site_url('login'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors shadow-lg">
                    <span class="mr-2">🔑</span>
                    Me connecter
                </a>
            </div>
            <?php if ($is_demo && !$is_admin): ?>
                <p class="text-center text-sm text-indigo-600 italic mt-4">💡 Mode démo : aperçu limité seulement.</p>
            <?php endif; ?>
        </div>

        <!-- Aperçu du contenu -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
            <strong class="text-indigo-800">🔍 Aperçu du contenu disponible :</strong><br>
            <div class="text-indigo-700 mt-2">
                • Techniques complètes pour le Grand Oral<br>
                • Stratégies pour l'oral de français<br>
                • Gestion du stress et préparation mentale<br>
                • Conseils de communication professionnelle
            </div>
        </div>
    <?php else: ?>
        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <?php if ($has_access): ?>
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
            <strong class="text-indigo-800">👋 Salut <?php
            $display_name = $user_name ?? $_SESSION['user_name'] ?? 'Élève';
            echo htmlspecialchars($display_name);
            ?> !</strong><br>
            <span class="text-indigo-700">Bienvenue dans ta préparation aux épreuves orales ! Ces ressources complètes t'accompagneront
            pour réussir le Grand Oral, l'oral de français et maîtriser l'art de la communication orale.
            <?php if ($is_demo && !$is_admin): ?>
            <br><small class="text-indigo-600 italic">💡 Mode démo : tu as accès à un aperçu de la préparation orale. Crée un compte pour accéder à tous les conseils et simulations !</small>
            <?php endif; ?></span>
        </div>

        <!-- Introduction spécifique Préparation Orale -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-6 mb-8 border border-indigo-200">
            <h3 class="text-xl font-bold text-indigo-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Prépare-toi à Réussir Tes Épreuves Orales
            </h3>
            <p class="text-indigo-700 mb-4">
                Les épreuves orales demandent une préparation spécifique et une maîtrise de techniques particulières.
                Cette page te fournit tous les conseils, les structures de présentation et les stratégies éprouvées pour exceller :
            </p>
            <ul class="text-indigo-700 space-y-2">
                <li class="flex items-center gap-2">
                    <span>🎤</span>
                    <strong>Grand Oral</strong> : Techniques de présentation, gestion du stress, argumentation
                </li>
                <li class="flex items-center gap-2">
                    <span>📖</span>
                    <strong>Oral de Français</strong> : Explication de texte, analyse littéraire, expression orale
                </li>
                <li class="flex items-center gap-2">
                    <span>🗣️</span>
                    <strong>Communication Orale</strong> : Diction, posture, contact visuel, gestion du tempo
                </li>
                <li>💭 <strong>Préparation Mentale</strong> : Visualisation, routine, gestion de l'anxiété</li>
                <li>✅ <strong>Entraînement Pratique</strong> : Simulations, fiches de révision, exercices</li>
            </ul>
        </div>

        <!-- Section Épreuves rapide -->
        <section class="quick-nav-section">
            <div class="section-header">
                <h2>📋 Choisis ton épreuve</h2>
                <p>Accède directement aux ressources pour l'épreuve que tu prépares.</p>
            </div>
            <div class="epreuves-cards-grid">
                <a href="#grand-oral" class="epreuve-quick-card">
                    <div class="card-icon">🎤</div>
                    <h3>Grand Oral</h3>
                    <p>Terminale • 20 min</p>
                </a>
                <a href="#oral-francais" class="epreuve-quick-card">
                    <div class="card-icon">📖</div>
                    <h3>Oral de Français</h3>
                    <p>Première • 20 min</p>
                </a>
                <a href="#conseils-generaux" class="epreuve-quick-card">
                    <div class="card-icon">💡</div>
                    <h3>Conseils Généraux</h3>
                    <p>Techniques communes</p>
                </a>
                <a href="#ressources" class="epreuve-quick-card">
                    <div class="card-icon">📚</div>
                    <h3>Ressources</h3>
                    <p>Pour aller plus loin</p>
                </a>
            </div>
        </section>

        <section class="content-section">
            <!-- GRAND ORAL (Terminale) -->
            <div id="grand-oral" class="epreuve-section">
                <div class="section-title-wrapper">
                    <h2>🎯 Le Grand Oral du Baccalauréat</h2>
                    <span class="badge">Terminale</span>
                </div>
                <p class="intro-text">Le Grand Oral est une épreuve phare du nouveau baccalauréat. Elle évalue ta capacité à t'exprimer clairement, à argumenter et à présenter ton projet d'orientation.</p>

                <div class="epreuve-structure">
                    <h3>📋 Structure de l'épreuve (20 minutes)</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker">1</div>
                            <div class="timeline-content">
                                <h4>Présentation (5 minutes)</h4>
                                <p>Tu exposes une question préparée en lien avec l'une de tes spécialités. Cette question doit être pertinente, originale et te passionner.</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker">2</div>
                            <div class="timeline-content">
                                <h4>Échange avec le jury (10 minutes)</h4>
                                <p>Le jury approfondit le sujet en posant des questions. C'est le moment de montrer ta capacité à argumenter et à réfléchir.</p>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker">3</div>
                            <div class="timeline-content">
                                <h4>Discussion sur le projet d'orientation (5 minutes)</h4>
                                <p>Tu présentes ton projet professionnel ou de poursuite d'études en montrant les liens avec tes spécialités.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="conseils-box">
                    <h3>✅ Conseils pour réussir le Grand Oral</h3>

                    <div class="conseil-card">
                        <h4>1. Choisir une question pertinente</h4>
                        <ul>
                            <li>Sélectionne une question qui te passionne et qui est en lien avec tes spécialités</li>
                            <li>Assure-toi que la question soit originale et permette un développement approfondi</li>
                            <li>Prépare 2-3 questions différentes pour avoir un plan B</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>2. Structurer ta présentation</h4>
                        <ul>
                            <li><strong>Introduction</strong> : Accroche, présentation de la question, annonce du plan</li>
                            <li><strong>Développement</strong> : Arguments structurés avec exemples concrets</li>
                            <li><strong>Conclusion</strong> : Synthèse et ouverture sur ton projet d'orientation</li>
                            <li>Apprends par cœur l'introduction et la conclusion pour démarrer et terminer avec assurance</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>3. S'entraîner régulièrement</h4>
                        <ul>
                            <li>Pratique ta présentation devant des amis, ta famille ou tes enseignants</li>
                            <li>Enregistre-toi pour identifier tes points d'amélioration</li>
                            <li>Fais des simulations complètes dans les conditions réelles (20 minutes)</li>
                            <li>Commence l'entraînement 2 à 4 semaines avant l'épreuve</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>4. Soigner ton expression orale et ta posture</h4>
                        <ul>
                            <li><strong>Diction</strong> : Parle clairement, articule bien, contrôle ton débit</li>
                            <li><strong>Respiration</strong> : Respire profondément avant de commencer et entre les parties</li>
                            <li><strong>Gestuelle</strong> : Adopte une posture ouverte, utilise tes mains pour appuyer tes propos</li>
                            <li><strong>Contact visuel</strong> : Regarde les membres du jury dans les yeux</li>
                            <li><strong>Voix</strong> : Varie les intonations pour rendre ton discours vivant</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>5. Gérer le stress</h4>
                        <ul>
                            <li><strong>Techniques de relaxation</strong> : Respiration profonde, visualisation positive</li>
                            <li><strong>Préparation</strong> : Une bonne préparation réduit l'anxiété</li>
                            <li><strong>Routine</strong> : Établis une routine de préparation le jour J</li>
                            <li><strong>Pensée positive</strong> : Visualise-toi en train de réussir</li>
                            <li><strong>Acceptation</strong> : Accepte que le stress soit normal et utilise-le comme énergie</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>6. Anticiper les questions du jury</h4>
                        <ul>
                            <li>Réfléchis aux questions potentielles que le jury pourrait poser</li>
                            <li>Prépare des réponses argumentées pour chaque question</li>
                            <li>Sois prêt à approfondir certains aspects de ton sujet</li>
                            <li>Montre ta capacité à réfléchir et à interagir de manière pertinente</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>7. Préparer la partie sur le projet d'orientation</h4>
                        <ul>
                            <li>Explique clairement ton projet d'études ou professionnel</li>
                            <li>Mets en avant les liens avec tes spécialités</li>
                            <li>Montre la cohérence de ton parcours</li>
                            <li>Exprime tes motivations de manière convaincante</li>
                            <li>Sois prêt à justifier tes choix</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ORAL DE FRANÇAIS (Première) -->
            <div id="oral-francais" class="epreuve-section">
                <div class="section-title-wrapper">
                    <h2>📖 L'Épreuve Orale de Français (Première)</h2>
                    <span class="badge">Première</span>
                </div>
                <p class="intro-text">L'oral de français est une épreuve anticipée du baccalauréat. Elle se déroule en deux parties et nécessite une préparation méthodique.</p>

                <div class="epreuve-structure">
                    <h3>📋 Structure de l'épreuve (20 minutes)</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker">1</div>
                            <div class="timeline-content">
                                <h4>Explication de texte et question de grammaire (12 minutes)</h4>
                                <ul>
                                    <li><strong>Lecture à voix haute</strong> : Lecture claire et expressive du texte choisi</li>
                                    <li><strong>Explication linéaire</strong> : Analyse du texte en suivant sa progression, mise en évidence des procédés littéraires</li>
                                    <li><strong>Question de grammaire</strong> : Réponse à une question portant sur un aspect grammatical du texte</li>
                                </ul>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-marker">2</div>
                            <div class="timeline-content">
                                <h4>Présentation d'une œuvre et entretien (8 minutes)</h4>
                                <ul>
                                    <li><strong>Présentation</strong> : Exposé bref d'une œuvre littéraire de ton choix, explication de ton intérêt</li>
                                    <li><strong>Entretien</strong> : Échange avec l'examinateur sur l'œuvre présentée, approfondissement de certains aspects</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="conseils-box">
                    <h3>✅ Conseils pour réussir l'oral de français</h3>

                    <div class="conseil-card">
                        <h4>1. Préparation pendant l'année scolaire</h4>
                        <ul>
                            <li><strong>Fiches de révision</strong> : Après l'étude de chaque texte, élabore des fiches synthétiques incluant :
                                <ul>
                                    <li>Les points clés du texte</li>
                                    <li>Les procédés littéraires utilisés</li>
                                    <li>Des citations pertinentes</li>
                                    <li>Le contexte historique et littéraire</li>
                                </ul>
                            </li>
                            <li><strong>Entraînement à l'oral</strong> : Pratique régulièrement l'explication de texte à l'oral, seul ou avec des camarades</li>
                            <li><strong>Lecture des œuvres</strong> : Lis attentivement toutes les œuvres au programme</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>2. Pendant les 30 minutes de préparation</h4>
                        <ul>
                            <li><strong>Lecture attentive</strong> : Relis le texte en identifiant les passages clés et les procédés littéraires</li>
                            <li><strong>Plan détaillé</strong> : Élabore un plan structuré pour ton explication, note les idées principales et les exemples</li>
                            <li><strong>Introduction et conclusion</strong> : Rédige ces parties pour assurer une entrée en matière et une clôture efficaces</li>
                            <li><strong>Gestion du temps</strong> : Réserve 5 minutes pour relire et finaliser</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>3. Conseils pour l'épreuve orale</h4>
                        <ul>
                            <li><strong>Gestion du temps</strong> : Respecte le temps imparti pour chaque partie (12 min + 8 min)</li>
                            <li><strong>Clarté et articulation</strong> : Parle distinctement, articule bien pour faciliter la compréhension</li>
                            <li><strong>Posture et regard</strong> : Adopte une posture ouverte, maintiens un contact visuel avec l'examinateur</li>
                            <li><strong>Gestion du stress</strong> : Respire profondément, concentre-toi sur ta préparation</li>
                            <li><strong>Structure</strong> : Suis un plan clair : introduction, développement, conclusion</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>4. Pour la présentation de l'œuvre</h4>
                        <ul>
                            <li>Choisis une œuvre que tu as vraiment lue et qui t'a marqué(e)</li>
                            <li>Prépare un exposé structuré : présentation, analyse, intérêt personnel</li>
                            <li>Sois prêt(e) à répondre à des questions sur l'auteur, le contexte, les thèmes</li>
                            <li>Montre ta capacité à analyser et à argumenter</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- CONSEILS GÉNÉRAUX -->
            <div id="conseils-generaux" class="epreuve-section">
                <div class="section-title-wrapper">
                    <h2>💡 Conseils Généraux pour Toutes les Épreuves Orales</h2>
                    <span class="badge badge-general">Tous niveaux</span>
                </div>

                <div class="conseils-box">
                    <div class="conseil-card">
                        <h4>1. La préparation mentale</h4>
                        <ul>
                            <li>Visualise-toi en train de réussir l'épreuve</li>
                            <li>Établis une routine de préparation les jours précédents</li>
                            <li>Dors suffisamment la veille de l'épreuve</li>
                            <li>Mange équilibré et évite les excitants (café, énergie drinks)</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>2. Le jour J</h4>
                        <ul>
                            <li>Arrive en avance pour te familiariser avec le lieu</li>
                            <li>Porte une tenue soignée et confortable</li>
                            <li>Apporte tout le matériel nécessaire (textes, fiches, eau)</li>
                            <li>Respire profondément avant d'entrer dans la salle</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>3. Techniques de communication</h4>
                        <ul>
                            <li><strong>Voix</strong> : Varie les intonations, contrôle le volume</li>
                            <li><strong>Regard</strong> : Regarde l'examinateur dans les yeux, balaie l'audience</li>
                            <li><strong>Gestes</strong> : Utilise tes mains pour appuyer tes propos, évite les gestes parasites</li>
                            <li><strong>Posture</strong> : Tiens-toi droit(e), adopte une posture ouverte et confiante</li>
                        </ul>
                    </div>

                    <div class="conseil-card">
                        <h4>4. Gérer les imprévus</h4>
                        <ul>
                            <li>Si tu oublies quelque chose, respire et reprends calmement</li>
                            <li>Si tu ne comprends pas une question, demande poliment une précision</li>
                            <li>Si tu es stressé(e), prends quelques secondes pour respirer</li>
                            <li>Reste calme et professionnel(le) en toutes circonstances</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- RESSOURCES -->
            <div id="ressources" class="epreuve-section">
                <div class="section-title-wrapper">
                    <h2>📚 Ressources Complémentaires</h2>
                    <span class="badge badge-resources">🔗 Liens utiles</span>
                </div>
                <div class="ressources-box">
                    <p>Pour approfondir ta préparation, n'hésite pas à :</p>
                    <ul>
                        <li>Consulter les annales des années précédentes</li>
                        <li>Regarder des vidéos de simulations d'oraux</li>
                        <li>Participer aux séances d'entraînement organisées par ton établissement</li>
                        <li>Demander conseil à tes professeurs</li>
                        <li>Échanger avec d'anciens élèves qui ont réussi</li>
                    </ul>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>
        <!-- Contenu COMPLET pour les utilisateurs connectés -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-6">
            <strong class="text-indigo-800">👋 Salut <?php
                $display_name = $user_name ?? $_SESSION['user_name'] ?? 'Élève';
echo htmlspecialchars($display_name);
?> !</strong><br>
            <span class="text-indigo-700">Bienvenue dans ta préparation aux épreuves orales ! Ces ressources complètes t'accompagneront
            pour réussir le Grand Oral, l'oral de français et maîtriser l'art de la communication orale.
            <?php if ($is_demo && !$is_admin): ?>
            <br><small class="text-indigo-600 italic">💡 Mode démo : tu as accès à un aperçu de la préparation orale. Crée un compte pour accéder à tous les conseils et simulations !</small>
            <?php endif; ?></span>
        </div>

        <!-- Introduction spécifique Préparation Orale -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-6 mb-8 border border-indigo-200">
            <h3 class="text-xl font-bold text-indigo-800 mb-3 flex items-center gap-2">
                <span>🎯</span>
                Prépare-toi à Réussir Tes Épreuves Orales
            </h3>
            <p class="text-indigo-700 mb-4">
                Les épreuves orales demandent une préparation spécifique et une maîtrise de techniques particulières.
                Cette page te fournit tous les conseils, les structures de présentation et les stratégies éprouvées pour exceller :
            </p>
            <ul class="text-indigo-700 space-y-2">
                <li class="flex items-center gap-2">
                    <span>🎤</span>
                    <strong>Grand Oral</strong> : Techniques de présentation, gestion du stress, argumentation
                </li>
                <li class="flex items-center gap-2">
                    <span>📖</span>
                    <strong>Oral de Français</strong> : Explication de texte, analyse littéraire, expression orale
                </li>
                <li class="flex items-center gap-2">
                    <span>🗣️</span>
                    <strong>Communication Orale</strong> : Diction, posture, contact visuel, gestion du tempo
                </li>
                <li>💭 <strong>Préparation Mentale</strong> : Visualisation, routine, gestion de l'anxiété</li>
                <li>✅ <strong>Entraînement Pratique</strong> : Simulations, fiches de révision, exercices</li>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($has_access): ?>
    <!-- Section Épreuves rapide -->
    <section class="quick-nav-section">
        <div class="section-header">
            <h2>📋 Choisis ton épreuve</h2>
            <p>Accède directement aux ressources pour l'épreuve que tu prépares.</p>
        </div>
        <div class="epreuves-cards-grid">
            <a href="#grand-oral" class="epreuve-quick-card">
                <div class="card-icon">🎤</div>
                <h3>Grand Oral</h3>
                <p>Terminale • 20 min</p>
            </a>
            <a href="#oral-francais" class="epreuve-quick-card">
                <div class="card-icon">📖</div>
                <h3>Oral de Français</h3>
                <p>Première • 20 min</p>
            </a>
            <a href="#conseils-generaux" class="epreuve-quick-card">
                <div class="card-icon">💡</div>
                <h3>Conseils Généraux</h3>
                <p>Techniques communes</p>
            </a>
            <a href="#ressources" class="epreuve-quick-card">
                <div class="card-icon">📚</div>
                <h3>Ressources</h3>
                <p>Pour aller plus loin</p>
            </a>
        </div>
    </section>
    <?php endif; ?>

    <section class="content-section">
        <!-- GRAND ORAL (Terminale) -->
        <div id="grand-oral" class="epreuve-section">
            <div class="section-title-wrapper">
                <h2>🎯 Le Grand Oral du Baccalauréat</h2>
                <span class="badge">Terminale</span>
            </div>
            <p class="intro-text">Le Grand Oral est une épreuve phare du nouveau baccalauréat. Elle évalue ta capacité à t'exprimer clairement, à argumenter et à présenter ton projet d'orientation.</p>

            <div class="epreuve-structure">
                <h3>📋 Structure de l'épreuve (20 minutes)</h3>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker">1</div>
                        <div class="timeline-content">
                            <h4>Présentation (5 minutes)</h4>
                            <p>Tu exposes une question préparée en lien avec l'une de tes spécialités. Cette question doit être pertinente, originale et te passionner.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">2</div>
                        <div class="timeline-content">
                            <h4>Échange avec le jury (10 minutes)</h4>
                            <p>Le jury approfondit le sujet en posant des questions. C'est le moment de montrer ta capacité à argumenter et à réfléchir.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">3</div>
                        <div class="timeline-content">
                            <h4>Discussion sur le projet d'orientation (5 minutes)</h4>
                            <p>Tu présentes ton projet professionnel ou de poursuite d'études en montrant les liens avec tes spécialités.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="conseils-box">
                <h3>✅ Conseils pour réussir le Grand Oral</h3>

                <div class="conseil-card">
                    <h4>1. Choisir une question pertinente</h4>
                    <ul>
                        <li>Sélectionne une question qui te passionne et qui est en lien avec tes spécialités</li>
                        <li>Assure-toi que la question soit originale et permette un développement approfondi</li>
                        <li>Prépare 2-3 questions différentes pour avoir un plan B</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>2. Structurer ta présentation</h4>
                    <ul>
                        <li><strong>Introduction</strong> : Accroche, présentation de la question, annonce du plan</li>
                        <li><strong>Développement</strong> : Arguments structurés avec exemples concrets</li>
                        <li><strong>Conclusion</strong> : Synthèse et ouverture sur ton projet d'orientation</li>
                        <li>Apprends par cœur l'introduction et la conclusion pour démarrer et terminer avec assurance</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>3. S'entraîner régulièrement</h4>
                    <ul>
                        <li>Pratique ta présentation devant des amis, ta famille ou tes enseignants</li>
                        <li>Enregistre-toi pour identifier tes points d'amélioration</li>
                        <li>Fais des simulations complètes dans les conditions réelles (20 minutes)</li>
                        <li>Commence l'entraînement 2 à 4 semaines avant l'épreuve</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>4. Soigner ton expression orale et ta posture</h4>
                    <ul>
                        <li><strong>Diction</strong> : Parle clairement, articule bien, contrôle ton débit</li>
                        <li><strong>Respiration</strong> : Respire profondément avant de commencer et entre les parties</li>
                        <li><strong>Gestuelle</strong> : Adopte une posture ouverte, utilise tes mains pour appuyer tes propos</li>
                        <li><strong>Contact visuel</strong> : Regarde les membres du jury dans les yeux</li>
                        <li><strong>Voix</strong> : Varie les intonations pour rendre ton discours vivant</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>5. Gérer le stress</h4>
                    <ul>
                        <li><strong>Techniques de relaxation</strong> : Respiration profonde, visualisation positive</li>
                        <li><strong>Préparation</strong> : Une bonne préparation réduit l'anxiété</li>
                        <li><strong>Routine</strong> : Établis une routine de préparation le jour J</li>
                        <li><strong>Pensée positive</strong> : Visualise-toi en train de réussir</li>
                        <li><strong>Acceptation</strong> : Accepte que le stress soit normal et utilise-le comme énergie</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>6. Anticiper les questions du jury</h4>
                    <ul>
                        <li>Réfléchis aux questions potentielles que le jury pourrait poser</li>
                        <li>Prépare des réponses argumentées pour chaque question</li>
                        <li>Sois prêt à approfondir certains aspects de ton sujet</li>
                        <li>Montre ta capacité à réfléchir et à interagir de manière pertinente</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>7. Préparer la partie sur le projet d'orientation</h4>
                    <ul>
                        <li>Explique clairement ton projet d'études ou professionnel</li>
                        <li>Mets en avant les liens avec tes spécialités</li>
                        <li>Montre la cohérence de ton parcours</li>
                        <li>Exprime tes motivations de manière convaincante</li>
                        <li>Sois prêt à justifier tes choix</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ORAL DE FRANÇAIS (Première) -->
        <div id="oral-francais" class="epreuve-section">
            <div class="section-title-wrapper">
                <h2>📖 L'Épreuve Orale de Français (Première)</h2>
                <span class="badge">Première</span>
            </div>
            <p class="intro-text">L'oral de français est une épreuve anticipée du baccalauréat. Elle se déroule en deux parties et nécessite une préparation méthodique.</p>

            <div class="epreuve-structure">
                <h3>📋 Structure de l'épreuve (20 minutes)</h3>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker">1</div>
                        <div class="timeline-content">
                            <h4>Explication de texte et question de grammaire (12 minutes)</h4>
                            <ul>
                                <li><strong>Lecture à voix haute</strong> : Lecture claire et expressive du texte choisi</li>
                                <li><strong>Explication linéaire</strong> : Analyse du texte en suivant sa progression, mise en évidence des procédés littéraires</li>
                                <li><strong>Question de grammaire</strong> : Réponse à une question portant sur un aspect grammatical du texte</li>
                            </ul>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">2</div>
                        <div class="timeline-content">
                            <h4>Présentation d'une œuvre et entretien (8 minutes)</h4>
                            <ul>
                                <li><strong>Présentation</strong> : Exposé bref d'une œuvre littéraire de ton choix, explication de ton intérêt</li>
                                <li><strong>Entretien</strong> : Échange avec l'examinateur sur l'œuvre présentée, approfondissement de certains aspects</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="conseils-box">
                <h3>✅ Conseils pour réussir l'oral de français</h3>

                <div class="conseil-card">
                    <h4>1. Préparation pendant l'année scolaire</h4>
                    <ul>
                        <li><strong>Fiches de révision</strong> : Après l'étude de chaque texte, élabore des fiches synthétiques incluant :
                            <ul>
                                <li>Les points clés du texte</li>
                                <li>Les procédés littéraires utilisés</li>
                                <li>Des citations pertinentes</li>
                                <li>Le contexte historique et littéraire</li>
                            </ul>
                        </li>
                        <li><strong>Entraînement à l'oral</strong> : Pratique régulièrement l'explication de texte à l'oral, seul ou avec des camarades</li>
                        <li><strong>Lecture des œuvres</strong> : Lis attentivement toutes les œuvres au programme</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>2. Pendant les 30 minutes de préparation</h4>
                    <ul>
                        <li><strong>Lecture attentive</strong> : Relis le texte en identifiant les passages clés et les procédés littéraires</li>
                        <li><strong>Plan détaillé</strong> : Élabore un plan structuré pour ton explication, note les idées principales et les exemples</li>
                        <li><strong>Introduction et conclusion</strong> : Rédige ces parties pour assurer une entrée en matière et une clôture efficaces</li>
                        <li><strong>Gestion du temps</strong> : Réserve 5 minutes pour relire et finaliser</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>3. Conseils pour l'épreuve orale</h4>
                    <ul>
                        <li><strong>Gestion du temps</strong> : Respecte le temps imparti pour chaque partie (12 min + 8 min)</li>
                        <li><strong>Clarté et articulation</strong> : Parle distinctement, articule bien pour faciliter la compréhension</li>
                        <li><strong>Posture et regard</strong> : Adopte une posture ouverte, maintiens un contact visuel avec l'examinateur</li>
                        <li><strong>Gestion du stress</strong> : Respire profondément, concentre-toi sur ta préparation</li>
                        <li><strong>Structure</strong> : Suis un plan clair : introduction, développement, conclusion</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>4. Pour la présentation de l'œuvre</h4>
                    <ul>
                        <li>Choisis une œuvre que tu as vraiment lue et qui t'a marqué(e)</li>
                        <li>Prépare un exposé structuré : présentation, analyse, intérêt personnel</li>
                        <li>Sois prêt(e) à répondre à des questions sur l'auteur, le contexte, les thèmes</li>
                        <li>Montre ta capacité à analyser et à argumenter</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONSEILS GÉNÉRAUX -->
        <div id="conseils-generaux" class="epreuve-section">
            <div class="section-title-wrapper">
                <h2>💡 Conseils Généraux pour Toutes les Épreuves Orales</h2>
                <span class="badge badge-general">Tous niveaux</span>
            </div>

            <div class="conseils-box">
                <div class="conseil-card">
                    <h4>1. La préparation mentale</h4>
                    <ul>
                        <li>Visualise-toi en train de réussir l'épreuve</li>
                        <li>Établis une routine de préparation les jours précédents</li>
                        <li>Dors suffisamment la veille de l'épreuve</li>
                        <li>Mange équilibré et évite les excitants (café, énergie drinks)</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>2. Le jour J</h4>
                    <ul>
                        <li>Arrive en avance pour te familiariser avec le lieu</li>
                        <li>Porte une tenue soignée et confortable</li>
                        <li>Apporte tout le matériel nécessaire (textes, fiches, eau)</li>
                        <li>Respire profondément avant d'entrer dans la salle</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>3. Techniques de communication</h4>
                    <ul>
                        <li><strong>Voix</strong> : Varie les intonations, contrôle le volume</li>
                        <li><strong>Regard</strong> : Regarde l'examinateur dans les yeux, balaie l'audience</li>
                        <li><strong>Gestes</strong> : Utilise tes mains pour appuyer tes propos, évite les gestes parasites</li>
                        <li><strong>Posture</strong> : Tiens-toi droit(e), adopte une posture ouverte et confiante</li>
                    </ul>
                </div>

                <div class="conseil-card">
                    <h4>4. Gérer les imprévus</h4>
                    <ul>
                        <li>Si tu oublies quelque chose, respire et reprends calmement</li>
                        <li>Si tu ne comprends pas une question, demande poliment une précision</li>
                        <li>Si tu es stressé(e), prends quelques secondes pour respirer</li>
                        <li>Reste calme et professionnel(le) en toutes circonstances</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- RESSOURCES -->
        <div id="ressources" class="epreuve-section">
            <div class="section-title-wrapper">
                <h2>📚 Ressources Complémentaires</h2>
                <span class="badge badge-resources">🔗 Liens utiles</span>
            </div>
            <div class="ressources-box">
                <p>Pour approfondir ta préparation, n'hésite pas à :</p>
                <ul>
                    <li>Consulter les annales des années précédentes</li>
                    <li>Regarder des vidéos de simulations d'oraux</li>
                    <li>Participer aux séances d'entraînement organisées par ton établissement</li>
                    <li>Demander conseil à tes professeurs</li>
                    <li>Échanger avec d'anciens élèves qui ont réussi</li>
                </ul>
            </div>
        </div>
    </section>
</div>
</main>
