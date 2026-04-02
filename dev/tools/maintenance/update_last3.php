<?php
require_once __DIR__ . '/../db/connection.php';
$updates = [
    604 => 'Email simple : 1) From/To 2) Subject 3) Hello 4) I am ... 5) I live in ... 6) I write to tell you about ... 7) Closing / Best regards',
    692 => 'Email simple : 1) From/To 2) Subject 3) Hello 4) My name is ... 5) I am ... years old 6) I live in ... 7) Thank you / Best regards',
    676 => 'École et loisirs : 1) I have maths lessons on Monday 2) I love reading books 3) I play football after school 4) I like music',
];

$stmt = $pdo->prepare('UPDATE exercises SET Answer = ? WHERE Id = ?');
foreach ($updates as $id => $ans) {
    $stmt->execute([$ans, $id]);
    echo "Updated $id\n";
}
