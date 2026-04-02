<?php
require_once __DIR__ . '/../db/connection.php';

$ids_to_restore = [
    604 => 'Email simple : 1) From/To 2) Subject 3) Hello 4) I am ... 5) I live in ... 6) I write to tell you about ... 7) Closing / Best regards',
    692 => 'Email simple : 1) From/To 2) Subject 3) Hello 4) My name is ... 5) I am ... years old 6) I live in ... 7) Thank you / Best regards',
    676 => 'École et loisirs : 1) I have maths lessons on Monday 2) I love reading books 3) I play football after school 4) I like music',
    652 => 'Daily routine: I wake up at 7:00, I have breakfast, I go to school, I do my homework, and I go to bed at 10:00.',
];

$stmt = $pdo->prepare('UPDATE exercises SET Answer = ? WHERE Id = ?');
foreach ($ids_to_restore as $id => $ans) {
    $stmt->execute([$ans, $id]);
    echo "Restored #$id\n";
}
echo "\nDone.\n";
