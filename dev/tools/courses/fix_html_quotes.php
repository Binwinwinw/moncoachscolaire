<?php
/**
 * Correction des guillemets non échappés dans le JSON
 * Remplace les guillemets doubles par des apostrophes simples dans les attributs HTML
 */

$inputFile = 'dev/data/test_20_courses.json';
$outputFile = 'dev/data/test_20_courses_fixed.json';
$backupFile = 'dev/data/test_20_courses_backup.json';

if (!file_exists($inputFile)) {
    die("❌ Fichier introuvable: $inputFile\n");
}

echo "CORRECTION DES GUILLEMETS NON ÉCHAPPÉS\n";
echo str_repeat("=", 60) . "\n\n";

// Sauvegarde
if (!file_exists($backupFile)) {
    copy($inputFile, $backupFile);
    echo "✓ Sauvegarde créée: $backupFile\n";
}

// Lire le contenu
$content = file_get_contents($inputFile);
echo "✓ Fichier lu: " . strlen($content) . " octets\n";

// Stratégie de correction:
// Dans les valeurs JSON (entre "key": "value"), remplacer les guillemets doubles par des apostrophes simples
// pour les attributs HTML

echo "\nCorrection en cours...\n";

// Pattern pour capturer les valeurs de propriétés
$fixed = preg_replace_callback(
    '/"(\w+)"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s',
    function($matches) {
        $key = $matches[1];
        $value = $matches[2];

        // Si la valeur contient du HTML (détecté par la présence de <)
        if (strpos($value, '<') !== false) {
            // Remplacer les guillemets doubles par des apostrophes simples dans les attributs HTML
            // Pattern: attribute="value" devient attribute='value'
            $value = preg_replace('/\s(\w+)="([^"]*)"/U', ' $1=\'$2\'', $value);
        }

        return '"' . $key . '": "' . $value . '"';
    },
    $content
);

if ($fixed === null) {
    die("❌ Erreur lors de la correction\n");
}

echo "✓ Guillemets corrigés\n";

// Tenter de valider
$data = json_decode($fixed, true);

if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON validé avec succès!\n\n";

    // Reformater proprement
    $prettyJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    // Sauvegarder
    file_put_contents($outputFile, $prettyJson);

    echo "Résultat:\n";
    echo "✓ Fichier corrigé: $outputFile\n";
    echo "✓ Taille: " . strlen($prettyJson) . " octets\n\n";

    // Statistiques
    if (isset($data['courses'])) {
        $totalExercises = 0;
        foreach ($data['courses'] as $course) {
            $totalExercises += count($course['exercises'] ?? []);
        }

        echo "Statistiques:\n";
        echo "- Cours: " . count($data['courses']) . "\n";
        echo "- Exercices: $totalExercises\n";
        echo "- Moyenne: " . round($totalExercises / count($data['courses']), 2) . " exercices/cours\n\n";
    }

    echo "✅ SUCCÈS! Pour utiliser le fichier corrigé:\n";
    echo "   move $outputFile $inputFile\n\n";
    echo "Pour restaurer la sauvegarde si besoin:\n";
    echo "   move $backupFile $inputFile\n";

} else {
    echo "❌ Le JSON contient encore des erreurs: " . json_last_error_msg() . "\n";
    echo "Le fichier partiellement corrigé a été sauvegardé dans: $outputFile\n";
    file_put_contents($outputFile, $fixed);
}
