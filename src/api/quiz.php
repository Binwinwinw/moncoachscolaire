<?php

/**
 * API endpoint to serve quiz JSON from src/data/quiz.
 *
 * GET /src/api/quiz.php?id={id}
 */

header('Content-Type: application/json; charset=utf-8');

$quizIdRaw = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
$includeAnswers = isset($_GET['include_answers']) && $_GET['include_answers'] === '1';
if ($quizIdRaw === '' || !preg_match('/^\d+$/', $quizIdRaw)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID quiz invalide',
        'received' => $quizIdRaw,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$quizPath = __DIR__ . '/../data/quiz/' . $quizIdRaw . '.json';
$dataDir = realpath(__DIR__ . '/../data/quiz');
$realPath = realpath($quizPath);

if ($dataDir === false || $realPath === false || strpos($realPath, $dataDir) !== 0 || !is_file($realPath)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Quiz non trouve',
        'id' => (int) $quizIdRaw,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$content = file_get_contents($realPath);
$decoded = json_decode($content ?: '{}', true);

if (!is_array($decoded)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Format JSON invalide',
        'id' => (int) $quizIdRaw,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($decoded['contents']) || !is_array($decoded['contents'])) {
    $decoded['contents'] = [];
}
$decoded['contents']['_source'] = 'src/data/quiz/' . $quizIdRaw . '.json';
$decoded['success'] = true;

if ($includeAnswers) {
        $answersPath = findQuizAnswersPath((int) $quizIdRaw, __DIR__ . '/../data/quiz_answers');
        if ($answersPath !== false) {
            $answersContent = file_get_contents($answersPath);
            $answersDecoded = json_decode($answersContent ?: '{}', true);
            if (is_array($answersDecoded)) {
                $decoded['answers'] = $answersDecoded['quiz']['answers'] ?? [];
            }
        }
    }

function findQuizAnswersPath(int $quizId, string $answersDir)
{
    if ($answersDir === '') {
        return false;
    }

    $candidates = [
        $answersDir . '/' . $quizId . '.json',
        $answersDir . '/' . str_pad((string) $quizId, 4, '0', STR_PAD_LEFT) . '.json',
        $answersDir . '/' . str_pad((string) $quizId, 5, '0', STR_PAD_LEFT) . '.json',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return false;
}
echo json_encode($decoded, JSON_UNESCAPED_UNICODE);
