<?php
/**
 * dev/tools/exercises/count_exercises.php
 * Compte et analyse les exercices existants
 */

require_once __DIR__ . '/../../../src/database/connection.php';

echo "📊 ANALYSE DE LA BASE D'EXERCICES\n";
echo "==================================\n\n";

try {
    // Total général
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM exercises");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo "📚 TOTAL D'EXERCICES : $total\n\n";

    // Par matière
    echo "📖 RÉPARTITION PAR MATIÈRE\n";
    echo "─────────────────────────────\n";
    $stmt = $pdo->query("
        SELECT Subject, COUNT(*) as count
        FROM exercises
        WHERE Subject IS NOT NULL
        GROUP BY Subject
        ORDER BY count DESC
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-25s : %4d exercices\n", $row['Subject'], $row['count']);
    }

    // Par niveau
    echo "\n🎓 RÉPARTITION PAR NIVEAU\n";
    echo "─────────────────────────────\n";
    $stmt = $pdo->query("
        SELECT Level, COUNT(*) as count
        FROM exercises
        WHERE Level IS NOT NULL
        GROUP BY Level
        ORDER BY count DESC
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-25s : %4d exercices\n", $row['Level'], $row['count']);
    }

    // Déjà parsés ?
    echo "\n🔍 ÉTAT DU PARSING\n";
    echo "─────────────────────────────\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exercises WHERE processed = 1");
    $parsed = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $notParsed = $total - $parsed;

    printf("✅ Déjà parsés      : %4d (%.1f%%)\n", $parsed, ($total > 0 ? ($parsed/$total)*100 : 0));
    printf("⏳ Reste à parser   : %4d (%.1f%%)\n", $notParsed, ($total > 0 ? ($notParsed/$total)*100 : 0));

    // Identifier manquants
    echo "\n🏷️  IDENTIFIERS\n";
    echo "─────────────────────────────\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exercises WHERE Identifier IS NULL OR Identifier = ''");
    $noIdentifier = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    printf("✅ Avec identifier  : %4d\n", $total - $noIdentifier);
    printf("❌ Sans identifier  : %4d\n", $noIdentifier);

    // Content vide
    echo "\n📝 CONTENU\n";
    echo "─────────────────────────────\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM exercises WHERE Content IS NULL OR Content = ''");
    $noContent = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    printf("✅ Avec contenu     : %4d\n", $total - $noContent);
    printf("❌ Sans contenu     : %4d\n", $noContent);

    // Structures détectées (si colonnes existent)
    echo "\n📋 STRUCTURES (si déjà parsé)\n";
    echo "─────────────────────────────\n";

    $columns = $pdo->query("SHOW COLUMNS FROM exercises LIKE 'structure_type'")->fetchAll();

    if (!empty($columns)) {
        $stmt = $pdo->query("
            SELECT structure_type, COUNT(*) as count
            FROM exercises
            WHERE structure_type IS NOT NULL
            GROUP BY structure_type
        ");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            printf("%-25s : %4d exercices\n", $row['structure_type'], $row['count']);
        }
    } else {
        echo "⚠️  Colonnes de parsing pas encore ajoutées\n";
    }

    // Estimation temps de parsing
    echo "\n⏱️  ESTIMATION TEMPS DE PARSING\n";
    echo "─────────────────────────────────────\n";
    $tempsParExercice = 0.05; // 50ms par exercice
    $tempsTotal = ($notParsed * $tempsParExercice) / 60;

    printf("À raison de %.0fms par exercice\n", $tempsParExercice * 1000);
    printf("Temps estimé : %.1f minute(s)\n", $tempsTotal);

} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}

echo "\n✅ Analyse terminée !\n";
