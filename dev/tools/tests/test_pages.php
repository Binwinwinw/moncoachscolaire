<?php
// se/test/test_pages.php
// Simple URL checks, run from CLI: php se/test/test_pages.php

$base = 'http://localhost/moncoachscolaire';
$pages = [
    '/',
    '/index.php',
    '/college/index.php',
    '/lycee/index.php',
    '/bac/index.php',
    '/login.php',
    '/register.php'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$okCount = 0;
foreach ($pages as $p) {
    $url = rtrim($base, '/') . $p;
    curl_setopt($ch, CURLOPT_URL, $url);
    $res = curl_exec($ch);
    if ($res === false) {
        echo "FAIL: $url — cURL error: " . curl_error($ch) . PHP_EOL;
        continue;
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($code >= 200 && $code < 400) {
        echo "OK:   $url — HTTP $code" . PHP_EOL;
        $okCount++;
    } else {
        echo "FAIL: $url — HTTP $code" . PHP_EOL;
    }
}

curl_close($ch);

echo PHP_EOL . "Summary: $okCount/" . count($pages) . " pages OK" . PHP_EOL;
