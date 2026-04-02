<?php
// Diagnostic : sujets par niveau + vérifier les accents
require_once dirname(__DIR__) . '/src/database/connection.php';

echo "Sujets par niveau (avec accents détectés):\n";
echo "==========================================\n\n";

if (isset($pdo) && $pdo) {
    try {
        // Requête pour voir les sujets par niveau
        $stmt = $pdo->query("
            SELECT DISTINCT e.Level, e.Subject
            FROM Exercises e
            ORDER BY e.Level, e.Subject
        ");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $currentLevel = null;
        foreach ($results as $row) {
            $level = $row['Level'];
            $subject = $row['Subject'];
            
            // Détecter les accents
            $hasAccents = $subject !== iconv('UTF-8', 'ASCII//IGNORE', $subject);
            $accentMarker = $hasAccents ? ' ⚠️ ACCENTS' : '';
            
            if ($level !== $currentLevel) {
                echo "\n📚 $level:\n";
                $currentLevel = $level;
            }
            echo "  - $subject$accentMarker\n";
        }
        
        echo "\n\n=== RÉSUMÉ PAR NIVEAU ===\n";
        $stmt2 = $pdo->query("
            SELECT Level, COUNT(DISTINCT Subject) as subject_count, COUNT(*) as exercise_count
            FROM Exercises
            GROUP BY Level
            ORDER BY Level
        ");
        $levels = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($levels as $row) {
            echo $row['Level'] . ": " . $row['subject_count'] . " sujets, " . $row['exercise_count'] . " exercices\n";
        }
        
    } catch (Exception $e) {
        echo "Erreur SQL: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Erreur: Connexion PDO non disponible\n";
}
?>
