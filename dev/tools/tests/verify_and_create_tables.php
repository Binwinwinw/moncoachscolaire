<?php
require_once __DIR__ . '/../config.php';

$courseTables = ['Courses', 'ExerciseCourseLinks', 'UserCourseProgress', 'CourseViewEvents', 'CourseComments'];

echo "Vérification des tables Courses:\n";
foreach ($courseTables as $table) {
    $exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    echo ($exists ? "✓" : "✗") . " $table\n";
}

// Si aucune table, les créer
$coursesExists = $pdo->query("SHOW TABLES LIKE 'Courses'")->fetch();
if (!$coursesExists) {
    echo "\nCréation des tables...\n";
    $sql = file_get_contents(__DIR__ . '/../db/migration_courses_mysql.sql');
    
    // Exécuter chaque statement
    $statements = explode(';', $sql);
    $count = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt) && strpos($stmt, '--') !== 0 && strpos($stmt, '/*') !== 0) {
            try {
                $pdo->exec($stmt);
                $count++;
                echo ".";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "\nErreur: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    echo "\n$count commandes exécutées.\n";
    
    // Revérifier
    echo "\nVérification après création:\n";
    foreach ($courseTables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
        echo ($exists ? "✓" : "✗") . " $table\n";
    }
}
