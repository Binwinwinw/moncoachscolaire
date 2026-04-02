<?php
require_once __DIR__ . '/../db/connection.php';
if (!$pdo) { echo "DB indisponible\n"; exit(2); }

$sql = "SELECT Level, Subject, COUNT(*) as total, SUM(CASE WHEN Answer IS NULL OR TRIM(Answer)='' THEN 1 ELSE 0 END) as empty FROM Exercises GROUP BY Level, Subject ORDER BY Level, Subject";
$stmt = $pdo->query($sql);
foreach ($stmt->fetchAll() as $row) {
    echo $row['Level'] . ' / ' . $row['Subject'] . ' : total=' . $row['total'] . ' ; sans réponse=' . $row['empty'] . "\n";
}
