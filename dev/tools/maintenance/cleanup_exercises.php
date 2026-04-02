<?php
/**
 * Nettoyage des exercices
 */
require_once __DIR__ . '/../../../db/connection.php';

echo "=== NETTOYAGE ===\n\n";

// 1. Supprimer les doublons
echo "1. Suppression des doublons...\n";

$stmt = $pdo->query(
    "SELECT Id FROM (
        SELECT Id, ROW_NUMBER() OVER (PARTITION BY Level, Subject, Title ORDER BY Id) as rn
        FROM exercises
    ) as t
    WHERE rn > 1"
);

$dups = $stmt->fetchAll();
if (!empty($dups)) {
    $ids = array_column($dups, 'Id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("DELETE FROM exercises WHERE Id IN ($placeholders)");
    $stmt->execute($ids);

    echo "   ✓ {$stmt->rowCount()} doublons supprimés\n";
} else {
    echo "   ✓ Aucun doublon\n";
}

// 2. Wrapper le contenu sans HTML
echo "\n2. Wrappings des contenus non-HTML...\n";

$stmt = $pdo->query(
    "SELECT Id, Content FROM exercises
     WHERE Content NOT LIKE '%<p>%' AND Content NOT LIKE '%<li>%'
     LIMIT 250"
);

$to_wrap = $stmt->fetchAll();
if (!empty($to_wrap)) {
    $update = $pdo->prepare("UPDATE exercises SET Content = ? WHERE Id = ?");

    foreach ($to_wrap as $row) {
        $wrapped = '<p>' . htmlspecialchars($row['Content'], ENT_QUOTES, 'UTF-8') . '</p>';
        $update->execute([$wrapped, $row['Id']]);
    }

    echo "   ✓ {$stmt->rowCount()} contenus wrappés\n";
} else {
    echo "   ✓ Tous les contenus sont formatés\n";
}

// 3. Statistiques finales
echo "\n=== STATISTIQUES FINALES ===\n";
$total = $pdo->query("SELECT COUNT(*) as cnt FROM exercises")->fetch();
echo "Total exercices: {$total['cnt']}\n";

$by_level = $pdo->query(
    "SELECT Level, COUNT(*) as cnt FROM exercises
     GROUP BY Level ORDER BY FIELD(Level, '6ème', '5ème', '4ème', '3ème')"
);
echo "\nPar niveau:\n";
foreach ($by_level as $r) {
    echo "  {$r['Level']}: {$r['cnt']}\n";
}

echo "\n✓ Nettoyage terminé\n";
