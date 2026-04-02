<?php
// Test : vérification que les exercices se chargent correctement après nettoyage
require_once dirname(__DIR__) . '/src/database/connection.php';
require_once dirname(__DIR__) . '/src/includes/exercice_loader.php';

echo "🧪 Test de chargement des exercices par niveau\n";
echo "============================================\n\n";

$levels = ['6eme', '5eme', '4eme', '3eme', '2nde', '1ere', 'Terminale'];

foreach ($levels as $level) {
    $exercises = getExercisesByLevel($level, null, 5); // Récupérer les 5 premiers
    $count = is_array($exercises) ? count($exercises) : 0;
    echo "✓ $level: $count exercices trouvés\n";
}

echo "\n✅ Test de chargement terminé!\n";
?>
