<?php
/**
 * Importe les exercices 3ème et 4ème depuis des fichiers Markdown
 * Structure attendue : sections Mathématiques/Français/Anglais
 * avec exercices numérotés (Exercice X.Y) et corrections
 * Usage: php tools/import_34eme_md.php [--dry-run]
 */

require_once __DIR__ . '/../db/connection.php';

if (!$pdo) {
    fwrite(STDERR, "❌ DB indisponible\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $argv);
$sourceDir = __DIR__ . '/../exercices/college';

$files = [
    '4ème' => $sourceDir . '/4eme exercices&correction.md',
    '3ème' => $sourceDir . '/3eme exercices&correction.md',
];

$subjects = ['Mathématiques', 'Français', 'Anglais'];

function remove_accents($text) {
    $unwanted = [
        'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ç'=>'c',
        'Á'=>'A', 'É'=>'E', 'Í'=>'I', 'Ó'=>'O', 'Ú'=>'U', 'Ç'=>'C',
        'à'=>'a', 'è'=>'e', 'ù'=>'u', 'À'=>'A', 'È'=>'E', 'Ù'=>'U',
        'ä'=>'a', 'ë'=>'e', 'ï'=>'i', 'ö'=>'o', 'ü'=>'u',
        'Ä'=>'A', 'Ë'=>'E', 'Ï'=>'I', 'Ö'=>'O', 'Ü'=>'U'
    ];
    return strtr($text, $unwanted);
}

function extract_section($md, $heading) {
    $lines = explode("\n", $md);
    $start = -1;
    $end = count($lines);
    
    $target = strtoupper(remove_accents($heading));
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        // Chercher ## ou ### suivi du heading
        if (preg_match('/^(#{2,3})\s+(.+?)(?:\s*\([^)]*\))?\s*$/', $line, $m)) {
            $candidate = strtoupper(remove_accents(trim($m[2])));
            if (strpos($candidate, $target) === 0 || $candidate === $target) {
                $start = $i + 1;
            } elseif ($start !== -1) {
                $end = $i;
                break;
            }
        }
    }
    
    if ($start === -1) {
        return '';
    }
    
    return trim(implode("\n", array_slice($lines, $start, $end - $start)));
}

function parse_exercises($sectionText) {
    $pattern = '/^####\s+Exercice\s+([0-9]+\.[0-9]+)\s+-\s+([^\n]+)\s*\n+([\s\S]*?)(?=\n####\s+Exercice|\Z)/mi';
    $out = [];
    if (preg_match_all($pattern, $sectionText, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $num = $match[1];
            $title = trim($match[2]);
            $body = trim($match[3]);
            $body = preg_replace('/\n?-{3,}\s*$/', '', $body);
            $out[$num] = [
                'number' => $num,
                'title' => $title,
                'content' => $body,
            ];
        }
    }
    return $out;
}

function parse_corrections($corrText) {
    // Pattern : "**Exercice X.Y :**" ou "Exercice X.Y :" au début d'une ligne
    $pattern = '/^\*?\*?Exercice\s+([0-9]+\.[0-9]+)\s*:\*?\*?\s*\n?([\s\S]*?)(?=\n\*?\*?Exercice\s+[0-9]+\.[0-9]+|\n---|\n###|\Z)/mi';
    $out = [];
    if (preg_match_all($pattern, $corrText, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $num = $match[1];
            $txt = trim($match[2]);
            if (!empty($txt)) {
                $out[$num] = $txt;
            }
        }
    }
    return $out;
}

echo "🚀 Import 3ème/4ème depuis Markdown\n\n";

$totalInserted = 0;
$totalUpdated = 0;

foreach ($files as $level => $filepath) {
    if (!file_exists($filepath)) {
        echo "⚠️  Fichier non trouvé : $filepath\n";
        continue;
    }
    
    echo "📖 Traitement du niveau $level...\n";
    $md = file_get_contents($filepath);
    
    // Extraire section corrections (tout après ## Corrections)
    $allCorrections = extract_section($md, 'Corrections');
    
    echo "  📖 Extracting corrections (" . strlen($allCorrections) . " chars)...\n";
    
    foreach ($subjects as $subject) {
        $section = extract_section($md, $subject);
        if (empty($section)) {
            echo "  ⚠️  Aucune section '$subject' trouvée pour $level\n";
            continue;
        }
        
        $exercises = parse_exercises($section);
        
        // Chercher la section correction correspondante
        // Dans la section Corrections, chercher "### Mathématiques - Réponses" ou variantes
        $correctionSection = '';
        if (!empty($allCorrections)) {
            $correctionHeadings = [
                $subject . ' - Réponses',
                strtoupper($subject) . ' - Réponses',
                $subject . ' - RÉPONSES',
            ];
            foreach ($correctionHeadings as $key) {
                $temp = extract_section($allCorrections, $key);
                if (!empty($temp)) {
                    $correctionSection = $temp;
                    break;
                }
            }
        }
        
        $corrections = parse_corrections($correctionSection);
        
        if (empty($exercises)) {
            echo "  ⚠️  Aucun exercice trouvé pour $level/$subject\n";
            continue;
        }
        
        echo "  📚 $subject : " . count($exercises) . " exercices\n";
        
        $pdo->beginTransaction();
        try {
            $del = $pdo->prepare("DELETE FROM Exercises WHERE Level = ? AND Subject = ?");
            if (!$dryRun) {
                $del->execute([$level, $subject]);
            }
            
            $ins = $pdo->prepare("INSERT INTO Exercises (Subject, Level, Title, Content, Answer) VALUES (?, ?, ?, ?, ?)");
            
            $inserted = 0;
            foreach ($exercises as $num => $ex) {
                $ans = $corrections[$num] ?? '';
                $title = 'Exercice ' . $num . ' - ' . $ex['title'];
                
                if (!$dryRun) {
                    $ins->execute([$subject, $level, $title, $ex['content'], $ans]);
                }
                $inserted++;
            }
            
            if (!$dryRun) {
                $pdo->commit();
            } else {
                $pdo->rollBack();
            }
            
            $totalInserted += $inserted;
            echo "     ✅ $inserted exercices " . ($dryRun ? '(simulé)' : 'importés') . "\n";
        } catch (Throwable $e) {
            $pdo->rollBack();
            echo "     ❌ Erreur : " . $e->getMessage() . "\n";
        }
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "✅ Total exercices importés : $totalInserted\n";

if ($dryRun) {
    echo "⚠️  Mode DRY-RUN (simulation)\n";
    echo "   Exécutez sans --dry-run pour importer réellement.\n";
} else {
    echo "✅ Import terminé avec succès !\n";
}
echo "\n";
