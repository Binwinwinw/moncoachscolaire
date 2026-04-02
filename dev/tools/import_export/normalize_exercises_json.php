<?php
// normalize_exercises_json.php
// Normalise certains champs dans les fichiers JSON d'exercices (Choices, Answer) afin de faciliter la validation

$opts = [];
foreach ($argv as $a) {
    if (strpos($a, '--') === 0) {
        $p = explode('=', $a, 2);
        $opts[substr($p[0],2)] = $p[1] ?? true;
    }
}
$file = $opts['file'] ?? __DIR__ . '/../../../db/json/exercices/all_exercises_clean_enriched.json';
$dry = isset($opts['dry-run']) || isset($opts['dry']);
$inplace = isset($opts['inplace']) || isset($opts['i']);

if (!is_file($file)) {
    fwrite(STDERR, "Fichier introuvable: $file\n");
    exit(2);
}

$data = json_decode(file_get_contents($file), true);
if ($data === null) {
    fwrite(STDERR, "Impossible de parser JSON: $file\n");
    exit(2);
}

$changed = 0;
$fixes = [];
$processChoices = function($val) {
    if ($val === null) return null;
    if (is_array($val)) return array_values($val);
    if (!is_string($val)) return $val; // unexpected type: leave as-is
    $s = trim($val);
    if ($s === '') return null; // empty string -> null
    // common delimiters: newline, '|', '||', ';'
    if (strpos($s, "\n") !== false) {
        $parts = array_filter(array_map('trim', explode("\n", $s)), function($v){return $v !== '';});
        return array_values($parts);
    }
    if (strpos($s, '||') !== false) {
        $parts = array_filter(array_map('trim', explode('||', $s)), function($v){return $v !== '';});
        return array_values($parts);
    }
    if (strpos($s, '|') !== false) {
        // split but avoid breaking things that look like "A) 2 | B) 3" ; still OK
        $parts = array_filter(array_map('trim', explode('|', $s)), function($v){return $v !== '';});
        return array_values($parts);
    }
    if (strpos($s, ';') !== false && substr_count($s, ';') > 0) {
        $parts = array_filter(array_map('trim', explode(';', $s)), function($v){return $v !== '';});
        return array_values($parts);
    }
    // if none of delimiters, return as single-element array
    return [$s];
};

$items = [];
if (isset($data['exercises']) && is_array($data['exercises'])) {
    $items = &$data['exercises'];
} elseif (isset($data[0]) && is_array($data)) {
    $items = &$data;
} else {
    fwrite(STDERR, "Format inattendu: attente d'un tableau d'exercices ou d'un objet {exercises: [...]}\n");
    exit(2);
}

foreach ($items as $i => &$exo) {
    $orig = $exo;
    // Normalize Choices
    if (array_key_exists('Choices', $exo)) {
        $normalized = $processChoices($exo['Choices']);
        // if normalized is array of strings, ensure values are strings
        if (is_array($normalized)) {
            $normalized = array_map(function($v){ return is_string($v) ? $v : trim((string)$v); }, $normalized);
        }
        if ($normalized !== $exo['Choices']) {
            $exo['Choices'] = $normalized;
            $changed++;
            $fixes[] = [ 'index' => $i+1, 'field' => 'Choices', 'from' => $orig['Choices'], 'to' => $normalized ];
        }
    } else {
        // ensure presence? not necessary
    }
    // Normalize Answer if it's an empty string -> null
    if (array_key_exists('Answer', $exo)) {
        if (is_string($exo['Answer']) && trim($exo['Answer']) === '') {
            $exo['Answer'] = null;
            $changed++;
            $fixes[] = [ 'index' => $i+1, 'field' => 'Answer', 'from' => $orig['Answer'], 'to' => null ];
        }
        // If Answer is numeric string, leave as string; if it's array-like numeric, keep as-is (schema allows string/array/null)
    }

    // Coerce common boolean/integer strings to proper types
    if (array_key_exists('is_active', $exo)) {
        if (is_string($exo['is_active'])) {
            $vRaw = $exo['is_active'];
            $v = strtolower(trim($vRaw));
            if ($v === 'true' || $v === '1') {
                $exo['is_active'] = true; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'is_active', 'from' => $orig['is_active'], 'to' => true];
            } elseif ($v === 'false' || $v === '0') {
                $exo['is_active'] = false; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'is_active', 'from' => $orig['is_active'], 'to' => false];
            } elseif ($v === '') {
                // empty string => null
                $exo['is_active'] = null; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'is_active', 'from' => $orig['is_active'], 'to' => null];
            }
        }
    }
    if (array_key_exists('XP_Points', $exo)) {
        if (is_string($exo['XP_Points'])) {
            $s = trim($exo['XP_Points']);
            if ($s === '') {
                $exo['XP_Points'] = null; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'XP_Points', 'from' => $orig['XP_Points'], 'to' => null];
            } elseif (is_numeric($s)) {
                $exo['XP_Points'] = (int)$s; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'XP_Points', 'from' => $orig['XP_Points'], 'to' => $exo['XP_Points']];
            }
        }
    }
    if (array_key_exists('Coherence', $exo)) {
        if (is_string($exo['Coherence'])) {
            $v = strtolower(trim($exo['Coherence']));
            if ($v === 'true' || $v === '1') {
                $exo['Coherence'] = true; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'Coherence', 'from' => $orig['Coherence'], 'to' => true];
            } elseif ($v === 'false' || $v === '0') {
                $exo['Coherence'] = false; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'Coherence', 'from' => $orig['Coherence'], 'to' => false];
            } elseif ($v === '') {
                $exo['Coherence'] = null; $changed++; $fixes[] = ['index' => $i+1, 'field' => 'Coherence', 'from' => $orig['Coherence'], 'to' => null];
            }
        }
    }
}

if ($dry) {
    echo "DRY-RUN: changements detectes: $changed\n";
    foreach ($fixes as $f) {
        echo " - Exo #{$f['index']}: {$f['field']} => ";
        echo json_encode($f['to'], JSON_UNESCAPED_UNICODE) . " (was: ";
        echo json_encode($f['from'], JSON_UNESCAPED_UNICODE) . ")\n";
    }
    exit(0);
}

if ($changed === 0) {
    echo "Aucune modification necessaire.\n";
    exit(0);
}

if ($inplace) {
    // backup original
    copy($file, $file . '.bak.' . date('YmdHis'));
    file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    echo "Fichier normalisé et ecrit (inplace): $file (modifications: $changed)\n";
    exit(0);
} else {
    $out = dirname($file) . '/' . basename($file, '.json') . '.normalized.json';
    file_put_contents($out, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    echo "Fichier normalisé écrit: $out (modifications: $changed)\n";
    exit(0);
}
