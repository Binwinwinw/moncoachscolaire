<?php
require_once __DIR__ . '/../db/connection.php';
$id = 613;
$s = $pdo->prepare('SELECT Id, Title, Content, Answer FROM exercises WHERE Id = ?');
$s->execute([$id]);
$r = $s->fetch();
if ($r) {
    echo "ID: {$r['Id']}\n";
    echo "Title: {$r['Title']}\n";
    echo "Content: {$r['Content']}\n";
    echo "\nAnswer:\n{$r['Answer']}\n";
}
