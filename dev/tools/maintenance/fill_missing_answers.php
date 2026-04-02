<?php
require_once __DIR__ . '/../db/connection.php';

$rows = [
    604 => 'Réponse libre : rédiger un email simple (salutation, présentation, objet, conclusion)',
    600 => 'Production écrite libre : rédiger un récit court (situation, événements, fin)',
    589 => 'Production écrite libre : présenter un voyage (destination, transport, activités, impressions)',
];

$stmt = $pdo->prepare('UPDATE exercises SET Answer = ? WHERE Id = ?');
$total = 0;
foreach ($rows as $id => $ans) {
    $stmt->execute([$ans, $id]);
    $total += $stmt->rowCount();
    echo "Updated $id\n";
}

echo "Total updated: $total\n";
