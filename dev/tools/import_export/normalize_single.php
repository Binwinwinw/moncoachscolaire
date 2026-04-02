<?php
// normalize_single.php - normalise un seul fichier d'exercices et écrit un .normalized.json à côté
if ($argc < 2) {
    fwrite(STDERR, "Usage: php normalize_single.php <file> [--dry-run]\n");
    exit(2);
}
$file = $argv[1];
$dry = in_array('--dry-run', $argv) || in_array('--dry', $argv);
if (!is_file($file)) { fwrite(STDERR, "Fichier introuvable: $file\n"); exit(2); }
$content = file_get_contents($file);
$data = json_decode($content, true);
if ($data === null && json_last_error() !== JSON_ERROR_NONE) { fwrite(STDERR, "Impossible de parser JSON: $file - " . json_last_error_msg() . "\n"); exit(2); }
$processChoices = function($val) {
    if ($val === null) return null;
    if (is_array($val)) return array_values($val);
    if (!is_string($val)) return $val;
    $s = trim($val);
    if ($s === '') return null;
    if (strpos($s, "\n") !== false) {
        $parts = array_filter(array_map('trim', explode("\n", $s)), function($v){ return $v !== ''; });
        return array_values($parts);
    }
    if (strpos($s, '||') !== false) {
        $parts = array_filter(array_map('trim', explode('||', $s)), function($v){ return $v !== ''; });
        return array_values($parts);
    }
    if (strpos($s, '|') !== false) {
        $parts = array_filter(array_map('trim', explode('|', $s)), function($v){ return $v !== ''; });
        return array_values($parts);
    }
    if (strpos($s, ';') !== false && substr_count($s, ';') > 0) {
        $parts = array_filter(array_map('trim', explode(';', $s)), function($v){ return $v !== ''; });
        return array_values($parts);
    }
    return [$s];
};
$items = [];
if (isset($data['exercises']) && is_array($data['exercises'])) {
    $items = &$data['exercises'];
} elseif (isset($data[0]) && is_array($data)) {
    $items = &$data;
} else {
    fwrite(STDERR, "Format inattendu: attente d'un tableau d'exercices ou d'un objet {exercises: [...]}\n"); exit(2);
}
$changed = 0; $fixes = [];
foreach ($items as $i => &$exo) {
    $orig = $exo;
    if (array_key_exists('Choices', $exo)) {
        $normalized = $processChoices($exo['Choices']);
        if (is_array($normalized)) {
            $normalized = array_map(function($v){ return is_string($v) ? $v : trim((string)$v); }, $normalized);
        }
        if ($normalized !== $exo['Choices']) {
            $exo['Choices'] = $normalized;
            $changed++; $fixes[] = ['index' => $i+1, 'field' => 'Choices', 'from' => $orig['Choices'], 'to' => $normalized];
        }
    }
    if (array_key_exists('Answer', $exo)) {
        if (is_string($exo['Answer']) && trim($exo['Answer']) === '') {
            $exo['Answer'] = null; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'Answer', 'from' => $orig['Answer'], 'to' => null];
        }
    }
    if (array_key_exists('is_active', $exo) && is_string($exo['is_active'])) {
        $v = strtolower(trim($exo['is_active']));
        if ($v === 'true' || $v === '1') { $exo['is_active'] = true; $changed++; $fixes[] = ['index'=>$i+1,'field'=>'is_active','from'=>$orig['is_active'],'to'=>true]; }
        elseif ($v === 'false' || $v === '0') { $exo['is_active'] = false; $changed++; $fixes[] = ['index'=>$i+1,'field'=>'is_active','from'=>$orig['is_active'],'to'=>false]; }
    }
    if (array_key_exists('XP_Points', $exo) && is_string($exo['XP_Points']) && is_numeric($exo['XP_Points'])) {
        $exo['XP_Points'] = (int)$exo['XP_Points']; $changed++; $fixes[] = ['index'=>$i+1,'field'=>'XP_Points','from'=>$orig['XP_Points'],'to'=>$exo['XP_Points']];
    }
    if (array_key_exists('Coherence', $exo) && is_string($exo['Coherence'])) {
        $v = strtolower(trim($exo['Coherence']));
        if ($v === 'true' || $v === '1') { $exo['Coherence'] = true; $changed++; $fixes[] = ['index'=>$i+1,'field'=>'Coherence','from'=>$orig['Coherence'],'to'=>true]; }
        elseif ($v === 'false' || $v === '0') { $exo['Coherence'] = false; $changed++; $fixes[] = ['index'=>$i+1,'field'=>'Coherence','from'=>$orig['Coherence'],'to'=>false]; }
    }
}
if ($dry) {
    echo "DRY-RUN: changements detectes: $changed\n";
    foreach ($fixes as $f) echo " - Exo #{$f['index']}: {$f['field']} => " . json_encode($f['to'], JSON_UNESCAPED_UNICODE) . " (was: " . json_encode($f['from'], JSON_UNESCAPED_UNICODE) . ")\n";
    exit(0);
}
$out = dirname($file) . '/' . basename($file, '.json') . '.normalized.json';
file_put_contents($out, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
echo "Fichier normalisé écrit: $out (modifications: $changed)\n";
exit(0);
