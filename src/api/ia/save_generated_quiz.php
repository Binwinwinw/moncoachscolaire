<?php

/**
 * Endpoint pour sauvegarder un quiz généré par l'IA dans la bibliothèque d'exercices
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/exercice_loader.php';

// Optionnel : restreindre aux utilisateurs connectés
session_start();
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Veuillez vous connecter pour sauvegarder un quiz.']);
    exit;
}

try {
    // Récupérer les données POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    $questions = $input['questions'] ?? [];
    $subject = $input['subject'] ?? '';
    $level = $input['level'] ?? '';

    if (empty($questions) || !is_array($questions)) {
        throw new Exception('Aucune question à sauvegarder.');
    }

    if (empty($subject) || empty($level)) {
        throw new Exception('Sujet ou niveau manquant.');
    }

    global $pdo;
    if (!$pdo) {
        throw new Exception('Connexion à la base de données impossible.');
    }

    // Normalisation du niveau et de la matière pour la BDD
    if (function_exists('normalizeLevelForDB')) {
        $levelDB = normalizeLevelForDB($level);
    } else {
        $levelDB = $level;
    }
    
    if (function_exists('normalizeSubject')) {
        $subjectDB = normalizeSubject($subject);
    } else {
        $subjectDB = $subject;
    }

    $insertedCount = 0;
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO exercises (
            Title, Content, Answer, Subject, Level, 
            AnswerType, Choices, is_active, created_at
        ) VALUES (
            :title, :content, :answer, :subject, :level, 
            'choix', :choices, 1, NOW()
        )
    ");

    foreach ($questions as $index => $q) {
        $qText = $q['question'] ?? '';
        $choices = $q['choices'] ?? [];
        $correctKey = $q['correct'] ?? '';
        
        if (empty($qText)) continue;

        // Trouver le label de la réponse correcte
        $correctLabel = '';
        foreach ($choices as $choice) {
            if ($choice['value'] === $correctKey) {
                $correctLabel = $choice['label'];
                break;
            }
        }

        $title = "Quiz IA - " . $subjectDB . " (" . $levelDB . ") - Q" . ($index + 1);

        $stmt->execute([
            'title' => $title,
            'content' => $qText,
            'answer' => $correctLabel,
            'subject' => $subjectDB,
            'level' => $levelDB,
            'choices' => json_encode($choices, JSON_UNESCAPED_UNICODE)
        ]);
        
        $insertedCount++;
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "$insertedCount questions ont été ajoutées à la bibliothèque.",
        'count' => $insertedCount
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
