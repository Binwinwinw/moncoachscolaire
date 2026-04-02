<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!$pdo) { echo "DB indisponible\n"; exit(2); }

$subjects = ['Mathématiques','Français'];
$level = '6ème';
foreach ($subjects as $s) {
    $count = countExercisesByLevel($level, $s);
    echo "{$level} / {$s} : {$count}\n";
}
