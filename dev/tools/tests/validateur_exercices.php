#!/usr/bin/env php
<?php
/**
 * SYSTÈME ANTI-INCOHÉRENCE POUR EXERCICES
 * Validation automatique avant insertion/modification
 * Détection de réponses inadaptées
 */

require_once __DIR__ . '/../config.php';

class ExerciseValidator {
    
    /**
     * Patterns de réponses problématiques
     */
    private static $badPatterns = [
        'placeholder' => [
            'pattern' => '/^(a\)|b\)|c\)|d\))/i',
            'description' => 'Réponse commence par a), b), c), d)'
        ],
        'liste_simple' => [
            'pattern' => '/^[a-d]\).*\n[a-d]\).*\n[a-d]\).*\n[a-d]\).*/i',
            'description' => 'Liste a) b) c) d) sans explication'
        ],
        'vide' => [
            'pattern' => '/^[\s\r\n]*$/',
            'description' => 'Réponse vide ou espaces uniquement'
        ],
        'trop_court' => [
            'pattern' => '/^.{1,15}$/',
            'description' => 'Réponse trop courte (< 15 caractères)'
        ],
        'numero_seul' => [
            'pattern' => '/^[0-9]+$/',
            'description' => 'Réponse = numéro uniquement'
        ],
        'caracteres_etranges' => [
            'pattern' => '/[\x00-\x08\x0B\x0C\x0E-\x1F]/',
            'description' => 'Caractères de contrôle suspects'
        ]
    ];
    
    /**
     * Validation complète d'un exercice
     * 
     * @param array $exercise Tableau avec keys: title, content, answer, level, subject
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array]
     */
    public static function validate($exercise) {
        $errors = [];
        $warnings = [];
        
        // Champs obligatoires
        $required = ['title', 'content', 'answer', 'level', 'subject'];
        foreach ($required as $field) {
            if (empty($exercise[$field])) {
                $errors[] = "Champ obligatoire manquant: $field";
            }
        }
        
        if (!empty($errors)) {
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }
        
        // Validation de la réponse
        $answerCheck = self::validateAnswer($exercise['answer']);
        if (!$answerCheck['valid']) {
            $errors = array_merge($errors, $answerCheck['errors']);
        }
        $warnings = array_merge($warnings, $answerCheck['warnings']);
        
        // Validation du contenu
        $contentCheck = self::validateContent($exercise['content']);
        $warnings = array_merge($warnings, $contentCheck['warnings']);
        
        // Cohérence contenu/réponse
        $coherenceCheck = self::checkCoherence($exercise['content'], $exercise['answer']);
        $warnings = array_merge($warnings, $coherenceCheck['warnings']);
        
        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Validation de la réponse
     */
    private static function validateAnswer($answer) {
        $errors = [];
        $warnings = [];
        
        foreach (self::$badPatterns as $key => $pattern) {
            if (preg_match($pattern['pattern'], $answer)) {
                $errors[] = "⚠️ Réponse problématique détectée: " . $pattern['description'];
            }
        }
        
        // Réponse doit contenir des explications
        if (strlen($answer) < 50) {
            $warnings[] = "💡 Réponse courte (< 50 caractères). Pensez à ajouter des explications.";
        }
        
        // Vérifier si contient "La réponse correcte est"
        if (stripos($answer, 'réponse') === false && stripos($answer, 'answer') === false) {
            $warnings[] = "💡 La réponse devrait commencer par 'La réponse correcte est...'";
        }
        
        return ['valid' => count($errors) === 0, 'errors' => $errors, 'warnings' => $warnings];
    }
    
    /**
     * Validation du contenu
     */
    private static function validateContent($content) {
        $warnings = [];
        
        if (strlen($content) < 20) {
            $warnings[] = "💡 Contenu très court (< 20 caractères)";
        }
        
        // Vérifier si c'est un QCM
        if (preg_match('/a\).*b\).*c\).*d\)/is', $content)) {
            // C'est un QCM, c'est ok
        } elseif (preg_match('/\?/', $content)) {
            // Question sans choix multiples, c'est ok
        } else {
            $warnings[] = "💡 Le contenu ne semble ni être un QCM ni contenir de question";
        }
        
        return ['warnings' => $warnings];
    }
    
    /**
     * Vérifier cohérence contenu/réponse
     */
    private static function checkCoherence($content, $answer) {
        $warnings = [];
        
        // Si QCM, vérifier que la réponse mentionne a), b), c) ou d)
        if (preg_match('/a\).*b\).*c\).*d\)/is', $content)) {
            if (!preg_match('/[abcd]\)/i', $answer)) {
                $warnings[] = "⚠️ QCM détecté mais la réponse ne mentionne pas a), b), c) ou d)";
            }
        }
        
        return ['warnings' => $warnings];
    }
    
    /**
     * Scanner tous les exercices existants
     */
    public static function scanAllExercises(PDO $pdo) {
        $stmt = $pdo->query("
            SELECT Id, Level, Subject, Title, Content, Answer
            FROM Exercises
            WHERE is_active = 1
            ORDER BY Subject, Level, Id
        ");
        
        $results = [
            'total' => 0,
            'valides' => 0,
            'avec_erreurs' => 0,
            'avec_warnings' => 0,
            'details' => []
        ];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results['total']++;
            
            $validation = self::validate([
                'title' => $row['Title'],
                'content' => $row['Content'],
                'answer' => $row['Answer'],
                'level' => $row['Level'],
                'subject' => $row['Subject']
            ]);
            
            if ($validation['valid']) {
                if (count($validation['warnings']) === 0) {
                    $results['valides']++;
                } else {
                    $results['avec_warnings']++;
                }
            } else {
                $results['avec_erreurs']++;
            }
            
            if (!$validation['valid'] || count($validation['warnings']) > 0) {
                $results['details'][] = [
                    'id' => $row['Id'],
                    'level' => $row['Level'],
                    'subject' => $row['Subject'],
                    'title' => $row['Title'],
                    'validation' => $validation
                ];
            }
        }
        
        return $results;
    }
}

// TEST DU SYSTÈME
echo "🔍 SYSTÈME ANTI-INCOHÉRENCE - SCAN COMPLET\n";
echo "============================================================\n\n";

$results = ExerciseValidator::scanAllExercises($pdo);

echo "📊 RÉSULTATS GLOBAUX\n";
echo "============================================================\n\n";
echo "Total exercices scannés : " . $results['total'] . "\n";
echo "✅ Exercices valides     : " . $results['valides'] . " (" . round(($results['valides']/$results['total'])*100, 1) . "%)\n";
echo "⚠️  Avec avertissements  : " . $results['avec_warnings'] . " (" . round(($results['avec_warnings']/$results['total'])*100, 1) . "%)\n";
echo "❌ Avec erreurs         : " . $results['avec_erreurs'] . " (" . round(($results['avec_erreurs']/$results['total'])*100, 1) . "%)\n\n";

if (count($results['details']) > 0) {
    echo "🔎 DÉTAILS DES PROBLÈMES (" . count($results['details']) . " exercices)\n";
    echo "============================================================\n\n";
    
    $shown = 0;
    $maxShow = 20; // Limiter l'affichage
    
    foreach ($results['details'] as $detail) {
        if ($shown >= $maxShow) {
            echo "\n... (" . (count($results['details']) - $maxShow) . " autres exercices avec problèmes)\n";
            break;
        }
        
        echo "ID: {$detail['id']} - {$detail['subject']} - {$detail['level']}\n";
        echo "   Titre: {$detail['title']}\n";
        
        if (count($detail['validation']['errors']) > 0) {
            echo "   ❌ ERREURS:\n";
            foreach ($detail['validation']['errors'] as $error) {
                echo "      - $error\n";
            }
        }
        
        if (count($detail['validation']['warnings']) > 0) {
            echo "   ⚠️  AVERTISSEMENTS:\n";
            foreach ($detail['validation']['warnings'] as $warning) {
                echo "      - $warning\n";
            }
        }
        
        echo "\n";
        $shown++;
    }
}

echo "\n✅ Scan terminé!\n";
echo "💡 Utilisez ExerciseValidator::validate() avant toute insertion/modification.\n";
