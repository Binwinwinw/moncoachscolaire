<?php
/**
 * dev/tools/exercises/link_exercises_to_courses.php
 * Lie automatiquement les exercices aux cours via parsing de l'Identifier
 *
 * Usage: php dev/tools/exercises/link_exercises_to_courses.php
 */

// Support running from CLI only
if (php_sapi_name() !== 'cli') {
    die('❌ Ce script doit être exécuté en ligne de commande.' . PHP_EOL);
}

// Correct path resolution
$rootDir = dirname(dirname(dirname(__DIR__)));

// Load dependencies
require_once $rootDir . '/src/config/config.php';
require_once $rootDir . '/src/database/connection.php';

// Vérifier si exercise_parser.php existe, sinon inclure les fonctions ici
if (file_exists($rootDir . '/src/utils/exercise_parser.php')) {
    require_once $rootDir . '/src/utils/exercise_parser.php';
} else {
    // Fonctions de parsing inline si le fichier n'existe pas encore
    function parseExerciseIdentifier($identifier) {
        $parts = explode('-', strtoupper(trim($identifier)));

        if (count($parts) < 4) {
            return null;
        }

        return [
            'subject' => normalizeSubject($parts[0]),
            'level' => normalizeLevel($parts[1]),
            'competence' => normalizeCompetence($parts[2]),
            'number' => $parts[3],
            'raw' => $identifier
        ];
    }

    function normalizeSubject($subject) {
        $mapping = [
            'MATHEMATIQUES' => 'Mathématiques',
            'MATHS' => 'Mathématiques',
            'FRANCAIS' => 'Français',
            'PHYSIQUECHIMIE' => 'Physique-Chimie',
            'PHYSIQUE' => 'Physique-Chimie',
            'SVT' => 'SVT',
            'HISTOIREGEO' => 'Histoire-Géo',
            'HISTOIRE' => 'Histoire-Géo',
            'ANGLAIS' => 'Anglais'
        ];
        return $mapping[str_replace(['-', '_', ' '], '', $subject)] ?? ucfirst(strtolower($subject));
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
}

// Fonction utilitaire pour nettoyer les slugs
function cleanSlug($str) {
    // Translittération UTF-8 → ASCII
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    // Remplacer tout sauf lettres et chiffres par tirets
    $str = preg_replace('/[^a-z0-9]+/i', '-', $str);
    // Nettoyer les tirets multiples et ceux en début/fin
    $str = preg_replace('/-+/', '-', $str);
    return trim(strtolower($str), '-');
}

// ============================================
// DÉBUT DU SCRIPT
// ============================================

echo "🔗 Liaison automatique exercices → cours\n";
echo "========================================\n\n";

try {
    // 1. Récupérer tous les exercices actifs
    $stmt = $pdo->query("
        SELECT ExerciseID, Identifier, Level, Subject, Domain, Competence
        FROM exercises
        WHERE is_active = 1
        ORDER BY Level, Subject, Identifier
    ");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ " . count($exercises) . " exercices trouvés\n\n";

    $stats = [
        'linked' => 0,
        'already_linked' => 0,
        'no_course' => 0,
        'parse_error' => 0
    ];

    $missing_courses = [];

    // Barre de progression simple
    $total = count($exercises);
    $current = 0;

    foreach ($exercises as $exercise) {
        $current++;

        // Parse l'Identifier
        $parsed = parseExerciseIdentifier($exercise['Identifier']);

        if (!$parsed) {
            $stats['parse_error']++;
            continue;
        }

        // 2. Chercher le cours correspondant
        $sql = "SELECT id, section FROM courses
                WHERE level = :level
                  AND subject = :subject
                  AND competence = :competence
                  AND is_active = 1
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':level' => $parsed['level'],
            ':subject' => $parsed['subject'],
            ':competence' => $parsed['competence']
        ]);

        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($course) {
            // 3. Vérifier si le lien existe déjà
            $checkSql = "SELECT Id FROM exercisecourselinks
                         WHERE ExerciseId = :exercise_id
                           AND CourseId = :course_id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([
                ':exercise_id' => $exercise['ExerciseID'],
                ':course_id' => $course['id']
            ]);

            if ($checkStmt->fetch()) {
                $stats['already_linked']++;
                continue;
            }

            // 4. Créer le lien
            $linkSql = "INSERT INTO exercisecourselinks
                        (ExerciseId, CourseId, LinkType, LinkedAt)
                        VALUES (:exercise_id, :course_id, 'theory', NOW())";

            $linkStmt = $pdo->prepare($linkSql);
            $linkStmt->execute([
                ':exercise_id' => $exercise['ExerciseID'],
                ':course_id' => $course['id']
            ]);

            $stats['linked']++;

            // Afficher progression tous les 10 exercices
            if ($stats['linked'] % 10 === 0 || $stats['linked'] === 1) {
                echo "✅ Ex #{$exercise['ExerciseID']} → Cours #{$course['id']} ({$parsed['competence']}) [$current/$total]\n";
            }

        } else {
            // Cours manquant
            $stats['no_course']++;

            $key = "{$parsed['level']}|{$parsed['subject']}|{$parsed['competence']}";
            if (!isset($missing_courses[$key])) {
                $missing_courses[$key] = [
                    'level' => $parsed['level'],
                    'subject' => $parsed['subject'],
                    'competence' => $parsed['competence'],
                    'domain' => $exercise['Domain'] ?? '',
                    'count' => 0
                ];
            }
            $missing_courses[$key]['count']++;
        }
    }

    echo "\n📊 RÉSULTATS\n";
    echo "============\n";
    echo "✅ Nouveaux liens créés : {$stats['linked']}\n";
    echo "ℹ️  Déjà liés : {$stats['already_linked']}\n";
    echo "⚠️  Cours manquants : {$stats['no_course']}\n";
    echo "❌ Erreurs de format : {$stats['parse_error']}\n";

    // 5. Générer le fichier SQL pour les cours manquants
    if (!empty($missing_courses)) {
        echo "\n🔴 COURS MANQUANTS À CRÉER (" . count($missing_courses) . ")\n";
        echo "=====================================\n\n";

        // Créer le dossier dev/db s'il n'existe pas
        $dbDir = $rootDir . '/dev/db';
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        // Fichier de sortie SQL
        $outputFile = $dbDir . '/MISSING_COURSES_' . date('Ymd_His') . '.sql';

        $sqlBuffer = "-- SQL généré automatiquement le " . date('Y-m-d H:i:s') . "\n";
        $sqlBuffer .= "-- Cours manquants détectés depuis les Identifiers\n\n";
        $sqlBuffer .= "INSERT INTO courses (level, subject, competence, section, key_point, explanation, slug, domain, is_active) VALUES\n";

        $values = [];
        $slugsSeen = []; // Éviter les doublons de slug

        foreach ($missing_courses as $course) {
            // Génération du slug
            $slugBase = cleanSlug(
                $course['level'] . '-' .
                $course['subject'] . '-' .
                $course['competence']
            );

            // Gérer les slugs dupliqués (rare mais possible)
            $slug = $slugBase;
            $counter = 1;
            while (in_array($slug, $slugsSeen)) {
                $slug = $slugBase . '-' . $counter;
                $counter++;
            }
            $slugsSeen[] = $slug;

            $values[] = sprintf(
                "('%s', '%s', '%s', 'À définir', 'À compléter par Genspark', 'Explication à ajouter', '%s', '%s', 1) -- %d exercices",
                $course['level'],
                addslashes($course['subject']),
                addslashes($course['competence']),
                $slug,
                addslashes($course['domain']),
                $course['count']
            );
        }

        $sqlBuffer .= implode(",\n", $values) . ";\n";

        // Sauvegarder le fichier
        file_put_contents($outputFile, $sqlBuffer);

        echo "💾 Fichier SQL généré : " . basename($outputFile) . "\n";
        echo "📍 Emplacement : $outputFile\n\n";
        echo "👉 PROCHAINE ÉTAPE :\n";
        echo "   1. Exécutez ce fichier SQL dans phpMyAdmin ou via CLI\n";
        echo "   2. Demandez à Genspark d'enrichir les cours créés\n";
        echo "   3. Relancez ce script pour lier les exercices restants\n";
    }

    echo "\n✅ Script terminé avec succès !\n";

} catch (PDOException $e) {
    echo "\n❌ ERREUR BASE DE DONNÉES\n";
    echo "Message : " . $e->getMessage() . "\n";
    echo "Code : " . $e->getCode() . "\n";

    // Log l'erreur dans un fichier
    $logFile = $rootDir . '/dev/reports/link_exercises_errors.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents(
        $logFile,
        date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n",
        FILE_APPEND
    );

    exit(1);
} catch (Exception $e) {
    echo "\n❌ ERREUR GÉNÉRALE\n";
    echo "Message : " . $e->getMessage() . "\n";
    exit(1);
}
