<?php
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * EXPORT SCHEMA - Table exercises
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Script pour exporter le schéma complet de la table `exercises` de la BDD
 * 
 * 📄 Sortie : dev/reports/exercises_schema.json
 * 
 * 🎯 Objectif :
 *    - Extraire tous les champs de la table `exercises`
 *    - Récupérer les types, valeurs par défaut, contraintes
 *    - Générer un JSON utilisable pour la migration des anciens fichiers
 * 
 * 💻 Utilisation :
 *    php dev/tools/import_export/export_schema.php
 * 
 * 📅 Créé : 14 février 2026
 * 👤 Auteur : MonCoachScolaire Team
 * 
 * ═══════════════════════════════════════════════════════════════════════════
 */

// Chemins relatifs depuis la racine du projet
$projectRoot = dirname(__DIR__, 3); // Remonte de 3 niveaux : import_export → tools → dev → racine

// Inclure la connexion à la base de données
require_once $projectRoot . '/src/database/connection.php';

// Dossier de sortie
$outputDir = $projectRoot . '/dev/reports';
$outputFile = $outputDir . '/exercises_schema.json';

// Créer le dossier s'il n'existe pas
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "✅ Dossier créé : $outputDir\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  EXPORT SCHEMA - Table exercises\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

try {
    // Utiliser directement $pdo (fourni par connection.php)
    if (!isset($pdo)) {
        throw new Exception("❌ Variable \$pdo non disponible après inclusion de connection.php");
    }
    
    echo "🔌 Connexion à la base de données... ";
    
    // Vérifier que la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'exercises'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        throw new Exception("❌ La table 'exercises' n'existe pas dans la base de données.");
    }
    
    echo "✅ OK\n";
    
    // Récupérer la structure complète de la table
    echo "📊 Extraction du schéma de la table 'exercises'... ";
    
    $stmt = $pdo->query("DESCRIBE exercises");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        throw new Exception("❌ Aucune colonne trouvée dans la table 'exercises'.");
    }
    
    echo "✅ OK (" . count($columns) . " colonnes)\n\n";
    
    // Formater le schéma
    $schema = [
        'table_name' => 'exercises',
        'exported_at' => date('Y-m-d H:i:s'),
        'total_columns' => count($columns),
        'columns' => []
    ];
    
    echo "📋 Liste des colonnes :\n";
    echo str_repeat("─", 100) . "\n";
    printf("%-25s %-20s %-10s %-15s %-20s\n", "FIELD", "TYPE", "NULL", "KEY", "DEFAULT");
    echo str_repeat("─", 100) . "\n";
    
    foreach ($columns as $column) {
        $fieldName = $column['Field'];
        $fieldType = $column['Type'];
        $nullable = $column['Null'] === 'YES' ? 'YES' : 'NO';
        $key = $column['Key'] ?: '-';
        $default = $column['Default'] !== null ? $column['Default'] : 'NULL';
        
        // Extraire le type de base (sans la taille)
        preg_match('/^([a-z]+)/i', $fieldType, $matches);
        $baseType = $matches[1] ?? 'unknown';
        
        // Déterminer le type PHP correspondant
        $phpType = match(strtolower($baseType)) {
            'int', 'tinyint', 'smallint', 'mediumint', 'bigint' => 'integer',
            'varchar', 'char', 'text', 'mediumtext', 'longtext', 'enum' => 'string',
            'decimal', 'float', 'double' => 'float',
            'date', 'datetime', 'timestamp' => 'string', // Format ISO 8601
            'json' => 'array',
            'boolean', 'bool' => 'boolean',
            default => 'mixed'
        };
        
        // Ajouter au schéma
        $schema['columns'][$fieldName] = [
            'field' => $fieldName,
            'type' => $fieldType,
            'php_type' => $phpType,
            'nullable' => $nullable === 'YES',
            'key' => $key,
            'default' => $default,
            'extra' => $column['Extra'] ?? ''
        ];
        
        // Afficher dans la console
        printf("%-25s %-20s %-10s %-15s %-20s\n", 
            $fieldName, 
            $fieldType, 
            $nullable, 
            $key, 
            $default
        );
    }
    
    echo str_repeat("─", 100) . "\n";
    echo "\n";
    
    // Sauvegarder le schéma en JSON
    echo "💾 Sauvegarde du schéma... ";
    
    $jsonOutput = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
    if ($jsonOutput === false) {
        throw new Exception("❌ Erreur lors de l'encodage JSON : " . json_last_error_msg());
    }
    
    $bytesWritten = file_put_contents($outputFile, $jsonOutput);
    
    if ($bytesWritten === false) {
        throw new Exception("❌ Erreur lors de l'écriture du fichier : $outputFile");
    }
    
    echo "✅ OK\n";
    echo "📄 Fichier généré : $outputFile\n";
    echo "📦 Taille : " . number_format($bytesWritten) . " octets\n";
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  ✅ EXPORT TERMINÉ AVEC SUCCÈS\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";
    echo "📊 Résumé :\n";
    echo "   • Table : exercises\n";
    echo "   • Colonnes : " . count($columns) . "\n";
    echo "   • Fichier : $outputFile\n";
    echo "\n";
    echo "🚀 Prochaine étape :\n";
    echo "   php dev/tools/import_export/migrate_exercises_json.php\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  ❌ ERREUR\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";
    echo "Message : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . "\n";
    echo "Ligne : " . $e->getLine() . "\n";
    echo "\n";
    exit(1);
}
