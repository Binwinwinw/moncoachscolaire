<?php
$filepath = __DIR__ . '/../exercices/college/4eme exercices&correction.md';
$md = file_get_contents($filepath);

// Chercher section de corrections pour Français
$pattern = '/##+\s+' . preg_quote('Français') . '\s+-\s+R[ÉE]PONSES.*?\n([\s\S]*?)(?=\n###|\Z)/i';
echo "Pattern: $pattern\n\n";

if (preg_match($pattern, $md, $m)) {
    $corrSection = $m[1];
    echo "✅ Section Français trouvée (" . strlen($corrSection) . " chars)\n\n";
    echo "Premiers 500 chars:\n" . substr($corrSection, 0, 500) . "\n\n";
    
    // Chercher exercices
    $pattern = '/^[*]*Exercice\s+([0-9]+\.[0-9]+)\s*:[*]*\s*\n([\s\S]*?)(?=^[*]*Exercice\s+[0-9]+\.[0-9]+|\Z)/m';
    if (preg_match_all($pattern, $corrSection, $matches, PREG_SET_ORDER)) {
        echo "Exercices trouvés: " . count($matches) . "\n";
        foreach ($matches as $match) {
            echo "  {$match[1]}: " . substr(trim($match[2]), 0, 50) . "...\n";
        }
    } else {
        echo "Aucun exercice trouvé\n";
    }
} else {
    echo "❌ Section Français NON trouvée\n";
}
