<?php

/**
 * API: Submit Diagnostic Quiz
 * Endpoint pour soumettre les réponses d'un quiz diagnostic
 *
 * POST /api/submit_diagnostic
 * Body: {
 *   notion_id: int,
 *   answers: {exercise_id: user_answer},
 *   exercise_ids: [int]
 * }
 *
 * Returns: {
 *   success: bool,
 *   score: float,
 *   passed: bool,
 *   quiz_id: int,
 *   result_id: int,
 *   recommendations: string
 * }
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

api_require([
    'method' => 'POST',
    'auth' => true,
    'csrf' => true,
]);

// Charger configuration
if (is_file(__DIR__ . '/../../config/site_boot.php')) {
    require_once __DIR__ . '/../../config/site_boot.php';
}

// Charger connexion BD
if (is_file(__DIR__ . '/../../database/connection.php')) {
    require_once __DIR__ . '/../../database/connection.php';
}

// Vérifier session
$user_id = $_SESSION['user_id'];

// Lire données POST
$input = api_get_json_body(true);

if (!$input || !isset($input['notion_id']) || !isset($input['answers'])) {
    json_error('Données manquantes', 400, 'ERR_VALIDATION');
}

$notion_id = (int) $input['notion_id'];
$answers = $input['answers'];
$exercise_ids = $input['exercise_ids'] ?? array_keys($answers);

try {
    $pdo->beginTransaction();

    // 1. Récupérer les exercices et leurs réponses correctes
    $placeholders = implode(',', array_fill(0, count($exercise_ids), '?'));
    $stmt = $pdo->prepare("SELECT Id, Answer, Difficulty FROM exercises WHERE Id IN ($placeholders)");
    $stmt->execute($exercise_ids);
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $exercise_map = [];
    foreach ($exercises as $ex) {
        $exercise_map[$ex['Id']] = $ex;
    }

    // 2. Calculer le score
    $total_questions = count($exercise_ids);
    $correct_count = 0;
    $detailed_answers = [];
    $time_start = time() - 300; // Simuler 5 minutes par défaut

    foreach ($exercise_ids as $ex_id) {
        $user_answer = trim($answers[$ex_id] ?? '');
        $correct_answer = $exercise_map[$ex_id]['Answer'] ?? '';

        // Comparaison simple (normaliser pour éviter casse/espaces)
        $is_correct = strcasecmp(trim($user_answer), trim($correct_answer)) === 0;

        if ($is_correct) {
            $correct_count++;
        }

        $detailed_answers[] = [
            'exercise_id' => $ex_id,
            'user_answer' => $user_answer,
            'correct_answer' => $correct_answer,
            'is_correct' => $is_correct,
            'difficulty' => $exercise_map[$ex_id]['Difficulty'] ?? 1,
        ];
    }

    $score = ($total_questions > 0) ? round(($correct_count / $total_questions) * 100, 2) : 0;
    $passed = $score >= 70;
    $time_spent_seconds = time() - $time_start;

    // 3. Récupérer infos notion
    $stmt = $pdo->prepare("SELECT level, subject, name FROM notion WHERE id = ?");
    $stmt->execute([$notion_id]);
    $notion = $stmt->fetchOne(PDO::FETCH_ASSOC);

    // 4. Créer le quiz diagnostic
    $quiz_title = "Diagnostic: " . ($notion['name'] ?? 'Notion #' . $notion_id);
    $stmt = $pdo->prepare("
        INSERT INTO quiz (level, subject, notion_id, type, title, question_count, passing_score, created_at)
        VALUES (?, ?, ?, 'diagnostic', ?, ?, 70, NOW())
    ");
    $stmt->execute([
        $notion['level'] ?? '',
        $notion['subject'] ?? '',
        $notion_id,
        $quiz_title,
        $total_questions,
    ]);
    $quiz_id = $pdo->lastInsertId();

    // 5. Sauvegarder les résultats
    $stmt = $pdo->prepare("
        INSERT INTO quizresult (user_id, quiz_id, score, passed, time_spent_seconds, answers, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $user_id,
        $quiz_id,
        $score,
        $passed ? 1 : 0,
        $time_spent_seconds,
        json_encode($detailed_answers),
    ]);
    $result_id = $pdo->lastInsertId();

    // 6. Générer recommandations
    $recommendations = generateRecommendations($pdo, $user_id, $notion_id, $score, $detailed_answers);

    $pdo->commit();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'score' => $score,
        'passed' => $passed,
        'correct_count' => $correct_count,
        'total_questions' => $total_questions,
        'quiz_id' => $quiz_id,
        'result_id' => $result_id,
        'recommendations' => $recommendations,
        'time_spent' => $time_spent_seconds,
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erreur submit_diagnostic: " . $e->getMessage());
    json_error('Erreur serveur', 500, 'ERR_DB');
}

if (!function_exists('clean_utf8_recursive')) {
    function clean_utf8_recursive($data)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = clean_utf8_recursive($v);
            }
            return $data;
        } elseif (is_string($data)) {
            if (!mb_check_encoding($data, 'UTF-8')) {
                return mb_convert_encoding($data, 'UTF-8', 'auto');
            }
            return $data;
        } else {
            return $data;
        }
    }
}
$detailed_answers = clean_utf8_recursive($detailed_answers);
/**
 * Générer des recommandations personnalisées
 */
function generateRecommendations($pdo, $user_id, $notion_id, $score, $detailed_answers)
{
    $html = '<ul>';

    // Analyser les difficultés
    $wrong_difficulties = [];
    foreach ($detailed_answers as $answer) {
        if (!$answer['is_correct']) {
            $diff = $answer['difficulty'];
            $wrong_difficulties[$diff] = ($wrong_difficulties[$diff] ?? 0) + 1;
        }
    }

    // Recommandations basées sur le score
    if ($score < 40) {
        $html .= '<li><strong>📚 Revoir les bases</strong>: Ton score indique qu\'il faut consolider les fondamentaux de cette notion.</li>';
        $html .= '<li>💡 Commence par des exercices faciles (niveau 1-2) pour bien comprendre.</li>';

        // Suggérer cours
        $stmt = $pdo->prepare("SELECT id, titre FROM courses WHERE notion_id = ? LIMIT 1");
        $stmt->execute([$notion_id]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($course) {
            $html .= '<li>🎓 Consulte le cours: <a href="' . site_url('course/' . $course['id']) . '">' . htmlspecialchars($course['titre']) . '</a></li>';
        }

    } elseif ($score < 70) {
        $html .= '<li><strong>💪 Continue de pratiquer</strong>: Tu as compris les bases, mais il faut renforcer.</li>';
        $html .= '<li>🎯 Fais 10-15 exercices supplémentaires pour atteindre la maîtrise.</li>';

    } elseif ($score < 90) {
        $html .= '<li><strong>✅ Bon niveau</strong>: Tu maîtrises bien cette notion !</li>';
        $html .= '<li>🚀 Essaie des exercices plus difficiles (niveau 3-4) pour te perfectionner.</li>';

    } else {
        $html .= '<li><strong>🏆 Excellent</strong>: Tu maîtrises parfaitement cette notion !</li>';
        $html .= '<li>➡️ Passe à la notion suivante ou aide tes camarades.</li>';
    }

    // Suggérer exercices spécifiques
    if ($score < 90) {
        $recommended_difficulty = ($score < 60) ? 1 : (($score < 80) ? 2 : 3);

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM exercises e
            JOIN exercisenotion en ON e.Id = en.exercise_id
            WHERE en.notion_id = ? AND e.Difficulty = ? AND e.is_active = 1
        ");
        $stmt->execute([$notion_id, $recommended_difficulty]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $html .= '<li>📝 <strong>' . $count . ' exercices</strong> de niveau ' . $recommended_difficulty . ' sont disponibles pour cette notion.</li>';
        }
    }

    // Analyser mastery actuelle
    $stmt = $pdo->prepare("
        SELECT AVG(best_score) as avg_score, COUNT(*) as attempts
        FROM mastery
        WHERE user_id = ? AND exercise_id IN (
            SELECT exercise_id FROM exercisenotion WHERE notion_id = ?
        )
    ");
    $stmt->execute([$user_id, $notion_id]);
    $mastery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mastery && $mastery['attempts'] > 0) {
        $avg = round($mastery['avg_score'], 1);
        $html .= '<li>📊 Ta moyenne globale sur cette notion: <strong>' . $avg . '%</strong> (sur ' . $mastery['attempts'] . ' exercices)</li>';
    }

    $html .= '</ul>';

    return $html;
}
