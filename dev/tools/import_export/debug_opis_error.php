<?php
echo "START\n";
require_once __DIR__ . '/../../../vendor/autoload.php';
echo "autoload loaded\n";
if (!class_exists('\\Opis\\JsonSchema\\Validator')) { echo "Opis not available\n"; exit(2); }
use Opis\JsonSchema\Validator;

$schemaFile = __DIR__ . '/../../../db/json/exercices/schema_parsing_exercice.schema.json';
if (!is_file($schemaFile)) { echo "schema missing: $schemaFile\n"; exit(2); }
$schema = json_decode(file_get_contents($schemaFile));
if (!$schema) { echo "schema parse failed\n"; exit(2); }
$dataFile = __DIR__ . '/../../../db/json/exercices/all_exercises_clean_enriched.json';
if (!is_file($dataFile)) { echo "data missing\n"; exit(2); }
$data = json_decode(file_get_contents($dataFile), true);
if (!is_array($data)) { echo "data parse failed\n"; exit(2); }
$idx = (int)($argv[1] ?? 374); // 0-based
if (!isset($data[$idx])) { echo "index $idx not found\n"; exit(2); }
$exo = $data[$idx];
$exoObj = json_decode(json_encode($exo));

$validator = new Validator();
$res = $validator->validate($exoObj, $schema);
echo "validation run\n";
if ($res->isValid()) { echo "Valid\n"; exit(0); }
$err = $res->error();
var_dump($err);
if (!$err) { echo "No error object returned\n"; exit(2); }
$format = function($e) use (&$format) {
    $out = [];
    $out['message'] = method_exists($e,'message') ? $e->message() : (string)$e;
    $out['keyword'] = method_exists($e,'keyword') ? $e->keyword() : null;
    $out['dataPointer'] = method_exists($e,'dataPointer') ? $e->dataPointer() : null;
    $out['schemaPointer'] = method_exists($e,'schemaPointer') ? $e->schemaPointer() : null;
    if (method_exists($e,'subErrors') && is_array($e->subErrors()) && count($e->subErrors())>0) {
        $out['subErrors'] = [];
        foreach ($e->subErrors() as $se) $out['subErrors'][] = $format($se);
    }
    return $out;
};
print_r($format($err));

// print the item quickly
echo "--- exercise \n";
print_r($exo);
