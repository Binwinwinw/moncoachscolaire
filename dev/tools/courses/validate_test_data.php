<?php
/**
 * Script de validation des données de test
 * Vérifie la structure JSON et la diversité des exercices
 */

// Déterminer le chemin correct selon où le script est exécuté
$possiblePaths = [
    'dev/data/test_20_courses.json',  // Si exécuté depuis la racine
    '../../../dev/data/test_20_courses.json',  // Si exécuté depuis dev/tools/courses
    '../../data/test_20_courses.json'  // Si exécuté depuis dev/tools
];

$file = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $file = $path;
        break;
    }
}

if ($file === null) {
    die("❌ Fichier introuvable. Chemins testés:\n" . implode("\n", $possiblePaths) . "\n");
}

echo "✓ Fichier trouvé: $file\n\n";

// Charger et valider le JSON
$jsonContent = file_get_contents($file);
$data = json_decode($jsonContent, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ Erreur JSON: " . json_last_error_msg() . "\n");
}

echo str_repeat("=", 60) . "\n";
echo "TEST 1: STRUCTURE JSON DE BASE\n";
echo str_repeat("=", 60) . "\n";

echo "✓ JSON valide et chargé avec succès\n";
echo "✓ Clé 'courses' présente: " . (isset($data['courses']) ? "OUI" : "NON") . "\n";
echo "✓ Nombre total de cours: " .  count($data['courses']) . "\n\n";

// Test 2: Nombre d'exercices par cours
echo str_repeat("=", 60) . "\n";
echo "TEST 2: NOMBRE D'EXERCICES PAR COURS\n";
echo str_repeat("=", 60) . "\n";

$exercisesCounts = [];
$totalExercises = 0;

foreach ($data['courses'] as $course) {
    $courseTitle = mb_substr($course['title'], 0, 50);
    $numExercises = count($course['exercises'] ?? []);
    $exercisesCounts[$courseTitle] = $numExercises;
    $totalExercises += $numExercises;
}

$min = min($exercisesCounts);
$max = max($exercisesCounts);
$avg = $totalExercises / count($data['courses']);

echo "Cours avec le moins d'exercices: $min\n";
echo "Cours avec le plus d'exercices: $max\n";
echo sprintf("Moyenne d'exercices par cours: %.2f\n\n", $avg);

echo "Exemples de cours:\n";
$count = 0;
foreach ($exercisesCounts as $title => $num) {
    if (++$count > 5) break;
    echo "  $count. $title: $num exercices\n";
}
echo "\n";

// Test 3: Types d'exercices par niveau scolaire
echo str_repeat("=", 60) . "\n";
echo "TEST 3: TYPES D'EXERCICES PAR NIVEAU SCOLAIRE\n";
echo str_repeat("=", 60) . "\n";

$levelExercises = [];
$allExerciseTypes = [];

foreach ($data['courses'] as $course) {
    $level = $course['level'] ?? 'Inconnu';

    if (!isset($levelExercises[$level])) {
        $levelExercises[$level] = [];
    }

    foreach ($course['exercises'] ?? [] as $exercise) {
        $type = $exercise['type'] ?? 'unknown';
        $levelExercises[$level][] = $type;
        $allExerciseTypes[] = $type;
    }
}

// Analyser par niveau
ksort($levelExercises);
foreach ($levelExercises as $level => $types) {
    $uniqueTypes = array_unique($types);
    $typeCounts = array_count_values($types);

    echo "\n$level:\n";
    echo "  Total exercices: " . count($types) . "\n";
    echo "  Types différents: " . count($uniqueTypes) . "\n";
    echo "  Répartition:\n";

    ksort($typeCounts);
    foreach ($typeCounts as $type => $count) {
        $percentage = ($count / count($types)) * 100;
        echo sprintf("    - %s: %d (%.1f%%)\n", $type, $count, $percentage);
    }
}

// Résumé global
echo "\n" . str_repeat("=", 60) . "\n";
echo "RÉSUMÉ GLOBAL DES TYPES D'EXERCICES\n";
echo str_repeat("=", 60) . "\n";

$uniqueAll = array_unique($allExerciseTypes);
$allTypeCounts = array_count_values($allExerciseTypes);

echo "Types d'exercices utilisés: " . implode(", ", $uniqueAll) . "\n";
echo "Total exercices: " . count($allExerciseTypes) . "\n\n";

ksort($allTypeCounts);
foreach ($allTypeCounts as $type => $count) {
    $percentage = ($count / count($allExerciseTypes)) * 100;
    echo sprintf("  - %s: %d (%.1f%%)\n", $type, $count, $percentage);
}

echo "\n✅ Validation terminée avec succès!\n";
// Fonctions utilitaires pour la correction HTML
function textToHtmlList($text) {
    $items = preg_split('/\r?\n/', trim($text));
    $html = "<ul>\n";
    foreach ($items as $item) {
        $html .= "  <li>" . htmlspecialchars(trim($item)) . "</li>\n";
    }
    $html .= "</ul>";
    return $html;
}
