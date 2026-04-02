#!/usr/bin/env php
<?php
/**
 * RÉACTIVATION EXERCICES D'ANGLAIS + STATISTIQUES
 */

require_once __DIR__ . '/../config.php';

echo "🔄 RÉACTIVATION DES EXERCICES D'ANGLAIS\n";
echo "============================================================\n\n";

// Réactiver tous les exercices d'anglais
$stmt = $pdo->prepare("UPDATE Exercises SET is_active = 1 WHERE Subject = 'Anglais'");
$result = $stmt->execute();

if ($result) {
    $count = $stmt->rowCount();
    echo "✅ $count exercices d'Anglais réactivés\n\n";
} else {
    echo "❌ Erreur lors de la réactivation\n\n";
    exit(1);
}

// Statistiques par niveau
echo "📊 RÉPARTITION PAR NIVEAU\n";
echo "============================================================\n\n";

$stmt = $pdo->query("
    SELECT Level, COUNT(*) as count
    FROM Exercises
    WHERE Subject = 'Anglais' AND is_active = 1
    GROUP BY Level
    ORDER BY 
        CASE Level
            WHEN '6ème' THEN 1
            WHEN '5ème' THEN 2
            WHEN '4ème' THEN 3
            WHEN '3ème' THEN 4
            WHEN 'Seconde' THEN 5
            WHEN 'Première' THEN 6
            WHEN 'Terminale' THEN 7
        END
");

$total = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo str_pad($row['Level'], 15) . " : " . $row['count'] . " exercices\n";
    $total += $row['count'];
}

echo "\n" . str_pad("TOTAL", 15) . " : $total exercices actifs\n\n";

// Statistiques globales
echo "📈 STATISTIQUES GLOBALES\n";
echo "============================================================\n\n";

$stmt = $pdo->query("
    SELECT 
        Subject,
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as actifs,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactifs
    FROM Exercises
    GROUP BY Subject
    ORDER BY Subject
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pct = ($row['total'] > 0) ? round(($row['actifs'] / $row['total']) * 100, 1) : 0;
    echo str_pad($row['Subject'], 20) . " : ";
    echo str_pad($row['actifs'] . " actifs", 15);
    echo str_pad($row['inactifs'] . " inactifs", 15);
    echo "($pct%)\n";
}

echo "\n✅ Réactivation terminée avec succès!\n";
