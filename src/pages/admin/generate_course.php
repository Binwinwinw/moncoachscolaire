<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/database/connection.php';
require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
require_once dirname(__DIR__, 2) . '/includes/login_security.php';
require_once dirname(__DIR__, 2) . '/includes/ai_course_generator.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

requireAdmin();

$csrfToken = function_exists('generateCSRFToken')
    ? generateCSRFToken()
    : ((string) ($_SESSION['csrf_token'] ?? ''));

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $submittedToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? ''));
    if (!verifyCSRFToken($submittedToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'CSRF invalide'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $exerciseIdsRaw = (string) ($_POST['exercise_ids'] ?? '[]');
    $exerciseIds = json_decode($exerciseIdsRaw, true);
    if (!is_array($exerciseIds)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'exercise_ids invalide'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $exerciseIds = array_values(array_filter(array_map('intval', $exerciseIds), static function ($id) {
        return $id > 0;
    }));

    if (count($exerciseIds) === 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Aucun exercice sélectionné'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $provider = trim((string) ($_POST['ai_provider'] ?? 'perplexity'));
    $allowedProviders = ['perplexity', 'openai', 'claude', 'groq', 'ollama', 'gemini'];
    if (!in_array($provider, $allowedProviders, true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Provider IA invalide'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = generateCourseFromExercises($exerciseIds, $provider);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Générer un cours avec l'IA</title>
</head>
<body>
    <h1>🤖 Génération automatique de cours</h1>

    <form id="generate-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
        <label>Sélectionner les exercices :</label>
        <select name="exercise_ids[]" multiple size="10">
            <?php
            // Charger tous les exercices
            $exercises = [];
            if (isset($pdo) && $pdo instanceof PDO) {
                $stmt = $pdo->query("SELECT Id, Title, Subject, Level FROM exercises ORDER BY Subject, Level");
                if ($stmt) {
                    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
foreach ($exercises as $ex):
    ?>
                <option value="<?= $ex['Id'] ?>">
                    [<?= $ex['Subject'] ?>] <?= $ex['Title'] ?> (<?= $ex['Level'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label>Provider IA :</label>
        <select name="ai_provider">
            <option value="perplexity">Perplexity</option>
            <option value="openai">OpenAI</option>
            <option value="claude">Claude</option>
        </select>

        <button type="submit">🚀 Générer le cours</button>
    </form>

    <div id="result"></div>

    <script>
    document.getElementById('generate-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        formData.set('exercise_ids', JSON.stringify([...formData.getAll('exercise_ids[]')]));

        const response = await fetch('', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': formData.get('csrf_token') || ''
            },
            body: formData
        });

        const result = await response.json();
        document.getElementById('result').innerHTML = `
            <h2>✅ Cours généré !</h2>
            <p>ID: ${result.course_id}</p>
            <pre>${JSON.stringify(result.course_data, null, 2)}</pre>
        `;
    });
    </script>
</body>
</html>
