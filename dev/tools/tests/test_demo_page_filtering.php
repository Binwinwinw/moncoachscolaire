<?php
/**
 * Test complet du filtrage dans demo.php pour vérifier le problème signalé
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🔍 Test complet du filtrage pour la page démo\n\n";

// Simuler exactement ce que fait demo.php
$testLevels = [
    '6ème' => 'Collège',
    '5ème' => 'Collège',
    '4ème' => 'Collège',
    '3ème' => 'Collège',
    'Seconde' => 'Lycée',
    'Première' => 'Lycée',
    'Terminale' => 'Lycée'
];

foreach ($testLevels as $selected_level => $section) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📚 Test pour : $selected_level ($section)\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Simuler exactement le code de demo.php
    $sampleExercises = [];
    
    try {
        // Normaliser le niveau (comme dans demo.php après ma correction)
        $normalizedLevel = normalizeLevelForDB($selected_level);
        echo "   Niveau normalisé : $normalizedLevel\n";
        
        // Charger les exercices (comme dans demo.php)
        $sampleExercises = getExercisesByLevel($normalizedLevel, null, 10);
        echo "   Exercices trouvés avant filtrage : " . count($sampleExercises) . "\n";
        
        // Filtrage de sécurité (comme dans demo.php après ma correction)
        $filteredExercises = [];
        $wrongLevels = [];
        
        foreach ($sampleExercises as $ex) {
            $exLevel = normalizeLevelForDB($ex['Level'] ?? '');
            if ($exLevel === $normalizedLevel) {
                $filteredExercises[] = $ex;
            } else {
                $wrongLevels[] = [
                    'id' => $ex['Id'] ?? 'N/A',
                    'title' => $ex['Title'] ?? 'N/A',
                    'expected' => $normalizedLevel,
                    'found' => $exLevel,
                    'raw' => $ex['Level'] ?? 'N/A'
                ];
            }
        }
        
        $sampleExercises = $filteredExercises;
        echo "   Exercices après filtrage : " . count($sampleExercises) . "\n\n";
        
        if (!empty($wrongLevels)) {
            echo "   ❌ PROBLÈME DÉTECTÉ : " . count($wrongLevels) . " exercice(s) du mauvais niveau trouvé(s) !\n";
            foreach ($wrongLevels as $wrong) {
                echo "      - ID {$wrong['id']} : '{$wrong['title']}'\n";
                echo "        Attendu: {$wrong['expected']}, Trouvé: {$wrong['found']} (raw: {$wrong['raw']})\n";
            }
            echo "\n";
        }
        
        // Vérifier le mélange collège/lycée
        $collegeLevels = ['6ème', '5ème', '4ème', '3ème'];
        $lyceeLevels = ['Seconde', 'Première', 'Terminale'];
        
        $collegeInLycée = [];
        $lycéeInCollege = [];
        
        foreach ($sampleExercises as $ex) {
            $exLevel = normalizeLevelForDB($ex['Level'] ?? '');
            if (in_array($selected_level, $collegeLevels) && in_array($exLevel, $lyceeLevels)) {
                $lycéeInCollege[] = $ex;
            }
            if (in_array($selected_level, $lyceeLevels) && in_array($exLevel, $collegeLevels)) {
                $collegeInLycée[] = $ex;
            }
        }
        
        if (!empty($lycéeInCollege)) {
            echo "   ❌ ERREUR CRITIQUE : Exercices du LYCÉE trouvés pour un niveau du COLLÈGE !\n";
            foreach ($lycéeInCollege as $ex) {
                echo "      - ID {$ex['Id']} : '{$ex['Title']}' (Niveau: {$ex['Level']})\n";
            }
            echo "\n";
        }
        
        if (!empty($collegeInLycée)) {
            echo "   ❌ ERREUR CRITIQUE : Exercices du COLLÈGE trouvés pour un niveau du LYCÉE !\n";
            foreach ($collegeInLycée as $ex) {
                echo "      - ID {$ex['Id']} : '{$ex['Title']}' (Niveau: {$ex['Level']})\n";
            }
            echo "\n";
        }
        
        // Afficher quelques exemples d'exercices trouvés
        if (!empty($sampleExercises)) {
            echo "   📝 Exemples d'exercices trouvés :\n";
            foreach (array_slice($sampleExercises, 0, 3) as $ex) {
                echo "      • {$ex['Title']} ({$ex['Subject']})\n";
            }
        } else {
            echo "   ⚠️  Aucun exercice trouvé pour ce niveau.\n";
        }
        
        if (empty($wrongLevels) && empty($lycéeInCollege) && empty($collegeInLycée)) {
            echo "\n   ✅ TOUS LES EXERCICES SONT DU BON NIVEAU\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ ERREUR : " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "✅ Test terminé !\n";

