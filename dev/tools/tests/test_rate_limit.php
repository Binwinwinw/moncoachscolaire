<?php
/**
 * Test rate-limit (doit renvoyer 429 après seuil)
 * Usage: php dev/tools/tests/test_rate_limit.php https://example.com
 */

$base = $argv[1] ?? 'http://localhost/moncoachscolaire/public';
$endpoint = rtrim($base, '/') . '/index.php?page=api/admin/stats';

$hits = 130;
$lastCode = null;
for ($i = 0; $i < $hits; $i++) {
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'GET'
    ]);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $lastCode = $code;
    echo "#" . ($i + 1) . " HTTP $code\n";
    if ($code === 429) {
        exit(0);
    }
}

// Si on n'a pas atteint 429
exit(1);
