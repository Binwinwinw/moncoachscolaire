#!/usr/bin/env php
<?php
/**
 * NETTOYAGE EXERCICES VIDES OU PROBLÉMATIQUES
 */

require_once __DIR__ . '/../config.php';

echo "🧹 NETTOYAGE EXERCICES PROBLÉMATIQUES\n";
echo "============================================================\n\n";

// 1. Supprimer les exercices avec content OU answer vide
$stmt = $pdo->prepare("
    DELETE FROM Exercises
    WHERE (Content IS NULL OR Content = '' OR TRIM(Content) = '')
       OR (Answer IS NULL OR Answer = '' OR TRIM(Answer) = '')
");

$result = $stmt->execute();
$deleted = $stmt->rowCount();

echo "🗑️  Exercices supprimés (content ou answer vide) : $deleted\n\n";

// 2. Lister les exercices restants problématiques
echo "🔍 RECHERCHE D'AUTRES PROBLÈMES...\n\n";

$stmt = $pdo->query("
    SELECT Id, Level, Subject, Title, 
           LENGTH(Content) as content_length,
           LENGTH(Answer) as answer_length
    FROM Exercises
    WHERE is_active = 1
      AND (LENGTH(Answer) < 15 OR Content LIKE '%a)%b)%c)%d)%')
    ORDER BY Subject, Level
    LIMIT 30
");

$problematic = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $problematic[] = $row;
}

if (count($problematic) > 0) {
    echo "⚠️  " . count($problematic) . " exercices avec réponses très courtes:\n\n";
    
    foreach ($problematic as $ex) {
        echo "ID {$ex['Id']} - {$ex['Subject']} - {$ex['Level']}\n";
        echo "   Titre: {$ex['Title']}\n";
        echo "   Longueur réponse: {$ex['answer_length']} caractères\n\n";
    }
    
    echo "💡 Ces exercices nécessitent une amélioration manuelle des réponses.\n\n";
}

// 3. Statistiques finales
echo "📊 STATISTIQUES APRÈS NETTOYAGE\n";
echo "============================================================\n\n";

$stmt = $pdo->query("
    SELECT 
        Subject,
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as actifs,
        AVG(LENGTH(Answer)) as avg_answer_length
    FROM Exercises
    GROUP BY Subject
    ORDER BY Subject
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $avg_len = round($row['avg_answer_length'], 0);
    echo str_pad($row['Subject'], 20) . " : ";
    echo str_pad($row['actifs'] . " actifs", 15);
    echo "(réponse moy: {$avg_len} car.)\n";
}

echo "\n✅ Nettoyage terminé!\n";
