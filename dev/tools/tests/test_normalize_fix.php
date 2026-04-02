<?php
/**
 * Test de la fonction normalize_level_for_url() avec des données corrompues
 */

// Charger la fonction depuis site_boot.php
require_once __DIR__ . '/src/config/site_boot.php';

echo "=== TEST DE normalize_level_for_url() ===\n";
echo "Test avec données corrompues et normales\n";
echo str_repeat("=", 60) . "\n\n";

$test_cases = [
    // Cas normaux
    "6ème" => "6eme",
    "5ème" => "5eme",
    "4ème" => "4eme",
    "3ème" => "3eme",
    "Seconde" => "seconde",
    "Première" => "premiere",
    "Terminale" => "terminale",
    
    // Cas avec corruption UTF-8 (caractères corrompus remplacés par ?)
    "4??me" => "4eme",  // Simulation d'une corruption
    "5??me" => "5eme",
    "3??me" => "3eme",
    "6??me" => "6eme",
    
    // Cas limites
    "4eme" => "4eme",   // Déjà nettoyé
    "seconde" => "seconde",
    "premiere" => "premiere",
    "" => "6eme",        // Vide = défaut
    "  4ème  " => "4eme",  // Avec espaces
    
    // Cas avec caractères corrompus mélangés
    "4è??e" => "4eme",
    "Seco??de" => "seconde",
];

$passed = 0;
$failed = 0;

foreach ($test_cases as $input => $expected) {
    $result = normalize_level_for_url($input);
    $status = ($result === $expected) ? "✅ PASS" : "❌ FAIL";
    
    if ($result === $expected) {
        $passed++;
    } else {
        $failed++;
    }
    
    printf("%-20s => %-15s (expected: %-15s) %s\n", 
        '"' . $input . '"',
        '"' . $result . '"',
        '"' . $expected . '"',
        $status
    );
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Résultats: $passed réussis, $failed échoués\n";

if ($failed === 0) {
    echo "✅ TOUS LES TESTS PASSENT!\n";
} else {
    echo "❌ CERTAINS TESTS ONT ÉCHOUÉ\n";
}

echo "\n=== TEST D'INTÉGRATION AVEC SITE_URL() ===\n";
echo "Simulation de la génération d'URL avec données corrompues\n";
echo str_repeat("=", 60) . "\n\n";

// Simuler l'utilisation dans le dashboard
$_SESSION['user_level'] = "4??me";  // Simulation d'une corruption
$user_level = $_SESSION['user_level'];
$level_normalized = normalize_level_for_url($user_level);

echo "Session user_level: " . json_encode($user_level) . "\n";
echo "Après normalize_level_for_url(): " . json_encode($level_normalized) . "\n";

// Construire l'URL comme dans le dashboard
$level_lower = strtolower(trim($user_level ?? ''));
$is_college = (preg_match('/[6543]/', $level_lower) && !preg_match('/seconde|premiere|terminale/', $level_lower));

echo "Est collège?: " . ($is_college ? "OUI" : "NON") . "\n";

if ($is_college) {
    $expected_url = "college/$level_normalized/exercices-$level_normalized";
} else {
    $expected_url = "lycee/$level_normalized/exercices-$level_normalized";
}

echo "URL construite: " . $expected_url . "\n";
echo "URL attendue: college/4eme/exercices-4eme\n";
echo "Status: " . ($expected_url === "college/4eme/exercices-4eme" ? "✅ CORRECT" : "❌ INCORRECT") . "\n";

?>
