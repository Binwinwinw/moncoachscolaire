<?php
/**
 * Script simplifié pour extraire et intégrer les exercices des guides HTML
 * Usage: php dev/scripts/extract_and_integrate_exercises.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes

$baseDir = __DIR__ . '/../..';

// Chemins des guides
$guide_college = "$baseDir/pages/college/Guide complet de remédiation Collège 6eme a la 3eme _ Programmes 2025.html";
$guide_lycee = "$baseDir/pages/lycee/Guide complet de remédiation lycee seconde a terminale Programmes 2025.html";

echo "🚀 Début de l'extraction des exercices des guides...\n\n";

/**
 * Extrait tous les exercices d'un guide HTML en utilisant des regex
 */
function extractExercisesFromHTML($htmlContent, $guideType = 'college') {
    $exercises = [];
    
    // Pattern pour trouver chaque exercice complet
    // On cherche les divs avec class="exercise"
    $pattern = '/<div class="exercise">(.*?)<\/div>\s*(?=<div class="exercise">|<\/div>\s*$)/s';
    
    preg_match_all($pattern, $htmlContent, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $exerciseHtml = $match[1];
        $exercise = [];
        
        // Extraire le titre
        if (preg_match('/<div class="exercise-title">(.*?)<\/div>/s', $exerciseHtml, $titleMatch)) {
            $titleContent = strip_tags($titleMatch[1]);
            // Retirer la difficulté du titre si présente
            $titleContent = preg_replace('/<span class="difficulty">.*?<\/span>/', '', $titleContent);
            $exercise['title'] = trim($titleContent);
            
            // Extraire la difficulté
            if (preg_match('/<span class="difficulty">(.*?)<\/span>/', $titleMatch[1], $diffMatch)) {
                $exercise['difficulty'] = trim(strip_tags($diffMatch[1]));
            }
        }
        
        // Extraire le contenu (tout sauf la correction)
        $contentHtml = $exerciseHtml;
        // Retirer le titre et la correction
        $contentHtml = preg_replace('/<div class="exercise-title">.*?<\/div>/s', '', $contentHtml);
        
        // Extraire la correction séparément
        if (preg_match('/<div class="correction">(.*?)<\/div>/s', $contentHtml, $corrMatch)) {
            $exercise['correction'] = trim($corrMatch[1]);
            $contentHtml = preg_replace('/<div class="correction">.*?<\/div>/s', '', $contentHtml);
        }
        
        $exercise['content'] = trim($contentHtml);
        
        if (!empty($exercise['title'])) {
            $exercises[] = $exercise;
        }
    }
    
    return $exercises;
}

/**
 * Détermine le niveau et la matière depuis le contexte HTML
 */
function determineLevelAndSubject($htmlContent, $exerciseIndex, $allExercises) {
    $result = ['level' => '', 'subject' => ''];
    
    // Chercher les sections autour de l'exercice
    // Pattern pour trouver les IDs de sections (maths-6eme, francais-5eme, etc.)
    $sectionPattern = '/<section[^>]*id="([^"]*)"|id="([^"]*)"[^>]*class="[^"]*section-([^"]*)"/i';
    
    // Extraire le contexte avant l'exercice
    $beforeExercise = substr($htmlContent, 0, strpos($htmlContent, $allExercises[$exerciseIndex] ?? ''));
    
    // Chercher le dernier ID de section trouvé
    if (preg_match_all($sectionPattern, $beforeExercise, $sectionMatches, PREG_SET_ORDER)) {
        $lastSection = end($sectionMatches);
        
        // Analyser l'ID
        $sectionId = $lastSection[1] ?? $lastSection[2] ?? '';
        $sectionClass = $lastSection[3] ?? '';
        
        // Pattern pour niveau: guide-6eme, maths-6eme, etc.
        if (preg_match('/(\d+eme|seconde|premiere|terminale)/i', $sectionId . ' ' . $sectionClass, $levelMatch)) {
            $result['level'] = strtolower($levelMatch[1]);
        }
        
        // Pattern pour matière
        if (preg_match('/(maths?|francais|français|sciences?|svt|physique|histoire|geo|philosophie)/i', $sectionId, $subjectMatch)) {
            $result['subject'] = normalizeSubject($subjectMatch[1]);
        }
    }
    
    // Si on n'a pas trouvé, chercher dans les divs avec des IDs spécifiques
    if (empty($result['level']) || empty($result['subject'])) {
        $divPattern = '/<div[^>]*id="([^"]*)"[^>]*>/i';
        preg_match_all($divPattern, $beforeExercise, $divMatches, PREG_SET_ORDER);
        
        foreach (array_reverse($divMatches) as $divMatch) {
            $divId = $divMatch[1] ?? '';
            
            // Chercher niveau et matière dans l'ID
            if (preg_match('/(maths?|francais|français|sciences?|svt|physique|histoire|geo|philosophie)[-_]?(\d+eme|seconde|premiere|terminale)/i', $divId, $fullMatch)) {
                $result['subject'] = normalizeSubject($fullMatch[1]);
                $result['level'] = normalizeLevel($fullMatch[2]);
                break;
            }
        }
    }
    
    return $result;
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
        'maths' => 'maths', 'math' => 'maths', 'mathematiques' => 'maths', 'mathématiques' => 'maths',
        'sciences' => 'sciences', 'svt' => 'sciences', 'physique' => 'sciences', 'physique-chimie' => 'sciences',
        'histoire' => 'histoire-geo', 'geo' => 'histoire-geo', 'geographie' => 'histoire-geo',
        'philosophie' => 'philosophie'
    ];
    return $mapping[$subject] ?? $subject;
}

/**
 * Extrait les exercices existants d'un fichier PHP
 */
function getExistingExerciseTitles($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    
    $content = file_get_contents($filePath);
    $titles = [];
    
    // Chercher les titres dans les exercise-card
    if (preg_match_all('/<h3>(.*?)<\/h3>/', $content, $matches)) {
        foreach ($matches[1] as $title) {
            $cleanTitle = trim(strip_tags($title));
            if (!empty($cleanTitle)) {
                $titles[] = strtolower($cleanTitle);
            }
        }
    }
    
    return $titles;
}

/**
 * Formate un exercice pour l'application (format gamifié)
 */
function formatExerciseHTML($exercise, $subject, $exerciseId) {
    $title = htmlspecialchars($exercise['title']);
    $difficulty = isset($exercise['difficulty']) ? htmlspecialchars($exercise['difficulty']) : '★';
    
    // Convertir en étoiles
    $stars = '⭐';
    if (preg_match('/★+/', $difficulty)) {
        $stars = str_replace('★', '⭐', preg_replace('/[^★]/', '', $difficulty));
    } elseif (preg_match('/\d+/', $difficulty, $numMatch)) {
        $num = (int)$numMatch[0];
        $stars = str_repeat('⭐', min(3, max(1, $num)));
    }
    
    $content = $exercise['content'] ?? '';
    $correction = $exercise['correction'] ?? '';
    
    // Nettoyer le HTML du contenu
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);
    
    // Nettoyer la correction
    $correctionClean = strip_tags($correction, '<p><strong><em><br><ul><li><ol>');
    $correctionClean = preg_replace('/\s+/', ' ', $correctionClean);
    $correctionClean = trim($correctionClean);
    
    $uniqueId = uniqid('ex');
    
    $html = <<<HTML
        <div class="exercise-card">
            <div class="exercise-header">
                <h3>{$title}</h3>
                <span class="difficulty">{$stars}</span>
            </div>
            <div class="exercise-content">
                {$content}
            </div>
            <button class="btn-coach" onclick="showCorrection('{$uniqueId}')">Voir la correction</button>
            <div class="success-message" id="correction-{$uniqueId}">
                {$correctionClean}
            </div>
        </div>
HTML;

    return $html;
}

// Lire les guides
if (!file_exists($guide_college)) {
    die("❌ Guide collège introuvable : $guide_college\n");
}
if (!file_exists($guide_lycee)) {
    die("❌ Guide lycée introuvable : $guide_lycee\n");
}

$collegeHtml = file_get_contents($guide_college);
$lyceeHtml = file_get_contents($guide_lycee);

echo "📖 Extraction des exercices du guide Collège...\n";
$collegeExercises = extractExercisesFromHTML($collegeHtml, 'college');
echo "   ✅ " . count($collegeExercises) . " exercices trouvés\n";

echo "📖 Extraction des exercices du guide Lycée...\n";
$lyceeExercises = extractExercisesFromHTML($lyceeHtml, 'lycee');
echo "   ✅ " . count($lyceeExercises) . " exercices trouvés\n\n";

// Déterminer le niveau et la matière pour chaque exercice
// Pour simplifier, on va chercher dans le HTML autour de chaque exercice
echo "🔍 Détermination des niveaux et matières...\n";

$allExercises = array_merge($collegeExercises, $lyceeExercises);
$grouped = [];

// Pour chaque exercice, chercher son contexte dans le HTML complet
$combinedHtml = $collegeHtml . "\n" . $lyceeHtml;

foreach ($allExercises as $idx => $exercise) {
    // Trouver la position de l'exercice dans le HTML
    $exercisePattern = preg_quote($exercise['title'], '/');
    $pos = stripos($combinedHtml, $exercise['title']);
    
    if ($pos !== false) {
        // Prendre un contexte de 5000 caractères avant
        $contextStart = max(0, $pos - 5000);
        $context = substr($combinedHtml, $contextStart, 10000);
        
        // Chercher les sections dans ce contexte
        if (preg_match('/(maths?|francais|français|sciences?|svt|physique|histoire|geo|philosophie)[-_\s]*(\d+eme|seconde|premiere|terminale)/i', $context, $match)) {
            $subject = normalizeSubject($match[1]);
            $level = normalizeLevel($match[2]);
            
            $key = "$level-$subject";
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            
            $exercise['level'] = $level;
            $exercise['subject'] = $subject;
            $grouped[$key][] = $exercise;
        }
    }
}

echo "   ✅ Exercices groupés : " . count($grouped) . " groupes\n\n";

// Afficher le résumé
echo "📊 Résumé des exercices par niveau et matière :\n";
foreach ($grouped as $key => $exercises) {
    echo "   • $key : " . count($exercises) . " exercices\n";
}
echo "\n";

// Mapping des fichiers
$fileMap = [
    '6eme-francais' => "$baseDir/pages/college/6eme/exercices-6eme.php",
    '6eme-maths' => "$baseDir/pages/college/6eme/exercices-6eme.php",
    '6eme-sciences' => "$baseDir/pages/college/6eme/exercices-6eme.php",
    '5eme-francais' => "$baseDir/pages/college/5eme/exercices-5eme.php",
    '5eme-maths' => "$baseDir/pages/college/5eme/exercices-5eme.php",
    '5eme-sciences' => "$baseDir/pages/college/5eme/exercices-5eme.php",
    '4eme-francais' => "$baseDir/pages/college/4eme/exercices-4eme.php",
    '4eme-maths' => "$baseDir/pages/college/4eme/exercices-4eme.php",
    '4eme-sciences' => "$baseDir/pages/college/4eme/exercices-4eme.php",
    '3eme-francais' => "$baseDir/pages/college/3eme/exercices-3eme.php",
    '3eme-maths' => "$baseDir/pages/college/3eme/exercices-3eme.php",
    '3eme-sciences' => "$baseDir/pages/college/3eme/exercices-3eme.php",
    'seconde-francais' => "$baseDir/pages/lycee/seconde/exercices-seconde.php",
    'seconde-maths' => "$baseDir/pages/lycee/seconde/exercices-seconde.php",
    'seconde-sciences' => "$baseDir/pages/lycee/seconde/exercices-seconde.php",
    'premiere-francais' => "$baseDir/pages/lycee/premiere/exercices-premiere.php",
    'premiere-maths' => "$baseDir/pages/lycee/premiere/exercices-premiere.php",
    'premiere-sciences' => "$baseDir/pages/lycee/premiere/exercices-premiere.php",
    'terminale-francais' => "$baseDir/pages/lycee/terminale/exercices-terminale.php",
    'terminale-maths' => "$baseDir/pages/lycee/terminale/exercices-terminale.php",
    'terminale-sciences' => "$baseDir/pages/lycee/terminale/exercices-terminale.php",
    'terminale-philosophie' => "$baseDir/pages/lycee/terminale/exercices-terminale.php",
];

echo "🔧 Intégration dans les fichiers...\n\n";

foreach ($grouped as $key => $exercises) {
    if (!isset($fileMap[$key])) {
        echo "⚠️  Pas de fichier défini pour $key\n";
        continue;
    }
    
    $filePath = $fileMap[$key];
    if (!file_exists($filePath)) {
        echo "⚠️  Fichier introuvable : $filePath\n";
        continue;
    }
    
    list($level, $subject) = explode('-', $key, 2);
    
    echo "📝 Traitement de $key → " . basename($filePath) . "\n";
    
    // Lire les exercices existants
    $existingTitles = getExistingExerciseTitles($filePath);
    
    // Filtrer les nouveaux exercices
    $newExercises = [];
    foreach ($exercises as $exercise) {
        $titleLower = strtolower(trim($exercise['title']));
        if (!in_array($titleLower, $existingTitles)) {
            $newExercises[] = $exercise;
        } else {
            echo "   ⏭️  Exercice déjà présent : " . substr($exercise['title'], 0, 50) . "...\n";
        }
    }
    
    if (empty($newExercises)) {
        echo "   ✅ Aucun nouvel exercice à ajouter\n\n";
        continue;
    }
    
    // Lire le contenu du fichier
    $fileContent = file_get_contents($filePath);
    
    // Chercher la section correspondante
    $sectionPattern = '/<section\s+id="' . preg_quote($subject, '/') . '">(.*?)<\/section>/s';
    
    if (preg_match($sectionPattern, $fileContent, $sectionMatch)) {
        // Section existe, ajouter avant la fermeture
        $sectionContent = $sectionMatch[1];
        $newExercisesHtml = "\n\n";
        
        foreach ($newExercises as $exercise) {
            $newExercisesHtml .= formatExerciseHTML($exercise, $subject, uniqid()) . "\n\n";
        }
        
        $newSectionContent = rtrim($sectionContent) . $newExercisesHtml;
        $fileContent = str_replace($sectionMatch[0], '<section id="' . $subject . '">' . $newSectionContent . '</section>', $fileContent);
    } else {
        // Créer une nouvelle section
        $newExercisesHtml = '';
        foreach ($newExercises as $exercise) {
            $newExercisesHtml .= formatExerciseHTML($exercise, $subject, uniqid()) . "\n\n";
        }
        
        $subjectTitle = ucfirst($subject);
        $newSection = "\n\n    <!-- " . strtoupper($subject) . " -->\n    <section id=\"{$subject}\">\n        <h2>📚 {$subjectTitle}</h2>\n        {$newExercisesHtml}\n    </section>\n";
        
        // Ajouter avant le script ou avant </main>
        if (preg_match('/(<script>)/', $fileContent, $scriptMatch, PREG_OFFSET_CAPTURE)) {
            $fileContent = substr_replace($fileContent, $newSection, $scriptMatch[1][1], 0);
        } elseif (preg_match('/(<\/main>)/', $fileContent, $mainMatch, PREG_OFFSET_CAPTURE)) {
            $fileContent = substr_replace($fileContent, $newSection, $mainMatch[1][1], 0);
        }
    }
    
    // Sauvegarder
    file_put_contents($filePath, $fileContent);
    echo "   ✅ " . count($newExercises) . " exercices ajoutés\n\n";
}

echo "✅ Terminé !\n";


