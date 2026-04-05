<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
$page_title = 'Diagnostic - MonCoachScolaire';
$page_css = 'diagnostic.css';
// Charger site_boot.php
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}
// Vérifier connexion utilisateur
$session_status = function_exists('session_status') ? session_status() : PHP_SESSION_NONE;
if ($session_status !== PHP_SESSION_ACTIVE) {
    session_start();
}
$is_logged_in = !empty($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
if (!$is_logged_in) {
    safe_redirect(site_url('login') . '?redirect=admin/diagnostic');
}
// Charger et vérifier admin
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
    // Si l'utilisateur connecté n'est pas admin, rediriger
    if (!function_exists('isAdmin') || !isAdmin()) {
        safe_redirect(site_url('eleve/dashboard'));
    }
}
// Charger connexion BD
if (is_file(dirname(__DIR__, 2) . '/database/connection.php')) {
    require_once dirname(__DIR__, 2) . '/database/connection.php';
}
// Récupérer niveau/matière depuis URL
$selected_level = $_GET['level'] ?? null;
$selected_subject = $_GET['subject'] ?? null;
$notion_id = $_GET['notion_id'] ?? null;
// Récupérer les notions disponibles
$notions = [];
if ($selected_level && $selected_subject && $pdo) {
    $stmt = $pdo->prepare("SELECT id, name, description, difficulty FROM notion WHERE level = ? AND subject = ? ORDER BY order_index, name");
    $stmt->execute([$selected_level, $selected_subject]);
    $notions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Si notion sélectionnée, charger les exercices pour le quiz
$quiz_exercises = [];
if ($notion_id && $pdo) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT e.Id, e.Title, e.Content, e.Answer, e.Difficulty
        FROM exercises e
        JOIN exercisenotion en ON e.Id = en.exercise_id
        WHERE en.notion_id = ? AND e.is_active = 1
        ORDER BY RAND()
        LIMIT 5
    ");
    $stmt->execute([$notion_id]);
    $quiz_exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<main class="diagnostic-container">
    <div class="diagnostic-header">
        <h1>🎯 Diagnostic de Compétences</h1>
        <p class="subtitle">Évalue ton niveau et reçois des recommandations personnalisées</p>
    </div>

    <?php if (!$selected_level || !$selected_subject): ?>
        <!-- Étape 1: Sélection niveau et matière -->
        <div class="selection-step">
            <h2>Choisir ton niveau et ta matière</h2>

            <form method="GET" action="<?php echo site_url('admin/diagnostic'); ?>" class="diagnostic-form">
                <div class="form-group">
                    <label for="level">Niveau scolaire</label>
                    <select name="level" id="level" required>
                        <option value="">-- Sélectionne ton niveau --</option>
                        <optgroup label="Collège">
                            <option value="6eme">6ème</option>
                            <option value="5eme">5ème</option>
                            <option value="4eme">4ème</option>
                            <option value="3eme">3ème</option>
                        </optgroup>
                        <optgroup label="Lycée">
                            <option value="2nde">Seconde</option>
                            <option value="1ere">Première</option>
                            <option value="Terminale">Terminale</option>
                        </optgroup>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject">Matière</label>
                    <select name="subject" id="subject" required>
                        <option value="">-- Sélectionne une matière --</option>
                        <option value="Mathematiques">Mathématiques</option>
                        <option value="Francais">Français</option>
                        <option value="Anglais">Anglais</option>
                        <option value="Histoire-Geographie">Histoire-Géographie</option>
                        <option value="SVT">SVT</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Continuer →</button>
            </form>
        </div>

    <?php elseif (!$notion_id && !empty($notions)): ?>
        <!-- Étape 2: Sélection notion -->
        <div class="notion-selection">
            <h2>Quelle notion veux-tu évaluer ?</h2>
            <p class="info-text">Niveau: <strong><?php echo htmlspecialchars($selected_level); ?></strong> | Matière: <strong><?php echo htmlspecialchars($selected_subject); ?></strong></p>

            <div class="notions-grid">
                <?php foreach ($notions as $notion): ?>
                    <a href="?level=<?php echo urlencode($selected_level); ?>&subject=<?php echo urlencode($selected_subject); ?>&notion_id=<?php echo $notion['id']; ?>" class="notion-card">
                        <h3><?php echo htmlspecialchars($notion['name']); ?></h3>
                        <?php if ($notion['description']): ?>
                            <p><?php echo htmlspecialchars($notion['description']); ?></p>
                        <?php endif; ?>
                        <div class="notion-meta">
                            <span class="difficulty difficulty-<?php echo $notion['difficulty']; ?>">
                                <?php echo str_repeat('⭐', $notion['difficulty']); ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <a href="?level=<?php echo urlencode($selected_level); ?>" class="btn-secondary">← Changer de matière</a>
        </div>

    <?php elseif ($notion_id && !empty($quiz_exercises)): ?>
        <!-- Étape 3: Quiz diagnostic -->
        <div class="quiz-container" id="quizContainer">
            <div class="quiz-header">
                <h2>Quiz Diagnostic</h2>
                <div class="quiz-progress">
                    <span id="currentQuestion">1</span> / <span id="totalQuestions"><?php echo count($quiz_exercises); ?></span>
                </div>
            </div>

            <form id="diagnosticForm" data-notion-id="<?php echo $notion_id; ?>">
                <?php foreach ($quiz_exercises as $index => $exercise): ?>
                    <div class="question-slide" data-question="<?php echo $index + 1; ?>">
                        <div class="question-content">
                            <h3>Question <?php echo $index + 1; ?></h3>
                            <div class="exercise-title"><?php echo htmlspecialchars($exercise['Title']); ?></div>
                            <div class="exercise-content">
                                <?php echo $exercise['Content']; ?>
                            </div>
                        </div>

                        <div class="answer-input">
                            <label for="answer_<?php echo $exercise['Id']; ?>">Ta réponse :</label>
                            <textarea
                                id="answer_<?php echo $exercise['Id']; ?>"
                                name="answers[<?php echo $exercise['Id']; ?>]"
                                rows="4"
                                placeholder="Écris ta réponse ici..."
                                required
                            ></textarea>
                            <input type="hidden" name="exercise_ids[]" value="<?php echo $exercise['Id']; ?>">
                            <input type="hidden" name="correct_answers[<?php echo $exercise['Id']; ?>]" value="<?php echo htmlspecialchars($exercise['Answer']); ?>">
                        </div>

                        <div class="question-navigation">
                            <?php if ($index > 0): ?>
                                <button type="button" class="btn-secondary prev-question" data-prev="<?php echo $index; ?>">← Précédent</button>
                            <?php endif; ?>

                            <?php if ($index < count($quiz_exercises) - 1): ?>
                                <button type="button" class="btn-primary next-question" data-next="<?php echo $index + 2; ?>">Suivant →</button>
                            <?php else: ?>
                                <button type="submit" class="btn-success">Terminer le Quiz ✓</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>

            <div id="quizResults">
                <div class="results-header">
                    <h2>📊 Résultats de ton diagnostic</h2>
                </div>
                <div id="resultsContent"></div>
            </div>
        </div>

    <?php elseif ($notion_id && empty($quiz_exercises)): ?>
        <!-- Pas d'exercices disponibles -->
        <div class="error-message">
            <h2>⚠️ Aucun exercice disponible</h2>
            <p>Il n'y a pas encore d'exercices pour cette notion. Essaie une autre notion ou reviens plus tard.</p>
            <a href="?level=<?php echo urlencode($selected_level); ?>&subject=<?php echo urlencode($selected_subject); ?>" class="btn-primary">← Retour aux notions</a>
        </div>

    <?php else: ?>
        <!-- Pas de notions disponibles -->
        <div class="error-message">
            <h2>⚠️ Aucune notion disponible</h2>
            <p>Il n'y a pas de notions configurées pour ce niveau/matière.</p>
            <a href="?" class="btn-primary">← Recommencer</a>
        </div>
    <?php endif; ?>
</main>

<script>
// Navigation quiz
document.querySelectorAll('.next-question').forEach(btn => {
    btn.addEventListener('click', function() {
        const nextQ = this.dataset.next;
        document.querySelectorAll('.question-slide').forEach(slide => slide.style.display = 'none');
        document.querySelector(`.question-slide[data-question="${nextQ}"]`).style.display = 'block';
        document.getElementById('currentQuestion').textContent = nextQ;
        window.scrollTo(0, 0);
    });
});

document.querySelectorAll('.prev-question').forEach(btn => {
    btn.addEventListener('click', function() {
        const prevQ = this.dataset.prev;
        document.querySelectorAll('.question-slide').forEach(slide => slide.style.display = 'none');
        document.querySelector(`.question-slide[data-question="${prevQ}"]`).style.display = 'block';
        document.getElementById('currentQuestion').textContent = prevQ;
        window.scrollTo(0, 0);
    });
});

// Soumission quiz
const form = document.getElementById('diagnosticForm');
if (form) {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const notionId = this.dataset.notionId;

        // Préparer données pour API
        const quizData = {
            notion_id: notionId,
            answers: {},
            exercise_ids: []
        };

        formData.getAll('exercise_ids[]').forEach(id => {
            quizData.exercise_ids.push(id);
            quizData.answers[id] = formData.get(`answers[${id}]`);
        });

        try {
            const response = await fetch('<?php echo site_url('api/submit_diagnostic'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(quizData)
            });

            const result = await response.json();

            if (result.success) {
                displayResults(result);
            } else {
                alert('Erreur: ' + (result.error || 'Erreur inconnue'));
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur de connexion au serveur');
        }
    });
}

function displayResults(result) {
    document.getElementById('quizContainer').querySelector('form').style.display = 'none';
    const resultsDiv = document.getElementById('quizResults');
    const contentDiv = document.getElementById('resultsContent');

    const score = result.score || 0;
    const passed = score >= 70;

    let html = `
        <div class="score-display ${passed ? 'passed' : 'failed'}">
            <div class="score-circle">${score}%</div>
            <h3>${passed ? '✅ Bon travail !' : '💪 Continue tes efforts !'}</h3>
        </div>

        <div class="recommendations">
            <h3>📌 Recommandations</h3>
            ${result.recommendations || '<p>Continue de pratiquer pour améliorer tes compétences !</p>'}
        </div>

        <div class="actions">
            <a href="<?php echo site_url('exercices'); ?>" class="btn-primary">Pratiquer avec des exercices</a>
            <a href="?" class="btn-secondary">Nouveau diagnostic</a>
        </div>
    `;

    contentDiv.innerHTML = html;
    resultsDiv.style.display = 'block';
    window.scrollTo(0, 0);
}
</script>

<style>
.diagnostic-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.diagnostic-header {
    text-align: center;
    margin-bottom: 3rem;
}

.diagnostic-header h1 {
    font-size: 2.5rem;
    color: #1e40af;
    margin-bottom: 0.5rem;
}

.subtitle {
    color: #64748b;
    font-size: 1.1rem;
}

.diagnostic-form {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #334155;
}

.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
}

.notions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.notion-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s, box-shadow 0.2s;
}

.notion-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.notion-card h3 {
    color: #1e40af;
    margin-bottom: 0.5rem;
}

.quiz-container {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.quiz-progress {
    font-size: 1.2rem;
    font-weight: 600;
    color: #3b82f6;
}

.question-slide {
    margin: 2rem 0;
}

.exercise-content {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 1rem 0;
}

.answer-input textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    font-family: inherit;
}

.question-navigation {
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    margin-top: 2rem;
}

.btn-primary, .btn-secondary, .btn-success {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-secondary {
    background: #64748b;
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.score-display {
    text-align: center;
    padding: 2rem;
    margin: 2rem 0;
}

.score-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: #10b981;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
    margin: 0 auto 1rem;
}

.score-display.failed .score-circle {
    background: #ef4444;
}

.recommendations {
    background: #f0f9ff;
    padding: 1.5rem;
    border-radius: 8px;
    margin: 2rem 0;
}

.actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}
</style>

