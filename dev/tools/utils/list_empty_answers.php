<?php
require_once __DIR__ . '/../db/connection.php';

$stmt = $pdo->query("SELECT Id, Level, Subject, Title FROM exercises WHERE Answer IS NULL OR Answer = '' ORDER BY Level, Subject, Id LIMIT 200");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "[{$r['Level']}][{$r['Subject']}] #{$r['Id']} {$r['Title']}\n";
}
