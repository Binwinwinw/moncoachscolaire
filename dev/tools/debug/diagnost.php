<?php
/**
 * Diagnostic : Pourquoi les exercices ne sont pas liés aux cours ?
 */

require_once __DIR__ . '/src/database/connection.php';

echo "DIAGNOSTIC DE LA LIAISON EXERCICES ↔ COURS\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Exemples d'exercices
echo "Étape 1: Exemples d'exercices dans la BDD\n";
$exercises = $pdo->query("
    SELECT Id, Subject, Level, Competence, course_id
    FROM exercises
    WHERE is_active = 'true'
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($exercises as $ex) {
    echo sprintf(
        "  Ex #%d | %s | %s | %s | course_id: %s\n",
        $ex['Id'],
        $ex['Subject'],
        $ex['Level'],
        $ex['Competence'],
        $ex['course_id'] ?? 'NULL'
    );
}

echo "\n";

// 2. Exemples de cours
echo "Étape 2: Exemples de cours dans la BDD\n";
$courses = $pdo->query("
    SELECT id, subject, level, competence
    FROM courses
    WHERE is_active = 1
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($courses as $c) {
    echo sprintf(
        "  Cours #%d | %s | %s | %s\n",
        $c['id'],
        $c['subject'],
        $c['level'],
        $c['competence']
    );
}

echo "\n";

// 3. Tester une correspondance manuelle
echo "Étape 3: Test de correspondance manuelle\n";

if (!empty($exercises) && !empty($courses)) {
    $ex = $exercises[0];

    echo "Recherche d'un cours pour l'exercice #" . $ex['Id'] . ":\n";
    echo "  Subject: {$ex['Subject']}\n";
    echo "  Level: {$ex['Level']}\n";
    echo "  Competence: {$ex['Competence']}\n\n";

    // Recherche exacte
    $stmt = $pdo->prepare("
        SELECT id, subject, level, competence
        FROM courses
        WHERE UPPER(subject) = UPPER(:subject)
        AND UPPER(level) = UPPER(:level)
        LIMIT 5
    ");

    $stmt->execute([
        'subject' => $ex['Subject'],
        'level' => $ex['Level']
    ]);

    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($matches)) {
        echo "  ❌ Aucun cours trouvé avec ce Subject + Level\n\n";
    } else {
        echo "  ✓ Cours trouvés avec ce Subject + Level:\n";
        foreach ($matches as $m) {
            echo "    → Cours #{$m['id']}: {$m['competence']}\n";
        }
    }
}

echo "\n";

// 4. Compter les correspondances potentielles
echo "Étape 4: Statistiques de correspondances potentielles\n";

$stats = $pdo->query("
    SELECT
        e.Subject,
        e.Level,
        COUNT(DISTINCT e.Id) as nb_exercises,
        COUNT(DISTINCT c.id) as nb_courses_match
    FROM exercises e
    LEFT JOIN courses c ON
        UPPER(e.Subject) = UPPER(c.subject)
        AND UPPER(e.Level) = UPPER(c.level)
    WHERE e.is_active = 'true'
    GROUP BY e.Subject, e.Level
    ORDER BY e.Subject, e.Level
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($stats as $s) {
    echo sprintf(
        "  %s | %s : %d exercices → %d cours disponibles\n",
        $s['Subject'],
        $s['Level'],
        $s['nb_exercises'],
        $s['nb_courses_match']
    );
}

echo "\n✅ Diagnostic terminé\n";
