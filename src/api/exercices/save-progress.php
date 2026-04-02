<?php

/**
 * API universelle pour sauvegarder la progression
 * Compatible local (XAMPP) et production
 * Avec fallback localStorage si BDD indisponible
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../../includes/demo_security.php';
header('Content-Type: application/json; charset=utf-8');

api_require([
    'method' => 'POST',
    'auth' => true,
    'roles' => ['student', 'admin'],
    'csrf' => true,
    'rate' => ['key' => 'save_progress', 'limit' => 30, 'window' => 60],
]);

// SÉCURITÉ CRITIQUE : Bloquer toutes les écritures pour le compte démo
if (isDemoUser()) {
    json_error('Mode démo : sauvegarde désactivée', 403, 'ERR_DEMO');
}

$userId = (int) $_SESSION['user_id'];

// Récupérer les données POST JSON
$data = api_get_json_body(true);
validate_allowed_fields($data, ['exerciseId', 'score', 'correct', 'xp', 'cristaux', 'subject']);

$exerciseId = isset($data['exerciseId']) ? validate_int($data['exerciseId'], 'exerciseId', 1) : null;
$score = isset($data['score']) ? validate_int($data['score'], 'score', 0, 100) : 0;
$correct = isset($data['correct']) ? validate_bool($data['correct'], 'correct') : ($score >= 50);
$xpGained = isset($data['xp']) ? validate_int($data['xp'], 'xp', 0, 1000) : ($correct ? 15 : 0);
$cristaux = isset($data['cristaux']) ? validate_int($data['cristaux'], 'cristaux', 0, 1000) : ($correct ? 1 : 0);
$subject = isset($data['subject']) ? validate_string($data['subject'], 'subject', 80) : null;

// Vérifier si la BDD est disponible
if (!isset($pdo) || !$pdo) {
    // BDD indisponible → fallback fichier local (comme save_progress.php)
    $storeDir = __DIR__ . '/../data';
    if (!is_dir($storeDir)) {
        @mkdir($storeDir, 0755, true);
    }
    $file = $storeDir . '/unsaved_progress.json';
    $payload = [
        'exerciseId' => $exerciseId,
        'score' => $score,
        'correct' => $correct,
        'userId' => $userId,
        'xp' => $xpGained,
        'cristaux' => $cristaux,
        'subject' => $subject,
        'at' => date('c'),
    ];
    $arr = [];
    if (file_exists($file)) {
        $cur = @file_get_contents($file);
        $arr = json_decode($cur, true) ?: [];
    }
    $arr[] = $payload;
    @file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT));
    json_response([
        'saved' => 'file',
        'message' => 'Progression sauvegardée localement (fallback)',
    ], 202);
}

// Charger les fonctions de gamification si disponibles
if (!function_exists('completeExercise')) {
    if (file_exists(__DIR__ . '/../includes/gamification.php')) {
        require_once __DIR__ . '/../includes/gamification.php';
    }
}

try {
    // Vérifier si l'exercice existe (si exerciseId fourni)
    if ($exerciseId && $exerciseId > 0) {
        // Vérifier que l'exercice existe dans la base
        $stmt = $pdo->prepare('SELECT Id FROM exercises WHERE Id = ?');
        $stmt->execute([$exerciseId]);
        $exerciseExists = $stmt->fetch();

        if (!$exerciseExists) {
            // Exercice n'existe pas en BDD → peut être un exercice statique
            // On accepte quand même la sauvegarde mais avec un ID spécial
            // ou on retourne un message informatif
            json_error('Exercice non trouvé dans la base de données', 404, 'ERR_NOT_FOUND');
        }
    }

    // Si la fonction completeExercise existe, l'utiliser
    if (function_exists('completeExercise') && $exerciseId && $exerciseId > 0) {
        $rewards = [
            'cristaux' => $cristaux,
            'xp' => $xpGained,
            'badge' => null,
        ];

        $result = completeExercise($userId, $exerciseId, $correct, $rewards);

        if ($result['success']) {
            // Récupérer la progression mise à jour
            $progress = getUserProgress($userId);

            json_response([
                'xp' => $result['xp'] ?? $xpGained,
                'cristaux' => $result['cristaux'] ?? $cristaux,
                'badge_unlocked' => $result['badge_unlocked'] ?? false,
                'badge_name' => $result['badge_name'] ?? null,
                'progress' => $progress,
                'message' => $result['message'] ?? 'Progression sauvegardée',
            ]);
        } else {
            json_error($result['error'] ?? 'Erreur inconnue', 200, 'ERR_PROGRESS');
        }
    }

    // Sinon, sauvegarde manuelle (si exerciseId valide)
    if ($exerciseId && $exerciseId > 0) {
        // 1. Sauvegarder dans ExerciseResponses
        // Vérifier si déjà existant pour éviter les doublons
        $stmt = $pdo->prepare('
            SELECT Id FROM ExerciseResponses
            WHERE UserId = ? AND ExerciseId = ?
            ORDER BY SubmittedAt DESC LIMIT 1
        ');
        $stmt->execute([$userId, $exerciseId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Mettre à jour
            $stmt = $pdo->prepare('
                UPDATE ExerciseResponses
                SET Correct = ?, Score = ?, SubmittedAt = NOW()
                WHERE Id = ?
            ');
            $stmt->execute([$correct ? 1 : 0, $score, $existing['Id']]);
        } else {
            // Insérer
            $stmt = $pdo->prepare('
                INSERT INTO ExerciseResponses (UserId, ExerciseId, Correct, Score, SubmittedAt)
                VALUES (?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$userId, $exerciseId, $correct ? 1 : 0, $score]);
        }

        // 2. Mettre à jour UserProgress (XP)
        if ($correct && $xpGained > 0) {
            $stmt = $pdo->prepare('
                SELECT Id, XP FROM UserProgress WHERE UserId = ?
            ');
            $stmt->execute([$userId]);
            $userProgress = $stmt->fetch();

            if ($userProgress) {
                // Mettre à jour
                $newXP = ($userProgress['XP'] ?? 0) + $xpGained;
                $stmt = $pdo->prepare('
                    UPDATE UserProgress
                    SET XP = ?, UpdatedAt = NOW()
                    WHERE UserId = ?
                ');
                $stmt->execute([$newXP, $userId]);
            } else {
                // Créer
                $stmt = $pdo->prepare('
                    INSERT INTO UserProgress (UserId, XP, UpdatedAt)
                    VALUES (?, ?, NOW())
                ');
                $stmt->execute([$userId, $xpGained]);
            }
        }

        // 3. Sauvegarder les cristaux dans ProgressJson
        if ($correct && $cristaux > 0) {
            $stmt = $pdo->prepare('SELECT Id, ProgressJson FROM UserProgress WHERE UserId = ?');
            $stmt->execute([$userId]);
            $progress = $stmt->fetch();

            $jsonData = ['cristaux' => $cristaux, 'cristaux_by_subject' => []];

            if ($progress && $progress['ProgressJson']) {
                $existing = json_decode($progress['ProgressJson'], true);
                if ($existing) {
                    $jsonData['cristaux'] = ($existing['cristaux'] ?? 0) + $cristaux;
                    $jsonData['cristaux_by_subject'] = $existing['cristaux_by_subject'] ?? [];
                    if ($subject) {
                        $jsonData['cristaux_by_subject'][$subject]
                            = ($jsonData['cristaux_by_subject'][$subject] ?? 0) + $cristaux;
                    }
                }
            }

            $jsonString = json_encode($jsonData);

            if ($progress) {
                // Mettre à jour
                $stmt = $pdo->prepare('
                    UPDATE UserProgress
                    SET ProgressJson = ?, UpdatedAt = NOW()
                    WHERE UserId = ?
                ');
                $stmt->execute([$jsonString, $userId]);
            } else {
                // Créer avec cristaux
                $stmt = $pdo->prepare('
                    INSERT INTO UserProgress (UserId, ProgressJson, UpdatedAt)
                    VALUES (?, ?, NOW())
                ');
                $stmt->execute([$userId, $jsonString]);
            }
        }

        // Récupérer la progression mise à jour
        $stmt = $pdo->prepare('SELECT XP, ProgressJson FROM UserProgress WHERE UserId = ?');
        $stmt->execute([$userId]);
        $progress = $stmt->fetch();

        $cristauxTotal = 0;
        if ($progress && $progress['ProgressJson']) {
            $json = json_decode($progress['ProgressJson'], true);
            $cristauxTotal = $json['cristaux'] ?? 0;
        }

        json_response([
            'xp' => (int) ($progress['XP'] ?? 0),
            'cristaux' => $cristauxTotal,
            'badge_unlocked' => false,
            'message' => 'Progression sauvegardée avec succès',
        ]);
    } else {
        // Pas d'exerciseId valide
        json_error('ID exercice invalide', 400, 'ERR_VALIDATION');
    }
} catch (PDOException $e) {
    error_log("Erreur sauvegarde progression: " . $e->getMessage());
    // Fallback fichier local
    $storeDir = __DIR__ . '/../data';
    if (!is_dir($storeDir)) {
        @mkdir($storeDir, 0755, true);
    }
    $file = $storeDir . '/unsaved_progress.json';
    $payload = [
        'exerciseId' => $exerciseId,
        'score' => $score,
        'correct' => $correct,
        'userId' => $userId,
        'xp' => $xpGained,
        'cristaux' => $cristaux,
        'subject' => $subject,
        'at' => date('c'),
    ];
    $arr = [];
    if (file_exists($file)) {
        $cur = @file_get_contents($file);
        $arr = json_decode($cur, true) ?: [];
    }
    $arr[] = $payload;
    @file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT));
    json_response([
        'saved' => 'file',
        'message' => 'Progression sauvegardée localement (fallback BDD)',
    ], 202);
} catch (Exception $e) {
    error_log("Erreur sauvegarde progression: " . $e->getMessage());
    // Fallback fichier local
    $storeDir = __DIR__ . '/../data';
    if (!is_dir($storeDir)) {
        @mkdir($storeDir, 0755, true);
    }
    $file = $storeDir . '/unsaved_progress.json';
    $payload = [
        'exerciseId' => $exerciseId,
        'score' => $score,
        'correct' => $correct,
        'userId' => $userId,
        'xp' => $xpGained,
        'cristaux' => $cristaux,
        'subject' => $subject,
        'at' => date('c'),
    ];
    $arr = [];
    if (file_exists($file)) {
        $cur = @file_get_contents($file);
        $arr = json_decode($cur, true) ?: [];
    }
    $arr[] = $payload;
    @file_put_contents($file, json_encode($arr, JSON_PRETTY_PRINT));
    json_response([
        'saved' => 'file',
        'message' => 'Progression sauvegardée localement (fallback serveur)',
    ], 202);
}
