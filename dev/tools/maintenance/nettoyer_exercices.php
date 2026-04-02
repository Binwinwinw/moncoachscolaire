#!/usr/bin/env php
<?php
/**
 * SCRIPT DE NETTOYAGE DES EXERCICES
 * 1. Désactive les exercices des matières non prioritaires
 * 2. Ajoute une colonne 'is_active' si elle n'existe pas
 */

require_once __DIR__ . '/../config.php';

echo "🧹 NETTOYAGE DES EXERCICES\n";
echo "============================================================\n\n";

// Vérifier si la colonne is_active existe
try {
    $pdo->query("SELECT is_active FROM Exercises LIMIT 1");
    echo "✅ Colonne 'is_active' existe déjà\n\n";
} catch (PDOException $e) {
    echo "➕ Ajout de la colonne 'is_active' à la table Exercises...\n";
    try {
        $pdo->exec("ALTER TABLE Exercises ADD COLUMN is_active TINYINT(1) DEFAULT 1");
        echo "✅ Colonne 'is_active' ajoutée avec succès\n\n";
    } catch (PDOException $e2) {
        echo "❌ Erreur lors de l'ajout de la colonne: " . $e2->getMessage() . "\n";
        exit(1);
    }
}

// Matières à désactiver
$matieresADesactiver = ['Anglais', 'Arts plastiques', 'Éducation musicale', 'Philosophie'];

echo "🔴 DÉSACTIVATION DES MATIÈRES NON PRIORITAIRES\n";
echo "============================================================\n\n";

foreach ($matieresADesactiver as $matiere) {
    // Compter les exercices
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Exercises WHERE Subject = ?");
    $stmt->execute([$matiere]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        // Désactiver les exercices
        $stmt = $pdo->prepare("UPDATE Exercises SET is_active = 0 WHERE Subject = ?");
        $result = $stmt->execute([$matiere]);
        
        if ($result) {
            echo "✅ $matiere: $count exercices désactivés\n";
        } else {
            echo "❌ $matiere: Échec de la désactivation\n";
        }
    } else {
        echo "ℹ️  $matiere: Aucun exercice trouvé\n";
    }
}

echo "\n";
echo "✅ ACTIVATION DES MATIÈRES PRIORITAIRES\n";
echo "============================================================\n\n";

$matieresPrioritaires = ['Mathématiques', 'Français', 'Histoire-Géographie', 'Histoire-Géo', 'SVT', 'Physique-Chimie'];

foreach ($matieresPrioritaires as $matiere) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Exercises WHERE Subject = ?");
    $stmt->execute([$matiere]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        $stmt = $pdo->prepare("UPDATE Exercises SET is_active = 1 WHERE Subject = ?");
        $result = $stmt->execute([$matiere]);
        
        if ($result) {
            echo "✅ $matiere: $count exercices activés\n";
        } else {
            echo "❌ $matiere: Échec de l'activation\n";
        }
    }
}

echo "\n";
echo "============================================================\n";
echo "📊 STATISTIQUES FINALES\n";
echo "============================================================\n\n";

// Compter les exercices actifs
$stmt = $pdo->query("SELECT COUNT(*) FROM Exercises WHERE is_active = 1");
$actifs = $stmt->fetchColumn();

// Compter les exercices inactifs
$stmt = $pdo->query("SELECT COUNT(*) FROM Exercises WHERE is_active = 0");
$inactifs = $stmt->fetchColumn();

// Total
$total = $actifs + $inactifs;

echo "Total exercices: $total\n";
echo "✅ Actifs: $actifs (" . round(($actifs / $total) * 100, 1) . "%)\n";
echo "❌ Inactifs: $inactifs (" . round(($inactifs / $total) * 100, 1) . "%)\n";

echo "\n";
echo "============================================================\n";
echo "✅ NETTOYAGE TERMINÉ\n";
echo "============================================================\n\n";
echo "💡 Les exercices désactivés ne seront plus affichés aux utilisateurs\n";
echo "   mais restent en base de données pour référence future.\n\n";
