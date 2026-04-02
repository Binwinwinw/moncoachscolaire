<?php
/**
 * dev/tools/courses/generate_missing_courses.php
 * Génère automatiquement les cours manquants depuis les Identifiers
 */

require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/database/connection.php';

echo "🏗️  Génération des cours manquants\n";
echo "==================================\n\n";

// Fonctions de normalisation
function normalizeSubject($subject) {
    $mapping = [
        'MATHEMATIQUES' => 'Mathématiques',
        'MATHS' => 'Mathématiques',
        'FRANCAIS' => 'Français',
        'PHYSIQUECHIMIE' => 'Physique-Chimie',
        'PHYSIQUE' => 'Physique-Chimie',
        'SVT' => 'SVT',
        'HISTOIREGEO' => 'Histoire-Géo',
        'ANGLAIS' => 'Anglais'
    ];
    return $mapping[$subject] ?? ucfirst(strtolower($subject));
}

function normalizeLevel($level) {
    $mapping = [
        'SIXIEME' => '6eme', '6EME' => '6eme', '6E' => '6eme',
        'CINQUIEME' => '5eme', '5EME' => '5eme', '5E' => '5eme',
        'QUATRIEME' => '4eme', '4EME' => '4eme', '4E' => '4eme',
        'TROISIEME' => '3eme', '3EME' => '3eme', '3E' => '3eme',
        'SECONDE' => '2nde', '2NDE' => '2nde',
        'PREMIERE' => '1ere', '1ERE' => '1ere',
        'TERMINALE' => 'terminale', 'TERM' => 'terminale', 'BAC' => 'terminale'
    ];
    return $mapping[$level] ?? strtolower($level);
}

function normalizeCompetence($competence) {
    return ucwords(strtolower(str_replace('_', ' ', $competence)));
}

try {
    // 1. Récupérer toutes les combinaisons uniques depuis les exercices
    $stmt = $pdo->query("
        SELECT DISTINCT
            Identifier,
            Level,
            Subject,
            Domain
        FROM exercises
        WHERE is_active = 1
        ORDER BY Level, Subject
    ");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ " . count($exercises) . " exercices analysés\n\n";

    $coursesToCreate = [];

    foreach ($exercises as $exercise) {
        $parts = explode('-', strtoupper($exercise['Identifier']));

        if (count($parts) < 4) {
            continue;
        }

        $level = normalizeLevel($parts[1]);
        $subject = normalizeSubject($parts[0]);
        $competence = normalizeCompetence($parts[2]);

        $key = "$level|$subject|$competence";

        if (!isset($coursesToCreate[$key])) {
            $coursesToCreate[$key] = [
                'level' => $level,
                'subject' => $subject,
                'competence' => $competence,
                'domain' => $exercise['Domain'] ?? '',
                'count' => 0
            ];
        }
        $coursesToCreate[$key]['count']++;
    }

    echo "📚 " . count($coursesToCreate) . " cours uniques identifiés\n\n";

    // 2. Vérifier quels cours existent déjà
    $created = 0;
    $skipped = 0;

    foreach ($coursesToCreate as $course) {
        // Vérifier existence
        $checkStmt = $pdo->prepare("
            SELECT id FROM courses
            WHERE level = :level
              AND subject = :subject
              AND competence = :competence
            LIMIT 1
        ");
        $checkStmt->execute([
            ':level' => $course['level'],
            ':subject' => $course['subject'],
            ':competence' => $course['competence']
        ]);

        if ($checkStmt->fetch()) {
            $skipped++;
            continue; // Cours déjà existant
        }

        // Créer le slug
        $slug = strtolower(
            $course['level'] . '-' .
            str_replace([' ', '\''], '-', $course['subject']) . '-' .
            str_replace([' ', '\''], '-', $course['competence'])
        );

        // Créer le cours (template de base)
        $insertStmt = $pdo->prepare("
            INSERT INTO courses (
                level, subject, competence, section,
                key_point, explanation, slug, domain,
                is_active, created_at
            ) VALUES (
                :level, :subject, :competence, 'À définir',
                'À compléter par Genspark', 'À compléter par Genspark',
                :slug, :domain, 1, NOW()
            )
        ");

        $insertStmt->execute([
            ':level' => $course['level'],
            ':subject' => $course['subject'],
            ':competence' => $course['competence'],
            ':slug' => $slug,
            ':domain' => $course['domain']
        ]);

        $created++;
        echo "✅ Créé : {$course['level']} / {$course['subject']} / {$course['competence']} ({$course['count']} exercices)\n";
    }

    echo "\n📊 RÉSULTATS\n";
    echo "============\n";
    echo "✅ Cours créés : $created\n";
    echo "⏭️  Déjà existants : $skipped\n";

    if ($created > 0) {
        echo "\n💡 PROCHAINE ÉTAPE :\n";
        echo "Demande à Genspark de remplir les champs key_point, explanation, example\n";
        echo "pour les $created cours créés.\n";
    }

    echo "\n✅ Génération terminée !\n";

} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
