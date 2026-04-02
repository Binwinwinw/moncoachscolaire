<?php
/**
 * Extraction automatique des exercices et corrigés 6ème depuis deux PDF
 * Entrées attendues (chemins par défaut):
 *   - exercices/Exercices 6ème Collège.pdf
 *   - exercices/Corrigés Complets 6ème.pdf
 * Sorties:
 *   - exercices/college-6eme-math-auto.json
 *   - exercices/college-6eme-fr-auto.json
 *
 * Heuristiques:
 *   - Découpe par blocs commençant par "Exercice" (numérotés).
 *   - Associe par ordre les corrections.
 *   - Détecte la matière via mots-clés (maths/grammaire), sinon fallback Mathématiques.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

function readPdfText($path) {
    if (!file_exists($path)) {
        fwrite(STDERR, "[ERREUR] PDF introuvable: {$path}\n");
        return null;
    }
    try {
        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();
        return $text;
    } catch (Throwable $e) {
        fwrite(STDERR, "[ERREUR] Extraction PDF: " . $e->getMessage() . "\n");
        return null;
    }
}

function splitExercises($text) {
    // Normaliser les fins de ligne
    $t = preg_replace('/\r\n|\r/', "\n", $text);
    $blocks = [];
    // Heuristique principale: découper par "Consigne :" via split
    $parts = preg_split('/Consigne\s*:/i', $t);
    if ($parts && count($parts) > 1) {
        // Ignorer l'entête avant la première consigne
        $i = 1;
        for ($p = 1; $p < count($parts); $p++) {
            $content = trim($parts[$p]);
            if ($content === '') continue;
            // Déterminer un titre simple à partir de la première ligne
            $lines = explode("\n", $content);
            $firstLine = trim($lines[0] ?? '');
            $titleHint = '';
            if (stripos($firstLine, 'calcul') !== false) $titleHint = 'Calculs';
            elseif (stripos($firstLine, 'fraction') !== false) $titleHint = 'Fractions';
            elseif (stripos($firstLine, 'périmètre') !== false || stripos($firstLine, 'perimetre') !== false) $titleHint = 'Périmètre';
            elseif (stripos($firstLine, 'problème') !== false || stripos($firstLine, 'probleme') !== false) $titleHint = 'Problème';
            else $titleHint = $firstLine ?: ('Consigne ' . $i);
            $blocks[] = [
                'title' => 'Consigne ' . $i . ' — ' . $titleHint,
                'content' => $content,
            ];
            $i++;
        }
    } else {
        // Fallback: tenter par "Exercice X.Y"
        $patternEx = '/(^|\n)\s*(Exercice\s*[0-9]+(?:\.[0-9]+)?[^\n]*)([\s\S]*?)(?=(\n\s*Exercice\s*[0-9]+(?:\.[0-9]+)?|$))/mi';
        if (preg_match_all($patternEx, $t, $mx, PREG_SET_ORDER)) {
            foreach ($mx as $m1) {
                $blocks[] = [
                    'title' => trim($m1[2]),
                    'content' => trim($m1[3]),
                ];
            }
        } else {
            // Dernier recours: un seul bloc
            $blocks[] = [
                'title' => 'Exercices 6ème',
                'content' => trim($t),
            ];
        }
    }
    return $blocks;
}

function splitCorrections($text) {
    $t = preg_replace('/\r\n|\r/', "\n", $text);
    $blocks = [];
    // Regrouper par "Correction :" ou "Corrections détaillées :"
    $pattern = '/(^|\n)\s*(Corrections\s+d[ée]taill[ée]es\s*:|Correction\s*:)([\s\S]*?)(?=(\n\s*(Corrections\s+d[ée]taill[ée]es\s*:|Correction\s*:)|$))/mi';
    if (preg_match_all($pattern, $t, $m, PREG_SET_ORDER)) {
        $i = 1;
        foreach ($m as $match) {
            $content = trim($match[3]);
            $title = 'Correction ' . $i;
            // Essayez d'extraire un numéro Exercice X.Y présent dans le bloc
            if (preg_match('/Exercice\s*([0-9]+(?:\.[0-9]+)?)/i', $content, $nm)) {
                $title .= ' — Exercice ' . $nm[1];
            }
            $blocks[] = [
                'title' => $title,
                'content' => $content,
            ];
            $i++;
        }
    } else {
        // Fallback: un seul bloc
        $blocks[] = [
            'title' => 'Corrections 6ème',
            'content' => trim($t),
        ];
    }
    return $blocks;
}

function detectSubject($content) {
    $c = mb_strtolower($content);
    $mathKeywords = ['fraction', 'proportion', 'nombre', 'relatif', 'angle', 'périmètre', 'perimetre', 'aire', 'multiplication', 'division', 'équation', 'equation', 'pourcentage', 'géométrie', 'geometrie'];
    $frKeywords = ['grammaire', 'conjugaison', 'orthographe', 'nature des mots', 'accord', 'phrase', 'verbe', 'adjectif', 'nom'];

    foreach ($mathKeywords as $kw) {
        if (str_contains($c, $kw)) return 'Mathématiques';
    }
    foreach ($frKeywords as $kw) {
        if (str_contains($c, $kw)) return 'Français';
    }
    // Heuristique: beaucoup de chiffres → probabilité maths
    $digits = preg_match_all('/\d/', $c);
    if ($digits >= 10) return 'Mathématiques';
    return 'Mathématiques';
}

function buildJsonDatasets($exercises, $corrections, $level = '6ème') {
    $math = [
        'level' => $level,
        'subject' => 'Mathématiques',
        'replace' => true,
        'source_pdf' => 'exercices/Exercices 6ème Collège.pdf',
        'corrections_pdf' => 'exercices/Corrigés Complets 6ème.pdf',
        'exercises' => []
    ];
    $fr = [
        'level' => $level,
        'subject' => 'Français',
        'replace' => true,
        'source_pdf' => 'exercices/Exercices 6ème Collège.pdf',
        'corrections_pdf' => 'exercices/Corrigés Complets 6ème.pdf',
        'exercises' => []
    ];

    $count = min(count($exercises), count($corrections));
    if ($count === 0) {
        fwrite(STDERR, "[WARN] Aucun appariement exercice/correction détecté.\n");
    }

    for ($i = 0; $i < $count; $i++) {
        $ex = $exercises[$i];
        $corr = $corrections[$i];
        $subject = detectSubject($ex['content']);

        $item = [
            'title' => $ex['title'],
            'content' => $ex['content'],
            'answer' => $corr['content'] ?? '',
            'tags' => [],
            'difficulty' => 1
        ];

        if ($subject === 'Français') {
            $fr['exercises'][] = $item;
        } else {
            $math['exercises'][] = $item;
        }
    }

    return [$math, $fr];
}

// Entrées
$base = dirname(__DIR__);
$exPdf = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'Exercices 6ème Collège.pdf';
$corrPdf = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'Corrigés Complets 6ème.pdf';

$exText = readPdfText($exPdf);
$corrText = readPdfText($corrPdf);

if ($exText === null || $corrText === null) {
    exit(2);
}

$exBlocks = splitExercises($exText);
$corrBlocks = splitCorrections($corrText);

list($mathJson, $frJson) = buildJsonDatasets($exBlocks, $corrBlocks, '6ème');

$mathOut = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'college-6eme-math-auto.json';
$frOut = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'college-6eme-fr-auto.json';

file_put_contents($mathOut, json_encode($mathJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents($frOut, json_encode($frJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "[OK] Généré: \n - {$mathOut} (" . count($mathJson['exercises']) . " exercices)\n - {$frOut} (" . count($frJson['exercises']) . " exercices)\n";

exit(0);
