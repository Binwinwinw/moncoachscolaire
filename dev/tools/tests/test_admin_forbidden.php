<?php
/**
 * Test accès admin interdit (doit renvoyer 403)
 * Usage: php dev/tools/tests/test_admin_forbidden.php https://example.com
 */

$base = $argv[1] ?? 'http://localhost/moncoachscolaire/public';
$endpoint = rtrim($base, '/') . '/index.php?page=api/admin/stats';

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

if ($code !== 403) {
    exit(1);
}
