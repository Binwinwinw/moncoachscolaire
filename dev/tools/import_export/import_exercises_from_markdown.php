<?php
/**
 * Import des exercices depuis les fichiers Markdown structurés
 * Parcourt exercices/college/<niveau>/<matiere>/*.md
 * Détecte Titre (premier H1) et Corrigé (section Solution/Correction/Réponse)
 * Insère dans la table Exercises avec remplacement optionnel par matière/niveau.
 */

require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../includes/exercice_loader.php';

if (!$pdo) { fwrite(STDERR, "DB indisponible\n"); exit(2); }
if (function_exists('db_is_read_only') && db_is_read_only()) { fwrite(STDERR, "Mode lecture seule\n"); exit(3); }

function md_read($path){ return file_exists($path) ? file_get_contents($path) : ''; }
function md_title($md){
    // Premier H1
    if (preg_match('/^\s*#\s*(.+)$/m', $md, $m)) return trim($m[1]);
    // Fallback: première ligne non vide
    foreach (preg_split('/\r\n|\r|\n/', $md) as $line){ $line = trim($line); if ($line !== '') return $line; }
    return 'Exercice';
}
function md_answer($md){
    // Chercher section Solution/Correction/Réponse jusqu'au prochain H2/H1
    $patterns = [
        '/^\s*##\s*Solution[\s\S]*?(?=^\s*##\s|^\s*#\s|\z)/m',
        '/^\s*##\s*Correction[\s\S]*?(?=^\s*##\s|^\s*#\s|\z)/m',
        '/^\s*##\s*R\x{00E9}ponse[\s\S]*?(?=^\s*##\s|^\s*#\s|\z)/mu',
    ];
    foreach ($patterns as $p){ if (preg_match($p, $md, $m)) return trim($m[0]); }
    // Sinon, dernière section si présente
    if (preg_match('/(##\s.*)$/ms', $md, $m)) return trim($m[1]);
    return '';
}
function md_content_core($md){
    // Retirer Titre H1 et sections de métadonnées éventuelles
    $lines = preg_split('/\r\n|\r|\n/', $md);
    // remove leading H1 line
    if (isset($lines[0]) && preg_match('/^\s*#\s+/', $lines[0])) array_shift($lines);
    return trim(implode("\n", $lines));
}

function import_dir($levelDir, $levelLabel){
    global $pdo;
    $subjects = [
        'mathematiques' => 'Mathématiques',
        'francais' => 'Français',
        'histoire-geo' => 'Histoire-Géographie',
        'svt' => 'SVT',
        'anglais' => 'Anglais',
    ];
    foreach ($subjects as $subDir => $subjectLabel){
        $dir = $levelDir . DIRECTORY_SEPARATOR . $subDir;
        if (!is_dir($dir)) continue;
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.md');
        if (!$files) continue;
        echo "[INFO] Import {$levelLabel}/{$subjectLabel} depuis {$dir}\n";
        $pdo->beginTransaction();
        try {
            // Remplacer le corpus pour ce couple
            $del = $pdo->prepare("DELETE FROM Exercises WHERE Level = ? AND Subject = ?");
            $del->execute([$levelLabel, $subjectLabel]);
            $ins = $pdo->prepare("INSERT INTO Exercises (Subject, Level, Title, Content, Answer) VALUES (?, ?, ?, ?, ?)");
            $inserted = 0;
            foreach ($files as $f){
                $md = md_read($f);
                if ($md === '') continue;
                $title = md_title($md);
                $answer = md_answer($md);
                $content = md_content_core($md);
                $ins->execute([$subjectLabel, $levelLabel, $title, $content, $answer]);
                $inserted++;
            }
            $pdo->commit();
            echo "[OK] {$levelLabel}/{$subjectLabel}: {$inserted} exercices insérés.\n";
        } catch (Throwable $e){
            $pdo->rollBack();
            fwrite(STDERR, "[ERREUR] {$levelLabel}/{$subjectLabel}: " . $e->getMessage() . "\n");
        }
    }
}

$base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'college';
$levels = [
    '6eme' => '6ème',
    '5eme' => '5ème',
    '4eme' => '4ème',
    '3eme' => '3ème',
];

foreach ($levels as $dirName => $label){
    $levelDir = $base . DIRECTORY_SEPARATOR . $dirName;
    if (is_dir($levelDir)) import_dir($levelDir, $label);
}

echo "[DONE] Import Markdown terminé.";
