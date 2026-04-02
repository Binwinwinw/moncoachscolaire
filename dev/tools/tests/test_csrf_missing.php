<?php
/**
 * Test CSRF manquant (doit renvoyer 403)
 * Usage: php dev/tools/tests/test_csrf_missing.php https://example.com
 */

$base = $argv[1] ?? 'http://localhost/moncoachscolaire/public';
$endpoint = rtrim($base, '/') . '/index.php?page=api/exercices/save-progress';

$payload = json_encode([
    'exerciseId' => 1,
    'score' => 100,
    'correct' => true
]);

$headers = ['Content-Type: application/json'];
$sessionId = getenv('MCS_SESSION_ID');
if ($sessionId) {
    $headers[] = 'Cookie: PHPSESSID=' . $sessionId;
}

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => $payload
]);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $code\n";
echo $response . "\n";

if ($sessionId) {
    if ($code !== 403) {
        exit(1);
    }
} else {
    if (!in_array($code, [401, 403], true)) {
        exit(1);
    }
}
