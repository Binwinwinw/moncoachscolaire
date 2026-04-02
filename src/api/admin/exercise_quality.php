<?php

// Endpoint : /api/admin/exercise_quality.php
// Réponse JSON
require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../../includes/exercice_loader.php';

if (!isset($pdo) || !$pdo) {
    json_error('Base de données non disponible', 503, 'ERR_DB');
}

api_require([
    'method' => 'GET',
    'auth' => true,
    'roles' => ['admin'],
    'rate' => ['key' => 'admin_exercise_quality_get', 'limit' => 60, 'window' => 60],
]);

// Fusion des endpoints qualité exercices
$type = isset($_GET['type']) ? $_GET['type'] : 'summary';
$type = validate_enum($type, 'type', ['summary', 'list', 'by_level_subject', 'placeholders']);

try {
    if ($type === 'summary') {
        // Statistiques globales sur la qualité des exercices
        $stmt = $pdo->query('SELECT COUNT(*) as total, SUM(CASE WHEN Coherence=1 THEN 1 ELSE 0 END) as coherents, SUM(CASE WHEN Difficulty="facile" THEN 1 ELSE 0 END) as faciles, SUM(CASE WHEN Difficulty="difficile" THEN 1 ELSE 0 END) as difficiles FROM exercises');
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        json_response($stats);
    }
    if ($type === 'list') {
        // Liste des exercices avec indicateurs qualité
        $stmt = $pdo->query('SELECT Identifier, Title, Coherence, Difficulty, XP_Points FROM exercises ORDER BY Identifier DESC LIMIT 100');
        $exos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_response($exos);
    }
    if ($type === 'by_level_subject') {
        // Statistiques par niveau/matière (logique complète reproduite depuis exercises_quality_live.php)
        $levelsOrder = ['6ème','5ème','4ème','3ème','Seconde','Première','Terminale'];
        $levelsPlaceholder = implode(',', array_fill(0, count($levelsOrder), '?'));

        $sql = "SELECT Level, Subject,
                        COUNT(*) AS total,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_count,
                        SUM(CASE WHEN Answer IS NULL OR TRIM(Answer) = '' THEN 1 ELSE 0 END) AS empty_answers,
                        SUM(CASE WHEN LENGTH(Answer) < 30 THEN 1 ELSE 0 END) AS short_answers,
                        MIN(LENGTH(Answer)) AS min_answer,
                        AVG(LENGTH(Answer)) AS avg_answer,
                        MAX(LENGTH(Answer)) AS max_answer
                FROM exercises
                GROUP BY Level, Subject
                ORDER BY FIELD(Level, $levelsPlaceholder), Subject ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($levelsOrder);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byLevelSubject = [];
        $levels = [];
        $subjects = [];

        foreach ($rows as $row) {
            $level = ($row['Level'] === 'Bac' ? 'BAC' : $row['Level']);
            $subject = $row['Subject'];
            $levels[$level] = true;
            $subjects[$subject] = true;

            $byLevelSubject[] = [
                'level' => $level,
                'subject' => $subject,
                'total' => (int) $row['total'],
                'active' => (int) $row['active_count'],
                'empty_answers' => (int) $row['empty_answers'],
                'short_answers' => (int) $row['short_answers'],
                'min_answer' => isset($row['min_answer']) ? (int) $row['min_answer'] : 0,
                'avg_answer' => isset($row['avg_answer']) ? round($row['avg_answer'], 0) : 0,
                'max_answer' => isset($row['max_answer']) ? (int) $row['max_answer'] : 0,
            ];
        }

        // Totaux globaux
        $totals = [
            'total' => 0,
            'active' => 0,
            'empty_answers' => 0,
            'short_answers' => 0,
            'min_answer' => null,
            'avg_answer' => 0,
            'max_answer' => 0,
        ];

        foreach ($byLevelSubject as $item) {
            $totals['total'] += $item['total'];
            $totals['active'] += $item['active'];
            $totals['empty_answers'] += $item['empty_answers'];
            $totals['short_answers'] += $item['short_answers'];

            if ($item['min_answer'] !== null) {
                if ($totals['min_answer'] === null || ($item['min_answer'] > 0 && $item['min_answer'] < $totals['min_answer'])) {
                    $totals['min_answer'] = $item['min_answer'];
                }
            }
            if ($item['max_answer'] > $totals['max_answer']) {
                $totals['max_answer'] = $item['max_answer'];
            }
        }

        // Calcul de l'avg global à partir de la moyenne pondérée
        if ($totals['total'] > 0) {
            $sumLengths = 0;
            $countLengths = 0;
            foreach ($byLevelSubject as $item) {
                if ($item['avg_answer'] > 0 && $item['total'] > 0) {
                    $sumLengths += ($item['avg_answer'] * $item['total']);
                    $countLengths += $item['total'];
                }
            }
            if ($countLengths > 0) {
                $totals['avg_answer'] = round($sumLengths / $countLengths, 0);
            }
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'request_id' => api_request_id(),
            'updated_at' => date('Y-m-d H:i:s'),
            'levels' => array_keys($levels),
            'subjects' => array_keys($subjects),
            'totals' => $totals,
            'byLevelSubject' => $byLevelSubject,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if ($type === 'placeholders') {
        // Détection de placeholders dans les exercices
        function isPlaceholderLine($line)
        {
            $trim = trim($line);
            $patterns = [
                '/^[a-dA-D][\)\.\-]\s*Réponse\s+correcte\s*$/u',
                '/^[a-dA-D][\)\.\-]\s*Une\s+autre\s+réponse\s+possible\s*$/u',
                '/^[a-dA-D][\)\.\-]\s*aucune\s+des\s+réponses\s*$/ui',
                '/^[a-dA-D][\)\.\-]\s*Réponse\s+alternative\s*$/u',
                '/^\-\s*Réponse\s+correcte\s*$/u',
                '/^\-\s*Une\s+autre\s+réponse\s+possible\s*$/u',
                '/^\-\s*aucune\s+des\s+réponses\s*$/ui',
                '/^\-\s*Réponse\s+alternative\s*$/u',
            ];
            foreach ($patterns as $p) {
                if (preg_match($p, $trim)) {
                    return true;
                }
            }
            return false;
        }
        function hasPlaceholders($text)
        {
            if (!$text) {
                return false;
            }
            $lines = preg_split('/\r?\n/', $text);
            foreach ($lines as $line) {
                if (isPlaceholderLine($line)) {
                    return true;
                }
            }
            return false;
        }
        $levels = ['6ème','5ème','4ème','3ème','Seconde','Première','Terminale','Bac'];
        $result = [];
        foreach ($levels as $level) {
            $stmt = $pdo->prepare("SELECT Id, Level, Subject, Title, Content, Answer FROM exercises WHERE Level = ? ORDER BY Id ASC LIMIT 1");
            $stmt->execute([$level]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $result[] = [
                    'level' => ($level === 'Bac' ? 'BAC' : $level),
                    'hasExercise' => false,
                ];
                continue;
            }
            $exerciseId = (int) $row['Id'];
            // Charger les cours liés si possible
            $linkedCourses = [];
            $linkedCoursesCount = 0;
            if (function_exists('getCoursesForExercise')) {
                $linkedCourses = getCoursesForExercise($exerciseId);
                $linkedCoursesCount = count($linkedCourses);
            }

            $result[] = [
                'level' => ($row['Level'] === 'Bac' ? 'BAC' : $row['Level']),
                'hasExercise' => true,
                'exercise' => [
                    'id' => $exerciseId,
                    'subject' => $row['Subject'],
                    'title' => $row['Title'],
                    'placeholdersInContent' => hasPlaceholders($row['Content']),
                    'placeholdersInAnswer' => hasPlaceholders($row['Answer']),
                    'linkedCoursesCount' => $linkedCoursesCount,
                    'linkedCourses' => array_map(function ($c) {
                        return [
                            'id' => $c['Id'],
                            'title' => $c['Title'],
                            'subject' => $c['Subject'],
                            'level' => $c['Level'],
                        ];
                    }, $linkedCourses),
                ],
            ];
        }
        json_response($result);
    }
    json_error('Type de requête non supporté', 400, 'ERR_VALIDATION');
} catch (Exception $e) {
    json_error('Erreur serveur', 500, 'ERR_QUALITY');
}
