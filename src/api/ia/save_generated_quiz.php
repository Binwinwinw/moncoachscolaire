<?php

/**
 * Endpoint pour sauvegarder un quiz généré par l'IA dans la bibliothèque d'exercices
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/exercice_loader.php';
if (is_file(__DIR__ . '/../../includes/login_security.php')) {
    require_once __DIR__ . '/../../includes/login_security.php';
}
require_once __DIR__ . '/../_core/bootstrap.php';

api_require([
    'method' => 'POST',
    'auth' => true,
    'csrf' => true,
    'rate' => [
        'key' => 'ai_save_quiz',
        'limit' => 20,
        'window' => 60,
    ],
]);

$requestId = 'rq_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));

function normalizeDiagnosticLevel(string $level): string
{
    $normalized = strtolower(trim($level));
    $normalized = str_replace(['é', 'è', 'ê'], 'e', $normalized);
    $normalized = preg_replace('/\s+/', '', $normalized) ?? $normalized;

    $map = [
        '6eme' => '6eme',
        '5eme' => '5eme',
        '4eme' => '4eme',
        '3eme' => '3eme',
        'seconde' => '2nde',
        '2nde' => '2nde',
        'premiere' => '1ere',
        '1ere' => '1ere',
        'terminale' => 'terminale',
        'bac' => 'bac',
    ];

    return $map[$normalized] ?? $normalized;
}

function findNextDiagnosticQuizId(string $quizDir, string $answersDir): int
{
    $maxId = 0;
    foreach ([$quizDir, $answersDir] as $dir) {
        if (!is_dir($dir)) {
            continue;
        }

        $files = glob($dir . '/*.json');
        if (!is_array($files)) {
            continue;
        }

        foreach ($files as $path) {
            $name = basename((string) $path, '.json');
            if (ctype_digit($name)) {
                $maxId = max($maxId, (int) $name);
            }
        }
    }

    return $maxId + 1;
}

try {
    $input = api_get_json_body(true);
    if (!is_array($input)) {
        throw new Exception('JSON invalide.');
    }

    $questions = $input['questions'] ?? [];
    $subject = $input['subject'] ?? '';
    $level = $input['level'] ?? '';

    if (empty($questions) || !is_array($questions)) {
        throw new Exception('Aucune question à sauvegarder.');
    }

    if (empty($subject) || empty($level)) {
        throw new Exception('Sujet ou niveau manquant.');
    }

    global $pdo;
    $pdoConnection = $pdo instanceof PDO ? $pdo : null;
    if (!$pdoConnection instanceof PDO) {
        throw new Exception('Connexion à la base de données impossible.');
    }

    // Normalisation du niveau et de la matière pour la BDD
    if (function_exists('normalizeLevelForDB')) {
        $levelDB = normalizeLevelForDB($level);
    } else {
        $levelDB = $level;
    }

    if (function_exists('normalizeSubject')) {
        $subjectDB = normalizeSubject($subject);
    } else {
        $subjectDB = $subject;
    }

    $insertedCount = 0;
    $diagnosticQuestions = [];
    $diagnosticAnswers = [];
    $createdFilePaths = [];

    $pdoConnection->beginTransaction();

    $stmt = $pdoConnection->prepare(
        "INSERT INTO `exercises` (
            `Title`, `Content`, `Answer`, `Subject`, `Level`,
            `AnswerType`, `Choices`, `is_active`, `created_at`
        ) VALUES (
            :title, :content, :answer, :subject, :level,
            'choix', :choices, 1, NOW()
        )"
    );

    foreach ($questions as $index => $q) {
        $qText = $q['question'] ?? '';
        $choices = $q['choices'] ?? [];
        $correctKey = $q['correct'] ?? '';

        if (empty($qText)) {
            continue;
        }

        // Trouver le label de la réponse correcte
        $correctLabel = '';
        foreach ($choices as $choice) {
            if (!is_array($choice)) {
                continue;
            }

            $choiceValue = (string) ($choice['value'] ?? '');
            $choiceLabel = (string) ($choice['label'] ?? '');

            if ($choiceValue === (string) $correctKey) {
                $correctLabel = $choiceLabel;
                break;
            }
        }

        $choiceLabels = [];
        foreach ($choices as $choice) {
            if (is_array($choice)) {
                $label = trim((string) ($choice['label'] ?? ''));
                if ($label !== '') {
                    $choiceLabels[] = $label;
                }
            } elseif (is_string($choice) && trim($choice) !== '') {
                $choiceLabels[] = trim($choice);
            }
        }

        if (count($choiceLabels) < 2) {
            continue;
        }

        $title = "Quiz IA - " . $subjectDB . " (" . $levelDB . ") - Q" . ($index + 1);

        $stmt->execute([
            'title' => $title,
            'content' => $qText,
            'answer' => $correctLabel,
            'subject' => $subjectDB,
            'level' => $levelDB,
            'choices' => json_encode($choices, JSON_UNESCAPED_UNICODE)
        ]);

        $diagnosticQuestions[] = [
            'id' => $index + 1,
            'type' => 'qcm',
            'question' => (string) $qText,
            'choices' => array_values($choiceLabels),
        ];

        $diagnosticAnswers[] = [
            'index' => $index,
            'question_id' => $index + 1,
            'type' => 'qcm',
            'answer' => (string) $correctLabel,
            'correction' => $correctLabel !== ''
                ? ('La bonne réponse est : ' . $correctLabel)
                : 'Correction indisponible',
        ];

        $insertedCount++;
    }

    if ($insertedCount === 0 || count($diagnosticQuestions) === 0) {
        throw new Exception('Aucune question valide à sauvegarder.');
    }

    $quizDir = dirname(__DIR__, 2) . '/data/quiz';
    $answersDir = dirname(__DIR__, 2) . '/data/quiz_answers';

    if (!is_dir($quizDir) || !is_dir($answersDir)) {
        throw new Exception('Répertoires de quiz diagnostics introuvables.');
    }

    $diagnosticLevel = normalizeDiagnosticLevel((string) $levelDB);
    $quizId = findNextDiagnosticQuizId($quizDir, $answersDir);
    $isoNow = gmdate('c');
    $quizTitle = 'Quiz IA ' . $subjectDB . ' - ' . $diagnosticLevel;

    $quizPayload = [
        'contents' => [
            'title' => $quizTitle,
            'type' => 'quiz',
            'level' => $diagnosticLevel,
            'subject' => $subjectDB,
            'description' => 'Quiz diagnostique généré par IA',
            'status' => 'published',
            'created_at' => $isoNow,
            'updated_at' => $isoNow,
        ],
        'quiz' => [
            'title' => $quizTitle,
            'type' => 'quiz',
            'level' => $diagnosticLevel,
            'subject' => $subjectDB,
            'question_count' => count($diagnosticQuestions),
            'passing_score' => 70,
            'time_limit_minutes' => 15,
            'questions' => $diagnosticQuestions,
        ],
        'exercisenotion' => [],
        'exerciseresponses' => [],
    ];

    $answersPayload = [
        'contents' => [
            'title' => $quizTitle,
            'level' => $diagnosticLevel,
            'subject' => $subjectDB,
        ],
        'quiz' => [
            'title' => $quizTitle,
            'question_count' => count($diagnosticAnswers),
            'level' => $diagnosticLevel,
            'subject' => $subjectDB,
            'answers' => $diagnosticAnswers,
        ],
    ];

    $quizPath = $quizDir . '/' . $quizId . '.json';
    $answersPath = $answersDir . '/' . $quizId . '.json';

    $quizJson = json_encode($quizPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $answersJson = json_encode($answersPayload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    if ($quizJson === false || $answersJson === false) {
        throw new Exception('Impossible de sérialiser les fichiers quiz diagnostics.');
    }

    if (file_put_contents($quizPath, $quizJson . PHP_EOL) === false) {
        throw new Exception('Impossible d\'écrire le fichier quiz diagnostic.');
    }
    $createdFilePaths[] = $quizPath;

    if (file_put_contents($answersPath, $answersJson . PHP_EOL) === false) {
        throw new Exception('Impossible d\'écrire le fichier réponses diagnostic.');
    }
    $createdFilePaths[] = $answersPath;

    $pdoConnection->commit();

    api_additive_response([
        'success' => true,
        'message' => "$insertedCount questions ont été ajoutées à la bibliothèque.",
        'count' => $insertedCount,
        'data' => [
            'count' => $insertedCount,
            'subject' => $subjectDB,
            'level' => $levelDB,
            'diagnostic_quiz_id' => $quizId,
        ],
        'meta' => [
            'request_id' => $requestId,
            'saved_at' => date('c'),
        ],
    ], 200);

} catch (Throwable $e) {
    if (isset($createdFilePaths) && is_array($createdFilePaths)) {
        foreach ($createdFilePaths as $path) {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }

    if (isset($pdoConnection) && $pdoConnection instanceof PDO && $pdoConnection->inTransaction()) {
        $pdoConnection->rollBack();
    }
    api_additive_error($e->getMessage(), 400, [], ['request_id' => $requestId]);
}
