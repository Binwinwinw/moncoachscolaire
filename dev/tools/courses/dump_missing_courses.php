<?php
// dev/tools/courses/dump_missing_courses.php

$rootDir = dirname(dirname(dirname(__DIR__)));
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

// On exclut ceux qui ont déjà une explication (ex: Maths 3eme qu'on vient de faire)
$sql = "SELECT id, subject, level, competence
        FROM courses
        WHERE is_active = 1
        AND (explanation IS NULL OR explanation = '')
        ORDER BY subject, level";

$stmt = $pdo->query($sql);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$json = json_encode($courses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents(__DIR__ . '/missing_courses.json', $json);

echo "Exporté " . count($courses) . " cours manquants dans missing_courses.json\n";
