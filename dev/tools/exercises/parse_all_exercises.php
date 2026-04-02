<?php
/**
 * dev/tools/exercises/parse_all_exercises.php
 * Parse TOUS les exercices de la base
 */

require_once __DIR__ . '/../../../src/database/connection.php';
require_once __DIR__ . '/../../../src/utils/ExerciseParser.php';

echo "🚀 PARSING COMPLET DE TOUS LES EXERCICES\n";
echo str_repeat("=", 70) . "\n\n";

$parser = new ExerciseParser($pdo);

// Demander confirmation
echo "⚠️  Cette opération va parser les 1122 exercices non parsés\n";
echo "Temps estimé : ~1 minute\n\n";
echo "Continuer ? (oui/non) : ";

$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);

if (strtolower($line) !== 'oui') {
    echo "❌ Annulé\n";
    exit;
}

echo "\n🔄 Parsing en cours...\n\n";

$startTime = microtime(true);

// Parser tous les exercices
$stats = $parser->parseAllExercises();

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

// Afficher résultats
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 RÉSULTATS DU PARSING\n";
echo str_repeat("=", 70) . "\n\n";

printf("⏱️  Temps d'exécution : %.2f secondes\n\n", $duration);

printf("📚 Total d'exercices traités : %d\n", $stats['total']);
printf("✅ Parsés avec succès        : %d (%.1f%%)\n",
    $stats['success'],
    $stats['total'] > 0 ? ($stats['success']/$stats['total'])*100 : 0
);
printf("❌ Erreurs                    : %d\n", $stats['errors']);
printf("⚠️  Avertissements            : %d\n\n", $stats['warnings']);

printf("📝 Exercices simples          : %d\n", $stats['simple']);
printf("🔢 Exercices multi-parties    : %d\n", $stats['multi_parties']);

// Afficher les erreurs s'il y en a
if (!empty($stats['details'])) {
    echo "\n\n❌ DÉTAILS DES ERREURS\n";
    echo str_repeat("-", 70) . "\n";

    foreach (array_slice($stats['details'], 0, 10) as $detail) {
        echo "ID {$detail['id']} : ";
        if (isset($detail['errors'])) {
            echo implode(', ', $detail['errors']);
        } elseif (isset($detail['exception'])) {
            echo $detail['exception'];
        }
        echo "\n";
    }

    if (count($stats['details']) > 10) {
        echo "... et " . (count($stats['details']) - 10) . " autre(s)\n";
    }
}

echo "\n✅ Parsing terminé !\n";
echo "💡 Vérifie avec : php dev/tools/exercises/count_exercises.php\n";
