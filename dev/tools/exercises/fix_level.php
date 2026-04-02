<?php
require_once __DIR__ . '/src/database/connection.php';

echo "Correction des niveaux manquants\n";
echo str_repeat("=", 50) . "\n\n";

// Français Première : cours 406-419
$pdo->exec("UPDATE courses SET level = 'Première' WHERE id >= 406 AND id <= 419");
echo "✅ Cours 406-419: niveau 'Première' ajouté\n";

// Mathématiques Seconde : cours 420-434
$pdo->exec("UPDATE courses SET level = 'Seconde' WHERE id >= 420 AND id <= 434");
echo "✅ Cours 420-434: niveau 'Seconde' ajouté\n";

echo "\n✅ Correction terminée !\n";
