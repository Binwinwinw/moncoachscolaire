<?php
$page_title = 'Guide de remédiation 4ème - MonCoachScolaire';
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
$user_level = $_SESSION['user_level'] ?? '4ème';
$level_normalized = normalize_level_for_url($user_level);
$theme = get_theme_variant_by_level($user_level);
$subject_tones = $theme['soft_buttons'];
?>

<main class="main-content app-bg bg-gray-50 min-h-screen">
    <!-- Navigation entre niveaux supprimée -->
    <div class="header text-center py-8">
        <h1 class="text-3xl md:text-4xl font-bold <?php echo $theme['title']; ?> mb-2">📚 Guide de remédiation 4ème</h1>
        <p class="text-lg md:text-xl <?php echo $theme['subtitle']; ?> mb-6 font-medium">Programmes 2025 – Accompagnement personnalisé pour réussir ta 4ème</p>

        <!-- Boutons de navigation -->
        <div class="guide-navigation flex flex-wrap justify-center gap-4 mt-6">
            <a href="<?php echo site_url('college/4eme/exercices-4eme'); ?>" class="nav-btn nav-exercices <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes exercices 4eme">📝 Mes Exercices</a>
            <a href="<?php echo site_url('cours'); ?>" class="nav-btn nav-cours <?php echo $theme['nav_primary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mes cours">📚 Mes Cours</a>
            <a href="<?php echo site_url('college/college-accueil'); ?>" class="nav-btn nav-accueil <?php echo $theme['nav_secondary']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Accueil college">🏠 Accueil Collège</a>
            <?php if (!empty($is_logged_in)): ?>
                <a href="<?php echo site_url('eleve/dashboard'); ?>" class="nav-btn nav-dashboard <?php echo $theme['nav_dashboard']; ?> text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2" aria-label="Mon dashboard">📊 Mon Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <section class="content-section max-w-3xl mx-auto px-2 md:px-0">
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
                <button type="button" onclick="openMatiereModal('svt')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[3]; ?>">
                    <span class="text-2xl">🧪</span> SVT
                </button>
                <button type="button" onclick="openMatiereModal('physique')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[1]; ?>">
                    <span class="text-2xl">⚗️</span> Physique-Chimie
                </button>
                <button type="button" onclick="openMatiereModal('technologie')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[3]; ?>">
                    <span class="text-2xl">🔧</span> Technologie
                </button>
                <button type="button" onclick="openMatiereModal('anglais')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[2]; ?>">
                    <span class="text-2xl">🌐</span> Anglais
                </button>
                <button type="button" onclick="openMatiereModal('arts')" class="px-6 py-3 rounded-xl font-semibold shadow transition flex items-center gap-2 justify-center focus:outline-none focus:ring-2 <?php echo $subject_tones[0]; ?>">
                    <span class="text-2xl">🎨</span> Arts
                </button>
            </div>
        </div>

        <!-- Modals pour chaque matière -->
        <div id="modal-francais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-francais-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('francais')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-francais-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">📖</span> Français</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Lecture et compréhension de textes complexes</li>
                        <li>Expression écrite : récit, description, argumentation, dissertation littéraire</li>
                        <li>Expression orale : argumentation orale, prise de parole en public</li>
                        <li>Étude de la langue : grammaire, orthographe, analyse syntaxique</li>
                        <li>Littérature : œuvres du XIXe siècle, théâtre moderne, poésie engagée</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Lecture régulière d'œuvres variées, pratique de l'écriture argumentative, analyse de textes, révision des règles grammaticales.</p>
            </div>
        </div>
        <div id="modal-maths" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-maths-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('maths')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-maths-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧮</span> Mathématiques</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Nombres et calculs : nombres réels, calcul algébrique</li>
                        <li>Géométrie : triangles, propriétés, théorèmes</li>
                        <li>Fonctions et analyse : tableaux de valeurs, représentations graphiques, variations</li>
                        <li>Statistiques et probabilités</li>
                        <li>Algorithmique et programmation : Python, algorithmes complexes</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Exercices quotidiens, utilisation de tableaux de valeurs, représentation graphique, résolution de problèmes complexes.</p>
            </div>
        </div>
        <div id="modal-histoire" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-histoire-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('histoire')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-histoire-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🌍</span> Histoire-Géo</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Histoire : périodes chronologiques, événements majeurs</li>
                        <li>Géographie : espaces, sociétés, environnement</li>
                        <li>Méthodes : analyse de documents, cartes, frises</li>
                        <li>Citoyenneté : valeurs républicaines, droits et devoirs</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Création de frises chronologiques, observation de cartes, débats sur l'actualité, analyse critique de documents.</p>
            </div>
        </div>
        <div id="modal-svt" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-svt-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('svt')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-svt-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🧪</span> SVT</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>La matière et l'énergie</li>
                        <li>La Terre et l'Univers</li>
                        <li>Le vivant et son évolution</li>
                        <li>L'environnement et développement durable</li>
                        <li>Méthodes scientifiques : observation, expérimentation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Expériences simples, observation de la nature, schémas scientifiques, compréhension des enjeux environnementaux.</p>
            </div>
        </div>
        <div id="modal-physique" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-physique-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('physique')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-physique-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">⚗️</span> Physique-Chimie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Mouvement et forces : équilibre, conditions d'équilibre</li>
                        <li>Énergie : formes, transformations</li>
                        <li>Matière : propriétés, mélanges, solutions</li>
                        <li>Expérimentation : mesures, protocoles</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Manipulations concrètes, mesures quotidiennes, observation des phénomènes physiques, compréhension des équilibres.</p>
            </div>
        </div>
        <div id="modal-technologie" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-technologie-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('technologie')" class="absolute top-4 right-4 text-gray-400 hover:text-green-800 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-technologie-title" class="text-2xl font-bold text-green-800 mb-4 flex items-center gap-2"><span class="text-3xl">🔧</span> Technologie</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-800 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Conception et réalisation de systèmes techniques complexes</li>
                        <li>Programmation avancée</li>
                        <li>Utilisation d'outils numériques adaptés</li>
                        <li>Analyse de systèmes techniques</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-800">💡 Conseils de remédiation :</span> Conception de systèmes complexes, programmation avancée, projets collaboratifs.</p>
            </div>
        </div>
        <div id="modal-anglais" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-anglais-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('anglais')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-anglais-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🌐</span> Anglais</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Compréhension orale et écrite</li>
                        <li>Expression orale : présentation, interaction, argumentation</li>
                        <li>Expression écrite : description, récit, argumentation</li>
                        <li>Civilisation : culture anglophone</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Écoute de podcasts, visionnage de vidéos, conversation en anglais, jeux linguistiques, lecture de textes variés.</p>
            </div>
        </div>
        <div id="modal-arts" class="fixed inset-0 z-50 items-center justify-center bg-black/40 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-arts-title">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 relative">
                <button onclick="closeMatiereModal('arts')" class="absolute top-4 right-4 text-gray-400 hover:text-green-600 text-2xl font-bold focus:outline-none" aria-label="Fermer">&times;</button>
                <h2 id="modal-arts-title" class="text-2xl font-bold text-green-700 mb-4 flex items-center gap-2"><span class="text-3xl">🎨</span> Arts</h2>
                <div class="remediation-tips mb-2 text-left">
                    <h4 class="font-semibold text-green-600 mb-1">✅ Compétences à maîtriser :</h4>
                    <ul class="competence-list list-disc list-inside text-gray-700">
                        <li>Arts plastiques : techniques, composition, expression</li>
                        <li>Éducation musicale : écoute, pratique, culture</li>
                        <li>Arts du spectacle : théâtre, danse, cinéma</li>
                        <li>Analyse d'œuvres : description, interprétation</li>
                    </ul>
                </div>
                <p class="mt-2 text-left"><span class="font-semibold text-green-700">💡 Conseils de remédiation :</span> Pratique artistique régulière, visites culturelles, création personnelle, analyse d'œuvres.</p>
            </div>
        </div>
        </div>

        <!-- Bloc Plan d'action personnalisé -->
        <section class="content-section max-w-2xl mx-auto my-12 p-6 bg-green-50 rounded-2xl shadow text-center">
            <h3 class="text-2xl font-bold text-green-700 mb-2">🎯 Plan d'action personnalisé</h3>
            <p class="text-lg text-green-900 mb-4">Pour bénéficier d'un accompagnement sur mesure, crée-toi un compte et suis tes progrès&nbsp;!</p>
            <div class="cta-actions flex flex-col sm:flex-row gap-4 justify-center mt-4">
                <a href="<?php echo site_url('register'); ?>" class="nav-btn nav-register bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-400">Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="nav-btn nav-login bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold shadow transition focus:outline-none focus:ring-2 focus:ring-green-300">Me connecter</a>
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
    // Rendre le focus au déclencheur
    if (modal._prevActive && typeof modal._prevActive.focus === 'function') {
        setTimeout(function() { modal._prevActive.focus(); }, 100);
    }
}
</script>
