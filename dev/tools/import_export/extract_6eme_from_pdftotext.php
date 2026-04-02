<?php
/**
 * Extraction 6ème via pdftotext (Poppler/Xpdf)
 * - Convertit les PDF en .txt avec mise en page conservée (-layout)
 * - Apparimente les exercices et corrections par numéro (Exercice X.Y)
 * - Produit deux JSON: maths et français
 */

function findPdftotext(): ?string {
    $paths = [];
    @exec('where pdftotext', $paths, $code);
    if ($code === 0 && !empty($paths)) {
        return $paths[0];
    }
    return null;
}

function runPdftotext(string $pdf, string $txt): bool {
    $bin = findPdftotext();
    if (!$bin) {
        fwrite(STDERR, "[ERREUR] pdftotext introuvable. Installez Poppler ou Xpdf tools et assurez-vous que 'pdftotext' est dans le PATH.\n");
        return false;
    }
    $cmd = '"' . $bin . '" -layout -enc UTF-8 "' . $pdf . '" "' . $txt . '"';
    exec($cmd, $out, $code);
    if ($code !== 0) {
        fwrite(STDERR, "[ERREUR] pdftotext a échoué (code {$code}).\n");
        return false;
    }
    return file_exists($txt) && filesize($txt) > 0;
}

function loadText(string $path): string {
    return file_exists($path) ? file_get_contents($path) : '';
}

function parseBlocks(string $text): array {
    $t = preg_replace('/\r\n|\r/', "\n", $text);
    $pattern = '/(^|\n)\s*(Exercice\s*([0-9]+(?:\.[0-9]+)?))([^\n]*)([\s\S]*?)(?=(\n\s*Exercice\s*[0-9]+(?:\.[0-9]+)?|$))/mi';
    $blocks = [];
    if (preg_match_all($pattern, $t, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $num = trim($match[2]);
            $titleTail = trim($match[4]);
            $content = trim($match[5]);
            $title = $num . (strlen($titleTail) ? ' - ' . $titleTail : '');
            $blocks[] = [
                'number' => $num,
                'title' => $title,
                'content' => $content,
            ];
        }
    }
    return $blocks;
}

function detectSubject(string $content): string {
    $c = mb_strtolower($content);
    $math = ['fraction','proportion','nombre','relatif','angle','périmètre','perimetre','aire','multiplication','division','équation','equation','pourcentage','géométrie','geometrie','tableau','graphique'];
    $fr = ['grammaire','conjugaison','orthographe','nature des mots','accord','phrase','verbe','adjectif','nom','complément','synonyme','antonyme'];
    foreach ($math as $kw) if (str_contains($c, $kw)) return 'Mathématiques';
    foreach ($fr as $kw) if (str_contains($c, $kw)) return 'Français';
    $digits = preg_match_all('/\d/', $c);
    if ($digits >= 10) return 'Mathématiques';
    return 'Mathématiques';
}

function buildJson(array $exBlocks, array $corrBlocks): array {
    $corrByNum = [];
    foreach ($corrBlocks as $c) { $corrByNum[$c['number']] = $c; }

    $math = ['level'=>'6ème','subject'=>'Mathématiques','replace'=>true,'source_pdf'=>'exercices/Exercices 6ème Collège.pdf','corrections_pdf'=>'exercices/Corrigés Complets 6ème.pdf','exercises'=>[]];
    $fr = ['level'=>'6ème','subject'=>'Français','replace'=>true,'source_pdf'=>'exercices/Exercices 6ème Collège.pdf','corrections_pdf'=>'exercices/Corrigés Complets 6ème.pdf','exercises'=>[]];

    foreach ($exBlocks as $ex) {
        $num = $ex['number'];
        $corr = $corrByNum[$num] ?? null;
        $subject = detectSubject($ex['content'] . ' ' . $ex['title']);
        $item = [
            'title' => $ex['title'],
            'content' => $ex['content'],
            'answer' => $corr ? $corr['content'] : '',
            'tags' => [],
            'difficulty' => 1,
        ];
        if ($subject === 'Français') $fr['exercises'][] = $item; else $math['exercises'][] = $item;
    }
    return [$math, $fr];
}

$base = dirname(__DIR__);
$exPdf = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'Exercices 6ème Collège.pdf';
$corrPdf = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'Corrigés Complets 6ème.pdf';
$outDir = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . '_parsed';
if (!is_dir($outDir)) { @mkdir($outDir, 0777, true); }

$exTxt = $outDir . DIRECTORY_SEPARATOR . 'exercices-6eme.txt';
$corrTxt = $outDir . DIRECTORY_SEPARATOR . 'corriges-6eme.txt';

if (!runPdftotext($exPdf, $exTxt) || !runPdftotext($corrPdf, $corrTxt)) {
    exit(2);
}

$exText = loadText($exTxt);
$corrText = loadText($corrTxt);
$exBlocks = parseBlocks($exText);
$corrBlocks = parseBlocks($corrText);
list($mathJson, $frJson) = buildJson($exBlocks, $corrBlocks);

$mathOut = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'college-6eme-math-auto.json';
$frOut = $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'college-6eme-fr-auto.json';
file_put_contents($mathOut, json_encode($mathJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
file_put_contents($frOut, json_encode($frJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "[OK] pdftotext extraction terminée.\n";
echo "- Ex: {$exTxt}\n- Corr: {$corrTxt}\n";
echo "- JSON maths: {$mathOut} (" . count($mathJson['exercises']) . ")\n- JSON français: {$frOut} (" . count($frJson['exercises']) . ")\n";
