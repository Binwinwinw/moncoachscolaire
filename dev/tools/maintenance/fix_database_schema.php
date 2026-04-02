<?php
/**
 * Script de vérification et correction du schéma de base de données
 * Ajoute les colonnes manquantes détectées par les APIs admin
 */

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/db/connection.php';

echo "=== Vérification et correction du schéma ===\n\n";

// Table Exercises - vérifier les colonnes
echo "1️⃣  Table Exercises:\n";
$sql = "DESCRIBE Exercises";
$stmt = $pdo->query($sql);
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$columnNames = array_map(fn($c) => $c['Field'], $columns);

// Colonnes requises
$requiredColumns = [
    'is_active' => 'TINYINT(1) DEFAULT 1'
];

foreach ($requiredColumns as $col => $type) {
    if (in_array($col, $columnNames)) {
        echo "   ✅ $col - OK\n";
    } else {
        echo "   ❌ $col - MANQUANTE, ajout en cours...\n";
        try {
            $pdo->exec("ALTER TABLE Exercises ADD COLUMN $col $type");
            echo "   ✅ Colonne $col ajoutée\n";
        } catch (Exception $e) {
            echo "   ❌ Erreur: " . $e->getMessage() . "\n";
        }
    }
}

// Table Users - vérifier les colonnes
echo "\n2️⃣  Table Users:\n";
$sql = "DESCRIBE Users";
$stmt = $pdo->query($sql);
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$columnNames = array_map(fn($c) => $c['Field'], $columns);

$requiredColumns = [
    'Username' => 'VARCHAR(100)',
    'Email' => 'VARCHAR(100)',
    'Role' => "ENUM('student','admin','parent') DEFAULT 'student'",
    'CreatedAt' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
];

foreach ($requiredColumns as $col => $type) {
    if (in_array($col, $columnNames)) {
        echo "   ✅ $col - OK\n";
    } else {
        echo "   ❌ $col - MANQUANTE, ajout en cours...\n";
        try {
            $pdo->exec("ALTER TABLE Users ADD COLUMN $col $type");
            echo "   ✅ Colonne $col ajoutée\n";
        } catch (Exception $e) {
            echo "   ⚠️  Erreur (peut déjà exister): " . $e->getMessage() . "\n";
        }
    }
}

// Résumé final
echo "\n✅ Vérification du schéma terminée!\n\n";

// Tester que l'API fonctionne
echo "3️⃣  Test de l'API quality_live:\n";
session_start();
$_SESSION['user_id'] = 4;
$_SESSION['logged_in'] = true;
$_SESSION['username'] = 'zinzin';

try {
    ob_start();
    require_once __DIR__ . '/src/api/admin/exercises_quality_live.php';
    $output = ob_get_clean();
    $data = json_decode($output, true);
    if ($data['success']) {
        echo "   ✅ API fonctionne!\n";
        echo "   📊 Résumé: " . $data['totals']['total'] . " exercices, " . $data['totals']['active'] . " actifs\n";
    } else {
        echo "   ❌ API erreur: " . ($data['error'] ?? 'Unknown') . "\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n✨ Fait!\n";
?>
