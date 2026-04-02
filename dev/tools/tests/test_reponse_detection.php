<?php
// Test simple pour vérifier la détection de "**Réponse attendue** :"

$testLines = [
    '**Réponse attendue** :',
    '**Réponse attendue**:',
    '**Reponse attendue** :',
    '**Réponse attendue** : ',
    '  **Réponse attendue** :  ',
];

foreach ($testLines as $line) {
    $trimmed = trim($line);
    echo "Ligne: [$line]\n";
    echo "Trimmed: [$trimmed]\n";
    
    $match1 = preg_match('/^\*\*R[ée]ponse attendue\*\*\s*:\s*$/', $trimmed);
    echo "Regex 1 (avec [ée]): " . ($match1 ? 'MATCH' : 'NO MATCH') . "\n";
    
    $match2 = preg_match('/^\*\*Réponse attendue\*\*\s*:\s*$/', $trimmed);
    echo "Regex 2 (avec é): " . ($match2 ? 'MATCH' : 'NO MATCH') . "\n";
    
    $match3 = preg_match('/^\*\*R.*ponse attendue\*\*\s*:\s*$/', $trimmed);
    echo "Regex 3 (avec .*): " . ($match3 ? 'MATCH' : 'NO MATCH') . "\n";
    
    echo "\n";
}

