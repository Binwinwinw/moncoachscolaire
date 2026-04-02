<?php
// dev/tools/debug/check_exercises_coverage.php
// Vérifie la présence d'au moins un exercice par matière et par niveau


// Correction du chemin de connexion
if (file_exists(__DIR__ . '/../../../db/connection.php')) {
    require_once __DIR__ . '/../../../db/connection.php';
} elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
    require_once __DIR__ . '/../../db/connection.php';
} else {
    echo "DB connection file introuvable\n";
    exit(2);
}

$levels = [
    '6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Terminale'
];
$subjects = [
    'Mathématiques', 'Français', 'Histoire-Géographie', 'SVT', 'Physique-Chimie', 'Anglais', 'Espagnol', 'Allemand', 'Philosophie'
];

if (!$pdo) {
    echo "DB indisponible\n";
    exit(2);
}

foreach ($levels as $level) {
    echo "\n=== $level ===\n";
    foreach ($subjects as $subject) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM exercises WHERE Level = ? AND Subject = ?');
        $stmt->execute([$level, $subject]);
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            echo "✔ $subject : $count exercice(s)\n";
        } else {
            echo "❌ $subject : aucun exercice\n";
        }
    }
}
