<?php
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../db/connection.php';

if (!isset($pdo) || !$pdo) {
    echo "[ERR] PDO non disponible\n";
    exit(1);
}

$level = $argv[1] ?? '6ème';
$subject = $argv[2] ?? null;

$levelVariants = [$level];
$levelVariants[] = str_replace('ème','eme',$level);
$levelVariants[] = '6ème';
$levelVariants[] = '6eme';
$levelVariants = array_values(array_unique(array_filter($levelVariants)));

$placeholders = implode(',', array_fill(0, count($levelVariants), '?'));
$sql = "SELECT Level, Subject, COUNT(*) as cnt,
        SUM(CASE WHEN Answer IS NOT NULL AND TRIM(Answer)!='' THEN 1 ELSE 0 END) as with_answer,
        SUM(CASE WHEN LENGTH(Answer) >= 30 THEN 1 ELSE 0 END) as answer_30
        FROM Exercises WHERE Level IN ($placeholders)";
$params = $levelVariants;

if ($subject) {
    $sql .= " AND Subject = ?";
    $params[] = $subject;
}
$sql .= " GROUP BY Level, Subject ORDER BY Subject";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

echo "Level debug for: $level" . ($subject ? " | Subject: $subject" : "") . "\n\n";
if (!$rows) {
    echo "No rows found.\n";
    exit(0);
}
foreach ($rows as $r) {
    printf("%-12s | %-25s | total=%3d | with_answer=%3d | answer>=30=%3d\n",
        $r['Level'], $r['Subject'], $r['cnt'], $r['with_answer'], $r['answer_30']);
}

// Montrer un échantillon pour vérifier Title/Content
$sample = $pdo->prepare("SELECT Id, Title, Subject, LENGTH(Content) as content_len, LENGTH(Answer) as answer_len FROM Exercises WHERE Level IN ($placeholders) ORDER BY Id LIMIT 3");
$sample->execute($levelVariants);
$items = $sample->fetchAll();
echo "\nSamples:\n";
foreach ($items as $it) {
    printf("#%d | %s | %s | content_len=%d | answer_len=%d\n", $it['Id'], $it['Title'], $it['Subject'], $it['content_len'], $it['answer_len']);
}
?>
