<?php
$ids = [433,434,442,443,102,122,479,485,490,491,496,150,136];
require __DIR__ . '/../db/connection.php';
foreach ($ids as $id) {
    $stmt = $pdo->query("SELECT Id, Title, Subject, Level, Content, Answer FROM Exercises WHERE Id=" . (int)$id);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "---$id--- introuvable\n";
        continue;
    }
    echo "---{$row['Id']}---\n";
    echo $row['Title'] . " [{$row['Level']}/{$row['Subject']}]\n";
    echo "Q: " . substr(strip_tags($row['Content']), 0, 200) . "\n";
    echo "A: " . substr(strip_tags($row['Answer']), 0, 200) . "\n\n";
}
