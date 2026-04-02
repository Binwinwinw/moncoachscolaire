<?php
/**
 * dev/tools/exercises/normalize_data.php
 * Normalise les données avant parsing
 */

require_once __DIR__ . '/../../../src/database/connection.php';

echo "🔧 NORMALISATION DES DONNÉES\n";
echo "============================\n\n";

$updates = 0;

// 1. NORMALISER LES MATIÈRES
echo "📚 Normalisation des matières...\n";

$subjectMapping = [
    'mathematiques' => 'Mathématiques',
    'Francais' => 'Français',
    'histoire-geo' => 'Histoire-Géographie',
    'Culture Générale' => 'Culture Générale', // OK avec accent
];

foreach ($subjectMapping as $old => $new) {
    $stmt = $pdo->prepare("
        UPDATE exercises
        SET Subject = :new
        WHERE Subject = :old
    ");
    $stmt->execute([':old' => $old, ':new' => $new]);
    $count = $stmt->rowCount();

    if ($count > 0) {
        echo "   ✅ $old → $new ($count exercices)\n";
        $updates += $count;
    }
}

// 2. NORMALISER LES NIVEAUX
echo "\n🎓 Normalisation des niveaux...\n";

$levelMapping = [
    'bac' => 'Terminale', // Important : on déplacera vers exam_prep après
];

foreach ($levelMapping as $old => $new) {
    $stmt = $pdo->prepare("
        UPDATE exercises
        SET Level = :new
        WHERE Level = :old
    ");
    $stmt->execute([':old' => $old, ':new' => $new]);
    $count = $stmt->rowCount();

    if ($count > 0) {
        echo "   ✅ $old → $new ($count exercices)\n";
        $updates += $count;
    }
}

// 3. MARQUER LES EXERCICES "bac" COMME PRÉPA BAC
echo "\n🎯 Marquage des exercices de préparation BAC...\n";

// Vérifier si la colonne exam_prep existe
$columns = $pdo->query("SHOW COLUMNS FROM exercises LIKE 'exam_prep'")->fetchAll();

if (!empty($columns)) {
    $stmt = $pdo->prepare("
        UPDATE exercises
        SET exam_prep = 'BAC'
        WHERE Level = 'Terminale'
        AND (
            Identifier LIKE '%-BAC-%'
            OR Title LIKE '%BAC%'
            OR Title LIKE '%bac%'
            OR Domain LIKE '%BAC%'
        )
        AND (exam_prep IS NULL OR exam_prep = '')
    ");
    $stmt->execute();
    $count = $stmt->rowCount();
    echo "   ✅ $count exercices marqués comme prépa BAC\n";
    $updates += $count;
} else {
    echo "   ⚠️  Colonne exam_prep n'existe pas encore\n";
    echo "   💡 Exécute d'abord : ALTER TABLE exercises ADD COLUMN exam_prep VARCHAR(20);\n";
}

// 4. RÉSUMÉ
echo "\n📊 RÉSUMÉ\n";
echo "─────────\n";
echo "Total de modifications : $updates\n";

// Afficher nouvelle répartition
echo "\n📖 NOUVELLE RÉPARTITION PAR MATIÈRE\n";
$stmt = $pdo->query("
    SELECT Subject, COUNT(*) as count
    FROM exercises
    GROUP BY Subject
    ORDER BY count DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("   %-30s : %4d exercices\n", $row['Subject'], $row['count']);
}

echo "\n✅ Normalisation terminée !\n";
