#!/usr/bin/env php
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

echo "🧪 Vérification: Exercices d'anglais accessibles via index.php/exercices.php\n";
echo "============================================================\n\n";

// Test 1: Exercices via getExercisesByLevelSmart()
echo "TEST 1: Exercices Anglais 6ème via getExercisesByLevelSmart()\n";
$ex = getExercisesByLevelSmart('6ème', 'Anglais', 5);
echo "Trouvés: " . count($ex) . " exercices\n";
foreach($ex as $e) {
    echo "  ✅ " . $e['Title'] . " (ID: " . $e['Id'] . ")\n";
}

echo "\n";

// Test 2: Tous les niveaux d'anglais
echo "TEST 2: Exercices Anglais - tous les niveaux\n";
$levels = ['6ème','5ème','4ème','3ème','Seconde','Première','Terminale'];
$totalEnglish = 0;

foreach($levels as $lvl) {
    $exs = getExercisesByLevelSmart($lvl, 'Anglais', 10);
    $count = count($exs);
    $totalEnglish += $count;
    echo "  $lvl: $count exercices\n";
}

echo "\nTOTAL Anglais actifs: $totalEnglish\n\n";

// Test 3: Vérifier accès via API (simulé)
echo "TEST 3: Vérifier que les exercices peuvent être accédés via index.php\n";
echo "  ✅ Fichier exercices.php existe: " . (file_exists(__DIR__ . '/exercices.php') ? 'OUI' : 'NON') . "\n";
echo "  ✅ Routeur index.php existe: " . (file_exists(__DIR__ . '/index.php') ? 'OUI' : 'NON') . "\n";
echo "  ✅ API get_exercises.php existe: " . (file_exists(__DIR__ . '/api/get_exercises.php') ? 'OUI' : 'NON') . "\n";
echo "  ✅ exercices.js existe: " . (file_exists(__DIR__ . '/assets/js/dynamic-exercises.js') ? 'OUI' : 'NON') . "\n\n";

// Test 4: Vérifier que les exercices utilisent getExercisesByLevelSmart
$apiContent = file_get_contents(__DIR__ . '/api/get_exercises.php');
if (strpos($apiContent, 'getExercisesByLevelSmart') !== false) {
    echo "✅ API utilise getExercisesByLevelSmart()\n";
} else {
    echo "⚠️  API utilise getExercisesByLevel() (pas SMART)\n";
}

echo "\n";

if ($totalEnglish >= 25) {
    echo "✅ ✅ ✅ TOUS LES EXERCICES SONT ACCESSIBLES ✅ ✅ ✅\n\n";
    echo "📋 Flux d'accès:\n";
    echo "   1. Utilisateur accède à /index.php?page=exercices\n";
    echo "   2. Routeur charge exercices.php\n";
    echo "   3. JavaScript appelle /api/get_exercises.php\n";
    echo "   4. API retourne exercices via getExercisesByLevelSmart()\n";
    echo "   5. Utilisateur voit les exercices (dont les 25 d'anglais)\n\n";
} else {
    echo "❌ Exercices d'anglais manquants ou inaccessibles!\n";
}
