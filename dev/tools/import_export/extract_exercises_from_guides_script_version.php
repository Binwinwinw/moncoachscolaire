<?php
/**
 * Script pour extraire les exercices des guides HTML et les intégrer dans les fichiers PHP
 * Usage: php dev/scripts/extract_exercises_from_guides.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Chemins des guides
$guide_college = __DIR__ . '/../../pages/college/Guide complet de remédiation Collège 6eme a la 3eme _ Programmes 2025.html';
$guide_lycee = __DIR__ . '/../../pages/lycee/Guide complet de remédiation lycee seconde a terminale Programmes 2025.html';

/**
 * Parse un fichier HTML pour extraire les exercices
 */
function parseGuideHTML($filePath) {
    if (!file_exists($filePath)) {
        echo "❌ Fichier introuvable : $filePath\n";
        return [];
    }

    $content = file_get_contents($filePath);
    $exercises = [];

    // Utiliser DOMDocument pour parser le HTML
    $dom = new DOMDocument();
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $content);
    $xpath = new DOMXPath($dom);

    // Chercher toutes les divs avec class="exercise"
    $exerciseNodes = $xpath->query('//div[@class="exercise"]');

    foreach ($exerciseNodes as $exerciseNode) {
        $exercise = [];
        
        // Extraire le titre
        $titleNode = $xpath->query('.//div[@class="exercise-title"]', $exerciseNode)->item(0);
        if ($titleNode) {
            $exercise['title'] = trim($titleNode->textContent);
            
            // Extraire la difficulté depuis le titre
            $difficultyNode = $xpath->query('.//span[@class="difficulty"]', $titleNode)->item(0);
            if ($difficultyNode) {
                $exercise['difficulty'] = trim($difficultyNode->textContent);
            }
        }

        // Extraire le contenu (tous les paragraphes et éléments avant la correction)
        $contentHtml = '';
        $correctionNode = $xpath->query('.//div[@class="correction"]', $exerciseNode)->item(0);
        
        $childNodes = $exerciseNode->childNodes;
        foreach ($childNodes as $child) {
            if ($child === $correctionNode) {
                break;
            }
            if ($child->nodeType === XML_ELEMENT_NODE && $child->getAttribute('class') !== 'exercise-title') {
                $contentHtml .= $dom->saveHTML($child);
            } elseif ($child->nodeType === XML_TEXT_NODE && trim($child->textContent)) {
                $contentHtml .= '<p>' . htmlspecialchars(trim($child->textContent)) . '</p>';
            }
        }
        $exercise['content'] = trim($contentHtml);

        // Extraire la correction
        if ($correctionNode) {
            $exercise['correction'] = trim($dom->saveHTML($correctionNode));
        }

        // Déterminer le niveau et la matière depuis le contexte
        // On cherche dans les sections parentes
        $sectionNode = $exerciseNode;
        while ($sectionNode && $sectionNode->nodeName !== 'section' && $sectionNode->nodeName !== 'body') {
            $sectionNode = $sectionNode->parentNode;
        }

        if ($sectionNode) {
            $sectionId = $sectionNode->getAttribute('id');
            $sectionClass = $sectionNode->getAttribute('class');
            
            // Déterminer le niveau
            if (preg_match('/guide-(\d+eme|seconde|premiere|terminal)/', $sectionId, $matches)) {
                $exercise['level'] = $matches[1];
            } elseif (preg_match('/section-(\d+eme|seconde|premiere|terminal)/', $sectionClass, $matches)) {
                $exercise['level'] = $matches[1];
            }

            // Déterminer la matière
            if (preg_match('/(maths|francais|français|sciences|svt|physique|histoire|geo|philosophie)/i', $sectionId, $matches)) {
                $exercise['subject'] = strtolower($matches[1]);
            }
        }

        // Chercher dans les ancêtres pour trouver le domaine
        $ancestors = $xpath->query('ancestor::*[@id]', $exerciseNode);
        foreach ($ancestors as $ancestor) {
            $id = $ancestor->getAttribute('id');
            if (preg_match('/(maths|francais|français|sciences|svt|physique|histoire|geo|philosophie)-(\d+eme|seconde|premiere|terminal)/i', $id, $matches)) {
                $exercise['subject'] = strtolower($matches[1]);
                if (empty($exercise['level'])) {
                    $exercise['level'] = $matches[2];
                }
                break;
            }
        }

        if (!empty($exercise['title'])) {
            $exercises[] = $exercise;
        }
    }

    return $exercises;
}

/**
 * Convertit un niveau en format standardisé
 */
function normalizeLevel($level) {
    $level = strtolower(trim($level));
    $mapping = [
        '6eme' => '6eme',
        '6ème' => '6eme',
        '5eme' => '5eme',
        '5ème' => '5eme',
        '4eme' => '4eme',
        '4ème' => '4eme',
        '3eme' => '3eme',
        '3ème' => '3eme',
        'seconde' => 'seconde',
        'premiere' => 'premiere',
        'première' => 'premiere',
        'terminal' => 'terminale',
        'terminale' => 'terminale'
    ];
    return $mapping[$level] ?? $level;
}

/**
 * Normalise le nom de matière
 */
function normalizeSubject($subject) {
    $subject = strtolower(trim($subject));
    $mapping = [
        'francais' => 'francais',
        'français' => 'francais',
        'maths' => 'maths',
        'mathematiques' => 'maths',
        'mathématiques' => 'maths',
        'sciences' => 'sciences',
        'svt' => 'sciences',
        'physique' => 'sciences',
        'physique-chimie' => 'sciences',
        'histoire' => 'histoire-geo',
        'geo' => 'histoire-geo',
        'geographie' => 'histoire-geo',
        'philosophie' => 'philosophie'
    ];
    return $mapping[$subject] ?? $subject;
}

/**
 * Extrait les exercices existants d'un fichier PHP
 */
function extractExistingExercises($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    $exercises = [];

    // Chercher les exercices dans le format existant
    // Format: <div class="exercise-card"> avec un h3 contenant le titre
    preg_match_all('/<div class="exercise-card">.*?<h3>(.*?)<\/h3>.*?<\/div>/s', $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $title = strip_tags(trim($match[1]));
        $exercises[] = [
            'title' => $title,
            'content' => $match[0] // Garder le contenu complet pour comparaison
        ];
    }

    return $exercises;
}

/**
 * Formate un exercice au format gamifié de l'application
 */
function formatExerciseForApp($exercise, $subject) {
    $title = htmlspecialchars($exercise['title']);
    $difficulty = isset($exercise['difficulty']) ? htmlspecialchars($exercise['difficulty']) : '⭐';
    
    // Convertir la difficulté en format étoiles
    $difficultyStars = '⭐';
    if (preg_match('/\*+/', $difficulty, $matches)) {
        $difficultyStars = $matches[0];
    } elseif (preg_match('/(facile|★)/i', $difficulty)) {
        $difficultyStars = '⭐';
    } elseif (preg_match('/(moyen|★★)/i', $difficulty)) {
        $difficultyStars = '⭐⭐';
    } elseif (preg_match('/(difficile|★★★)/i', $difficulty)) {
        $difficultyStars = '⭐⭐⭐';
    }

    $content = isset($exercise['content']) ? $exercise['content'] : '';
    $correction = isset($exercise['correction']) ? $exercise['correction'] : '';

    // Nettoyer le HTML
    $content = preg_replace('/<div class="correction">.*?<\/div>/s', '', $content);
    $content = trim($content);

    // Formater la correction
    $correctionHtml = '';
    if ($correction) {
        $correctionHtml = '<div class="success-message" style="display:none;" id="correction-' . uniqid() . '">' . 
                         strip_tags($correction, '<p><strong><br><ul><li>') . 
                         '</div>';
    }

    $html = <<<HTML
        <div class="exercise-card">
            <div class="exercise-header">
                <h3>{$title}</h3>
                <span class="difficulty">{$difficultyStars}</span>
            </div>
            <div class="exercise-content">
                {$content}
            </div>
            <button class="btn-coach" onclick="showCorrection('{$subject}-' + Math.random().toString(36).substr(2, 9))">
                Voir la correction
            </button>
            {$correctionHtml}
        </div>
HTML;

    return $html;
}

/**
 * Ajoute des exercices à un fichier PHP existant
 */
function addExercisesToFile($filePath, $newExercises, $subject) {
    if (!file_exists($filePath)) {
        echo "⚠️ Fichier non trouvé : $filePath - Création d'un nouveau fichier de base\n";
        // On ne crée pas de nouveau fichier ici, on attend qu'il existe
        return false;
    }

    // Extraire les exercices existants
    $existingExercises = extractExistingExercises($filePath);
    $existingTitles = array_map(function($ex) {
        return strtolower(trim($ex['title']));
    }, $existingExercises);

    // Filtrer les nouveaux exercices (éviter les doublons)
    $exercisesToAdd = [];
    foreach ($newExercises as $exercise) {
        $title = strtolower(trim($exercise['title']));
        if (!in_array($title, $existingTitles)) {
            $exercisesToAdd[] = $exercise;
        } else {
            echo "⏭️ Exercice déjà présent, ignoré : {$exercise['title']}\n";
        }
    }

    if (empty($exercisesToAdd)) {
        echo "✅ Aucun nouvel exercice à ajouter pour $filePath\n";
        return true;
    }

    // Lire le contenu du fichier
    $content = file_get_contents($filePath);

    // Trouver la section appropriée pour ajouter les exercices
    // Chercher la section avec id="$subject" ou créer une nouvelle section
    $sectionPattern = '/<section\s+id="' . preg_quote($subject, '/') . '">(.*?)<\/section>/s';
    
    if (preg_match($sectionPattern, $content, $matches)) {
        // Section existe, ajouter à la fin avant la fermeture
        $sectionContent = $matches[1];
        $newExercisesHtml = '';
        
        foreach ($exercisesToAdd as $exercise) {
            $newExercisesHtml .= formatExerciseForApp($exercise, $subject) . "\n\n";
        }

        // Ajouter avant la fermeture de la section
        $newSectionContent = rtrim($sectionContent) . "\n\n" . $newExercisesHtml;
        $content = str_replace($matches[0], '<section id="' . $subject . '">' . $newSectionContent . '</section>', $content);
    } else {
        // Section n'existe pas, créer une nouvelle section avant la fin du main
        $newExercisesHtml = '';
        foreach ($exercisesToAdd as $exercise) {
            $newExercisesHtml .= formatExerciseForApp($exercise, $subject) . "\n\n";
        }

        $subjectUpper = strtoupper($subject);
        $subjectTitle = ucfirst($subject);
        $newSection = <<<HTML

    <!-- {$subjectUpper} -->
    <section id="{$subject}">
        <h2>📚 {$subjectTitle}</h2>
        {$newExercisesHtml}
    </section>

HTML;

        // Ajouter avant la fermeture du main ou avant le script
        if (preg_match('/(<script>.*?<\/script>)/s', $content, $scriptMatch)) {
            $content = str_replace($scriptMatch[0], $newSection . "\n\n" . $scriptMatch[0], $content);
        } elseif (preg_match('/(<\/main>)/', $content, $mainMatch)) {
            $content = str_replace($mainMatch[0], $newSection . "\n\n" . $mainMatch[0], $content);
        }
    }

    // Sauvegarder
    file_put_contents($filePath, $content);
    echo "✅ " . count($exercisesToAdd) . " exercices ajoutés à $filePath\n";

    return true;
}

// ========== EXÉCUTION PRINCIPALE ==========

echo "🚀 Début de l'extraction des exercices...\n\n";

// Parser le guide Collège
echo "📖 Parsing du guide Collège...\n";
$collegeExercises = parseGuideHTML($guide_college);
echo "   ✅ " . count($collegeExercises) . " exercices trouvés\n\n";

// Parser le guide Lycée
echo "📖 Parsing du guide Lycée...\n";
$lyceeExercises = parseGuideHTML($guide_lycee);
echo "   ✅ " . count($lyceeExercises) . " exercices trouvés\n\n";

// Grouper les exercices par niveau et matière
$groupedExercises = [];

foreach ($collegeExercises as $exercise) {
    $level = normalizeLevel($exercise['level'] ?? '');
    $subject = normalizeSubject($exercise['subject'] ?? '');
    
    if ($level && $subject) {
        $key = "{$level}-{$subject}";
        if (!isset($groupedExercises[$key])) {
            $groupedExercises[$key] = [];
        }
        $groupedExercises[$key][] = $exercise;
    }
}

foreach ($lyceeExercises as $exercise) {
    $level = normalizeLevel($exercise['level'] ?? '');
    $subject = normalizeSubject($exercise['subject'] ?? '');
    
    if ($level && $subject) {
        $key = "{$level}-{$subject}";
        if (!isset($groupedExercises[$key])) {
            $groupedExercises[$key] = [];
        }
        $groupedExercises[$key][] = $exercise;
    }
}

echo "📊 Répartition des exercices par niveau et matière :\n";
foreach ($groupedExercises as $key => $exercises) {
    echo "   • $key : " . count($exercises) . " exercices\n";
}
echo "\n";

// Mapping des niveaux vers les fichiers
$fileMapping = [
    '6eme' => [
        'francais' => __DIR__ . '/../../pages/college/6eme/exercices-6eme.php',
        'maths' => __DIR__ . '/../../pages/college/6eme/exercices-6eme.php',
        'sciences' => __DIR__ . '/../../pages/college/6eme/exercices-6eme.php',
    ],
    '5eme' => [
        'francais' => __DIR__ . '/../../pages/college/5eme/exercices-5eme.php',
        'maths' => __DIR__ . '/../../pages/college/5eme/exercices-5eme.php',
        'sciences' => __DIR__ . '/../../pages/college/5eme/exercices-5eme.php',
    ],
    '4eme' => [
        'francais' => __DIR__ . '/../../pages/college/4eme/exercices-4eme.php',
        'maths' => __DIR__ . '/../../pages/college/4eme/exercices-4eme.php',
        'sciences' => __DIR__ . '/../../pages/college/4eme/exercices-4eme.php',
    ],
    '3eme' => [
        'francais' => __DIR__ . '/../../pages/college/3eme/exercices-3eme.php',
        'maths' => __DIR__ . '/../../pages/college/3eme/exercices-3eme.php',
        'sciences' => __DIR__ . '/../../pages/college/3eme/exercices-3eme.php',
    ],
    'seconde' => [
        'francais' => __DIR__ . '/../../pages/lycee/seconde/exercices-seconde.php',
        'maths' => __DIR__ . '/../../pages/lycee/seconde/exercices-seconde.php',
        'sciences' => __DIR__ . '/../../pages/lycee/seconde/exercices-seconde.php',
    ],
    'premiere' => [
        'francais' => __DIR__ . '/../../pages/lycee/premiere/exercices-premiere.php',
        'maths' => __DIR__ . '/../../pages/lycee/premiere/exercices-premiere.php',
        'sciences' => __DIR__ . '/../../pages/lycee/premiere/exercices-premiere.php',
    ],
    'terminale' => [
        'francais' => __DIR__ . '/../../pages/lycee/terminale/exercices-terminale.php',
        'maths' => __DIR__ . '/../../pages/lycee/terminale/exercices-terminale.php',
        'sciences' => __DIR__ . '/../../pages/lycee/terminale/exercices-terminale.php',
        'philosophie' => __DIR__ . '/../../pages/lycee/terminale/exercices-terminale.php',
    ],
];

// Intégrer les exercices dans les fichiers appropriés
echo "🔧 Intégration des exercices...\n\n";

foreach ($groupedExercises as $key => $exercises) {
    list($level, $subject) = explode('-', $key, 2);
    
    if (isset($fileMapping[$level][$subject])) {
        $filePath = $fileMapping[$level][$subject];
        echo "📝 Traitement de $key → $filePath\n";
        addExercisesToFile($filePath, $exercises, $subject);
    } else {
        echo "⚠️ Pas de fichier défini pour $key\n";
    }
}

echo "\n✅ Extraction et intégration terminées !\n";

