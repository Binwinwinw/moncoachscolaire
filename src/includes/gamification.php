<?php

/**
 * Système de gamification pour MonCoachScolaire
 *
 * Gère les cristaux, badges, XP et progression utilisateur
 *
 * Usage: require_once __DIR__ . '/gamification.php';
 */

// S'assurer que la connexion DB est disponible
if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/../database/connection.php')) {
        require_once __DIR__ . '/../database/connection.php';
    } elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
        require_once __DIR__ . '/../../db/connection.php';
    }
}

/**
 * Marque un exercice comme complété et attribue les récompenses
 *
 * @param int $userId ID de l'utilisateur
 * @param int $exerciseId ID de l'exercice
 * @param bool $correct Réponse correcte ou non
 * @param array $rewards Récompenses (éléments scientifiques, xp, badge)
 * @return array Résultat de l'opération
 */
function completeExercise($userId, $exerciseId, $correct = true, $rewards = [])
{
    global $pdo;

    // Charger le système de sécurité démo si disponible
    if (file_exists(__DIR__ . '/demo_security.php')) {
        require_once __DIR__ . '/demo_security.php';

        // SÉCURITÉ : Bloquer les écritures pour le compte démo
        if (isDemoAccount($userId) || isDemoUser()) {
            error_log("SECURITY: Tentative de complétion d'exercice bloquée pour compte démo");
            return [
                'success' => false,
                'error' => 'Mode démo : sauvegarde désactivée',
                'demo_mode' => true,
                'message' => 'Créez un compte gratuit pour sauvegarder votre progression !',
            ];
        }
    }

    if (!$pdo || !$userId || !$exerciseId) {
        return ['success' => false, 'error' => 'Paramètres invalides'];
    }

    try {
        // Vérifier si l'exercice existe
        $exercise = getExerciseById($exerciseId);
        if (!$exercise) {
            return ['success' => false, 'error' => 'Exercice non trouvé'];
        }

        // Vérifier si déjà complété
        $existing = checkExerciseCompleted($userId, $exerciseId);
        if ($existing) {
            return ['success' => false, 'error' => 'Exercice déjà complété', 'already_completed' => true];
        }

        // Extraire les récompenses de l'exercice si non fournies
        if (empty($rewards)) {
            $rewards = extractRewardsFromExercise($exercise);
        }

        $cristaux = $rewards['cristaux'] ?? 0;
        $xp = $rewards['xp'] ?? 0;
        $badge = $rewards['badge'] ?? null;

        // Commencer une transaction
        $pdo->beginTransaction();

        // 1. Sauvegarder la réponse
        $score = $correct ? 100 : 0;
        $stmt = $pdo->prepare("
            INSERT INTO ExerciseResponses (UserId, ExerciseId, Correct, Score)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $exerciseId, $correct ? 1 : 0, $score]);

        // 2. Mettre à jour la progression (XP)
        if ($correct && $xp > 0) {
            updateUserXP($userId, $xp);
        }

        // 3. Ajouter les cristaux (si correct)
        if ($correct && $cristaux > 0) {
            addCristaux($userId, $cristaux, $exercise['Subject'] ?? null);
        }

        // 4. Attribuer le badge (si correct et badge défini)
        $badgeUnlocked = false;
        if ($correct && $badge) {
            $badgeUnlocked = unlockBadge($userId, $badge);
        }

        // 5. Vérifier les badges automatiques
        checkAutomaticBadges($userId, $exercise);

        $pdo->commit();

        return [
            'success' => true,
            'cristaux' => $correct ? $cristaux : 0,
            'xp' => $correct ? $xp : 0,
            'badge_unlocked' => $badgeUnlocked,
            'badge_name' => $badgeUnlocked ? $badge : null,
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Erreur complétion exercice: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Vérifie si un exercice est déjà complété
 */
function checkExerciseCompleted($userId, $exerciseId)
{
    global $pdo;

    if (!$pdo) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT Id, Correct, Score, SubmittedAt
            FROM ExerciseResponses
            WHERE UserId = ? AND ExerciseId = ?
            ORDER BY SubmittedAt DESC
            LIMIT 1
        ");
        $stmt->execute([$userId, $exerciseId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Erreur vérification exercice: " . $e->getMessage());
        return false;
    }
}

/**
 * Extrait les récompenses depuis les métadonnées de l'exercice
 */
function extractRewardsFromExercise($exercise)
{
    // Les récompenses sont dans les métadonnées Markdown
    // Pour l'instant, valeurs par défaut
    return [
        'cristaux' => 25,  // Valeur par défaut
        'xp' => 15,        // Valeur par défaut
        'badge' => null,    // Sera extrait depuis les métadonnées
    ];
}

/**
 * Met à jour l'XP de l'utilisateur
 */
function updateUserXP($userId, $xpToAdd)
{
    global $pdo;

    if (!$pdo) {
        return false;
    }

    try {
        // Vérifier si UserProgress existe
        $stmt = $pdo->prepare("SELECT Id, XP FROM UserProgress WHERE UserId = ?");
        $stmt->execute([$userId]);
        $progress = $stmt->fetch();

        if ($progress) {
            // Mettre à jour
            $newXP = ($progress['XP'] ?? 0) + $xpToAdd;
            $stmt = $pdo->prepare("UPDATE UserProgress SET XP = ? WHERE UserId = ?");
            $stmt->execute([$newXP, $userId]);
        } else {
            // Créer
            $stmt = $pdo->prepare("INSERT INTO UserProgress (UserId, XP) VALUES (?, ?)");
            $stmt->execute([$userId, $xpToAdd]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Erreur mise à jour XP: " . $e->getMessage());
        return false;
    }
}

/**
 * Ajoute des cristaux à l'utilisateur
 * Les cristaux sont stockés dans UserProgress.ProgressJson
 */
function addCristaux($userId, $amount, $subject = null)
{
    global $pdo;

    if (!$pdo) {
        return false;
    }

    try {
        // Récupérer la progression actuelle
        $stmt = $pdo->prepare("SELECT ProgressJson FROM UserProgress WHERE UserId = ?");
        $stmt->execute([$userId]);
        $progress = $stmt->fetch();

        $cristaux = 0;
        $cristauxBySubject = [];

        if ($progress && $progress['ProgressJson']) {
            $json = json_decode($progress['ProgressJson'], true);
            $cristaux = $json['cristaux'] ?? 0;
            $cristauxBySubject = $json['cristaux_by_subject'] ?? [];
        }

        // Ajouter les cristaux
        $cristaux += $amount;

        if ($subject) {
            $cristauxBySubject[$subject] = ($cristauxBySubject[$subject] ?? 0) + $amount;
        }

        // Sauvegarder
        $jsonData = json_encode([
            'cristaux' => $cristaux,
            'cristaux_by_subject' => $cristauxBySubject,
        ]);

        if ($progress) {
            $stmt = $pdo->prepare("UPDATE UserProgress SET ProgressJson = ? WHERE UserId = ?");
            $stmt->execute([$jsonData, $userId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO UserProgress (UserId, ProgressJson) VALUES (?, ?)");
            $stmt->execute([$userId, $jsonData]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Erreur ajout cristaux: " . $e->getMessage());
        return false;
    }
}

/**
 * Débloque un badge pour l'utilisateur
 */
function unlockBadge($userId, $badgeName)
{
    global $pdo;

    if (!$pdo || !$badgeName) {
        return false;
    }

    try {
        // Chercher ou créer le badge
        $stmt = $pdo->prepare("SELECT Id FROM Achievements WHERE Name = ?");
        $stmt->execute([$badgeName]);
        $achievement = $stmt->fetch();

        if (!$achievement) {
            // Créer le badge s'il n'existe pas
            $stmt = $pdo->prepare("INSERT INTO Achievements (Name, Description, Points) VALUES (?, ?, ?)");
            $stmt->execute([$badgeName, "Badge: $badgeName", 0]);
            $achievementId = $pdo->lastInsertId();
        } else {
            $achievementId = $achievement['Id'];
        }

        // Vérifier si l'utilisateur a déjà ce badge
        $stmt = $pdo->prepare("SELECT Id FROM UserAchievements WHERE UserId = ? AND AchievementId = ?");
        $stmt->execute([$userId, $achievementId]);
        if ($stmt->fetch()) {
            return false; // Déjà débloqué
        }

        // Débloquer le badge
        $stmt = $pdo->prepare("INSERT INTO UserAchievements (UserId, AchievementId) VALUES (?, ?)");
        $stmt->execute([$userId, $achievementId]);

        return true;
    } catch (Exception $e) {
        error_log("Erreur déblocage badge: " . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie et débloque les badges automatiques
 */
function checkAutomaticBadges($userId, $exercise)
{
    global $pdo;

    if (!$pdo) {
        return;
    }

    try {
        // Badge : Premier exercice complété
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM ExerciseResponses
            WHERE UserId = ? AND Correct = 1
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        $completedCount = (int) ($result['count'] ?? 0);

        if ($completedCount === 1) {
            unlockBadge($userId, "Premier pas");
        }

        // Badge : 10 exercices complétés
        if ($completedCount === 10) {
            unlockBadge($userId, "Débutant confirmé");
        }

        // Badge : 50 exercices complétés
        if ($completedCount === 50) {
            unlockBadge($userId, "Expert en exercices");
        }

        // Badge par matière
        $subject = $exercise['Subject'] ?? '';
        if ($subject) {
            $stmt = $pdo->prepare("
                SELECT COUNT(DISTINCT er.ExerciseId) as count
                FROM ExerciseResponses er
                INNER JOIN exercises e ON er.ExerciseId = e.Id
                WHERE er.UserId = ? AND er.Correct = 1 AND e.Subject = ?
            ");
            $stmt->execute([$userId, $subject]);
            $result = $stmt->fetch();
            $subjectCount = (int) ($result['count'] ?? 0);

            if ($subject === 'Mathématiques') {
                if ($subjectCount === 5) {
                    unlockBadge($userId, "Apprenti mathématicien");
                }
                if ($subjectCount === 15) {
                    unlockBadge($userId, "Mathématicien confirmé");
                }
            } elseif ($subject === 'Français') {
                if ($subjectCount === 5) {
                    unlockBadge($userId, "Apprenti linguiste");
                }
                if ($subjectCount === 15) {
                    unlockBadge($userId, "Linguiste confirmé");
                }
            }
        }

    } catch (Exception $e) {
        error_log("Erreur vérification badges: " . $e->getMessage());
    }
}

/**
 * Récupère les statistiques de progression d'un utilisateur
 */
function getUserProgress($userId)
{
    global $pdo;

    if (!$pdo) {
        return null;
    }

    try {
        // Récupérer la progression
        $stmt = $pdo->prepare("
            SELECT XP, ProgressJson
            FROM UserProgress
            WHERE UserId = ?
        ");
        $stmt->execute([$userId]);
        $progress = $stmt->fetch();

        // Compter les exercices complétés
        $stmt = $pdo->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN Correct = 1 THEN 1 ELSE 0 END) as correct,
                SUM(Score) as total_score
            FROM ExerciseResponses
            WHERE UserId = ?
        ");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();

        // Compter par matière
        $stmt = $pdo->prepare("
            SELECT e.Subject, COUNT(DISTINCT er.ExerciseId) as count
            FROM ExerciseResponses er
            INNER JOIN exercises e ON er.ExerciseId = e.Id
            WHERE er.UserId = ? AND er.Correct = 1
            GROUP BY e.Subject
        ");
        $stmt->execute([$userId]);
        $bySubject = $stmt->fetchAll();

        // Récupérer les badges
        $stmt = $pdo->prepare("
            SELECT a.Name, a.Description, ua.UnlockedAt
            FROM UserAchievements ua
            INNER JOIN Achievements a ON ua.AchievementId = a.Id
            WHERE ua.UserId = ?
            ORDER BY ua.UnlockedAt DESC
        ");
        $stmt->execute([$userId]);
        $badges = $stmt->fetchAll();

        // Extraire les cristaux du JSON
        $cristaux = 0;
        $cristauxBySubject = [];
        if ($progress && $progress['ProgressJson']) {
            $json = json_decode($progress['ProgressJson'], true);
            $cristaux = $json['cristaux'] ?? 0;
            $cristauxBySubject = $json['cristaux_by_subject'] ?? [];
        }

        return [
            'xp' => (int) ($progress['XP'] ?? 0),
            'cristaux' => $cristaux,
            'cristaux_by_subject' => $cristauxBySubject,
            'exercises_completed' => (int) ($stats['correct'] ?? 0),
            'exercises_total' => (int) ($stats['total'] ?? 0),
            'total_score' => (int) ($stats['total_score'] ?? 0),
            'by_subject' => $bySubject,
            'badges' => $badges,
            'badges_count' => count($badges),
        ];
    } catch (Exception $e) {
        error_log("Erreur récupération progression: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupère les exercices complétés par un utilisateur
 */
function getCompletedExercises($userId, $level = null, $subject = null, $limit = null)
{
    global $pdo;

    if (!$pdo) {
        return [];
    }

    try {
        $sql = "
            SELECT er.*, e.Title, e.Subject, e.Level
            FROM ExerciseResponses er
            INNER JOIN exercises e ON er.ExerciseId = e.Id
            WHERE er.UserId = ? AND er.Correct = 1
        ";
        $params = [$userId];

        if ($level) {
            $sql .= " AND e.Level = ?";
            $params[] = $level;
        }

        if ($subject) {
            $sql .= " AND e.Subject = ?";
            $params[] = $subject;
        }

        $sql .= " ORDER BY er.SubmittedAt DESC";

        if ($limit && is_numeric($limit)) {
            $sql .= " LIMIT " . (int) $limit;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erreur récupération exercices complétés: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère le niveau de l'utilisateur basé sur son XP
 */
function getUserLevel($xp)
{
    // Niveaux basés sur l'XP
    $levels = [
        1 => 0,
        2 => 100,
        3 => 300,
        4 => 600,
        5 => 1000,
        6 => 1500,
        7 => 2100,
        8 => 2800,
        9 => 3600,
        10 => 4500,
    ];

    $level = 1;
    foreach ($levels as $lvl => $minXP) {
        if ($xp >= $minXP) {
            $level = $lvl;
        } else {
            break;
        }
    }

    return $level;
}
