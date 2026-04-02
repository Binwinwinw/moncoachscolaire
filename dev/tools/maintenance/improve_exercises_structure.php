<?php
/**
 * Script d'amélioration de la structure des exercices
 * 
 * Améliore :
 * 1. Standardise le format des questions (Q1:, Q2:, etc.)
 * 2. Organise les réponses multiples clairement
 * 3. Corrige les exercices avec contenu trop court
 * 4. Améliore la détection et l'organisation des questions
 */

require_once __DIR__ . '/../config.php';

// Essayer de charger la connexion
require_once __DIR__ . '/../db/connection.php';

// Si la connexion a échoué, essayer avec la configuration locale
if (!$pdo) {
    try {
        $localHost = '127.0.0.1';
        $localDb = 'moncoachscolaire';
        $localUser = 'root';
        $localPass = '';
        
        $dsn = "mysql:host={$localHost};dbname={$localDb};charset=utf8mb4";
        $pdo = new PDO($dsn, $localUser, $localPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        echo "❌ ERREUR: Impossible de se connecter à la base de données.\n";
        exit(1);
    }
}

/**
 * Numérote les questions dans le contenu
 */
function numberQuestions($content) {
    $lines = explode("\n", $content);
    $numberedLines = [];
    $questionNumber = 1;
    $inQuestion = false;
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Détecter une question (se termine par ? ou commence par certains mots)
        $isQuestion = false;
        if (preg_match('/^[A-Z][^?!]*[?]/u', $trimmed) || 
            preg_match('/^(Quelle|Quel|Quels|Quelles|Comment|Pourquoi|Quand|Où|Combien)/iu', $trimmed)) {
            $isQuestion = true;
        }
        
        // Si c'est une question et qu'elle n'est pas déjà numérotée
        if ($isQuestion && !preg_match('/^Q\d+[:\.]/i', $trimmed) && 
            !preg_match('/^Question\s+\d+/i', $trimmed)) {
            // Ajouter le numéro
            $numberedLines[] = "Q" . $questionNumber . ": " . $trimmed;
            $questionNumber++;
            $inQuestion = true;
        } else {
            $numberedLines[] = $line;
            // Si on trouve une réponse ou un nouveau paragraphe, on sort de la question
            if (preg_match('/^(Réponse|Solution|Correction|a\)|b\)|c\)|d\))/i', $trimmed)) {
                $inQuestion = false;
            }
        }
    }
    
    return implode("\n", $numberedLines);
}

/**
 * Organise les réponses multiples par question
 */
function organizeMultipleAnswers($answer, $questionCount) {
    if (empty($answer)) {
        return $answer;
    }
    
    // Si une seule question, retourner tel quel
    if ($questionCount <= 1) {
        return $answer;
    }
    
    // Détecter si les réponses sont déjà organisées (Q1:, Q2:, etc.)
    if (preg_match('/Q\d+[:\.]/i', $answer)) {
        return $answer; // Déjà organisé
    }
    
    // Séparer les réponses (par virgule, point-virgule, ou nouvelle ligne)
    $answerParts = preg_split('/[,;]\s*|\n+/', $answer);
    $answerParts = array_filter(array_map('trim', $answerParts), function($part) {
        return mb_strlen($part) > 2;
    });
    
    // Si on a plusieurs réponses, les organiser
    if (count($answerParts) > 1) {
        $organized = [];
        $index = 1;
        foreach ($answerParts as $part) {
            $organized[] = "Q" . $index . ": " . $part;
            $index++;
        }
        return implode("\n", $organized);
    }
    
    return $answer;
}

/**
 * Améliore le contenu d'un exercice
 */
function improveExerciseContent($content, $answer) {
    $improved = $content;
    
    // Compter les questions
    $questionCount = 0;
    if (preg_match_all('/(?:Q\d+|Question\s+\d+)[:\s\.]\s*(.+?)(?:\?|$|\n)/iu', $content, $matches)) {
        $questionCount = count($matches[0]);
    } elseif (preg_match_all('/(.+?\?)/u', $content, $matches)) {
        $questionCount = count($matches[0]);
    }
    
    // Si plusieurs questions détectées mais non numérotées
    if ($questionCount > 1 && !preg_match('/Q\d+[:\.]/i', $content)) {
        $improved = numberQuestions($content);
    }
    
    // Organiser les réponses multiples
    if (!empty($answer) && $questionCount > 1) {
        $answer = organizeMultipleAnswers($answer, $questionCount);
    }
    
    return ['content' => $improved, 'answer' => $answer];
}

/**
 * Corrige un contenu trop court
 */
function fixShortContent($content, $title) {
    if (mb_strlen(strip_tags($content)) < 20) {
        // Si le contenu est trop court, utiliser le titre comme base
        if (!empty($title) && mb_strlen($title) > 10) {
            return $title . "\n\n" . $content;
        }
    }
    return $content;
}

echo "=== AMÉLIORATION DE LA STRUCTURE DES EXERCICES ===\n\n";
echo "⚠️  ATTENTION: Ce script va modifier la base de données.\n";
echo "   Assurez-vous d'avoir fait une sauvegarde avant de continuer.\n\n";

// Mode dry-run par défaut
$dryRun = true;
if (isset($argv[1]) && $argv[1] === '--apply') {
    $dryRun = false;
    echo "✅ Mode APPLY activé - Les modifications seront effectuées.\n\n";
} else {
    echo "🔍 Mode DRY-RUN - Aucune modification ne sera effectuée.\n";
    echo "   Utilisez --apply pour appliquer les améliorations.\n\n";
}

// Charger tous les exercices
$stmt = $pdo->query("SELECT * FROM Exercises ORDER BY Id");
$exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

$improvements = [];
$stats = [
    'questions_numbered' => 0,
    'answers_organized' => 0,
    'short_content_fixed' => 0,
    'total_improved' => 0
];

foreach ($exercises as $exercise) {
    $id = $exercise['Id'];
    $originalContent = $exercise['Content'] ?? '';
    $originalAnswer = $exercise['Answer'] ?? '';
    $title = $exercise['Title'] ?? '';
    
    $needsImprovement = false;
    $improvement = [
        'id' => $id,
        'level' => $exercise['Level'] ?? '',
        'subject' => $exercise['Subject'] ?? '',
        'title' => mb_substr($title, 0, 50),
        'changes' => []
    ];
    
    // Vérifier si le contenu est trop court
    if (mb_strlen(strip_tags($originalContent)) < 20) {
        $fixedContent = fixShortContent($originalContent, $title);
        if ($fixedContent !== $originalContent) {
            $improvement['content'] = $fixedContent;
            $improvement['changes'][] = 'Contenu trop court corrigé';
            $needsImprovement = true;
            $stats['short_content_fixed']++;
        }
    }
    
    // Compter les questions
    $questionCount = 0;
    if (preg_match_all('/(?:Q\d+|Question\s+\d+)[:\s\.]\s*(.+?)(?:\?|$|\n)/iu', $originalContent, $matches)) {
        $questionCount = count($matches[0]);
    } elseif (preg_match_all('/(.+?\?)/u', $originalContent, $matches)) {
        $questionCount = count($matches[0]);
    }
    
    // Améliorer le contenu
    $contentToImprove = $improvement['content'] ?? $originalContent;
    $result = improveExerciseContent($contentToImprove, $originalAnswer);
    
    if ($result['content'] !== $contentToImprove) {
        $improvement['content'] = $result['content'];
        $improvement['changes'][] = 'Questions numérotées';
        $needsImprovement = true;
        $stats['questions_numbered']++;
    }
    
    if ($result['answer'] !== $originalAnswer && !empty($result['answer'])) {
        $improvement['answer'] = $result['answer'];
        $improvement['changes'][] = 'Réponses organisées';
        $needsImprovement = true;
        $stats['answers_organized']++;
    }
    
    if ($needsImprovement) {
        $improvements[] = $improvement;
        $stats['total_improved']++;
    }
}

if (empty($improvements)) {
    echo "✅ Aucune amélioration nécessaire.\n";
    exit(0);
}

echo "📋 " . count($improvements) . " exercice(s) à améliorer:\n\n";

// Afficher les statistiques
echo "=== STATISTIQUES ===\n";
echo sprintf("  Questions numérotées: %d\n", $stats['questions_numbered']);
echo sprintf("  Réponses organisées: %d\n", $stats['answers_organized']);
echo sprintf("  Contenus courts corrigés: %d\n", $stats['short_content_fixed']);
echo sprintf("  Total d'exercices améliorés: %d\n\n", $stats['total_improved']);

// Afficher quelques exemples
echo "=== EXEMPLES D'AMÉLIORATIONS ===\n\n";
$examplesShown = 0;
foreach (array_slice($improvements, 0, 5) as $improvement) {
    echo sprintf("ID %d | %s | %s\n", 
        $improvement['id'],
        $improvement['level'],
        $improvement['subject']
    );
    echo sprintf("  Titre: %s\n", $improvement['title']);
    echo sprintf("  Améliorations: %s\n", implode(', ', $improvement['changes']));
    echo "\n";
    $examplesShown++;
}

if (count($improvements) > $examplesShown) {
    echo sprintf("  ... et %d autre(s) exercice(s)\n\n", count($improvements) - $examplesShown);
}

if ($dryRun) {
    echo "🔍 Mode DRY-RUN: Aucune modification effectuée.\n";
    echo "   Pour appliquer ces améliorations, exécutez:\n";
    echo "   php tools/improve_exercises_structure.php --apply\n";
} else {
    echo "🔧 Application des améliorations...\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($improvements as $improvement) {
        try {
            $updates = [];
            $params = [];
            
            if (isset($improvement['content'])) {
                $updates[] = "Content = ?";
                $params[] = $improvement['content'];
            }
            
            if (isset($improvement['answer'])) {
                $updates[] = "Answer = ?";
                $params[] = $improvement['answer'];
            }
            
            if (!empty($updates)) {
                $params[] = $improvement['id'];
                $sql = "UPDATE Exercises SET " . implode(", ", $updates) . " WHERE Id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $successCount++;
                
                echo sprintf("✅ ID %d: %s\n", 
                    $improvement['id'],
                    implode(', ', $improvement['changes'])
                );
            }
        } catch (PDOException $e) {
            $errorCount++;
            echo sprintf("❌ ID %d: Erreur - %s\n", $improvement['id'], $e->getMessage());
        }
    }
    
    echo "\n=== RÉSULTAT ===\n";
    echo sprintf("✅ %d amélioration(s) appliquée(s)\n", $successCount);
    if ($errorCount > 0) {
        echo sprintf("❌ %d erreur(s)\n", $errorCount);
    }
    
    echo "\n💡 Conseil: Ré-exécutez tools/analyze_exercises_issues.php pour vérifier les améliorations.\n";
}
