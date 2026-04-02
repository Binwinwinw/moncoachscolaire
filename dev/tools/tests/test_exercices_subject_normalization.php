<?php
// Test normalization: ensure malformed variants (e.g. Math'ematiques) are canonicalized to Mathématiques

function subject_key($s) {
    $s = trim((string)$s);
    if ($s === '') return '';
    $k = mb_strtolower($s, 'UTF-8');
    $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $k);
    if ($trans !== false) $k = $trans;
    $k = preg_replace('/[^a-z0-9]+/', '', $k);
    return $k;
}

function prefer_subject($new, $old) {
    if ($new === $old) return false;
    $new_mb = preg_match('/[^\x00-\x7F]/', $new);
    $old_mb = preg_match('/[^\x00-\x7F]/', $old);
    if ($new_mb && !$old_mb) return true;
    if (strpos($old, "'") !== false && strpos($new, "'") === false) return true;
    if (mb_strlen($new, 'UTF-8') > mb_strlen($old, 'UTF-8')) return true;
    return false;
}

$sample = [
    "Math'ematiques",
    'Mathematiques',
    'Mathématiques',
    "mathématiques",
    "Français",
    "Francais"
];

$canonical = [];
foreach ($sample as $sub) {
    $k = subject_key($sub);
    if ($k === '') continue;
    if (!isset($canonical[$k]) || prefer_subject($sub, $canonical[$k])) {
        $canonical[$k] = $sub;
    }
}

// Expectations: math* key should map to 'Mathématiques' (prefer accented form)
$k = subject_key('Mathématiques');
if (!isset($canonical[$k])) {
    echo "FAIL: canonical key for math not found\n";
    exit(1);
}
$chosen = $canonical[$k];
if (mb_strtolower($chosen, 'UTF-8') !== mb_strtolower('Mathématiques', 'UTF-8')) {
    echo "FAIL: unexpected canonical subject for math: $chosen\n";
    exit(1);
}

// Also ensure "Math'ematiques" does not appear as a canonical label
if (in_array("Math'emat iques", $canonical, true)) {
    // weird transformation, but check direct presence
}
if (in_array("Math'ematiques", $canonical, true)) {
    echo "FAIL: malformed variant Math'ema tiques present in canonical list\n";
    exit(1);
}

echo "PASS: normalization picks canonical accented label and excludes malformed variant\n";
exit(0);
