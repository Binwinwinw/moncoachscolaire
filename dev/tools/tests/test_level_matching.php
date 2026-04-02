<?php
/**
 * Script pour tester la correspondance entre le niveau utilisateur et les exercices
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🔍 Test de correspondance niveau utilisateur ↔ exercices\n\n";

// Simuler différents niveaux utilisateur (comme ils pourraient être stockés)
$user_levels = [
    '6ème', '6eme', '6EME',
    '5ème', '5eme', '5EME',
    '4ème', '4eme', '4EME',
    '3ème', '3eme', '3EME',
    'Seconde', 'seconde', 'SECONDE',
    'Première', 'Premiere', 'premiere', 'PREMIERE',
    'Terminale', 'terminale', 'TERMINALE'
];

// Niveaux stockés en base de données
$db_levels = ['6ème', '3ème', 'Seconde', 'Première'];

echo "📊 Test avec les niveaux actuellement en base de données :\n\n";

foreach ($db_levels as $db_level) {
    echo "🎓 Niveau en BDD : $db_level\n";
    
    // Tester différentes variantes du niveau utilisateur
    $variants = [];
    if (stripos($db_level, 'ème') !== false) {
        $variants[] = $db_level; // avec accent
        $variants[] = str_replace('ème', 'eme', $db_level); // sans accent
        $variants[] = str_replace('ème', 'EME', $db_level); // majuscules
    } else {
        $variants[] = $db_level;
        $variants[] = strtolower($db_level);
        $variants[] = strtoupper($db_level);
    }
    
    foreach ($variants as $user_level) {
        $exercises = getExercisesByLevel($user_level, null, 5);
        $status = count($exercises) > 0 ? '✅' : '❌';
        echo "   $status Utilisateur '$user_level' → " . count($exercises) . " exercice(s) trouvé(s)\n";
        
        if (count($exercises) === 0 && $user_level !== $db_level) {
            echo "      ⚠️  PROBLÈME : Le niveau utilisateur '$user_level' ne trouve pas les exercices de '$db_level'\n";
        }
    }
    echo "\n";
}

echo "💡 Conclusion :\n";
echo "   La fonction getExercisesByLevel() utilise une comparaison exacte (WHERE Level = ?)\n";
echo "   Si le niveau utilisateur ne correspond pas exactement au niveau en BDD, aucun exercice ne sera trouvé.\n";
echo "   Il faut normaliser les niveaux avant la requête.\n";

