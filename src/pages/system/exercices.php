<?php
/*
 * Page publique des exercices (route ?page=exercices)
 * - Affichage centralisé, adapte le contenu selon le contexte (visiteur, loggé, démo)
 * - Séparation stricte des accès, panneau coach uniquement pour visiteurs
 * - Liens dynamiques sécurisés (site_url ou fallback)
 * - Toutes les données issues de l’utilisateur ou de la session sont échappées
 * - Toute confusion sur la page à modifier doit être signalée à l’utilisateur (règle IA)
 */



$page_title = 'Exercices - MonCoachScolaire';
$page_css = 'exercices.css';
$page_class = 'page-exercices';

// ========== 1. PROTECTION SESSION & CONFIG ==========
if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}

// ========== 2. INCLUDES SÉCURITÉ ==========
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/demo_security.php')) {
    require_once dirname(__DIR__, 2) . '/includes/demo_security.php';
}

// ========== 3. VÉRIFICATION ACCÈS ==========
$is_admin = function_exists('isAdmin') && isAdmin();
$is_demo = function_exists('isDemoUser') && isDemoUser();
$has_access = !empty($is_logged_in) || $is_admin || $is_demo;

// ========== 4. INCLUSION TOPBAR ==========
if (!isset($hide_topbar)) {
    $hide_topbar = false;
}
if (!$hide_topbar && file_exists(dirname(__DIR__, 2) . '/includes/topbar.php')) {
    include_once dirname(__DIR__, 2) . '/includes/topbar.php';
}
?>
<?php include_once dirname(__DIR__, 2) . '/components/course_modal.php'; ?>


<main class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:px-8">
    <?php if ($has_access): ?>
        <!-- Bouton Exercice IA (réservé connectés) -->
        <div class="flex flex-wrap justify-end gap-3 mb-4">
            <button id="btn-quiz-ia" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700 transition" aria-haspopup="dialog" aria-controls="modal-quiz-ia">
                🤖 Exercice IA
            </button>
            <a href="<?php echo function_exists('site_url') ? site_url('revisions') : 'index.php?page=revisions'; ?>" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-slate-900 text-white font-semibold shadow hover:bg-slate-800 transition">
                📚 Révisions IA
            </a>
        </div>

        <!-- Modal Exercice IA -->
        <div id="modal-quiz-ia" class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-black/40 p-4 md:items-center" aria-hidden="true">
            <div class="flex w-full max-w-4xl max-h-[90vh] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" role="dialog" aria-labelledby="modal-exercice-ia-title" aria-modal="true">
                <header class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5 shrink-0">
                    <div>
                        <h2 id="modal-exercice-ia-title" class="text-2xl font-bold text-emerald-700">Générer un exercice IA</h2>
                        <p class="mt-1 text-sm text-slate-500">Choisis une matière et décris un thème ou un titre précis.</p>
                    </div>
                    <button id="close-modal-quiz-ia" type="button" class="text-3xl leading-none text-gray-400 transition hover:text-gray-700" aria-label="Fermer">&times;</button>
                </header>

                <div class="flex-1 min-h-0 overflow-y-auto px-6 py-5">
                    <form id="form-quiz-ia" class="flex flex-col gap-4">
                        <input type="hidden" id="quiz-niveau" name="niveau" value="">
                        <div>
                            <label for="quiz-matiere" class="block font-semibold mb-1">Matière</label>
                            <select id="quiz-matiere" name="matiere" class="w-full border rounded px-3 py-2">
                                <option value="">Sélectionner…</option>
                                <option value="Mathématiques">Mathématiques</option>
                                <option value="Français">Français</option>
                                <option value="Physique-Chimie">Physique-Chimie</option>
                                <option value="SVT">SVT</option>
                                <option value="Histoire-Géographie">Histoire-Géographie</option>
                                <option value="Anglais">Anglais</option>
                                <option value="Espagnol">Espagnol</option>
                                <option value="Philosophie">Philosophie</option>
                            </select>
                        </div>
                        <div>
                            <label for="quiz-theme" class="block font-semibold mb-1">Thème ou titre</label>
                            <input id="quiz-theme" name="theme" class="w-full border rounded px-3 py-2" placeholder="Ex. Les fractions, Les accords du participe passé, Les volcans" aria-describedby="quiz-theme-help" />
                            <p id="quiz-theme-help" class="mt-1 text-sm text-slate-500">Décris la notion, le chapitre ou le titre que tu veux travailler.</p>
                        </div>
                        <div>
                            <label for="quiz-type" class="block font-semibold mb-1">Type d’exercice (optionnel)</label>
                            <input id="quiz-type" name="type" class="w-full border rounded px-3 py-2" placeholder="QCM, application, problème, etc." />
                        </div>
                        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded font-bold hover:bg-emerald-700 transition">Générer les exercices</button>
                    </form>
                    <div id="quiz-ia-result" class="mt-6 hidden overflow-y-auto pr-1" aria-live="polite"></div>
                </div>
                <footer id="modal-quiz-ia-footer" class="hidden shrink-0 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" id="btn-save-generated-quiz" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-700 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-blue-800 transition">
                            📂 Sauvegarder dans la bibliothèque
                        </button>
                        <button type="button" id="btn-regenerate-quiz" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-semibold hover:bg-slate-100 transition">
                            🔄 Modifier
                        </button>
                        <button type="button" id="btn-close-modal-footer" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-semibold hover:bg-slate-100 transition">
                            Fermer
                        </button>
                    </div>
                </footer>
            </div>
        </div>
    <?php endif; ?>
    <div class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-8 text-center shadow-[0_24px_70px_-30px_rgba(15,23,42,0.28)] backdrop-blur-sm">
        <h1 class="mb-4 flex items-center justify-center gap-3 text-4xl font-bold text-slate-800 md:text-5xl">📝 Exercices Interactifs</h1>
        <p class="mb-6 text-xl text-slate-600">Entraîne-toi avec des exercices adaptés à ton niveau !</p>
        <?php if (empty($_SESSION['user_id'])): ?>
            <div id="mode-emploi-exercices" class="mx-auto max-w-2xl banner-theme rounded-xl p-4 mb-4 flex flex-col items-center gap-2 shadow">
                <div class="flex items-center gap-2 text-theme text-lg font-semibold">
                    <span class="text-2xl">ℹ️</span>
                    <span id="mode-emploi-message">Choisis d'abord ton niveau scolaire, puis une matière pour découvrir un exercice interactif adapté !</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Détection du niveau scolaire de l'élève connecté
    $niveau = $_SESSION['user_level'] ?? '';
    $niveau_normalise = strtolower(preg_replace('/[^a-z0-9]/i', '', $niveau));
    // Si élève connecté : afficher directement la section matière, sinon afficher la section niveau
    if (empty($niveau_normalise)) :
    ?>
        <section class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="niveau-section">
            <div class="mb-6 text-center">
                <h2 class="mb-2 text-2xl font-bold text-slate-800">Choisis ton niveau</h2>
                <p class="text-slate-600">Accède directement aux exercices adaptés à ta classe.</p>
            </div>
            <?php
            $niveau_links = [
                '6eme' => [
                    'label' => '📘 6ème',
                    'href' => function_exists('site_url') ? site_url('college/6eme/exercices-6eme') : '/public/pages/college/6eme/exercices-6eme.php',
                    'class' => 'text-blue-700 hover:bg-blue-50',
                ],
                '5eme' => [
                    'label' => '📗 5ème',
                    'href' => function_exists('site_url') ? site_url('college/5eme/exercices-5eme') : '/public/pages/college/5eme/exercices-5eme.php',
                    'class' => 'text-green-700 hover:bg-green-50',
                ],
                '4eme' => [
                    'label' => '📙 4ème',
                    'href' => function_exists('site_url') ? site_url('college/4eme/exercices-4eme') : '/public/pages/college/4eme/exercices-4eme.php',
                    'class' => 'text-yellow-700 hover:bg-yellow-50',
                ],
                '3eme' => [
                    'label' => '📕 3ème',
                    'href' => function_exists('site_url') ? site_url('college/3eme/exercices-3eme') : '/public/pages/college/3eme/exercices-3eme.php',
                    'class' => 'text-red-700 hover:bg-red-50',
                ],
                '2nde' => [
                    'label' => '📓 Seconde',
                    'href' => function_exists('site_url') ? site_url('lycee/2nde/exercices-2nde') : '/public/pages/lycee/2nde/exercices-2nde.php',
                    'class' => 'text-indigo-700 hover:bg-indigo-50',
                ],
                '1ere' => [
                    'label' => '📒 Première',
                    'href' => function_exists('site_url') ? site_url('lycee/1ere/exercices-1ere') : '/public/pages/lycee/1ere/exercices-1ere.php',
                    'class' => 'text-purple-700 hover:bg-purple-50',
                ],
                'terminale' => [
                    'label' => '📔 Terminale',
                    'href' => function_exists('site_url') ? site_url('lycee/terminale/exercices-terminale') : '/public/pages/lycee/terminale/exercices-terminale.php',
                    'class' => 'text-pink-700 hover:bg-pink-50',
                ],
                'bac' => [
                    'label' => '🏆 BAC',
                    'href' => function_exists('site_url') ? site_url('bac/exercices-bac') : '/public/pages/bac/exercices-bac.php',
                    'class' => 'text-amber-700 hover:bg-amber-50',
                ],
            ];
            echo '<div id="niveau-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 justify-center">';
            foreach ($niveau_links as $key => $link) {
                echo '<button type="button" class="niveau-btn block px-4 py-6 rounded-2xl bg-white shadow text-center font-semibold ' . $link['class'] . ' transition" data-level="' . htmlspecialchars($key) . '">' . $link['label'] . '</button>';
            }
            echo '</div>';
            ?>
        </section>
    <?php else: ?>
        <script>
            window.EXERCICE_USER_LEVEL = '<?php echo htmlspecialchars($niveau_normalise); ?>';
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Masquer la section niveau, afficher la section matière
                var niveauSection = document.getElementById('niveau-section');
                var matiereSection = document.getElementById('matiere-section');
                if (niveauSection) niveauSection.style.display = 'none';
                if (matiereSection) matiereSection.style.display = '';
            });
        </script>
    <?php endif; ?>

    <!-- Section Par matière -->
    <section class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="matiere-section">
        <div class="mb-6 text-center">
            <h2 class="mb-2 text-2xl font-bold text-slate-800">Choisis ta matière</h2>
            <p class="text-slate-600">Retrouve tes exercices par discipline pour cibler tes révisions.</p>
        </div>
        <div id="matiere-list" class="flex flex-wrap gap-2 justify-center">
            <?php
            $defaultSubjects = [
                'Mathématiques',
                'Français',
                'Physique-Chimie',
                'SVT',
                'Histoire-Géographie',
                'Anglais',
                'Espagnol',
                'Philosophie',
            ];
            foreach ($defaultSubjects as $subject) {
                echo '<button type="button" class="subject-filter-btn px-4 py-2 rounded-full bg-slate-100 text-slate-700 font-semibold shadow hover:bg-blue-100 transition" data-subject="' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</button>';
            }
            ?>
        </div>
    </section>

    <!-- Bloc exercice aléatoire principal -->
    <section class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="random-exercise-section">
        <div class="mb-6 text-center">
            <h2 class="mb-2 text-xl font-bold text-blue-700">🎯 Ton exercice à faire</h2>
            <p class="text-slate-600">Un exercice aléatoire de ton niveau, ou choisis une matière ci-dessous.</p>
        </div>
        <div id="random-exercise-card" class="flex justify-center"></div>
        <div class="flex justify-center mt-4">
            <button id="btn-new-random-exercise" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl btn-theme-primary font-semibold shadow transition">
                🔄 Voir un autre exercice
            </button>
        </div>
        <div id="random-exercise-error" class="text-center text-red-600 mt-4 hidden"></div>
    </section>
    <!-- ...existing code... -->
    <!-- Fin du bloc visiteur, pas de endif ici -->

    <?php if (!$has_access): ?>
        <!-- Panneau coach déplacé tout en bas, après toutes les infos -->
        <div class="banner-theme mt-6 rounded-[2rem] border border-slate-200/70 p-8 shadow-sm">
            <h2>🔒 Accède à des centaines d'exercices interactifs</h2>
            <p>
                Découvre ci-dessous un aperçu de nos exercices, mais pour t'entraîner et progresser avec un suivi personnalisé,
                crée ton compte gratuit !
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8">
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">📚 Exercices Multi-niveaux</h3>
                    <p class="text-slate-600">Du collège au BAC : 6ème, 5ème, 4ème, 3ème, Seconde, Première, Terminale</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">✅ Corrections Détaillées</h3>
                    <p class="text-slate-600">Chaque exercice avec sa correction expliquée pas à pas</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">📊 Suivi de Progression</h3>
                    <p class="text-slate-600">Statistiques personnalisées pour suivre tes progrès</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">🎯 Exercices Adaptatifs</h3>
                    <p class="text-slate-600">Système qui s'adapte à ton rythme et tes difficultés</p>
                </div>
            </div>
            <p><strong>💡 Plus tu t'entraînes, plus tu progresses !</strong></p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
                <a href="<?php echo function_exists('site_url') ? site_url('register') : '/public/pages/register.php'; ?>" class="btn-theme-primary inline-flex items-center justify-center px-6 py-3 font-semibold rounded-xl shadow-lg">✨ Créer mon compte gratuit</a>
                <a href="<?php echo function_exists('site_url') ? site_url('login') : '/public/pages/login.php'; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl hover:bg-gray-700 transition-colors shadow-lg">🔑 Me connecter</a>
            </div>
        </div>
    <?php endif; ?>
    <?php
    // Détection du niveau scolaire normalisé (ex: Premiere, Terminale...)
    $niveau_js = '';
    if (!empty($_SESSION['user_level'])) {
        // Utiliser la valeur brute pour l'API
        $niveau_js = strtolower(preg_replace('/[^a-z0-9]/i', '', iconv('UTF-8', 'ASCII//TRANSLIT', $_SESSION['user_level'])));
    }
    if ($niveau_js) {
        echo '<script>window.__USER_LEVEL = "' . htmlspecialchars($niveau_js, ENT_QUOTES) . '";</script>';
    }
    ?>
    <!-- Auto-load exercice aléatoire pour utilisateur connecté -->
    <script>
        // Fonction réutilisable : charger un exercice aléatoire (optionnellement filtré par matière)
        function loadExercise(userLevel, subjectFilter = null) {
            const cardContainer = document.getElementById('random-exercise-card');
            const errorDiv = document.getElementById('random-exercise-error');

            if (!cardContainer) return;

            cardContainer.innerHTML = '<div class="text-slate-400 py-8">Chargement de ton exercice...</div>';
            if (errorDiv) errorDiv.classList.add('hidden');

            // Construire l'URL API
            let baseUrl = (typeof window.baseUrl !== 'undefined' && window.baseUrl) ? window.baseUrl : '';
            if (baseUrl.endsWith('/')) baseUrl = baseUrl.slice(0, -1);
            let url = `${baseUrl}/index.php?page=api/exercices/get_exercises&action=exercises&level=${encodeURIComponent(userLevel)}`;

            // Ajouter le filtre matière si spécifié
            if (subjectFilter) {
                url += `&subject=${encodeURIComponent(subjectFilter)}`;
            }

            // Fetch exercices du niveau (optionnellement filtré)
            fetch(url)
                .then(r => r.clone().text().then(raw => {
                    try {
                        return JSON.parse(raw);
                    } catch (e) {
                        throw e;
                    }
                }))
                .then(data => {
                    let exercises = [];
                    if (Array.isArray(data.exercises)) exercises = data.exercises;
                    else if (data.data && Array.isArray(data.data.exercises)) exercises = data.data.exercises;

                    if (!exercises.length) {
                        cardContainer.innerHTML = '';
                        if (errorDiv) {
                            const filterText = subjectFilter ? ` en ${subjectFilter}` : '';
                            errorDiv.textContent = `Aucun exercice trouvé pour ce niveau${filterText}.`;
                            errorDiv.classList.remove('hidden');
                        }
                        return;
                    }

                    // Sélectionner un exercice aléatoire
                    const ex = exercises[Math.floor(Math.random() * exercises.length)];
                    const htmlUrl = `${baseUrl}/index.php?page=api/exercices/get_exercises&action=exercise_html&id=${ex.Id}`;

                    // Fetch le HTML de l'exercice
                    fetch(htmlUrl)
                        .then(r2 => r2.json())
                        .then(data2 => {
                            let html = data2.html;
                            if (!html && data2.data && data2.data.html) html = data2.data.html;
                            if (html) {
                                cardContainer.innerHTML = html;
                            } else {
                                cardContainer.innerHTML = '<div class="text-red-600">Erreur de rendu de l\'exercice.</div>';
                            }
                        })
                        .catch(err => {
                            console.error('Error loading exercise HTML:', err);
                            cardContainer.innerHTML = '<div class="text-red-600">Erreur lors du chargement de l\'exercice.</div>';
                        });
                })
                .catch(err => {
                    console.error('Error loading exercises:', err);
                    cardContainer.innerHTML = '';
                    if (errorDiv) {
                        errorDiv.textContent = 'Erreur lors du chargement des exercices.';
                        errorDiv.classList.remove('hidden');
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Si utilisateur connecté (window.__USER_LEVEL défini), auto-load son exercice aléatoire
            if (typeof window.__USER_LEVEL !== 'undefined' && window.__USER_LEVEL) {
                const userLevel = window.__USER_LEVEL;
                loadExercise(userLevel);

                // Attacher le listener au bouton "Voir un autre exercice"
                const btnNew = document.getElementById('btn-new-random-exercise');
                if (btnNew) {
                    btnNew.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Charger sans filtre matière (reset)
                        loadExercise(userLevel);
                    });
                }

                // Attacher les listeners aux boutons matière
                const subjectButtons = document.querySelectorAll('.subject-filter-btn');
                subjectButtons.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const subject = this.getAttribute('data-subject');
                        console.log('[DEBUG] Clic matière:', subject, 'userLevel:', userLevel);
                        if (typeof openCourseModal !== 'undefined') {
                            console.log('[DEBUG] openCourseModal est défini');
                        } else {
                            console.warn('[DEBUG] openCourseModal NON défini');
                        }
                        if (subject) {
                            loadExercise(userLevel, subject);
                        }
                    });
                });
            }
        });
    </script>
    <script src="<?php
                    if (function_exists('asset_url')) {
                        echo asset_url('assets/js/exercices.js');
                    } else {
                        $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
                        echo htmlspecialchars($assetBase . '/assets/js/exercices.js', ENT_QUOTES);
                    }
                    ?>"></script>
    <script src="<?php
                    if (function_exists('asset_url')) {
                        echo asset_url('assets/js/course_modal.js');
                    } else {
                        $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
                        echo htmlspecialchars($assetBase . '/assets/js/course_modal.js', ENT_QUOTES);
                    }
                    ?>"></script>
    <!-- Exercices interactifs (nécessaires pour les vérifications) -->
    <script src="<?php
                    if (function_exists('asset_url')) {
                        echo asset_url('assets/js/interactive-exercises.js');
                    } else {
                        $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
                        echo htmlspecialchars($assetBase . '/assets/js/interactive-exercises.js', ENT_QUOTES);
                    }
                    ?>"></script>

    <!-- Coach WebM -->
    <script src="<?php
                    if (function_exists('asset_url')) {
                        echo asset_url('assets/js/coach-webm.js');
                    } else {
                        $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
                        echo htmlspecialchars($assetBase . '/assets/js/coach-webm.js', ENT_QUOTES);
                    }
                    ?>"></script>

    <?php include __DIR__ . '/../../components/course_modal.php'; ?>

</main>

<script>
    // Encapsulation pour éviter la pollution globale
    (function() {
        const btnQuizIa = document.getElementById('btn-quiz-ia');
        const modalQuizIa = document.getElementById('modal-quiz-ia');
        const closeModalQuizIa = document.getElementById('close-modal-quiz-ia');
        const formQuizIa = document.getElementById('form-quiz-ia');
        const quizIaResult = document.getElementById('quiz-ia-result');
        const niveauInput = document.getElementById('quiz-niveau');
        const themeInput = document.getElementById('quiz-theme');

        function normalizeExerciseSchoolLevel(level) {
            let normalized = String(level || '').toLowerCase().replace(/é/g, 'e').replace(/è/g, 'e').replace(/ê/g, 'e');
            if (normalized === 'seconde') return '2nde';
            if (normalized === 'premiere' || normalized === 'premiere') return '1ere';
            if (normalized === 'terminale') return 'terminale';
            if (normalized === 'bac') return 'bac';
            return normalized;
        }
        const matiereInput = document.getElementById('quiz-matiere');
        const modalFooter = document.getElementById('modal-quiz-ia-footer');
        const btnSaveGeneratedQuiz = document.getElementById('btn-save-generated-quiz');
        const btnRegenerateQuiz = document.getElementById('btn-regenerate-quiz');
        const btnCloseModalFooter = document.getElementById('btn-close-modal-footer');
        let lastGeneratedQuiz = null;
        let savedQuizUrl = '';

        function setQuizSaveState(state, url = '') {
            if (!btnSaveGeneratedQuiz || !modalFooter) {
                return;
            }

            savedQuizUrl = url;

            if (state === 'saved') {
                btnSaveGeneratedQuiz.disabled = false;
                btnSaveGeneratedQuiz.innerHTML = '📖 Voir l’exercice IA';
                btnSaveGeneratedQuiz.classList.remove('bg-blue-700');
                btnSaveGeneratedQuiz.classList.add('bg-green-600');
                btnSaveGeneratedQuiz.dataset.state = 'view';
                return;
            }

            btnSaveGeneratedQuiz.disabled = false;
            btnSaveGeneratedQuiz.innerHTML = '📂 Sauvegarder dans la bibliothèque';
            btnSaveGeneratedQuiz.classList.remove('bg-green-600');
            btnSaveGeneratedQuiz.classList.add('bg-blue-700');
            btnSaveGeneratedQuiz.dataset.state = 'save';
        }

        function showQuizForm() {
            if (formQuizIa) {
                formQuizIa.classList.remove('hidden');
            }
            if (quizIaResult) {
                quizIaResult.classList.add('hidden');
                quizIaResult.innerHTML = '';
            }
            if (modalFooter) {
                modalFooter.classList.add('hidden');
            }
            lastGeneratedQuiz = null;
            setQuizSaveState('save');
        }

        if (btnQuizIa && modalQuizIa && closeModalQuizIa) {
            btnQuizIa.addEventListener('click', function() {
                modalQuizIa.classList.add('flex');
                modalQuizIa.classList.remove('hidden');
                showQuizForm();

                const lockedLevel = normalizeExerciseSchoolLevel(
                    String(window.EXERCICE_USER_LEVEL || window.__USER_LEVEL || window.userLevel || '').trim(),
                );

                if (niveauInput && lockedLevel) {
                    niveauInput.value = lockedLevel;
                }

                if (themeInput) {
                    themeInput.focus();
                }
                if (modalFooter) {
                    modalFooter.classList.add('hidden');
                }
                setQuizSaveState('save');
            });
            closeModalQuizIa.addEventListener('click', function() {
                modalQuizIa.classList.remove('flex');
                modalQuizIa.classList.add('hidden');
            });
            if (btnCloseModalFooter) {
                btnCloseModalFooter.addEventListener('click', function() {
                    modalQuizIa.classList.remove('flex');
                    modalQuizIa.classList.add('hidden');
                });
            }
            if (btnRegenerateQuiz) {
                btnRegenerateQuiz.addEventListener('click', function() {
                    showQuizForm();
                });
            }
            modalQuizIa.addEventListener('click', function(e) {
                if (e.target === modalQuizIa) {
                    modalQuizIa.classList.remove('flex');
                    modalQuizIa.classList.add('hidden');
                }
            });
        }

        async function saveGeneratedQuiz() {
            if (!lastGeneratedQuiz || !btnSaveGeneratedQuiz) {
                return;
            }

            if (btnSaveGeneratedQuiz.dataset.state === 'view' && savedQuizUrl) {
                window.location.href = savedQuizUrl;
                return;
            }

            btnSaveGeneratedQuiz.disabled = true;
            btnSaveGeneratedQuiz.innerHTML = '⌛ Sauvegarde en cours...';

            try {
                const saveUrl = (window.baseUrl || '') + '/index.php?page=api/ia/save_generated_quiz';
                const csrfToken = window.csrfToken || '';
                const saveRes = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken,
                    },
                    body: JSON.stringify({
                        questions: lastGeneratedQuiz.questions,
                        level: lastGeneratedQuiz.level,
                        subject: lastGeneratedQuiz.subject,
                        csrf_token: csrfToken,
                    }),
                });
                const saveData = await saveRes.json();
                if (!saveRes.ok || !saveData.success) {
                    throw new Error(saveData.error || 'Erreur de sauvegarde');
                }

                const exerciseUrl =
                    typeof saveData.exercise_url === 'string' && saveData.exercise_url.trim() !== '' ?
                    saveData.exercise_url.trim() :
                    (saveData.data && typeof saveData.data.exercise_url === 'string' ?
                        saveData.data.exercise_url.trim() :
                        '');

                setQuizSaveState('saved', exerciseUrl);
                alert(saveData.message || 'Quiz sauvegardé.');
            } catch (err) {
                alert('Erreur lors de la sauvegarde : ' + err.message);
                setQuizSaveState('save');
            }
        }

        if (btnSaveGeneratedQuiz) {
            btnSaveGeneratedQuiz.addEventListener('click', saveGeneratedQuiz);
        }

        if (formQuizIa) {
            formQuizIa.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (quizIaResult) {
                    quizIaResult.classList.remove('hidden');
                    quizIaResult.innerHTML = '<div class="text-center text-slate-500 py-8">Génération des exercices en cours…</div>';
                }
                let niveau = niveauInput ? niveauInput.value.trim() : '';
                const theme = themeInput ? themeInput.value.trim() : '';
                const matiere = matiereInput ? matiereInput.value.trim() : '';
                let type = formQuizIa.type.value.trim();

                if (!niveau) {
                    niveau = normalizeExerciseSchoolLevel(
                        String(window.EXERCICE_USER_LEVEL || window.__USER_LEVEL || window.userLevel || '').trim(),
                    );
                }

                // Si type n'est pas renseigné, on choisit un type aléatoire par défaut
                if (!type) {
                    const randomTypes = ['QCM', 'vrai/faux', 'QCU', 'texte'];
                    type = randomTypes[Math.floor(Math.random() * randomTypes.length)];
                    formQuizIa.type.value = type; // afficher le type choisi
                }

                if (!niveau || !matiere || !theme) {
                    if (quizIaResult) {
                        quizIaResult.innerHTML = '<div class="text-red-600 py-4">Merci de choisir une matière et de préciser un thème ou un titre.</div>';
                    }
                    return;
                }

                try {
                    const apiUrl = (window.baseUrl || '') + '/index.php?page=api/ia/generate_quiz';
                    const csrfToken = window.csrfToken || '';
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': csrfToken
                        },
                        body: JSON.stringify({
                            niveau,
                            matiere,
                            theme,
                            type,
                            output_mode: 'exercise',
                            context_page: 'exercices',
                            csrf_token: csrfToken
                        })
                    });
                    if (!response.ok) {
                        throw new Error('Réponse serveur invalide (' + response.status + ')');
                    }
                    const data = await response.json();
                    if (data && data.quiz_html) {
                        formQuizIa.classList.add('hidden');
                        quizIaResult.innerHTML = data.quiz_html;
                        if (typeof window.InteractiveExercises !== 'undefined') window.InteractiveExercises.initAll();

                        if (modalFooter) {
                            modalFooter.classList.remove('hidden');
                        }

                        lastGeneratedQuiz = {
                            questions: data.questions || [],
                            level: data.level || niveau,
                            subject: data.subject || matiere,
                        };
                        setQuizSaveState('save');
                    } else if (data && data.error) {
                        quizIaResult.innerHTML = '<div><b>Erreur :</b> ' + data.error + '</div>';
                        if (modalFooter) {
                            modalFooter.classList.add('hidden');
                        }
                        lastGeneratedQuiz = null;
                    } else {
                        quizIaResult.innerHTML = '<div class="text-red-600">Aucun exercice généré. Réessaie ou change les paramètres.</div>';
                        if (modalFooter) {
                            modalFooter.classList.add('hidden');
                        }
                        lastGeneratedQuiz = null;
                    }
                    quizIaResult.scrollTop = 0;
                } catch (err) {
                    quizIaResult.innerHTML = '<div class="text-red-600">Erreur : ' + err.message + '</div>';
                    if (modalFooter) {
                        modalFooter.classList.add('hidden');
                    }
                    quizIaResult.scrollTop = 0;
                    lastGeneratedQuiz = null;
                }
            });
        }
    })();
</script>