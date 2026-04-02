<?php
$page_title = 'Guide de remédiation Première - MonCoachScolaire';
$page_css = '';

// Charger les fichiers necessaires
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

// Charger admin_auth.php pour verifier si l'utilisateur est admin
if (is_file(__DIR__ . '/../../../includes/admin_auth.php')) {
    require_once __DIR__ . '/../../../includes/admin_auth.php';
}

// Verifier si l'utilisateur est connecte OU s'il est admin
$is_admin = function_exists('isAdmin') && isAdmin();
$has_access = !empty($is_logged_in) || $is_admin;
$user_level = $_SESSION['user_level'] ?? 'Première';
$level_normalized = normalize_level_for_url($user_level);
$theme = get_theme_variant_by_level($user_level);
?>

<main class="main-content app-bg bg-gray-50 min-h-screen">
    <!-- Navigation entre niveaux supprimee -->
    <div class="header text-center py-8">
        <h1 class="text-3xl md:text-4xl font-bold <?php echo $theme['title']; ?> mb-2">Guide de remédiation Première</h1>
        <p class="text-lg md:text-xl <?php echo $theme['subtitle']; ?> mb-6 font-medium">Programmes 2025 - Accompagnement personnalisé pour réussir ta Première</p>

        <!-- Boutons de navigation -->
        <div class="guide-navigation flex flex-wrap justify-center gap-4 mt-6">
            <a href="<?php echo site_url('lycee/première/exercices-première'); ?>" class="nav-btn nav-exercices <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes exercices Première">Mes Exercices</a>
            <a href="<?php echo site_url('cours'); ?>" class="nav-btn nav-cours <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes cours">Mes Cours</a>
            <a href="<?php echo site_url('lycee/lycee-accueil'); ?>" class="nav-btn nav-accueil <?php echo $theme['nav_secondary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Accueil lycee">Accueil Lycee</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard <?php echo $theme['nav_dashboard']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mon dashboard">Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <section class="content-section max-w-3xl mx-auto px-2 md:px-0">
        <div class="guide-content flex flex-col items-center gap-6">
            <!-- Liste des matières -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 justify-center mb-8 w-full max-w-2xl mx-auto">
                <button type="button" onclick="openMatiereModal('francais')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Francais
                </button>
                <button type="button" onclick="openMatiereModal('sciences')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Sciences
                </button>
                <button type="button" onclick="openMatiereModal('histoire')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Histoire-Geo
                </button>
                <button type="button" onclick="openMatiereModal('methodo')" class="px-6 py-3 rounded-xl bg-gray-100 text-gray-800 font-semibold shadow hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center gap-2 justify-center">
                    Methodo
                </button>
                <button type="button" onclick="openMatiereModal('orientation')" class="px-6 py-3 rounded-xl bg-purple-200 text-purple-900 font-semibold shadow hover:bg-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Orientation
                </button>
                <button type="button" onclick="openMatiereModal('oral')" class="px-6 py-3 rounded-xl bg-fuchsia-100 text-fuchsia-800 font-semibold shadow hover:bg-fuchsia-200 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 transition flex items-center gap-2 justify-center">
                    Grand Oral
                </button>
            </div>

            <!-- Modals pour chaque matiere -->
            <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('francais')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-francais-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Francais</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>?criture : Expression argumentee, commentaire, dissertation courte, synthese</li>
                            <li>Analyse de texte : Identification du registre, procedes stylistiques, tension argumentative</li>
                            <li>Preparation au grand oral : Presentation structuree, argumentation</li>
                            <li>Litterature : 'uvres du programme de Première</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Travail sur l'écriture argumentee, analyse de texte reguliere, preparation au grand oral, organisation d'un carnet de revision par matiere.</p>
                </div>
            </div>
            <div id="modal-sciences" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-sciences-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('sciences')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-sciences-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Sciences (SVT / Physique-Chimie)</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Methodes experimentales : Analyse de resultats, mise en relation des connaissances</li>
                            <li>Physique-Chimie : ?quilibres, reactions, notions d'energie</li>
                            <li>SVT : Approfondissement des connaissances biologiques et geologiques</li>
                            <li>Analyse critique : Interpretation de resultats experimentaux</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Renforcement des methodes experimentales, exercices sur equilibres et reactions, preparation des dossiers de specialite par projets concrets.</p>
                </div>
            </div>
            <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-histoire-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Histoire-Geo / EMC</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Construction d'une synthese et d'un raisonnement structure</li>
                            <li>Utilisation de documents historiques et geographiques</li>
                            <li>Analyse critique de sources</li>
                            <li>Argumentation et expression écrite</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Travail sur la capacite a construire une synthese, utilisation de documents pour etayer un propos, pratique reguliere de l'argumentation.</p>
                </div>
            </div>
            <div id="modal-methodo" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-methodo-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('methodo')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-methodo-title" class="text-2xl font-bold text-purple-800 mb-4 flex items-center gap-2">Methodologie et Gestion du Travail</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-800 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Organisation : Carnet de revision par matiere</li>
                            <li>Travail regulier : Seances courtes + sorties d'entrainement</li>
                            <li>Preparation des dossiers : Projets concrets pour les specialites</li>
                            <li>Gestion du temps : Planning efficace</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-800">Conseils de remédiation :</span> Organiser un carnet de revision par matiere, travail regulier avec séances courtes, preparer les dossiers de specialite par projets concrets.</p>
                </div>
            </div>
            <div id="modal-orientation" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-orientation-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('orientation')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-orientation-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Orientation - Choix des Specialites</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Comment choisir :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>évaluer ses gouts : Quelles matières te plaisent le plus ?</li>
                            <li>évaluer ses competences : Dans quelles matières réussis-tu le mieux ?</li>
                            <li>évaluer les debouches : Vers quels métiers ou études veux-tu t'orienter ?</li>
                            <li>Plan de 6 semaines : Tester des specialites et valider un choix</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Explication de comment evaluer ses gouts, ses competences et le debouche des specialites. Proposer un plan de 6 semaines pour tester des specialites et valider un choix.</p>
                </div>
            </div>
            <div id="modal-oral" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-oral-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeMatiereModal('oral')" class="absolute top-4 right-4 text-gray-400 hover:text-fuchsia-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-oral-title" class="text-2xl font-bold text-fuchsia-700 mb-4 flex items-center gap-2">Preparation au Grand Oral</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-fuchsia-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Presentation structuree : Organisation de la prise de parole</li>
                            <li>Argumentation : Construction d'un raisonnement clair</li>
                            <li>Gestion du stress : Techniques de relaxation et de preparation</li>
                            <li>Expression orale : Clarte, fluidite, conviction</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-fuchsia-700">Conseils de remédiation :</span> Conseils pour preparer le grand oral, simulations regulieres, fiches synthetiques pour chaque projet, entrainement a la prise de parole.</p>
                </div>
            </div>
        </div>

        <!-- Bloc Plan d'action personnalisé -->
        <section class="content-section max-w-2xl mx-auto my-12 p-6 bg-violet-50 rounded-2xl shadow text-center">
            <h3 class="text-2xl font-bold text-violet-700 mb-2">Plan d'action personnalisé</h3>
            <p class="text-lg text-violet-900 mb-4">Pour beneficier d'un accompagnement sur mesure, cree-toi un compte et suis tes progres&nbsp;!</p>
            <div class="cta-actions flex flex-col sm:flex-row gap-4 justify-center mt-4">
                <a href="<?php echo site_url('register'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-violet-600 text-white font-semibold rounded-lg hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-400 transition-all shadow-lg">Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-white text-violet-700 font-semibold rounded-lg border border-violet-300 hover:bg-violet-50 focus:outline-none focus:ring-2 focus:ring-violet-300 transition-colors shadow-lg">Me connecter</a>
            </div>
        </section>
    </section>
</main>

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
    // Rendre le focus au declencheur
    if (modal._prevActive && typeof modal._prevActive.focus === 'function') {
        setTimeout(function() { modal._prevActive.focus(); }, 100);
    }
}
</script>

