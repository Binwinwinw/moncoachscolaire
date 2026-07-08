<?php

/**
 * Endpoint de soumission de quiz diagnostic.
 *
 * POST JSON attendu:
 * {
 *   "quiz_id": 100,
 *   "answers": { "q0": "...", "q1": "..." },
 *   "duration_seconds": 120
 * }
 */

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_file(__DIR__ . '/../../config/config.php')) {
    require_once __DIR__ . '/../../config/config.php';
}
if (is_file(__DIR__ . '/../../database/connection.php')) {
    require_once __DIR__ . '/../../database/connection.php';
}
if (is_file(__DIR__ . '/../../includes/login_security.php')) {
    require_once __DIR__ . '/../../includes/login_security.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Methode non autorisee',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] <= 0) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Connexion requise',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Connexion base indisponible',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody ?: '{}', true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Payload JSON invalide',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($payload['csrf_token'] ?? ''));
$csrfValid = function_exists('verifyCSRFToken')
    ? verifyCSRFToken($csrfToken)
    : (
        isset($_SESSION['csrf_token'])
        && $csrfToken !== ''
        && hash_equals((string) $_SESSION['csrf_token'], $csrfToken)
    );

if (!$csrfValid) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Jeton CSRF invalide',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$quizId = isset($payload['quiz_id']) ? (int) $payload['quiz_id'] : 0;
$answers = $payload['answers'] ?? [];
$durationSeconds = isset($payload['duration_seconds']) ? (int) $payload['duration_seconds'] : 0;
$userId = (int) $_SESSION['user_id'];
$sessionRole = strtolower((string) ($_SESSION['user_role'] ?? $_SESSION['role'] ?? ''));
$requestedChildUserId = isset($payload['child_user_id']) ? (int) $payload['child_user_id'] : 0;

if (in_array($sessionRole, ['parent', 'parents'], true)) {
    if ($requestedChildUserId <= 0) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => 'child_user_id est obligatoire pour un parent',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $parentUserId = (int) ($_SESSION['parent_id'] ?? $_SESSION['user_id'] ?? 0);
    $linkStmt = $pdo->prepare(
        "SELECT 1
         FROM parent_child_invites pci
         JOIN users u ON u.Id = pci.child_user_id AND u.Role = 'student'
         WHERE pci.parent_user_id = ? AND pci.child_user_id = ? AND pci.status = 'accepted'
         LIMIT 1"
    );
    $linkStmt->execute([$parentUserId, $requestedChildUserId]);
    if (!$linkStmt->fetchColumn()) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Enfant non autorisé pour ce parent',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userId = $requestedChildUserId;
}

// 🆕 Gestion révision : fusionner réponses initiales + nouvelles réponses
$isReview = isset($payload['is_review']) && $payload['is_review'] === true;
$originalAnswers = $isReview && isset($payload['original_answers']) ? $payload['original_answers'] : null;
$reviewedIndexes = $isReview && isset($payload['reviewed_question_indexes']) ? $payload['reviewed_question_indexes'] : [];

// Si révision, fusionner les réponses : nouvelles pour les questions revues, anciennes pour le reste
if ($isReview && is_array($originalAnswers) && is_array($reviewedIndexes)) {
    $mergedAnswers = $originalAnswers;
    foreach ($reviewedIndexes as $idx) {
        $key = 'q' . $idx;
        if (isset($answers[$key])) {
            $mergedAnswers[$key] = $answers[$key];
        }
    }
    $answers = $mergedAnswers;
}

if ($quizId <= 0 || !is_array($answers)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'error' => 'quiz_id et answers sont obligatoires',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$dataDir = dirname(__DIR__, 2) . '/data';
$loadedPayload = loadQuizPayloadForSubmit($quizId, $dataDir);

if ($loadedPayload === null) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Quiz ou reponses introuvables cote serveur',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$quizData = $loadedPayload['quiz_data'];
$answersData = $loadedPayload['answers_data'];

$questions = $quizData['quiz']['questions'] ?? [];
$answerRows = $answersData['quiz']['answers'] ?? [];

if (!is_array($questions) || count($questions) === 0) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Quiz serveur incomplet',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!is_array($answerRows) || count($answerRows) === 0) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fichier reponses serveur incomplet',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$answersByQuestionId = [];
$answersByIndex = [];
foreach ($answerRows as $row) {
    if (!is_array($row)) {
        continue;
    }

    $index = isset($row['index']) ? (int) $row['index'] : null;
    $questionId = isset($row['question_id']) ? (int) $row['question_id'] : null;

    if ($index !== null) {
        $answersByIndex[$index] = $row;
    }
    if ($questionId !== null) {
        $answersByQuestionId[$questionId] = $row;
    }
}

$questionCount = count($questions);
$correctCount = 0;
$results = [];
$toReview = [];
$strengths = [];

foreach ($questions as $index => $question) {
    $userAnswer = $answers['q' . $index] ?? $answers[(string) $index] ?? '';
    $questionId = isset($question['id']) ? (int) $question['id'] : ($index + 1);
    $answerDefinition = $answersByQuestionId[$questionId] ?? $answersByIndex[$index] ?? null;

    $expected = is_array($answerDefinition) ? ($answerDefinition['answer'] ?? null) : null;
    if ($expected === null || $expected === '') {
        $expected = inferExpectedAnswer($question, $answerDefinition);
    }
    $correction = is_array($answerDefinition) ? (string) ($answerDefinition['correction'] ?? '') : '';
    $type = isset($question['type']) ? (string) $question['type'] : (
        is_array($answerDefinition) && isset($answerDefinition['type']) ? (string) $answerDefinition['type'] : 'texte'
    );

    $isCorrect = isAnswerCorrect($type, $userAnswer, $expected);
    if ($isCorrect) {
        $correctCount++;
        $strengths[] = 'Q' . ($index + 1);
    } else {
        $toReview[] = 'Q' . ($index + 1);
    }

    $results[] = [
        'question_id' => $questionId,
        'question' => (string) ($question['question'] ?? ''),
        'type' => $type,
        'user_answer' => normalizeAnswerForStorage($userAnswer),
        'expected_answer' => normalizeAnswerForStorage($expected),
        'is_correct' => $isCorrect,
        'correction' => $correction,
    ];
}

$score = $questionCount > 0 ? round(($correctCount / $questionCount) * 100, 2) : 0.0;
$passingScore = (int) ($quizData['quiz']['passing_score'] ?? 70);
$passed = $score >= $passingScore;
$baseXp = max(5, (int) round(($correctCount / max(1, $questionCount)) * 20));
$xpGained = 0;

if ($durationSeconds <= 0) {
    $durationSeconds = 60;
}

$quizLevel = (string) ($quizData['quiz']['level'] ?? $quizData['contents']['level'] ?? '');
$quizSubject = (string) ($quizData['quiz']['subject'] ?? $quizData['contents']['subject'] ?? '');
$quizTitle = (string) ($quizData['quiz']['title'] ?? $quizData['contents']['title'] ?? ('Diagnostic #' . $quizId));
$quizDescription = (string) ($quizData['contents']['description'] ?? 'Diagnostic personnalise');

$oldScore = null;

try {
    $pdo->beginTransaction();

    $quizRowId = findOrCreateQuizRow(
        $pdo,
        $quizLevel,
        $quizSubject,
        $quizTitle,
        $quizDescription,
        $questionCount,
        $passingScore,
    );

    $selectPreviousAttempt = $pdo->prepare(
        'SELECT score FROM quizresult WHERE user_id = ? AND quiz_id = ? ORDER BY created_at DESC LIMIT 1 FOR UPDATE',
    );
    $selectPreviousAttempt->execute([$userId, $quizRowId]);
    $previousAttempt = $selectPreviousAttempt->fetch(PDO::FETCH_ASSOC);
    $previousScore = $previousAttempt ? (float) $previousAttempt['score'] : null;

    $selectBest24h = $pdo->prepare(
        'SELECT MAX(score) AS best_score_24h FROM quizresult WHERE user_id = ? AND quiz_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)',
    );
    $selectBest24h->execute([$userId, $quizRowId]);
    $best24hRow = $selectBest24h->fetch(PDO::FETCH_ASSOC);
    $bestScore24h = $best24hRow && $best24hRow['best_score_24h'] !== null ? (float) $best24hRow['best_score_24h'] : null;

    $xpGained = computeXpGainAntiFarming($baseXp, $score, $previousScore, $bestScore24h, $isReview);

    if ($isReview && $previousScore !== null) {
        $oldScore = $previousScore;
    }

    $insertResult = $pdo->prepare(
        'INSERT INTO quizresult (user_id, quiz_id, score, passed, time_spent_seconds, answers, created_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())',
    );
    $insertResult->execute([
        $userId,
        $quizRowId,
        $score,
        $passed ? 1 : 0,
        $durationSeconds,
        json_encode($results, JSON_UNESCAPED_UNICODE),
    ]);

    $selectProgress = $pdo->prepare('SELECT Id, XP FROM userprogress WHERE UserId = ? LIMIT 1 FOR UPDATE');
    $selectProgress->execute([$userId]);
    $progressRow = $selectProgress->fetch(PDO::FETCH_ASSOC);

    if ($progressRow) {
        $updateProgress = $pdo->prepare('UPDATE userprogress SET XP = XP + ?, UpdatedAt = NOW() WHERE UserId = ?');
        $updateProgress->execute([$xpGained, $userId]);
        $xpTotal = (int) $progressRow['XP'] + $xpGained;
    } else {
        $insertProgress = $pdo->prepare(
            'INSERT INTO userprogress (UserId, CurrentPosition, XP, ProgressJson, UpdatedAt) VALUES (?, 1, ?, NULL, NOW())',
        );
        $insertProgress->execute([$userId, $xpGained]);
        $xpTotal = $xpGained;
    }

    $pdo->commit();

    $responseData = [
        'success' => true,
        'data' => [
            'quiz_id' => $quizId,
            'quiz_title' => $quizTitle,
            'level' => $quizLevel,
            'subject' => $quizSubject,
            'score' => $score,
            'passed' => $passed,
            'correct_count' => $correctCount,
            'total_questions' => $questionCount,
            'passing_score' => $passingScore,
            'xp_gained' => $xpGained,
            'xp_total' => $xpTotal,
            'anti_farming' => true,
            'results' => $results,
            'feedback' => [
                'message' => $isReview && $oldScore !== null
                    ? buildReviewMessage($score, $oldScore)
                    : buildEncouragingMessage($score),
                'strengths' => array_slice($strengths, 0, 4),
                'to_review' => array_slice($toReview, 0, 4),
            ],
        ],
    ];

    // 🆕 Ajouter métadonnées révision si applicable
    if ($isReview) {
        $responseData['data']['is_review'] = true;
        if ($oldScore !== null) {
            $responseData['data']['old_score'] = $oldScore;
        }
    }

    echo json_encode($responseData, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('diagnostic submit error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur lors de la correction',
    ], JSON_UNESCAPED_UNICODE);
}

function loadQuizPayloadForSubmit(int $quizId, string $dataDir): ?array
{
    $bundlePayload = loadQuizPayloadFromBundles($quizId, $dataDir);
    if ($bundlePayload !== null) {
        return $bundlePayload;
    }

    $quizPath = $dataDir . '/quiz/' . $quizId . '.json';
    $answersPath = findQuizAnswersPath($quizId, $dataDir . '/quiz_answers');

    if (!is_file($quizPath) || $answersPath === false || !is_file($answersPath)) {
        return null;
    }

    $quizRaw = file_get_contents($quizPath);
    $answersRaw = file_get_contents($answersPath);

    $quizData = json_decode($quizRaw ?: '{}', true);
    $answersData = json_decode($answersRaw ?: '{}', true);

    if (!is_array($quizData) || !is_array($answersData)) {
        return null;
    }

    return [
        'quiz_data' => $quizData,
        'answers_data' => $answersData,
        'source' => 'files',
    ];
}

function loadQuizPayloadFromBundles(int $quizId, string $dataDir): ?array
{
    $bundleIndexPath = $dataDir . '/quiz_packs/index.json';
    $bundleAnswersIndexPath = $dataDir . '/quiz_answers_packs/index.json';

    if (!is_file($bundleIndexPath) || !is_file($bundleAnswersIndexPath)) {
        return null;
    }

    $indexRaw = file_get_contents($bundleIndexPath);
    $indexData = json_decode($indexRaw ?: '{}', true);

    if (!is_array($indexData) || !isset($indexData['quiz_id_to_level'][(string) $quizId])) {
        return null;
    }

    $level = (string) $indexData['quiz_id_to_level'][(string) $quizId];
    if ($level === '') {
        return null;
    }

    $quizBundlePath = $dataDir . '/quiz_packs/' . $level . '.json';
    $answersBundlePath = $dataDir . '/quiz_answers_packs/' . $level . '.json';

    if (!is_file($quizBundlePath) || !is_file($answersBundlePath)) {
        return null;
    }

    $quizBundleRaw = file_get_contents($quizBundlePath);
    $answersBundleRaw = file_get_contents($answersBundlePath);

    $quizBundleData = json_decode($quizBundleRaw ?: '{}', true);
    $answersBundleData = json_decode($answersBundleRaw ?: '{}', true);

    if (!is_array($quizBundleData) || !is_array($answersBundleData)) {
        return null;
    }

    $quizData = findQuizInBundle($quizBundleData, $quizId);
    $answersData = findQuizInBundle($answersBundleData, $quizId);

    if (!is_array($quizData) || !is_array($answersData)) {
        return null;
    }

    return [
        'quiz_data' => $quizData,
        'answers_data' => $answersData,
        'source' => 'bundles',
    ];
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

function findQuizInBundle(array $bundleData, int $quizId): ?array
{
    $quizzes = $bundleData['quizzes'] ?? null;
    if (!is_array($quizzes)) {
        return null;
    }

    foreach ($quizzes as $entry) {
        if (!is_array($entry)) {
            continue;
        }

        if (!isset($entry['id']) || (int) $entry['id'] !== $quizId) {
            continue;
        }

        $quizData = $entry;
        unset($quizData['id']);
        return $quizData;
    }

    return null;
}

function normalizeAnswerForStorage($value): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_array($value)) {
        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
    }

    return trim((string) $value);
}

function normalizeText(string $value): string
{
    $value = mb_strtolower(trim($value), 'UTF-8');
    $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
    return $value;
}

function isAnswerCorrect(string $type, $userAnswer, $expected): bool
{
    $normalizedType = normalizeText($type);

    if ($normalizedType === 'vrai-faux' || $normalizedType === 'vrai_faux' || $normalizedType === 'boolean') {
        $u = normalizeText((string) $userAnswer);
        $expectedBool = filter_var($expected, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $truthy = ['vrai', 'true', '1', 'oui'];
        $falsy = ['faux', 'false', '0', 'non'];

        if (in_array($u, $truthy, true)) {
            return $expectedBool === true;
        }
        if (in_array($u, $falsy, true)) {
            return $expectedBool === false;
        }

        return false;
    }

    $u = normalizeText((string) $userAnswer);
    $e = normalizeText((string) $expected);

    return $u !== '' && $u === $e;
}

function inferExpectedAnswer(array $question, ?array $answerDefinition)
{
    $type = normalizeText((string) ($question['type'] ?? ($answerDefinition['type'] ?? '')));
    $correction = is_array($answerDefinition) ? (string) ($answerDefinition['correction'] ?? '') : '';

    if ($type === 'vrai-faux' || $type === 'vrai_faux' || $type === 'boolean') {
        $normalizedCorrection = normalizeText($correction);
        if (preg_match('/\b(vrai|true)\b/u', $normalizedCorrection)) {
            return true;
        }
        if (preg_match('/\b(faux|false)\b/u', $normalizedCorrection)) {
            return false;
        }
        return null;
    }

    $choices = $question['choices'] ?? [];
    if (is_array($choices) && count($choices) > 0) {
        foreach ($choices as $choice) {
            if (!is_string($choice)) {
                continue;
            }

            if (preg_match('/[✓✔]$/u', trim($choice))) {
                return trim(preg_replace('/[✓✔]$/u', '', $choice) ?? $choice);
            }
        }

        $normalizedCorrection = normalizeText($correction);
        foreach ($choices as $choice) {
            if (!is_string($choice)) {
                continue;
            }

            $normalizedChoice = normalizeText($choice);
            if ($normalizedChoice !== '' && str_contains($normalizedCorrection, $normalizedChoice)) {
                return $choice;
            }
        }
    }

    return null;
}

function buildEncouragingMessage(float $score): string
{
    if ($score >= 90) {
        return 'Excellent travail ! Tu maitrises tres bien ces notions.';
    }
    if ($score >= 70) {
        return 'Bravo ! Le diagnostic est valide, continue sur cette dynamique.';
    }
    if ($score >= 50) {
        return 'Belle progression ! Encore quelques points a consolider et tu vas y arriver.';
    }

    return 'Bonne tentative ! On identifie maintenant les notions a renforcer pour progresser rapidement.';
}

function buildReviewMessage(float $newScore, float $oldScore): string
{
    $improvement = $newScore - $oldScore;

    if ($improvement >= 30) {
        return 'Enorme progres ! Ta perseverance paie : +' . round($improvement, 1) . ' points. Continue comme ca !';
    }
    if ($improvement >= 15) {
        return 'Super amelioration ! +' . round($improvement, 1) . ' points. Tu as bien travaille tes lacunes.';
    }
    if ($improvement > 0) {
        return 'Bien joue ! +' . round($improvement, 1) . ' points. Chaque progres compte, continue sur cette lancee.';
    }
    if ($improvement === 0.0) {
        return 'Score stable. Tu as revise, c\'est deja une victoire. Besoin de plus de pratique sur ces notions.';
    }

    // Cas rare : score en baisse (fatigue, stress...)
    return 'Pas de panique ! Parfois ca fluctue. Repose-toi et retente quand tu es pret.';
}

function computeXpGainAntiFarming(int $baseXp, float $score, ?float $previousScore, ?float $bestScore24h, bool $isReview): int
{
    if ($previousScore === null) {
        return $baseXp;
    }

    $improvement = $score - $previousScore;
    if ($improvement <= 0.01) {
        return 0;
    }

    $scaledXp = (int) round((min(20.0, $improvement) / 20.0) * $baseXp);
    $xp = max(1, $scaledXp);

    if ($bestScore24h !== null && $score <= $bestScore24h + 0.01) {
        $xp = min($xp, 2);
    }

    if ($isReview) {
        $xp = min($xp, 6);
    }

    return $xp;
}

function findOrCreateQuizRow(
    PDO $pdo,
    string $level,
    string $subject,
    string $title,
    string $description,
    int $questionCount,
    int $passingScore,
): int {
    $findQuiz = $pdo->prepare(
        'SELECT id FROM quiz WHERE type = "diagnostic" AND level = ? AND subject <=> ? AND title = ? LIMIT 1',
    );
    $subjectValue = $subject !== '' ? $subject : null;
    $findQuiz->execute([$level, $subjectValue, $title]);
    $quizId = $findQuiz->fetchColumn();

    if ($quizId) {
        return (int) $quizId;
    }

    $createQuiz = $pdo->prepare(
        'INSERT INTO quiz (level, subject, notion_id, type, title, description, question_count, passing_score, created_at)
         VALUES (?, ?, NULL, "diagnostic", ?, ?, ?, ?, NOW())',
    );
    $createQuiz->execute([
        $level,
        $subjectValue,
        $title,
        $description,
        $questionCount,
        $passingScore,
    ]);

    return (int) $pdo->lastInsertId();
}
