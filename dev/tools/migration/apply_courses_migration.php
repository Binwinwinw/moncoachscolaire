<?php
/**
 * Script pour appliquer la migration des tables Courses
 */

require_once __DIR__ . '/src/database/connection.php';

echo "🔄 Application de la migration Courses...\n\n";

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur: Pas de connexion PDO\n");
}

try {
    // Lire le fichier de migration
    $sql = file_get_contents(__DIR__ . '/db/migration_courses_mysql.sql');
    
    if (!$sql) {
        die("❌ Erreur: Impossible de lire le fichier migration_courses_mysql.sql\n");
    }
    
    // Diviser en requêtes individuelles et exécuter
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "📝 Exécution de " . count($statements) . " requêtes...\n\n";
    
    foreach ($statements as $index => $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            
            // Afficher le type de requête exécutée
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches);
                $tableName = $matches[1] ?? 'inconnue';
                echo "✅ Table créée: $tableName\n";
            }
        } catch (PDOException $e) {
            // Ignorer les erreurs "table already exists"
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate') === false) {
                echo "⚠️ Erreur requête #$index: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✅ Migration terminée avec succès!\n\n";
    
    // Vérifier que les tables existent
    echo "🔍 Vérification des tables créées:\n";
    $tables = ['Courses', 'ExerciseCourseLinks', 'UserCourseProgress', 'CourseViewEvents', 'CourseComments'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->fetch();
        if ($exists) {
            echo "  ✅ $table\n";
        } else {
            echo "  ❌ $table (non créée)\n";
        }
    }
    
} catch (Exception $e) {
    die("❌ Erreur: " . $e->getMessage() . "\n");
}
