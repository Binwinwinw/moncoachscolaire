<?php
/**
 * Script d'extraction des "tips" depuis la colonne Answer
 * et remplissage de la nouvelle colonne Tips
 * 
 * Usage: php tools/extract_tips_from_answers.php
 * 
 * Ce script:
 * 1. Lit tous les exercices
 * 2. Parse le champ Answer pour identifier les sections "tips" ou "astuces"
 * 3. Extrait ces conseils dans la colonne Tips
 * 4. Nettoie le champ Answer pour ne garder que la réponse
 */

require_once __DIR__ . '/../src/database/connection.php';

if (!$pdo) {
    die("❌ Erreur: Impossible de se connecter à la base de données.\n");
}

echo "🔍 Extraction des tips depuis la colonne Answer...\n\n";

// Vérifier que la colonne Tips existe
try {
    $pdo->query("SELECT Tips FROM Exercises LIMIT 1");
} catch (PDOException $e) {
    die("❌ La colonne 'Tips' n'existe pas. Exécutez d'abord la migration:\n   mysql < db/migrations/add_tips_column.sql\n\n");
}

// Récupérer tous les exercices
$stmt = $pdo->query("SELECT Id, Title, Subject, Level, Answer FROM Exercises ORDER BY Id");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "📚 " . count($exercises) . " exercices trouvés.\n\n";

$updated = 0;
$skipped = 0;
$errors = 0;

foreach ($exercises as $exercise) {
    $id = $exercise['Id'];
    $answer = $exercise['Answer'] ?? '';
    
    if (empty($answer)) {
        $skipped++;
        continue;
    }
    
    // Parser le contenu pour extraire les tips
    $result = extractTipsFromAnswer($answer);
    
    if ($result['tips']) {
        try {
            // Mettre à jour l'exercice avec les tips extraits et la réponse nettoyée
            $updateStmt = $pdo->prepare(
                "UPDATE Exercises SET Answer = ?, Tips = ? WHERE Id = ?"
            );
            $updateStmt->execute([
                $result['cleanAnswer'],
                $result['tips'],
                $id
            ]);
            
            $updated++;
            echo "✅ Ex #{$id}: Tips extraits (" . mb_strlen($result['tips']) . " caractères)\n";
        } catch (PDOException $e) {
            $errors++;
            echo "❌ Ex #{$id}: Erreur - " . $e->getMessage() . "\n";
        }
    } else {
        $skipped++;
    }
}

echo "\n📊 Résumé:\n";
echo "   ✅ Exercices mis à jour: $updated\n";
echo "   ⏭️  Exercices ignorés: $skipped\n";
if ($errors > 0) {
    echo "   ❌ Erreurs: $errors\n";
}
echo "\n✨ Terminé!\n";

/**
 * Extrait les tips d'un champ Answer
 * 
 * Patterns recherchés:
 * - Sections commençant par "Astuce:", "Conseil:", "💡", "Aide:"
 * - Balises HTML comme <div class="tip">, <p class="astuce">
 * - Sections après "Pour t'aider:", "Rappel:"
 */
function extractTipsFromAnswer($answer) {
    $tips = '';
    $cleanAnswer = $answer;
    
    // Décoder les entités HTML pour faciliter le parsing
    $decoded = html_entity_decode($answer, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Pattern 1: Sections explicites avec marqueurs
    $patterns = [
        '/(?:<p[^>]*>)?\s*(?:💡|🔍|📝)?\s*(?:<strong>)?(?:Astuce|Conseil|Aide|Pour t\'aider|Rappel)(?:<\/strong>)?\s*:?\s*(.*?)(?=<\/p>|$)/isu',
        '/<div[^>]*class=["\'](?:tip|astuce|hint|conseil)["\'][^>]*>(.*?)<\/div>/isu',
        '/<p[^>]*class=["\'](?:tip|astuce|hint|conseil)["\'][^>]*>(.*?)<\/p>/isu',
    ];
    
    $foundTips = [];
    
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $decoded, $matches)) {
            foreach ($matches[1] as $match) {
                $tip = trim(strip_tags($match));
                if ($tip && mb_strlen($tip) > 10) {
                    $foundTips[] = $tip;
                }
            }
        }
    }
    
    // Pattern 2: Recherche de paragraphes contenant des mots-clés d'aide
    if (preg_match_all('/<p[^>]*>(.*?(?:astuce|conseil|aide|rappel|pour t\'aider|n\'oublie pas).*?)<\/p>/isu', $decoded, $matches)) {
        foreach ($matches[1] as $match) {
            $tip = trim(strip_tags($match));
            if ($tip && mb_strlen($tip) > 20 && !in_array($tip, $foundTips)) {
                // Vérifier que ce n'est pas juste la réponse elle-même
                if (stripos($tip, 'astuce') !== false || 
                    stripos($tip, 'conseil') !== false || 
                    stripos($tip, 'pour t\'aider') !== false ||
                    stripos($tip, 'rappel') !== false) {
                    $foundTips[] = $tip;
                }
            }
        }
    }
    
    if (!empty($foundTips)) {
        // Formater les tips en HTML propre
        $tips = '<div class="exercise-tips">';
        foreach ($foundTips as $tip) {
            $tips .= '<p class="tip">💡 ' . htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') . '</p>';
        }
        $tips .= '</div>';
        
        // Nettoyer la réponse en retirant les sections de tips
        $cleanAnswer = $answer;
        foreach ($patterns as $pattern) {
            $cleanAnswer = preg_replace($pattern, '', $cleanAnswer);
        }
        
        // Nettoyer les balises vides et espaces multiples
        $cleanAnswer = preg_replace('/<p[^>]*>\s*<\/p>/', '', $cleanAnswer);
        $cleanAnswer = preg_replace('/\s+/', ' ', $cleanAnswer);
        $cleanAnswer = trim($cleanAnswer);
    }
    
    return [
        'tips' => $tips,
        'cleanAnswer' => $cleanAnswer
    ];
}
