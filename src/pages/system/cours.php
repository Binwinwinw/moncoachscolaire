<?php
/*
 * Page publique des cours (route ?page=cours)
 * - Affichage centralisé, adapte le contenu selon le contexte (visiteur, loggé, démo)
 * - Séparation stricte des accès, panneau coach uniquement pour visiteurs
 * - Liens dynamiques sécurisés (site_url ou fallback)
 * - Toutes les données issues de l’utilisateur ou de la session sont échappées
 * - Toute confusion sur la page à modifier doit être signalée à l’utilisateur (règle IA)
 */



$page_title = 'Cours - MonCoachScolaire';
$page_css = 'cours.css';
$page_class = 'page-cours';

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

<?php
// Détection du niveau scolaire de l'élève connecté
$niveau = $_SESSION['user_level'] ?? '';
$niveau_normalise = strtolower((string) $niveau);
if (function_exists('iconv')) {
    $niveau_normalise = iconv('UTF-8', 'ASCII//TRANSLIT', $niveau_normalise);
}
$niveau_normalise = preg_replace('/[^a-z0-9]/i', '', $niveau_normalise);
$niveau_normalise = strtr($niveau_normalise, [
    '6me' => '6eme',
    '5me' => '5eme',
    '4me' => '4eme',
    '3me' => '3eme',
    '1ere' => '1ere',
    'terminale' => 'terminale',
    'bac' => 'bac',
    'seconde' => '2nde',
    'premiere' => '1ere',
    'quatrieme' => '4eme',
    'cinquieme' => '5eme',
    'sixieme' => '6eme',
    'troisieme' => '3eme',
]);
?>

<main class="main-content mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 md:px-6 lg:px-8">
    <?php if ($has_access): ?>
        <!-- Bouton Cours IA (réservé connectés) -->
        <div class="flex flex-wrap justify-end gap-3 mb-4">
            <button id="btn-quiz-ia" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700 transition">
                🤖 Cours IA
            </button>
            <a href="<?php echo function_exists('site_url') ? site_url('revisions') : 'index.php?page=revisions'; ?>" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-slate-900 text-white font-semibold shadow hover:bg-slate-800 transition">
                📚 Révisions IA
            </a>
        </div>

        <!-- Modal Cours IA -->
        <div id="modal-quiz-ia" class="modal-cours-ia fixed inset-0 z-50 items-center justify-center bg-black/50 p-4 hidden" aria-hidden="true">
            <div id="modal-quiz-ia-panel" class="modal-cours-ia-panel bg-white rounded-2xl shadow-2xl w-full relative flex flex-col" role="dialog" aria-labelledby="modal-cours-ia-title" aria-modal="true">
                <header class="modal-cours-ia-header flex items-start justify-between gap-4 px-6 pt-6 pb-4 border-b border-slate-100 shrink-0">
                    <div>
                        <h2 id="modal-cours-ia-title" class="text-2xl font-bold text-emerald-700">Générer un cours IA</h2>
                        <p id="modal-cours-ia-subtitle" class="text-sm text-slate-500 mt-1">
                            <?php if (!empty($niveau_normalise)): ?>
                                Ton niveau scolaire est détecté automatiquement, choisis une matière et un thème optionnel.
                            <?php else: ?>
                                Choisis le niveau, la matière et un thème optionnel.
                            <?php endif; ?>
                        </p>
                    </div>
                </header>

                <div class="modal-cours-ia-body flex-1 min-h-0 overflow-y-auto px-6 py-4">
                    <form id="form-quiz-ia" class="modal-cours-ia-form flex flex-col gap-4">
                        <div id="quiz-niveau-container">
                            <label for="quiz-niveau" class="block font-semibold mb-1">Niveau scolaire</label>
                            <select id="quiz-niveau" name="niveau" class="w-full border rounded-lg px-3 py-2">
                                <option value="">Sélectionner…</option>
                                <option value="6eme">6ème</option>
                                <option value="5eme">5ème</option>
                                <option value="4eme">4ème</option>
                                <option value="3eme">3ème</option>
                                <option value="2nde">Seconde</option>
                                <option value="1ere">Première</option>
                                <option value="terminale">Terminale</option>
                                <option value="bac">BAC</option>
                            </select>
                        </div>
                        <input type="hidden" id="quiz-niveau-hidden" name="niveau_hidden" value="<?php echo htmlspecialchars($niveau_normalise); ?>" />
                        <div>
                            <label for="quiz-matiere" class="block font-semibold mb-1">Matière</label>
                            <select id="quiz-matiere" name="matiere" class="w-full border rounded-lg px-3 py-2">
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
                            <label for="quiz-theme" class="block font-semibold mb-1">Thème du cours (optionnel)</label>
                            <input id="quiz-theme" name="theme" class="w-full border rounded-lg px-3 py-2" placeholder="Fractions, révolution française, etc." aria-describedby="quiz-theme-help" />
                            <p id="quiz-theme-help" class="mt-1 text-sm text-slate-500">Tu peux saisir un chapitre, une notion ou un titre précis.</p>
                        </div>
                        <button type="submit" class="bg-emerald-600 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-emerald-700 transition">Générer le cours</button>
                    </form>
                    <div id="quiz-ia-result" class="modal-cours-ia-result hidden" aria-live="polite"></div>
                </div>

                <footer id="modal-quiz-ia-footer" class="modal-cours-ia-footer hidden shrink-0 px-6 py-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" id="btn-save-generated-cours" class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-700 text-white px-4 py-2.5 rounded-lg font-bold hover:bg-blue-800 transition">
                            📂 Sauvegarder dans la bibliothèque
                        </button>
                        <button type="button" id="btn-regenerate-cours" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-semibold hover:bg-slate-100 transition">
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
    <div class="mb-8 rounded-[2rem] border border-slate-200/70 bg-white/90 p-8 text-center shadow-[0_24px_70px_-30px_rgba(15,23,42,0.28)] backdrop-blur-sm">
        <h1 class="mb-4 flex items-center justify-center gap-3 text-4xl font-bold text-slate-800 md:text-5xl">📚 Cours Interactifs</h1>
        <p class="mb-6 text-xl text-slate-600">Étudie avec des cours adaptés à ton niveau !</p>
        <?php if (empty($_SESSION['user_id'])): ?>
            <div id="mode-emploi-cours" class="mx-auto max-w-2xl banner-theme rounded-xl p-4 mb-4 flex flex-col items-center gap-2 shadow">
                <div class="flex items-center gap-2 text-theme text-lg font-semibold">
                    <span class="text-2xl">ℹ️</span>
                    <span id="mode-emploi-message">Choisis d'abord ton niveau scolaire, puis une matière pour découvrir un cours interactif adapté !</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Détection du niveau scolaire de l'élève connecté
    $niveau = $_SESSION['user_level'] ?? '';
    $niveau_normalise = strtolower((string) $niveau);
    if (function_exists('iconv')) {
        $niveau_normalise = iconv('UTF-8', 'ASCII//TRANSLIT', $niveau_normalise);
    }
    $niveau_normalise = preg_replace('/[^a-z0-9]/i', '', $niveau_normalise);
    $niveau_normalise = strtr($niveau_normalise, [
        '6me' => '6eme',
        '5me' => '5eme',
        '4me' => '4eme',
        '3me' => '3eme',
        '1ere' => '1ere',
        'terminale' => 'terminale',
        'bac' => 'bac',
        'seconde' => '2nde',
        'premiere' => '1ere',
        'quatrieme' => '4eme',
        'cinquieme' => '5eme',
        'sixieme' => '6eme',
        'troisieme' => '3eme',
    ]);
    // Si élève connecté : afficher directement la section matière, sinon afficher la section niveau
    if (empty($niveau_normalise)) :
    ?>
        <section class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="niveau-section">
            <div class="mb-6 text-center">
                <h2 class="mb-2 text-2xl font-bold text-slate-800">Choisis ton niveau</h2>
                <p class="text-slate-600">Accède directement aux cours adaptés à ta classe.</p>
            </div>
            <?php
            $niveau_links = [
                '6eme' => [
                    'label' => '📘 6ème',
                    'href' => function_exists('site_url') ? site_url('college/6eme/cours-6eme') : '/public/pages/college/6eme/exercices-6eme.php',
                    'class' => 'text-blue-700 hover:bg-blue-50',
                ],
                '5eme' => [
                    'label' => '📗 5ème',
                    'href' => function_exists('site_url') ? site_url('college/5eme/cours-5eme') : '/public/pages/college/5eme/cours-5eme.php',
                    'class' => 'text-green-700 hover:bg-green-50',
                ],
                '4eme' => [
                    'label' => '📙 4ème',
                    'href' => function_exists('site_url') ? site_url('college/4eme/cours-4eme') : '/public/pages/college/4eme/cours-4eme.php',
                    'class' => 'text-yellow-700 hover:bg-yellow-50',
                ],
                '3eme' => [
                    'label' => '📕 3ème',
                    'href' => function_exists('site_url') ? site_url('college/3eme/cours-3eme') : '/public/pages/college/3eme/cours-3eme.php',
                    'class' => 'text-red-700 hover:bg-red-50',
                ],
                '2nde' => [
                    'label' => '📓 Seconde',
                    'href' => function_exists('site_url') ? site_url('lycee/2nde/cours-2nde') : '/public/pages/lycee/2nde/cours-2nde.php',
                    'class' => 'text-indigo-700 hover:bg-indigo-50',
                ],
                '1ere' => [
                    'label' => '📒 Première',
                    'href' => function_exists('site_url') ? site_url('lycee/1ere/cours-1ere') : '/public/pages/lycee/1ere/cours-1ere.php',
                    'class' => 'text-purple-700 hover:bg-purple-50',
                ],
                'terminale' => [
                    'label' => '📔 Terminale',
                    'href' => function_exists('site_url') ? site_url('lycee/terminale/cours-terminale') : '/public/pages/lycee/terminale/cours-terminale.php',
                    'class' => 'text-pink-700 hover:bg-pink-50',
                ],
                'bac' => [
                    'label' => '🏆 BAC',
                    'href' => function_exists('site_url') ? site_url('bac/cours-bac') : '/public/pages/bac/cours-bac.php',
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
            window.COURS_USER_LEVEL = '<?php echo htmlspecialchars($niveau_normalise); ?>';
        </script>
    <?php endif; ?>

    <!-- Section Par matière -->
    <section class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="matiere-section">
        <div class="mb-6 text-center">
            <h2 class="mb-2 text-2xl font-bold text-slate-800">Choisis ta matière</h2>
            <p class="text-slate-600">Retrouve tes cours par discipline pour cibler tes révisions.</p>
        </div>
        <div id="matiere-list" class="flex flex-wrap gap-2 justify-center"></div>
    </section>

    <!-- Bloc cours aléatoire principal -->
    <section class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-6 shadow-sm backdrop-blur-sm" id="random-cours-section">
        <div class="mb-6 text-center">
            <h2 class="mb-2 text-xl font-bold text-blue-700">🎯 Ton cours à étudier</h2>
            <p class="text-slate-600">Un cours aléatoire de ton niveau, ou choisis une matière ci-dessous pour découvrir un cours interactif adapté !</p>
        </div>
        <div id="random-cours-card" class="flex justify-center"></div>
        <div class="flex justify-center mt-4">
            <button id="btn-new-random-cours" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl btn-theme-primary font-semibold shadow transition">
                🔄 Voir un autre cours
            </button>
        </div>
        <div id="random-cours-error" class="text-center text-red-600 mt-4 hidden"></div>
    </section>
    <!-- ...existing code... -->
    <!-- Fin du bloc visiteur, pas de endif ici -->

    <?php if (!$has_access): ?>
        <!-- Panneau coach déplacé tout en bas, après toutes les infos -->
        <div class="banner-theme mt-6 rounded-[2rem] border border-slate-200/70 p-8 shadow-sm">
            <h2>🔒 Accède à des centaines de cours interactifs</h2>
            <p>
                Découvre ci-dessous un aperçu de nos cours, mais pour t'entraîner et progresser avec un suivi personnalisé,
                crée ton compte gratuit !
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8">
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">📚 Cours Multi-niveaux</h3>
                    <p class="text-slate-600">Du collège au BAC : 6ème, 5ème, 4ème, 3ème, Seconde, Première, Terminale</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">✅ Corrections Détaillées</h3>
                    <p class="text-slate-600">Chaque cours avec sa correction détaillée</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">📊 Suivi de Progrès</h3>
                    <p class="text-slate-600">Statistiques personnalisées pour suivre tes progrès</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow flex flex-col items-center">
                    <h3 class="text-lg font-bold mb-2">🎯 Cours Adaptatifs</h3>
                    <p class="text-slate-600">Système qui s'adapte à ton rythme et tes difficultés pour progresser</p>
                </div>
            </div>
            <p><strong>💡 Plus tu t'entraînes, plus tu progresses !</strong></p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
                <a href="<?php echo function_exists('site_url') ? site_url('register') : '/public/pages/register.php'; ?>" class="btn-theme-primary inline-flex items-center justify-center px-6 py-3 font-semibold rounded-xl shadow-lg">✨ Créer mon compte gratuit</a>
                <a href="<?php echo function_exists('site_url') ? site_url('login') : '/public/pages/login.php'; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl hover:bg-gray-700 transition-colors shadow-lg">🔑 Me connecter</a>
            </div>
        </div>
    <?php endif; ?>
    <script src="<?php
                    if (function_exists('asset_url')) {
                        echo asset_url('assets/js/cours.js');
                    } else {
                        $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
                        echo htmlspecialchars($assetBase . '/assets/js/cours.js', ENT_QUOTES);
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
    <!-- Cours interactifs (nécessaires pour les vérifications) -->
    <script src="<?php
                    if (function_exists('asset_url')) {
                        echo asset_url('assets/js/interactive-cours.js');
                    } else {
                        $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
                        echo htmlspecialchars($assetBase . '/assets/js/interactive-cours.js', ENT_QUOTES);
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

</main>

<script>
    (function() {
        const btnQuizIa = document.getElementById('btn-quiz-ia');
        const modalQuizIa = document.getElementById('modal-quiz-ia');
        const modalPanel = document.getElementById('modal-quiz-ia-panel');
        const modalSubtitle = document.getElementById('modal-cours-ia-subtitle');
        const closeModalQuizIa = document.getElementById('close-modal-quiz-ia');
        const closeModalFooter = document.getElementById('btn-close-modal-footer');
        const btnRegenerate = document.getElementById('btn-regenerate-cours');
        const modalFooter = document.getElementById('modal-quiz-ia-footer');
        const btnSave = document.getElementById('btn-save-generated-cours');
        const formQuizIa = document.getElementById('form-quiz-ia');
        const quizIaResult = document.getElementById('quiz-ia-result');
        const themeInput = document.getElementById('quiz-theme');

        let lastGeneratedCourse = null;
        let savedCourseUrl = '';

        function normalizeModalSchoolLevel(level) {
            let normalized = String(level || '').toLowerCase().replace(/é/g, 'e').replace(/è/g, 'e').replace(/ê/g, 'e');
            if (normalized === 'seconde') return '2nde';
            if (normalized === 'premiere' || normalized === 'première') return '1ere';
            if (normalized === 'terminale') return 'terminale';
            if (normalized === 'bac') return 'bac';
            return normalized;
        }

        function setSaveButtonState(state, courseUrl = '') {
            if (!btnSave) return;

            savedCourseUrl = courseUrl || '';
            if (state === 'saved') {
                btnSave.disabled = false;
                btnSave.innerHTML = '📖 Voir le cours complet';
                btnSave.classList.remove('bg-blue-700');
                btnSave.classList.add('bg-green-600');
                btnSave.dataset.state = 'view';
                return;
            }

            btnSave.disabled = false;
            btnSave.innerHTML = '📂 Sauvegarder dans la bibliothèque';
            btnSave.classList.remove('bg-green-600');
            btnSave.classList.add('bg-blue-700');
            btnSave.dataset.state = 'save';
        }

        function resetModalPreview() {
            if (modalPanel) modalPanel.classList.remove('is-preview');
            if (formQuizIa) formQuizIa.classList.remove('hidden');
            if (quizIaResult) {
                quizIaResult.innerHTML = '';
                quizIaResult.classList.add('hidden');
            }
            if (modalFooter) modalFooter.classList.add('hidden');
            if (modalSubtitle) {
                modalSubtitle.textContent = 'Choisis le niveau, la matière et un thème optionnel.';
            }
            lastGeneratedCourse = null;
            setSaveButtonState('save');
        }

        function openModal() {
            if (!modalQuizIa) return;
            resetModalPreview();
            modalQuizIa.classList.add('flex');
            modalQuizIa.classList.remove('hidden');
            modalQuizIa.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-cours-ia-open');

            if (window.COURS_USER_LEVEL && document.getElementById('quiz-niveau')) {
                const normalizedLevel = normalizeModalSchoolLevel(window.COURS_USER_LEVEL);
                const niveauSelect = document.getElementById('quiz-niveau');
                const hiddenNiveau = document.getElementById('quiz-niveau-hidden');
                const matchingOption = niveauSelect.querySelector('option[value="' + normalizedLevel + '"]');
                if (matchingOption) {
                    matchingOption.selected = true;
                    if (hiddenNiveau) {
                        hiddenNiveau.value = normalizedLevel;
                    }
                    const container = document.getElementById('quiz-niveau-container');
                    if (container) container.style.display = 'none';
                    niveauSelect.disabled = true;
                    niveauSelect.setAttribute('aria-disabled', 'true');
                }
            }
        }

        function closeModal() {
            if (!modalQuizIa) return;
            modalQuizIa.classList.remove('flex');
            modalQuizIa.classList.add('hidden');
            modalQuizIa.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-cours-ia-open');
            resetModalPreview();
        }

        function showCoursePreview(previewHtml, courseData) {
            if (!quizIaResult || !modalPanel) return;

            lastGeneratedCourse = courseData;
            modalPanel.classList.add('is-preview');
            if (formQuizIa) formQuizIa.classList.add('hidden');
            quizIaResult.innerHTML = previewHtml;
            quizIaResult.classList.remove('hidden');
            if (modalFooter) modalFooter.classList.remove('hidden');
            if (modalSubtitle) {
                modalSubtitle.textContent = 'Aperçu du cours généré — tu peux le sauvegarder ou modifier les paramètres.';
            }
            if (typeof window.InteractiveCours !== 'undefined') {
                window.InteractiveCours.initAll();
            }
            quizIaResult.scrollTop = 0;
        }

        if (btnQuizIa) btnQuizIa.addEventListener('click', openModal);
        if (closeModalQuizIa) closeModalQuizIa.addEventListener('click', closeModal);
        if (closeModalFooter) closeModalFooter.addEventListener('click', closeModal);
        if (btnRegenerate) btnRegenerate.addEventListener('click', resetModalPreview);
        if (modalQuizIa) {
            modalQuizIa.addEventListener('click', function(e) {
                if (e.target === modalQuizIa) closeModal();
            });
        }

        function resolveSavedCourseUrl(saveData) {
            if (!saveData || typeof saveData !== 'object') {
                return '';
            }

            const baseUrl = (window.baseUrl || '').replace(/\/$/, '');
            const normalizeUrl = (url) => {
                if (typeof url !== 'string') {
                    return '';
                }
                const trimmed = url.trim();
                if (trimmed === '') {
                    return '';
                }
                if (/^[a-zA-Z][a-zA-Z0-9+.-]*:/.test(trimmed)) {
                    return trimmed;
                }
                if (trimmed.startsWith('/')) {
                    if (trimmed.startsWith('/index.php') && baseUrl) {
                        return `${baseUrl}${trimmed}`.replace(/\/\/+/g, '/');
                    }
                    return trimmed;
                }
                if (trimmed.startsWith('index.php')) {
                    return `${baseUrl}/${trimmed}`.replace(/\/\/+/g, '/');
                }
                if (trimmed.startsWith('?')) {
                    return `${baseUrl}/index.php${trimmed}`.replace(/\/\/+/g, '/');
                }
                return trimmed;
            };

            const candidateUrls = [];
            if (typeof saveData.course_url === 'string' && saveData.course_url.trim() !== '') {
                candidateUrls.push(saveData.course_url.trim());
            }
            if (saveData.data && typeof saveData.data.course_url === 'string' && saveData.data.course_url.trim() !== '') {
                candidateUrls.push(saveData.data.course_url.trim());
            }

            for (const url of candidateUrls) {
                const resolved = normalizeUrl(url);
                if (resolved !== '') {
                    return resolved;
                }
            }

            const courseId = saveData.course_id || (saveData.data && saveData.data.course_id);
            if (courseId) {
                return `${baseUrl}/index.php?page=view_course&id=${encodeURIComponent(courseId)}`.replace(/\/\/+/g, '/');
            }

            return '';
        }

        if (btnSave) {
            btnSave.addEventListener('click', async function() {
                if (btnSave.dataset.state === 'view' && savedCourseUrl) {
                    window.location.href = savedCourseUrl;
                    return;
                }

                if (!lastGeneratedCourse) return;

                btnSave.disabled = true;
                btnSave.innerHTML = '⌛ Sauvegarde en cours...';
                try {
                    const saveUrl = (window.baseUrl || '') + '/index.php?page=api/ia/save_generated_cours';
                    const csrfToken = window.csrfToken || '';
                    const saveRes = await fetch(saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': csrfToken
                        },
                        body: JSON.stringify({
                            cours: lastGeneratedCourse.cours,
                            level: lastGeneratedCourse.level,
                            matiere: lastGeneratedCourse.matiere,
                            csrf_token: csrfToken
                        })
                    });
                    const saveData = await saveRes.json();
                    if (saveData.success) {
                        const url = resolveSavedCourseUrl(saveData);
                        setSaveButtonState('saved', url);
                        alert(saveData.message || 'Cours sauvegardé.');
                    } else {
                        throw new Error(saveData.error || 'Erreur inconnue');
                    }
                } catch (err) {
                    alert('Erreur lors de la sauvegarde : ' + err.message);
                    setSaveButtonState('save');
                }
            });
        }

        if (formQuizIa) {
            formQuizIa.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (modalPanel) modalPanel.classList.add('is-preview');
                if (formQuizIa) formQuizIa.classList.add('hidden');
                if (modalFooter) modalFooter.classList.add('hidden');
                lastGeneratedCourse = null;
                quizIaResult.classList.remove('hidden');
                quizIaResult.innerHTML = '<div class="modal-cours-ia-loading text-center text-slate-500 py-8">Génération du cours en cours…</div>';
                if (modalSubtitle) {
                    modalSubtitle.textContent = 'Génération en cours, merci de patienter quelques secondes…';
                }

                let niveau = formQuizIa.niveau.value.trim();
                const matiere = formQuizIa.matiere.value.trim();
                const theme = themeInput ? themeInput.value.trim() : '';
                const hiddenNiveau = document.getElementById('quiz-niveau-hidden');

                if (hiddenNiveau && hiddenNiveau.value) {
                    niveau = normalizeModalSchoolLevel(hiddenNiveau.value.trim());
                }

                if (!niveau || !matiere) {
                    quizIaResult.innerHTML = '<div class="text-red-600 py-4">Merci de choisir une matière. Le niveau est automatiquement détecté pour les élèves connectés.</div>';
                    return;
                }

                try {
                    const apiUrl = (window.baseUrl || '') + '/index.php?page=api/ia/generate_cours';
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
                            context_page: 'cours',
                            csrf_token: csrfToken
                        })
                    });
                    const data = await response.json();
                    const previewHtml = data.cours_html || data.quiz_html;

                    if (data && previewHtml && data.cours) {
                        showCoursePreview(previewHtml, {
                            cours: data.cours,
                            level: data.level,
                            matiere: data.matiere || data.subject
                        });
                    } else if (data && data.error) {
                        quizIaResult.innerHTML = '<div class="text-red-600 py-4"><b>Erreur :</b> ' + data.error + '</div>';
                    } else {
                        quizIaResult.innerHTML = '<div class="text-red-600 py-4">Aucun cours généré. Réessaie ou change les paramètres.</div>';
                    }
                } catch (err) {
                    quizIaResult.innerHTML = '<div class="text-red-600 py-4">Erreur : ' + err.message + '</div>';
                }
            });
        }
    })();
</script>