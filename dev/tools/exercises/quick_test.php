<?php
// Test manuel sans autoload PSR-4
require_once __DIR__ . '/Parsers/ExerciceParserInterface.php';
require_once __DIR__ . '/Parsers/ParsedExercise.php';
require_once __DIR__ . '/Parsers/ParserRegistry.php';
require_once __DIR__ . '/Parsers/MathParser.php';
require_once __DIR__ . '/Parsers/FrancaisParser.php';
require_once __DIR__ . '/Parsers/GenericParser.php';

use MonCoachScolaire\Parsers\ParserRegistry;
use MonCoachScolaire\Parsers\MathParser;
use MonCoachScolaire\Parsers\FrancaisParser;
use MonCoachScolaire\Parsers\GenericParser;

$registry = new ParserRegistry(new GenericParser());
$registry->register('mathématiques_probleme', new MathParser());
$registry->register('français_accord', new FrancaisParser());

$mathParser = $registry->getParser('Mathématiques', 'probleme');
$frParser = $registry->getParser('Français', 'accord');

echo "Math OK ? ";
var_dump($mathParser);
echo "\nFrançais OK ? ";
var_dump($frParser);
