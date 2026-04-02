<?php
require_once __DIR__ . '/../db/connection.php';

echo "=== Vérification des exercices BAC migrés ===" . PHP_EOL . PHP_EOL;

// Exercices migrés récemment (avec "(BAC)" dans le titre)
$stmt = $pdo->query('SELECT Subject, Title FROM exercises WHERE Level="BAC" AND Title LIKE "%(BAC)%" ORDER BY Subject, Title');
echo "Exercices migrés (contiennent \"(BAC)\" dans le titre):" . PHP_EOL;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("  [%-20s] %s\n", $row['Subject'], $row['Title']);
}

echo PHP_EOL . "=== Incohérences détectées ===" . PHP_EOL;

// Détecter les incohérences (titre ne correspond pas à la matière)
$incoherences = [
    ['subject' => 'Anglais', 'keywords' => ['chimie', 'organique', 'probabilités', 'math', 'géométrie', 'nombres']],
    ['subject' => 'Français', 'keywords' => ['probabilités', 'math', 'chimie', 'physique', 'nombres']],
    ['subject' => 'Philosophie', 'keywords' => ['math', 'nombres', 'complexes', 'chimie', 'physique']],
    ['subject' => 'Mathématiques', 'keywords' => ['littérature', 'commentaire', 'dissertation', 'essay']],
    ['subject' => 'Sciences', 'keywords' => ['essay', 'littérature', 'commentaire']],
];

$problems = [];
foreach ($incoherences as $check) {
    $subject = $check['subject'];
    foreach ($check['keywords'] as $keyword) {
        $stmt = $pdo->prepare('SELECT Id, Title FROM exercises WHERE Level="BAC" AND Subject=? AND (Title LIKE ? OR Content LIKE ?)');
        $likePattern = '%' . $keyword . '%';
        $stmt->execute([$subject, $likePattern, $likePattern]);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $problems[] = sprintf("  ⚠️  [%s] #%d - %s (contient: %s)", $subject, $row['Id'], $row['Title'], $keyword);
        }
    }
}

if (!empty($problems)) {
    echo "Exercices potentiellement mal catégorisés:" . PHP_EOL;
    foreach (array_unique($problems) as $problem) {
        echo $problem . PHP_EOL;
    }
} else {
    echo "  ✅ Aucune incohérence détectée" . PHP_EOL;
}

echo PHP_EOL . "=== Résumé par matière ===" . PHP_EOL;
$stmt = $pdo->query('SELECT Subject, COUNT(*) as nb FROM exercises WHERE Level="BAC" GROUP BY Subject ORDER BY Subject');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("  %-25s: %2d exercices\n", $row['Subject'], $row['nb']);
}
