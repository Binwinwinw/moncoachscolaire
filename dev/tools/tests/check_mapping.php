<?php
// Script de vérification du mapping
require_once __DIR__ . '/../src/database/connection.php';

$stmt = $pdo->query("
    SELECT 
        COUNT(DISTINCT e.Id) as total_exercises,
        COUNT(DISTINCT en.exercise_id) as with_notion,
        ROUND(COUNT(DISTINCT en.exercise_id) * 100 / COUNT(DISTINCT e.Id), 1) as percentage
    FROM Exercises e
    LEFT JOIN ExerciseNotion en ON e.Id = en.exercise_id
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Résumé du mapping:\n";
echo "==================\n";
echo "Total exercices: {$stats['total_exercises']}\n";
echo "Avec notion: {$stats['with_notion']}\n";
echo "Couverture: {$stats['percentage']}%\n";

// Notions les plus utilisées
echo "\nNotions les plus populaires:\n";
$stmt = $pdo->query("
    SELECT n.name, n.subject, n.level, COUNT(en.exercise_id) as count
    FROM Notion n
    LEFT JOIN ExerciseNotion en ON n.id = en.notion_id
    WHERE en.exercise_id IS NOT NULL
    GROUP BY n.id
    ORDER BY count DESC
    LIMIT 15
");
foreach ($stmt->fetchAll() as $row) {
    echo "  {$row['name']} ({$row['subject']}/{$row['level']}): {$row['count']}\n";
}
?>
