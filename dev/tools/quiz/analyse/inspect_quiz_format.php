<?php
require 'src/config/config.php';
require 'src/database/connection.php';

$stmt = $pdo->query('SELECT id, title, level, type, content_url FROM contents WHERE type="quiz" LIMIT 3');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== Sample Quiz Structure ===\n";
foreach ($rows as $r) {
    echo "\nID: " . $r['id'] . "\n";
    echo "Title: " . $r['title'] . "\n";
    echo "Level: " . $r['level'] . "\n";
    echo "Content URL (first 200 chars): " . substr($r['content_url'] ?? '', 0, 200) . "\n";
    echo "Content URL type: " . gettype($r['content_url']) . "\n";
}

// Check table structure
echo "\n=== Table Info ===\n";
$stmt = $pdo->query('SHOW COLUMNS FROM contents');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo $col['Field'] . " : " . $col['Type'] . "\n";
}
?>
