<?php
/**
 * dev/tools/demo/test_demo_exercises.php
 * Vérifie que le compte démo accède bien aux exercices parsés
 */

require_once __DIR__ . '/../../../src/database/connection.php';
require_once __DIR__ . '/../../../src/includes/exercice_loader.php';

echo "🧪 TEST DU COMPTE DÉMO - ACCÈS AUX EXERCICES PARSÉS\n";
echo str_repeat("=", 70) . "\n\n";

// Simuler le contexte démo
$_SESSION['user_id'] = 1; // ID du compte démo
$_SESSION['username'] = 'demo';
$_SESSION['is_demo'] = true;

// Tester pour chaque niveau
$levels = ['4ème', '6ème'];

foreach ($levels as $level) {
    echo "📚 Niveau : $level\n";
    echo str_repeat("-", 70) . "\n";

    // Appeler la fonction utilisée par demo.php
    if (function_exists('getExercisesByLevelSmart')) {
        $exercises = getExercisesByLevelSmart($level, null, 5);
    } elseif (function_exists('getExercisesByLevel')) {
        $exercises = getExercisesByLevel($level, null, 5);
    } else {
        echo "❌ Fonction de chargement non trouvée\n\n";
        continue;
    }

    if (empty($exercises)) {
        echo "⚠️  Aucun exercice trouvé\n\n";
        continue;
    }

    printf("✅ %d exercice(s) chargé(s)\n", count($exercises));

    // Vérifier les colonnes parsées
    $ex = $exercises[0];

    $hasStructure = isset($ex['structure_type']);
    $hasSubQuestions = isset($ex['sub_questions']);
    $hasInstruction = isset($ex['Instruction']) && !empty($ex['Instruction']);
    $hasExamPrep = isset($ex['exam_prep']);

    echo "   Structure parsée : " . ($hasStructure ? "✅ " . $ex['structure_type'] : "❌ Non disponible") . "\n";
    echo "   Consigne         : " . ($hasInstruction ? "✅" : "❌") . "\n";
    echo "   Sous-questions   : " . ($hasSubQuestions && $ex['sub_questions'] ? "✅" : "⬜") . "\n";
    echo "   Prépa exam       : " . ($hasExamPrep && $ex['exam_prep'] ? "✅ " . $ex['exam_prep'] : "⬜") . "\n";

    // Afficher un exemple
    if ($hasStructure && $ex['structure_type'] === 'multi-parties') {
        echo "\n   📋 Exemple multi-parties trouvé :\n";
        echo "      ID: {$ex['Id']}\n";
        echo "      Titre: {$ex['Title']}\n";
        echo "      Matière: {$ex['Subject']}\n";

        if ($hasSubQuestions && $ex['sub_questions']) {
            $subQ = json_decode($ex['sub_questions'], true);
            $nbQuestions = count($subQ['questions'] ?? []);
            echo "      Parties: $nbQuestions sous-questions\n";

            // Afficher les identifiants des sous-questions
            if (!empty($subQ['questions'])) {
                $ids = array_map(function($q) { return $q['id']; }, array_slice($subQ['questions'], 0, 3));
                echo "      IDs: " . implode(', ', $ids);
                if ($nbQuestions > 3) echo " ...";
                echo "\n";
            }
        }
    } elseif ($hasStructure) {
        echo "\n   📝 Exemple simple :\n";
        echo "      ID: {$ex['Id']}\n";
        echo "      Titre: {$ex['Title']}\n";
        echo "      Matière: {$ex['Subject']}\n";
    }

    // Compter les structures
    $simple = 0;
    $multi = 0;
    foreach ($exercises as $e) {
        if (isset($e['structure_type'])) {
            if ($e['structure_type'] === 'multi-parties') {
                $multi++;
            } else {
                $simple++;
            }
        }
    }

    if ($simple > 0 || $multi > 0) {
        echo "\n   📊 Répartition : $simple simple(s), $multi multi-parties\n";
    }

    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "✅ Test terminé\n\n";

// Test spécifique : chercher un exercice multi-parties connu
echo "🔍 RECHERCHE D'EXERCICE MULTI-PARTIES SPÉCIFIQUE\n";
echo str_repeat("=", 70) . "\n\n";

$stmt = $pdo->query("
    SELECT Id, Title, Subject, Level, structure_type, pattern_detected
    FROM exercises
    WHERE structure_type = 'multi-parties'
    LIMIT 5
");

$multiExercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($multiExercises)) {
    echo "⚠️  Aucun exercice multi-parties trouvé dans la BDD\n";
} else {
    echo "✅ " . count($multiExercises) . " exercice(s) multi-parties trouvé(s) :\n\n";

    foreach ($multiExercises as $ex) {
        printf("   • ID %d : %s (%s - %s) [%s]\n",
            $ex['Id'],
            $ex['Title'],
            $ex['Subject'],
            $ex['Level'],
            $ex['pattern_detected']
        );
    }
}

echo "\n✅ Tous les tests terminés !\n";
