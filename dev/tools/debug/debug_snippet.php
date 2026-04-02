<?php
$path = $argv[1] ?? __DIR__ . '/../pages/college/college.php';
$s = @file_get_contents($path, false, null, 0, 8192) ?: '';
echo "--- SNIPPET START ---\n";
echo substr($s,0,400) . "\n";
echo "--- SNIPPET END ---\n";
echo "contains header.php? " . (preg_match('/header\.php/', $s) ? 'yes' : 'no') . "\n";
echo "contains footer.php? " . (preg_match('/footer\.php/', $s) ? 'yes' : 'no') . "\n";
