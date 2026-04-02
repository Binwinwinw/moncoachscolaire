#!/usr/bin/env php
<?php
/**
 * Suppression définitive de la Philosophie
 */

require_once __DIR__ . '/../config.php';

echo "🗑️ SUPPRESSION DÉFINITIVE - Philosophie\n";
echo "============================================================\n\n";

// Compter avant suppression
$stmt = $pdo->query("SELECT COUNT(*) FROM Exercises WHERE Subject = 'Philosophie'");
$count = $stmt->fetchColumn();

echo "Exercices de Philosophie trouvés: $count\n\n";

if ($count > 0) {
    // Lister les exercices avant suppression
    $stmt = $pdo->query("SELECT Id, Title, Level FROM Exercises WHERE Subject = 'Philosophie'");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Exercices à supprimer:\n";
    foreach ($exercises as $ex) {
        echo "   - ID {$ex['Id']}: {$ex['Title']} [{$ex['Level']}]\n";
    }
    echo "\n";
    
    // Supprimer
    $stmt = $pdo->prepare("DELETE FROM Exercises WHERE Subject = 'Philosophie'");
    $result = $stmt->execute();
    
    if ($result) {
        echo "✅ SUCCÈS: $count exercice(s) de Philosophie supprimé(s)\n";
    } else {
        echo "❌ ÉCHEC: Impossible de supprimer les exercices\n";
    }
} else {
    echo "ℹ️  Aucun exercice de Philosophie à supprimer\n";
}

echo "\n";
echo "============================================================\n";
echo "📊 Statistiques après suppression\n";
echo "============================================================\n\n";

$stmt = $pdo->query("SELECT COUNT(*) FROM Exercises");
$total = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM Exercises WHERE is_active = 1");
$actifs = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM Exercises WHERE is_active = 0");
$inactifs = $stmt->fetchColumn();

echo "Total exercices: $total\n";
echo "✅ Actifs: $actifs\n";
echo "❌ Inactifs (Anglais): $inactifs\n\n";
