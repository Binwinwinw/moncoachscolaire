<?php
/**
 * Script de migration des exercices Terminale vers BAC
 * 
 * Ce script duplique les exercices de Terminale vers le niveau BAC
 * pour les matières manquantes ou sous-représentées
 */

require_once __DIR__ . '/../db/connection.php';

echo "=== Migration des exercices Terminale → BAC ===" . PHP_EOL . PHP_EOL;

// Matières à migrer et nombre d'exercices souhaités
$migrations = [
    'Anglais' => 10,        // 0 → 10 exercices
    'Sciences' => 5,        // 0 → 5 exercices
    'Mathématiques' => 3,   // 5 → 8 exercices (compléter)
    'Français' => 5,        // 3 → 8 exercices (compléter)
    'Histoire-Géographie' => 4,  // 4 → 8 exercices (compléter, noter le nom exact dans Terminale)
    'Philosophie' => 5      // 3 → 8 exercices (compléter)
];

$totalMigrated = 0;
$errors = [];

foreach ($migrations as $subject => $targetCount) {
    echo "📚 Matière: $subject" . PHP_EOL;
    
    // Compter les exercices BAC existants pour cette matière
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM exercises WHERE Level = "BAC" AND Subject = ?');
    $stmt->execute([$subject]);
    $currentCount = $stmt->fetchColumn();
    
    echo "  Exercices BAC actuels: $currentCount" . PHP_EOL;
    
    $needed = $targetCount - $currentCount;
    if ($needed <= 0) {
        echo "  ✅ Objectif atteint, rien à migrer" . PHP_EOL . PHP_EOL;
        continue;
    }
    
    echo "  🎯 Besoin de migrer: $needed exercices" . PHP_EOL;
    
    // Chercher des exercices Terminale pour cette matière
    // Variante pour Histoire-Géo : essayer avec et sans tiret
    $subjectVariants = [$subject];
    if ($subject === 'Histoire-Géographie') {
        $subjectVariants[] = 'Histoire-Géo';
    } elseif ($subject === 'Histoire-Géo') {
        $subjectVariants[] = 'Histoire-Géographie';
    }
    
    $sourceExercises = [];
    foreach ($subjectVariants as $variant) {
        $stmt = $pdo->prepare('
            SELECT * FROM exercises 
            WHERE Level = "Terminale" 
            AND Subject = ?
            AND Id NOT IN (
                SELECT Id FROM exercises WHERE Level = "BAC" AND Subject = ?
            )
        ');
        $stmt->execute([$variant, $subject]);
        $allExercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sourceExercises = array_slice($allExercises, 0, $needed);
        
        if (!empty($sourceExercises)) {
            echo "  📖 Trouvé " . count($sourceExercises) . " exercices Terminale (matière: $variant)" . PHP_EOL;
            break;
        }
    }
    
    if (empty($sourceExercises)) {
        $msg = "  ⚠️  Aucun exercice Terminale trouvé pour $subject";
        echo $msg . PHP_EOL . PHP_EOL;
        $errors[] = $msg;
        continue;
    }
    
    // Dupliquer chaque exercice vers BAC
    $migrated = 0;
    foreach ($sourceExercises as $exercise) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO exercises (
                    Title, Content, Subject, Level, Answer
                ) VALUES (
                    ?, ?, ?, "BAC", ?
                )
            ');
            
            $stmt->execute([
                $exercise['Title'] . ' (BAC)',  // Ajouter (BAC) au titre
                $exercise['Content'],
                $subject,  // Utiliser le nom unifié
                $exercise['Answer'] ?? ''
            ]);
            
            $migrated++;
            echo "    ✓ Migré: " . $exercise['Title'] . PHP_EOL;
            
        } catch (PDOException $e) {
            $msg = "    ✗ Erreur: " . $exercise['Title'] . " - " . $e->getMessage();
            echo $msg . PHP_EOL;
            $errors[] = $msg;
        }
    }
    
    $totalMigrated += $migrated;
    echo "  ✅ Migrés: $migrated/$needed exercices" . PHP_EOL . PHP_EOL;
}

echo PHP_EOL . "=== RÉSUMÉ ===" . PHP_EOL;
echo "Total exercices migrés: $totalMigrated" . PHP_EOL;

if (!empty($errors)) {
    echo PHP_EOL . "⚠️  Erreurs rencontrées:" . PHP_EOL;
    foreach ($errors as $error) {
        echo "  - $error" . PHP_EOL;
    }
}

// Afficher le résultat final
echo PHP_EOL . "=== État final ===" . PHP_EOL;
$stmt = $pdo->query('SELECT Subject, COUNT(*) as nb FROM exercises WHERE Level = "BAC" GROUP BY Subject ORDER BY Subject');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  " . $row['Subject'] . ": " . $row['nb'] . " exercices" . PHP_EOL;
}

$total = $pdo->query('SELECT COUNT(*) FROM exercises WHERE Level = "BAC"')->fetchColumn();
echo PHP_EOL . "Total exercices BAC: $total" . PHP_EOL;
