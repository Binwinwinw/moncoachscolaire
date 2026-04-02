<?php
/**
 * Script de test pour vérifier que les exercices interactifs sont bien générés
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/exercice_card.php';

echo "<h1>Test de génération d'exercices interactifs</h1>\n";

// Test 1 : Exercice QCM Français
echo "<h2>Test 1 : QCM Français</h2>\n";
$exercise1 = [
    'Id' => 1,
    'Title' => 'Analyse de texte',
    'Content' => 'Quel est le mouvement dominant de ce texte ? Le texte est méditatif et contemplatif.',
    'Answer' => 'Méditatif et contemplatif',
    'Subject' => 'Français'
];

$result1 = generateInteractiveExercise($exercise1, 'Français');
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
echo $result1;
echo "</div>\n";
echo "<p>✅ Contient 'qcm-exercise': " . (strpos($result1, 'qcm-exercise') !== false ? 'OUI' : 'NON') . "</p>\n";
echo "<p>✅ Contient 'data-questions': " . (strpos($result1, 'data-questions') !== false ? 'OUI' : 'NON') . "</p>\n";

// Test 2 : Exercice Mathématiques
echo "<h2>Test 2 : Mathématiques</h2>\n";
$exercise2 = [
    'Id' => 2,
    'Title' => 'Calcul mental',
    'Content' => 'Calcule : 15 + 27 = ?',
    'Answer' => '42',
    'Subject' => 'Mathématiques'
];

$result2 = generateInteractiveExercise($exercise2, 'Mathématiques');
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
echo $result2;
echo "</div>\n";
echo "<p>✅ Contient 'math-exercise': " . (strpos($result2, 'math-exercise') !== false ? 'OUI' : 'NON') . "</p>\n";

// Test 3 : Exercice à compléter
echo "<h2>Test 3 : Exercice à compléter (Français)</h2>\n";
$exercise3 = [
    'Id' => 3,
    'Title' => 'Conjugaison',
    'Content' => 'Je ____ (aller) à l\'école. Nous ____ (être) contents.',
    'Answer' => 'vais\nsommes',
    'Subject' => 'Français'
];

$result3 = generateInteractiveExercise($exercise3, 'Français');
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
echo $result3;
echo "</div>\n";
echo "<p>✅ Contient 'conjugation-exercise': " . (strpos($result3, 'conjugation-exercise') !== false ? 'OUI' : 'NON') . "</p>\n";

// Test 4 : Exercice Histoire-Géo
echo "<h2>Test 4 : Histoire-Géo QCM</h2>\n";
$exercise4 = [
    'Id' => 4,
    'Title' => 'La Première Guerre mondiale',
    'Content' => 'En quelle année a commencé la Première Guerre mondiale ?',
    'Answer' => '1914',
    'Subject' => 'Histoire'
];

$result4 = generateInteractiveExercise($exercise4, 'Histoire');
echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
echo $result4;
echo "</div>\n";
echo "<p>✅ Contient 'qcm-exercise': " . (strpos($result4, 'qcm-exercise') !== false ? 'OUI' : 'NON') . "</p>\n";

// Test 5 : Vérifier le format JSON
echo "<h2>Test 5 : Vérification du format JSON</h2>\n";
if (preg_match("/data-questions='(.*?)'/", $result1, $matches)) {
    $json = $matches[1];
    $decoded = json_decode($json, true);
    if ($decoded !== null) {
        echo "<p>✅ JSON valide</p>\n";
        echo "<pre>" . print_r($decoded, true) . "</pre>\n";
    } else {
        echo "<p>❌ JSON invalide</p>\n";
        echo "<p>JSON brut: " . htmlspecialchars($json) . "</p>\n";
    }
} else {
    echo "<p>❌ Aucun attribut data-questions trouvé</p>\n";
}

echo "<h2>Résumé</h2>\n";
echo "<p>Si tous les tests montrent ✅, les exercices sont bien générés en format interactif.</p>\n";

