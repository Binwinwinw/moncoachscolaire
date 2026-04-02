<?php
/**
 * Script final de correction : 
 * 1. Corriger les exercices Terminale sources (#152, #145, #495)
 * 2. Supprimer TOUS les exercices BAC migrés
 * 3. Refaire une migration propre
 */

require_once __DIR__ . '/../db/connection.php';

echo "=== CORRECTION FINALE DES EXERCICES ===" . PHP_EOL . PHP_EOL;

// ÉTAPE 1: Corriger les exercices Terminale sources
echo "ÉTAPE 1 : Correction des exercices Terminale sources" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$corrections = [
    ['id' => 152, 'old_subject' => 'Anglais', 'new_subject' => 'Sciences', 'title' => 'Chimie organique'],
    ['id' => 145, 'old_subject' => 'Français', 'new_subject' => 'Mathématiques', 'title' => 'Probabilités'],
    ['id' => 495, 'old_subject' => 'Philosophie', 'new_subject' => 'Mathématiques', 'title' => 'Nombres complexes'],
];

$terminale_corrected = 0;
foreach ($corrections as $correction) {
    try {
        $stmt = $pdo->prepare('UPDATE exercises SET Subject = ? WHERE Id = ? AND Level = "Terminale"');
        $stmt->execute([$correction['new_subject'], $correction['id']]);
        
        if ($stmt->rowCount() > 0) {
            echo sprintf("✓ #%d : [%s → %s] %s\n", 
                $correction['id'], 
                $correction['old_subject'], 
                $correction['new_subject'],
                $correction['title']
            );
            $terminale_corrected++;
        } else {
            echo sprintf("⚠️  #%d : Exercice non trouvé ou déjà corrigé\n", $correction['id']);
        }
    } catch (PDOException $e) {
        echo sprintf("✗ #%d : Erreur - %s\n", $correction['id'], $e->getMessage());
    }
}

echo sprintf("\nExercices Terminale corrigés: %d\n", $terminale_corrected);

// ÉTAPE 2: Supprimer TOUS les exercices BAC migrés
echo "\n" . str_repeat("=", 60) . "\n";
echo "ÉTAPE 2 : Suppression de tous les exercices BAC migrés" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$stmt = $pdo->query('SELECT COUNT(*) FROM exercises WHERE Level = "BAC" AND Title LIKE "%(BAC)%"');
$count_to_delete = $stmt->fetchColumn();

echo sprintf("Exercices BAC à supprimer: %d\n", $count_to_delete);

if ($count_to_delete > 0) {
    $stmt = $pdo->prepare('DELETE FROM exercises WHERE Level = "BAC" AND Title LIKE "%(BAC)%"');
    $stmt->execute();
    echo sprintf("✓ Supprimés: %d exercices\n", $stmt->rowCount());
}

// ÉTAPE 3: Re-migration propre
echo "\n" . str_repeat("=", 60) . "\n";
echo "ÉTAPE 3 : Re-migration propre Terminale → BAC" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$migrations = [
    'Anglais' => 10,
    'Sciences' => 6,  // +1 car on a récupéré l'exercice de chimie
    'Mathématiques' => 10,  // +2 car on a récupéré probabilités et nombres complexes
    'Français' => 4,  // -1 car on a perdu probabilités
    'Histoire-Géographie' => 4,
    'Philosophie' => 2  // -1 car on a perdu nombres complexes
];

$total_migrated = 0;

foreach ($migrations as $subject => $target_count) {
    // Compter les exercices BAC existants NON migrés (originaux)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM exercises WHERE Level = "BAC" AND Subject = ? AND Title NOT LIKE "%(BAC)%"');
    $stmt->execute([$subject]);
    $current_count = $stmt->fetchColumn();
    
    $needed = $target_count - $current_count;
    if ($needed <= 0) {
        echo sprintf("%-25s: %d/%d (OK, rien à migrer)\n", $subject, $current_count, $target_count);
        continue;
    }
    
    // Variantes pour Histoire-Géo
    $subject_variants = [$subject];
    if ($subject === 'Histoire-Géographie') {
        $subject_variants[] = 'Histoire-Géo';
    }
    
    $source_exercises = [];
    foreach ($subject_variants as $variant) {
        $stmt = $pdo->prepare('
            SELECT * FROM exercises 
            WHERE Level = "Terminale" 
            AND Subject = ?
        ');
        $stmt->execute([$variant]);
        $all_exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $source_exercises = array_slice($all_exercises, 0, $needed);
        
        if (!empty($source_exercises)) {
            break;
        }
    }
    
    if (empty($source_exercises)) {
        echo sprintf("%-25s: ⚠️  Aucun exercice Terminale trouvé\n", $subject);
        continue;
    }
    
    // Migrer
    $migrated = 0;
    foreach ($source_exercises as $exercise) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO exercises (Title, Content, Subject, Level, Answer)
                VALUES (?, ?, ?, "BAC", ?)
            ');
            
            $stmt->execute([
                $exercise['Title'] . ' (BAC)',
                $exercise['Content'],
                $subject,  // Utiliser le nom unifié
                $exercise['Answer'] ?? ''
            ]);
            
            $migrated++;
        } catch (PDOException $e) {
            echo sprintf("  ✗ Erreur: %s - %s\n", $exercise['Title'], $e->getMessage());
        }
    }
    
    $total_migrated += $migrated;
    echo sprintf("%-25s: ✓ Migrés %d/%d exercices\n", $subject, $migrated, $needed);
}

// RÉSUMÉ
echo "\n" . str_repeat("=", 60) . "\n";
echo "RÉSUMÉ FINAL" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

echo sprintf("Exercices Terminale corrigés: %d\n", $terminale_corrected);
echo sprintf("Exercices BAC supprimés: %d\n", $count_to_delete);
echo sprintf("Exercices BAC re-migrés: %d\n", $total_migrated);

echo "\n=== État final par matière (BAC) ===" . PHP_EOL;
$stmt = $pdo->query('SELECT Subject, COUNT(*) as nb FROM exercises WHERE Level = "BAC" GROUP BY Subject ORDER BY Subject');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    printf("  %-25s: %2d exercices\n", $row['Subject'], $row['nb']);
}

$total = $pdo->query('SELECT COUNT(*) FROM exercises WHERE Level = "BAC"')->fetchColumn();
echo sprintf("\nTotal exercices BAC: %d\n", $total);

echo "\n✅ CORRECTION FINALE TERMINÉE\n";
