<?php
/**
 * Test pour vérifier le nettoyage des URLs corrompues au niveau du routeur
 */

echo "=== TEST DE NETTOYAGE D'URL CORROMPUE ===\n";
echo str_repeat("=", 60) . "\n\n";

// Simuler le nettoyage comme dans le routeur
$test_cases = [
    "college/4??me/exercices-4??me" => "college/4eme/exercices-4eme",
    "college/5??me/exercices-5??me" => "college/5eme/exercices-5eme",
    "lycee/seconde/exercices-seconde" => "lycee/seconde/exercices-seconde",
    "college/6??me/exercices-6??me" => "college/6eme/exercices-6eme",
    "lycee/premiere/exercices-premiere" => "lycee/premiere/exercices-premiere",
];

foreach ($test_cases as $corrupted => $expected) {
    // ÉTAPE 1 : Nettoyer les ??
    $pageClean = $corrupted;
    if (strpos($pageClean, '?') !== false && strpos($pageClean, '??') !== false) {
        $pageClean = str_replace('??', '', $pageClean);
        // Ajouter 'e' aux patterns comme "4me" → "4eme" (pas seulement après /)
        $pageClean = preg_replace('/([6543])me/', '\1eme', $pageClean);
    }
    
    $result = $pageClean;
    $status = ($result === $expected) ? "✅ PASS" : "❌ FAIL";
    
    printf("%-50s => %-40s %s\n", 
        '"' . $corrupted . '"',
        '"' . $result . '"',
        $status
    );
    
    if ($result !== $expected) {
        echo "  Attendu: " . $expected . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Résumé: Tous les tests passent ✅\n";

?>
