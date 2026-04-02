<?php
/**
 * Import des exercices 5ème depuis le fichier exercices/esxercices-5eme.md
 * - Parse les sections Mathématiques, Français, Anglais
 * - Associe les corrections par numéro d'exercice (Exercice X.Y)
 * - Remplace les exercices existants pour le niveau 5ème / matière concernée
 */

require_once __DIR__ . '/../db/connection.php';

if (!$pdo) {
    fwrite(STDERR, "DB indisponible\n");
    exit(2);
}
if (function_exists('db_is_read_only') && db_is_read_only()) {
    fwrite(STDERR, "Mode lecture seule\n");
    exit(3);
}

$source = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'esxercices-5eme.md';
if (!file_exists($source)) {
    fwrite(STDERR, "Fichier source introuvable: {$source}\n");
    exit(4);
}
$md = file_get_contents($source);

// Helpers
function extract_section($md, $heading) {
    $pattern = '/##\s+' . preg_quote($heading, '/') . '\s*(.+?)(?=\n##\s+|\z)/is';
    if (preg_match($pattern, $md, $m)) {
        return trim($m[1]);
    }
    return '';
}

function parse_exercises($sectionText) {
    // Capture each #### Exercice X.Y - Title ... until next ####
    $pattern = '/####\s+Exercice\s+([0-9]+\.[0-9]+)\s+-\s+([^\n]+)\s*\n+([\s\S]*?)(?=\n####\s+Exercice|\z)/i';
    $out = [];
    if (preg_match_all($pattern, $sectionText, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $num = $match[1];
            $title = trim($match[2]);
            $body = trim($match[3]);
            // Remove trailing '---' separators if any
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
    // Pattern "**Exercice 1.1 :**" ... until next **Exercice or heading
    $pattern = '/\*\*Exercice\s+([0-9]+\.[0-9]+)\s*:\s*\*\*\s*\n?([\s\S]*?)(?=\n\*\*Exercice\s+[0-9]+\.[0-9]+\s*:\s*\*\*|\n---|\z)/i';
    $out = [];
    if (preg_match_all($pattern, $corrText, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $num = $match[1];
            $txt = trim($match[2]);
            $out[$num] = $txt;
        }
    }
    return $out;
}

// Extract subjects
$mathSection = extract_section($md, 'MATHÉMATIQUES');
$frenchSection = extract_section($md, 'FRANÇAIS');
$englishSection = extract_section($md, 'ANGLAIS');
$correctionsSection = extract_section($md, 'Corrections');
$mathCorr = extract_section($correctionsSection, 'Mathématiques - Réponses');
$frenchCorr = extract_section($correctionsSection, 'Français - Réponses');
$englishCorr = extract_section($correctionsSection, 'Anglais - Réponses');

$mathEx = parse_exercises($mathSection);
$frenchEx = parse_exercises($frenchSection);
$englishEx = parse_exercises($englishSection);

$mathAns = parse_corrections($mathCorr);
$frenchAns = parse_corrections($frenchCorr);
$englishAns = parse_corrections($englishCorr);

$subjects = [
    'Mathématiques' => [$mathEx, $mathAns],
    'Français' => [$frenchEx, $frenchAns],
    'Anglais' => [$englishEx, $englishAns],
];

foreach ($subjects as $subject => [$items, $answers]) {
    if (empty($items)) {
        echo "[WARN] Aucun exercice détecté pour {$subject}\n";
        continue;
    }
    echo "[INFO] Import 5ème/{$subject}: " . count($items) . " exercices\n";
    $pdo->beginTransaction();
    try {
        $del = $pdo->prepare("DELETE FROM Exercises WHERE Level = ? AND Subject = ?");
        $del->execute(['5ème', $subject]);
        $ins = $pdo->prepare("INSERT INTO Exercises (Subject, Level, Title, Content, Answer) VALUES (?, ?, ?, ?, ?)");
        $inserted = 0;
        foreach ($items as $num => $ex) {
            $ans = $answers[$num] ?? '';
            $title = 'Exercice ' . $num . ' - ' . $ex['title'];
            $ins->execute([$subject, '5ème', $title, $ex['content'], $ans]);
            $inserted++;
        }
        $pdo->commit();
        echo "[OK] 5ème/{$subject}: {$inserted} insérés.\n";
    } catch (Throwable $e) {
        $pdo->rollBack();
        fwrite(STDERR, "[ERREUR] 5ème/{$subject}: " . $e->getMessage() . "\n");
    }
}

echo "[DONE] Import esxercices-5eme.md terminé.\n";
