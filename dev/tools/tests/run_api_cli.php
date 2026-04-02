<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

action:
$action = $argv[1] ?? 'exercises';
$level = $argv[2] ?? '6ème';
$subject = $argv[3] ?? 'Mathématiques';

$_GET['action'] = $action;
$_GET['level'] = $level;
if ($subject) $_GET['subject'] = $subject;
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
include __DIR__ . '/../src/api/get_exercises.php';
$out = ob_get_clean();

echo "Action=$action Level=$level Subject=$subject\n";
echo $out, "\n";
