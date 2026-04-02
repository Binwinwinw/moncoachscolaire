<?php
require_once __DIR__ . '/../db/connection.php';
$updates = [
    604 => '1) Greeting (Hello/Hi) 2) Main message (who/why) 3) Closing (Best regards/Thank you)',
    692 => '1) Greeting (Hello/Hi) 2) Main message (who/why) 3) Closing (Best regards/Thank you)',
    589 => '1) Destination 2) Transport 3) Activities 4) Impressions',
    676 => '1) I have maths lessons on Monday 2) I love reading books 3) I play football after school',
    583 => '1) frayeur 2) angoisse 3) terreur 4) inquiétude 5) crainte 6) panique',
    671 => '1) frayeur 2) angoisse 3) terreur 4) inquiétude 5) crainte 6) panique',
    652 => "1) I wake up at 7 o'clock 2) I have breakfast 3) I go to school 4) I do my homework",
    636 => '1) Tracer la base donnée 2) Reporter les longueurs des côtés 3) Relier les points pour former le triangle',
    625 => '1) red (rouge) 2) blue (bleu) 3) green (vert) 4) yellow (jaune) 5) black (noir)',
    613 => 'Identifier le verbe conjugué et le sujet pour chaque phrase (ex: Les élèves arrivent -> verbe: arrivent, sujet: Les élèves)',
];

$stmt = $pdo->prepare('UPDATE exercises SET Answer = ? WHERE Id = ?');
$total=0;
foreach($updates as $id=>$ans){
    $stmt->execute([$ans,$id]);
    $total += $stmt->rowCount();
    echo "Updated $id\n";
}

echo "Total updated: $total\n";
