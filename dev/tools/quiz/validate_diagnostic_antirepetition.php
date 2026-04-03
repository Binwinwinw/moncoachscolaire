<?php

declare(strict_types=1);

/**
 * Simulation CLI de l'ordre anti-repetition des quiz diagnostics.
 *
 * OBJECTIF: Comprendre le comportement anti-repetition pour un niveau/sujet.
 * **NOTE [02/04/2026]**: L'ancien objectif "50 tentatives sans répétition" a été abandonné.
 * Cet outil simule plutôt le comportement réel du pool (qui peut être petit).
 * Utilité: vérifier l'ordre tri, identifier les petits pools, décider enrichissement.
 *
 * Usage:
 *   php dev/tools/quiz/validate_diagnostic_antirepetition.php [level] [subject] [attempts]
 *
 * Exemple:
 *   php dev/tools/quiz/validate_diagnostic_antirepetition.php 4eme "Mathématiques" 50
 *   php dev/tools/quiz/validate_diagnostic_antirepetition.php 4eme "Anglais" 50
 */

$projectRoot = dirname(__DIR__, 3);
$quizDir = $projectRoot . '/src/data/quiz';

$levelArg = isset($argv[1]) ? (string) $argv[1] : '4eme';
$subjectArg = isset($argv[2]) ? (string) $argv[2] : 'Mathématiques';
$attempts = isset($argv[3]) && is_numeric($argv[3]) ? max(1, (int) $argv[3]) : 50;

$levelCandidates = buildLevelCandidates($levelArg);
$catalog = loadQuizCatalogFromJson($quizDir, $levelCandidates, $subjectArg);

if (count($catalog) === 0) {
    fwrite(STDERR, "Aucun quiz trouve pour ce niveau/matiere.\n");
    exit(1);
}

$historyStats = [];
$selected = [];
$firstRepeatAt = null;
$seenIds = [];

for ($attempt = 1; $attempt <= $attempts; $attempt++) {
    $ordered = $catalog;
    usort($ordered, static function (array $a, array $b) use ($historyStats): int {
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

        return ((int) ($b['id'] ?? 0)) <=> ((int) ($a['id'] ?? 0));
    });

    $picked = $ordered[0];
    $pickedId = (int) ($picked['id'] ?? 0);
    $selected[] = [
        'attempt' => $attempt,
        'id' => $pickedId,
        'title' => (string) ($picked['title'] ?? ''),
        'subject' => (string) ($picked['subject'] ?? ''),
        'level' => (string) ($picked['level'] ?? ''),
    ];

    if ($firstRepeatAt === null && isset($seenIds[$pickedId])) {
        $firstRepeatAt = $attempt;
    }
    $seenIds[$pickedId] = ($seenIds[$pickedId] ?? 0) + 1;

    $signature = buildQuizSignature(
        (string) ($picked['title'] ?? ''),
        (string) ($picked['subject'] ?? ''),
        (string) ($picked['level'] ?? ''),
    );

    if ($signature !== '') {
        if (!isset($historyStats[$signature])) {
            $historyStats[$signature] = [
                'attempts' => 0,
                'last_seen_at' => '',
            ];
        }

        $historyStats[$signature]['attempts']++;
        $historyStats[$signature]['last_seen_at'] = sprintf('attempt-%04d', $attempt);
    }
}

$uniqueCount = count($seenIds);
$repeatCount = $attempts - $uniqueCount;

echo '=== Simulation Anti-Répétition Diagnostic ===' . PHP_EOL;
echo '[02/04/2026] NOTE: Ancien objectif "50 sans répétition" ABANDONNÉ.' . PHP_EOL;
echo 'Cet outil mesure le comportement réel du pool.' . PHP_EOL;
echo PHP_EOL;
echo 'Level: ' . ($levelCandidates[0] ?? $levelArg) . PHP_EOL;
echo 'Subject: ' . $subjectArg . PHP_EOL;
echo 'Pool size: ' . count($catalog) . PHP_EOL;
echo 'Attempts simulated: ' . $attempts . PHP_EOL;
echo 'Unique quiz served: ' . $uniqueCount . PHP_EOL;
echo 'First repeat at attempt: ' . ($firstRepeatAt ?? 'none') . PHP_EOL;
echo 'Total repeats: ' . $repeatCount . PHP_EOL;
echo PHP_EOL;
echo 'First 15 picks:' . PHP_EOL;

foreach (array_slice($selected, 0, 15) as $row) {
    echo '- #' . $row['attempt'] . ' -> quiz ' . $row['id'] . ' | ' . $row['title'] . PHP_EOL;
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

    return array_values(array_unique(array_filter($candidates, static fn($value) => $value !== '')));
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
    $stopWords = ['quiz', 'diagnostic', 'diag', 'test', 'evaluation', 'de', 'du', 'des', 'la', 'le', 'les', 'en', 'pour'];
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

function getQuizHistoryStats(array $row, array $historyStats): array
{
    $signature = buildQuizSignature(
        (string) ($row['title'] ?? ''),
        (string) ($row['subject'] ?? ''),
        (string) ($row['level'] ?? ''),
    );

    if ($signature === '' || !isset($historyStats[$signature])) {
        return ['attempts' => 0, 'last_seen_at' => ''];
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

        $level = normalizeSchoolLevel((string) ($quiz['level'] ?? $contents['level'] ?? ''));
        if ($level === '' || !isset($levelIndex[$level])) {
            continue;
        }

        $subject = trim((string) ($quiz['subject'] ?? $contents['subject'] ?? ''));
        if ($subjectFilterNormalized !== '' && normalizeTextForSignature($subject) !== $subjectFilterNormalized) {
            continue;
        }

        $rows[] = [
            'id' => $id,
            'title' => trim((string) ($contents['title'] ?? $quiz['title'] ?? ('Quiz #' . $id))),
            'level' => $level,
            'subject' => $subject,
        ];
    }

    return $rows;
}
