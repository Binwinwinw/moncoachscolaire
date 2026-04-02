<?php
/**
 * Diagnostic de l'erreur JSON
 */

$file = 'dev/data/test_20_courses.json';

if (!file_exists($file)) {
    die("❌ Fichier introuvable: $file\n");
}

echo "Diagnostic de l'erreur JSON\n";
echo str_repeat("=", 60) . "\n\n";

// Lire le contenu
$content = file_get_contents($file);
echo "✓ Taille du fichier: " . strlen($content) . " octets\n";
echo "✓ Premiers 200 caractères:\n";
echo substr($content, 0, 200) . "\n\n";

// Essayer de décoder
$data = json_decode($content, true);
$error = json_last_error();
$errorMsg = json_last_error_msg();

if ($error !== JSON_ERROR_NONE) {
    echo "❌ Erreur JSON détectée:\n";
    echo "Code d'erreur: $error\n";
    echo "Message: $errorMsg\n\n";

    // Essayer de trouver la position de l'erreur
    echo "Tentative de localisation de l'erreur...\n";

    // Vérifier les accolades/crochets
    $openBraces = substr_count($content, '{');
    $closeBraces = substr_count($content, '}');
    $openBrackets = substr_count($content, '[');
    $closeBrackets = substr_count($content, ']');

    echo "Accolades ouvrantes '{': $openBraces\n";
    echo "Accolades fermantes '}': $closeBraces\n";
    echo "Crochets ouvrants '[': $openBrackets\n";
    echo "Crochets fermants ']': $closeBrackets\n\n";

    if ($openBraces !== $closeBraces) {
        echo "⚠️  Déséquilibre des accolades!\n";
    }
    if ($openBrackets !== $closeBrackets) {
        echo "⚠️  Déséquilibre des crochets!\n";
    }

    // Afficher les 200 derniers caractères
    echo "\n✓ Derniers 200 caractères:\n";
    echo substr($content, -200) . "\n";

} else {
    echo "✅ JSON valide!\n";
}
