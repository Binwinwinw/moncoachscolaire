<?php
require_once __DIR__ . '/../db/connection.php';
$updates = [
    604 => 'Example email: Hello, my name is Anna. I am 13 years old and I live in Lyon. I am writing to tell you about my school. Best regards.',
    692 => 'Example email: Hello, my name is Alex. I am 14 years old and I live in Paris. I am writing to give you some news. Best regards.',
    676 => 'School: I have maths on Monday and science on Tuesday. Hobbies: I love reading books and playing football after school.',
    652 => 'Daily routine: I wake up at 7:00, I have breakfast, I go to school, I do my homework, and I go to bed at 10:00.',
    625 => 'Colours: red (rouge), blue (bleu), green (vert), yellow (jaune), black (noir).',
];

$stmt = $pdo->prepare('UPDATE exercises SET Answer = ? WHERE Id = ?');
foreach ($updates as $id => $ans) {
    $stmt->execute([$ans, $id]);
    echo "Updated $id\n";
}
