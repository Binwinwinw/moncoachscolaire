<?php
$page_title = 'Quiz du Jour - MonCoachScolaire';
$page_css = 'pages/quiz.css';

// Charger les fichiers nécessaires
if (!isset($pdo)) {
    require_once dirname(__DIR__, 2) . '/config/config.php';
}
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}
require_once dirname(__DIR__, 2) . '/includes/quiz_generator.php';

// Charger le système de normalisation des niveaux pour gérer les problèmes d'encodage UTF-8
require_once dirname(__DIR__, 2) . '/includes/level_normalization.php';

// Charger admin_auth.php pour vérifier si l'utilisateur est admin
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
}

// Vérifier si l'utilisateur est connecté OU s'il est admin
$is_admin = function_exists('isAdmin') && isAdmin();
$has_access = !empty($is_logged_in) || $is_admin;
$user_level = $_SESSION['user_level'] ?? '6ème';

// Normaliser le niveau de l'utilisateur pour affichage correct (6ème et non 6??me)
$user_level_display = get_level_display_name($user_level);

// Normaliser le niveau pour l'URL
$level_normalized = normalize_level_for_url($user_level);

// Déterminer si c'est collège ou lycée
$is_college = is_college_level($user_level);
$is_lycee = is_lycee_level($user_level);

// Matières disponibles selon le niveau (ou toutes si admin), filtrées par présence de quiz JSON
$allSubjects = $is_admin
    ? ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie']
    : ($is_college
        ? ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais']
        : ['Mathématiques', 'Français', 'Sciences', 'Histoire-Géo', 'Anglais', 'Philosophie']);

// Fonction utilitaire pour normaliser
function normalize_for_quiz_json($str)
{
    return strtolower(str_replace(
        [
            'ème', 'è', 'é', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'û', 'ü', 'ç', ' '],
        ['eme', 'e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'c', ''],
        $str,
    ));
}

$subjects = [];
foreach ($allSubjects as $subj) {
    // On ne garde la matière que si getQuizQuestions retourne au moins une question
    $quizTest = getQuizQuestions($subj, $user_level, null, 1);
    if (!empty($quizTest) && is_array($quizTest) && count($quizTest) > 0) {
        $subjects[] = $subj;
    }
}

// Déterminer la matière du quiz du jour (selon le jour de la semaine ou paramètre)
$todaySubject = $_GET['matiere'] ?? $subjects[date('w') % count($subjects)];

// Récupérer les questions de quiz
$quizQuestions = [];
if ($has_access) {
    $quizQuestions = getQuizQuestions($todaySubject, $user_level, $pdo, 5);
}
?>

<main class="max-w-7xl mx-auto px-4 py-8">
    <div class="text-center mb-8">
        <h1 class="text-4xl md:text-5xl font-bold text-slate-800 mb-4 flex items-center justify-center gap-3">❓ Quiz du Jour</h1>
        <p class="text-xl text-slate-600 mb-6">Teste tes connaissances quotidiennement !</p>
        <p class="text-base text-slate-500">📅 Quiz du <?php echo date('d/m/Y'); ?> - Matière : <strong><?php echo htmlspecialchars($todaySubject); ?></strong> - Niveau : <strong><?php echo htmlspecialchars($user_level_display); ?></strong></p>
    </div>

    <?php if (!$has_access): ?>
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8 mb-8 border border-blue-200 shadow">
            <h2>🔒 Débloque l'accès aux quiz</h2>
            <p>Crée un compte gratuit pour accéder aux quiz quotidiens adaptés à ton niveau !</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
                <a href="<?php echo site_url('register'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-lg">✨ Créer mon compte</a>
                <a href="<?php echo site_url('login'); ?>" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl hover:bg-gray-700 transition-colors shadow-lg">🔑 Me connecter</a>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-6 shadow">
            <strong>👋 Salut <?php echo htmlspecialchars($user_name ?? 'Élève'); ?> !</strong><br>
            Chaque jour, un nouveau quiz t'attend pour tester tes connaissances dans différentes matières.
            Réponds correctement pour gagner des points et progresser !<br>
            <strong>📚 Les questions sont directement liées aux leçons et exercices que tu étudies en <?php echo htmlspecialchars($user_level_display); ?>.</strong>
            Elles sont générées à partir des exercices réels de ton niveau pour t'aider à réviser efficacement.
        </div>

        <!-- Quiz du jour principal -->
        <section class="my-12">
            <h2>
                <?php
                $icons = [
                    'Mathématiques' => '🧮',
                    'Français' => '📚',
                    'Sciences' => '🔬',
                    'Histoire-Géo' => '🏛️',
                    'Anglais' => '🇬🇧',
                    'Philosophie' => '🤔',
                ];
        echo $icons[$todaySubject] ?? '❓';
        ?>
                Quiz du Jour : <?php echo htmlspecialchars($todaySubject); ?>
            </h2>

            <div class="quiz-container" id="quiz-container">
                <?php if (!empty($quizQuestions)): ?>
                    <form id="quiz-form" class="quiz-questions flex flex-col gap-8">
                        <?php foreach ($quizQuestions as $qIndex => $question): ?>
                            <div class="quiz-question bg-white rounded-2xl p-6 shadow flex flex-col gap-4" data-question-id="<?php echo htmlspecialchars($question['id'] ?? $qIndex); ?>" data-correct="<?php echo htmlspecialchars($question['correct'] ?? ''); ?>">
                                <h3 class="text-lg font-bold text-slate-800">Question <?php echo $qIndex + 1; ?></h3>
                                <p class="question-text text-slate-700 mb-2"><?php echo htmlspecialchars($question['question']); ?></p>

                                <div class="quiz-choices flex flex-col gap-2">
                                    <?php foreach ($question['choices'] as $choice => $answer): ?>
                                        <label class="quiz-choice flex items-center gap-3 bg-slate-100 rounded-xl px-4 py-2 cursor-pointer transition hover:bg-blue-100">
                                            <input type="radio" name="q<?php echo $qIndex; ?>" value="<?php echo strtolower($choice); ?>" required>
                                            <span class="choice-label"><strong><?php echo $choice; ?>)</strong> <?php echo htmlspecialchars($answer); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Zone d'explication (cachée par défaut) -->
                                <div class="question-explanation bg-blue-50 border-l-4 border-blue-500 rounded-xl mt-4 p-4">
                                    <strong>💡 Explication :</strong>
                                    <p><?php echo htmlspecialchars($question['explanation'] ?? 'Pas d\'explication disponible.'); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <button type="button" class="btn-quiz-submit px-6 py-3 rounded-xl bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition" id="btn-quiz-submit">✅ Valider le quiz</button>
                        <div class="quiz-results mt-6" id="quiz-results"></div>
                    </form>
                <?php else: ?>
                    <div class="text-center py-8 text-slate-500">
                        <p>Les questions de quiz seront bientôt disponibles pour cette matière. En attendant, consulte tes cours et exercices !</p>
                        <a href="<?php echo site_url('cours'); ?>" class="inline-block px-6 py-3 rounded-xl bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition">📚 Voir mes cours</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Autres quiz disponibles -->
        <section class="my-12">
            <h2>📚 Autres Quiz Disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($subjects as $subject): ?>
                    <?php if ($subject !== $todaySubject): ?>
                        <div class="quiz-card bg-white rounded-2xl p-6 shadow flex flex-col gap-4">
                            <div class="quiz-card-header">
                                <h3 class="text-lg font-bold text-slate-800">
                                    <?php
                            $icons = [
                                'Mathématiques' => '🧮',
                                'Français' => '📚',
                                'Sciences' => '🔬',
                                'Histoire-Géo' => '🏛️',
                                'Anglais' => '🇬🇧',
                                'Philosophie' => '🤔',
                            ];
                        echo $icons[$subject] ?? '❓';
                        ?>
                                    <?php echo htmlspecialchars($subject); ?>
                                </h3>
                            </div>
                            <div class="quiz-card-body">
                                <p class="text-slate-600">Quiz de <?php echo htmlspecialchars($subject); ?> pour le niveau <?php echo htmlspecialchars($user_level_display); ?></p>
                                <a href="<?php echo site_url('quiz', ['matiere' => $subject]); ?>" class="inline-block px-6 py-3 rounded-xl bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition">Commencer le quiz</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-6 shadow">
            <strong>💡 Conseil :</strong> Fais le quiz quotidien pour maintenir tes connaissances à jour et gagner des points d'expérience ! Les questions sont adaptées à ton niveau et basées sur les cours que tu étudies.
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('btn-quiz-submit');
            const quizForm = document.getElementById('quiz-form');

            if (!submitBtn || !quizForm) return;

            submitBtn.addEventListener('click', function() {
                submitQuiz();
            });
        });

        function submitQuiz() {
            const questions = document.querySelectorAll('.quiz-question');
            let score = 0;
            let total = questions.length;
            let allAnswered = true;

            // Vérifier que toutes les questions sont répondues
            questions.forEach((question, index) => {
                const selected = question.querySelector('input[type="radio"]:checked');
                if (!selected) {
                    allAnswered = false;
                    question.style.border = '2px solid #ef4444';
                } else {
                    question.style.border = 'none';

                    // Vérifier la réponse
                    const correct = question.getAttribute('data-correct').toLowerCase();
                    const userAnswer = selected.value.toLowerCase();

                    if (userAnswer === correct) {
                        score++;
                        selected.parentElement.style.background = '#d1fae5';
                        selected.parentElement.style.borderColor = '#10b981';
                    } else {
                        selected.parentElement.style.background = '#fee2e2';
                        selected.parentElement.style.borderColor = '#ef4444';
                        // Mettre en évidence la bonne réponse
                        const correctInput = question.querySelector('input[value="' + correct + '"]');
                        if (correctInput) {
                            correctInput.parentElement.style.background = '#d1fae5';
                            correctInput.parentElement.style.borderColor = '#10b981';
                            correctInput.parentElement.style.borderWidth = '3px';
                        }
                    }

                    // Afficher l'explication
                    const explanation = question.querySelector('.question-explanation');
                    if (explanation) {
                        explanation.style.display = 'block';
                    }
                }
            });

            if (!allAnswered) {
                alert('Veuillez répondre à toutes les questions avant de valider.');
                return;
            }

            // Désactiver le bouton et les inputs
            document.getElementById('btn-quiz-submit').disabled = true;
            questions.forEach(question => {
                const inputs = question.querySelectorAll('input[type="radio"]');
                inputs.forEach(input => input.disabled = true);
            });

            // Afficher les résultats
            const resultsDiv = document.getElementById('quiz-results');
            const percentage = Math.round((score / total) * 100);

            let message = '';
            let className = '';
            let emoji = '';

            if (percentage === 100) {
                message = '🎉 Excellent ! Tu maîtrises parfaitement ce sujet !';
                className = 'quiz-success';
                emoji = '🌟';
            } else if (percentage >= 80) {
                message = '👍 Très bien joué ! Continue comme ça !';
                className = 'quiz-good';
                emoji = '✨';
            } else if (percentage >= 60) {
                message = '👍 Bien joué ! Continue tes efforts pour progresser !';
                className = 'quiz-good';
                emoji = '💪';
            } else {
                message = '💪 Ne te décourage pas ! Relis tes cours et réessaie !';
                className = 'quiz-needs-work';
                emoji = '📚';
            }

            resultsDiv.innerHTML = `
                <div class="quiz-result ${className}">
                    <h3>${emoji} Résultats du Quiz</h3>
                    <p><strong>Score : ${score}/${total} (${percentage}%)</strong></p>
                    <p>${message}</p>
                    <p><a href="<?php echo site_url('cours'); ?>" class="coach-cta secondary">📚 Revoir mes cours</a></p>
                </div>
            `;
            resultsDiv.style.display = 'block';
            resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            // Déclencher le coach selon le score
            if (typeof window.onExerciseComplete === 'function') {
              window.onExerciseComplete(percentage);
            }

            // Sauvegarder le score (optionnel, via AJAX)
            // saveQuizScore(score, total, '<?php echo $todaySubject; ?>');
        }
    </script>

    <!-- Coach WebM -->
    <script src="<?php
        if (function_exists('asset_url')) {
            echo asset_url('assets/js/coach-webm.js');
        } else {
            $assetBase = function_exists('detectBaseUrl') ? rtrim(detectBaseUrl(), '/') : '';
            echo htmlspecialchars($assetBase . '/assets/js/coach-webm.js', ENT_QUOTES);
        }
    ?>"></script>
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
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
      }
      @media (max-width: 480px) {
        .coach-overlay { width: 180px; bottom: 10px; right: 10px; }
      }
    </style>
</main>

