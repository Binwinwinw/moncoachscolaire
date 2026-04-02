<?php
require_once __DIR__ . '/src/database/connection.php';

echo "Vérification des cours 406-435\n";
echo str_repeat("=", 50) . "\n";

$stmt = $pdo->query('SELECT id, subject, level, competence FROM courses WHERE id >= 406 AND id <= 435 ORDER BY id');
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($courses as $c) {
    echo sprintf(
        "Cours #%d: %s | %s | %s\n",
        $c['id'],
        $c['subject'],
        $c['level'] ?? 'NULL',
        substr($c['competence'], 0, 40)
    );
}
