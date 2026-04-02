<?php
require_once __DIR__ . '/../db/connection.php';

$suspects = [610, 613, 616, 624, 625, 634, 635, 636, 637, 638, 639, 648, 664, 665, 671, 672, 688];

$stmt = $pdo->prepare('SELECT Id, Title, Content, Answer FROM exercises WHERE Id = ? LIMIT 1');
foreach ($suspects as $id) {
    $stmt->execute([$id]);
    $r = $stmt->fetch();
    if ($r) {
        echo "\n=== ID {$r['Id']} ===\n";
        echo "Title: {$r['Title']}\n";
        echo "Content: " . substr($r['Content'], 0, 100) . "...\n";
        echo "Answer: " . substr($r['Answer'], 0, 100) . "...\n";
    }
}
