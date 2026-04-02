<?php
/**
 * ═══════════════════════════════════════════════════════════════
 *  CORRECTION DES 12 EXERCICES EN ERREUR
 * ═══════════════════════════════════════════════════════════════
 *
 * Corrige le champ Choices pour les exercices qui ont échoué
 * à l'import à cause de la contrainte CHECK JSON.
 */

define('PROJECT_ROOT', dirname(__DIR__, 3));
require_once PROJECT_ROOT . '/src/database/connection.php';

$failedIdentifiers = [
    'FR-1ERE-LITTERATURE-001',
    'FR-1ERE-DISSERTATION-002',
    'FR-1ERE-ORAL-003',
    'FR-1ERE-DISSERTATION-008',
    'FR-1ERE-GRAMMAIRE-010',
    'FR-1ERE-REGISTRES-013',
    'FR-1ERE-FIGURES-014',
    'FR-1ERE-ESSAI-015',
    'MATH-2NDE-PROBABILITES-005',
    'MATH-2NDE-EQUATIONS-008',
    'MATH-2NDE-PROBA-014',
    'MATH-TERM-PROBA-COND-001'
];

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  CORRECTION DES 12 EXERCICES EN ERREUR\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// Charger le fichier JSON
$jsonFile = PROJECT_ROOT . '/dev/db/json/schema/exercices/exercises_final_deduplicated.json';
$exercises = json_decode(file_get_contents($jsonFile), true);

echo "📄 Fichier chargé : " . count($exercises) . " exercices\n\n";

// Connexion BDD
$pdo = new PDO(
    "mysql:host=localhost;dbname=moncoachscolaire;charset=utf8mb4",
    "root",
    ""
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🔌 Connecté à la BDD\n\n";

echo "🔧 Correction des exercices...\n";

$fixed = 0;
$notFound = 0;

foreach ($failedIdentifiers as $identifier) {
    // Trouver l'exercice dans le JSON
    $exercise = null;
    foreach ($exercises as $ex) {
        if (($ex['Identifier'] ?? '') === $identifier) {
            $exercise = $ex;
            break;
        }
    }

    if (!$exercise) {
        echo "   ❌ $identifier : Non trouvé dans le JSON\n";
        $notFound++;
        continue;
    }

    // Préparer les données
    $choices = $exercise['Choices'] ?? null;

    // Convertir "" en NULL ou en JSON valide
    if ($choices === '' || $choices === '""') {
        $choices = null;
    } elseif (is_string($choices) && $choices !== 'null') {
        // Si c'est une chaîne, essayer de la décoder
        $decoded = json_decode($choices, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $choices = $choices; // C'est déjà du JSON valide
        } else {
            $choices = null; // JSON invalide → NULL
        }
    }

    // Vérifier si l'exercice existe déjà
    $stmt = $pdo->prepare("SELECT Id FROM exercises WHERE Identifier = ?");
    $stmt->execute([$identifier]);
    $exists = $stmt->fetch();

    if ($exists) {
        // UPDATE
        $sql = "UPDATE exercises SET
            Subject = :Subject,
            Level = :Level,
            Title = :Title,
            Content = :Content,
            Answer = :Answer,
            Tips = :Tips,
            Domain = :Domain,
            Competence = :Competence,
            Difficulty = :Difficulty,
            AnswerType = :AnswerType,
            XP_Points = :XP_Points,
            is_active = :is_active,
            Choices = :Choices,
            InteractiveConfig = :InteractiveConfig,
            LinkedCourses = :LinkedCourses
        WHERE Identifier = :Identifier";
    } else {
        // INSERT
        $sql = "INSERT INTO exercises (
            Subject, Level, Title, Content, Answer, Tips, Domain, Competence,
            Difficulty, Identifier, AnswerType, XP_Points, is_active,
            Choices, InteractiveConfig, LinkedCourses
        ) VALUES (
            :Subject, :Level, :Title, :Content, :Answer, :Tips, :Domain, :Competence,
            :Difficulty, :Identifier, :AnswerType, :XP_Points, :is_active,
            :Choices, :InteractiveConfig, :LinkedCourses
        )";
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':Subject' => $exercise['Subject'] ?? null,
            ':Level' => $exercise['Level'] ?? null,
            ':Title' => $exercise['Title'] ?? null,
            ':Content' => $exercise['Content'] ?? null,
            ':Answer' => $exercise['Answer'] ?? null,
            ':Tips' => $exercise['Tips'] ?? null,
            ':Domain' => $exercise['Domain'] ?? null,
            ':Competence' => $exercise['Competence'] ?? null,
            ':Difficulty' => $exercise['Difficulty'] ?? null,
            ':AnswerType' => $exercise['AnswerType'] ?? null,
            ':XP_Points' => $exercise['XP_Points'] ?? 0,
            ':is_active' => $exercise['is_active'] ?? 1,
            ':Choices' => $choices,
            ':InteractiveConfig' => $exercise['InteractiveConfig'] ?? null,
            ':LinkedCourses' => $exercise['LinkedCourses'] ?? null,
            ':Identifier' => $identifier
        ]);

        echo "   ✅ $identifier : " . ($exists ? "UPDATE" : "INSERT") . " réussi\n";
        $fixed++;
    } catch (Exception $e) {
        echo "   ❌ $identifier : ERREUR - " . $e->getMessage() . "\n";
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo sprintf("Total à corriger:     %2d\n", count($failedIdentifiers));
echo sprintf("Corrigés avec succès: %2d\n", $fixed);
echo sprintf("Non trouvés:          %2d\n", $notFound);
echo sprintf("Échecs:               %2d\n", count($failedIdentifiers) - $fixed - $notFound);
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

if ($fixed === count($failedIdentifiers)) {
    echo "🎉 TOUS LES EXERCICES ONT ÉTÉ CORRIGÉS !\n";
    echo "\n";
    echo "📊 Bilan final de l'import:\n";
    echo "   Total exercices:  1245\n";
    echo "   Réussis:          1233 + " . $fixed . " = " . (1233 + $fixed) . "\n";
    echo "   Base complète:    ✅\n";
} else {
    echo "⚠️  Certains exercices n'ont pas pu être corrigés.\n";
    echo "   Vérifiez les messages d'erreur ci-dessus.\n";
}

echo "\n";
