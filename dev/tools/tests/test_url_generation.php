<?php
/**
 * TEST : Vérifier la génération d'URLs pour tous les niveaux
 */

// Charger la configuration
require_once __DIR__ . '/../src/config/site_boot.php';

$college_levels = ['6ème', '5ème', '4ème', '3ème'];
$lycee_levels = ['Seconde', 'Première', 'Terminale'];

echo "🧪 TEST DES URLS GÉNÉRÉES\n\n";

// Simulation pour chaque niveau
foreach ($college_levels as $level) {
    $normalized = normalize_level_for_url($level);
    $url = site_url('college/' . $normalized . '/exercices-' . $normalized);
    echo "✅ Collège - $level : $url\n";
}

echo "\n";

foreach ($lycee_levels as $level) {
    $normalized = normalize_level_for_url($level);
    $url = site_url('lycee/' . $normalized . '/exercices-' . $normalized);
    echo "✅ Lycée - $level : $url\n";
}

echo "\n\n";

// Fonction getDashboardUrlForLevel de register.php
function getDashboardUrlForLevelTest($level) {
    if (in_array($level, ['6ème', '6eme'])) {
        $url = site_url('college/6eme/exercices-6eme', ['welcome' => '1']);
        echo "TEST REGISTER: 6ème → $url\n";
        return $url;
    } elseif (in_array($level, ['5ème', '5eme'])) {
        $url = site_url('college/5eme/exercices-5eme', ['welcome' => '1']);
        echo "TEST REGISTER: 5ème → $url\n";
        return $url;
    } elseif (in_array($level, ['4ème', '4eme'])) {
        $url = site_url('college/4eme/exercices-4eme', ['welcome' => '1']);
        echo "TEST REGISTER: 4ème → $url\n";
        return $url;
    } elseif (in_array($level, ['3ème', '3eme'])) {
        $url = site_url('college/3eme/exercices-3eme', ['welcome' => '1']);
        echo "TEST REGISTER: 3ème → $url\n";
        return $url;
    } elseif (in_array($level, ['Seconde', 'seconde'])) {
        $url = site_url('lycee/seconde/exercices-seconde', ['welcome' => '1']);
        echo "TEST REGISTER: Seconde → $url\n";
        return $url;
    }
}

echo "REGISTER.PHP REDIRECTS TEST:\n";
getDashboardUrlForLevelTest('6ème');
getDashboardUrlForLevelTest('Seconde');

?>
