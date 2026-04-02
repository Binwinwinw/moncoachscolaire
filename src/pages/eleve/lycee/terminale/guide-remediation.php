<?php
$page_title = 'Guide de remédiation Terminale - MonCoachScolaire';
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
$user_level = $_SESSION['user_level'] ?? 'Terminale';
$level_normalized = normalize_level_for_url($user_level);
$theme = get_theme_variant_by_level($user_level);
?>

<main class="main-content app-bg bg-gray-50 min-h-screen">
    <?php
    // Navigation entre niveaux supprimee (demande accessibilite)
?>
    <div class="header text-center py-8">
        <h1 class="text-3xl md:text-4xl font-bold <?php echo $theme['title']; ?> mb-2">Guide de remédiation Terminale</h1>
        <p class="text-lg md:text-xl <?php echo $theme['subtitle']; ?> mb-6 font-medium">Programmes 2025 - Accompagnement personnalisé pour réussir ta Terminale et le Bac</p>

        <!-- Boutons de navigation -->
        <div class="guide-navigation flex flex-wrap justify-center gap-4 mt-6">
            <a href="<?php echo site_url('lycee/terminale/exercices-terminale'); ?>" class="nav-btn nav-exercices <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes exercices Terminale">Mes Exercices</a>
            <a href="<?php echo site_url('cours'); ?>" class="nav-btn nav-cours <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes cours">Mes Cours</a>
            <a href="<?php echo site_url('lycee/lycee-accueil'); ?>" class="nav-btn nav-accueil <?php echo $theme['nav_secondary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Accueil lycee">Accueil Lycee</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard <?php echo $theme['nav_dashboard']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mon dashboard">Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <section class="content-section max-w-3xl mx-auto px-2 md:px-0">
            <!-- Grille de boutons matières Terminale -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 justify-center mb-8 w-full max-w-2xl mx-auto">
                <button type="button" onclick="openTermModal('maths')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Mathematiques
                </button>
                <button type="button" onclick="openTermModal('francais')" class="px-6 py-3 rounded-xl bg-fuchsia-100 text-fuchsia-800 font-semibold shadow hover:bg-fuchsia-200 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 transition flex items-center gap-2 justify-center">
                    Francais
                </button>
                <button type="button" onclick="openTermModal('sciences')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Sciences
                </button>
                <button type="button" onclick="openTermModal('histoire')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Histoire-Geo
                </button>
                <button type="button" onclick="openTermModal('organisation')" class="px-6 py-3 rounded-xl bg-gray-100 text-gray-800 font-semibold shadow hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center gap-2 justify-center">
                    Organisation
                </button>
                <button type="button" onclick="openTermModal('epreuve')" class="px-6 py-3 rounded-xl bg-violet-100 text-violet-800 font-semibold shadow hover:bg-violet-200 focus:outline-none focus:ring-2 focus:ring-violet-400 transition flex items-center gap-2 justify-center">
                    Epreuve Ecrite
                </button>
                <button type="button" onclick="openTermModal('oral')" class="px-6 py-3 rounded-xl bg-fuchsia-200 text-fuchsia-900 font-semibold shadow hover:bg-fuchsia-300 focus:outline-none focus:ring-2 focus:ring-fuchsia-400 transition flex items-center gap-2 justify-center">
                    Prepa Oral
                </button>
                <button type="button" onclick="openTermModal('annales')" class="px-6 py-3 rounded-xl bg-purple-100 text-purple-800 font-semibold shadow hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-purple-400 transition flex items-center gap-2 justify-center">
                    Annales
                </button>
            </div>

            <!-- Modals Terminale -->
            <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-maths-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Mathematiques</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Suites : arithmetiques, geometriques, limites</li>
                            <li>Fonctions : derivees avancees, variations, limites</li>
                            <li>Probabilites : lois de probabilite, variables aleatoires</li>
                            <li>Algorithmique : algorithmes complexes, programmation</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Utiliser des annales recentes, s'entrainer en temps limite, corriger avec bareme, noter les axes d'amelioration. Exemple : 1 sujet complet par semaine.</p>
                </div>
            </div>
            <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('francais')" class="absolute top-4 right-4 text-gray-400 hover:text-fuchsia-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-francais-title" class="text-2xl font-bold text-fuchsia-700 mb-4 flex items-center gap-2">Francais</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-fuchsia-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Dissertation : Construction d'une argumentation structuree</li>
                            <li>Commentaire : Analyse approfondie de texte</li>
                            <li>Dictee ciblee : Orthographe et grammaire</li>
                            <li>Expression écrite : Clarte, precision, style</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-fuchsia-700">Conseils de remédiation :</span> Pratique reguliere de la dissertation et du commentaire, revision des regles grammaticales, entrainement a la dictee.</p>
                </div>
            </div>
            <div id="modal-sciences" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-sciences-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('sciences')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-sciences-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Sciences (Physique-Chimie / SVT)</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Physique-Chimie : Problemes complexes, equilibres, reactions</li>
                            <li>SVT : Experimentation, analyse critique de resultats</li>
                            <li>Methodes experimentales : Protocoles, interpretation</li>
                            <li>Analyse critique : Interpretation de resultats</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Resolution de problemes de physique-chimie, experimentation en SVT, analyse critique de resultats, maitrise des protocoles.</p>
                </div>
            </div>
            <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-histoire-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Histoire-Geo</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Methodologie de la dissertation : Structure, argumentation</li>
                            <li>Composition : Synthese structuree</li>
                            <li>Etude de document : Analyse critique</li>
                            <li>Expression écrite : Clarte, precision</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Maitrise de la méthodologie de la dissertation, pratique de la composition, analyse de documents, entrainement regulier.</p>
                </div>
            </div>
            <div id="modal-organisation" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-organisation-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('organisation')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-organisation-title" class="text-2xl font-bold text-purple-800 mb-4 flex items-center gap-2">Organisation des Revisions</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-800 mb-1">o. Calendrier a 12 semaines :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Alternance matières : Repartir le temps par matiere</li>
                            <li>Annales hebdomadaires : 1 sujet complet par semaine</li>
                            <li>Temps de correction : Analyser les erreurs</li>
                            <li>Seances de simulation : ?crits et oraux</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-800">Conseils de remédiation :</span> Calendrier a 12 semaines avec alternance des matières, annales hebdomadaires, temps de correction et séances de simulation (écrits et oraux).</p>
                </div>
            </div>
            <div id="modal-epreuve" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-epreuve-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('epreuve')" class="absolute top-4 right-4 text-gray-400 hover:text-violet-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-epreuve-title" class="text-2xl font-bold text-violet-700 mb-4 flex items-center gap-2">Methode pour l'Epreuve Ecrite</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-violet-700 mb-1">o. étapes cles :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Lire attentivement le sujet et reperer les mots-cles</li>
                            <li>Temps de planification (5-10 min) : Schemas, brouillon</li>
                            <li>Redaction claire et structuree</li>
                            <li>Relecture finale : Verification et correction</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-violet-700">Conseils de remédiation :</span> Lire attentivement le sujet, temps de planification (5-10 min) avec schemas et brouillon, redaction claire et structuree, relecture finale.</p>
                </div>
            </div>
            <div id="modal-oral" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-oral-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('oral')" class="absolute top-4 right-4 text-gray-400 hover:text-fuchsia-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-oral-title" class="text-2xl font-bold text-fuchsia-700 mb-4 flex items-center gap-2">Preparation a l'Oral</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-fuchsia-700 mb-1">o. Competences a maitriser :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Simulations regulieres : 2 a 4 semaines avant l'oral</li>
                            <li>Fiches synthetiques : Pour chaque projet</li>
                            <li>Entrainement a la prise de parole</li>
                            <li>Gestion du stress : Techniques de relaxation</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-fuchsia-700">Conseils de remédiation :</span> Simulations regulieres (2 a 4 semaines avant l'oral), fiches synthetiques pour chaque projet, entrainement a la prise de parole et gestion du stress.</p>
                </div>
            </div>
            <div id="modal-annales" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-annales-title">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                    <button onclick="closeTermModal('annales')" class="absolute top-4 right-4 text-gray-400 hover:text-purple-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                    <h2 id="modal-annales-title" class="text-2xl font-bold text-purple-700 mb-4 flex items-center gap-2">Annales et Sujets Types</h2>
                    <div class="remédiation-tips mb-2 text-left">
                        <h4 class="font-semibold text-purple-600 mb-1">o. Strategie d'entrainement :</h4>
                        <ul class="competence-list list-disc list-inside text-gray-700">
                            <li>Utiliser des annales recentes</li>
                            <li>S'entrainer en temps limite</li>
                            <li>Corriger avec bareme</li>
                            <li>Noter les axes d'amelioration</li>
                            <li>Progression : 1 sujet complet par semaine</li>
                        </ul>
                    </div>
                    <p class="mt-2 text-left"><span class="font-semibold text-purple-700">Conseils de remédiation :</span> Utiliser des annales recentes, s'entrainer en temps limite, corriger avec bareme, et noter les axes d'amelioration. Exemple de progression : 1 sujet complet par semaine.</p>
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
// Gestion dynamique des modales Terminale (ouverture/fermeture)
function openTermModal(nom) {
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
    function escListener(e) {
        if (e.key === 'Escape') closeTermModal(nom);
    }
    modal._escListener = escListener;
    document.addEventListener('keydown', escListener);
    modal._bgListener = function(e) {
        if (e.target === modal) closeTermModal(nom);
    };
    modal.addEventListener('click', modal._bgListener);
}

function closeTermModal(nom) {
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

