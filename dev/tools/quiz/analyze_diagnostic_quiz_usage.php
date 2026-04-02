<?php

declare(strict_types=1);

/**
 * Diagnostic quiz usage analytics.
 *
 * Usage:
 *   php dev/tools/quiz/analyze_diagnostic_quiz_usage.php [limit]
 *
 * Example:
 *   php dev/tools/quiz/analyze_diagnostic_quiz_usage.php 50
 */

$projectRoot = dirname(__DIR__, 3);
$configPath = $projectRoot . '/src/config/config.php';
$connectionPath = $projectRoot . '/src/database/connection.php';

if (is_file($configPath)) {
    require_once $configPath;
}
if (is_file($connectionPath)) {
    require_once $connectionPath;
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    fwrite(STDERR, "ERROR: PDO connection unavailable. Check src/config/config.php and src/database/connection.php.\n");
    exit(1);
}

$limit = 50;
if (isset($argv[1]) && is_numeric($argv[1])) {
    $limit = max(1, (int) $argv[1]);
}

$query = "
    SELECT
        qr.quiz_id,
        COUNT(*) AS attempts,
        COUNT(DISTINCT qr.user_id) AS unique_users,
        ROUND(AVG(qr.score), 2) AS avg_score,
        MAX(qr.created_at) AS last_used_at,
        COALESCE(q.level, '') AS level,
        COALESCE(q.subject, '') AS subject,
        COALESCE(q.title, '') AS title,
        COALESCE(q.type, '') AS quiz_type
    FROM quizresult qr
    LEFT JOIN quiz q ON q.id = qr.quiz_id
    WHERE (q.type = 'diagnostic' OR q.id IS NULL)
    GROUP BY qr.quiz_id, q.level, q.subject, q.title, q.type
    ORDER BY attempts DESC, unique_users DESC, qr.quiz_id ASC
    LIMIT :limit
";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    fwrite(STDOUT, "No diagnostic usage found in quizresult.\n");
    exit(0);
}

$totalAttempts = 0;
$quizIds = [];
foreach ($rows as $row) {
    $totalAttempts += (int) $row['attempts'];
    $quizIds[] = (int) $row['quiz_id'];
}

$reportsDir = $projectRoot . '/dev/reports';
if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0777, true);
}

$timestamp = date('Y-m-d H:i:s');
$markdownPath = $reportsDir . '/diagnostic_quiz_usage_top' . $limit . '.md';
$jsonPath = $reportsDir . '/diagnostic_quiz_usage_top' . $limit . '.json';

$md = [];
$md[] = '# Top ' . $limit . ' quiz diagnostics les plus utilises';
$md[] = '';
$md[] = '- Genere le: ' . $timestamp;
$md[] = '- Source: table `quizresult` (join `quiz`)';
$md[] = '- Nombre de quiz classes: ' . count($rows);
$md[] = '- Total tentatives (sur ce top): ' . $totalAttempts;
$md[] = '';
$md[] = '| Rang | Quiz ID | Tentatives | Utilisateurs uniques | Score moyen | Derniere tentative | Niveau | Matiere | Titre |';
$md[] = '|------|---------|------------|-----------------------|-------------|--------------------|--------|---------|-------|';

$rank = 1;
foreach ($rows as $row) {
    $level = trim((string) $row['level']) !== '' ? (string) $row['level'] : '-';
    $subject = trim((string) $row['subject']) !== '' ? (string) $row['subject'] : '-';
    $title = trim((string) $row['title']) !== '' ? (string) $row['title'] : '-';

    $md[] = sprintf(
        '| %d | %d | %d | %d | %s | %s | %s | %s | %s |',
        $rank,
        (int) $row['quiz_id'],
        (int) $row['attempts'],
        (int) $row['unique_users'],
        (string) ($row['avg_score'] ?? '0.00'),
        (string) ($row['last_used_at'] ?? '-'),
        str_replace('|', '/', $level),
        str_replace('|', '/', $subject),
        str_replace('|', '/', $title)
    );

    $rank++;
}

file_put_contents($markdownPath, implode("\n", $md) . "\n");
file_put_contents($jsonPath, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

fwrite(STDOUT, 'Top usage generated successfully.' . PHP_EOL);
fwrite(STDOUT, 'Top quiz IDs: ' . implode(', ', $quizIds) . PHP_EOL);
fwrite(STDOUT, 'Markdown report: ' . $markdownPath . PHP_EOL);
fwrite(STDOUT, 'JSON report: ' . $jsonPath . PHP_EOL);
