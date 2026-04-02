<?php
/**
 * Test injection basique (doit renvoyer 4xx sans fuite SQL)
 * Usage: php dev/tools/tests/test_injection_rejected.php https://example.com
 */

$base = $argv[1] ?? 'http://localhost/moncoachscolaire/public';
$endpoint = rtrim($base, '/') . '/index.php?page=api/admin/security&type=alerts%27%20OR%201%3D1--';

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'GET'
]);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $code\n";
echo $response . "\n";

if ($code < 400 || $code >= 500) {
    exit(1);
}
