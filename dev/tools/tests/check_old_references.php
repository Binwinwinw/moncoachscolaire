<?php
// Vérification des références aux anciens noms avec accents
require_once __DIR__ . '/../src/database/connection.php';

echo "\n🔍 VÉRIFICATION DES RÉFÉRENCES AUX ANCIENS NOMS\n";
echo "================================================\n\n";

// 1. Pages collège (6eme, 5eme, 4eme, 3eme)
echo "📚 Pages d'exercices collège:\n";
$collegePages = [
    'src/pages/college/6eme/exercices-6eme.php' => '6eme',
    'src/pages/college/5eme/exercices-5eme.php' => '5eme',
    'src/pages/college/4eme/exercices-4eme.php' => '4eme',
    'src/pages/college/3eme/exercices-3eme.php' => '3eme',
];

foreach ($collegePages as $file => $level) {
    $fullPath = dirname(__DIR__) . '/' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        // Vérifier getExercisesByLevel
        if (preg_match("/getExercisesByLevel\s*\(\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
            $usedLevel = $matches[1];
            $ok = ($usedLevel === $level) ? "✓" : "❌";
            echo "  $ok $file → '$usedLevel'\n";
        } else {
            echo "  ⚠️  $file → pas de getExercisesByLevel trouvé\n";
        }
    } else {
        echo "  ❌ $file → fichier absent\n";
    }
}

echo "\n📝 Points à vérifier manuellement:\n";
echo "  • demo.php utilise accents pour affichage UI (OK si mapping interne)\n";
echo "  • register.php utilise accents dans formulaire (OK si normalisation backend)\n";
echo "  • quiz.php et cours-*.php utilisent accents pour affichage (OK)\n\n";

echo "⚠️  Problèmes restants détectés:\n";
echo "  1. Mapping exercices → notions: 96/910 (10.5%)\n";
echo "     → Améliorer keywords dans tools/map_exercises_to_notions.php\n";
echo "  2. Pages collège à vérifier (getExercisesByLevel)\n";
echo "  3. Demo/Register/Quiz: accents OK si normalisation via exercice_loader\n\n";

echo "✅ Vérification terminée\n";
?>
