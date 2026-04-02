#!/usr/bin/env php
<?php
/**
 * TEST FINAL COMPLET DU SYSTÈME ANTI-INCOHÉRENCE
 * Vérifie tous les composants:
 * - Validateur d'exercices
 * - Fonction getExercisesByLevelSmart()
 * - Exercices d'anglais activés
 * - Pas de réponses vides/incohérentes
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/ExerciseValidator.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

echo "🧪 TEST FINAL COMPLET - SYSTÈME ANTI-INCOHÉRENCE\n";
echo "============================================================\n\n";

$allTestsPassed = true;

// TEST 1: Vérifier qu'aucun exercice actif n'a de réponse vide
echo "TEST 1: Vérification réponses vides\n";
echo "------------------------------------------------------------\n";
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM Exercises 
    WHERE is_active = 1 
    AND (Answer IS NULL OR TRIM(Answer) = '' OR LENGTH(Answer) < 15)
");
$emptyAnswers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($emptyAnswers == 0) {
    echo "✅ SUCCÈS: Aucun exercice actif avec réponse vide/trop courte\n\n";
} else {
    echo "❌ ÉCHEC: $emptyAnswers exercices actifs ont des réponses vides/trop courtes\n\n";
    $allTestsPassed = false;
}

// TEST 2: Vérifier que les exercices d'anglais sont actifs
echo "TEST 2: Exercices d'Anglais actifs\n";
echo "------------------------------------------------------------\n";
$stmt = $pdo->query("
    SELECT COUNT(*) as count 
    FROM Exercises 
    WHERE Subject = 'Anglais' 
    AND is_active = 1
");
$activeEnglish = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($activeEnglish >= 25) {
    echo "✅ SUCCÈS: $activeEnglish exercices d'Anglais actifs (≥25)\n\n";
} else {
    echo "❌ ÉCHEC: Seulement $activeEnglish exercices d'Anglais actifs (attendu ≥25)\n\n";
    $allTestsPassed = false;
}

// TEST 3: Tester le validateur sur un exercice valide
echo "TEST 3: Validateur - Exercice valide\n";
echo "------------------------------------------------------------\n";
$validExercise = [
    'title' => 'Test - Le verbe être',
    'content' => 'Conjuguez le verbe être au présent: Je ___ étudiant.',
    'answer' => 'La réponse correcte est "suis". Le verbe être se conjugue ainsi: je suis, tu es, il/elle est, nous sommes, vous êtes, ils/elles sont.',
    'level' => '6ème',
    'subject' => 'Français'
];

$validation = ExerciseValidator::validate($validExercise);
if ($validation['valid']) {
    echo "✅ SUCCÈS: Exercice valide correctement détecté\n\n";
} else {
    echo "❌ ÉCHEC: Exercice valide rejeté\n";
    echo "   Erreurs: " . implode(', ', $validation['errors']) . "\n\n";
    $allTestsPassed = false;
}

// TEST 4: Tester le validateur sur un exercice invalide
echo "TEST 4: Validateur - Exercice invalide (réponse vide)\n";
echo "------------------------------------------------------------\n";
$invalidExercise = [
    'title' => 'Test invalide',
    'content' => 'Question test',
    'answer' => '',
    'level' => '6ème',
    'subject' => 'Test'
];

$validation = ExerciseValidator::validate($invalidExercise);
if (!$validation['valid']) {
    echo "✅ SUCCÈS: Exercice invalide correctement rejeté\n";
    echo "   Raison: " . $validation['errors'][0] . "\n\n";
} else {
    echo "❌ ÉCHEC: Exercice invalide accepté\n\n";
    $allTestsPassed = false;
}

// TEST 5: Tester getExercisesByLevelSmart() pour l'anglais
echo "TEST 5: Fonction getExercisesByLevelSmart() - Anglais 6ème\n";
echo "------------------------------------------------------------\n";
$smartExercises = getExercisesByLevelSmart('6ème', 'Anglais', 5);

if (count($smartExercises) >= 5) {
    echo "✅ SUCCÈS: " . count($smartExercises) . " exercices d'Anglais 6ème retournés\n";
    
    // Vérifier que le premier a une réponse complète
    $firstEx = $smartExercises[0];
    $answerLength = strlen($firstEx['Answer'] ?? '');
    
    if ($answerLength >= 50) {
        echo "✅ SUCCÈS: Premier exercice a une réponse complète ($answerLength caractères)\n";
        echo "   Titre: {$firstEx['Title']}\n\n";
    } else {
        echo "⚠️  ATTENTION: Premier exercice a une réponse courte ($answerLength caractères)\n\n";
    }
} else {
    echo "❌ ÉCHEC: Seulement " . count($smartExercises) . " exercices retournés (attendu ≥5)\n\n";
    $allTestsPassed = false;
}

// TEST 6: Vérifier distribution des matières actives
echo "TEST 6: Distribution des matières actives\n";
echo "------------------------------------------------------------\n";
$stmt = $pdo->query("
    SELECT Subject, COUNT(*) as count
    FROM Exercises
    WHERE is_active = 1
    GROUP BY Subject
    ORDER BY count DESC
");

$subjects = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $subjects[$row['Subject']] = $row['count'];
    echo str_pad($row['Subject'], 20) . ": {$row['count']} exercices\n";
}

// Vérifier que les matières principales sont présentes
$requiredSubjects = ['Mathématiques', 'Français', 'Anglais'];
$missingSubjects = [];

foreach ($requiredSubjects as $subject) {
    if (!isset($subjects[$subject]) || $subjects[$subject] == 0) {
        $missingSubjects[] = $subject;
    }
}

if (empty($missingSubjects)) {
    echo "\n✅ SUCCÈS: Toutes les matières principales sont présentes\n\n";
} else {
    echo "\n❌ ÉCHEC: Matières manquantes: " . implode(', ', $missingSubjects) . "\n\n";
    $allTestsPassed = false;
}

// TEST 7: Statistiques globales de qualité
echo "TEST 7: Statistiques globales de qualité\n";
echo "------------------------------------------------------------\n";
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        AVG(LENGTH(Answer)) as avg_answer_length,
        MIN(LENGTH(Answer)) as min_answer_length,
        MAX(LENGTH(Answer)) as max_answer_length
    FROM Exercises
    WHERE is_active = 1
");

$stats = $stmt->fetch(PDO::FETCH_ASSOC);
$avgLength = round($stats['avg_answer_length'], 0);

echo "Total exercices actifs: {$stats['total']}\n";
echo "Longueur moyenne réponse: $avgLength caractères\n";
echo "Longueur min: {$stats['min_answer_length']} caractères\n";
echo "Longueur max: {$stats['max_answer_length']} caractères\n";

if ($avgLength >= 200) {
    echo "\n✅ SUCCÈS: Longueur moyenne des réponses satisfaisante (≥200 car.)\n\n";
} else {
    echo "\n⚠️  ATTENTION: Longueur moyenne des réponses pourrait être améliorée\n\n";
}

// RÉSULTAT FINAL
echo "============================================================\n";
echo "📊 RÉSULTAT FINAL\n";
echo "============================================================\n\n";

if ($allTestsPassed) {
    echo "✅ ✅ ✅ TOUS LES TESTS RÉUSSIS ✅ ✅ ✅\n\n";
    echo "🎉 Le système anti-incohérence est opérationnel!\n";
    echo "💡 Utilisez ExerciseValidator::validate() avant toute insertion.\n";
    echo "💡 Utilisez getExercisesByLevelSmart() pour prioriser les meilleurs exercices.\n\n";
    exit(0);
} else {
    echo "❌ ❌ ❌ CERTAINS TESTS ONT ÉCHOUÉ ❌ ❌ ❌\n\n";
    echo "⚠️  Vérifiez les erreurs ci-dessus.\n\n";
    exit(1);
}
