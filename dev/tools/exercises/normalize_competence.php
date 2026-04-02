<?php
/**
 * dev/tools/exercises/normalize_competence.php
 * Normalise le champ Competence selon l'Identifier
 */

require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/database/connection.php';

echo "🔧 Normalisation du champ Competence\n";
echo "====================================\n\n";

// Fonctions de normalisation
function normalizeCompetence($competence) {
    // Remplacer underscores par espaces
    $competence = str_replace('_', ' ', $competence);
    // Capitaliser proprement
    return ucwords(strtolower($competence));
}

try {
    // Récupérer tous les exercices
    $stmt = $pdo->query("SELECT ExerciseID, Identifier, Competence FROM exercises WHERE is_active = 1");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ " . count($exercises) . " exercices trouvés\n\n";

    $updated = 0;
    $skipped = 0;
    $errors = [];

    foreach ($exercises as $exercise) {
        // Parser l'Identifier
        $parts = explode('-', strtoupper($exercise['Identifier']));

        if (count($parts) < 4) {
            $errors[] = "⚠️  Format invalide : {$exercise['Identifier']} (ID: {$exercise['ExerciseID']})";
            $skipped++;
            continue;
        }

        // Extraire la compétence (3ème segment)
        $newCompetence = normalizeCompetence($parts[2]);
        $oldCompetence = $exercise['Competence'];

        // Comparer avec l'ancien
        if ($oldCompetence === $newCompetence) {
            $skipped++;
            continue; // Déjà correct
        }

        // Mettre à jour
        $updateStmt = $pdo->prepare("UPDATE exercises SET Competence = :competence WHERE ExerciseID = :id");
        $updateStmt->execute([
            ':competence' => $newCompetence,
            ':id' => $exercise['ExerciseID']
        ]);

        $updated++;
        echo "✅ Ex #{$exercise['ExerciseID']} : \"{$oldCompetence}\" → \"{$newCompetence}\"\n";
    }

    echo "\n📊 RÉSULTATS\n";
    echo "============\n";
    echo "✅ Mis à jour : $updated\n";
    echo "⏭️  Ignorés (déjà corrects) : $skipped\n";
    echo "❌ Erreurs : " . count($errors) . "\n";

    if (!empty($errors)) {
        echo "\n🔴 ERREURS :\n";
        foreach ($errors as $error) {
            echo "$error\n";
        }
    }

    echo "\n✅ Normalisation terminée !\n";

} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
