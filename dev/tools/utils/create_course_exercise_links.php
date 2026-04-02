<?php
/**
 * Script de création des liens intelligents entre cours et exercices
 * Basé sur les métadonnées (niveau, matière, mots-clés)
 */

require_once __DIR__ . '/../config.php';

if (!$pdo) die("❌ Pas de connexion BDD\n");

echo "🔗 CRÉATION DES LIENS COURS-EXERCICES\n";
echo str_repeat("=", 60) . "\n\n";

// Récupérer tous les cours
$courses = $pdo->query("
    SELECT Id, Subject, Level, Title, FilePath, Keywords 
    FROM Courses 
    ORDER BY Level, Subject, CourseNumber
")->fetchAll();

echo "📚 " . count($courses) . " cours trouvés\n";

// Récupérer tous les exercices
$exercises = $pdo->query("
    SELECT Id, Subject, Level, Title, Content 
    FROM Exercises 
    ORDER BY Level, Subject
")->fetchAll();

echo "✏️  " . count($exercises) . " exercices trouvés\n\n";

$linkedCount = 0;
$linkDetails = [];

// Stratégie de matching
foreach ($courses as $course) {
    $courseKeywords = strtolower($course['Keywords'] ?? $course['Title']);
    
    foreach ($exercises as $exercise) {
        // Critère 1: Même niveau ET même matière
        if ($course['Level'] !== $exercise['Level'] || 
            $course['Subject'] !== $exercise['Subject']) {
            continue;
        }
        
        // Critère 2: Correspondance de mots-clés
        $exerciseText = strtolower($exercise['Title'] . ' ' . substr($exercise['Content'], 0, 200));
        
        // Normaliser pour comparaison
        $courseSlug = preg_replace('/[^a-z0-9]/', '', strtolower($course['Title']));
        $exerciseSlug = preg_replace('/[^a-z0-9]/', '', $exerciseText);
        
        $matched = false;
        $matchReason = '';
        
        // Extraire mots-clés significatifs du titre du cours
        $significantWords = [];
        preg_match_all('/\b[a-zàéèêïô]{4,}\b/iu', $course['Title'], $matches);
        foreach ($matches[0] as $word) {
            $word = strtolower($word);
            if (!in_array($word, ['cours', 'exercice', 'leçon', 'chapitre'])) {
                $significantWords[] = $word;
            }
        }
        
        // Vérifier si au moins un mot significatif est dans l'exercice
        foreach ($significantWords as $word) {
            if (strlen($word) >= 6 && stripos($exerciseText, $word) !== false) {
                $matched = true;
                $matchReason = "mot-clé: $word";
                break;
            }
        }
        
        // Matching spécifiques par matière
        if (!$matched) {
            // Mathématiques
            if ($course['Subject'] === 'Mathématiques') {
                if (stripos($course['Title'], 'pythagore') !== false && 
                    stripos($exerciseText, 'pythagore') !== false) {
                    $matched = true;
                    $matchReason = 'Pythagore';
                }
                elseif (stripos($course['Title'], 'fraction') !== false && 
                        stripos($exerciseText, 'fraction') !== false) {
                    $matched = true;
                    $matchReason = 'fractions';
                }
                elseif (stripos($course['Title'], 'équation') !== false && 
                        stripos($exerciseText, 'équation') !== false) {
                    $matched = true;
                    $matchReason = 'équations';
                }
                elseif (stripos($course['Title'], 'relatif') !== false && 
                        stripos($exerciseText, 'relatif') !== false) {
                    $matched = true;
                    $matchReason = 'nombres relatifs';
                }
                elseif (stripos($course['Title'], 'proportionnal') !== false && 
                        stripos($exerciseText, 'proportionnal') !== false) {
                    $matched = true;
                    $matchReason = 'proportionnalité';
                }
                elseif (stripos($course['Title'], 'périmètre') !== false && 
                        stripos($exerciseText, 'périmètre') !== false) {
                    $matched = true;
                    $matchReason = 'périmètre';
                }
                elseif (stripos($course['Title'], 'décima') !== false && 
                        stripos($exerciseText, 'décima') !== false) {
                    $matched = true;
                    $matchReason = 'décimaux';
                }
            }
            
            // SVT
            elseif ($course['Subject'] === 'SVT') {
                if (stripos($course['Title'], 'cellule') !== false && 
                    stripos($exerciseText, 'cellule') !== false) {
                    $matched = true;
                    $matchReason = 'cellule';
                }
                elseif (stripos($course['Title'], 'respiration') !== false && 
                        stripos($exerciseText, 'respiration') !== false) {
                    $matched = true;
                    $matchReason = 'respiration';
                }
                elseif (stripos($course['Title'], 'génétique') !== false && 
                        stripos($exerciseText, 'génétique') !== false) {
                    $matched = true;
                    $matchReason = 'génétique';
                }
                elseif (stripos($course['Title'], 'nerveux') !== false && 
                        stripos($exerciseText, 'nerveux') !== false) {
                    $matched = true;
                    $matchReason = 'système nerveux';
                }
            }
            
            // Physique-Chimie
            elseif ($course['Subject'] === 'Physique-Chimie') {
                if (stripos($course['Title'], 'circuit') !== false && 
                    stripos($exerciseText, 'circuit') !== false) {
                    $matched = true;
                    $matchReason = 'circuits';
                }
                elseif (stripos($course['Title'], 'énergie') !== false && 
                        stripos($exerciseText, 'énergie') !== false) {
                    $matched = true;
                    $matchReason = 'énergie';
                }
            }
            
            // Anglais
            elseif ($course['Subject'] === 'Anglais') {
                if (stripos($course['Title'], 'introduction') !== false && 
                    stripos($exerciseText, 'introduc') !== false) {
                    $matched = true;
                    $matchReason = 'introductions';
                }
                elseif (stripos($course['Title'], 'present simple') !== false && 
                        stripos($exerciseText, 'present simple') !== false) {
                    $matched = true;
                    $matchReason = 'present simple';
                }
            }
            
            // Français
            elseif ($course['Subject'] === 'Français') {
                if (stripos($course['Title'], 'accord') !== false && 
                    stripos($exerciseText, 'accord') !== false) {
                    $matched = true;
                    $matchReason = 'accords';
                }
                elseif (stripos($course['Title'], 'classe') !== false && 
                        stripos($exerciseText, 'classe') !== false) {
                    $matched = true;
                    $matchReason = 'classes de mots';
                }
                elseif (stripos($course['Title'], 'passé composé') !== false && 
                        stripos($exerciseText, 'passé composé') !== false) {
                    $matched = true;
                    $matchReason = 'passé composé';
                }
            }
            
            // Histoire-Géo
            elseif ($course['Subject'] === 'Histoire-Géo') {
                if (stripos($course['Title'], 'egypte') !== false && 
                    stripos($exerciseText, 'egypte') !== false) {
                    $matched = true;
                    $matchReason = 'Égypte';
                }
            }
        }
        
        // Si match trouvé, créer le lien
        if ($matched) {
            try {
                // Vérifier si le lien existe déjà
                $checkStmt = $pdo->prepare("
                    SELECT Id FROM ExerciseCourseLinks 
                    WHERE ExerciseId = ? AND CourseId = ?
                ");
                $checkStmt->execute([$exercise['Id'], $course['Id']]);
                
                if (!$checkStmt->fetch()) {
                    // Créer le lien
                    $insertStmt = $pdo->prepare("
                        INSERT INTO ExerciseCourseLinks (ExerciseId, CourseId, LinkType)
                        VALUES (?, ?, 'theory')
                    ");
                    $insertStmt->execute([$exercise['Id'], $course['Id']]);
                    
                    $linkedCount++;
                    $linkDetails[] = [
                        'course' => $course['Level'] . ' - ' . $course['Subject'] . ' - ' . $course['Title'],
                        'exercise' => substr($exercise['Title'], 0, 50),
                        'reason' => $matchReason
                    ];
                }
            } catch (PDOException $e) {
                echo "⚠️  Erreur: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ RÉSULTAT\n";
echo str_repeat("=", 60) . "\n";
echo "Liens créés: $linkedCount\n\n";

if ($linkedCount > 0) {
    echo "Détails des liens:\n";
    foreach ($linkDetails as $i => $link) {
        echo sprintf("%2d. [%s]\n", $i + 1, $link['reason']);
        echo "    Cours: {$link['course']}\n";
        echo "    Exercice: {$link['exercise']}\n\n";
    }
}

echo "✅ Script terminé.\n";
