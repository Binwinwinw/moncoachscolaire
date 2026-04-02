<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Smalot\PdfParser\Parser;

$base = dirname(__DIR__);
$files = [
    $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'Exercices 6ème Collège.pdf',
    $base . DIRECTORY_SEPARATOR . 'exercices' . DIRECTORY_SEPARATOR . 'Corrigés Complets 6ème.pdf',
];

$parser = new Parser();
foreach ($files as $f) {
    echo "\n=== $f ===\n";
    if (!file_exists($f)) { echo "Missing\n"; continue; }
    try {
        $pdf = $parser->parseFile($f);
        $text = $pdf->getText();
        echo "Length: " . strlen($text) . "\n";
        echo substr($text, 0, 1000) . "\n"; // preview
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
