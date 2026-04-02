#!/usr/bin/env php
<?php
/**
 * TEST DE VÉRIFICATION - Exercices désactivés
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

echo "🧪 TEST DE VÉRIFICATION - Exercices désactivés\n";
echo "============================================================\n\n";

// Test 1: Vérifier que les exercices d'Anglais sont marqués inactifs
echo "TEST 1: Vérification état des exercices d'Anglais\n";
echo "------------------------------------------------------------\n";
$stmt = $pdo->query("SELECT Id, Title, Subject, Level, is_active FROM Exercises WHERE Subject = 'Anglais'");
$anglaisExercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($anglaisExercises) > 0) {
    echo "✅ Exercices d'Anglais trouvés en base: " . count($anglaisExercises) . "\n";
    foreach ($anglaisExercises as $ex) {
        $status = $ex['is_active'] == 1 ? '❌ ACTIF (PROBLÈME!)' : '✅ INACTIF (OK)';
        echo "   - ID {$ex['Id']}: {$ex['Title']} [{$ex['Level']}] - $status\n";
    }
} else {
    echo "ℹ️  Aucun exercice d'Anglais en base\n";
}
echo "\n";

// Test 2: Vérifier que getExercisesByLevel ne retourne pas d'Anglais
echo "TEST 2: getExercisesByLevel('6ème', 'Anglais')\n";
echo "------------------------------------------------------------\n";
$exercises = getExercisesByLevel('6ème', 'Anglais');
if (count($exercises) === 0) {
    echo "✅ SUCCÈS: Aucun exercice d'Anglais retourné pour la 6ème\n";
} else {
    echo "❌ ÉCHEC: " . count($exercises) . " exercice(s) d'Anglais retourné(s)\n";
    foreach ($exercises as $ex) {
        echo "   - ID {$ex['Id']}: {$ex['Title']}\n";
    }
}
echo "\n";

// Test 3: Vérifier qu'un exercice d'Anglais spécifique n'est pas accessible
echo "TEST 3: getExerciseById(184) - Exercice d'Anglais 6ème\n";
echo "------------------------------------------------------------\n";
$exercise = getExerciseById(184);
if ($exercise === null) {
    echo "✅ SUCCÈS: Exercice d'Anglais ID 184 non accessible\n";
} else {
    echo "❌ ÉCHEC: Exercice d'Anglais ID 184 accessible!\n";
    echo "   Titre: {$exercise['Title']}\n";
    echo "   is_active: " . ($exercise['is_active'] ?? 'NULL') . "\n";
}
echo "\n";

// Test 4: Vérifier que les exercices actifs sont bien retournés
echo "TEST 4: Vérification exercices ACTIFS (Mathématiques 6ème)\n";
echo "------------------------------------------------------------\n";
$mathExercises = getExercisesByLevel('6ème', 'Mathématiques', 5);
if (count($mathExercises) > 0) {
    echo "✅ SUCCÈS: " . count($mathExercises) . " exercice(s) de Maths retourné(s)\n";
    foreach ($mathExercises as $ex) {
        echo "   - ID {$ex['Id']}: " . substr($ex['Title'], 0, 50) . "...\n";
    }
} else {
    echo "❌ PROBLÈME: Aucun exercice de Maths retourné\n";
}
echo "\n";

// Test 5: Compter les exercices actifs vs inactifs
echo "TEST 5: Statistiques globales\n";
echo "------------------------------------------------------------\n";
$stmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as actifs,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactifs
FROM Exercises");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Total exercices: {$stats['total']}\n";
echo "✅ Actifs: {$stats['actifs']} (" . round(($stats['actifs']/$stats['total'])*100, 1) . "%)\n";
echo "❌ Inactifs: {$stats['inactifs']} (" . round(($stats['inactifs']/$stats['total'])*100, 1) . "%)\n";
echo "\n";

// Test 6: Vérifier les matières actives
echo "TEST 6: Matières disponibles (actives uniquement)\n";
echo "------------------------------------------------------------\n";
$stmt = $pdo->query("SELECT DISTINCT Subject FROM Exercises WHERE is_active = 1 ORDER BY Subject");
$activeSubjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Matières actives (" . count($activeSubjects) . "):\n";
foreach ($activeSubjects as $subject) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Exercises WHERE Subject = ? AND is_active = 1");
    $stmt->execute([$subject]);
    $count = $stmt->fetchColumn();
    echo "   ✅ $subject: $count exercices\n";
}
echo "\n";

// Test 7: Vérifier les matières inactives
echo "TEST 7: Matières inactives\n";
echo "------------------------------------------------------------\n";
$stmt = $pdo->query("SELECT DISTINCT Subject FROM Exercises WHERE is_active = 0 ORDER BY Subject");
$inactiveSubjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (count($inactiveSubjects) > 0) {
    echo "Matières inactives (" . count($inactiveSubjects) . "):\n";
    foreach ($inactiveSubjects as $subject) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Exercises WHERE Subject = ? AND is_active = 0");
        $stmt->execute([$subject]);
        $count = $stmt->fetchColumn();
        echo "   🔴 $subject: $count exercices\n";
    }
} else {
    echo "ℹ️  Aucune matière complètement inactive\n";
}
echo "\n";

echo "============================================================\n";
echo "📊 RÉSULTAT FINAL\n";
echo "============================================================\n\n";

$allTestsPassed = (
    count($anglaisExercises) > 0 && $anglaisExercises[0]['is_active'] == 0 &&
    count($exercises) === 0 &&
    $exercise === null &&
    count($mathExercises) > 0 &&
    $stats['inactifs'] == 6
);

if ($allTestsPassed) {
    echo "✅ TOUS LES TESTS RÉUSSIS!\n";
    echo "   - Les exercices d'Anglais sont bien inactifs\n";
    echo "   - Les utilisateurs ne peuvent plus y accéder\n";
    echo "   - Les exercices prioritaires fonctionnent correctement\n";
} else {
    echo "⚠️  ATTENTION: Certains tests ont échoué\n";
    echo "   Vérifier les détails ci-dessus\n";
}
echo "\n";
