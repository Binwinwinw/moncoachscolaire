#!/usr/bin/env php
<?php
/**
 * TEST DE VALIDATION FINALE APRÈS MIGRATION
 * Vérifie que les fonctions SMART sont bien utilisées
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

echo "🧪 TEST VALIDATION MIGRATION SMART\n";
echo "============================================================\n\n";

$allTestsPassed = true;

// TEST 1: getExercisesByLevelSmart() existe et fonctionne
echo "TEST 1: Fonction getExercisesByLevelSmart() disponible\n";
echo "------------------------------------------------------------\n";

if (function_exists('getExercisesByLevelSmart')) {
    echo "✅ SUCCÈS: Fonction existe\n";
    
    // Tester avec 6ème Mathématiques
    $exercises = getExercisesByLevelSmart('6ème', 'Mathématiques', 5);
    
    if (count($exercises) > 0) {
        echo "✅ SUCCÈS: " . count($exercises) . " exercices retournés\n";
        
        // Vérifier que le premier a un quality_score
        if (isset($exercises[0]['quality_score'])) {
            echo "✅ SUCCÈS: Tri par qualité activé (quality_score présent)\n";
        } else {
            echo "⚠️  ATTENTION: quality_score absent (tri non optimal)\n";
        }
    } else {
        echo "❌ ÉCHEC: Aucun exercice retourné\n";
        $allTestsPassed = false;
    }
} else {
    echo "❌ ÉCHEC: Fonction getExercisesByLevelSmart() n'existe pas\n";
    $allTestsPassed = false;
}
echo "\n";

// TEST 2: Vérifier api/get_exercises.php utilise SMART
echo "TEST 2: API get_exercises.php utilise SMART\n";
echo "------------------------------------------------------------\n";

$apiFile = __DIR__ . '/../api/get_exercises.php';
$apiContent = file_get_contents($apiFile);

if (strpos($apiContent, 'getExercisesByLevelSmart') !== false) {
    echo "✅ SUCCÈS: API utilise getExercisesByLevelSmart()\n\n";
} else {
    echo "❌ ÉCHEC: API n'utilise pas getExercisesByLevelSmart()\n";
    echo "   Vérifier ligne ~184 de api/get_exercises.php\n\n";
    $allTestsPassed = false;
}

// TEST 3: Vérifier quiz_generator.php utilise SMART
echo "TEST 3: quiz_generator.php utilise SMART\n";
echo "------------------------------------------------------------\n";

$quizFile = __DIR__ . '/../includes/quiz_generator.php';
$quizContent = file_get_contents($quizFile);

if (strpos($quizContent, 'getExercisesByLevelSmart') !== false) {
    echo "✅ SUCCÈS: Quiz utilise getExercisesByLevelSmart()\n\n";
} else {
    echo "❌ ÉCHEC: Quiz n'utilise pas getExercisesByLevelSmart()\n";
    echo "   Vérifier ligne ~945 de includes/quiz_generator.php\n\n";
    $allTestsPassed = false;
}

// TEST 4: Comparer SMART vs Standard
echo "TEST 4: Comparaison SMART vs Standard (Anglais 6ème)\n";
echo "------------------------------------------------------------\n";

$standardExercises = getExercisesByLevel('6ème', 'Anglais', 5);
$smartExercises = getExercisesByLevelSmart('6ème', 'Anglais', 5);

echo "Standard retourne: " . count($standardExercises) . " exercices\n";
echo "SMART retourne: " . count($smartExercises) . " exercices\n";

if (count($smartExercises) > 0) {
    $firstSmart = $smartExercises[0];
    $answerLength = strlen($firstSmart['Answer'] ?? '');
    echo "Premier exercice SMART:\n";
    echo "  Titre: {$firstSmart['Title']}\n";
    echo "  Longueur réponse: $answerLength caractères\n";
    
    if (isset($firstSmart['quality_score'])) {
        echo "  Quality score: {$firstSmart['quality_score']}/3\n";
    }
    
    if ($answerLength >= 30) {
        echo "✅ SUCCÈS: Réponse complète (≥30 car.)\n\n";
    } else {
        echo "❌ ÉCHEC: Réponse trop courte\n\n";
        $allTestsPassed = false;
    }
} else {
    echo "⚠️  ATTENTION: Aucun exercice SMART retourné\n\n";
}

// TEST 5: Vérifier ExerciseValidator disponible
echo "TEST 5: ExerciseValidator disponible\n";
echo "------------------------------------------------------------\n";

if (file_exists(__DIR__ . '/../includes/ExerciseValidator.php')) {
    require_once __DIR__ . '/../includes/ExerciseValidator.php';
    
    if (class_exists('ExerciseValidator')) {
        echo "✅ SUCCÈS: ExerciseValidator chargé\n";
        
        // Test rapide
        $testExercise = [
            'title' => 'Test',
            'content' => 'Question de test complète avec du contexte',
            'answer' => 'Réponse complète avec au moins 30 caractères pour passer la validation',
            'level' => '6ème',
            'subject' => 'Test'
        ];
        
        $validation = ExerciseValidator::validate($testExercise);
        if ($validation['valid']) {
            echo "✅ SUCCÈS: Validation fonctionne\n\n";
        } else {
            echo "❌ ÉCHEC: Validation ne fonctionne pas correctement\n\n";
            $allTestsPassed = false;
        }
    } else {
        echo "❌ ÉCHEC: Classe ExerciseValidator introuvable\n\n";
        $allTestsPassed = false;
    }
} else {
    echo "❌ ÉCHEC: Fichier ExerciseValidator.php introuvable\n\n";
    $allTestsPassed = false;
}

// TEST 6: Vérifier statistiques finales
echo "TEST 6: Statistiques finales de qualité\n";
echo "------------------------------------------------------------\n";

$stmt = $pdo->query("
    SELECT 
        Subject,
        COUNT(*) as total,
        AVG(LENGTH(Answer)) as avg_length,
        MIN(LENGTH(Answer)) as min_length
    FROM Exercises
    WHERE is_active = 1
    GROUP BY Subject
    HAVING COUNT(*) > 0
    ORDER BY avg_length DESC
    LIMIT 5
");

echo "Top 5 matières par qualité des réponses:\n\n";
$rank = 1;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $avgLen = round($row['avg_length'], 0);
    echo "$rank. " . str_pad($row['Subject'], 20) . " : ";
    echo "{$row['total']} exercices, moy: {$avgLen} car., min: {$row['min_length']} car.\n";
    $rank++;
}

echo "\n✅ SUCCÈS: Statistiques calculées\n\n";

// RÉSULTAT FINAL
echo "============================================================\n";
echo "📊 RÉSULTAT FINAL DE LA MIGRATION\n";
echo "============================================================\n\n";

if ($allTestsPassed) {
    echo "✅ ✅ ✅ MIGRATION RÉUSSIE ✅ ✅ ✅\n\n";
    echo "🎉 Le système SMART est maintenant actif!\n\n";
    echo "✨ Fonctionnalités activées:\n";
    echo "   • getExercisesByLevelSmart() - Sélection intelligente\n";
    echo "   • API get_exercises.php - Priorise meilleurs exercices\n";
    echo "   • Quiz - Questions de meilleure qualité\n";
    echo "   • ExerciseValidator - Protection anti-incohérence\n\n";
    echo "💡 Les utilisateurs verront maintenant les meilleurs exercices en premier!\n\n";
    exit(0);
} else {
    echo "❌ ❌ ❌ MIGRATION INCOMPLÈTE ❌ ❌ ❌\n\n";
    echo "⚠️  Certains tests ont échoué. Vérifiez les messages ci-dessus.\n\n";
    exit(1);
}
