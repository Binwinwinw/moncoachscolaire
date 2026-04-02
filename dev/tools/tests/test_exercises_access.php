<?php
/**
 * Script de test pour vérifier l'accès aux exercices
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🧪 Test d'accès aux exercices\n\n";

$levels = ['6ème', '3ème', 'Seconde', 'Première'];
$subjects = ['Mathématiques', 'Français'];

foreach ($levels as $level) {
    echo "📚 Niveau : $level\n";
    foreach ($subjects as $subject) {
        $exercises = getExercisesByLevel($level, $subject, 5);
        echo "   • $subject : " . count($exercises) . " exercice(s)\n";
        if (count($exercises) > 0) {
            echo "     - " . $exercises[0]['Title'] . "\n";
        }
    }
    echo "\n";
}

// Test sans filtre matière
echo "📊 Total par niveau (toutes matières) :\n";
foreach ($levels as $level) {
    $all = getExercisesByLevel($level, null, 100);
    echo "   • $level : " . count($all) . " exercice(s)\n";
}

echo "\n✅ Tests terminés !\n";

