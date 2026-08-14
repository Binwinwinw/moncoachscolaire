<?php
require_once __DIR__ . '/../db/connection.php';
$id = 625;
$stmt = $pdo->prepare('SELECT Id, Level, Subject, Title, Answer FROM exercises WHERE Id = ?');
$stmt->execute([$id]);
$stmt->fetch(PDO::FETCH_ASSOC);
