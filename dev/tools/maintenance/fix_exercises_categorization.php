<?php
/**
 * Script de correction automatique des exercices mal catégorisés
 *
 * 1. Détecte les exercices avec un titre incohérent avec la matière
 * 2. Corrige la matière (Subject) pour Terminale et BAC
 * 3. Supprime les exercices BAC mal migrés
 * 4. Refait une migration propre
 */

require_once __DIR__ . '/../db/connection.php';

echo "=== CORRECTION AUTOMATIQUE DES EXERCICES ===" . PHP_EOL . PHP_EOL;

// Définir les règles de détection et correction
$corrections = [
    [
        'id' => 918,
        'wrong_subject' => 'Anglais',
        'correct_subject' => 'Sciences',
        'title_pattern' => 'Chimie organique',
        'reason' => 'Titre contient "Chimie organique"'
    ],
    [
        'id' => 933,
        'wrong_subject' => 'Français',
        'correct_subject' => 'Mathématiques',
        'title_pattern' => 'Probabilités',
        'reason' => 'Titre contient "Probabilités"'
    ],
    [
        'id' => 939,
        'wrong_subject' => 'Philosophie',
        'correct_subject' => 'Mathématiques',
        'title_pattern' => 'Nombres complexes',
        'reason' => 'Titre contient "Nombres complexes"'
    ]
];

echo "ÉTAPE 1 : Vérification des exercices à corriger" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

$toCorrect = [];
if (!$pdo) {
    echo "❌ Connexion PDO non initialisée, arrêt du script.\n";
    exit(1);
}
foreach ($corrections as $correction) {
    $stmt = $pdo->prepare('SELECT Id, Title, Subject, Level FROM exercises WHERE Id = ?');
    $stmt->execute([$correction['id']]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exercise) {
        echo sprintf("✓ Trouvé #%d [%s → %s]: %s\n",
            $exercise['Id'],
            $exercise['Subject'],
            $correction['correct_subject'],
            $exercise['Title']
        );
        echo sprintf("  Raison: %s\n", $correction['reason']);
        $toCorrect[] = [
            'exercise' => $exercise,
            'new_subject' => $correction['correct_subject']
        ];
    } else {
        echo sprintf("⚠️  Exercice #%d non trouvé\n", $correction['id']);
    }
}

if (empty($toCorrect)) {
    echo "\nℹ️  Les exercices sources sont déjà corrigés ou n'existent plus.\n";
    $corrected = 0; // Pas de correction nécessaire
} else {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "ÉTAPE 2 : Correction des exercices Terminale (sources)" . PHP_EOL;
    echo str_repeat("-", 60) . PHP_EOL;

    $corrected = 0;
    foreach ($toCorrect as $item) {
        $id = $item['exercise']['Id'];
        $newSubject = $item['new_subject'];

        if ($pdo) {
            try {
                $stmt = $pdo->prepare('UPDATE exercises SET Subject = ? WHERE Id = ?');
                $stmt->execute([$newSubject, $id]);
                echo sprintf("✓ Corrigé #%d : Subject = %s\n", $id, $newSubject);
                $corrected++;
            } catch (PDOException $e) {
                echo sprintf("✗ Erreur #%d : %s\n", $id, $e->getMessage());
            }
        } else {
            echo sprintf("❌ PDO non initialisé pour correction #%d\n", $id);
        }
    }

    echo sprintf("\nExercices Terminale corrigés: %d/%d\n", $corrected, count($toCorrect));
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "ÉTAPE 2 : Correction des exercices BAC mal catégorisés" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

// Supprimer tous les exercices BAC qui ont été migrés (avec "(BAC)" dans le titre)

if ($pdo) {
    $stmt = $pdo->query('SELECT COUNT(*) FROM exercises WHERE Level = "BAC" AND Title LIKE "%(BAC)%"');
    $countBefore = $stmt->fetchColumn();
} else {
    echo "❌ PDO non initialisé pour comptage exercices BAC à supprimer\n";
    $countBefore = 0;
}

echo sprintf("Exercices BAC à supprimer: %d\n", $countBefore);

if ($countBefore > 0 && $pdo) {
    $stmt = $pdo->prepare('DELETE FROM exercises WHERE Level = "BAC" AND Title LIKE "%(BAC)%"');
    $stmt->execute();
    echo sprintf("✓ Supprimés: %d exercices\n", $stmt->rowCount());
} else if (!$pdo) {
    echo "❌ PDO non initialisé pour suppression exercices BAC\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "ÉTAPE 4 : Re-migration propre Terminale → BAC" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;\n" . str_repeat("=", 60) . "\n";
echo "ÉTAPE 2 : Correction des exercices BAC mal catégorisés" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

// Définir les patterns de détection automatique
$autoCorrections = [
    ['pattern' => '%chimie%', 'wrong_subjects' => ['Anglais', 'Français', 'Philosophie'], 'correct_subject' => 'Sciences'],
    ['pattern' => '%organique%', 'wrong_subjects' => ['Anglais', 'Français', 'Philosophie'], 'correct_subject' => 'Sciences'],
    ['pattern' => '%probabilité%', 'wrong_subjects' => ['Anglais', 'Français', 'Philosophie', 'Sciences'], 'correct_subject' => 'Mathématiques'],
    ['pattern' => '%nombre%complexe%', 'wrong_subjects' => ['Anglais', 'Français', 'Philosophie', 'Sciences'], 'correct_subject' => 'Mathématiques'],
    ['pattern' => '%intégrale%', 'wrong_subjects' => ['Anglais', 'Français', 'Philosophie', 'Sciences'], 'correct_subject' => 'Mathématiques'],
    ['pattern' => '%géométrie%', 'wrong_subjects' => ['Anglais', 'Français', 'Philosophie', 'Sciences'], 'correct_subject' => 'Mathématiques'],
];

$bacCorrected = 0;
if ($pdo) {
    foreach ($autoCorrections as $rule) {
        foreach ($rule['wrong_subjects'] as $wrongSubject) {
            $stmt = $pdo->prepare('
                SELECT Id, Title, Subject
                FROM exercises
                WHERE Level = "BAC"
                AND Subject = ?
                AND Title LIKE ?
            ');
            $stmt->execute([$wrongSubject, $rule['pattern']]);

            while ($exercise = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $updateStmt = $pdo->prepare('UPDATE exercises SET Subject = ? WHERE Id = ?');
                $updateStmt->execute([$rule['correct_subject'], $exercise['Id']]);
                echo sprintf("✓ Corrigé BAC #%d [%s → %s]: %s\n",
                    $exercise['Id'],
                    $wrongSubject,
                    $rule['correct_subject'],
                    $exercise['Title']
                );
                $bacCorrected++;
            }
        }
    }
} else {
    echo "❌ PDO non initialisé pour correction automatique BAC\n";
}

if ($bacCorrected === 0) {
    echo "Aucune correction BAC nécessaire\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "ÉTAPE 3 : Suppression des exercices BAC pour re-migration" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

// Supprimer tous les exercices BAC qui ont été migrés (avec "(BAC)" dans le titre)
echo str_repeat("-", 60) . PHP_EOL;

// Re-migration avec les exercices corrigés
$migrations = [
    'Anglais' => 10,
    'Sciences' => 5,
    'Mathématiques' => 8,  // Augmenté car on a récupéré 2 exercices
    'Français' => 5,
    'Histoire-Géographie' => 4,
    'Philosophie' => 3  // Réduit car on a perdu 1 exercice vers Maths
];

$totalMigrated = 0;

foreach ($migrations as $subject => $targetCount) {
    if (!$pdo) {
        echo "❌ PDO non initialisé pour migration exercices BAC\n";
        continue;
    }
    // Compter les exercices BAC existants (non migrés)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM exercises WHERE Level = "BAC" AND Subject = ? AND Title NOT LIKE "%(BAC)%"');
    $stmt->execute([$subject]);
    $currentCount = $stmt->fetchColumn();

    $needed = $targetCount - $currentCount;
    if ($needed <= 0) {
        echo sprintf("%-25s: %d/%d (OK, rien à migrer)\n", $subject, $currentCount, $targetCount);
        continue;
    }

    // Variantes pour Histoire-Géo
    $subjectVariants = [$subject];
    if ($subject === 'Histoire-Géographie') {
        $subjectVariants[] = 'Histoire-Géo';
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
            break;
        }
    }

    if (empty($sourceExercises)) {
        echo sprintf("%-25s: ⚠️  Aucun exercice Terminale trouvé\n", $subject);
        continue;
    }

    // Migrer
    $migrated = 0;
    foreach ($sourceExercises as $exercise) {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare('
                    INSERT INTO exercises (Title, Content, Subject, Level, Answer)
                    VALUES (?, ?, ?, "BAC", ?)
                ');

                $stmt->execute([
                    $exercise['Title'] . ' (BAC)',
                    $exercise['Content'],
                    $subject,
                    $exercise['Answer'] ?? ''
                ]);

                $migrated++;
            } catch (PDOException $e) {
                echo sprintf("  ✗ Erreur: %s - %s\n", $exercise['Title'], $e->getMessage());
            }
        } else {
            echo "❌ PDO non initialisé pour migration exercice BAC : " . $exercise['Title'] . "\n";
        }
    }

    $totalMigrated += $migrated;
    echo sprintf("%-25s: ✓ Migrés %d/%d exercices\n", $subject, $migrated, $needed);
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "RÉSUMÉ FINAL" . PHP_EOL;
echo str_repeat("-", 60) . PHP_EOL;

echo sprintf("Exercices Terminale corrigés: %d\n", $corrected);
echo sprintf("Exercices BAC corrigés: %d\n", $bacCorrected);
echo sprintf("Exercices BAC supprimés: %d\n", $countBefore);
echo sprintf("Exercices BAC re-migrés: %d\n", $totalMigrated);

echo "\n=== État final par matière (BAC) ===" . PHP_EOL;
if ($pdo) {
    $stmt = $pdo->query('SELECT Subject, COUNT(*) as nb FROM exercises WHERE Level = "BAC" GROUP BY Subject ORDER BY Subject');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("  %-25s: %2d exercices\n", $row['Subject'], $row['nb']);
    }

    $total = $pdo->query('SELECT COUNT(*) FROM exercises WHERE Level = "BAC"')->fetchColumn();
    echo sprintf("\nTotal exercices BAC: %d\n", $total);
} else {
    echo "❌ PDO non initialisé pour état final par matière (BAC)\n";
}

echo "\n✅ CORRECTION TERMINÉE\n";
