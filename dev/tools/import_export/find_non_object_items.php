<?php
$file = $argv[1] ?? __DIR__ . '/../../../db/json/exercices/all_exercises_clean_enriched.normalized.json';
if (!file_exists($file)) { echo "File not found: $file\n"; exit(1); }
$data = json_decode(file_get_contents($file), true);
if ($data === null) { echo "JSON parse error: " . json_last_error_msg() . "\n"; exit(1); }
$bad = [];
foreach (array_values($data) as $i => $item) {
    if (!is_array($item)) {
        $bad[] = $i + 1;
        if (count($bad) >= 20) break;
    }
}
if (empty($bad)) { echo "No non-object items found.\n"; exit(0); }
echo "Found non-object items at indices: " . implode(', ', $bad) . "\n";
