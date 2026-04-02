<?php
// Smoke test: Subject filtering on college exercises page via ?subject= should return exercises with that subject
// The test file is located in dev/tools/tests, so point to the project src/database/connection.php (three levels up)
require_once __DIR__ . '/../../../src/database/connection.php';

// Get one level and one subject available
$stmt = $pdo->query("SELECT Level, Subject FROM Exercises WHERE Subject IS NOT NULL AND TRIM(Subject) <> '' LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo "SKIP: no subject data in DB to test subject filtering\n";
    exit(0);
}
$level = $row['Level'];
$subject = $row['Subject'];

// Use college page (shows multiple levels) to test subject filtering
$url = 'http://localhost/moncoachscolaire/public/index.php?page=college/exercices-college&subject=' . rawurlencode($subject);
$context = stream_context_create(['http' => ['timeout' => 5]]);
$html = @file_get_contents($url, false, $context);
if ($html === false) {
    echo "FAIL: could not fetch $url\n";
    exit(1);
}

// Ensure at least one exercise card contains the subject label
if (!preg_match('/<article[^>]+class="[^"]*exercise-card[^"]*"[\s\S]*?' . preg_quote($subject, '/') . '/i', $html)) {
    echo "FAIL: no exercise card contains the subject '$subject'\n";
    exit(1);
}

echo "PASS: subject filter returns exercises for subject '$subject'\n";
exit(0);
