#!/usr/bin/env php
<?php
/**
 * Script pour vérifier la qualité des réponses d'exercices
 */

require_once __DIR__ . '/../config.php';

echo "🔍 VÉRIFICATION DES RÉPONSES D'EXERCICES\n";
echo "============================================================\n\n";

// Récupérer tous les exercices
$stmt = $pdo->query('SELECT Id, Title, Subject, Level, Answer FROM Exercises ORDER BY Id');
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$problematicAnswers = [];
$patterns = [
    'placeholder' => '/^(a\)|b\)|c\)|d\))/i',
    'vide' => '/^[\s\r\n]*$/',
    'trop_court' => '/^.{1,10}$/',
    'html_brut' => '/<[^>]+>/',
    'liste_simple' => '/^[a-d]\).*\n[a-d]\).*\n[a-d]\).*\n[a-d]\).*/i',
    'numero_seul' => '/^[0-9]+$/',
    'caractere_bizarre' => '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
];

foreach ($exercises as $exercise) {
    $answer = trim($exercise['Answer'] ?? '');
    $issues = [];
    
    // Vérifications
    if (empty($answer)) {
        $issues[] = 'VIDE';
    }
    
    if (preg_match($patterns['placeholder'], $answer)) {
        $issues[] = 'PLACEHOLDER (a), b), c)...)';
    }
    
    if (strlen($answer) > 0 && strlen($answer) < 15) {
        $issues[] = 'TROP COURT (<15 caractères)';
    }
    
    if (preg_match($patterns['numero_seul'], $answer)) {
        $issues[] = 'NUMERO SEUL';
    }
    
    if (preg_match($patterns['liste_simple'], $answer)) {
        $issues[] = 'LISTE a) b) c) d)';
    }
    
    if (preg_match($patterns['caractere_bizarre'], $answer)) {
        $issues[] = 'CARACTÈRES BIZARRES';
    }
    
    // Si problème détecté
    if (!empty($issues)) {
        $problematicAnswers[] = [
            'id' => $exercise['Id'],
            'title' => $exercise['Title'],
            'subject' => $exercise['Subject'],
            'level' => $exercise['Level'],
            'answer' => substr($answer, 0, 150),
            'issues' => $issues
        ];
    }
}

// Afficher les résultats
echo "Exercices vérifiés: " . count($exercises) . "\n";
echo "Exercices problématiques: " . count($problematicAnswers) . "\n\n";

if (!empty($problematicAnswers)) {
    echo "📋 LISTE DES EXERCICES PROBLÉMATIQUES\n";
    echo "============================================================\n\n";
    
    foreach ($problematicAnswers as $item) {
        echo "🔴 ID: " . $item['id'] . " | " . $item['level'] . " - " . $item['subject'] . "\n";
        echo "   Titre: " . $item['title'] . "\n";
        echo "   Problèmes: " . implode(', ', $item['issues']) . "\n";
        echo "   Réponse actuelle: " . $item['answer'] . "\n";
        echo "\n";
    }
} else {
    echo "✅ Aucun exercice problématique détecté !\n";
}

echo "\n============================================================\n";
echo "Fin de la vérification\n";
