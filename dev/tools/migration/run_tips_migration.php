<?php
/**
 * Exécute la migration pour ajouter la colonne Tips
 * Usage: php tools/run_tips_migration.php
 */

require_once __DIR__ . '/../src/database/connection.php';

if (!$pdo) {
    die("❌ Erreur: Impossible de se connecter à la base de données.\n");
}

echo "🔧 Exécution de la migration add_tips_column...\n\n";

try {
    // Vérifier si la colonne existe déjà
    $stmt = $pdo->query("SHOW COLUMNS FROM Exercises LIKE 'Tips'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "ℹ️  La colonne 'Tips' existe déjà.\n";
    } else {
        // Ajouter la colonne
        $pdo->exec("ALTER TABLE Exercises ADD COLUMN Tips TEXT NULL AFTER Answer");
        echo "✅ Colonne 'Tips' ajoutée avec succès.\n";
        
        // Ajouter le commentaire
        $pdo->exec("ALTER TABLE Exercises MODIFY COLUMN Tips TEXT NULL COMMENT 'Astuces et conseils pédagogiques pour aider l''élève'");
        echo "✅ Commentaire ajouté à la colonne.\n";
    }
    
    echo "\n✨ Migration terminée!\n";
    
} catch (PDOException $e) {
    die("❌ Erreur lors de la migration: " . $e->getMessage() . "\n");
}
