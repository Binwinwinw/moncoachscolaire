<?php
/**
 * Vérification complète du parsing et de la répartition
 * Content / Answer / Tips
 */

require_once __DIR__ . '/../src/database/connection.php';

if (!$pdo) {
    die("❌ Erreur: Impossible de se connecter à la base de données.\n");
}

echo "🔍 VÉRIFICATION COMPLÈTE DU PARSING CONTENT/ANSWER/TIPS\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Vérifier la structure des colonnes
echo "1️⃣  STRUCTURE DE LA TABLE EXERCISES\n";
echo str_repeat("-", 80) . "\n";
$cols = $pdo->query("DESCRIBE Exercises")->fetchAll();
foreach ($cols as $col) {
    if (in_array($col['Field'], ['Id', 'Title', 'Content', 'Answer', 'Tips'])) {
        $null = $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL';
        echo "  {$col['Field']}: {$col['Type']} ($null)\n";
    }
}
echo "\n";

// 2. Statistiques globales
echo "2️⃣  STATISTIQUES GLOBALES\n";
echo str_repeat("-", 80) . "\n";

$stats = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN Content IS NOT NULL AND Content != '' THEN 1 ELSE 0 END) as has_content,
    SUM(CASE WHEN Answer IS NOT NULL AND Answer != '' THEN 1 ELSE 0 END) as has_answer,
    SUM(CASE WHEN Tips IS NOT NULL AND Tips != '' THEN 1 ELSE 0 END) as has_tips
FROM Exercises")->fetch();

echo "  Total exercices: {$stats['total']}\n";
echo "  Avec Content: {$stats['has_content']} (" . round(($stats['has_content'] / $stats['total']) * 100, 1) . "%)\n";
echo "  Avec Answer: {$stats['has_answer']} (" . round(($stats['has_answer'] / $stats['total']) * 100, 1) . "%)\n";
echo "  Avec Tips: {$stats['has_tips']} (" . round(($stats['has_tips'] / $stats['total']) * 100, 1) . "%)\n";
echo "\n";

// 3. Exemples avec tips extraits
echo "3️⃣  EXEMPLES D'EXERCICES AVEC TIPS EXTRAITS\n";
echo str_repeat("-", 80) . "\n";

$withTips = $pdo->query("
    SELECT Id, Title, Content, Answer, Tips 
    FROM Exercises 
    WHERE Tips IS NOT NULL AND Tips != '' 
    LIMIT 3
")->fetchAll();

foreach ($withTips as $idx => $ex) {
    echo "\n🎯 Exercice #{$ex['Id']}: {$ex['Title']}\n";
    echo "\n📖 CONTENT (" . mb_strlen($ex['Content']) . " chars):\n";
    echo "   " . substr(strip_tags($ex['Content']), 0, 120) . "...\n";
    
    echo "\n💡 ANSWER (" . mb_strlen($ex['Answer']) . " chars):\n";
    echo "   " . substr($ex['Answer'], 0, 120) . "...\n";
    
    echo "\n✨ TIPS (" . mb_strlen($ex['Tips']) . " chars):\n";
    echo "   " . substr(strip_tags($ex['Tips']), 0, 120) . "...\n";
    
    echo "\n" . str_repeat("-", 80) . "\n";
}

// 4. Exemple sans tips
echo "\n4️⃣  EXEMPLE D'EXERCICE SANS TIPS\n";
echo str_repeat("-", 80) . "\n";

$noTips = $pdo->query("
    SELECT Id, Title, Content, Answer, Tips 
    FROM Exercises 
    WHERE Tips IS NULL OR Tips = '' 
    LIMIT 1
")->fetch();

if ($noTips) {
    echo "\n🎯 Exercice #{$noTips['Id']}: {$noTips['Title']}\n";
    echo "\n📖 CONTENT (" . mb_strlen($noTips['Content']) . " chars):\n";
    echo "   " . substr(strip_tags($noTips['Content']), 0, 120) . "...\n";
    
    echo "\n💡 ANSWER (" . mb_strlen($noTips['Answer']) . " chars):\n";
    echo "   " . substr($noTips['Answer'], 0, 120) . "...\n";
    
    echo "\n✨ TIPS: (aucun)\n";
    echo "   Tips = NULL ou vide\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "✅ VÉRIFICATION TERMINÉE\n";
