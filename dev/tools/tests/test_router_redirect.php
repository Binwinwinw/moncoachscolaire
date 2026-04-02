<?php
/**
 * Simuler une requête avec une URL corrompue et vérifier que le routeur la nettoie
 */

// Définir l'URL corrompue
$_GET['page'] = 'college/4??me/exercices-4??me';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING'] = 'page=college%2F4%3F%3Fme%2Fexercices-4%3F%3Fme';
$_SERVER['HTTP_HOST'] = 'localhost';

// Charger le routeur dans un contexte contrôlé pour tester le nettoyage
$root = __DIR__;

// Charger config.php pour initialiser baseUrl
if (is_file($root . '/src/config/config.php')) {
    require_once $root . '/src/config/config.php';
}

echo "=== TEST DE REDIRECTION DU ROUTEUR ===\n";
echo str_repeat("=", 60) . "\n\n";

// Effectuer le nettoyage exactement comme le routeur le ferait
$pageRaw = trim($_GET['page'], '/');
$pageRaw = preg_replace('#/+#', '/', $pageRaw);

echo "URL reçue (GET['page']): " . $pageRaw . "\n";

// ÉTAPE 1 : Nettoyer les caractères corrompus
$pageClean = $pageRaw;
$corruptionFound = false;

if (strpos($pageClean, '?') !== false && strpos($pageClean, '??') !== false) {
    $pageClean = str_replace('??', '', $pageClean);
    $corruptionFound = true;
    $pageClean = preg_replace('/([6543])me/', '\1eme', $pageClean);
}

echo "Après nettoyage corruption: " . $pageClean . "\n";
echo "Corruption trouvée: " . ($corruptionFound ? "OUI" : "NON") . "\n";

// ÉTAPE 2 : Normaliser les accents
$normalizedPage = $pageClean;
$needsNormalization = false;

// Vérifier les accents
$accents = ['6ème', '5ème', '4ème', '3ème'];
foreach ($accents as $accent) {
    if (stripos($pageClean, $accent) !== false) {
        $normalizedPage = str_ireplace($accent, str_replace('ème', 'eme', $accent), $pageClean);
        $needsNormalization = true;
    }
}

echo "Après normalisation accents: " . $normalizedPage . "\n";
echo "Normalisation nécessaire: " . ($needsNormalization ? "OUI" : "NON") . "\n";

echo "\n" . str_repeat("=", 60) . "\n";

// Vérifier si une redirection est nécessaire
if (($corruptionFound || $needsNormalization) && $normalizedPage !== $pageRaw) {
    echo "\n✅ REDIRECTION NÉCESSAIRE:\n";
    echo "  De: " . $pageRaw . "\n";
    echo "  Vers: " . $normalizedPage . "\n";
    echo "  URL complète: /index.php?page=" . urlencode($normalizedPage) . "\n";
} else {
    echo "\n✅ AUCUNE REDIRECTION NÉCESSAIRE\n";
}

// Fichier final
$candidates = [
    $root . '/src/pages/' . $normalizedPage . '.php',
    $root . '/src/pages/' . $normalizedPage . '/index.php',
];

echo "\nFichiers à chercher:\n";
foreach ($candidates as $cand) {
    $exists = file_exists($cand) ? "✅ EXISTE" : "❌ N'EXISTE PAS";
    echo "  $exists: $cand\n";
}

?>
