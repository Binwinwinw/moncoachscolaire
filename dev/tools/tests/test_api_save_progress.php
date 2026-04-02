<?php
// tests/test_api_save_progress.php
// Usage: php tests/test_api_save_progress.php

$url = 'http://localhost/moncoachscolaire/api/save_progress.php';
$payload = json_encode(['exerciseId' => 9999, 'score' => 100, 'correct' => 1]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$res = curl_exec($ch);
if ($res === false) {
    echo "FAIL: cURL error: " . curl_error($ch) . PHP_EOL;
    exit(1);
}
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($code >= 200 && $code < 300) {
    echo "OK: API returned HTTP $code" . PHP_EOL;
    exit(0);
}

echo "FAIL: API returned HTTP $code" . PHP_EOL;
exit(1);
