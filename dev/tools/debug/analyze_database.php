<?php
/**
 * Script d'analyse de la structure existante de la base de données
 * Pour comprendre ce qui existe avant toute modification
 */

// Charger les variables d'environnement locales directement
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (empty(trim($line)) || strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// Connexion directe
$host = getenv('DB_HOST') ?: '127.0.0.1';
$database = getenv('DB_DATABASE') ?: 'moncoachscolaire';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("❌ Pas de connexion à la base de données: " . $e->getMessage() . "\n");
}

echo "🔍 ANALYSE DE LA STRUCTURE EXISTANTE\n";
echo str_repeat("=", 70) . "\n\n";

// 1. Lister toutes les tables
echo "1️⃣ TABLES EXISTANTES\n";
echo str_repeat("-", 70) . "\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo sprintf("  %-30s %6d lignes\n", $table, $count);
}

// 2. Structure de la table Exercises (la plus importante)
echo "\n2️⃣ STRUCTURE DE LA TABLE EXERCISES\n";
echo str_repeat("-", 70) . "\n";
$columns = $pdo->query("DESCRIBE Exercises")->fetchAll();
foreach ($columns as $col) {
    echo sprintf("  %-20s %-20s %s %s\n", 
        $col['Field'], 
        $col['Type'], 
        $col['Null'] === 'NO' ? 'NOT NULL' : 'NULL',
        $col['Key'] ? "[$col[Key]]" : ''
    );
}

// 3. Échantillon de données Exercises
echo "\n3️⃣ ÉCHANTILLON D'EXERCICES (5 premiers)\n";
echo str_repeat("-", 70) . "\n";
$exercises = $pdo->query("SELECT Id, Subject, Level, Title FROM Exercises LIMIT 5")->fetchAll();
foreach ($exercises as $ex) {
    echo sprintf("  [%3d] %-20s %-8s %s\n", $ex['Id'], $ex['Subject'], $ex['Level'], substr($ex['Title'], 0, 35));
}

// 4. Répartition par niveau et matière
echo "\n4️⃣ RÉPARTITION DES EXERCICES PAR NIVEAU ET MATIÈRE\n";
echo str_repeat("-", 70) . "\n";
$distribution = $pdo->query("
    SELECT Level, Subject, COUNT(*) as count 
    FROM Exercises 
    GROUP BY Level, Subject 
    ORDER BY Level, Subject
")->fetchAll();
foreach ($distribution as $dist) {
    echo sprintf("  %-10s %-25s %3d exercices\n", $dist['Level'], $dist['Subject'], $dist['count']);
}

// 5. Vérifier si des tables liées aux cours existent déjà
echo "\n5️⃣ TABLES LIÉES AUX COURS (existantes ?)\n";
echo str_repeat("-", 70) . "\n";
$courseRelatedTables = ['Courses', 'Course', 'Lessons', 'CourseMaterials', 
                        'ExerciseCourseLinks', 'CourseProgress', 'UserCourseProgress'];
foreach ($courseRelatedTables as $tableName) {
    $exists = $pdo->query("SHOW TABLES LIKE '$tableName'")->fetch();
    if ($exists) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$tableName`")->fetchColumn();
        echo sprintf("  ✓ %-30s EXISTE (%d lignes)\n", $tableName, $count);
        
        // Montrer la structure
        $cols = $pdo->query("DESCRIBE `$tableName`")->fetchAll(PDO::FETCH_COLUMN, 0);
        echo "    Colonnes: " . implode(', ', $cols) . "\n";
    } else {
        echo sprintf("  ✗ %-30s N'existe pas\n", $tableName);
    }
}

// 6. Structure de la table Users
echo "\n6️⃣ STRUCTURE DE LA TABLE USERS\n";
echo str_repeat("-", 70) . "\n";
$userColumns = $pdo->query("DESCRIBE Users")->fetchAll();
foreach ($userColumns as $col) {
    echo sprintf("  %-20s %-20s %s\n", 
        $col['Field'], 
        $col['Type'], 
        $col['Null'] === 'NO' ? 'NOT NULL' : 'NULL'
    );
}

// 7. Vérifier les foreign keys existantes
echo "\n7️⃣ FOREIGN KEYS EXISTANTES\n";
echo str_repeat("-", 70) . "\n";
$fks = $pdo->query("
    SELECT 
        TABLE_NAME,
        COLUMN_NAME,
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE()
    AND REFERENCED_TABLE_NAME IS NOT NULL
    ORDER BY TABLE_NAME, COLUMN_NAME
")->fetchAll();

$currentTable = '';
foreach ($fks as $fk) {
    if ($currentTable !== $fk['TABLE_NAME']) {
        $currentTable = $fk['TABLE_NAME'];
        echo "\n  Table: $currentTable\n";
    }
    echo sprintf("    %-20s -> %s.%s\n", 
        $fk['COLUMN_NAME'], 
        $fk['REFERENCED_TABLE_NAME'], 
        $fk['REFERENCED_COLUMN_NAME']
    );
}

// 8. Recommandations
echo "\n" . str_repeat("=", 70) . "\n";
echo "📋 RECOMMANDATIONS\n";
echo str_repeat("=", 70) . "\n";

$coursesExists = $pdo->query("SHOW TABLES LIKE 'Courses'")->fetch();
if (!$coursesExists) {
    echo "  ✓ Pas de table Courses → Sûr de créer les nouvelles tables\n";
} else {
    echo "  ⚠️ Table Courses existe déjà → Analyser avant modification\n";
}

echo "\n  Prochaines étapes suggérées:\n";
echo "  1. Créer les tables manquantes (Courses, ExerciseCourseLinks, etc.)\n";
echo "  2. Importer les métadonnées des cours markdown dans la table Courses\n";
echo "  3. Créer les liens entre exercices et cours existants\n";
echo "  4. Mettre à jour l'interface pour afficher les cours\n";

echo "\n✅ Analyse terminée.\n";
