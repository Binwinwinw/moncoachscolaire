<?php
// Emulate the routing environment
$requestUri = '/moncoachscolaire/public/index.php?page=api/exercices/manage';
$pageParam = 'api/exercices/manage';
$root = dirname(__DIR__);

echo "Testing Router Logic...\n";
echo "Page Param: '$pageParam'\n";

if (preg_match('#^/api/(.+)$#', parse_url($requestUri, PHP_URL_PATH), $matches)) {
    echo "Matched URI Regex\n";
} else {
    echo "Did NOT match URI Regex\n";
}

if (preg_match('#^api/(.+)$#', $pageParam, $matches)) {
    echo "Matched Page Regex. Capture: " . $matches[1] . "\n";
    $apiPath = $matches[1];
    $apiFile = $root . '/src/api/' . $apiPath . '.php';
    echo "Target File: $apiFile\n";
    echo "Realpath: " . realpath($apiFile) . "\n";
} else {
    echo "Did NOT match Page Regex\n";
}

if (strpos($pageParam, 'api/') === 0) {
    echo "Matched strpos check\n";
}
