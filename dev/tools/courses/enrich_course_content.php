<?php
/**
 * dev/tools/courses/enrich_course_content.php
 *
 * Enrichit la colonne 'example' des cours avec des exercices liés.
 */

if (php_sapi_name() !== 'cli') {
    die('Ce script doit être exécuté en ligne de commande.');
}

$rootDir = dirname(dirname(dirname(__DIR__)));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

echo "🚀 Enrichissement de la colonne 'example' des cours\n";
echo "=================================================\n\n";

try {
    // 1. Récupérer les cours actifs
    $stmt = $pdo->query("SELECT id, competence, example, subject, level FROM courses WHERE is_active = 1");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "INFO : " . count($courses) . " cours trouvés.\n\n";

    $updatedCount = 0;
    $skippedCount = 0;
    $noExerciseCount = 0;

    foreach ($courses as $course) {
        $courseId = $course['id'];

        // Si le champ example n'est pas vide (ou contient déjà du HTML significatif), on peut choisir d'ignorer ou d'écraser.
        // Ici, on ignore si > 10 chars (pour éviter d'écraser du contenu manuel).
        if (!empty($course['example']) && strlen(trim($course['example'])) > 10) {
            $skippedCount++;
            continue;
        }

        // 2. Récupérer exercices liés
        $exStmt = $pdo->prepare("
            SELECT e.Title, e.Content, e.Answer, e.Tips
            FROM exercises e
            JOIN exercisecourselinks l ON e.id = l.ExerciseId
            WHERE l.CourseId = :courseId
              AND e.is_active = 1
            LIMIT 2
        ");
        $exStmt->execute([':courseId' => $courseId]);
        $exercises = $exStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($exercises)) {
            $noExerciseCount++;
            continue;
        }

        // 3. Générer le HTML
        $exampleHtml = "";

        foreach ($exercises as $ex) {
            $rawAnswer = $ex['Answer'];
            $decoded = json_decode($rawAnswer, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $displayAnswer = implode(', ', $decoded);
            } else {
                $displayAnswer = $rawAnswer;
            }

            $content = strip_tags($ex['Content'], '<p><br><strong><em><ul><li>');
            $title = htmlspecialchars($ex['Title']);
            $tips = !empty($ex['Tips']) ? htmlspecialchars($ex['Tips']) : null;

            $exampleHtml .= "<div class=\"example-card mb-4 p-4 border rounded bg-white shadow-sm\">\n";
            $exampleHtml .= "  <h5 class=\"font-bold text-lg mb-2\">📝 $title</h5>\n";
            $exampleHtml .= "  <div class=\"example-body mb-2\">$content</div>\n";
            $exampleHtml .= "  <details class=\"example-solution mt-2\">\n";
            $exampleHtml .= "    <summary class=\"cursor-pointer text-blue-600 hover:text-blue-800\">Voir la correction</summary>\n";
            $exampleHtml .= "    <div class=\"solution-content mt-2 p-2 bg-gray-50 rounded\">\n";
            $exampleHtml .= "      <p><strong>✅ Réponse :</strong> $displayAnswer</p>\n";
            if ($tips) {
                $exampleHtml .= "      <p class=\"text-sm text-gray-600 mt-1\">💡 <em>Astuce : $tips</em></p>\n";
            }
            $exampleHtml .= "    </div>\n";
            $exampleHtml .= "  </details>\n";
            $exampleHtml .= "</div>\n";
        }

        // 4. Update
        $updateStmt = $pdo->prepare("UPDATE courses SET example = :ex WHERE id = :id");
        $updateStmt->execute([
            ':ex' => $exampleHtml,
            ':id' => $courseId
        ]);

        echo "✅ Cours #{$courseId} ({$course['subject']} - {$course['competence']}) enrichi.\n";
        $updatedCount++;
    }

    echo "\n📊 BILAN\n";
    echo "========\n";
    echo "✅ Cours enrichis : $updatedCount\n";
    echo "⏭️  Ignorés (déjà remplis) : $skippedCount\n";
    echo "⚠️  Sans exercices : $noExerciseCount\n";

} catch (PDOException $e) {
    echo "❌ Erreur SQL : " . $e->getMessage() . "\n";
}
