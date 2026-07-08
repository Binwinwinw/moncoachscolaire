<?php

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (is_file(dirname(__DIR__, 2) . '/src/config/config.php')) {
    require_once dirname(__DIR__, 2) . '/src/config/config.php';
}
if (is_file(dirname(__DIR__, 2) . '/src/database/connection.php')) {
    require_once dirname(__DIR__, 2) . '/src/database/connection.php';
}

$levelRaw = isset($_GET['level']) ? strtolower(trim((string) $_GET['level'])) : '6eme';
$subject = isset($_GET['subject']) ? trim((string) $_GET['subject']) : '';
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
$sessionRole = strtolower((string) ($_SESSION['user_role'] ?? $_SESSION['role'] ?? ''));
$requestedChildId = filter_input(INPUT_GET, 'child_id', FILTER_VALIDATE_INT);

if (in_array($sessionRole, ['parent', 'parents'], true) && $requestedChildId) {
    if (!isset($pdo) || !$pdo instanceof PDO) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Connexion base indisponible',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $parentUserId = (int) ($_SESSION['parent_id'] ?? $_SESSION['user_id'] ?? 0);
    $linkStmt = $pdo->prepare(
        "SELECT u.level, u.preferred_subject
         FROM parent_child_invites pci
         JOIN users u ON u.Id = pci.child_user_id AND u.Role = 'student'
         WHERE pci.parent_user_id = ? AND pci.child_user_id = ? AND pci.status = 'accepted'
         LIMIT 1"
    );
    $linkStmt->execute([$parentUserId, (int) $requestedChildId]);
    $linkedChild = $linkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$linkedChild) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Enfant non autorisé pour ce parent',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $userId = (int) $requestedChildId;
    if ($levelRaw === '' || $levelRaw === '6eme') {
        $childLevel = trim((string) ($linkedChild['level'] ?? ''));
        if ($childLevel !== '') {
            $levelRaw = strtolower($childLevel);
        }
    }
    if ($subject === '') {
        $subject = trim((string) ($linkedChild['preferred_subject'] ?? ''));
    }
}

$levelCandidates = buildLevelCandidates($levelRaw);
$levelNormalized = $levelCandidates[0] ?? '6eme';

try {
    $quizDir = dirname(__DIR__) . '/data/quiz';
    $allQuiz = loadQuizCatalogFromJson($quizDir, $levelCandidates, $subject);

    // [02/04/2026] Filtrer les quizzes draft (marqués comme non-servables en diagnostic)
    $draftIds = [];
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->query("SELECT DISTINCT id FROM quiz WHERE status = 'draft'");
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $draftIds[(int)$row['id']] = true;
                }
            }
        } catch (PDOException $e) {
            // Colonne status peut ne pas exister si migration non appliquée
            // Continuer sans filtrage
        }
    }

    // Exclure les quizzes draft du catalogue servi
    if (!empty($draftIds)) {
        $allQuiz = array_filter($allQuiz, static function (array $quiz) use ($draftIds): bool {
            return !isset($draftIds[(int)$quiz['id']]);
        });
        $allQuiz = array_values($allQuiz); // Re-index
    }

    $historyStats = [];
    if ($userId > 0 && isset($pdo) && $pdo instanceof PDO) {
        $historyStats = loadUserQuizHistoryStats($pdo, $userId, $levelCandidates);
    }

    usort($allQuiz, static function (array $a, array $b) use ($historyStats): int {
        $statsA = getQuizHistoryStats($a, $historyStats);
        $statsB = getQuizHistoryStats($b, $historyStats);

        $attemptsA = (int) ($statsA['attempts'] ?? 0);
        $attemptsB = (int) ($statsB['attempts'] ?? 0);

        if ($attemptsA !== $attemptsB) {
            return $attemptsA <=> $attemptsB;
        }

        $lastSeenA = (string) ($statsA['last_seen_at'] ?? '');
        $lastSeenB = (string) ($statsB['last_seen_at'] ?? '');

        if ($lastSeenA === '' && $lastSeenB !== '') {
            return -1;
        }
        if ($lastSeenB === '' && $lastSeenA !== '') {
            return 1;
        }
        if ($lastSeenA !== '' && $lastSeenB !== '' && $lastSeenA !== $lastSeenB) {
            return strcmp($lastSeenA, $lastSeenB);
        }

        $subjectA = normalizeTextForSignature((string) ($a['subject'] ?? ''));
        $subjectB = normalizeTextForSignature((string) ($b['subject'] ?? ''));
        if ($subjectA !== $subjectB) {
            return strcmp($subjectA, $subjectB);
        }

        $aId = isset($a['id']) ? (int) $a['id'] : 0;
        $bId = isset($b['id']) ? (int) $b['id'] : 0;
        return $bId <=> $aId;
    });

    $quiz = $allQuiz;
    if ($subject === '') {
        $perSubject = [];
        foreach ($allQuiz as $row) {
            $subjectKey = normalizeTextForSignature((string) ($row['subject'] ?? ''));
            if ($subjectKey === '') {
                $subjectKey = '__sans_matiere__';
            }
            if (!isset($perSubject[$subjectKey])) {
                $perSubject[$subjectKey] = $row;
            }
        }
        $quiz = array_values($perSubject);
    }

    $recommendedQuiz = null;
    if (count($quiz) > 0) {
        $recommendedQuiz = [
            'id' => (int) $quiz[0]['id'],
            'title' => (string) ($quiz[0]['title'] ?? ''),
            'subject' => (string) ($quiz[0]['subject'] ?? ''),
        ];
    }

    if (isset($_GET['ids_only']) && trim((string) $_GET['ids_only']) === '1') {
        echo json_encode([
            'success' => true,
            'quiz' => $allQuiz,
            'count' => count($allQuiz),
            'total_pool' => count($allQuiz),
            'level' => $levelNormalized,
            'subject' => $subject,
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'quiz' => $quiz,
            'count' => count($quiz),
            'total_pool' => count($allQuiz),
            'recommendation' => $recommendedQuiz,
            'history_window' => 'full-history',
            'level' => $levelNormalized,
            'subject' => $subject,
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur lors du chargement des quiz diagnostics',
    ], JSON_UNESCAPED_UNICODE);
}

function buildLevelCandidates(string $inputLevel): array
{
    $normalized = normalizeSchoolLevel($inputLevel);
    if ($normalized === '') {
        $normalized = '6eme';
    }

    $candidates = [$normalized];

    if ($normalized === 'seconde') {
        $candidates[] = '2nde';
    }
    if ($normalized === '1ere') {
        $candidates[] = 'premiere';
    }

    return array_values(array_unique(array_filter($candidates, static fn($v) => $v !== '')));
}

function normalizeSchoolLevel(string $level): string
{
    $value = normalizeTextForSignature($level);

    $map = [
        '6eme' => '6eme',
        '5eme' => '5eme',
        '4eme' => '4eme',
        '3eme' => '3eme',
        '2nde' => 'seconde',
        'seconde' => 'seconde',
        'premiere' => '1ere',
        '1ere' => '1ere',
        'terminale' => 'terminale',
        'bac' => 'bac',
    ];

    return $map[$value] ?? '';
}

function normalizeTextForSignature(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $value = mb_strtolower($value, 'UTF-8');
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if (is_string($ascii) && $ascii !== '') {
        $value = $ascii;
    }

    $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;

    return trim($value);
}

function canonicalTitleTokens(string $title): string
{
    $stopWords = [
        'quiz', 'diagnostic', 'diag', 'test', 'evaluation',
        'de', 'du', 'des', 'la', 'le', 'les', 'en', 'pour',
    ];

    $normalized = normalizeTextForSignature($title);
    if ($normalized === '') {
        return '';
    }

    $tokens = explode(' ', $normalized);
    $filtered = [];

    foreach ($tokens as $token) {
        if ($token === '' || in_array($token, $stopWords, true)) {
            continue;
        }
        $filtered[$token] = true;
    }

    $tokenList = array_keys($filtered);
    sort($tokenList);

    return implode(' ', $tokenList);
}

function buildQuizSignature(string $title, string $subject, string $level): string
{
    $titleKey = canonicalTitleTokens($title);
    $subjectKey = normalizeTextForSignature($subject);
    $levelKey = normalizeSchoolLevel($level);

    if ($titleKey === '' && $subjectKey === '') {
        return '';
    }

    return $titleKey . '|' . $subjectKey . '|' . $levelKey;
}

function loadUserQuizHistoryStats(PDO $pdo, int $userId, array $levelCandidates): array
{
    if ($userId <= 0) {
        return [];
    }

    $stmt = $pdo->prepare(
        "SELECT q.title, q.subject, q.level, COUNT(*) AS attempts, MAX(qr.created_at) AS last_seen_at
         FROM quizresult qr
         JOIN quiz q ON q.id = qr.quiz_id
         WHERE qr.user_id = ? AND q.type = 'diagnostic'
         GROUP BY q.title, q.subject, q.level",
    );
    $stmt->execute([$userId]);

    $levelIndex = [];
    foreach ($levelCandidates as $candidate) {
        $normalized = normalizeSchoolLevel((string) $candidate);
        if ($normalized !== '') {
            $levelIndex[$normalized] = true;
        }
    }

    $stats = [];
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $historyLevel = normalizeSchoolLevel((string) ($row['level'] ?? ''));
        if ($historyLevel !== '' && !isset($levelIndex[$historyLevel])) {
            continue;
        }

        $signature = buildQuizSignature(
            (string) ($row['title'] ?? ''),
            (string) ($row['subject'] ?? ''),
            (string) ($row['level'] ?? ''),
        );

        if ($signature === '') {
            continue;
        }

        if (!isset($stats[$signature])) {
            $stats[$signature] = [
                'attempts' => 0,
                'last_seen_at' => '',
            ];
        }

        $stats[$signature]['attempts'] += (int) ($row['attempts'] ?? 0);
        $lastSeenAt = (string) ($row['last_seen_at'] ?? '');
        if ($lastSeenAt !== '' && ($stats[$signature]['last_seen_at'] === '' || strcmp($lastSeenAt, $stats[$signature]['last_seen_at']) > 0)) {
            $stats[$signature]['last_seen_at'] = $lastSeenAt;
        }
    }

    return $stats;
}

function getQuizHistoryStats(array $row, array $historyStats): array
{
    $signature = buildQuizSignature(
        (string) ($row['title'] ?? ''),
        (string) ($row['subject'] ?? ''),
        (string) ($row['level'] ?? ''),
    );

    if ($signature === '' || !isset($historyStats[$signature]) || !is_array($historyStats[$signature])) {
        return [
            'attempts' => 0,
            'last_seen_at' => '',
        ];
    }

    return $historyStats[$signature];
}

function loadQuizCatalogFromJson(string $quizDir, array $levelCandidates, string $subjectFilter): array
{
    if ($quizDir === '' || !is_dir($quizDir)) {
        return [];
    }

    $subjectFilterNormalized = normalizeTextForSignature($subjectFilter);
    $levelIndex = [];
    foreach ($levelCandidates as $candidate) {
        $normalized = normalizeSchoolLevel((string) $candidate);
        if ($normalized !== '') {
            $levelIndex[$normalized] = true;
        }
    }

    $rows = [];
    $files = glob($quizDir . '/*.json');
    if (!is_array($files)) {
        return [];
    }

    foreach ($files as $filePath) {
        $fileName = basename((string) $filePath);
        if (!preg_match('/^(\d+)\.json$/', $fileName, $matches)) {
            continue;
        }

        $id = (int) $matches[1];
        if ($id <= 0) {
            continue;
        }

        $raw = file_get_contents($filePath);
        if (!is_string($raw) || $raw === '') {
            continue;
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            continue;
        }

        $contents = isset($payload['contents']) && is_array($payload['contents']) ? $payload['contents'] : [];
        $quiz = isset($payload['quiz']) && is_array($payload['quiz']) ? $payload['quiz'] : [];

        $answersDir = dirname($quizDir) . '/quiz_answers';
        $answerPath = findQuizAnswersPath($id, $answersDir);
        if ($answerPath === false) {
            continue;
        }

        $level = normalizeSchoolLevel((string) ($quiz['level'] ?? $contents['level'] ?? ''));
        if ($level === '' || !isset($levelIndex[$level])) {
            continue;
        }

        $subject = trim((string) ($quiz['subject'] ?? $contents['subject'] ?? ''));
        if ($subjectFilterNormalized !== '' && normalizeTextForSignature($subject) !== $subjectFilterNormalized) {
            continue;
        }

        $title = trim((string) ($contents['title'] ?? $quiz['title'] ?? ('Quiz #' . $id)));
        $description = trim((string) ($contents['description'] ?? ''));

        $rows[] = [
            'id' => $id,
            'title' => $title,
            'level' => $level,
            'subject' => $subject,
            'description' => $description,
        ];
    }

    usort($rows, static function (array $a, array $b): int {
        return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
    });

    return $rows;
}

function filterRowsWithExistingQuizFile(array $rows, string $quizDir): array
{
    if ($quizDir === '' || !is_dir($quizDir)) {
        return [];
    }

    $filtered = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $id = isset($row['id']) ? (int) $row['id'] : 0;
        if ($id <= 0) {
            continue;
        }

        $quizPath = $quizDir . '/' . $id . '.json';
        if (!is_file($quizPath)) {
            continue;
        }

        $filtered[] = $row;
    }

    return $filtered;
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
