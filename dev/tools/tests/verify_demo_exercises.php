<?php
/**
 * Script pour vérifier que le compte démo a accès aux exercices
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🧪 Vérification des exercices pour le compte démo\n\n";

// Fonction de normalisation (copiée de demo.php)
function normalizeLevelForDB($level) {
    $variants = [
        '6ème' => ['6ème', '6eme', '6eme'],
        '5ème' => ['5ème', '5eme', '5eme'],
        '4ème' => ['4ème', '4eme', '4eme'],
        '3ème' => ['3ème', '3eme', '3eme'],
        'Seconde' => ['Seconde', 'seconde'],
        'Première' => ['Première', 'Premiere', 'premiere'],
        'Terminale' => ['Terminale', 'terminale']
    ];
    
    foreach ($variants as $standard => $alt) {
        if (in_array($level, $alt)) {
            return $standard;
        }
    }
    return $level;
}

// Tester avec différents niveaux
$demoLevels = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale'];

echo "📊 Exercices disponibles pour chaque niveau (comme dans la page démo) :\n\n";

foreach ($demoLevels as $level) {
    $normalizedLevel = normalizeLevelForDB($level);
    $levelVariants = [$normalizedLevel, $level];
    
    // Ajouter variantes sans accent
    if (mb_strpos($normalizedLevel, 'ème') !== false) {
        $levelVariants[] = str_replace('ème', 'eme', $normalizedLevel);
    }
    
    $exercises = [];
    foreach ($levelVariants as $variant) {
        $found = getExercisesByLevel($variant, null, 10);
        if (!empty($found)) {
            $exercises = $found;
            break;
        }
    }
    
    echo "  🎓 $level : " . count($exercises) . " exercice(s) disponible(s)\n";
    if (count($exercises) > 0) {
        echo "     Premier exercice : " . $exercises[0]['Title'] . "\n";
        echo "     Matière : " . ($exercises[0]['Subject'] ?? 'N/A') . "\n";
    } else {
        echo "     ⚠️  Aucun exercice trouvé\n";
    }
    echo "\n";
}

echo "✅ Vérification terminée !\n";

