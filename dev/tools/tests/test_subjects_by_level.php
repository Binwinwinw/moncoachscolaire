<?php
// Test pour voir les matières par niveau et détecter les incohérences
require_once dirname(__DIR__) . '/src/database/connection.php';

echo "📊 Matières par niveau:\n";
echo "=======================\n\n";

if (isset($pdo) && $pdo) {
    try {
        // Récupérer tous les niveaux uniques
        $stmt = $pdo->query("SELECT DISTINCT Level FROM Exercises ORDER BY Level");
        $levels = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($levels as $level) {
            echo "📚 Niveau: $level\n";
            $stmt = $pdo->prepare("SELECT DISTINCT Subject FROM Exercises WHERE Level = ? ORDER BY Subject");
            $stmt->execute([$level]);
            $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($subjects) > 0) {
                foreach ($subjects as $subject) {
                    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM Exercises WHERE Level = ? AND Subject = ?");
                    $countStmt->execute([$level, $subject]);
                    $count = $countStmt->fetchColumn();
                    echo "   - $subject: $count\n";
                }
            } else {
                echo "   ⚠️  Aucune matière trouvée\n";
            }
            echo "\n";
        }
    } catch (Exception $e) {
        echo "Erreur SQL: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Erreur: Connexion PDO non disponible\n";
}
?>
