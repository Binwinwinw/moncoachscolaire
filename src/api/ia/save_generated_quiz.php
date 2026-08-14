<?php

/**
 * Endpoint pour sauvegarder un quiz généré par l'IA dans les révisions privées
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/ai_course_generator.php';
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

    $validatedQuestions = [];

    foreach ($questions as $index => $q) {
        if (!is_array($q)) {
            throw new InvalidArgumentException('Question ' . ($index + 1) . ' invalide.');
        }

        $qText = trim((string) ($q['question'] ?? ''));
        $choices = $q['choices'] ?? [];
        $correctKey = trim((string) ($q['correct'] ?? ''));

        if ($qText === '') {
            throw new InvalidArgumentException('Le texte de la question ' . ($index + 1) . ' est requis.');
        }

        if (!is_array($choices) || count($choices) < 2) {
            throw new InvalidArgumentException('La question ' . ($index + 1) . ' doit proposer au moins deux choix.');
        }

        $validatedChoices = [];
        $hasCorrectChoice = false;
        foreach ($choices as $choice) {
            if (!is_array($choice)) {
                throw new InvalidArgumentException('Un choix de la question ' . ($index + 1) . ' est invalide.');
            }

            $choiceValue = trim((string) ($choice['value'] ?? ''));
            $choiceLabel = trim((string) ($choice['label'] ?? ''));
            if ($choiceValue === '' || $choiceLabel === '') {
                throw new InvalidArgumentException('Chaque choix de la question ' . ($index + 1) . ' doit avoir une valeur et un libellé.');
            }

            if ($choiceValue === $correctKey) {
                $hasCorrectChoice = true;
            }

            $validatedChoices[] = [
                'value' => $choiceValue,
                'label' => $choiceLabel,
            ];
        }

        if ($correctKey === '' || !$hasCorrectChoice) {
            throw new InvalidArgumentException('La réponse correcte de la question ' . ($index + 1) . ' ne correspond à aucun choix.');
        }

        $validatedQuestions[] = [
            'question' => $qText,
            'choices' => $validatedChoices,
            'correct' => $correctKey,
        ];
    }

    $quizTitle = 'Quiz IA ' . $subjectDB . ' - ' . $levelDB;
    $revisionId = saveAiRevision(
        (int) $_SESSION['user_id'],
        'quiz',
        $quizTitle,
        (string) $subjectDB,
        (string) $levelDB,
        null,
        [],
        ['source' => 'save_generated_quiz'],
        [
            'title' => $quizTitle,
            'subject' => $subjectDB,
            'level' => $levelDB,
            'questions' => $validatedQuestions,
        ]
    );

    if ($revisionId <= 0) {
        throw new RuntimeException('Impossible de sauvegarder le brouillon du quiz.');
    }

    $revisionUrl = function_exists('site_url')
        ? site_url('revisions', ['id' => $revisionId])
        : 'index.php?page=revisions&id=' . $revisionId;

    api_additive_response([
        'success' => true,
        'message' => 'Quiz sauvegardé dans tes révisions privées.',
        'count' => count($validatedQuestions),
        'revision_id' => $revisionId,
        'revision_url' => $revisionUrl,
        'data' => [
            'count' => count($validatedQuestions),
            'subject' => $subjectDB,
            'level' => $levelDB,
            'revision_id' => $revisionId,
            'revision_url' => $revisionUrl,
        ],
        'meta' => [
            'request_id' => $requestId,
            'saved_at' => date('c'),
        ],
    ], 200);
} catch (Throwable $e) {
    api_additive_error($e->getMessage(), 400, [], ['request_id' => $requestId]);
}
