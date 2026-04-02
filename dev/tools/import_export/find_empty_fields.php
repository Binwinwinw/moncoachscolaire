<?php
$file = $argv[1] ?? __DIR__ . '/../../../db/json/exercices/all_exercises_clean_enriched.normalized.json';
$data = json_decode(file_get_contents($file), true);
if ($data === null) { echo "parse error: " . json_last_error_msg() . "\n"; exit(1); }
$found = [];
foreach (array_values($data) as $i => $item) {
    $idx = $i+1;
    if (array_key_exists('Answer', $item) && $item['Answer'] === '') {
        $found[] = "#{$idx} Answer is empty string";
    }
    if (array_key_exists('Choices', $item) && $item['Choices'] === '') {
        $found[] = "#{$idx} Choices is empty string";
    }
    if (count($found) >= 20) break;
}
if (empty($found)) { echo "No empty Answer/Choices found.\n"; exit(0); }
echo implode("\n", $found) . "\n";
