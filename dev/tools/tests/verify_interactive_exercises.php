<?php
/**
 * Script de vérification pour confirmer que les exercices interactifs sont bien générés
 * 
 * Usage: php dev/verify_interactive_exercises.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== VÉRIFICATION DES EXERCICES INTERACTIFS ===\n\n";

// 1. Vérifier que le fichier générateur existe
$generatorFile = __DIR__ . '/../includes/exercise_interactive_generator.php';
if (file_exists($generatorFile)) {
    echo "✅ Fichier générateur trouvé: exercise_interactive_generator.php\n";
} else {
    echo "❌ Fichier générateur manquant: exercise_interactive_generator.php\n";
    exit(1);
}

// 2. Vérifier que les fonctions principales existent
require_once $generatorFile;
$requiredFunctions = [
    'generateQualityInteractiveExercise',
    'detectQualityExerciseType',
    'extractQCMQuestions',
    'generateQualityMathExercise',
    'generateQualityConjugationExercise'
];

echo "\n=== Vérification des fonctions ===\n";
foreach ($requiredFunctions as $func) {
    if (function_exists($func)) {
        echo "✅ Fonction '$func' existe\n";
    } else {
        echo "❌ Fonction '$func' manquante\n";
    }
}

// 3. Tester la génération pour différents types d'exercices
echo "\n=== Tests de génération ===\n";

// Test QCM
$qcmExercise = [
    'Id' => 1,
    'Subject' => 'Français',
    'Level' => '3ème',
    'Title' => 'Test QCM',
    'Content' => 'Quelle est la capitale de la France ? Paris, Londres, Berlin, Madrid.',
    'Answer' => 'Paris'
];
$qcmResult = generateQualityInteractiveExercise($qcmExercise, 'Français');
if ($qcmResult && strpos($qcmResult, 'qcm-exercise') !== false) {
    echo "✅ QCM généré correctement\n";
} else {
    echo "❌ Échec génération QCM\n";
}

// Test Math
$mathExercise = [
    'Id' => 2,
    'Subject' => 'Mathématiques',
    'Level' => '3ème',
    'Title' => 'Test Math',
    'Content' => 'Calcule : 15 + 27 = ?',
    'Answer' => '42'
];
$mathResult = generateQualityInteractiveExercise($mathExercise, 'Mathématiques');
if ($mathResult && strpos($mathResult, 'math-exercise') !== false) {
    echo "✅ Math généré correctement\n";
} else {
    echo "❌ Échec génération Math\n";
}

// Test Conjugation
$conjExercise = [
    'Id' => 3,
    'Subject' => 'Français',
    'Level' => '3ème',
    'Title' => 'Test Conjugaison',
    'Content' => 'Je ____ au marché. Nous ____ fatigués.',
    'Answer' => 'vais. sommes.'
];
$conjResult = generateQualityInteractiveExercise($conjExercise, 'Français');
if ($conjResult && strpos($conjResult, 'conjugation-exercise') !== false) {
    echo "✅ Conjugaison générée correctement\n";
} else {
    echo "❌ Échec génération Conjugaison\n";
}

// 4. Vérifier que la page 3ème utilise bien le générateur
echo "\n=== Vérification de l'intégration ===\n";
$pageFile = __DIR__ . '/../pages/college/3eme/exercices-3eme.php';
if (file_exists($pageFile)) {
    $pageContent = file_get_contents($pageFile);
    if (strpos($pageContent, "'interactive' => true") !== false) {
        echo "✅ Page 3ème configure l'interactivité\n";
    } else {
        echo "⚠️  Page 3ème: 'interactive' => true non trouvé\n";
    }
    
    if (strpos($pageContent, 'interactive-exercises.js') !== false) {
        echo "✅ Script JavaScript chargé\n";
    } else {
        echo "❌ Script JavaScript non trouvé\n";
    }
    
    if (strpos($pageContent, 'InteractiveExercises.initAll') !== false) {
        echo "✅ Initialisation JavaScript présente\n";
    } else {
        echo "❌ Initialisation JavaScript manquante\n";
    }
} else {
    echo "❌ Page 3ème non trouvée\n";
}

// 5. Vérifier exercice_card.php
echo "\n=== Vérification du composant ===\n";
$cardFile = __DIR__ . '/../includes/exercice_card.php';
if (file_exists($cardFile)) {
    $cardContent = file_get_contents($cardFile);
    if (strpos($cardContent, 'generateQualityInteractiveExercise') !== false) {
        echo "✅ exercice_card.php utilise le nouveau générateur\n";
    } else {
        echo "❌ exercice_card.php n'utilise pas le nouveau générateur\n";
    }
} else {
    echo "❌ exercice_card.php non trouvé\n";
}

echo "\n=== RÉSUMÉ ===\n";
echo "Si tous les tests montrent ✅, les exercices interactifs sont correctement configurés.\n";
echo "Vous pouvez maintenant tester sur: http://localhost/moncoachscolaire/index.php?page=college%2F3eme%2Fexercices-3eme\n";
echo "\nPour vérifier dans le navigateur:\n";
echo "1. Ouvrez la console (F12)\n";
echo "2. Vérifiez qu'il n'y a pas d'erreurs JavaScript\n";
echo "3. Vérifiez que les exercices affichent des boutons interactifs\n";
echo "4. Testez un exercice pour confirmer l'interactivité\n";
?>

