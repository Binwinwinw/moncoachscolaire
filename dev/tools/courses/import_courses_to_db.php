<?php
/**
 * Import des cours dans la base de données
 * Crée la table courses et lie automatiquement les exercices aux cours
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();

    echo "IMPORT DES COURS DANS LA BASE DE DONNÉES\n";
    echo str_repeat("=", 60) . "\n\n";

    // Étape 1: Créer la table courses si elle n'existe pas
    echo "Étape 1: Création de la table courses\n";

    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level VARCHAR(50) NOT NULL,
        subject VARCHAR(100) NOT NULL,
        competence VARCHAR(200) NOT NULL,
        domain VARCHAR(100) NOT NULL,
        section VARCHAR(200) NOT NULL,
        key_point TEXT,
        explanation TEXT,
        example TEXT,
        formula VARCHAR(500),
        identifier VARCHAR(255) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_level (level),
        INDEX idx_subject (subject),
        INDEX idx_competence (competence),
        INDEX idx_identifier (identifier)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($createTableSQL);
    echo "✓ Table courses créée ou déjà existante\n\n";

    // Étape 2: Charger les cours depuis le JSON
    echo "Étape 2: Chargement du fichier JSON\n";

    $jsonFile = 'dev/data/test_20_courses.json';
    if (!file_exists($jsonFile)) {
        die("❌ Fichier introuvable: $jsonFile\n");
    }

    $jsonData = json_decode(file_get_contents($jsonFile), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("❌ Erreur JSON: " . json_last_error_msg() . "\n");
    }

    $courses = $jsonData['courses'] ?? [];
    echo "✓ " . count($courses) . " cours chargés\n\n";

    // Étape 3: Insérer les cours avec des identifiants
    echo "Étape 3: Insertion des cours\n";

    $insertSQL = "
    INSERT INTO courses (level, subject, competence, domain, section, key_point, explanation, example, formula, identifier)
    VALUES (:level, :subject, :competence, :domain, :section, :key_point, :explanation, :example, :formula, :identifier)
    ON DUPLICATE KEY UPDATE
        section = VALUES(section),
        key_point = VALUES(key_point),
        explanation = VALUES(explanation),
        example = VALUES(example),
        formula = VALUES(formula),
        updated_at = CURRENT_TIMESTAMP
    ";

    $stmt = $pdo->prepare($insertSQL);

    $inserted = 0;
    $updated = 0;

    foreach ($courses as $course) {
        // Générer un identifiant unique pour le cours
        $identifier = 'COURSE-' . 
                      strtoupper($course['subject']) . '-' . 
                      strtoupper($course['level']) . '-' . 
                      strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $course['competence']));

        try {
            $stmt->execute([
                ':level' => $course['level'],
                ':subject' => $course['subject'],
                ':competence' => $course['competence'],
                ':domain' => $course['domain'],
                ':section' => $course['section'],
                ':key_point' => $course['key_point'],
                ':explanation' => $course['explanation'],
                ':example' => $course['example'],
                ':formula' => $course['formula'],
                ':identifier' => $identifier
            ]);

            if ($stmt->rowCount() > 0) {
                $inserted++;
                echo "  ✓ Inséré: $identifier\n";
            } else {
                $updated++;
            }

        } catch (PDOException $e) {
            echo "  ❌ Erreur pour $identifier: " . $e->getMessage() . "\n";
        }
    }

    echo "\n✓ Insertion terminée: $inserted nouveaux, $updated mis à jour\n\n";

    // Étape 4: Ajouter la colonne course_id à la table exercises si elle n'existe pas
    echo "Étape 4: Préparation de la liaison exercises ↔ courses\n";

    try {
        $pdo->exec("ALTER TABLE exercises ADD COLUMN course_id INT NULL AFTER Identifier");
        echo "✓ Colonne course_id ajoutée à la table exercises\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "✓ Colonne course_id existe déjà\n";
        } else {
            echo "⚠️  Erreur: " . $e->getMessage() . "\n";
        }
    }

    try {
        $pdo->exec("ALTER TABLE exercises ADD FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL");
        echo "✓ Clé étrangère ajoutée\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key') !== false) {
            echo "✓ Clé étrangère existe déjà\n";
        } else {
            echo "⚠️  Erreur FK: " . $e->getMessage() . "\n";
        }
    }

    echo "\n";

    // Étape 5: Lier automatiquement les exercices aux cours
    echo "Étape 5: Liaison automatique exercices ↔ cours\n";

    $linkSQL = "
    UPDATE exercises e
    INNER JOIN courses c ON 
        UPPER(e.Subject) = UPPER(c.subject) 
        AND UPPER(e.Level) = UPPER(c.level)
        AND UPPER(REPLACE(e.Competence, ' ', '')) = UPPER(REPLACE(c.competence, ' ', ''))
    SET e.course_id = c.id
    WHERE e.course_id IS NULL
    ";

    $linked = $pdo->exec($linkSQL);
    echo "✓ $linked exercices liés automatiquement aux cours\n\n";

    // Statistiques finales
    echo str_repeat("=", 60) . "\n";
    echo "STATISTIQUES FINALES\n";
    echo str_repeat("=", 60) . "\n";

    $stats = $pdo->query("
        SELECT 
            c.level,
            c.subject,
            COUNT(DISTINCT c.id) as nb_courses,
            COUNT(e.id) as nb_exercises
        FROM courses c
        LEFT JOIN exercises e ON e.course_id = c.id
        GROUP BY c.level, c.subject
        ORDER BY c.level, c.subject
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stats as $stat) {
        echo sprintf(
            "%s - %s: %d cours, %d exercices liés\n",
            $stat['level'],
            $stat['subject'],
            $stat['nb_courses'],
            $stat['nb_exercises']
        );
    }

    echo "\n✅ Import terminé avec succès!\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
