<?php
// Script de nettoyage : enlever accents et normaliser niveaux
require_once dirname(__DIR__) . '/src/database/connection.php';

// Fonction pour enlever accents
function remove_accents($text) {
    return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
}

// Mapping : normalization des niveaux
$levelMapping = [
    '1ère'     => '1ere',
    'première' => '1ere',
    'Première' => '1ere',
    
    '2nde'     => '2nde',
    'Seconde'  => '2nde',
    
    '3ème'     => '3eme',
    
    '4ème'     => '4eme',
    
    '5ème'     => '5eme',
    
    '6ème'     => '6eme',
    
    'Terminale' => 'Terminale',
];

// Sujets avec accents à nettoyer
$subjectMapping = [
    'Français'           => 'Francais',
    'Mathématiques'      => 'Mathematiques',
    'Général'            => 'General',
    'Histoire-Géographie' => 'Histoire-Geographie',
];

if (isset($pdo) && $pdo) {
    try {
        echo "🧹 Nettoyage de la base de données\n";
        echo "===================================\n\n";
        
        // 1. Normaliser les niveaux
        echo "1️⃣ Normalisation des niveaux:\n";
        foreach ($levelMapping as $oldLevel => $newLevel) {
            if ($oldLevel !== $newLevel) {
                $stmt = $pdo->prepare("UPDATE Exercises SET Level = ? WHERE Level = ?");
                $stmt->execute([$newLevel, $oldLevel]);
                $count = $stmt->rowCount();
                if ($count > 0) {
                    echo "   '$oldLevel' → '$newLevel' ($count exercices)\n";
                }
            }
        }
        
        // 2. Nettoyer accents des sujets
        echo "\n2️⃣ Nettoyage des accents dans les sujets:\n";
        foreach ($subjectMapping as $oldSubject => $newSubject) {
            $stmt = $pdo->prepare("UPDATE Exercises SET Subject = ? WHERE Subject = ?");
            $stmt->execute([$newSubject, $oldSubject]);
            $count = $stmt->rowCount();
            if ($count > 0) {
                echo "   '$oldSubject' → '$newSubject' ($count exercices)\n";
            }
        }
        
        echo "\n✅ Nettoyage terminé!\n\n";
        
        // Afficher le résumé final
        echo "📊 État final de la base:\n";
        $stmt = $pdo->query("
            SELECT Level, COUNT(DISTINCT Subject) as subject_count, COUNT(*) as exercise_count
            FROM Exercises
            GROUP BY Level
            ORDER BY Level
        ");
        $levels = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($levels as $row) {
            echo "  " . $row['Level'] . ": " . $row['subject_count'] . " sujets, " . $row['exercise_count'] . " exercices\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Erreur SQL: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Erreur: Connexion PDO non disponible\n";
}
?>
