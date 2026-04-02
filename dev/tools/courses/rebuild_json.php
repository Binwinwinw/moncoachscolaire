<?php
/**
 * Reconstruction robuste du fichier JSON
 * Lit le fichier corrompu et reconstruit un JSON valide
 */

$inputFile = 'dev/data/test_20_courses.json';
$outputFile = 'dev/data/test_20_courses_fixed.json';
$backupFile = 'dev/data/test_20_courses_backup.json';

if (!file_exists($inputFile)) {
    die("❌ Fichier introuvable: $inputFile\n");
}

echo "Reconstruction du fichier JSON\n";
echo str_repeat("=", 60) . "\n\n";

// Sauvegarde
if (!file_exists($backupFile)) {
    copy($inputFile, $backupFile);
    echo "✓ Sauvegarde créée: $backupFile\n";
}

// Lire le contenu brut
$content = file_get_contents($inputFile);
echo "✓ Fichier lu: " . strlen($content) . " octets\n";

// Nettoyer le contenu
$content = trim($content);

// Supprimer le [ au début et ] à la fin si présent
if ($content[0] === '[') {
    $content = substr($content, 1);
}
if ($content[strlen($content) - 1] === ']') {
    $content = substr($content, 0, -1);
}

echo "✓ Contenu nettoyé\n";

// Stratégie: Découper par les marqueurs d'objets de cours
// Chaque cours commence par { "id": X,
$pattern = '/\{\s*"id"\s*:\s*\d+/';
preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);

echo "✓ " . count($matches[0]) . " cours détectés\n\n";

$courses = [];
$errorCount = 0;

for ($i = 0; $i < count($matches[0]); $i++) {
    $startPos = $matches[0][$i][1];

    // Trouver la fin de cet objet (le prochain objet ou la fin du fichier)
    if ($i < count($matches[0]) - 1) {
        $endPos = $matches[0][$i + 1][1];
        $jsonStr = substr($content, $startPos, $endPos - $startPos);
        // Enlever la virgule finale si présente
        $jsonStr = rtrim($jsonStr);
        if ($jsonStr[strlen($jsonStr) - 1] === ',') {
            $jsonStr = substr($jsonStr, 0, -1);
        }
    } else {
        $jsonStr = substr($content, $startPos);
        $jsonStr = rtrim($jsonStr);
        if ($jsonStr[strlen($jsonStr) - 1] === ',') {
            $jsonStr = substr($jsonStr, 0, -1);
        }
    }

    // Essayer de parser ce cours
    $course = json_decode($jsonStr, true);

    if ($course === null) {
        echo "⚠️  Erreur cours #" . ($i + 1) . ": " . json_last_error_msg() . "\n";
        echo "   Tentative de nettoyage...\n";

        // Nettoyer les caractères problématiques
        $jsonStr = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $jsonStr);

        // Échapper les guillemets dans les valeurs HTML
        $jsonStr = preg_replace_callback(
            '/"(key_point|formula|title|description|section|domain|competence|question|answer)"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s',
            function($m) {
                $key = $m[1];
                $value = $m[2];
                // Ne rien faire si déjà échappé correctement
                return '"' . $key . '": "' . $value . '"';
            },
            $jsonStr
        );

        $course = json_decode($jsonStr, true);

        if ($course === null) {
            $errorCount++;
            echo "   ❌ Échec du nettoyage\n";
            continue;
        } else {
            echo "   ✓ Cours récupéré\n";
        }
    }

    $courses[] = $course;
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "RÉSULTAT DE LA RECONSTRUCTION\n";
echo str_repeat("=", 60) . "\n";
echo "Cours récupérés: " . count($courses) . "/" . count($matches[0]) . "\n";
echo "Erreurs: $errorCount\n\n";

if (count($courses) > 0) {
    // Créer la structure finale
    $finalData = ['courses' => $courses];

    // Encoder avec options pour gérer les caractères spéciaux
    $prettyJson = json_encode(
        $finalData, 
        JSON_PRETTY_PRINT | 
        JSON_UNESCAPED_UNICODE | 
        JSON_UNESCAPED_SLASHES
    );

    if ($prettyJson === false) {
        die("❌ Erreur lors de l'encodage JSON final\n");
    }

    // Sauvegarder
    file_put_contents($outputFile, $prettyJson);

    echo "✅ Fichier reconstruit avec succès!\n";
    echo "✓ Fichier: $outputFile\n";
    echo "✓ Taille: " . strlen($prettyJson) . " octets\n\n";

    // Statistiques
    $totalExercises = 0;
    foreach ($courses as $course) {
        $totalExercises += count($course['exercises'] ?? []);
    }

    echo "Statistiques:\n";
    echo "- Cours: " . count($courses) . "\n";
    echo "- Exercices: $totalExercises\n";
    echo "- Moyenne: " . round($totalExercises / count($courses), 2) . " exercices/cours\n\n";

    // Valider le JSON généré
    $validation = json_decode($prettyJson, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON validé et correct!\n\n";
        echo "Pour remplacer le fichier original:\n";
        echo "   move $outputFile $inputFile\n";
    } else {
        echo "❌ Le JSON généré contient encore des erreurs\n";
    }

} else {
    echo "❌ Aucun cours n'a pu être récupéré\n";
}
