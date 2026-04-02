<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
require_once '../../includes/ai_course_generator.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exerciseIds = json_decode($_POST['exercise_ids'], true);
    $provider = $_POST['ai_provider'] ?? 'perplexity';
    $result = generateCourseFromExercises($exerciseIds, $provider);
    echo json_encode($result);
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
        <label>Sélectionner les exercices :</label>
        <select name="exercise_ids[]" multiple size="10">
            <?php
            // Charger tous les exercices
            $stmt = $pdo->query("SELECT Id, Title, Subject, Level FROM exercises ORDER BY Subject, Level");
while ($ex = $stmt->fetch()):
    ?>
                <option value="<?= $ex['Id'] ?>">
                    [<?= $ex['Subject'] ?>] <?= $ex['Title'] ?> (<?= $ex['Level'] ?>)
                </option>
            <?php endwhile; ?>
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
