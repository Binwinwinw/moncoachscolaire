<?php
// dev/tools/debug/audit_levels.php

// Connexion DB compatible avec plusieurs emplacements

// Connexion DB via chemin absolu (structure validée par ls_debug)
$connPath = dirname(__DIR__, 3) . '/src/database/connection.php';
echo "Connexion via : $connPath\n";
if (file_exists($connPath)) {
    require $connPath;
} else {
    die("Erreur : Fichier de connexion à la base introuvable ($connPath)\n");
}

echo "Niveaux distincts :\n";
$q1 = $pdo->query('SELECT DISTINCT Level FROM exercises ORDER BY Level');
foreach ($q1 as $row) {
    echo '- ' . ($row['Level'] ?? 'NULL') . "\n";
}

echo "\nExercices actifs par niveau/matière :\n";
$q2 = $pdo->query('SELECT Level, Subject, COUNT(*) AS nb_exercices FROM exercises WHERE is_active = 1 GROUP BY Level, Subject ORDER BY Level, Subject');
foreach ($q2 as $row) {
    echo '- ' . ($row['Level'] ?? 'NULL') . ' | ' . ($row['Subject'] ?? 'NULL') . ' : ' . $row['nb_exercices'] . "\n";
}
