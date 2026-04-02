<?php
/**
 * Script pour vérifier que le filtrage par niveau est strict
 * Un élève de 6ème ne doit voir QUE les exercices de 6ème, pas ceux d'autres niveaux
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🔍 Vérification du filtrage strict par niveau\n\n";

// Niveaux à tester
$test_levels = [
    '6ème' => 'Collège',
    '5ème' => 'Collège',
    '4ème' => 'Collège',
    '3ème' => 'Collège',
    'Seconde' => 'Lycée',
    'Première' => 'Lycée',
    'Terminale' => 'Lycée'
];

$errors = [];

foreach ($test_levels as $user_level => $section) {
    echo "🎓 Test pour un élève de niveau : $user_level ($section)\n";
    
    // Récupérer les exercices pour ce niveau
    $exercises = getExercisesByLevel($user_level, null, 1000);
    
    echo "   Exercices trouvés : " . count($exercises) . "\n";
    
    // Vérifier que TOUS les exercices retournés sont bien du bon niveau
    $wrong_levels = [];
    foreach ($exercises as $ex) {
        $exercise_level = $ex['Level'] ?? '';
        
        // Normaliser pour comparaison
        $normalized_user = normalizeLevelForDB($user_level);
        $normalized_ex = normalizeLevelForDB($exercise_level);
        
        if ($normalized_ex !== $normalized_user) {
            $wrong_levels[] = [
                'id' => $ex['Id'],
                'title' => $ex['Title'] ?? 'N/A',
                'expected' => $normalized_user,
                'found' => $normalized_ex,
                'raw_level' => $exercise_level
            ];
        }
    }
    
    if (empty($wrong_levels)) {
        echo "   ✅ Tous les exercices sont du bon niveau\n";
    } else {
        echo "   ❌ ERREUR : " . count($wrong_levels) . " exercice(s) du mauvais niveau trouvé(s) !\n";
        foreach ($wrong_levels as $wrong) {
            echo "      - ID {$wrong['id']} : '{$wrong['title']}' (Niveau attendu: {$wrong['expected']}, Niveau trouvé: {$wrong['found']} [raw: {$wrong['raw_level']}])\n";
        }
        $errors[$user_level] = $wrong_levels;
    }
    
    // Vérifier aussi qu'on ne mélange pas collège et lycée
    if ($section === 'Collège') {
        foreach ($exercises as $ex) {
            $ex_level = normalizeLevelForDB($ex['Level'] ?? '');
            if (in_array($ex_level, ['Seconde', 'Première', 'Terminale'])) {
                echo "   ❌ ERREUR CRITIQUE : Un exercice du LYCÉE trouvé pour un élève du COLLÈGE !\n";
                echo "      - ID {$ex['Id']} : '{$ex['Title']}' (Niveau: {$ex['Level']})\n";
                $errors[$user_level][] = ['type' => 'cross-section', 'exercise' => $ex];
            }
        }
    } elseif ($section === 'Lycée') {
        foreach ($exercises as $ex) {
            $ex_level = normalizeLevelForDB($ex['Level'] ?? '');
            if (in_array($ex_level, ['6ème', '5ème', '4ème', '3ème'])) {
                echo "   ❌ ERREUR CRITIQUE : Un exercice du COLLÈGE trouvé pour un élève du LYCÉE !\n";
                echo "      - ID {$ex['Id']} : '{$ex['Title']}' (Niveau: {$ex['Level']})\n";
                $errors[$user_level][] = ['type' => 'cross-section', 'exercise' => $ex];
            }
        }
    }
    
    echo "\n";
}

// Résumé
echo "📊 RÉSUMÉ\n";
echo str_repeat("=", 60) . "\n";

if (empty($errors)) {
    echo "✅ SUCCÈS : Le filtrage par niveau est strict et correct.\n";
    echo "   Tous les élèves ne voient que les exercices de leur niveau.\n";
} else {
    echo "❌ ERREURS TROUVÉES : " . count($errors) . " niveau(s) avec des problèmes\n";
    echo "   Il faut corriger ces erreurs pour garantir l'intégrité pédagogique.\n";
}

echo "\n";

