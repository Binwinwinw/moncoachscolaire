<?php
/**
 * Script amélioré pour extraire les exercices des guides HTML
 * Parse séquentiellement le HTML en gardant le contexte
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);

$baseDir = __DIR__ . '/../..';
$guide_college = "$baseDir/pages/college/Guide complet de remédiation Collège 6eme a la 3eme _ Programmes 2025.html";
$guide_lycee = "$baseDir/pages/lycee/Guide complet de remédiation lycee seconde a terminale Programmes 2025.html";

echo "🚀 Extraction des exercices des guides HTML...\n\n";

/**
 * Parse séquentiellement le HTML pour extraire les exercices avec leur contexte
 */
function parseHTMLSequentially($htmlContent) {
    $exercises = [];
    $currentLevel = '';
    $currentSubject = '';
    
    // Lire ligne par ligne pour garder le contexte
    $lines = explode("\n", $htmlContent);
    
    foreach ($lines as $lineNum => $line) {
        // Détecter les sections de niveau
        if (preg_match('/id="guide-(\d+eme|seconde|premiere|terminale)"/i', $line, $match)) {
            $currentLevel = normalizeLevel($match[1]);
            echo "   📍 Niveau détecté : $currentLevel (ligne $lineNum)\n";
        }
        
        // Détecter les divs avec ID contenant niveau et matière (maths-6eme, francais-5eme, etc.)
        if (preg_match('/id="([^"]*maths?|francais|français|sciences?|svt|physique|histoire|geo|philosophie)[-_\s]*(\d+eme|seconde|premiere|terminale)"/i', $line, $match)) {
            $currentSubject = normalizeSubject($match[1]);
            if (isset($match[2])) {
                $currentLevel = normalizeLevel($match[2]);
            }
            echo "   📍 Matière et niveau : $currentSubject - $currentLevel (ligne $lineNum)\n";
        }
        
        // Détecter un exercice
        if (strpos($line, '<div class="exercise">') !== false) {
            // Lire l'exercice complet (peut s'étendre sur plusieurs lignes)
            $exerciseHtml = $line;
            $depth = substr_count($line, '<div') - substr_count($line, '</div');
            $lineIdx = $lineNum + 1;
            
            while ($depth > 0 && $lineIdx < count($lines)) {
                $exerciseHtml .= "\n" . $lines[$lineIdx];
                $depth += substr_count($lines[$lineIdx], '<div') - substr_count($lines[$lineIdx], '</div');
                $lineIdx++;
            }
            
            $exercise = parseExerciseHTML($exerciseHtml);
            if (!empty($exercise['title'])) {
                $exercise['level'] = $currentLevel;
                $exercise['subject'] = $currentSubject;
                $exercises[] = $exercise;
                echo "   ✅ Exercice extrait : {$exercise['title']} ($currentLevel - $currentSubject)\n";
            }
        }
    }
    
    return $exercises;
}

/**
 * Parse un bloc HTML d'exercice
 */
function parseExerciseHTML($html) {
    $exercise = [];
    
    // Extraire le titre
    if (preg_match('/<div class="exercise-title">(.*?)<\/div>/s', $html, $titleMatch)) {
        $titleHtml = $titleMatch[1];
        $title = strip_tags($titleHtml);
        // Retirer la difficulté
        $title = preg_replace('/<span class="difficulty">.*?<\/span>/', '', $title);
        $exercise['title'] = trim($title);
        
        // Extraire la difficulté
        if (preg_match('/<span class="difficulty">(.*?)<\/span>/', $titleHtml, $diffMatch)) {
            $exercise['difficulty'] = trim(strip_tags($diffMatch[1]));
        }
    }
    
    // Extraire le contenu (tout sauf titre et correction)
    $content = $html;
    $content = preg_replace('/<div class="exercise-title">.*?<\/div>/s', '', $content);
    
    // Extraire la correction séparément
    if (preg_match('/<div class="correction">(.*?)<\/div>/s', $content, $corrMatch)) {
        $exercise['correction'] = trim($corrMatch[1]);
        $content = preg_replace('/<div class="correction">.*?<\/div>/s', '', $content);
    }
    
    // Retirer les balises de fermeture d'exercice
    $content = preg_replace('/<\/div>\s*$/', '', $content);
    $content = preg_replace('/^<div class="exercise">/', '', $content);
    
    $exercise['content'] = trim($content);
    
    return $exercise;
}

/**
 * Normalise le niveau
 */
function normalizeLevel($level) {
    $level = strtolower(trim($level));
    $mapping = [
        '6eme' => '6eme', '6ème' => '6eme',
        '5eme' => '5eme', '5ème' => '5eme',
        '4eme' => '4eme', '4ème' => '4eme',
        '3eme' => '3eme', '3ème' => '3eme',
        'seconde' => 'seconde',
        'premiere' => 'premiere', 'première' => 'premiere',
        'terminal' => 'terminale', 'terminale' => 'terminale'
    ];
    return $mapping[$level] ?? $level;
}

/**
 * Normalise la matière
 */
function normalizeSubject($subject) {
    $subject = strtolower(trim($subject));
    $mapping = [
        'francais' => 'francais', 'français' => 'francais',
        'maths' => 'maths', 'math' => 'maths', 'mathematiques' => 'maths',
        'sciences' => 'sciences', 'svt' => 'sciences', 'physique' => 'sciences',
        'histoire' => 'histoire-geo', 'geo' => 'histoire-geo',
        'philosophie' => 'philosophie'
    ];
    return $mapping[$subject] ?? $subject;
}

// Lire et parser les guides
echo "📖 Parsing du guide Collège...\n";
$collegeHtml = file_get_contents($guide_college);
$collegeExercises = parseHTMLSequentially($collegeHtml);
echo "\n   ✅ " . count($collegeExercises) . " exercices extraits du guide Collège\n\n";

echo "📖 Parsing du guide Lycée...\n";
$lyceeHtml = file_get_contents($guide_lycee);
$lyceeExercises = parseHTMLSequentially($lyceeHtml);
echo "\n   ✅ " . count($lyceeExercises) . " exercices extraits du guide Lycée\n\n";

// Grouper les exercices
$allExercises = array_merge($collegeExercises, $lyceeExercises);
$grouped = [];

foreach ($allExercises as $exercise) {
    $level = $exercise['level'] ?? '';
    $subject = $exercise['subject'] ?? '';
    
    if ($level && $subject) {
        $key = "$level-$subject";
        if (!isset($grouped[$key])) {
            $grouped[$key] = [];
        }
        $grouped[$key][] = $exercise;
    } else {
        echo "⚠️  Exercice sans niveau/matière : {$exercise['title']}\n";
    }
}

// Afficher le résumé
echo "\n📊 Résumé des exercices par niveau et matière :\n";
foreach ($grouped as $key => $exercises) {
    echo "   • $key : " . count($exercises) . " exercices\n";
}

// Sauvegarder dans un fichier JSON pour inspection
$outputFile = "$baseDir/dev/extracted_exercises.json";
file_put_contents($outputFile, json_encode($grouped, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\n💾 Exercices sauvegardés dans : $outputFile\n";
echo "\n✅ Extraction terminée !\n";

