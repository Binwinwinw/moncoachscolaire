<?php
/**
 * Script de normalisation des niveaux d'exercices
 * Unifie: "Bac" + "BAC" → "Terminale"
 * Unifie: "Histoire-Géo" → "Histoire-Géographie"
 * 
 * Usage: php tools/normalize_exercise_levels.php [--dry-run]
 */

require_once __DIR__ . '/../db/connection.php';

// Debugging
if (!isset($pdo) || $pdo === null) {
    require_once __DIR__ . '/../src/database/connection.php';
}

$dryRun = in_array('--dry-run', $argv);

echo "\n╔════════════════════════════════════════════════════════╗\n";
echo "║  🔧 NORMALISATION DES NIVEAUX D'EXERCICES             ║\n";
echo "╚════════════════════════════════════════════════════════╝\n\n";

// 1. État avant
echo "📊 ÉTAT AVANT NORMALISATION\n";
echo "──────────────────────────\n";

$result = $pdo->query("SELECT DISTINCT Level FROM Exercises ORDER BY Level");
$levels = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Niveaux uniques trouvés:\n";
foreach ($levels as $row) {
    $countResult = $pdo->prepare("SELECT COUNT(*) as count FROM Exercises WHERE Level = ?");
    $countResult->execute([$row['Level']]);
    $count = $countResult->fetch()['count'];
    echo "  • {$row['Level']}: $count exercices\n";
}

$result = $pdo->query("SELECT DISTINCT Subject FROM Exercises ORDER BY Subject");
$subjects = $result->fetchAll(PDO::FETCH_ASSOC);
echo "\nMatières uniques trouvées:\n";
foreach ($subjects as $row) {
    $countResult = $pdo->prepare("SELECT COUNT(*) as count FROM Exercises WHERE Subject = ?");
    $countResult->execute([$row['Subject']]);
    $count = $countResult->fetch()['count'];
    echo "  • {$row['Subject']}: $count exercices\n";
}

// 2. Définir les normalisations
$levelNormalizations = [
    'Bac' => 'Terminale',
    'BAC' => 'Terminale',
    'bac' => 'Terminale'
];

$subjectNormalizations = [
    'Histoire-Géo' => 'Histoire-Géographie'
];

// 3. Mode DRY-RUN
if ($dryRun) {
    echo "\n\n🏁 MODE DRY-RUN - AFFICHAGE DES CHANGEMENTS\n";
    echo "───────────────────────────────────────────\n";
    
    $changes = 0;
    foreach ($levelNormalizations as $old => $new) {
        $countResult = $pdo->prepare("SELECT COUNT(*) as count FROM Exercises WHERE Level = ?");
        $countResult->execute([$old]);
        $count = $countResult->fetch()['count'];
        if ($count > 0) {
            echo "✓ Remplacer \"$old\" → \"$new\": $count exercices\n";
            $changes += $count;
        }
    }
    
    foreach ($subjectNormalizations as $old => $new) {
        $countResult = $pdo->prepare("SELECT COUNT(*) as count FROM Exercises WHERE Subject = ?");
        $countResult->execute([$old]);
        $count = $countResult->fetch()['count'];
        if ($count > 0) {
            echo "✓ Remplacer \"$old\" → \"$new\": $count exercices\n";
            $changes += $count;
        }
    }
    
    echo "\nTotal changements: $changes\n";
    exit(0);
}

// 4. Appliquer les changements
echo "\n\n⚙️  APPLICATION DES NORMALISATIONS\n";
echo "─────────────────────────────────\n";

$totalChanges = 0;

// Niveaux
foreach ($levelNormalizations as $old => $new) {
    $stmt = $pdo->prepare("UPDATE Exercises SET Level = ? WHERE Level = ?");
    $stmt->execute([$new, $old]);
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "✅ Niveau: \"$old\" → \"$new\": $affected exercices modifiés\n";
        $totalChanges += $affected;
    }
}

// Matières
foreach ($subjectNormalizations as $old => $new) {
    $stmt = $pdo->prepare("UPDATE Exercises SET Subject = ? WHERE Subject = ?");
    $stmt->execute([$new, $old]);
    $affected = $stmt->rowCount();
    if ($affected > 0) {
        echo "✅ Matière: \"$old\" → \"$new\": $affected exercices modifiés\n";
        $totalChanges += $affected;
    }
}

// 5. État après
echo "\n\n📊 ÉTAT APRÈS NORMALISATION\n";
echo "──────────────────────────\n";

$result = $pdo->query("SELECT DISTINCT Level FROM Exercises ORDER BY FIELD(Level, '6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale')");
$levels = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Niveaux après normalisation:\n";
foreach ($levels as $row) {
    $countResult = $pdo->prepare("SELECT COUNT(*) as count FROM Exercises WHERE Level = ?");
    $countResult->execute([$row['Level']]);
    $count = $countResult->fetch()['count'];
    echo "  • {$row['Level']}: $count exercices\n";
}

// Vérifier qu'il n'y a plus de doublons
$result = $pdo->query("SELECT COUNT(*) as count FROM Exercises");
$totalCount = $result->fetch()['count'];
echo "\nTotal exercices: $totalCount\n";

echo "\n✅ NORMALISATION TERMINÉE\n";
echo "──────────────────────────\n";
echo "Changements appliqués: $totalChanges\n";

echo "\n";
?>
