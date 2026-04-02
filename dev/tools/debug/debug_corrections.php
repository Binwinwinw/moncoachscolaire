<?php
$filepath = __DIR__ . '/../exercices/college/4eme exercices&correction.md';
$md = file_get_contents($filepath);

// Parse corrections section
function extract_section_debug($md, $heading) {
    $lines = explode("\n", $md);
    $start = -1;
    $end = count($lines);
    
    $headingNoAccent = strtoupper(str_replace(['é', 'ç'], ['e', 'c'], $heading));
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        if (preg_match('/^##\s+(.+)$/', $line, $m)) {
            $lineHeading = strtoupper(str_replace(['é', 'ç'], ['e', 'c'], $m[1]));
            if ($lineHeading === $headingNoAccent) {
                $start = $i + 1;
                echo "Found '$heading' at line $i\n";
            } elseif ($start !== -1) {
                $end = $i;
                break;
            }
        }
    }
    
    if ($start === -1) {
        return '';
    }
    
    return trim(implode("\n", array_slice($lines, $start, $end - $start)));
}

// Get Corrections section
$allCorrections = extract_section_debug($md, 'Corrections');
echo "Got " . strlen($allCorrections) . " chars from Corrections\n\n";

// Extract Français section from corrections
$frSection = extract_section_debug($allCorrections, 'Français - Réponses');
echo "Got " . strlen($frSection) . " chars from Français - Réponses\n";
echo "First 500 chars:\n" . substr($frSection, 0, 500) . "\n\n";

// Now parse corrections using regex
$pattern = '/\*?\*?Exercice\s+([0-9]+\.[0-9]+)\s*:\*?\*?\s*\n?([\s\S]*?)(?=\n\*?\*?Exercice\s+[0-9]+\.[0-9]+|\n---|\n###|\Z)/i';
if (preg_match_all($pattern, $frSection, $m, PREG_SET_ORDER)) {
    echo "Found " . count($m) . " exercises\n";
    foreach ($m as $match) {
        echo "  - " . $match[1] . ": " . substr($match[2], 0, 50) . "...\n";
    }
} else {
    echo "No matches found!\n";
}
