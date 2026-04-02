<?php
/**
 * Import des 20 cours de test dans la table courses existante
 * Lie automatiquement les exercices aux cours
 */

require_once __DIR__ . '/src/database/connection.php';

try {
    $pdo = getDBConnection();

    echo "IMPORT DES COURS DANS LA TABLE EXISTANTE\n";
    echo str_repeat("=", 60) . "\n\n";

    // Étape 1: Charger les cours depuis le JSON
    echo "Étape 1: Chargement du fichier JSON\n";

    $jsonFile = __DIR__ . '/../../../dev/data/test_20_courses.json';
    if (!file_exists($jsonFile)) {
        die("❌ Fichier introuvable: $jsonFile\n");
    }

    $jsonData = json_decode(file_get_contents($jsonFile), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("❌ Erreur JSON: " . json_last_error_msg() . "\n");
    }

    $courses = $jsonData['courses'] ?? [];
    echo "✓ " . count($courses) . " cours chargés\n\n";

    // Étape 2: Insérer les cours
    echo "Étape 2: Insertion des cours\n";

    $insertSQL = "
    INSERT INTO courses (
        level, subject, competence, domain, section,
        key_point, explanation, example, formula,
        slug, is_active
    ) VALUES (
        :level, :subject, :competence, :domain, :section,
        :key_point, :explanation, :example, :formula,
        :slug, 1
    )
    ";

    $stmt = $pdo->prepare($insertSQL);

    $inserted = 0;
    $errors = 0;

    foreach ($courses as $course) {
        // Générer un slug unique
        $slug = strtolower(
            $course['level'] . '-' .
            $course['subject'] . '-' .
            preg_replace('/[^a-z0-9]+/', '-', strtolower($course['competence']))
        );

        try {
            $stmt->execute([
                ':level' => $course['level'],
                ':subject' => $course['subject'],
                ':competence' => $course['competence'],
                ':domain' => $course['domain'] ?? null,
                ':section' => $course['section'],
                ':key_point' => $course['key_point'],
                ':explanation' => $course['explanation'],
                ':example' => $course['example'],
                ':formula' => $course['formula'] ?? null,
                ':slug' => $slug
            ]);

            $inserted++;
            echo "  ✓ Inséré: {$course['level']} - {$course['competence']}\n";

        } catch (PDOException $e) {
            $errors++;
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "  ⚠️  Déjà existant: {$course['level']} - {$course['competence']}\n";
            } else {
                echo "  ❌ Erreur: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n✓ Insertion terminée: $inserted nouveaux, $errors doublons/erreurs\n\n";

    // Étape 3: Vérifier si la colonne course_id existe dans exercises
    echo "Étape 3: Vérification de la liaison exercises ↔ courses\n";

    $columns = $pdo->query("SHOW COLUMNS FROM exercises LIKE 'course_id'")->fetchAll();

    if (empty($columns)) {
        echo "Ajout de la colonne course_id...\n";
        $pdo->exec("ALTER TABLE exercises ADD COLUMN course_id INT NULL");
        $pdo->exec("ALTER TABLE exercises ADD INDEX idx_course_id (course_id)");
        $pdo->exec("ALTER TABLE exercises ADD FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL");
        echo "✓ Colonne course_id ajoutée\n";
    } else {
        echo "✓ Colonne course_id existe déjà\n";
    }

    echo "\n";

    // Étape 4: Lier automatiquement les exercices aux cours
    echo "Étape 4: Liaison automatique exercices ↔ cours\n";

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
    echo "✓ $linked exercices liés automatiquement aux cours\n\n";

    // Étape 5: Statistiques finales
    echo str_repeat("=", 60) . "\n";
    echo "STATISTIQUES FINALES\n";
    echo str_repeat("=", 60) . "\n\n";

    // Nombre total de cours
    $totalCourses = $pdo->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetchColumn();
    echo "Total cours actifs: $totalCourses\n\n";

    // Statistiques par niveau
    $stats = $pdo->query("
        SELECT
            c.level,
            c.subject,
            COUNT(DISTINCT c.id) as nb_courses,
            COUNT(e.id) as nb_exercises
        FROM courses c
        LEFT JOIN exercises e ON e.course_id = c.id AND e.is_active = 'true'
        WHERE c.is_active = 1
        GROUP BY c.level, c.subject
        ORDER BY
            FIELD(c.level, '6eme', '5eme', '4eme', '3eme', 'Seconde', 'Première', 'Terminale'),
            c.subject
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stats as $stat) {
        echo sprintf(
            "%-12s | %-15s | %2d cours | %3d exercices\n",
            $stat['level'],
            $stat['subject'],
            $stat['nb_courses'],
            $stat['nb_exercises']
        );
    }

    // Exercices sans cours
    $orphans = $pdo->query("
        SELECT COUNT(*) FROM exercises
        WHERE course_id IS NULL AND is_active = 'true'
    ")->fetchColumn();

    echo "\n⚠️  Exercices sans cours lié: $orphans\n";

    if ($orphans > 0) {
        echo "\nExemples d'exercices orphelins:\n";
        $examples = $pdo->query("
            SELECT Subject, Level, Competence
            FROM exercises
            WHERE course_id IS NULL AND is_active = 'true'
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($examples as $ex) {
            echo "  - {$ex['Level']} | {$ex['Subject']} | {$ex['Competence']}\n";
        }
    }

    echo "\n✅ Import terminé avec succès!\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
