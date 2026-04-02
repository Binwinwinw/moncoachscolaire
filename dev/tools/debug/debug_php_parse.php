<?php
$filepath = __DIR__ . '/../exercices/college/4eme exercices&correction.md';
$md = file_get_contents($filepath);

// Chercher les en-têtes ## 
if (preg_match_all('/^##\s+(.+?)$/m', $md, $m)) {
    echo "En-têtes ## trouvés:\n";
    foreach ($m[1] as $heading) {
        echo "  - '$heading'\n";
    }
}

echo "\n";

// Tester la détection MATHÉMATIQUES
$heading = 'Mathématiques';
$patterns = [
    '/##\s+' . preg_quote(strtoupper($heading), '/') . '\s*\n([\s\S]*?)(?=\n##\s+|\Z)/i',
    '/##\s+' . preg_quote($heading, '/') . '\s*\n([\s\S]*?)(?=\n##\s+|\Z)/i',
];

foreach ($patterns as $i => $pattern) {
    echo "Pattern $i: " . substr($pattern, 0, 80) . "...\n";
    if (preg_match($pattern, $md, $m)) {
        echo "  ✅ Trouvé " . strlen($m[1]) . " chars\n";
    } else {
        echo "  ❌ Non trouvé\n";
    }
}
