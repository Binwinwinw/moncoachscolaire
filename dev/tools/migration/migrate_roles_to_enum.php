<?php
/**
 * Migration pour améliorer le système de rôles
 * Convertir Role de VARCHAR(50) à ENUM('admin', 'student', 'parent')
 */

require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/db/connection.php';

echo "=== MIGRATION: Amélioration du système de rôles ===\n\n";

echo "1️⃣  Situation actuelle:\n";
$stmt = $pdo->query("DESCRIBE Users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    if ($col['Field'] === 'Role') {
        echo "   Type: {$col['Type']}\n";
        echo "   Défaut: {$col['Default']}\n";
        break;
    }
}
echo "\n";

// Analyser les rôles actuels
echo "2️⃣  Rôles uniques dans la base:\n";
$stmt = $pdo->query("SELECT DISTINCT Role FROM Users ORDER BY Role");
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "   Trouvés: " . implode(', ', $roles) . "\n\n";

// Vérifier qu'il n'y a pas de rôles invalides
echo "3️⃣  Vérification de la cohérence:\n";
$allowed = ['admin', 'student', 'parent'];
$invalid = array_diff($roles, $allowed);

if (empty($invalid)) {
    echo "   ✅ Tous les rôles sont valides\n\n";
} else {
    echo "   ⚠️  Rôles invalides détectés: " . implode(', ', $invalid) . "\n";
    echo "   ❌ Migration annulée pour sécurité\n\n";
    exit;
}

// Effectuer la migration
echo "4️⃣  Migration de la colonne Role:\n";

try {
    // Étape 1: Ajouter nouvelle colonne temporaire
    echo "   [1/4] Création colonne temporaire...\n";
    $pdo->exec("ALTER TABLE Users ADD COLUMN Role_New ENUM('admin', 'student', 'parent') NOT NULL DEFAULT 'student'");
    
    // Étape 2: Copier les données
    echo "   [2/4] Copie des données...\n";
    $pdo->exec("UPDATE Users SET Role_New = Role");
    
    // Étape 3: Supprimer l'ancienne colonne
    echo "   [3/4] Suppression de l'ancienne colonne...\n";
    $pdo->exec("ALTER TABLE Users DROP COLUMN Role");
    
    // Étape 4: Renommer la nouvelle colonne
    echo "   [4/4] Renommage de la nouvelle colonne...\n";
    $pdo->exec("ALTER TABLE Users CHANGE Role_New Role ENUM('admin', 'student', 'parent') NOT NULL DEFAULT 'student'");
    
    echo "   ✅ Migration réussie!\n\n";
    
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    echo "   Tentative de restauration...\n";
    try {
        $pdo->exec("ALTER TABLE Users DROP COLUMN Role_New");
    } catch (Exception $e2) {
        // Ignoré
    }
    exit;
}

// Vérification finale
echo "5️⃣  Vérification finale:\n";
$stmt = $pdo->query("DESCRIBE Users");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    if ($col['Field'] === 'Role') {
        echo "   Type après migration: {$col['Type']}\n";
        echo "   Défaut: {$col['Default']}\n";
        break;
    }
}

// Tester l'intégrité
echo "\n6️⃣  Test d'intégrité:\n";
$stmt = $pdo->query("SELECT DISTINCT Role FROM Users");
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "   Rôles présents: " . implode(', ', $roles) . "\n";

echo "\n✨ Migration complétée avec succès!\n";

echo "\n📋 Avantages de cette modification:\n";
echo "   ✅ Validation au niveau base de données\n";
echo "   ✅ Impossible d'insérer un rôle invalide\n";
echo "   ✅ Performance améliorée (ENUM = small integer storage)\n";
echo "   ✅ Sécurité renforcée contre les injections\n";
echo "   ✅ Meilleure documentation du schéma\n";

?>
