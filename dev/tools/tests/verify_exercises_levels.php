<?php
/**
 * Script pour vérifier que tous les exercices ont le bon niveau
 */

require_once __DIR__ . '/../db/connection.php';

if (!isset($pdo) || !$pdo) {
    die("❌ Erreur : Impossible de se connecter à la base de données.\n");
}

echo "🔍 Vérification des niveaux des exercices en base de données\n\n";

// Niveaux attendus
$expected_levels = [
    '6ème' => 'Collège',
    '5ème' => 'Collège',
    '4ème' => 'Collège',
    '3ème' => 'Collège',
    'Seconde' => 'Lycée',
    'Première' => 'Lycée',
    'Terminale' => 'Lycée',
    'BAC' => 'BAC'
];

// Récupérer tous les exercices avec leur niveau
$stmt = $pdo->query("SELECT Id, Level, Subject, Title FROM Exercises ORDER BY Level, Subject, Id");
$exercises = $stmt->fetchAll();

echo "📊 Total d'exercices : " . count($exercises) . "\n\n";

// Grouper par niveau
$by_level = [];
foreach ($exercises as $ex) {
    $level = $ex['Level'];
    if (!isset($by_level[$level])) {
        $by_level[$level] = [];
    }
    $by_level[$level][] = $ex;
}

// Afficher les statistiques par niveau
echo "📚 Répartition par niveau :\n";
foreach ($by_level as $level => $exs) {
    $section = $expected_levels[$level] ?? 'Inconnu';
    echo "\n  🎓 $level ($section) : " . count($exs) . " exercice(s)\n";
    
    // Grouper par matière
    $by_subject = [];
    foreach ($exs as $ex) {
        $subject = $ex['Subject'] ?? 'Sans matière';
        if (!isset($by_subject[$subject])) {
            $by_subject[$subject] = 0;
        }
        $by_subject[$subject]++;
    }
    
    foreach ($by_subject as $subject => $count) {
        echo "     • $subject : $count exercice(s)\n";
    }
}

// Vérifier les niveaux inattendus
echo "\n⚠️  Vérification des niveaux inattendus :\n";
$unexpected = [];
foreach ($by_level as $level => $exs) {
    if (!isset($expected_levels[$level])) {
        $unexpected[$level] = $exs;
    }
}

if (empty($unexpected)) {
    echo "  ✅ Tous les niveaux sont valides\n";
} else {
    echo "  ❌ Niveaux inattendus trouvés :\n";
    foreach ($unexpected as $level => $exs) {
        echo "     • $level : " . count($exs) . " exercice(s)\n";
        echo "       Exemples :\n";
        foreach (array_slice($exs, 0, 3) as $ex) {
            echo "         - ID {$ex['Id']} : {$ex['Title']}\n";
        }
    }
}

// Vérifier les exercices sans niveau
echo "\n⚠️  Vérification des exercices sans niveau :\n";
$stmt = $pdo->query("SELECT Id, Level, Subject, Title FROM Exercises WHERE Level IS NULL OR Level = ''");
$no_level = $stmt->fetchAll();

if (empty($no_level)) {
    echo "  ✅ Tous les exercices ont un niveau défini\n";
} else {
    echo "  ❌ Exercices sans niveau : " . count($no_level) . "\n";
    foreach ($no_level as $ex) {
        echo "     - ID {$ex['Id']} : {$ex['Title']} (Matière: " . ($ex['Subject'] ?? 'N/A') . ")\n";
    }
}

echo "\n✅ Vérification terminée !\n";

