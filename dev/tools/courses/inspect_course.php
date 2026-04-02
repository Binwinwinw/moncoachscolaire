<?php
/**
 * Inspection détaillée du premier cours pour identifier l'erreur
 */

$inputFile = 'dev/data/test_20_courses.json';

if (!file_exists($inputFile)) {
    die("❌ Fichier introuvable: $inputFile\n");
}

$content = file_get_contents($inputFile);
$content = trim($content);

// Supprimer [ et ] extérieurs
if ($content[0] === '[') {
    $content = substr($content, 1);
}
if ($content[strlen($content) - 1] === ']') {
    $content = substr($content, 0, -1);
}

// Trouver le premier cours
preg_match('/\{\s*"id"\s*:\s*\d+/', $content, $match, PREG_OFFSET_CAPTURE);

if (empty($match)) {
    die("❌ Aucun cours trouvé\n");
}

$startPos = $match[0][1];

// Trouver le deuxième cours pour avoir la limite
preg_match_all('/\{\s*"id"\s*:\s*\d+/', $content, $matches, PREG_OFFSET_CAPTURE);

if (count($matches[0]) > 1) {
    $endPos = $matches[0][1][1];
} else {
    $endPos = strlen($content);
}

$firstCourse = substr($content, $startPos, $endPos - $startPos);
$firstCourse = rtrim($firstCourse);
if ($firstCourse[strlen($firstCourse) - 1] === ',') {
    $firstCourse = substr($firstCourse, 0, -1);
}

echo "INSPECTION DU PREMIER COURS\n";
echo str_repeat("=", 60) . "\n\n";

echo "Longueur: " . strlen($firstCourse) . " caractères\n\n";

// Sauvegarder dans un fichier temporaire
$tempFile = 'dev/data/temp_first_course.json';
file_put_contents($tempFile, $firstCourse);
echo "✓ Cours extrait dans: $tempFile\n\n";

// Essayer de parser
$course = json_decode($firstCourse, true);

if ($course === null) {
    echo "❌ Erreur JSON: " . json_last_error_msg() . "\n\n";

    // Afficher les 500 premiers caractères
    echo "--- Début du cours (500 caractères) ---\n";
    echo substr($firstCourse, 0, 500) . "\n";
    echo "--- ... ---\n\n";

    // Afficher les 500 derniers caractères
    echo "--- Fin du cours (500 caractères) ---\n";
    echo substr($firstCourse, -500) . "\n";
    echo "--- ... ---\n\n";

    // Chercher des patterns problématiques
    echo "Analyse des patterns problématiques:\n";

    // Guillemets non fermés
    $quoteCount = substr_count($firstCourse, '"');
    $escapedQuoteCount = substr_count($firstCourse, '\\"');
    $actualQuotes = $quoteCount - ($escapedQuoteCount * 2);
    echo "- Guillemets totaux: $quoteCount\n";
    echo "- Guillemets échappés: $escapedQuoteCount\n";
    echo "- Guillemets réels: $actualQuotes " . ($actualQuotes % 2 === 0 ? "✓ pair" : "❌ impair") . "\n\n";

    // Chercher des lignes avec des guillemets suspects
    echo "Lignes avec potentiellement des guillemets non échappés:\n";
    $lines = explode("\n", $firstCourse);
    $lineNum = 0;
    foreach ($lines as $line) {
        $lineNum++;
        // Chercher des lignes qui contiennent ": " suivi de texte avec des guillemets
        if (preg_match('/"\w+"\s*:\s*"[^"]*"[^"]*"/', $line)) {
            echo "Ligne $lineNum: " . substr($line, 0, 80) . "...\n";
        }
    }

} else {
    echo "✅ Le premier cours est valide!\n";
    echo "Structure:\n";
    print_r(array_keys($course));
}
