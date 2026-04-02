<?php
// dev/tools/courses/list_course_subjects.php
$rootDir = dirname(dirname(dirname(__DIR__)));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

$stmt = $pdo->query("SELECT subject, level, COUNT(*) as c FROM courses GROUP BY subject, level ORDER BY subject, level");
foreach ($stmt as $row) {
    echo "{$row['subject']} - {$row['level']} : {$row['c']} cours\n";
}
