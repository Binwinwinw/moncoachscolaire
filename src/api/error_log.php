<?php

echo "<pre>";
echo "POST: " . json_encode($_POST) . "\n";
echo "GET: " . json_encode($_GET) . "\n";
echo "SESSION: " . json_encode($_SESSION ?? []) . "\n";
echo "PDO: " . (isset($pdo) ? 'OK' : 'NULL') . "\n";
var_dump($pdo ?? 'NO PDO');
echo "</pre>";
