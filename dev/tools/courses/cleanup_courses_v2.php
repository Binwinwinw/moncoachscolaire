<?php
/**
 * Nettoyage des doublons et normalisation
 * Étape 1: Supprimer les doublons
 * Étape 2: Normaliser les valeurs
 */

require_once __DIR__ . '/src/database/connection.php';

echo "NETTOYAGE AVANCÉ DES COURS\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Étape 1: Identifier et supprimer les doublons exacts
    echo "Étape 1: Suppression des doublons exacts\n";

    $duplicates = $pdo->query("
        SELECT subject, level, competence, COUNT(*) as count, 
               GROUP_CONCAT(id ORDER BY id) as ids
        FROM courses
        GROUP BY subject, level, competence
        HAVING COUNT(*) > 1
    ")->fetchAll(PDO::FETCH_ASSOC);

    $deletedCount = 0;

    foreach ($duplicates as $dup) {
        $ids = explode(',', $dup['ids']);
        // Garder le premier, supprimer les autres
        $keepId = array_shift($ids);

        if (!empty($ids)) {
            $idsToDelete = implode(',', $ids);

            // Transférer les exercices vers le cours à garder
            $pdo->exec("
                UPDATE exercises 
                SET course_id = $keepId 
                WHERE course_id IN ($idsToDelete)
            ");

            // Supprimer les doublons
            $stmt = $pdo->exec("DELETE FROM courses WHERE id IN ($idsToDelete)");
            $deletedCount += count($ids);

            echo "  ✓ Doublon supprimé: {$dup['subject']} - {$dup['level']} - {$dup['competence']}\n";
        }
    }

    echo "  Total: $deletedCount doublons supprimés\n\n";

    // Étape 2: Désactiver temporairement la contrainte unique
    echo "Étape 2: Normalisation des niveaux\n";

    // Normalisation sans contrainte
    $levelMapping = [
        'terminale' => 'Terminale',
        'premiere' => 'Première',
        'seconde' => 'Seconde',
        '1ere' => 'Première',
        '2nde' => 'Seconde',
        '' => 'Non classé'
    ];

    $levelCount = 0;

    foreach ($levelMapping as $old => $new) {
        // Vérifier si la mise à jour créerait un doublon
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM courses c1
            WHERE c1.level = :old
            AND EXISTS (
                SELECT 1 FROM courses c2 
                WHERE c2.level = :new 
                AND c2.subject = c1.subject 
                AND c2.competence = c1.competence
                AND c2.id != c1.id
            )
        ");
        $stmt->execute(['old' => $old, 'new' => $new]);
        $conflicts = $stmt->fetchColumn();

        if ($conflicts > 0) {
            // Fusionner avec le cours existant
            $pdo->exec("
                UPDATE exercises e
                INNER JOIN courses c_old ON e.course_id = c_old.id
                INNER JOIN courses c_new ON 
                    c_new.subject = c_old.subject 
                    AND c_new.competence = c_old.competence
                    AND c_new.level = '$new'
                SET e.course_id = c_new.id
                WHERE c_old.level = '$old'
            ");

            // Supprimer l'ancien
            $pdo->exec("DELETE FROM courses WHERE level = '$old'");
            echo "  ✓ Fusionné: '$old' → '$new' ($conflicts cours)\n";
        } else {
            // Mise à jour simple
            $stmt = $pdo->prepare("UPDATE courses SET level = :new WHERE level = :old");
            $stmt->execute(['old' => $old, 'new' => $new]);
            $count = $stmt->rowCount();
            if ($count > 0) {
                echo "  ✓ '$old' → '$new': $count cours\n";
                $levelCount += $count;
            }
        }
    }

    echo "\n";

    // Étape 3: Normalisation des matières
    echo "Étape 3: Normalisation des matières\n";

    $subjectMapping = [
        'Math' => 'Mathématiques',
        "Math'ematiques" => 'Mathématiques',
        'Ang' => 'Anglais',
        'Fr' => 'Français',
        '' => 'Non classé'
    ];

    $subjectCount = 0;

    foreach ($subjectMapping as $old => $new) {
        // Même logique de fusion
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM courses c1
            WHERE c1.subject = :old
            AND EXISTS (
                SELECT 1 FROM courses c2 
                WHERE c2.subject = :new 
                AND c2.level = c1.level 
                AND c2.competence = c1.competence
                AND c2.id != c1.id
            )
        ");
        $stmt->execute(['old' => $old, 'new' => $new]);
        $conflicts = $stmt->fetchColumn();

        if ($conflicts > 0) {
            $pdo->exec("
                UPDATE exercises e
                INNER JOIN courses c_old ON e.course_id = c_old.id
                INNER JOIN courses c_new ON 
                    c_new.level = c_old.level 
                    AND c_new.competence = c_old.competence
                    AND c_new.subject = '$new'
                SET e.course_id = c_new.id
                WHERE c_old.subject = '$old'
            ");

            $pdo->exec("DELETE FROM courses WHERE subject = '$old'");
            echo "  ✓ Fusionné: '$old' → '$new' ($conflicts cours)\n";
        } else {
            $stmt = $pdo->prepare("UPDATE courses SET subject = :new WHERE subject = :old");
            $stmt->execute(['old' => $old, 'new' => $new]);
            $count = $stmt->rowCount();
            if ($count > 0) {
                echo "  ✓ '$old' → '$new': $count cours\n";
                $subjectCount += $count;
            }
        }
    }

    echo "\n";

    // Étape 4: Même chose pour exercises
    echo "Étape 4: Normalisation dans exercises\n";

    foreach ($levelMapping as $old => $new) {
        $stmt = $pdo->prepare("UPDATE exercises SET Level = :new WHERE Level = :old");
        $stmt->execute(['old' => $old, 'new' => $new]);
        if ($stmt->rowCount() > 0) {
            echo "  ✓ Niveau exercices '$old' → '$new'\n";
        }
    }

    foreach ($subjectMapping as $old => $new) {
        $stmt = $pdo->prepare("UPDATE exercises SET Subject = :new WHERE Subject = :old");
        $stmt->execute(['old' => $old, 'new' => $new]);
        if ($stmt->rowCount() > 0) {
            echo "  ✓ Matière exercices '$old' → '$new'\n";
        }
    }

    echo "\n";

    // Étape 5: Reliaison
    echo "Étape 5: Reliaison automatique\n";

    $linkSQL = "
    UPDATE exercises e
    INNER JOIN courses c ON 
        UPPER(e.Subject) = UPPER(c.subject) 
        AND UPPER(e.Level) = UPPER(c.level)
        AND (
            UPPER(REPLACE(e.Competence, ' ', '')) = UPPER(REPLACE(c.competence, ' ', ''))
            OR UPPER(e.Competence) LIKE CONCAT('%', UPPER(c.competence), '%')
            OR UPPER(c.competence) LIKE CONCAT('%', UPPER(e.Competence), '%')
        )
    SET e.course_id = c.id
    WHERE e.course_id IS NULL
    ";

    $linked = $pdo->exec($linkSQL);
    echo "✓ $linked exercices supplémentaires liés\n\n";

    // Stats finales
    echo str_repeat("=", 60) . "\n";
    echo "RÉSULTAT FINAL\n";
    echo str_repeat("=", 60) . "\n\n";

    $totalCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn();
    $orphans = $pdo->query("SELECT COUNT(*) FROM exercises WHERE course_id IS NULL AND is_active = 'true'")->fetchColumn();
    $linked_total = $pdo->query("SELECT COUNT(*) FROM exercises WHERE course_id IS NOT NULL AND is_active = 'true'")->fetchColumn();

    echo "Cours actifs: $totalCourses\n";
    echo "Exercices liés: $linked_total\n";
    echo "Exercices orphelins: $orphans\n\n";

    echo "✅ Nettoyage terminé!\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
