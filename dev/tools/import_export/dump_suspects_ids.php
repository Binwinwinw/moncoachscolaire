<?php
require_once __DIR__ . '/../db/connection.php';
$ids = [604,692,688,589,676,677,583,584,671,672,664,665,652,655,656,648,634,635,636,637,638,639,624,625,628,629,630,613,616,610];
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT Id, Level, Subject, Title, Answer FROM exercises WHERE Id IN ($placeholders) ORDER BY Level, Subject, Id");
$stmt->execute($ids);
foreach ($stmt as $r) {
    echo "[{$r['Level']}][{$r['Subject']}] #{$r['Id']} {$r['Title']} => {$r['Answer']}\n";
}
