<?php
/**
 * Script de correction du fichier JSON
 * Corrige les erreurs de syntaxe et valide le résultat
 */

$inputFile = 'dev/data/test_20_courses.json';
$outputFile = 'dev/data/test_20_courses_fixed.json';
$backupFile = 'dev/data/test_20_courses_backup.json';

if (!file_exists($inputFile)) {
    die("❌ Fichier introuvable: $inputFile\n");
}

echo "Correction du fichier JSON\n";
echo str_repeat("=", 60) . "\n\n";

// Faire une sauvegarde
copy($inputFile, $backupFile);
echo "✓ Sauvegarde créée: $backupFile\n";

// Lire le contenu
$content = file_get_contents($inputFile);
echo "✓ Fichier lu: " . strlen($content) . " octets\n\n";

// Méthode 1: Essayer de parser avec une approche plus tolérante
echo "Tentative de correction...\n";

// Détecter si c'est un array ou un objet au premier niveau
$content = trim($content);

// Essayer de parser ligne par ligne pour trouver l'erreur
$lines = explode("\n", $content);
$fixedLines = [];
$inString = false;
$stringChar = null;

foreach ($lines as $lineNum => $line) {
    // Vérifier les guillemets non échappés dans les valeurs HTML
    $fixedLine = $line;

    // Détecter si on est dans une valeur de propriété contenant du HTML
    if (preg_match('/"(key_point|formula|title|description)"\s*:\s*"/', $line)) {
        // Cette ligne contient potentiellement du HTML avec des guillemets
        // On va s'assurer que les guillemets internes sont échappés
        $fixedLine = preg_replace_callback(
            '/"(key_point|formula|title|description|section|domain|competence)"\s*:\s*"(.*?)"([,\s]*$)/s',
            function($matches) {
                $key = $matches[1];
                $value = $matches[2];
                $end = $matches[3];

                // Échapper les guillemets qui ne sont pas déjà échappés
                $value = str_replace('\\"', '___ESCAPED_QUOTE___', $value);
                $value = str_replace('"', '\\"', $value);
                $value = str_replace('___ESCAPED_QUOTE___', '\\"', $value);

                return "\"$key\": \"$value\"$end";
            },
            $line
        );
    }

    $fixedLines[] = $fixedLine;
}

$fixedContent = implode("\n", $fixedLines);

// Essayer de décoder
$data = json_decode($fixedContent, true);

if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON corrigé avec succès!\n";

    // Réécrire proprement le JSON
    $prettyJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    file_put_contents($outputFile, $prettyJson);
    echo "✓ Fichier corrigé sauvegardé: $outputFile\n";
    echo "✓ Nouvelle taille: " . strlen($prettyJson) . " octets\n\n";

    // Statistiques
    if (isset($data['courses'])) {
        echo "Statistiques:\n";
        echo "- Nombre de cours: " . count($data['courses']) . "\n";

        $totalExercises = 0;
        foreach ($data['courses'] as $course) {
            $totalExercises += count($course['exercises'] ?? []);
        }
        echo "- Total exercices: $totalExercises\n\n";
    }

    echo "✅ Pour remplacer le fichier original:\n";
    echo "   move $outputFile $inputFile\n\n";
    echo "✅ Pour restaurer la sauvegarde si besoin:\n";
    echo "   move $backupFile $inputFile\n";

} else {
    echo "❌ Échec de la correction automatique\n";
    echo "Erreur: " . json_last_error_msg() . "\n\n";

    // Méthode alternative: reconstruire complètement
    echo "Tentative de reconstruction complète...\n";

    // Utiliser une regex pour extraire les objets JSON un par un
    preg_match_all('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $content, $matches);

    if (!empty($matches[0])) {
        echo "✓ " . count($matches[0]) . " objets JSON trouvés\n";

        $courses = [];
        foreach ($matches[0] as $jsonObj) {
            $obj = json_decode($jsonObj, true);
            if ($obj !== null) {
                $courses[] = $obj;
            }
        }

        if (!empty($courses)) {
            $newData = ['courses' => $courses];
            $prettyJson = json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            file_put_contents($outputFile, $prettyJson);
            echo "✅ Reconstruction réussie!\n";
            echo "✓ Fichier sauvegardé: $outputFile\n";
            echo "✓ Cours reconstruits: " . count($courses) . "\n";
        }
    }
}
