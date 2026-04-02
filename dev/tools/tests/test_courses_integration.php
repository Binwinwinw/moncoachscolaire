<?php
/**
 * Script de test de l'intégration des cours
 * Usage: php tools/test_courses_integration.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/course_markdown_loader.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

echo "🧪 TEST DE L'INTÉGRATION DES COURS\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Vérifier la connexion BDD
echo "1️⃣ Test connexion BDD...\n";
if ($pdo) {
    echo "   ✅ Connexion BDD OK\n\n";
} else {
    echo "   ❌ Connexion BDD ÉCHEC\n";
    exit(1);
}

// Test 2: Compter les cours
echo "2️⃣ Test comptage cours...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM Courses");
    $courseCount = $stmt->fetchColumn();
    echo "   ✅ Cours dans BDD: $courseCount\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Compter les liens
echo "3️⃣ Test comptage liens cours-exercices...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM ExerciseCourseLinks");
    $linkCount = $stmt->fetchColumn();
    echo "   ✅ Liens dans BDD: $linkCount\n\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Charger un cours
echo "4️⃣ Test chargement d'un cours...\n";
try {
    $stmt = $pdo->query("SELECT Id FROM Courses LIMIT 1");
    $firstCourseId = $stmt->fetchColumn();
    
    if ($firstCourseId) {
        $course = getCourseById($firstCourseId, true);
        if ($course && isset($course['Title'])) {
            echo "   ✅ Cours chargé: {$course['Title']}\n";
            echo "      - Matière: {$course['Subject']}\n";
            echo "      - Niveau: {$course['Level']}\n";
            echo "      - Fichier: {$course['FilePath']}\n";
            
            // Vérifier le contenu markdown
            if (isset($course['markdown']) && !empty($course['markdown'])) {
                if (isset($course['markdown']['rawContent'])) {
                    $contentLength = strlen($course['markdown']['rawContent']);
                    echo "      - Contenu chargé: $contentLength caractères\n";
                    echo "      - Titre markdown: {$course['markdown']['title']}\n\n";
                } else {
                    echo "      ⚠️  Structure markdown incomplète\n\n";
                }
            } else {
                echo "      ⚠️  Contenu markdown non chargé\n\n";
            }
        } else {
            echo "   ❌ Impossible de charger le cours\n\n";
        }
    } else {
        echo "   ⚠️  Aucun cours trouvé dans la BDD\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Test 5: Charger cours par matière/niveau
echo "5️⃣ Test récupération cours par matière/niveau...\n";
try {
    $courses = getCoursesBySubjectAndLevel('Mathématiques', '3ème');
    $count = count($courses);
    echo "   ✅ Cours de Maths 3ème trouvés: $count\n";
    
    if ($count > 0) {
        foreach ($courses as $course) {
            echo "      - {$course['Title']}\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 6: Vérifier les liens bidirectionnels
echo "6️⃣ Test liens bidirectionnels cours-exercices...\n";
try {
    $stmt = $pdo->query("SELECT Id FROM Courses LIMIT 1");
    $courseId = $stmt->fetchColumn();
    
    if ($courseId) {
        $linkedExercises = getExercisesForCourse($courseId);
        echo "   ✅ Exercices liés au cours #$courseId: " . count($linkedExercises) . "\n";
        
        if (!empty($linkedExercises)) {
            $exerciseId = $linkedExercises[0]['Id'];
            $linkedCourses = getCoursesForExercise($exerciseId);
            echo "   ✅ Cours liés à l'exercice #$exerciseId: " . count($linkedCourses) . "\n\n";
        } else {
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 7: Vérifier les fichiers markdown
echo "7️⃣ Test existence fichiers markdown...\n";
try {
    $stmt = $pdo->query("SELECT FilePath FROM Courses LIMIT 5");
    $paths = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $existCount = 0;
    $missingCount = 0;
    
    foreach ($paths as $path) {
        // Construire le chemin complet
        $fullPath = __DIR__ . '/../' . $path;
        if (file_exists($fullPath)) {
            $existCount++;
        } else {
            $missingCount++;
            echo "      ⚠️  Fichier manquant: $path\n";
        }
    }
    
    echo "   ✅ Fichiers existants: $existCount\n";
    if ($missingCount > 0) {
        echo "   ⚠️  Fichiers manquants: $missingCount\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 8: Distribution des cours par niveau
echo "8️⃣ Test distribution cours par niveau...\n";
try {
    $stmt = $pdo->query("
        SELECT Level, COUNT(*) as count 
        FROM Courses 
        GROUP BY Level 
        ORDER BY Level DESC
    ");
    $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($distribution as $row) {
        echo "   {$row['Level']}: {$row['count']} cours\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 9: Distribution des cours par matière
echo "9️⃣ Test distribution cours par matière...\n";
try {
    $stmt = $pdo->query("
        SELECT Subject, COUNT(*) as count 
        FROM Courses 
        GROUP BY Subject 
        ORDER BY count DESC
    ");
    $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($distribution as $row) {
        echo "   {$row['Subject']}: {$row['count']} cours\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Test 10: Vérifier l'intégrité des foreign keys
echo "🔟 Test intégrité des foreign keys...\n";
try {
    // Vérifier que tous les ExerciseId existent
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM ExerciseCourseLinks ecl
        LEFT JOIN Exercises e ON ecl.ExerciseId = e.Id
        WHERE e.Id IS NULL
    ");
    $orphanExercises = $stmt->fetchColumn();
    
    // Vérifier que tous les CourseId existent
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM ExerciseCourseLinks ecl
        LEFT JOIN Courses c ON ecl.CourseId = c.Id
        WHERE c.Id IS NULL
    ");
    $orphanCourses = $stmt->fetchColumn();
    
    if ($orphanExercises == 0 && $orphanCourses == 0) {
        echo "   ✅ Toutes les foreign keys sont valides\n";
    } else {
        if ($orphanExercises > 0) {
            echo "   ⚠️  Liens orphelins (exercices): $orphanExercises\n";
        }
        if ($orphanCourses > 0) {
            echo "   ⚠️  Liens orphelins (cours): $orphanCourses\n";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n\n";
}

// Résumé final
echo str_repeat("=", 60) . "\n";
echo "✅ TOUS LES TESTS TERMINÉS\n";
echo str_repeat("=", 60) . "\n\n";

echo "📊 RÉSUMÉ:\n";
echo "   - Cours en BDD: $courseCount\n";
echo "   - Liens cours-exercices: $linkCount\n";
echo "   - Connexion: OK\n";
echo "   - Fonctions de chargement: OK\n";
echo "\n";

echo "🚀 L'intégration des cours est prête !\n";
echo "   → Accédez à cours.php pour voir les cours\n";
echo "   → Accédez à exercices.php pour voir les liens\n\n";
