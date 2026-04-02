<?php

/**
 * API: Notion Progress
 * Endpoint pour récupérer la progression de l'utilisateur par notion
 *
 * GET /api/notion_progress?level=6eme&subject=Mathematiques
 *
 * Returns: {
 *   success: bool,
 *   notions: [{
 *     id, name, description, difficulty,
 *     exercises_total, exercises_completed, exercises_mastered,
 *     avg_score, best_score,
 *     attempts, total_time_seconds,
 *     last_activity, status, recommended
 *   }]
 * }
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';
require_once __DIR__ . '/../database/connection.php';

api_require([
    'method' => 'GET',
    'auth' => true,
]);

$user_id = $_SESSION['user_id'];
$level = $_GET['level'] ?? null;
$subject = $_GET['subject'] ?? null;

try {
    // Construire requête avec filtres optionnels
    $where_clauses = ['1=1'];
    $params = [$user_id];

    if ($level) {
        $where_clauses[] = 'n.level = ?';
        $params[] = $level;
    }

    if ($subject) {
        $where_clauses[] = 'n.subject = ?';
        $params[] = $subject;
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Récupérer toutes les notions avec stats
    $stmt = $pdo->prepare("
        SELECT
            n.id,
            n.name,
            n.description,
            n.difficulty,
            n.level,
            n.subject,
            COUNT(DISTINCT en.exercise_id) as exercises_total,
            COUNT(DISTINCT CASE WHEN m.user_id = ? THEN m.exercise_id END) as exercises_attempted,
            COUNT(DISTINCT CASE WHEN m.user_id = ? AND m.best_score >= 80 THEN m.exercise_id END) as exercises_mastered,
            AVG(CASE WHEN m.user_id = ? THEN m.best_score END) as avg_score,
            MAX(CASE WHEN m.user_id = ? THEN m.best_score END) as best_score,
            SUM(CASE WHEN m.user_id = ? THEN m.attempts END) as total_attempts,
            SUM(CASE WHEN m.user_id = ? THEN m.total_time_seconds END) as total_time_seconds,
            MAX(CASE WHEN m.user_id = ? THEN m.last_attempt_at END) as last_activity
        FROM notion n
        LEFT JOIN exercisenotion en ON n.id = en.notion_id
        LEFT JOIN mastery m ON en.exercise_id = m.exercise_id
        WHERE $where_sql
        GROUP BY n.id
        ORDER BY n.order_index, n.name
    ");

    $stmt->execute(array_merge(
        array_fill(0, 7, $user_id), // Pour les 7 CASE WHEN m.user_id = ?
        $params,                      // Pour les WHERE clauses
    ));

    $notions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enrichir avec statut et recommandation
    foreach ($notions as &$notion) {
        $exercises_total = (int) $notion['exercises_total'];
        $exercises_attempted = (int) $notion['exercises_attempted'];
        $exercises_mastered = (int) $notion['exercises_mastered'];
        $avg_score = $notion['avg_score'] ? round($notion['avg_score'], 1) : null;

        // Déterminer statut
        if ($exercises_attempted === 0) {
            $status = 'not_started';
            $status_label = 'À commencer';
        } elseif ($avg_score >= 80 && $exercises_mastered >= ceil($exercises_total * 0.7)) {
            $status = 'mastered';
            $status_label = 'Maîtrisée';
        } else {
            $status = 'in_progress';
            $status_label = 'En cours';
        }

        // Déterminer si recommandé
        $recommended = false;
        if ($status === 'not_started' && $exercises_total >= 5) {
            $recommended = true;
        } elseif ($status === 'in_progress' && $avg_score < 70) {
            $recommended = true;
        }

        // Calculer progression (% exercices maîtrisés)
        $progress_percent = ($exercises_total > 0)
            ? round(($exercises_mastered / $exercises_total) * 100, 1)
            : 0;

        $notion['exercises_attempted'] = $exercises_attempted;
        $notion['exercises_mastered'] = $exercises_mastered;
        $notion['avg_score'] = $avg_score;
        $notion['best_score'] = $notion['best_score'] ? round($notion['best_score'], 1) : null;
        $notion['total_attempts'] = (int) ($notion['total_attempts'] ?? 0);
        $notion['total_time_seconds'] = (int) ($notion['total_time_seconds'] ?? 0);
        $notion['status'] = $status;
        $notion['status_label'] = $status_label;
        $notion['recommended'] = $recommended;
        $notion['progress_percent'] = $progress_percent;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'filters' => [
            'level' => $level,
            'subject' => $subject,
        ],
        'notions' => $notions,
    ]);

} catch (PDOException $e) {
    error_log("Erreur notion_progress: " . $e->getMessage());
    json_error('Erreur serveur', 500, 'ERR_DB');
}
