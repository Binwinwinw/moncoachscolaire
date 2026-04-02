<?php
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/db/connection.php';

echo "=== Structure de la table Exercises ===\n\n";

$sql = "DESCRIBE Exercises";
$stmt = $pdo->query($sql);
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    printf("%-20s %-15s %s\n", $col['Field'], $col['Type'], $col['Key']);
}

echo "\n=== Recherche de la colonne is_active ===\n";
$hasIsActive = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'is_active') {
        $hasIsActive = true;
        echo "✅ Colonne is_active EXISTE\n";
        break;
    }
}

if (!$hasIsActive) {
    echo "❌ Colonne is_active N'EXISTE PAS\n";
    echo "\nAjout de la colonne...\n";
    
    try {
        $pdo->exec("ALTER TABLE Exercises ADD COLUMN is_active TINYINT(1) DEFAULT 1");
        echo "✅ Colonne ajoutée avec succès!\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'ajout: " . $e->getMessage() . "\n";
    }
}
?>
