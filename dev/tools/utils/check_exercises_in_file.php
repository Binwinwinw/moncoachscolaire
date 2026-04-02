<?php
/**
 * Script pour vérifier la présence d'exercices dans le fichier sources
 * pour les niveaux manquants : 4ème, 5ème, Terminale
 */

$sourcesFile = __DIR__ . '/../docs/exercices-sources-par-niveau.md';

if (!file_exists($sourcesFile)) {
    die("❌ Fichier non trouvé : $sourcesFile\n");
}

echo "🔍 Vérification des exercices dans le fichier sources\n\n";

$content = file_get_contents($sourcesFile);
$lines = explode("\n", $content);

$targetLevels = ['5ème', '4ème', 'Terminale'];
$results = [];

foreach ($targetLevels as $level) {
    echo "📚 Recherche pour le niveau : $level\n";
    
    $inLevel = false;
    $inSubject = false;
    $currentSubject = null;
    $exerciseCount = 0;
    $exercisesBySubject = [];
    
    foreach ($lines as $lineNum => $line) {
        $lineTrimmed = trim($line);
        
        // Détecter le niveau
        if (preg_match('/^##\s*[🎓📚]*\s*' . preg_quote($level, '/') . '\s*$/i', $lineTrimmed)) {
            $inLevel = true;
            $inSubject = false;
            $currentSubject = null;
            echo "   ✅ Section niveau trouvée à la ligne " . ($lineNum + 1) . "\n";
            continue;
        }
        
        // Si on rencontre un autre niveau, arrêter
        if ($inLevel && preg_match('/^##\s*[🎓📚]*\s*(\d+[èmee]+|Seconde|Premi[èe]re|Terminale|BAC)\s*$/i', $lineTrimmed, $matches)) {
            $otherLevel = trim($matches[1]);
            if ($otherLevel !== $level) {
                $inLevel = false;
                $inSubject = false;
                continue;
            }
        }
        
        // Détecter les matières dans ce niveau
        if ($inLevel && preg_match('/^###\s*(.+?)$/', $lineTrimmed, $matches)) {
            $subject = trim($matches[1]);
            // Ignorer si c'est un exercice (#### Exercice)
            if (!preg_match('/^Exercice\s+\d+/i', $subject)) {
                $currentSubject = $subject;
                $inSubject = true;
                if (!isset($exercisesBySubject[$subject])) {
                    $exercisesBySubject[$subject] = 0;
                }
                echo "   📖 Matière trouvée : $subject (ligne " . ($lineNum + 1) . ")\n";
            }
        }
        
        // Compter les exercices
        if ($inLevel && preg_match('/^####\s*Exercice\s+\d+\s*:\s*(.+?)$/', $lineTrimmed, $matches)) {
            $exerciseCount++;
            $title = trim($matches[1]);
            if ($currentSubject) {
                $exercisesBySubject[$currentSubject]++;
            }
            echo "      • Exercice : $title (ligne " . ($lineNum + 1) . ")\n";
        }
    }
    
    echo "\n   📊 Total d'exercices pour $level : $exerciseCount\n";
    if (!empty($exercisesBySubject)) {
        echo "   📊 Répartition par matière :\n";
        foreach ($exercisesBySubject as $subject => $count) {
            echo "      - $subject : $count exercice(s)\n";
        }
    }
    
    $results[$level] = [
        'found' => $exerciseCount > 0,
        'count' => $exerciseCount,
        'subjects' => $exercisesBySubject
    ];
    
    echo "\n";
}

// Résumé
echo "═══════════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n\n";

foreach ($results as $level => $data) {
    if ($data['found']) {
        echo "✅ $level : {$data['count']} exercice(s) trouvé(s)\n";
    } else {
        echo "❌ $level : AUCUN exercice trouvé dans le fichier\n";
    }
}

echo "\n";

// Vérifier aussi le format des exercices trouvés
echo "🔍 Vérification du format des exercices (exemple pour 5ème) :\n";
$in5eme = false;
$exerciseExample = null;
$lineCount = 0;

foreach ($lines as $lineNum => $line) {
    $lineTrimmed = trim($line);
    
    if (preg_match('/^##\s*[🎓📚]*\s*5ème\s*$/i', $lineTrimmed)) {
        $in5eme = true;
        continue;
    }
    
    if ($in5eme && preg_match('/^##\s*[🎓📚]*\s*4ème\s*$/i', $lineTrimmed)) {
        break; // Arrêter à la section suivante
    }
    
    if ($in5eme && preg_match('/^####\s*Exercice\s+\d+\s*:\s*(.+?)$/', $lineTrimmed, $matches)) {
        if (!$exerciseExample) {
            $exerciseExample = [
                'title' => trim($matches[1]),
                'line' => $lineNum + 1,
                'content' => [],
                'answer' => []
            ];
            echo "\n   Exemple d'exercice trouvé :\n";
            echo "   - Titre : {$exerciseExample['title']} (ligne {$exerciseExample['line']})\n";
            
            // Lire les lignes suivantes pour voir le format
            $inContent = false;
            $inAnswer = false;
            for ($i = $lineNum + 1; $i < min($lineNum + 20, count($lines)); $i++) {
                $nextLine = trim($lines[$i]);
                
                if (preg_match('/^\*\*Contenu\*\*\s*:?\s*$/', $nextLine)) {
                    $inContent = true;
                    $inAnswer = false;
                    continue;
                }
                if (preg_match('/^\*\*R[ée]ponse attendue\*\*\s*:?\s*$/', $nextLine)) {
                    $inContent = false;
                    $inAnswer = true;
                    continue;
                }
                if (preg_match('/^####\s*Exercice/', $nextLine)) {
                    break; // Nouvel exercice
                }
                
                if ($inContent && $nextLine !== '') {
                    $exerciseExample['content'][] = $nextLine;
                }
                if ($inAnswer && $nextLine !== '') {
                    $exerciseExample['answer'][] = $nextLine;
                }
            }
            
            echo "   - Contenu trouvé : " . (empty($exerciseExample['content']) ? '❌ NON' : '✅ OUI (' . count($exerciseExample['content']) . ' lignes)') . "\n";
            echo "   - Réponse trouvée : " . (empty($exerciseExample['answer']) ? '❌ NON' : '✅ OUI (' . count($exerciseExample['answer']) . ' lignes)') . "\n";
            
            if (!empty($exerciseExample['content'])) {
                echo "   - Première ligne de contenu : " . substr($exerciseExample['content'][0], 0, 60) . "...\n";
            }
            if (!empty($exerciseExample['answer'])) {
                echo "   - Première ligne de réponse : " . substr($exerciseExample['answer'][0], 0, 60) . "...\n";
            }
            
            break; // Un seul exemple suffit
        }
    }
}

echo "\n✅ Vérification terminée !\n";

