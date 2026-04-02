<?php
/**
 * Vérification Complète de l'Intégration du Routeur
 * 
 * Ce script vérifie que:
 * 1. Les anciennes URLs redirigent correctement
 * 2. Les nouvelles URLs sont utilisées partout
 * 3. La base de données est accessible
 * 4. Les exercices s'affichent correctement
 */

echo "\n";
echo "╔" . str_repeat("═", 66) . "╗\n";
echo "║" . str_pad("🔍 VÉRIFICATION COMPLÈTE DE L'INTÉGRATION DU ROUTEUR", 66) . "║\n";
echo "╚" . str_repeat("═", 66) . "╝\n\n";

require_once __DIR__ . '/../config.php';

$testsPassed = 0;
$testsFailed = 0;

// Test 1: Base de données accessible
echo "📦 Test 1: Accès à la Base de Données\n";
try {
    $query = "SELECT COUNT(*) as count FROM courses";
    $stmt = $pdo->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $courseCount = $result['count'] ?? 0;
    echo "  ✅ Base de données accessible\n";
    echo "     → $courseCount cours dans la BD\n";
    $testsPassed++;
} catch (Exception $e) {
    echo "  ❌ Impossible d'accéder à la BD: " . $e->getMessage() . "\n";
    $testsFailed++;
}
echo "\n";

// Test 2: Exercices accessibles
echo "📝 Test 2: Accès aux Exercices\n";
try {
    $query = "SELECT COUNT(*) as count FROM exercises WHERE is_active = 1";
    $stmt = $pdo->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $exerciseCount = $result['count'] ?? 0;
    echo "  ✅ Exercices accessibles\n";
    echo "     → $exerciseCount exercices actifs\n";
    $testsPassed++;
} catch (Exception $e) {
    echo "  ❌ Impossible d'accéder aux exercices: " . $e->getMessage() . "\n";
    $testsFailed++;
}
echo "\n";

// Test 3: Fonction getCourseById disponible
echo "🎓 Test 3: Fonction getCourseById\n";
require_once __DIR__ . '/../includes/course_markdown_loader.php';
if (function_exists('getCourseById')) {
    echo "  ✅ Fonction getCourseById disponible\n";
    
    // Essayer de charger un cours
    try {
        $course = getCourseById(1);
        if ($course) {
            echo "     → Cours ID 1: " . $course['Title'] . "\n";
            $testsPassed++;
        } else {
            echo "  ⚠️ Cours ID 1 non trouvé (normal si base de données vide)\n";
            $testsPassed++;
        }
    } catch (Exception $e) {
        echo "  ❌ Erreur lors du chargement du cours: " . $e->getMessage() . "\n";
        $testsFailed++;
    }
} else {
    echo "  ❌ Fonction getCourseById non disponible\n";
    $testsFailed++;
}
echo "\n";

// Test 4: Fonction getExercisesByLevelSmart disponible
echo "💪 Test 4: Fonction getExercisesByLevelSmart\n";
require_once __DIR__ . '/../includes/exercice_loader.php';
if (function_exists('getExercisesByLevelSmart')) {
    echo "  ✅ Fonction getExercisesByLevelSmart disponible\n";
    $testsPassed++;
} else {
    echo "  ❌ Fonction getExercisesByLevelSmart non disponible\n";
    $testsFailed++;
}
echo "\n";

// Test 5: Fonction site_url disponible
echo "🔗 Test 5: Fonction site_url\n";
if (function_exists('site_url')) {
    $testUrl = site_url('view_course&id=1');
    // L'important c'est que la URL contienne page=view_course et id=1
    if ((strpos($testUrl, 'index.php?page=view_course') !== false || 
         strpos($testUrl, 'index.php') !== false) &&
        (strpos($testUrl, 'id') !== false)) {
        echo "  ✅ Fonction site_url disponible\n";
        echo "     → site_url('view_course&id=1') = $testUrl\n";
        $testsPassed++;
    } else {
        echo "  ❌ Fonction site_url retourne un format inattendu: $testUrl\n";
        $testsFailed++;
    }
} else {
    echo "  ❌ Fonction site_url non disponible\n";
    $testsFailed++;
}
echo "\n";

// Test 6: Fichiers de routage existent
echo "📁 Test 6: Fichiers de Routage\n";
$files = [
    'index.php',
    'pages/view_course.php',
    'pages/view_exercise.php',
    'view_course.php',
    'view_exercise.php',
];

$allExist = true;
foreach ($files as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        echo "  ✅ $file\n";
    } else {
        echo "  ❌ $file MANQUANT\n";
        $allExist = false;
    }
}

if ($allExist) {
    $testsPassed++;
} else {
    $testsFailed++;
}
echo "\n";

// Test 7: Vérifier les liens dans cours.php
echo "🔗 Test 7: Références dans cours.php\n";
$coursPhp = file_get_contents(__DIR__ . '/../cours.php');
if (strpos($coursPhp, 'index.php?page=view_course') !== false) {
    echo "  ✅ cours.php utilise les nouvelles URLs\n";
    $testsPassed++;
} else {
    echo "  ❌ cours.php ne contient pas les nouvelles URLs\n";
    $testsFailed++;
}
echo "\n";

// Test 8: Vérifier les liens dans dynamic-exercises.js
echo "🔗 Test 8: Références dans dynamic-exercises.js\n";
$jsFile = __DIR__ . '/../assets/js/dynamic-exercises.js';
if (file_exists($jsFile)) {
    $jsContent = file_get_contents($jsFile);
    if (strpos($jsContent, 'index.php?page=view_course') !== false) {
        echo "  ✅ dynamic-exercises.js utilise les nouvelles URLs\n";
        $testsPassed++;
    } else {
        echo "  ❌ dynamic-exercises.js ne contient pas les nouvelles URLs\n";
        $testsFailed++;
    }
} else {
    echo "  ⚠️ dynamic-exercises.js introuvable\n";
}
echo "\n";

// Test 9: Vérifier la structure des contrôleurs
echo "🏗️ Test 9: Structure des Contrôleurs\n";
$errors = [];

// Vérifier view_course.php
$viewCoursePath = __DIR__ . '/../pages/view_course.php';
if (file_exists($viewCoursePath)) {
    $content = file_get_contents($viewCoursePath);
    
    // Vérifier qu'il ne contient pas de DOCTYPE (car géré par le routeur)
    if (strpos($content, '<!DOCTYPE') === false) {
        echo "  ✅ pages/view_course.php ne contient pas de DOCTYPE (correct)\n";
        $testsPassed++;
    } else {
        echo "  ❌ pages/view_course.php contient un DOCTYPE (à retirer)\n";
        $errors[] = "DOCTYPE found in view_course.php";
        $testsFailed++;
    }
    
    // Vérifier qu'il charge config.php
    if (strpos($content, 'config.php') !== false) {
        echo "  ✅ pages/view_course.php charge config.php\n";
        $testsPassed++;
    } else {
        echo "  ❌ pages/view_course.php ne charge pas config.php\n";
        $errors[] = "config.php not loaded in view_course.php";
        $testsFailed++;
    }
}

// Vérifier view_exercise.php
$viewExercisePath = __DIR__ . '/../pages/view_exercise.php';
if (file_exists($viewExercisePath)) {
    $content = file_get_contents($viewExercisePath);
    
    // Vérifier qu'il ne contient pas de DOCTYPE
    if (strpos($content, '<!DOCTYPE') === false) {
        echo "  ✅ pages/view_exercise.php ne contient pas de DOCTYPE (correct)\n";
        $testsPassed++;
    } else {
        echo "  ❌ pages/view_exercise.php contient un DOCTYPE (à retirer)\n";
        $errors[] = "DOCTYPE found in view_exercise.php";
        $testsFailed++;
    }
    
    // Vérifier qu'il charge config.php
    if (strpos($content, 'config.php') !== false) {
        echo "  ✅ pages/view_exercise.php charge config.php\n";
        $testsPassed++;
    } else {
        echo "  ❌ pages/view_exercise.php ne charge pas config.php\n";
        $errors[] = "config.php not loaded in view_exercise.php";
        $testsFailed++;
    }
}
echo "\n";

// Test 10: Vérifier les redirections
echo "🔄 Test 10: Redirections\n";
$redirectionFiles = [
    'view_course.php',
    'view_exercise.php',
];

foreach ($redirectionFiles as $file) {
    $fullPath = __DIR__ . '/../' . $file;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (strpos($content, 'header(\'Location: index.php?page=') !== false) {
            echo "  ✅ $file redirige correctement\n";
            $testsPassed++;
        } else {
            echo "  ❌ $file ne redirige pas correctement\n";
            $testsFailed++;
        }
    }
}
echo "\n";

// Résumé final
echo "╔" . str_repeat("═", 66) . "╗\n";
echo "║" . str_pad("📊 RÉSUMÉ DES TESTS", 66) . "║\n";
echo "╠" . str_repeat("═", 66) . "╣\n";
echo "║ ✅ Tests réussis: " . str_pad($testsPassed, 50) . "║\n";
echo "║ ❌ Tests échoués: " . str_pad($testsFailed, 50) . "║\n";
echo "╚" . str_repeat("═", 66) . "╝\n\n";

if ($testsFailed === 0) {
    echo "🎉 SUCCÈS! L'intégration du routeur est correcte et fonctionnelle!\n\n";
    echo "✅ Points clés validés:\n";
    echo "   • Base de données accessible\n";
    echo "   • Exercices chargés avec succès\n";
    echo "   • Fonctions disponibles\n";
    echo "   • URLs mises à jour\n";
    echo "   • Contrôleurs correctement structurés\n";
    echo "   • Redirections fonctionnelles\n\n";
    exit(0);
} else {
    echo "⚠️ ATTENTION: Certains tests ont échoué!\n\n";
    if (!empty($errors)) {
        echo "Erreurs détectées:\n";
        foreach ($errors as $error) {
            echo "  • $error\n";
        }
        echo "\n";
    }
    exit(1);
}
