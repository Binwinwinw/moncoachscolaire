<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  SCRIPT DE DEBUG - ExerciseNormalizer
 * ═══════════════════════════════════════════════════════════════
 *
 * Teste la normalisation d'un exercice pour identifier
 * les problèmes de mapping des champs.
 *
 * Usage:
 *   php dev/tools/exercises/debug_normalizer.php
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
require_once PROJECT_ROOT . '/dev/db/json/schema/exercices/ExerciseNormalizer.php';

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  DEBUG EXERCISENORMALIZER\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// 1. Charger un exercice depuis la BDD
$dbFile = PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_from_database.json';
$dbData = json_decode(file_get_contents($dbFile), true);
$sampleDb = $dbData[0] ?? null;

if (!$sampleDb) {
    die("❌ Impossible de charger un exercice de la BDD\n");
}

echo "📄 Exercice BDD (avant normalisation):\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "Clés présentes: " . implode(', ', array_keys($sampleDb)) . "\n";
echo "Identifier: " . ($sampleDb['Identifier'] ?? 'NON PRÉSENT') . "\n";
echo "Subject: " . ($sampleDb['Subject'] ?? 'NON PRÉSENT') . "\n";
echo "Title: " . ($sampleDb['Title'] ?? 'NON PRÉSENT') . "\n";
echo "\n";

// 2. Normaliser
echo "🔄 Normalisation via ExerciseNormalizer::normalizeToDbFields()...\n";
$normalized = ExerciseNormalizer::normalizeToDbFields($sampleDb);

echo "\n";
echo "📄 Exercice normalisé (après normalisation):\n";
echo "───────────────────────────────────────────────────────────────\n";
echo "Clés présentes: " . implode(', ', array_keys($normalized)) . "\n";
echo "Identifier: " . ($normalized['Identifier'] ?? '❌ NON PRÉSENT') . "\n";
echo "Subject: " . ($normalized['Subject'] ?? '❌ NON PRÉSENT') . "\n";
echo "Title: " . ($normalized['Title'] ?? '❌ NON PRÉSENT') . "\n";
echo "\n";

// 3. Charger un exercice JSON migré
$jsonFile = PROJECT_ROOT . '/dev/db/json/schema/exercices/unified_exercises.json';
$jsonData = json_decode(file_get_contents($jsonFile), true);
$sampleJson = $jsonData[0] ?? null;

if ($sampleJson) {
    echo "📄 Exercice JSON migré (avant normalisation):\n";
    echo "───────────────────────────────────────────────────────────────\n";
    echo "Clés présentes: " . implode(', ', array_keys($sampleJson)) . "\n";
    echo "Identifier: " . ($sampleJson['Identifier'] ?? $sampleJson['identifier'] ?? 'NON PRÉSENT') . "\n";
    echo "\n";

    $normalizedJson = ExerciseNormalizer::normalizeToDbFields($sampleJson);

    echo "📄 Exercice JSON normalisé (après normalisation):\n";
    echo "───────────────────────────────────────────────────────────────\n";
    echo "Clés présentes: " . implode(', ', array_keys($normalizedJson)) . "\n";
    echo "Identifier: " . ($normalizedJson['Identifier'] ?? '❌ NON PRÉSENT') . "\n";
    echo "\n";
}

// 4. Test de détection
echo "🔍 TEST DE DÉTECTION D'IDENTIFIER\n";
echo "───────────────────────────────────────────────────────────────\n";

function testDetection($ex, $label) {
    $id = $ex['Identifier'] ?? null;

    if (empty($id)) {
        echo "❌ $label: Identifier vide ou absent\n";
        echo "   Clés disponibles: " . implode(', ', array_keys($ex)) . "\n";
    } else {
        echo "✅ $label: Identifier trouvé = $id\n";
    }
}

testDetection($normalized, "BDD normalisé");
if (isset($normalizedJson)) {
    testDetection($normalizedJson, "JSON normalisé");
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  DIAGNOSTIC\n";
echo "═══════════════════════════════════════════════════════════════\n";

if (!isset($normalized['Identifier']) || empty($normalized['Identifier'])) {
    echo "❌ PROBLÈME IDENTIFIÉ:\n";
    echo "   La normalisation NE PRÉSERVE PAS la clé 'Identifier'\n";
    echo "\n";
    echo "💡 SOLUTION:\n";
    echo "   Modifier ExerciseNormalizer::normalizeToDbFields() pour\n";
    echo "   s'assurer que la clé 'Identifier' est bien conservée.\n";
} else {
    echo "✅ La normalisation préserve correctement 'Identifier'\n";
    echo "   Le problème vient probablement d'ailleurs dans deduplicate_exercises_v3.php\n";
}

echo "\n";
