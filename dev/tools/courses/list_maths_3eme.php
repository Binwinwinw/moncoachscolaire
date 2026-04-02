<?php
// dev/tools/courses/list_maths_3eme.php
$rootDir = dirname(dirname(dirname(__DIR__)));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

$stmt = $pdo->query("SELECT id, competence FROM courses WHERE subject='Mathématiques' AND level='3eme'");
foreach ($stmt as $row) {
    echo "#{$row['id']} : {$row['competence']}\n";
}
