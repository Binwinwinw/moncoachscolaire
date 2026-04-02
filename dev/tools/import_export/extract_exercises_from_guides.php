<?php
/**
 * Script pour extraire les exercices des guides HTML et les intégrer dans les fichiers PHP appropriés
 */

// Chemins des guides
$guide_college = __DIR__ . '/../pages/college/Guide complet de remédiation Collège 6eme a la 3eme _ Programmes 2025.html';
$guide_lycee = __DIR__ . '/../pages/lycee/Guide complet de remédiation lycee seconde a terminale Programmes 2025.html';

// Mapping des niveaux vers les fichiers
$college_mapping = [
    '6eme' => __DIR__ . '/../pages/college/6eme/exercices-6eme.php',
    '5eme' => __DIR__ . '/../pages/college/5eme/exercices-5eme.php',
    '4eme' => __DIR__ . '/../pages/college/4eme/exercices-4eme.php',
    '3eme' => __DIR__ . '/../pages/college/3eme/exercices-3eme.php',
];

$lycee_mapping = [
    'seconde' => __DIR__ . '/../pages/lycee/seconde/exercices-seconde.php',
    'premiere' => __DIR__ . '/../pages/lycee/premiere/exercices-premiere.php',
    'terminale' => __DIR__ . '/../pages/lycee/terminale/exercices-terminale.php',
];

/**
 * Extrait les exercices d'un fichier HTML pour un niveau donné
 */
function extractExercises($htmlFile, $level) {
    if (!file_exists($htmlFile)) {
        echo "❌ Fichier non trouvé : $htmlFile\n";
        return [];
    }
    
    $content = file_get_contents($htmlFile);
    $exercises = [];
    
    // Normaliser le niveau pour correspondre aux IDs des sections
    $levelId = $level;
    if ($level === '6eme') $levelId = '6eme';
    if ($level === '5eme') $levelId = '5eme';
    if ($level === '4eme') $levelId = '4eme';
    if ($level === '3eme') $levelId = '3eme';
    if ($level === 'seconde') $levelId = 'seconde';
    if ($level === 'premiere') $levelId = 'premiere';
    if ($level === 'terminale') $levelId = 'terminale';
    
    // Extraire la section correspondant au niveau
    $sectionPattern = '/<section[^>]*id="guide-' . preg_quote($levelId, '/') . '"[^>]*>(.*?)<\/section>/is';
    
    if (!preg_match($sectionPattern, $content, $sectionMatch)) {
        echo "⚠️  Section guide-$levelId non trouvée\n";
        return [];
    }
    
    $sectionContent = $sectionMatch[1];
    $currentMatiere = 'Général';
    
    // Extraire tous les exercices de la section
    // Pattern amélioré pour capturer les divs exercise complètes
    preg_match_all('/<div\s+class="exercise"[^>]*>((?:[^<]|<(?!\/div>))*?)<\/div>\s*(?:<\/div>|(?=<div))/is', $sectionContent, $matches, PREG_OFFSET_CAPTURE);
    
    foreach ($matches[1] as $index => $match) {
        $exerciseHtml = $match[0];
        $offset = $match[1];
        
        // Chercher la matière avant cet exercice dans la section
        $beforeExercise = substr($sectionContent, max(0, $offset - 2000), 2000);
        if (preg_match('/<h2[^>]*>([^<]*(?:Mathématiques|Français|Sciences|SVT|Physique|Philosophie)[^<]*)<\/h2>/i', $beforeExercise, $matiereMatch)) {
            $currentMatiere = trim(strip_tags($matiereMatch[1]));
        }
        
        // Extraire le titre
        preg_match('/<div\s+class="exercise-title"[^>]*>(.*?)<\/div>/is', $exerciseHtml, $titleMatch);
        $title = $titleMatch[1] ?? "Exercice " . ($index + 1);
        $title = strip_tags($title);
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = trim($title);
        
        // Extraire la difficulté
        preg_match('/<span\s+class="difficulty"[^>]*>(.*?)<\/span>/is', $exerciseHtml, $difficultyMatch);
        $difficulty = $difficultyMatch[1] ?? '';
        $difficulty = strip_tags($difficulty);
        
        // Extraire les consignes (tout ce qui précède la correction)
        $correctionPattern = '/<div\s+class="correction"[^>]*>.*$/is';
        $consignesHtml = preg_replace($correctionPattern, '', $exerciseHtml);
        // Retirer aussi le titre
        $consignesHtml = preg_replace('/<div\s+class="exercise-title"[^>]*>.*?<\/div>/is', '', $consignesHtml);
        $consignes = strip_tags($consignesHtml);
        $consignes = html_entity_decode($consignes, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $consignes = trim(preg_replace('/\s+/', ' ', $consignes));
        
        // Extraire la correction
        preg_match('/<div\s+class="correction"[^>]*>(.*?)<\/div>/is', $exerciseHtml, $correctionMatch);
        $correction = $correctionMatch[1] ?? '';
        $correction = strip_tags($correction);
        $correction = html_entity_decode($correction, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $correction = trim(preg_replace('/\s+/', ' ', $correction));
        
        if (empty($title) || $title === "Exercice " . ($index + 1)) {
            continue; // Ignorer les exercices sans titre valide
        }
        
        $exercises[] = [
            'title' => $title,
            'difficulty' => $difficulty,
            'consignes' => $consignes,
            'correction' => $correction,
            'matiere' => $currentMatiere,
            'raw_html' => $exerciseHtml
        ];
    }
    
    return $exercises;
}

/**
 * Vérifie si un exercice existe déjà dans un fichier
 */
function exerciseExists($filePath, $title) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    // Chercher le titre dans le contenu
    $titleClean = preg_quote(strip_tags($title), '/');
    return preg_match('/' . $titleClean . '/i', $content);
}

/**
 * Ajoute les exercices à un fichier PHP
 */
function addExercisesToFile($filePath, $exercises, $level) {
    if (!file_exists($filePath)) {
        echo "⚠️  Fichier non trouvé : $filePath - Création d'un nouveau fichier\n";
        // Créer le fichier de base
        $baseContent = "<?php\n";
        $baseContent .= "\$page_title = 'Exercices " . ucfirst($level) . "';\n";
        $baseContent .= "?>\n\n";
        $baseContent .= "<main>\n";
        $baseContent .= "    <h1>Exercices " . ucfirst($level) . "</h1>\n";
        $baseContent .= "    <div class=\"exercises-container\">\n";
        file_put_contents($filePath, $baseContent);
    }
    
    $content = file_get_contents($filePath);
    $newExercises = [];
    
    foreach ($exercises as $exercise) {
        if (!exerciseExists($filePath, $exercise['title'])) {
            $newExercises[] = $exercise;
        } else {
            echo "⏭️  Exercice déjà présent : " . substr($exercise['title'], 0, 50) . "...\n";
        }
    }
    
    if (empty($newExercises)) {
        echo "✅ Aucun nouvel exercice à ajouter pour $level\n";
        return;
    }
    
    echo "📝 Ajout de " . count($newExercises) . " nouveaux exercices pour $level\n";
    
    // Trouver où insérer les exercices (avant la fermeture du main ou à la fin)
    $insertPosition = strrpos($content, '</main>');
    if ($insertPosition === false) {
        $insertPosition = strlen($content);
    }
    
    $exercisesHtml = "\n    <!-- Exercices extraits du guide de remédiation -->\n";
    $exercisesHtml .= "    <section class=\"exercises-section\">\n";
    
    foreach ($newExercises as $exercise) {
        $difficultyText = '';
        if (!empty($exercise['difficulty'])) {
            $difficultyText = ' - ' . trim(strip_tags($exercise['difficulty']));
        }
        
        $exercisesHtml .= "        <div class=\"exercise-item\">\n";
        $exercisesHtml .= "            <h3>" . htmlspecialchars($exercise['title']) . $difficultyText . "</h3>\n";
        
        if (!empty($exercise['matiere']) && $exercise['matiere'] !== 'Général') {
            $exercisesHtml .= "            <p class=\"matiere\"><strong>Matière :</strong> " . htmlspecialchars($exercise['matiere']) . "</p>\n";
        }
        
        if (!empty($exercise['consignes'])) {
            $exercisesHtml .= "            <div class=\"consignes\">\n";
            $exercisesHtml .= "                <p><strong>Consignes :</strong></p>\n";
            $exercisesHtml .= "                <p>" . nl2br(htmlspecialchars($exercise['consignes'])) . "</p>\n";
            $exercisesHtml .= "            </div>\n";
        }
        
        if (!empty($exercise['correction'])) {
            $exercisesHtml .= "            <details class=\"correction\">\n";
            $exercisesHtml .= "                <summary>🔍 Voir la correction</summary>\n";
            $exercisesHtml .= "                <div class=\"correction-content\">\n";
            $exercisesHtml .= "                    <p>" . nl2br(htmlspecialchars($exercise['correction'])) . "</p>\n";
            $exercisesHtml .= "                </div>\n";
            $exercisesHtml .= "            </details>\n";
        }
        
        $exercisesHtml .= "        </div>\n\n";
    }
    
    $exercisesHtml .= "    </section>\n";
    
    // Insérer avant </main>
    $newContent = substr_replace($content, $exercisesHtml, $insertPosition, 0);
    
    file_put_contents($filePath, $newContent);
    echo "✅ Exercices ajoutés avec succès dans $filePath\n";
}

// Traitement du guide collège
echo "📚 Traitement du guide Collège...\n";
foreach ($college_mapping as $level => $filePath) {
    echo "\n🔍 Extraction des exercices pour $level...\n";
    $exercises = extractExercises($guide_college, $level);
    
    if (empty($exercises)) {
        echo "⚠️  Aucun exercice trouvé pour $level\n";
        continue;
    }
    
    echo "📊 " . count($exercises) . " exercices trouvés pour $level\n";
    addExercisesToFile($filePath, $exercises, $level);
}

// Traitement du guide lycée
echo "\n\n📚 Traitement du guide Lycée...\n";
foreach ($lycee_mapping as $level => $filePath) {
    echo "\n🔍 Extraction des exercices pour $level...\n";
    $exercises = extractExercises($guide_lycee, $level);
    
    if (empty($exercises)) {
        echo "⚠️  Aucun exercice trouvé pour $level\n";
        continue;
    }
    
    echo "📊 " . count($exercises) . " exercices trouvés pour $level\n";
    addExercisesToFile($filePath, $exercises, $level);
}

echo "\n\n✅ Traitement terminé !\n";

