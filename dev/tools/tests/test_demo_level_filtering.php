<?php
/**
 * Script pour tester le filtrage des exercices dans la page démo
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🔍 Test du filtrage des exercices pour la page démo\n\n";

$testLevels = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale'];

foreach ($testLevels as $selectedLevel) {
    echo "📚 Test pour le niveau : $selectedLevel\n";
    
    // Simuler ce que fait demo.php
    $sampleExercises = [];
    $normalizedLevel = normalizeLevelForDB($selectedLevel);
    $levelVariants = [$normalizedLevel, $selectedLevel];
    
    // Ajouter variantes sans accent
    if (mb_strpos($normalizedLevel, 'ème') !== false) {
        $levelVariants[] = str_replace('ème', 'eme', $normalizedLevel);
    }
    
    // Charger les exercices
    foreach ($levelVariants as $variant) {
        $allExercises = getExercisesByLevel($variant, null, 10);
        if (!empty($allExercises)) {
            $sampleExercises = $allExercises;
            break;
        }
    }
    
    echo "   Exercices trouvés : " . count($sampleExercises) . "\n";
    
    // Vérifier que tous les exercices sont du bon niveau
    $wrongLevels = [];
    foreach ($sampleExercises as $ex) {
        $exLevel = normalizeLevelForDB($ex['Level'] ?? '');
        $expectedLevel = normalizeLevelForDB($selectedLevel);
        
        if ($exLevel !== $expectedLevel) {
            $wrongLevels[] = [
                'id' => $ex['Id'] ?? 'N/A',
                'title' => $ex['Title'] ?? 'N/A',
                'expected' => $expectedLevel,
                'found' => $exLevel,
                'raw' => $ex['Level'] ?? 'N/A'
            ];
        }
    }
    
    if (empty($wrongLevels)) {
        echo "   ✅ Tous les exercices sont du bon niveau\n";
    } else {
        echo "   ❌ PROBLÈME : " . count($wrongLevels) . " exercice(s) du mauvais niveau trouvé(s) !\n";
        foreach ($wrongLevels as $wrong) {
            echo "      - ID {$wrong['id']} : '{$wrong['title']}' (Attendu: {$wrong['expected']}, Trouvé: {$wrong['found']} [raw: {$wrong['raw']}])\n";
        }
    }
    
    // Vérifier le mélange collège/lycée
    $collegeLevels = ['6ème', '5ème', '4ème', '3ème'];
    $lyceeLevels = ['Seconde', 'Première', 'Terminale'];
    
    if (in_array($selectedLevel, $collegeLevels)) {
        foreach ($sampleExercises as $ex) {
            $exLevel = normalizeLevelForDB($ex['Level'] ?? '');
            if (in_array($exLevel, $lyceeLevels)) {
                echo "   ❌ ERREUR CRITIQUE : Exercice du lycée trouvé pour un niveau du collège !\n";
                echo "      - ID {$ex['Id']} : '{$ex['Title']}' (Niveau: {$ex['Level']})\n";
            }
        }
    } elseif (in_array($selectedLevel, $lyceeLevels)) {
        foreach ($sampleExercises as $ex) {
            $exLevel = normalizeLevelForDB($ex['Level'] ?? '');
            if (in_array($exLevel, $collegeLevels)) {
                echo "   ❌ ERREUR CRITIQUE : Exercice du collège trouvé pour un niveau du lycée !\n";
                echo "      - ID {$ex['Id']} : '{$ex['Title']}' (Niveau: {$ex['Level']})\n";
            }
        }
    }
    
    echo "\n";
}

echo "✅ Test terminé !\n";

