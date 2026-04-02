<?php
$file = $argv[1] ?? __DIR__ . '/../../../db/json/exercices/all_exercises_clean_enriched.normalized.json';
$data = json_decode(file_get_contents($file), true);
if ($data === null) { echo "parse error: " . json_last_error_msg() . "\n"; exit(1); }
$bad = [];
foreach (array_values($data) as $i => $item) {
    $idx = $i+1;
    // XP_Points should be int or null
    if (isset($item['XP_Points']) && !is_int($item['XP_Points']) && !is_null($item['XP_Points'])) {
        $bad[] = ["idx"=>$idx, "field"=>"XP_Points", "type"=>gettype($item['XP_Points']), "value"=>substr(json_encode($item['XP_Points']),0,50)];
    }
    if (isset($item['is_active']) && !is_bool($item['is_active']) && !is_null($item['is_active']) && !is_int($item['is_active'])) {
        $bad[] = ["idx"=>$idx, "field"=>"is_active", "type"=>gettype($item['is_active']), "value"=>substr(json_encode($item['is_active']),0,50)];
    }
    if (isset($item['Coherence']) && !is_bool($item['Coherence']) && !is_null($item['Coherence'])) {
        $bad[] = ["idx"=>$idx, "field"=>"Coherence", "type"=>gettype($item['Coherence']), "value"=>substr(json_encode($item['Coherence']),0,50)];
    }
    // If more than 20 found, stop
    if (count($bad) >= 20) break;
}
if (empty($bad)) { echo "No type issues detected (sample).\n"; exit(0); }
foreach ($bad as $b) echo "#{$b['idx']} field {$b['field']} type {$b['type']} value {$b['value']}\n";
