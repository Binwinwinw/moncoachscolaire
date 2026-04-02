<?php
/**
 * Script de débogage pour comprendre pourquoi parseSourcesFile ne fonctionne pas
 */

$sourcesFile = __DIR__ . '/../docs/exercices-sources-par-niveau.md';
$content = file_get_contents($sourcesFile);
$lines = explode("\n", $content);

echo "🔍 Débogage du parsing - Analyse ligne par ligne pour 5ème\n\n";

$in5eme = false;
$currentLevel = null;
$currentSubject = null;
$currentExercise = null;
$currentSection = null;
$exerciseCount = 0;

foreach ($lines as $lineNum => $line) {
    $lineTrimmed = trim($line);
    $lineNumDisplay = $lineNum + 1;
    
    // Détecter le niveau 5ème
    if (preg_match('/^##\s*[🎓📚]*\s*5ème\s*$/i', $lineTrimmed)) {
        $in5eme = true;
        $currentLevel = '5ème';
        echo "✅ Ligne $lineNumDisplay : Niveau 5ème détecté\n";
        continue;
    }
    
    // Si on sort de la section 5ème, arrêter
    if ($in5eme && preg_match('/^##\s*[🎓📚]*\s*4ème\s*$/i', $lineTrimmed)) {
        echo "⏹️  Ligne $lineNumDisplay : Fin de la section 5ème\n";
        break;
    }
    
    if (!$in5eme) continue;
    
    // Détecter un nouvel exercice (AVANT les matières pour éviter les conflits)
    if (preg_match('/^####\s*Exercice\s+\d+\s*:\s*(.+?)$/', $lineTrimmed, $matches)) {
        if ($currentExercise) {
            echo "💾 Sauvegarde exercice précédent : {$currentExercise['title']}\n";
            echo "   - Contenu : " . (empty($currentExercise['content']) ? 'VIDE' : strlen($currentExercise['content']) . ' caractères') . "\n";
            echo "   - Réponse : " . (empty($currentExercise['answer']) ? 'VIDE' : strlen($currentExercise['answer']) . ' caractères') . "\n";
        }
        
        $currentExercise = [
            'title' => trim($matches[1]),
            'content' => '',
            'answer' => '',
            'level' => $currentLevel,
            'subject' => $currentSubject
        ];
        $currentSection = null;
        $exerciseCount++;
        echo "📝 Ligne $lineNumDisplay : NOUVEL EXERCICE #$exerciseCount : {$currentExercise['title']}\n";
        continue;
    }
    
    // Détecter les matières (APRÈS les exercices)
    if (preg_match('/^###\s*(.+?)$/', $lineTrimmed, $matches)) {
        $subjectRaw = trim($matches[1]);
        // Ignorer si c'est un exercice
        if (!preg_match('/^Exercice\s+\d+/i', $subjectRaw)) {
            $currentSubject = $subjectRaw;
            echo "📖 Ligne $lineNumDisplay : Matière = $currentSubject\n";
            continue;
        }
    }
    
    // Métadonnées
    if (preg_match('/^\*\*([^:]+)\*\*\s*:\s*(.+?)$/', $lineTrimmed, $matches)) {
        $key = strtolower(trim($matches[1]));
        if ($key === 'matière' || $key === 'niveau' || $key === 'type') {
            // Ignorer, déjà traité
            continue;
        }
    }
    
    // Détecter **Contenu** :
    if (preg_match('/^\*\*Contenu\*\*\s*:\s*$/', $lineTrimmed)) {
        $currentSection = 'content';
        echo "   ✅ Ligne $lineNumDisplay : Section CONTENU détectée\n";
        continue;
    }
    
    // Détecter **Réponse attendue** :
    if (preg_match('/^\*\*R[ée]ponse attendue\*\*\s*:\s*$/', $lineTrimmed)) {
        $currentSection = 'answer';
        echo "   ✅ Ligne $lineNumDisplay : Section RÉPONSE détectée\n";
        continue;
    }
    
    // Ajouter le contenu
    if ($currentExercise && $currentSection) {
        if ($currentSection === 'content') {
            $currentExercise['content'] .= ($currentExercise['content'] ? "\n" : '') . $line;
            if (strlen($currentExercise['content']) <= 100) {
                echo "   📄 Ligne $lineNumDisplay : Ajout au CONTENU : " . substr($line, 0, 50) . "...\n";
            }
        } elseif ($currentSection === 'answer') {
            $currentExercise['answer'] .= ($currentExercise['answer'] ? "\n" : '') . $line;
            if (strlen($currentExercise['answer']) <= 100) {
                echo "   📄 Ligne $lineNumDisplay : Ajout à la RÉPONSE : " . substr($line, 0, 50) . "...\n";
            }
        }
    }
}

// Afficher le dernier exercice
if ($currentExercise) {
    echo "\n💾 Dernier exercice : {$currentExercise['title']}\n";
    echo "   - Contenu : " . (empty($currentExercise['content']) ? 'VIDE ❌' : strlen($currentExercise['content']) . ' caractères ✅') . "\n";
    echo "   - Réponse : " . (empty($currentExercise['answer']) ? 'VIDE ❌' : strlen($currentExercise['answer']) . ' caractères ✅') . "\n";
    if (!empty($currentExercise['content'])) {
        echo "   - Aperçu contenu : " . substr($currentExercise['content'], 0, 80) . "...\n";
    }
    if (!empty($currentExercise['answer'])) {
        echo "   - Aperçu réponse : " . substr($currentExercise['answer'], 0, 80) . "...\n";
    }
}

echo "\n📊 Total d'exercices détectés : $exerciseCount\n";

