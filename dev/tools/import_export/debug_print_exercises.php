<?php
// debug_print_exercises.php
// Usage: php debug_print_exercises.php [jsonFile] [start] [len]
$defaultJson = __DIR__ . '/../../../db/json/exercices/all_exercises_clean_enriched.json';
$jsonFile = $defaultJson;
$start = 370;
$len = 10;

$args = array_slice($argv, 1);
if (count($args) === 1) {
    if (is_numeric($args[0])) {
        $start = (int)$args[0];
    } elseif (file_exists($args[0])) {
        $jsonFile = $args[0];
    } else {
        $p = __DIR__ . '/../../../' . ltrim($args[0], '/\\');
        if (file_exists($p)) {
            $jsonFile = $p;
        } else {
            echo "Argument inconnu: {$args[0]}\n";
            exit(1);
        }
    }
} elseif (count($args) >= 2) {
    // first arg is likely a filename
    $p = file_exists($args[0]) ? $args[0] : (__DIR__ . '/../../../' . ltrim($args[0], '/\\'));
    if (file_exists($p)) {
        $jsonFile = $p;
        $start = is_numeric($args[1]) ? (int)$args[1] : 0;
        $len = isset($args[2]) && is_numeric($args[2]) ? (int)$args[2] : 1;
    } else {
        $start = is_numeric($args[0]) ? (int)$args[0] : $start;
        $len = isset($args[1]) && is_numeric($args[1]) ? (int)$args[1] : $len;
    }
}

if (!file_exists($jsonFile)) {
    echo "Fichier JSON introuvable: $jsonFile\n";
    exit(1);
}

$data = json_decode(file_get_contents($jsonFile), true);
if (!is_array($data)) { echo "Not array or failed to parse JSON: $jsonFile\n"; echo json_last_error_msg() . "\n"; exit(1);}
$slice = array_slice($data, (int)$start, (int)$len);
foreach ($slice as $i => $item) {
    $idx = (int)$start + $i + 1;
    echo "--- Exercise #$idx ---\n";
    // print keys and types
    foreach ($item as $k => $v) {
        $type = gettype($v);
        echo "  $k ($type)\n";
    }
    echo "raw: ";
    echo json_encode($item, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n\n";
}
