<?php
/**
 * Script d'analyse des problèmes d'exercices
 * 
 * Objectifs :
 * 1. Détecter les incohérences de niveau (exercice de 4ème avec contenu mentionnant 6ème)
 * 2. Analyser la structure des exercices (questions, réponses multiples)
 * 3. Identifier les problèmes de parsing
 * 4. Générer un rapport détaillé
 */

require_once __DIR__ . '/../config.php';

// Essayer de charger la connexion
require_once __DIR__ . '/../db/connection.php';

// Si la connexion a échoué, essayer avec la configuration locale
if (!$pdo) {
    echo "⚠️  Première tentative de connexion échouée, essai avec la configuration locale...\n\n";
    
    try {
        // Configuration locale par défaut (XAMPP)
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
        echo "✅ Connexion réussie avec la configuration locale.\n\n";
    } catch (PDOException $e) {
        echo "❌ ERREUR: Impossible de se connecter à la base de données.\n";
        echo "   Erreur: " . $e->getMessage() . "\n";
        echo "   Vérifiez que:\n";
        echo "   - XAMPP est démarré (si en local)\n";
        echo "   - La base de données 'moncoachscolaire' existe\n";
        echo "   - Les identifiants dans .env ou config.php sont corrects\n";
        exit(1);
    }
}

require_once __DIR__ . '/../includes/exercice_loader.php';

// Niveaux valides
$validLevels = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale'];

// Patterns pour détecter les mentions de niveau dans le contenu
$levelPatterns = [
    '6ème' => ['/6[èe]me/i', '/6[èe]me/i', '/sixième/i', '/6e/i'],
    '5ème' => ['/5[èe]me/i', '/5[èe]me/i', '/cinquième/i', '/5e/i'],
    '4ème' => ['/4[èe]me/i', '/4[èe]me/i', '/quatrième/i', '/4e/i'],
    '3ème' => ['/3[èe]me/i', '/3[èe]me/i', '/troisième/i', '/3e/i'],
    'Seconde' => ['/seconde/i', '/2nde/i', '/2nde/i'],
    'Première' => ['/premi[èe]re/i', '/1[èe]re/i', '/1ère/i'],
    'Terminale' => ['/terminale/i', '/bac/i']
];

/**
 * Détecte les mentions de niveau dans un texte
 */
function detectLevelMentions($text, $currentLevel) {
    global $levelPatterns, $validLevels;
    
    $mentions = [];
    foreach ($validLevels as $level) {
        if ($level === $currentLevel) continue; // Ignorer le niveau actuel
        
        foreach ($levelPatterns[$level] as $pattern) {
            if (preg_match($pattern, $text)) {
                $mentions[$level] = true;
                break;
            }
        }
    }
    
    return array_keys($mentions);
}

/**
 * Analyse la structure d'un exercice
 */
function analyzeExerciseStructure($exercise) {
    $content = strip_tags($exercise['Content'] ?? '');
    $answer = strip_tags($exercise['Answer'] ?? '');
    $title = strip_tags($exercise['Title'] ?? '');
    
    $analysis = [
        'hasQuestion' => false,
        'questionCount' => 0,
        'hasMultipleAnswers' => false,
        'answerCount' => 0,
        'contentLength' => mb_strlen($content),
        'answerLength' => mb_strlen($answer),
        'hasQuestionMark' => mb_strpos($content, '?') !== false || mb_strpos($title, '?') !== false,
        'hasNumberedQuestions' => preg_match('/Q\d+|Question\s+\d+/i', $content) > 0,
        'hasChoices' => preg_match('/[A-D]\)|a\)|b\)|c\)|d\)/i', $content) > 0,
        'hasBlanks' => preg_match('/_+|\(_\)|\.\.\./u', $content) > 0,
        'issues' => []
    ];
    
    // Détecter les questions
    if (preg_match_all('/(?:Q\d+|Question\s+\d+)[:\s\.]\s*(.+?)(?:\?|$|\n)/iu', $content, $matches)) {
        $analysis['questionCount'] = count($matches[0]);
        $analysis['hasQuestion'] = true;
    } elseif (preg_match_all('/(.+?\?)/u', $content, $matches)) {
        $analysis['questionCount'] = count($matches[0]);
        $analysis['hasQuestion'] = true;
    } elseif ($analysis['hasQuestionMark']) {
        $analysis['questionCount'] = 1;
        $analysis['hasQuestion'] = true;
    }
    
    // Détecter les réponses multiples
    if (!empty($answer)) {
        // Compter les réponses séparées par des virgules, points-virgules, ou retours à la ligne
        $answerParts = preg_split('/[,;]\s*|\n+/', $answer);
        $answerParts = array_filter(array_map('trim', $answerParts), function($part) {
            return mb_strlen($part) > 2;
        });
        
        $analysis['answerCount'] = count($answerParts);
        $analysis['hasMultipleAnswers'] = $analysis['answerCount'] > 1;
    }
    
    // Identifier les problèmes
    if ($analysis['contentLength'] < 20) {
        $analysis['issues'][] = 'Contenu trop court (< 20 caractères)';
    }
    
    if ($analysis['contentLength'] > 5000) {
        $analysis['issues'][] = 'Contenu très long (> 5000 caractères) - peut causer des problèmes d\'affichage';
    }
    
    if (!$analysis['hasQuestion'] && !$analysis['hasBlanks']) {
        $analysis['issues'][] = 'Aucune question détectée - structure non standard';
    }
    
    if ($analysis['hasMultipleAnswers'] && $analysis['questionCount'] === 1) {
        $analysis['issues'][] = 'Plusieurs réponses pour une seule question - organisation à vérifier';
    }
    
    if ($analysis['questionCount'] > 1 && !$analysis['hasNumberedQuestions']) {
        $analysis['issues'][] = 'Plusieurs questions détectées mais non numérotées - organisation à améliorer';
    }
    
    return $analysis;
}

// Charger tous les exercices
try {
    // Vérifier que la table existe
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'Exercises'");
    if ($tableCheck->rowCount() === 0) {
        echo "❌ ERREUR: La table 'Exercises' n'existe pas dans la base de données.\n";
        echo "   Veuillez créer la table ou importer les exercices.\n";
        exit(1);
    }
    
    $stmt = $pdo->query("SELECT * FROM Exercises ORDER BY Level, Subject, Id");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== ANALYSE DES EXERCICES ===\n\n";
    echo "Total d'exercices analysés : " . count($exercises) . "\n\n";
    
    // Statistiques globales
    $stats = [
        'byLevel' => [],
        'issues' => [
            'levelInconsistencies' => [],
            'structureIssues' => [],
            'parsingIssues' => []
        ]
    ];
    
    // Analyser chaque exercice
    foreach ($exercises as $exercise) {
        $level = $exercise['Level'] ?? 'Inconnu';
        $subject = $exercise['Subject'] ?? 'Inconnu';
        $id = $exercise['Id'] ?? 0;
        
        // Statistiques par niveau
        if (!isset($stats['byLevel'][$level])) {
            $stats['byLevel'][$level] = 0;
        }
        $stats['byLevel'][$level]++;
        
        // Analyser le contenu pour détecter les mentions de niveau
        $content = strip_tags($exercise['Content'] ?? '');
        $title = strip_tags($exercise['Title'] ?? '');
        $fullText = $title . ' ' . $content;
        
        $levelMentions = detectLevelMentions($fullText, $level);
        
        if (!empty($levelMentions)) {
            $stats['issues']['levelInconsistencies'][] = [
                'id' => $id,
                'level' => $level,
                'subject' => $subject,
                'title' => mb_substr($title, 0, 60),
                'mentions' => $levelMentions,
                'severity' => in_array($levelMentions[0], ['6ème', '5ème', '4ème', '3ème']) && 
                             in_array($level, ['6ème', '5ème', '4ème', '3ème']) ? 'warning' : 'error'
            ];
        }
        
        // Analyser la structure
        $structure = analyzeExerciseStructure($exercise);
        
        if (!empty($structure['issues'])) {
            $stats['issues']['structureIssues'][] = [
                'id' => $id,
                'level' => $level,
                'subject' => $subject,
                'title' => mb_substr($title, 0, 60),
                'issues' => $structure['issues'],
                'structure' => $structure
            ];
        }
        
        // Problèmes de parsing potentiels
        if ($structure['hasMultipleAnswers'] && $structure['questionCount'] === 1) {
            $stats['issues']['parsingIssues'][] = [
                'id' => $id,
                'level' => $level,
                'subject' => $subject,
                'title' => mb_substr($title, 0, 60),
                'issue' => 'Plusieurs réponses pour une seule question - parsing complexe requis'
            ];
        }
    }
    
    // Afficher les statistiques par niveau
    echo "=== RÉPARTITION PAR NIVEAU ===\n";
    foreach ($stats['byLevel'] as $level => $count) {
        echo sprintf("  %-15s : %3d exercices\n", $level, $count);
    }
    echo "\n";
    
    // Afficher les incohérences de niveau
    echo "=== INCOHÉRENCES DE NIVEAU ===\n";
    if (empty($stats['issues']['levelInconsistencies'])) {
        echo "✅ Aucune incohérence détectée\n\n";
    } else {
        echo "⚠️ " . count($stats['issues']['levelInconsistencies']) . " exercice(s) avec incohérence(s) de niveau\n\n";
        
        foreach ($stats['issues']['levelInconsistencies'] as $issue) {
            $severity = $issue['severity'] === 'error' ? '❌' : '⚠️';
            echo sprintf("%s ID %d | Niveau: %s | Matière: %s\n", 
                $severity, $issue['id'], $issue['level'], $issue['subject']);
            echo sprintf("   Titre: %s\n", $issue['title']);
            echo sprintf("   Mentionne: %s\n", implode(', ', $issue['mentions']));
            echo "\n";
        }
    }
    
    // Afficher les problèmes de structure
    echo "=== PROBLÈMES DE STRUCTURE ===\n";
    if (empty($stats['issues']['structureIssues'])) {
        echo "✅ Aucun problème de structure détecté\n\n";
    } else {
        echo "⚠️ " . count($stats['issues']['structureIssues']) . " exercice(s) avec problème(s) de structure\n\n";
        
        // Grouper par type de problème
        $issuesByType = [];
        foreach ($stats['issues']['structureIssues'] as $issue) {
            foreach ($issue['issues'] as $issueType) {
                if (!isset($issuesByType[$issueType])) {
                    $issuesByType[$issueType] = [];
                }
                $issuesByType[$issueType][] = $issue;
            }
        }
        
        foreach ($issuesByType as $issueType => $exercises) {
            echo sprintf("  %s (%d exercice(s))\n", $issueType, count($exercises));
            foreach (array_slice($exercises, 0, 5) as $exercise) { // Limiter à 5 exemples
                echo sprintf("    - ID %d | %s | %s\n", 
                    $exercise['id'], $exercise['level'], mb_substr($exercise['title'], 0, 50));
            }
            if (count($exercises) > 5) {
                echo sprintf("    ... et %d autre(s)\n", count($exercises) - 5);
            }
            echo "\n";
        }
    }
    
    // Afficher les problèmes de parsing
    echo "=== PROBLÈMES DE PARSING ===\n";
    if (empty($stats['issues']['parsingIssues'])) {
        echo "✅ Aucun problème de parsing détecté\n\n";
    } else {
        echo "⚠️ " . count($stats['issues']['parsingIssues']) . " exercice(s) avec problème(s) de parsing\n\n";
        
        foreach (array_slice($stats['issues']['parsingIssues'], 0, 10) as $issue) {
            echo sprintf("  ID %d | %s | %s\n", 
                $issue['id'], $issue['level'], $issue['subject']);
            echo sprintf("    %s\n", $issue['title']);
            echo sprintf("    Problème: %s\n", $issue['issue']);
            echo "\n";
        }
        
        if (count($stats['issues']['parsingIssues']) > 10) {
            echo sprintf("  ... et %d autre(s)\n\n", count($stats['issues']['parsingIssues']) - 10);
        }
    }
    
    // Recommandations
    echo "=== RECOMMANDATIONS ===\n\n";
    
    if (!empty($stats['issues']['levelInconsistencies'])) {
        echo "1. CORRIGER LES INCOHÉRENCES DE NIVEAU\n";
        echo "   - Vérifier manuellement les exercices listés ci-dessus\n";
        echo "   - Corriger le niveau dans la BDD OU corriger le contenu\n";
        echo "   - Priorité: " . count(array_filter($stats['issues']['levelInconsistencies'], function($i) { 
            return $i['severity'] === 'error'; 
        })) . " erreur(s) critique(s)\n\n";
    }
    
    if (!empty($stats['issues']['structureIssues'])) {
        echo "2. AMÉLIORER LA STRUCTURE DES EXERCICES\n";
        echo "   - Standardiser le format des questions (Q1:, Q2:, etc.)\n";
        echo "   - Organiser les réponses multiples clairement\n";
        echo "   - Vérifier que les textes longs sont bien formatés\n\n";
    }
    
    if (!empty($stats['issues']['parsingIssues'])) {
        echo "3. AMÉLIORER LE PARSING\n";
        echo "   - Adapter le parser pour gérer les réponses multiples\n";
        echo "   - Créer des règles spécifiques par type d'exercice\n";
        echo "   - Tester le parsing sur les exercices problématiques\n\n";
    }
    
    echo "=== FIN DE L'ANALYSE ===\n";
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    error_log("Erreur analyse exercices: " . $e->getMessage());
}
