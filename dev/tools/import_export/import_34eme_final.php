<?php
require_once __DIR__ . '/../db/connection.php';

$dryRun = in_array('--dry-run', $argv);
$sourceDir = __DIR__ . '/../exercices/college';

echo "🚀 Import 3ème/4ème - Version simple\n\n";

$totalInserted = 0;

foreach (['4eme' => '4ème', '3eme' => '3ème'] as $prefix => $levelName) {
    $filepath = $sourceDir . '/' . $prefix . ' exercices&correction.md';
    if (!file_exists($filepath)) {
        echo "⚠️  {$filepath} introuvable\n";
        continue;
    }
    
    echo "📖 {$levelName}\n";
    $md = file_get_contents($filepath);
    
    // Extraire chaque matière de contenu + corrections
    foreach (['Mathématiques' => 'MATHÉMATIQUES', 'Français' => 'FRANÇAIS', 'Anglais' => 'ANGLAIS'] as $subject => $heading) {
        // Extraire la section du sujet principal
        if (!preg_match('/##\s+' . preg_quote($heading) . '\s*\n([\s\S]*?)(?=\n##\s+|\Z)/i', $md, $m)) {
            continue;
        }
        $subjectSection = $m[1];
        
        // Extraire les exercices
        $exercises = [];
        $pattern = '/^####\s+Exercice\s+([0-9]+\.[0-9]+)\s+-\s+([^\n]+)\n([\s\S]*?)(?=^####|\Z)/m';
        if (preg_match_all($pattern, $subjectSection, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $num = $match[1];
                $title = trim($match[2]);
                $content = trim($match[3]);
                $content = preg_replace('/^---+\s*$/m', '', $content);
                $exercises[$num] = ['title' => $title, 'content' => $content];
            }
        }
        
        if (empty($exercises)) {
            continue;
        }
        
        // Extraire les corrections
        $corrections = [];
        $pattern = '/##+\s+' . preg_quote($subject) . '\s+-\s+R[ÉE]PONSES.*?\n([\s\S]*?)(?=\n###|\Z)/i';
        if (preg_match($pattern, $md, $m)) {
            $corrSection = $m[1];
            $pattern = '/^[*]*Exercice\s+([0-9]+\.[0-9]+)\s*:[*]*\s*\n([\s\S]*?)(?=^[*]*Exercice\s+[0-9]+\.[0-9]+|\Z)/m';
            if (preg_match_all($pattern, $corrSection, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $num = $match[1];
                    $answer = trim($match[2]);
                    if (!empty($answer)) {
                        $corrections[$num] = $answer;
                    }
                }
            }
        }
        
        // Importer en base
        $pdo->beginTransaction();
        try {
            $del = $pdo->prepare("DELETE FROM Exercises WHERE Level = ? AND Subject = ?");
            if (!$dryRun) {
                $del->execute([$levelName, $subject]);
            }
            
            $ins = $pdo->prepare("INSERT INTO Exercises (Subject, Level, Title, Content, Answer) VALUES (?, ?, ?, ?, ?)");
            $count = 0;
            foreach ($exercises as $num => $ex) {
                $answer = $corrections[$num] ?? '';
                $title = 'Exercice ' . $num . ' - ' . $ex['title'];
                
                if (!$dryRun) {
                    $ins->execute([$subject, $levelName, $title, $ex['content'], $answer]);
                }
                $count++;
            }
            
            if (!$dryRun) {
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
            
            echo "  ✅ {$subject} : {$count} exercices " . ($dryRun ? '(simulé)' : 'importés') . "\n";
            $totalInserted += $count;
        } catch (Throwable $e) {
            $pdo->rollBack();
            echo "  ❌ {$subject} : " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Total: {$totalInserted} exercices " . ($dryRun ? '(simulation)' : 'importés') . "\n";
