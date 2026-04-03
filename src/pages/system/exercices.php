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


<main class="main-content max-w-7xl mx-auto px-4 py-8">
    <?php if ($has_access): ?>
    <!-- Bouton Quiz IA (réservé connectés) -->
    <div class="flex justify-end mb-4">
        <button id="btn-quiz-ia" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 text-white font-semibold shadow hover:bg-emerald-700 transition">
            🤖 Quiz IA
        </button>
    </div>

    <!-- Modal Quiz IA -->
    <div id="modal-quiz-ia" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full relative">
            <button id="close-modal-quiz-ia" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
            <h2 class="text-2xl font-bold mb-4 text-emerald-700">Générer un quiz IA</h2>
            <form id="form-quiz-ia" class="flex flex-col gap-4">
                <div id="quiz-niveau-container">
                    <label for="quiz-niveau" class="block font-semibold mb-1">Niveau scolaire</label>
                    <select id="quiz-niveau" name="niveau" class="w-full border rounded px-3 py-2">
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
                    <label for="quiz-type" class="block font-semibold mb-1">Type d’exercice (optionnel)</label>
                    <input id="quiz-type" name="type" class="w-full border rounded px-3 py-2" placeholder="QCM, vrai/faux, etc." />
                </div>
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded font-bold hover:bg-emerald-700 transition">Générer le quiz</button>
            </form>
            <div id="quiz-ia-result" class="mt-6"></div>
        </div>
    </div>
    <?php endif; ?>
    <div class="text-center mb-8">
        <h1 class="text-4xl md:text-5xl font-bold text-slate-800 mb-4 flex items-center justify-center gap-3">📝 Exercices Interactifs</h1>
        <p class="text-xl text-slate-600 mb-6">Entraîne-toi avec des exercices adaptés à ton niveau !</p>
        <?php if (empty($_SESSION['user_id'])): ?>
        <div id="mode-emploi-exercices" class="mx-auto max-w-2xl bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4 flex flex-col items-center gap-2 shadow">
            <div class="flex items-center gap-2 text-blue-700 text-lg font-semibold">
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
    <section class="my-12" id="niveau-section">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Choisis ton niveau</h2>
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
    <script>window.EXERCICE_USER_LEVEL = '<?php echo htmlspecialchars($niveau_normalise); ?>';</script>
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
    <section class="my-12" id="matiere-section">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Choisis ta matière</h2>
            <p class="text-slate-600">Retrouve tes exercices par discipline pour cibler tes révisions.</p>
        </div>
        <div id="matiere-list" class="flex flex-wrap gap-2 justify-center"></div>
    </section>

    <!-- Bloc exercice aléatoire principal -->
    <section class="my-12" id="random-exercise-section">
        <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-blue-700 mb-2">🎯 Ton exercice à faire</h2>
            <p class="text-slate-600">Un exercice aléatoire de ton niveau, ou choisis une matière ci-dessous.</p>
        </div>
        <div id="random-exercise-card" class="flex justify-center"></div>
        <div class="flex justify-center mt-4">
            <button id="btn-new-random-exercise" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition">
                🔄 Voir un autre exercice
            </button>
        </div>
        <div id="random-exercise-error" class="text-center text-red-600 mt-4 hidden"></div>
    </section>
<!-- ...existing code... -->
    <!-- Fin du bloc visiteur, pas de endif ici -->

    <?php if (!$has_access): ?>
        <!-- Panneau coach déplacé tout en bas, après toutes les infos -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8 mt-12 border border-blue-200 shadow">
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
                <a href="<?php echo function_exists('site_url') ? site_url('register') : '/public/pages/register.php'; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-lg">✨ Créer mon compte gratuit</a>
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
                try { return JSON.parse(raw); } catch (e) { throw e; }
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
    <script src="<?php echo(function_exists('asset_url') ? asset_url('assets/js/exercices.js') : '/assets/js/exercices.js'); ?>"></script>
    <script src="<?php echo(function_exists('asset_url') ? asset_url('assets/js/course_modal.js') : '/assets/js/course_modal.js'); ?>"></script>
    <!-- Exercices interactifs (nécessaires pour les vérifications) -->
    <script src="<?php echo(function_exists('asset_url') ? asset_url('assets/js/interactive-exercises.js') : '/assets/js/interactive-exercises.js'); ?>"></script>

    <!-- Coach WebM -->
    <script src="<?php echo(function_exists('asset_url') ? asset_url('assets/js/coach-webm.js') : '/assets/js/coach-webm.js'); ?>"></script>

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

    if (btnQuizIa && modalQuizIa && closeModalQuizIa) {
        btnQuizIa.addEventListener('click', function() {
            modalQuizIa.classList.remove('hidden');
            quizIaResult.innerHTML = '';

            // Détecter automatiquement le niveau de l'élève connecté
            if (window.EXERCICE_USER_LEVEL && document.getElementById('quiz-niveau')) {
                const normalizedLevel = window.EXERCICE_USER_LEVEL.toLowerCase().replace('é', 'e').replace('è', 'e');
                const niveauSelect = document.getElementById('quiz-niveau');
                const matchingOption = niveauSelect.querySelector('option[value="' + normalizedLevel + '"]');
                if (matchingOption) {
                    matchingOption.selected = true;
                    // Cacher la sélection du niveau pour l'élève (demande utilisateur)
                    const container = document.getElementById('quiz-niveau-container');
                    if (container) container.style.display = 'none';
                }
            }
        });
        closeModalQuizIa.addEventListener('click', function() {
            modalQuizIa.classList.add('hidden');
        });
        modalQuizIa.addEventListener('click', function(e) {
            if (e.target === modalQuizIa) modalQuizIa.classList.add('hidden');
        });
    }

    if (formQuizIa) {
        formQuizIa.addEventListener('submit', async function(e) {
            e.preventDefault();
            quizIaResult.innerHTML = '<div class="text-center text-slate-500">Génération du quiz en cours…</div>';
            let niveau = formQuizIa.niveau.value.trim();
            const matiere = formQuizIa.matiere.value.trim();
            let type = formQuizIa.type.value.trim();

            if (!niveau && window.EXERCICE_USER_LEVEL) {
                niveau = window.EXERCICE_USER_LEVEL.toLowerCase().replace('é', 'e').replace('è', 'e');
            }

            // Si type n'est pas renseigné, on choisit un type aléatoire par défaut
            if (!type) {
                const randomTypes = ['QCM', 'vrai/faux', 'QCU', 'texte'];
                type = randomTypes[Math.floor(Math.random() * randomTypes.length)];
                formQuizIa.type.value = type; // afficher le type choisi
            }

            if (!niveau || !matiere) {
                quizIaResult.innerHTML = '<div class="text-red-600">Merci de choisir un niveau et une matière.</div>';
                return;
            }
            // Appel API IA (placeholder)
            try {
                // Utiliser le routeur pour l'API
                const apiUrl = (window.baseUrl || '') + '/index.php?page=api/ia/generate_quiz';
                const csrfToken = window.csrfToken || '';
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ niveau, matiere, type, csrf_token: csrfToken })
                });
                const data = await response.json();
                if (data && data.quiz_html) {
                    quizIaResult.innerHTML = data.quiz_html;
                    if (typeof window.InteractiveExercises !== 'undefined') window.InteractiveExercises.initAll();

                    // Ajouter le bouton de sauvegarde (demande utilisateur)
                    if (data.questions && data.questions.length > 0) {
                        const saveBtn = document.createElement('button');
                        saveBtn.className = 'mt-4 w-full bg-blue-700 text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-800 transition flex items-center justify-center gap-2';
                        saveBtn.innerHTML = '📂 Sauvegarder dans la bibliothèque';
                        saveBtn.onclick = async () => {
                            saveBtn.disabled = true;
                            saveBtn.innerHTML = '⌛ Sauvegarde en cours...';
                            try {
                                const saveUrl = (window.baseUrl || '') + '/index.php?page=api/ia/save_generated_quiz';
                                const csrfToken = window.csrfToken || '';
                                const saveRes = await fetch(saveUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-Token': csrfToken
                                    },
                                    body: JSON.stringify({
                                        questions: data.questions,
                                        level: data.level,
                                        subject: data.subject,
                                        csrf_token: csrfToken
                                    })
                                });
                                const saveData = await saveRes.json();
                                if (saveData.success) {
                                    saveBtn.innerHTML = '✅ Quiz sauvegardé !';
                                    saveBtn.classList.replace('bg-blue-700', 'bg-green-600');
                                    alert(saveData.message);
                                } else {
                                    throw new Error(saveData.error || 'Erreur inconnue');
                                }
                            } catch (err) {
                                alert('Erreur lors de la sauvegarde : ' + err.message);
                                saveBtn.disabled = false;
                                saveBtn.innerHTML = '📂 Sauvegarder dans la bibliothèque';
                            }
                        };
                        quizIaResult.appendChild(saveBtn);
                    }
                } else if (data && data.error) {
                    quizIaResult.innerHTML = '<div><b>Erreur :</b> ' + data.error + '</div>';
                } else {
                    quizIaResult.innerHTML = '<div class="text-red-600">Aucun quiz généré. Réessaie ou change les paramètres.</div>';
                }
            } catch (err) {
                quizIaResult.innerHTML = '<div class="text-red-600">Erreur : ' + err.message + '</div>';
            }
        });
    }
})();
</script>


