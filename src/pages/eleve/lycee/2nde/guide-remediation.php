<?php
$page_title = 'Guide de remédiation Seconde - MonCoachScolaire';
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
$user_level = $_SESSION['user_level'] ?? 'Seconde';
$level_normalized = normalize_level_for_url($user_level);
$theme = get_theme_variant_by_level($user_level);
?>

<main class="main-content app-bg bg-gray-50 min-h-screen">
    <!-- Navigation entre niveaux supprimee -->
    <div class="header text-center py-8">
        <h1 class="text-3xl md:text-4xl font-bold <?php echo $theme['title']; ?> mb-2">Guide de remédiation Seconde</h1>
        <p class="text-lg md:text-xl <?php echo $theme['subtitle']; ?> mb-6 font-medium">Programmes 2025 - Accompagnement personnalisé pour réussir ta Seconde</p>

        <!-- Boutons de navigation -->
        <div class="guide-navigation flex flex-wrap justify-center gap-4 mt-6">
            <a href="<?php echo site_url('lycee/seconde/exercices-seconde'); ?>" class="nav-btn nav-exercices <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes exercices Seconde">Mes Exercices</a>
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
                <button type="button" onclick="openMatiereModal('maths')" class="px-6 py-3 rounded-xl bg-violet-100 text-violet-800 font-semibold shadow hover:bg-violet-200 focus:outline-none focus:ring-2 focus:ring-violet-400 transition flex items-center gap-2 justify-center">
                    Mathematiques
                </button>
                <button type="button" onclick="openMatiereModal('histoire')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Histoire-Geo
                </button>
                <button type="button" onclick="openMatiereModal('svt')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    SVT
                </button>
                <button type="button" onclick="openMatiereModal('physique')" class="px-6 py-3 rounded-xl bg-fuchsia-100 text-fuchsia-800 font-semibold shadow hover:bg-fuchsia-200 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 transition flex items-center gap-2 justify-center">
                    Physique-Chimie
                </button>
                <button type="button" onclick="openMatiereModal('langues')" class="px-6 py-3 rounded-xl bg-violet-100 text-violet-800 font-semibold shadow hover:bg-violet-200 focus:outline-none focus:ring-2 focus:ring-violet-400 transition flex items-center gap-2 justify-center">
                    Langues
                </button>
                <button type="button" onclick="openMatiereModal('methodo')" class="px-6 py-3 rounded-xl bg-gray-100 text-gray-800 font-semibold shadow hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center gap-2 justify-center">
                    Methodo
                </button>
                <button type="button" onclick="openMatiereModal('orientation')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Orientation
                </button>
            </div>
        </div>

        <!-- Modals pour chaque matiere -->
        <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('francais')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-francais-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Francais</h2>
                <div class="remédiation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Lecture : Analyser des textes litteraires varies (roman, theatre, poesie, argumentation)</li>
                        <li>?criture : Maitriser l'écriture d'invention, le commentaire et la dissertation</li>
                        <li>Oral : Presenter un expose structure et argumente</li>
                        <li>Langue : Renforcer la grammaire, l'orthographe et le vocabulaire</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Lecture reguliere (30 minutes par jour minimum) d'œuvres variees, tenir un carnet de lecture, pratiquer l'écriture creative hebdomadaire, reviser les regles de grammaire regulierement.</p>
            </div>
        </div>
        <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-violet-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-maths-title" class="text-2xl font-bold text-violet-700 mb-4 flex items-center gap-2">Mathematiques</h2>
                <div class="remédiation-tips mb-2 text-left">
                    <h4 class="font-semibold text-violet-600 mb-1">o. Competences a maitriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Nombres : Maitriser les intervalles, la valeur absolue, les puissances</li>
                        <li>Fonctions : Comprendre les fonctions (image, antecedent, variations, courbes)</li>
                        <li>Geometrie : Utiliser les vecteurs, les equations de droites</li>
                        <li>Probabilites : Calculer des probabilites, utiliser les arbres ponderes</li>
                        <li>Statistiques : Calculer moyenne, mediane, etendue, ecart-type</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-violet-700">Conseils de remédiation :</span> Refaire les exercices corriges jusqu'a maitriser la methode, apprendre par coeur les formules importantes, faire des exercices de calcul mental quotidiennement, utiliser la calculatrice avec methode.</p>
            </div>
        </div>
        <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-histoire-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Histoire-Geo</h2>
                <div class="remédiation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Histoire : Comprendre les grandes periodes (Renaissance, Lumieres, Révolutions)</li>
                        <li>Geographie : Analyser les enjeux du developpement durable et les dynamiques territoriales</li>
                        <li>Methodes : Analyser des documents, construire une argumentation, realiser un croquis</li>
                        <li>EMC : Comprendre les valeurs republicaines et la citoyennete</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Créer des frises chronologiques pour memoriser les dates cles, associer des images aux notions importantes, pratiquer l'analyse de documents regulierement, suivre l'actualite pour faire des liens avec le programme.</p>
            </div>
        </div>
        <div id="modal-svt" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-svt-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('svt')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-svt-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">SVT</h2>
                <div class="remédiation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Biologie : Comprendre l'organisation du vivant (cellule, organismes)</li>
                        <li>Geologie : étudier la structure de la Terre et les risques geologiques</li>
                        <li>écologie : Comprendre les ecosystemes et le developpement durable</li>
                        <li>Methodes scientifiques : observation, experimentation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Apprendre a realiser des schemas clairs et legendes, maitriser le vocabulaire scientifique (créer un glossaire), observer la nature pour faire des liens avec le cours, refaire les experiences vues en classe mentalement.</p>
            </div>
        </div>
        <div id="modal-physique" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-physique-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('physique')" class="absolute top-4 right-4 text-gray-400 hover:text-fuchsia-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-physique-title" class="text-2xl font-bold text-fuchsia-700 mb-4 flex items-center gap-2">Physique-Chimie</h2>
                <div class="remédiation-tips mb-2 text-left">
                    <h4 class="font-semibold text-fuchsia-600 mb-1">o. Competences a maitriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Mecanique : Mouvement, forces, energie</li>
                        <li>électricité : Courant, tension, resistance</li>
                        <li>Chimie : Atomes, molecules, transformations chimiques</li>
                        <li>Experimentation : mesures, protocoles</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-fuchsia-700">Conseils de remédiation :</span> Apprendre par coeur les formules importantes, maitriser les unites et les conversions, refaire les exercices resolus etape par etape, verifier toujours l'homogeneite des formules.</p>
            </div>
        </div>
        <div id="modal-langues" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-langues-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('langues')" class="absolute top-4 right-4 text-gray-400 hover:text-violet-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-langues-title" class="text-2xl font-bold text-violet-700 mb-4 flex items-center gap-2">Langues Vivantes</h2>
                <div class="remédiation-tips mb-2 text-left">
                    <h4 class="font-semibold text-violet-600 mb-1">o. Competences a maitriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Comprehension orale : Comprendre des documents audio authentiques</li>
                        <li>Expression orale : Prendre part a une conversation, presenter un expose</li>
                        <li>Comprehension écrite : Lire et comprendre des textes varies</li>
                        <li>Expression écrite : Rediger des textes coherents et varies</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-violet-700">Conseils de remédiation :</span> Ecouter regulierement des podcasts, series, films en version originale, pratiquer la langue quotidiennement (meme 10 minutes), tenir un journal en langue etrangere, memoriser du vocabulaire par themes (fiches).</p>
            </div>
        </div>
        <div id="modal-methodo" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-methodo-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('methodo')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-methodo-title" class="text-2xl font-bold text-purple-800 mb-4 flex items-center gap-2">Methodologie et Organisation</h2>
                <div class="remédiation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-800 mb-1">o. Competences a maitriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Organisation du travail : Planning hebdomadaire, fiches de revision</li>
                        <li>Methodes de memorisation : Repetition espacee, schemas mentaux</li>
                        <li>Gestion du stress : Bien dormir, faire du sport, prendre des pauses</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-800">Conseils de remédiation :</span> Repartir ton temps de travail par matiere, resumer chaque cours sur une fiche, reviser chaque jour plutot que de bachoter, commencer les revisions 1 semaine avant les controles.</p>
            </div>
        </div>
        <div id="modal-orientation" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-orientation-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('orientation')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-orientation-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Orientation</h2>
                <div class="remédiation-tips mb-2 text-left">
                    <h4 class="font-semibold text-purple-600 mb-1">o. Choisir tes specialites pour la Première :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Identifie tes centres d'interet : Quelles matières te plaisent le plus ?</li>
                        <li>?value tes competences : Dans quelles matières réussis-tu le mieux ?</li>
                        <li>Projette-toi : Vers quels métiers ou études veux-tu t'orienter ?</li>
                        <li>Informe-toi : Consulte les fiches métiers et les programmes des specialites</li>
                        <li>Teste : Participe aux portes ouvertes et aux mini-stages</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Calendrier d'orientation :</span> Octobre-Novembre (decouverte), Decembre-Janvier (forum des métiers), Fevrier-Mars (intention d'orientation), Avril-Mai (confirmation), Juin (voeux definitifs).</p>
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

