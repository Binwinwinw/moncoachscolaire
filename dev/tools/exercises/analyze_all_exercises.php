<?php
require_once __DIR__ . '/src/database/connection.php';

echo "ANALYSE COMPLÈTE DE TOUS LES EXERCICES\n";
echo str_repeat("=", 70) . "\n\n";

// 1. Vue d'ensemble
echo "📊 VUE D'ENSEMBLE\n";
echo str_repeat("-", 70) . "\n";

$overview = $pdo->query("
    SELECT 
        Subject,
        Level,
        COUNT(*) as total_exercises,
        SUM(CASE WHEN course_id IS NOT NULL THEN 1 ELSE 0 END) as linked,
        SUM(CASE WHEN course_id IS NULL THEN 1 ELSE 0 END) as unlinked
    FROM exercises
    WHERE is_active = 'true'
    GROUP BY Subject, Level
    ORDER BY Subject, Level
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($overview as $row) {
    echo sprintf(
        "%s | %s : %d exercices (%d liés, %d non liés)\n",
        $row['Subject'],
        $row['Level'],
        $row['total_exercises'],
        $row['linked'],
        $row['unlinked']
    );
}

echo "\n\n";

// 2. Toutes les compétences par matière/niveau (liées ou non)
echo "📚 COMPÉTENCES PAR MATIÈRE/NIVEAU (tous exercices)\n";
echo str_repeat("-", 70) . "\n\n";

$query = "
    SELECT 
        Subject,
        Level,
        Competence,
        COUNT(*) as nb_exercises,
        SUM(CASE WHEN course_id IS NOT NULL THEN 1 ELSE 0 END) as linked,
        SUM(CASE WHEN course_id IS NULL THEN 1 ELSE 0 END) as unlinked
    FROM exercises
    WHERE is_active = 'true'
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
        'nb_exercises' => $row['nb_exercises'],
        'linked' => $row['linked'],
        'unlinked' => $row['unlinked']
    ];
}

// Afficher
$total_courses_needed = 0;
$courses_to_create = [];

foreach ($organized as $subject_level => $competences) {
    list($subject, $level) = explode(' | ', $subject_level);

    echo "\n📚 " . strtoupper($subject_level) . "\n";

    foreach ($competences as $comp) {
        $total_courses_needed++;
        $status = $comp['unlinked'] > 0 ? '❌ À CRÉER' : '✅ Existe';

        echo sprintf(
            "   %s Cours : %s\n",
            $status,
            $comp['competence']
        );
        echo sprintf(
            "           → %d exercices (%d liés, %d non liés)\n",
            $comp['nb_exercises'],
            $comp['linked'],
            $comp['unlinked']
        );

        // Ajouter aux cours à créer
        $courses_to_create[] = [
            'subject' => $subject,
            'level' => $level,
            'competence' => $comp['competence'],
            'nb_exercises' => $comp['nb_exercises'],
            'linked' => $comp['linked'],
            'unlinked' => $comp['unlinked'],
            'needs_creation' => $comp['unlinked'] > 0
        ];
    }
}

echo "\n\n";
echo str_repeat("=", 70) . "\n";
echo "RÉSUMÉ\n";
echo str_repeat("-", 70) . "\n";
echo "Total de compétences identifiées : " . $total_courses_needed . "\n";
echo "Cours à créer : " . count(array_filter($courses_to_create, fn($c) => $c['needs_creation'])) . "\n";
echo "Cours déjà existants : " . count(array_filter($courses_to_create, fn($c) => !$c['needs_creation'])) . "\n";
echo "\n";

// Exporter en JSON
file_put_contents('all_courses_structure.json', json_encode($courses_to_create, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "✅ Structure complète exportée dans: all_courses_structure.json\n";
