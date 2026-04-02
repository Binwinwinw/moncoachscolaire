<?php
/**
 * NORMALISATION UNIVERSELLE DU SCHÉMA EXERCISES
 *
 * Convertit TOUS les fichiers JSON vers le format BDD compatible :
 * - Answer: array → JSON string
 * - Choices: array → JSON string
 * - LinkedCourses: array → JSON string
 * - InteractiveConfig: array → JSON string
 *
 * Usage :
 *   php normalize_exercises_schema.php <input_file> [output_file]
 *
 * Exemples :
 *   php normalize_exercises_schema.php unified_exercises.json
 *   php normalize_exercises_schema.php exercises_enriched.json exercises_ready.json
 */

if ($argc < 2) {
    die("Usage: php normalize_exercises_schema.php <input_file> [output_file]\n");
}

$inputFile = $argv[1];
$outputFile = $argv[2] ?? str_replace('.json', '_normalized.json', $inputFile);

define('PROJECT_ROOT', dirname(__DIR__, 3));

// Chemins absolus
if (!file_exists($inputFile)) {
    $inputFile = PROJECT_ROOT . '/dev/db/json/schema/exercices/' . basename($inputFile);
}

if (!file_exists($inputFile)) {
    die("❌ Fichier introuvable : $inputFile\n");
}

$outputPath = dirname($inputFile) . '/' . basename($outputFile);

echo "═══════════════════════════════════════════════════════════════\n";
echo "  NORMALISATION SCHÉMA EXERCISES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "📄 Entrée:  " . basename($inputFile) . "\n";
echo "📄 Sortie:  " . basename($outputPath) . "\n\n";

// 1. CHARGEMENT
echo "📥 Chargement...\n";
$json = file_get_contents($inputFile);
$exercises = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ ERREUR JSON : " . json_last_error_msg() . "\n");
}

$total = count($exercises);
echo "✅ $total exercices chargés\n\n";

// 2. NORMALISATION
echo "🔧 Normalisation en cours...\n";

$stats = [
    'total' => $total,
    'answer_array_to_json' => 0,
    'answer_string_kept' => 0,
    'answer_null_kept' => 0,
    'choices_array_to_json' => 0,
    'choices_string_kept' => 0,
    'linkedcourses_converted' => 0,
    'interactiveconfig_converted' => 0,
];

$examples = [
    'answer_converted' => [],
    'choices_converted' => [],
];

foreach ($exercises as $index => &$ex) {
    $identifier = $ex['Identifier'] ?? "UNKNOWN-$index";

    // ========== ANSWER ==========
    if (isset($ex['Answer'])) {
        if (is_array($ex['Answer'])) {
            $before = $ex['Answer'];
            $ex['Answer'] = json_encode($ex['Answer'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stats['answer_array_to_json']++;

            if (count($examples['answer_converted']) < 5) {
                $examples['answer_converted'][] = [
                    'identifier' => $identifier,
                    'before' => json_encode($before),
                    'after' => $ex['Answer'],
                ];
            }
        } elseif (is_string($ex['Answer'])) {
            $stats['answer_string_kept']++;
        }
    } else {
        $stats['answer_null_kept']++;
    }

    // ========== CHOICES ==========
    if (isset($ex['Choices'])) {
        if (is_array($ex['Choices'])) {
            $before = $ex['Choices'];
            $ex['Choices'] = json_encode($ex['Choices'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $stats['choices_array_to_json']++;

            if (count($examples['choices_converted']) < 5) {
                $examples['choices_converted'][] = [
                    'identifier' => $identifier,
                    'count' => count($before),
                ];
            }
        } elseif (is_string($ex['Choices'])) {
            $stats['choices_string_kept']++;
        }
    }

    // ========== LINKEDCOURSES ==========
    if (isset($ex['LinkedCourses']) && is_array($ex['LinkedCourses'])) {
        $ex['LinkedCourses'] = json_encode($ex['LinkedCourses'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stats['linkedcourses_converted']++;
    }

    // ========== INTERACTIVECONFIG ==========
    if (isset($ex['InteractiveConfig']) && is_array($ex['InteractiveConfig'])) {
        $ex['InteractiveConfig'] = json_encode($ex['InteractiveConfig'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stats['interactiveconfig_converted']++;
    }

    // Progression
    if (($index + 1) % 100 === 0) {
        echo "   Traité: " . ($index + 1) . "/$total\n";
    }
}

echo "\n";

// 3. SAUVEGARDE
echo "💾 Sauvegarde...\n";
$output = json_encode($exercises, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents($outputPath, $output);

$fileSize = filesize($outputPath);
echo "✅ Fichier créé : " . basename($outputPath) . " (" . round($fileSize / 1024 / 1024, 2) . " MB)\n\n";

// 4. RÉSUMÉ
echo "═══════════════════════════════════════════════════════════════\n";
echo "  ✅ NORMALISATION TERMINÉE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total exercices:              %4d\n\n", $stats['total']);

echo "ANSWER\n";
echo "───────────────────────────────────────────────────────────────\n";
echo sprintf("Array → JSON string:          %4d\n", $stats['answer_array_to_json']);
echo sprintf("String conservé:              %4d\n", $stats['answer_string_kept']);
echo sprintf("NULL conservé:                %4d\n\n", $stats['answer_null_kept']);

echo "CHOICES\n";
echo "───────────────────────────────────────────────────────────────\n";
echo sprintf("Array → JSON string:          %4d\n", $stats['choices_array_to_json']);
echo sprintf("String conservé:              %4d\n\n", $stats['choices_string_kept']);

echo "AUTRES CHAMPS\n";
echo "───────────────────────────────────────────────────────────────\n";
echo sprintf("LinkedCourses convertis:      %4d\n", $stats['linkedcourses_converted']);
echo sprintf("InteractiveConfig convertis:  %4d\n", $stats['interactiveconfig_converted']);
echo "═══════════════════════════════════════════════════════════════\n\n";

if (count($examples['answer_converted']) > 0) {
    echo "EXEMPLES : Answer array → JSON\n";
    echo "───────────────────────────────────────────────────────────────\n";
    foreach ($examples['answer_converted'] as $i => $ex) {
        echo sprintf("%d. %s\n", $i + 1, $ex['identifier']);
        echo "   AVANT: " . $ex['before'] . "\n";
        echo "   APRÈS: " . $ex['after'] . "\n\n";
    }
}

if (count($examples['choices_converted']) > 0) {
    echo "EXEMPLES : Choices array → JSON\n";
    echo "───────────────────────────────────────────────────────────────\n";
    foreach ($examples['choices_converted'] as $i => $ex) {
        echo sprintf("%d. %s (%d choix)\n", $i + 1, $ex['identifier'], $ex['count']);
    }
    echo "\n";
}

echo "📋 Prochaine étape:\n";
echo "   Utiliser ce fichier pour l'import en BDD\n\n";
