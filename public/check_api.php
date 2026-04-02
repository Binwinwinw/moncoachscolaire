<?php
$root = dirname(__DIR__); // As in public/index.php (if this script is in public/)
echo "Root: " . $root . "\n";
$apiPath = 'parent_family';
$apiFile = $root . '/src/api/' . $apiPath . '.php';
echo "API File: " . $apiFile . "\n";
echo "Exists: " . (file_exists($apiFile) ? 'YES' : 'NO') . "\n";
