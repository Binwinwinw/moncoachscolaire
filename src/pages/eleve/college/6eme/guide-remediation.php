<?php
$page_title = 'Guide de remédiation 6ème - MonCoachScolaire';
$page_css = null;

// Charger les fichiers nécessaires
if (!isset($pdo)) {
    if (is_file(dirname(__DIR__, 4) . '/config.php')) {
        require_once dirname(__DIR__, 4) . '/config.php';
    }
}
if (is_file(dirname(__DIR__, 4) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 4) . '/config/site_boot.php';
}

// Charger la fonction de navigation entre niveaux
if (is_file(dirname(__DIR__, 4) . '/includes/level_navigation.php')) {
    require_once dirname(__DIR__, 4) . '/includes/level_navigation.php';
}

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(__DIR__ . '/../../../includes/admin_auth.php')) {
    require_once __DIR__ . '/../../../includes/admin_auth.php';
}

// Vérifier si l'utilisateur est connecté OU s'il est admin
$is_admin = function_exists('isAdmin') && isAdmin();
$has_access = !empty($is_logged_in) || $is_admin;
$user_level = $_SESSION['user_level'] ?? '6ème';
$level_normalized = normalize_level_for_url($user_level);
$theme = get_theme_variant_by_level($user_level);
$subject_tones = $theme['soft_buttons'];
$modal_close_hover = $theme['modal_close_hover'];
$modal_title = $theme['modal_title'];
$modal_heading = $theme['modal_heading'];
$modal_advice = $theme['modal_advice'];
?>

<main class="main-content app-bg bg-gray-50 min-h-screen">
    <!-- Navigation entre niveaux supprimée -->
    <div class="header text-center py-8">
        <h1 class="text-3xl md:text-4xl font-bold <?php echo $theme['title']; ?> mb-2">📚 Guide de remédiation 6ème</h1>
        <p class="text-lg md:text-xl <?php echo $theme['subtitle']; ?> mb-6 font-medium">Programmes 2025 – Accompagnement personnalisé pour réussir ta 6ème</p>

        <!-- Boutons de navigation -->
        <div class="guide-navigation flex flex-wrap justify-center gap-4 mt-6">
            <a href="<?php echo site_url('college/6eme/exercices-6eme'); ?>" class="nav-btn nav-exercices <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes exercices 6eme">📝 Mes Exercices</a>
            <a href="<?php echo site_url('cours'); ?>" class="nav-btn nav-cours <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes cours">📚 Mes Cours</a>
            <a href="<?php echo site_url('college/college-accueil'); ?>" class="nav-btn nav-accueil <?php echo $theme['nav_secondary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Accueil college">🏠 Accueil Collège</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard <?php echo $theme['nav_dashboard']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mon dashboard">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <section class="content-section max-w-3xl mx-auto px-2 md:px-0 text-center">
        <div class="guide-content flex flex-col items-center gap-6">
            <!-- Liste des matières -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 justify-center mb-8 w-full max-w-2xl mx-auto">
                <button type="button" onclick="openMatiereModal('francais')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[0]; ?>">
                    <span class="text-2xl">📖</span> Français
                </button>
                <button type="button" onclick="openMatiereModal('maths')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[1]; ?>">
                    <span class="text-2xl">🧮</span> Mathématiques
                </button>
                <button type="button" onclick="openMatiereModal('histoire')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[2]; ?>">
                    <span class="text-2xl">🌍</span> Histoire-Géo
                </button>
                <button type="button" onclick="openMatiereModal('sciences')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[3]; ?>">
                    <span class="text-2xl">🔬</span> Sciences
                </button>
                <button type="button" onclick="openMatiereModal('anglais')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[1]; ?>">
                    <span class="text-2xl">🇬🇧</span> Anglais
                </button>
                <button type="button" onclick="openMatiereModal('espagnol')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[2]; ?>">
                    <span class="text-2xl">🇪🇸</span> Espagnol
                </button>
                <button type="button" onclick="openMatiereModal('technologie')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[3]; ?>">
                    <span class="text-2xl">🧑‍💻</span> Technologie
                </button>
                <button type="button" onclick="openMatiereModal('arts')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[0]; ?>">
                    <span class="text-2xl">🎨</span> Arts
                </button>
            </div>
            </div>

            <!-- Modals pour chaque matière -->
            <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('francais')" class="absolute top-4 right-4 text-gray-400 <?php echo $modal_close_hover; ?> text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-francais-title" class="text-2xl font-bold <?php echo $modal_title; ?> mb-4 flex items-center gap-2"><span class="text-3xl">📖</span> Français</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold <?php echo $modal_heading; ?> mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Lecture et compréhension de textes variés</li>
                            <li>Expression écrite : récit, description, dialogue</li>
                            <li>Expression orale : présentation et argumentation</li>
                            <li>Étude de la langue : grammaire et orthographe</li>
                            <li>Littérature : œuvres du patrimoine et contemporaines</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Lecture quotidienne de 20 minutes, exercices de grammaire réguliers, écriture créative hebdomadaire.</p>
                </div>
            </div>
            <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-maths-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧮</span> Mathématiques</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Nombres et calculs : opérations, fractions, décimaux</li>
                            <li>Géométrie : figures planes, solides, transformations</li>
                            <li>Grandeurs et mesures : périmètre, aire, volume</li>
                            <li>Organisation et gestion de données</li>
                            <li>Raisonnement logique et résolution de problèmes</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Exercices quotidiens de calcul mental, manipulation d'objets géométriques, jeux mathématiques.</p>
                </div>
            </div>
            <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-histoire-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🌍</span> Histoire-Géo</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Repères chronologiques et spatiaux</li>
                            <li>Lecture et analyse de documents historiques et géographiques</li>
                            <li>Compréhension des sociétés et des territoires</li>
                            <li>Expression écrite et orale sur des sujets historiques/géographiques</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Fiches de révision, cartes mentales, exposés oraux, visites virtuelles de musées.</p>
                </div>
            </div>
            <div id="modal-sciences" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-sciences-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('sciences')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-sciences-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🔬</span> Sciences</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Observation, expérimentation, démarche scientifique</li>
                            <li>Connaissances en SVT, physique, chimie</li>
                            <li>Lecture de graphiques et tableaux</li>
                            <li>Rédaction de comptes-rendus d’expériences</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Expériences à la maison, vidéos scientifiques, quiz interactifs.</p>
                </div>
            </div>
            <div id="modal-anglais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-anglais-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('anglais')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-anglais-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🇬🇧</span> Anglais</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Compréhension orale et écrite</li>
                            <li>Expression orale et écrite</li>
                            <li>Vocabulaire de base et grammaire</li>
                            <li>Interaction en situation réelle</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Séries/films en VO, applications d’apprentissage, échanges linguistiques.</p>
                </div>
            </div>
            <div id="modal-espagnol" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-espagnol-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('espagnol')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-espagnol-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🇪🇸</span> Espagnol</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Compréhension orale et écrite</li>
                            <li>Expression orale et écrite</li>
                            <li>Vocabulaire de base et grammaire</li>
                            <li>Découverte de la culture hispanique</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Chansons, vidéos, jeux de rôle, échanges avec des natifs.</p>
                </div>
            </div>
            <div id="modal-technologie" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-technologie-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('technologie')" class="absolute top-4 right-4 text-gray-400 hover:text-green-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-technologie-title" class="text-2xl font-bold text-green-800 mb-4 flex items-center gap-2"><span class="text-3xl">🧑‍💻</span> Technologie</h2>
                    <div class="remediation-tips mb-2 text-left">
                        <h4 class="font-semibold text-green-800 mb-1">✅ Compétences à maîtriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Compréhension des objets techniques</li>
                            <li>Initiation à la programmation</li>
                            <li>Travail en équipe sur des projets</li>
                            <li>Utilisation raisonnée du numérique</li>
                        </ul>
                                            <!-- Fin de la suppression : il ne reste que la grille de boutons + modals -->
                            <h4>✅ Compétences à maîtriser :</h4>
                            <ul class="competence-list">
                                <li>Arts plastiques : techniques, composition, expression</li>
                                <li>Éducation musicale : écoute, pratique, culture</li>
                                <li>Arts du spectacle : théâtre, danse, cinéma</li>
                                <li>Analyse d'œuvres : description, interprétation</li>
                            </ul>
                        </div>
                        <p><strong>💡 Conseils de remédiation :</strong> Pratique artistique régulière, visites culturelles, création personnelle.</p>
                    </div>
                </div>

                <!-- Suppression des cards accordéon EPS et Anglais : demandé par l'utilisateur -->
        </div>

        <div class="content-section">
            <h3>🎯 Plan d'action personnalisé</h3>
            <?php if (!$has_access): ?>
                <p>Pour bénéficier d'un accompagnement sur mesure, crée-toi un compte et suis tes progrès !</p>
                <div class="cta-actions flex flex-col sm:flex-row gap-4 justify-center mt-4">
                    <a href="<?php echo site_url('register'); ?>" class="nav-btn nav-register bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-400">Créer mon compte 6ème</a>
                    <a href="<?php echo site_url('login'); ?>" class="nav-btn nav-login bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-300">Me connecter</a>
                </div>
            <?php else: ?>
                <p><strong>👋 Salut <?php echo htmlspecialchars($user_name ?? 'Élève'); ?> !</strong> Tu es connecté(e) et peux accéder à tous les contenus personnalisés.</p>
                <div class="cta-actions flex flex-col sm:flex-row gap-4 justify-center mt-4">
                    <a href="<?php echo site_url('college/6eme/exercices-6eme'); ?>" class="nav-btn nav-exercices bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-400">📝 Faire des exercices</a>
                    <a href="<?php echo site_url('cours'); ?>" class="nav-btn nav-cours bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-400">📚 Voir mes cours</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<script>
// Script pour gérer l'accordéon des matières
(function() {
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(item => {
        const toggle = item.querySelector('.accordion-toggle');
        const content = item.querySelector('.accordion-content');
        const icon = item.querySelector('.accordion-icon');

        if (toggle && content) {
            toggle.addEventListener('click', function(e) {
                // Empêcher la propagation de l'événement
                e.stopPropagation();
                e.preventDefault();

                // Récupérer l'état actuel de CETTE matière uniquement
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

                // Toggle l'état de CETTE matière uniquement
                if (isExpanded) {
                    toggle.setAttribute('aria-expanded', 'false');
                    content.style.display = 'none';
                    if (icon) icon.textContent = '▼';
                } else {
                    toggle.setAttribute('aria-expanded', 'true');
                    content.style.display = 'block';
                    if (icon) icon.textContent = '▲';
                }
            });
        }
    });
})();
</script>

<script>
// Gestion dynamique des modales matières (ouverture/fermeture)
function openMatiereModal(nom) {
    var modal = document.getElementById('modal-' + nom);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    // Focus trap
    var focusable = modal.querySelectorAll('a, button, textarea, input, select, [tabindex]:not([tabindex="-1"])');
    var first = focusable[0], last = focusable[focusable.length-1];
    var prevActive = document.activeElement;
    modal._prevActive = prevActive;
    setTimeout(function() {
        if (first) first.focus();
    }, 100);
    function trap(e) {
        if (e.key === 'Tab') {
            if (focusable.length === 0) return;
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault(); last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault(); first.focus();
            }
        }
    }
    modal._trap = trap;
    modal.addEventListener('keydown', trap);
    // Fermer avec ESC
    function escListener(e) {
        if (e.key === 'Escape') closeMatiereModal(nom);
    }
    modal._escListener = escListener;
    document.addEventListener('keydown', escListener);
    // Fermer en cliquant sur le fond
    modal._bgListener = function(e) {
        if (e.target === modal) closeMatiereModal(nom);
    };
    modal.addEventListener('click', modal._bgListener);
}

function closeMatiereModal(nom) {
    var modal = document.getElementById('modal-' + nom);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (modal._trap) modal.removeEventListener('keydown', modal._trap);
    if (modal._escListener) document.removeEventListener('keydown', modal._escListener);
    if (modal._bgListener) modal.removeEventListener('click', modal._bgListener);
    // Rendre le focus au déclencheur
    if (modal._prevActive && typeof modal._prevActive.focus === 'function') {
        setTimeout(function() { modal._prevActive.focus(); }, 100);
    }
}
</script>
