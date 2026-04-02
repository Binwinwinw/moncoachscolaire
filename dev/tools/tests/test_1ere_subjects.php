<?php
// Vérification : sujets du niveau 1ere après normalization
require_once dirname(__DIR__) . '/src/database/connection.php';

echo "Sujets disponibles pour 1ere:\n";
echo "==============================\n\n";

if (isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT Subject FROM Exercises WHERE Level = '1ere' ORDER BY Subject");
        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($subjects)) {
            echo "❌ Aucun sujet trouvé pour le niveau '1ere'\n";
        } else {
            foreach ($subjects as $subject) {
                echo "✓ $subject\n";
            }
            echo "\n" . count($subjects) . " sujets trouvés\n";
        }
        
    } catch (Exception $e) {
        echo "Erreur SQL: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Erreur: Connexion PDO non disponible\n";
}
?>
