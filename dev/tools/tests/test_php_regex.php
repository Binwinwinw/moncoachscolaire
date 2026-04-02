<?php
$filepath = __DIR__ . '/../exercices/college/4eme exercices&correction.md';
$lines = file($filepath);

$count = 0;
foreach ($lines as $i => $line) {
    if (preg_match('/^####\s+Exercice\s+([0-9]+\.[0-9]+)\s+-\s+(.+)/', $line, $m)) {
        echo "Ligne " . ($i+1) . ": " . $m[1] . " => " . trim($m[2]) . "\n";
        $count++;
        if ($count >= 10) break;
    }
}

echo "\nTotal: $count found\n";
