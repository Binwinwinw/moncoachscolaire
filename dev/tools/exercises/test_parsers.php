<?php
spl_autoload_register(function($class) {
    $base = __DIR__ . '/Parsers/';
    $classPath = $base . str_replace(['MonCoachScolaire\\Parsers\\', '\\'], ['', '/'], $class) . '.php';
    if (file_exists($classPath)) {
        require_once $classPath;
    }
});

use MonCoachScolaire\Parsers\ParserRegistry;
use MonCoachScolaire\Parsers\MathParser;
use MonCoachScolaire\Parsers\FrancaisParser;
use MonCoachScolaire\Parsers\GenericParser;

$registry = new ParserRegistry(new GenericParser());
$registry->register('mathématiques_probleme', new MathParser());
$registry->register('français_accord', new FrancaisParser());

$raw_math = json_encode([
    'Subject' => 'Mathématiques',
    'Content' => 'Calculez ∫x dx. a) x²/2 b) x²',
    'Type' => 'probleme'
]);
$parsed_math = $registry->getParser('Mathématiques', 'probleme')->parse($raw_math);
echo "Math: " . json_encode($parsed_math, JSON_PRETTY_PRINT) . "\n\n";

$raw_fr = json_encode([
    'Subject' => 'Français',
    'Content' => 'Accordez : a) fleurs (blanc)',
    'Type' => 'accord'
]);
$parsed_fr = $registry->getParser('Français', 'accord')->parse($raw_fr);
echo "Français: " . json_encode($parsed_fr, JSON_PRETTY_PRINT) . "\n";
