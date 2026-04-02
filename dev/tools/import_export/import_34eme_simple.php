<?php
/**
 * Importateur 3ème/4ème robuste utilisant parsing par ligne simple
 */

require_once __DIR__ . '/../db/connection.php';

if (!$pdo) {
    die("❌ DB indisponible\n");
}

function clean_text($text) {
    // Nettoyer les accents pour comparaison
    return strtoupper(str_replace(['é', 'è', 'ê', 'ç', 'À', 'Á', 'Â', 'Ã', 'Ä', 'à', 'á', 'â', 'ã', 'ä', 'Ç'], 
                                  ['E', 'E', 'E', 'C', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'C'], $text));
}

$dryRun = in_array('--dry-run', $argv);
$sourceDir = __DIR__ . '/../exercices/college';

echo "🚀 Import 3ème/4ème depuis Markdown\n\n";

foreach (['4eme' => '4ème', '3eme' => '3ème'] as $filename => $levelName) {
    $filepath = $sourceDir . '/' . $filename . ' exercices&correction.md';
    if (!file_exists($filepath)) {
        echo "⚠️  Fichier introuvable : $filepath\n";
        continue;
    }
    
    echo "📖 Traitement $levelName...\n";
    $lines = file($filepath);
    
    // Parser simple par ligne
    $subjects = ['Mathématiques' => [], 'Français' => [], 'Anglais' => []];
    $currentSubject = null;
    $currentExercise = null;
    $inExercise = false;
    $exerciseContent = '';
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = rtrim($lines[$i]);
        
        // Détecter changement de matière (## MATIÈRE)
        if (preg_match('/^##\s+(MATHÉMATIQUES|FRANÇAIS|ANGLAIS|Corrections)/i', $line, $m)) {
            // Sauvegarder exercice précédent
            if ($inExercise && $currentSubject && $currentExercise) {
                $subjects[$currentSubject][$currentExercise['num']] = $currentExercise;
                $inExercise = false;
            }
            
            $matière = strtoupper(str_replace('é', 'e', str_replace('ç', 'c', $m[1])));
            if (in_array($matière, ['MATHEMATIQUES', 'FRANCAIS', 'ANGLAIS'])) {
                $currentSubject = str_replace('MATHEMATIQUES', 'Mathématiques', str_replace('FRANCAIS', 'Français', $matière));
            } else {
                $currentSubject = null;
            }
            continue;
        }
        
        // Détecter exercice (#### Exercice X.Y - Titre)
        if ($currentSubject && preg_match('/^####\s+Exercice\s+([0-9]+\.[0-9]+)\s+-\s+(.+)$/', $line, $m)) {
            // Sauvegarder précédent
            if ($inExercise && $currentExercise) {
                $subjects[$currentSubject][$currentExercise['num']] = $currentExercise;
            }
            
            $currentExercise = [
                'num' => $m[1],
                'title' => trim($m[2]),
                'content' => ''
            ];
            $inExercise = true;
            $exerciseContent = '';
            continue;
        }
        
        // Accumuler le contenu si on est dans un exercice
        if ($inExercise && !preg_match('/^####/', $line)) {
            $exerciseContent .= $line . "\n";
            $currentExercise['content'] = trim($exerciseContent);
        }
    }
    
    // Sauvegarder dernier exercice
    if ($inExercise && $currentSubject && $currentExercise) {
        $subjects[$currentSubject][$currentExercise['num']] = $currentExercise;
    }
    
    // Parser corrections (même approche)
    $corrections = ['Mathématiques' => [], 'Français' => [], 'Anglais' => []];
    $currentCorrSubject = null;
    $currentCorrExercise = null;
    $inCorrection = false;
    $correctionContent = '';
    
    $inCorrectionsSection = false;
    for ($i = 0; $i < count($lines); $i++) {
        $line = rtrim($lines[$i]);
        
        if (preg_match('/^##\s+Corrections/i', $line)) {
            $inCorrectionsSection = true;
            continue;
        }
        
        if (!$inCorrectionsSection) continue;
        
        // Détecter matière de correction (### Matière - Réponses)
        if (preg_match('/^###\s+(MATHÉMATIQUES|FRANÇAIS|ANGLAIS)\s+-\s+RÉ?PONSES/i', $line, $m)) {
            $matière = $m[1];
            $currentCorrSubject = str_replace(['MATHEMATIQUES', 'FRANCAIS', 'ANGLAIS'],
                                            ['Mathématiques', 'Français', 'Anglais'], strtoupper(str_replace('é', 'e', str_replace('ç', 'c', $matière))));
            continue;
        }
        
        // Détecter correction (Exercice X.Y :)
        if ($currentCorrSubject && preg_match('/^\*?\*?Exercice\s+([0-9]+\.[0-9]+)\s*:\*?\*?\s*$/', $line, $m)) {
            // Lire jusqu'à la prochaine "Exercice" ou fin de section
            $num = $m[1];
            $corrText = '';
            for ($j = $i + 1; $j < count($lines); $j++) {
                $nextLine = rtrim($lines[$j]);
                if (preg_match('/^\*?\*?Exercice\s+[0-9]+\.[0-9]+/', $nextLine) || preg_match('/^###/', $nextLine)) {
                    break;
                }
                $corrText .= $nextLine . "\n";
            }
            if (trim($corrText)) {
                $corrections[$currentCorrSubject][$num] = trim($corrText);
            }
        }
    }
    
    // Importer en base
    $pdo->beginTransaction();
    try {
        foreach (['Mathématiques', 'Français', 'Anglais'] as $subject) {
            // Supprimer anciens
            $del = $pdo->prepare("DELETE FROM Exercises WHERE Level = ? AND Subject = ?");
            if (!$dryRun) {
                $del->execute([$levelName, $subject]);
            }
            
            // Insérer nouveaux
            $ins = $pdo->prepare("INSERT INTO Exercises (Subject, Level, Title, Content, Answer) VALUES (?, ?, ?, ?, ?)");
            $count = 0;
            foreach ($subjects[$subject] as $num => $ex) {
                $ans = $corrections[$subject][$num] ?? '';
                $title = 'Exercice ' . $num . ' - ' . $ex['title'];
                
                if (!$dryRun) {
                    $ins->execute([$subject, $levelName, $title, $ex['content'], $ans]);
                }
                $count++;
            }
            
            if ($count > 0) {
                echo "  ✅ $subject : $count exercices " . ($dryRun ? '(simulé)' : 'importés') . "\n";
            }
        }
        
        if (!$dryRun) {
            $pdo->commit();
        } else {
            $pdo->rollBack();
        }
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "  ❌ Erreur : " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Import terminé.\n";
