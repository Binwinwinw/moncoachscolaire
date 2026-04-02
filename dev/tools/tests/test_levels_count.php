<?php
// Test temporaire pour compter les exercices par niveau
require_once dirname(__DIR__) . '/src/database/connection.php';

echo "Comptage des exercices par niveau:\n";
echo "====================================\n\n";

if (isset($pdo) && $pdo) {
    try {
        $stmt = $pdo->query("SELECT Level, COUNT(*) as count FROM Exercises GROUP BY Level ORDER BY count DESC");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalCount = 0;
        foreach ($results as $row) {
            echo $row['Level'] . ": " . $row['count'] . " exercices\n";
            $totalCount += $row['count'];
        }
        echo "\n📊 Total: " . $totalCount . " exercices\n";
    } catch (Exception $e) {
        echo "Erreur SQL: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Erreur: Connexion PDO non disponible\n";
}
?>
