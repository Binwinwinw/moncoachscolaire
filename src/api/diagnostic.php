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
$historyWindow = 20;

$levelCandidates = buildLevelCandidates($levelRaw);
$levelNormalized = $levelCandidates[0] ?? '6eme';

try {
    $quizDir = dirname(__DIR__) . '/data/quiz';
    $allQuiz = loadQuizCatalogFromJson($quizDir, $levelCandidates, $subject);

    $recentSignatures = [];
    if ($userId > 0 && isset($pdo) && $pdo instanceof PDO) {
        $historyStmt = $pdo->prepare(
            "SELECT q.title, q.subject, q.level
             FROM quizresult qr
             JOIN quiz q ON q.id = qr.quiz_id
             WHERE qr.user_id = ? AND q.type = 'diagnostic'
             ORDER BY qr.created_at DESC
             LIMIT {$historyWindow}",
        );
        $historyStmt->execute([$userId]);
        $historyRows = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($historyRows as $historyRow) {
            if (!is_array($historyRow)) {
                continue;
            }

            $historyLevel = normalizeSchoolLevel((string) ($historyRow['level'] ?? ''));
            if ($historyLevel !== '' && !in_array($historyLevel, $levelCandidates, true)) {
                continue;
            }

            $signature = buildQuizSignature(
                (string) ($historyRow['title'] ?? ''),
                (string) ($historyRow['subject'] ?? ''),
                (string) ($historyRow['level'] ?? ''),
            );
            if ($signature !== '') {
                $recentSignatures[$signature] = true;
            }
        }
    }

    usort($allQuiz, static function (array $a, array $b) use ($recentSignatures): int {
        $sigA = buildQuizSignature((string) ($a['title'] ?? ''), (string) ($a['subject'] ?? ''), (string) ($a['level'] ?? ''));
        $sigB = buildQuizSignature((string) ($b['title'] ?? ''), (string) ($b['subject'] ?? ''), (string) ($b['level'] ?? ''));

        $aSeen = $sigA !== '' && isset($recentSignatures[$sigA]);
        $bSeen = $sigB !== '' && isset($recentSignatures[$sigB]);

        if ($aSeen !== $bSeen) {
            return $aSeen ? 1 : -1;
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

    echo json_encode([
        'success' => true,
        'quiz' => $quiz,
        'count' => count($quiz),
        'total_pool' => count($allQuiz),
        'recommendation' => $recommendedQuiz,
        'history_window' => $historyWindow,
        'level' => $levelNormalized,
        'subject' => $subject,
    ], JSON_UNESCAPED_UNICODE);
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
