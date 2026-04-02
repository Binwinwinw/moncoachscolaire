<?php
// Smoke test: GET /api/subjects.php?level=Seconde doit renvoyer un JSON avec 'subjects' array
$url = 'http://localhost/moncoachscolaire/public/index.php?page=api/subjects&level=Seconde';
$context = stream_context_create(['http' => ['timeout' => 5]]);
$json = @file_get_contents($url, false, $context);
if ($json === false) {
    echo "FAIL: could not fetch $url\n";
    exit(1);
}
$data = json_decode($json, true);
if (!is_array($data) || !isset($data['subjects']) || !is_array($data['subjects'])) {
    echo "FAIL: invalid response, expected {subjects: []}\n";
    exit(1);
}
echo "PASS: api subjects returned " . count($data['subjects']) . " subjects for Seconde\n";
exit(0);
