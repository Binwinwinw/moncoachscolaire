oui le panneau de verrouillage fonctinne <?php
/**
 * Affichage détaillé du contenu réel des colonnes
 * Content / Answer / Tips pour vérification visuelle du parsing
 */

require_once __DIR__ . '/../src/database/connection.php';

if (!$pdo) {
    die("❌ Erreur: Impossible de se connecter à la base de données.\n");
}

echo "🔍 AFFICHAGE DÉTAILLÉ - CONTENT / ANSWER / TIPS\n";
echo str_repeat("=", 100) . "\n\n";

// Récupérer 5 exercices avec Tips extraits
$withTips = $pdo->query("
    SELECT Id, Title, Subject, Level, Content, Answer, Tips 
    FROM Exercises 
    WHERE Tips IS NOT NULL AND Tips != '' 
    ORDER BY Id 
    LIMIT 5
")->fetchAll();

echo "📋 Affichage de " . count($withTips) . " exercices avec Tips extraits\n\n";

foreach ($withTips as $ex) {
    echo str_repeat("█", 100) . "\n";
    echo "🎯 EXERCICE #{$ex['Id']}\n";
    echo "   Titre: {$ex['Title']}\n";
    echo "   Matière: {$ex['Subject']} | Niveau: {$ex['Level']}\n";
    echo str_repeat("█", 100) . "\n\n";
    
    // CONTENT - La consigne
    echo "📖 CONTENT (CONSIGNE)\n";
    echo str_repeat("-", 100) . "\n";
    echo stripHtmlKeepFormat($ex['Content']);
    echo "\n" . str_repeat("-", 100) . "\n\n";
    
    // ANSWER - La réponse
    echo "💡 ANSWER (RÉPONSE)\n";
    echo str_repeat("-", 100) . "\n";
    echo stripHtmlKeepFormat($ex['Answer']);
    echo "\n" . str_repeat("-", 100) . "\n\n";
    
    // TIPS - Les astuces extraites
    echo "✨ TIPS (ASTUCES EXTRAITES)\n";
    echo str_repeat("-", 100) . "\n";
    echo stripHtmlKeepFormat($ex['Tips']);
    echo "\n" . str_repeat("-", 100) . "\n\n";
    
    echo "\n";
}

// Afficher aussi 2 exercices SANS Tips
echo "\n\n";
echo str_repeat("=", 100) . "\n";
echo "🔍 EXEMPLES SANS TIPS (pour comparaison)\n";
echo str_repeat("=", 100) . "\n\n";

$noTips = $pdo->query("
    SELECT Id, Title, Subject, Level, Content, Answer, Tips 
    FROM Exercises 
    WHERE Tips IS NULL OR Tips = '' 
    ORDER BY Id 
    LIMIT 2
")->fetchAll();

foreach ($noTips as $ex) {
    echo str_repeat("█", 100) . "\n";
    echo "🎯 EXERCICE #{$ex['Id']}\n";
    echo "   Titre: {$ex['Title']}\n";
    echo "   Matière: {$ex['Subject']} | Niveau: {$ex['Level']}\n";
    echo str_repeat("█", 100) . "\n\n";
    
    echo "📖 CONTENT (CONSIGNE)\n";
    echo str_repeat("-", 100) . "\n";
    echo stripHtmlKeepFormat($ex['Content']);
    echo "\n" . str_repeat("-", 100) . "\n\n";
    
    echo "💡 ANSWER (RÉPONSE)\n";
    echo str_repeat("-", 100) . "\n";
    echo stripHtmlKeepFormat($ex['Answer']);
    echo "\n" . str_repeat("-", 100) . "\n\n";
    
    echo "✨ TIPS: " . ($ex['Tips'] ? "Présent" : "❌ ABSENT") . "\n";
    echo "\n";
}

/**
 * Nettoie le HTML tout en conservant la structure de base
 */
function stripHtmlKeepFormat($text) {
    // Décoder les entités
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Remplacer les balises de bloc par des sauts de ligne
    $text = preg_replace('/<\/?h[1-6][^>]*>/', "\n", $text);
    $text = preg_replace('/<\/?p[^>]*>/', "\n", $text);
    $text = preg_replace('/<\/?div[^>]*>/', "\n", $text);
    $text = preg_replace('/<\/?li[^>]*>/', "\n• ", $text);
    $text = preg_replace('/<\/?[ou]l[^>]*>/', "\n", $text);
    
    // Garder strong/em mais les remplacer par des astérisques
    $text = preg_replace('/<strong[^>]*>/', "**", $text);
    $text = preg_replace('/<\/strong>/', "**", $text);
    $text = preg_replace('/<em[^>]*>/', "_", $text);
    $text = preg_replace('/<\/em>/', "_", $text);
    $text = preg_replace('/<br[^>]*>/', "\n", $text);
    
    // Supprimer les autres balises
    $text = strip_tags($text);
    
    // Nettoyer les espaces excessifs
    $text = preg_replace('/\n+/', "\n", $text);
    $text = preg_replace('/[ \t]+/', " ", $text);
    $text = trim($text);
    
    // Limiter à 1500 caractères pour la lisibilité
    if (mb_strlen($text) > 1500) {
        $text = mb_substr($text, 0, 1500) . "\n\n[... contenu tronqué ...]";
    }
    
    return $text;
}
