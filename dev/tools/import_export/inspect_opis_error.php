<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$schemaFile = __DIR__ . '/../../../db/json/exercices/schema_parsing_exercice.schema.json';
if (!is_file($schemaFile)) { echo "Schema missing: $schemaFile\n"; exit(2); }
$schema = json_decode(file_get_contents($schemaFile));
if (!$schema) { echo "Invalid schema JSON\n"; exit(2); }

$fn = $argv[1] ?? __DIR__ . '/../../../db/json/exercices/all_exercises_clean_enriched.normalized.json';
$idx = isset($argv[2]) ? (int)$argv[2] : 385;
if (!file_exists($fn)) { echo "File not found: $fn\n"; exit(1); }
$data = json_decode(file_get_contents($fn), true);
if ($data === null) { echo "Parse error: " . json_last_error_msg() . "\n"; exit(1); }

// Ensure it's array or has 'exercises' key
if (isset($data['exercises']) && is_array($data['exercises'])) {
    $candidates = $data['exercises'];
} elseif (isset($data[0]) && is_array($data[0])) {
    $candidates = $data;
} else {
    echo "File has unexpected format\n"; exit(1);
}
if (!isset($candidates[$idx-1])) { echo "Index not found: $idx\n"; exit(1); }
$exo = $candidates[$idx-1];

$validator = new \Opis\JsonSchema\Validator();
$exoObj = json_decode(json_encode($exo));
$res = $validator->validate($exoObj, $schema);

if ($res->isValid()) {
    echo "Valid\n"; exit(0);
}
$err = $res->error();

$formatOpisError = function($e) use (&$formatOpisError) {
    $out = [];
    $out['message'] = method_exists($e, 'message') ? $e->message() : (string)$e;
    $out['keyword'] = method_exists($e, 'keyword') ? $e->keyword() : null;
    $out['dataPointer'] = method_exists($e, 'dataPointer') ? $e->dataPointer() : null;
    $out['schemaPointer'] = method_exists($e, 'schemaPointer') ? $e->schemaPointer() : null;
    if (method_exists($e, 'subErrors') && is_array($e->subErrors()) && count($e->subErrors()) > 0) {
        $out['subErrors'] = [];
        foreach ($e->subErrors() as $se) {
            $out['subErrors'][] = $formatOpisError($se);
        }
    }
    return $out;
};
$details = $formatOpisError($err);

echo "--- ITEM ---\n";
echo json_encode($exo, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";

echo "--- ERROR ---\n";
echo json_encode($details, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";

exit(1);
