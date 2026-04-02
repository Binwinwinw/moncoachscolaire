<?php
require_once __DIR__ . '/src/database/connection.php';

echo "ANALYSE DES EXERCICES POUR CRÉER LES COURS CIBLÉS\n";
echo str_repeat("=", 70) . "\n\n";

// Récupérer toutes les compétences uniques par matière/niveau
$query = "
    SELECT 
        Subject,
        Level,
        Competence,
        COUNT(*) as nb_exercises
    FROM exercises
    WHERE is_active = 'true'
    AND course_id IS NULL
    GROUP BY Subject, Level, Competence
    ORDER BY Subject, Level, Competence
";

$results = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Organiser par matière et niveau
$organized = [];
foreach ($results as $row) {
    $key = $row['Subject'] . ' | ' . $row['Level'];
    if (!isset($organized[$key])) {
        $organized[$key] = [];
    }
    $organized[$key][] = [
        'competence' => $row['Competence'],
        'nb_exercises' => $row['nb_exercises']
    ];
}

// Afficher les résultats
echo "COURS À CRÉER (basés sur les exercices existants)\n";
echo str_repeat("-", 70) . "\n\n";

$course_count = 0;
foreach ($organized as $subject_level => $competences) {
    echo "📚 " . $subject_level . "\n";
    foreach ($competences as $comp) {
        $course_count++;
        echo sprintf(
            "   → Cours #%d : %s (%d exercices)\n",
            $course_count,
            $comp['competence'],
            $comp['nb_exercises']
        );
    }
    echo "\n";
}

echo str_repeat("-", 70) . "\n";
echo "TOTAL : " . $course_count . " cours à créer\n\n";

// Exporter en JSON pour faciliter la création
$json_data = [];
foreach ($organized as $subject_level => $competences) {
    list($subject, $level) = explode(' | ', $subject_level);
    foreach ($competences as $comp) {
        $json_data[] = [
            'subject' => $subject,
            'level' => $level,
            'competence' => $comp['competence'],
            'nb_exercises' => $comp['nb_exercises']
        ];
    }
}

file_put_contents('courses_to_create.json', json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ Liste exportée dans: courses_to_create.json\n";
