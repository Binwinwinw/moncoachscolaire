<?php
/**
 * link_exercises_to_courses.php
 * Lie automatiquement les exercices aux cours avec normalisation des niveaux
 */

require_once __DIR__ . '/src/database/connection.php';

echo "🔗 LIAISON AUTOMATIQUE EXERCICES → COURS (avec normalisation)\n";
echo str_repeat("=", 70) . "\n\n";

// Fonction de normalisation des niveaux
function normalizeLevel($level) {
    $level = mb_strtolower(trim($level));

    // Mapping de toutes les variantes possibles
    $mapping = [
        // Collège
        '6eme' => '6eme',
        '6ème' => '6eme',
        'sixieme' => '6eme',
        'sixième' => '6eme',
        '6e' => '6eme',

        '5eme' => '5eme',
        '5ème' => '5eme',
        'cinquieme' => '5eme',
        'cinquième' => '5eme',
        '5e' => '5eme',

        '4eme' => '4eme',
        '4ème' => '4eme',
        'quatrieme' => '4eme',
        'quatrième' => '4eme',
        '4e' => '4eme',

        '3eme' => '3eme',
        '3ème' => '3eme',
        'troisieme' => '3eme',
        'troisième' => '3eme',
        '3e' => '3eme',

        // Lycée
        'seconde' => 'seconde',
        '2nde' => 'seconde',
        '2de' => 'seconde',

        'premiere' => 'premiere',
        'première' => 'premiere',
        '1ere' => 'premiere',
        '1ère' => 'premiere',
        '1e' => 'premiere',

        'terminale' => 'terminale',
        'term' => 'terminale',
        'tle' => 'terminale'
    ];

    return $mapping[$level] ?? $level;
}

try {
    // Récupérer tous les exercices actifs sans course_id
    $stmt = $pdo->query("
        SELECT Id, Subject, Level, Competence
        FROM exercises
        WHERE is_active = 'true'
        AND (course_id IS NULL OR course_id = 0)
        ORDER BY Subject, Level, Competence
    ");

    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "📚 Exercices à lier : " . count($exercises) . "\n\n";

    $stats = [
        'linked' => 0,
        'no_match' => 0,
        'errors' => 0
    ];

    $no_match_details = [];

    foreach ($exercises as $exercise) {
        $exId = $exercise['Id'];
        $subject = $exercise['Subject'];
        $level = $exercise['Level'];
        $competence = $exercise['Competence'];

        // Normaliser le niveau de l'exercice
        $normalizedLevel = normalizeLevel($level);

        // Chercher le cours avec normalisation
        $stmt = $pdo->prepare("
            SELECT id, level as course_level
            FROM courses
            WHERE UPPER(subject) = UPPER(:subject)
            AND UPPER(competence) = UPPER(:competence)
            AND is_active = 1
        ");

        $stmt->execute([
            'subject' => $subject,
            'competence' => $competence
        ]);

        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filtrer en PHP avec normalisation
        $matchedCourse = null;
        foreach ($courses as $course) {
            if (normalizeLevel($course['course_level']) === $normalizedLevel) {
                $matchedCourse = $course;
                break;
            }
        }

        if ($matchedCourse) {
            // Lier l'exercice au cours
            $updateStmt = $pdo->prepare("
                UPDATE exercises
                SET course_id = :course_id
                WHERE Id = :exercise_id
            ");

            $updateStmt->execute([
                'course_id' => $matchedCourse['id'],
                'exercise_id' => $exId
            ]);

            $stats['linked']++;

            if ($stats['linked'] <= 5 || $stats['linked'] % 10 === 0) {
                echo "✅ Ex #{$exId} → Cours #{$matchedCourse['id']} ({$subject} | {$level}→{$normalizedLevel} | " . substr($competence, 0, 40) . "...)\n";
            }

        } else {
            $stats['no_match']++;

            $key = "{$subject}|{$normalizedLevel}|{$competence}";
            if (!isset($no_match_details[$key])) {
                $no_match_details[$key] = [
                    'subject' => $subject,
                    'level' => $normalizedLevel,
                    'competence' => $competence,
                    'count' => 0
                ];
            }
            $no_match_details[$key]['count']++;
        }
    }

    echo "\n";
    echo str_repeat("=", 70) . "\n";
    echo "📊 RÉSULTATS\n";
    echo str_repeat("-", 70) . "\n";
    echo "✅ Exercices liés : {$stats['linked']}\n";
    echo "⚠️  Aucun cours trouvé : {$stats['no_match']}\n";
    echo "❌ Erreurs : {$stats['errors']}\n";

    if (!empty($no_match_details)) {
        echo "\n";
        echo "🔴 DÉTAILS DES EXERCICES NON LIÉS\n";
        echo str_repeat("-", 70) . "\n";
        foreach ($no_match_details as $detail) {
            echo sprintf(
                "  %s | %s | %s : %d exercices\n",
                $detail['subject'],
                $detail['level'],
                substr($detail['competence'], 0, 50),
                $detail['count']
            );
        }
        echo "\n💡 Astuce : Vérifiez que ces cours existent dans la BDD\n";
    }

    echo "\n✅ Liaison terminée !\n";

} catch (PDOException $e) {
    echo "\n❌ ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
