<?php
// Test wrapper for validate_exercises_json.php
$script = __DIR__ . '/../../import_export/validate_exercises_json.php';
if (!is_file($script)) { echo "SKIP: validator not found\n"; exit(0); }
$cmd = "php " . escapeshellarg($script);
exec($cmd, $out, $ret);
foreach ($out as $line) echo $line . "\n";
exit($ret);
