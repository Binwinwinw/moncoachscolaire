<?php
/**
 * Extensions du dashboard : Objectifs, Streaks, Historique, Notifications
 *
 * Usage: require_once __DIR__ . '/dashboard_extensions.php';
 */

if (!isset($pdo)) {
    if (file_exists(__DIR__ . '/../database/connection.php')) {
        require_once __DIR__ . '/../database/connection.php';
    } elseif (file_exists(__DIR__ . '/../../db/connection.php')) {
        require_once __DIR__ . '/../../db/connection.php';
    }
}

/**
 * Initialise ou met à jour les objectifs quotidiens
 */
function initializeDailyGoal($userId, $date = null)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return false;
    }

    $date = $date ?: date('Y-m-d');

    try {
        // Vérifier si l'objectif existe déjà
        $stmt = $pdo->prepare("SELECT Id FROM UserDailyGoals WHERE UserId = ? AND Date = ? AND GoalType = 'exercises'");
        $stmt->execute([$userId, $date]);

        if (!$stmt->fetch()) {
            // Créer l'objectif par défaut : 3 exercices par jour
            $stmt = $pdo->prepare("
                INSERT INTO UserDailyGoals (UserId, Date, GoalType, Target, Completed)
                VALUES (?, ?, 'exercises', 3, 0)
            ");
            $stmt->execute([$userId, $date]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Erreur initialisation objectif quotidien: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les objectifs quotidiens
 */
function getDailyGoals($userId, $date = null)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return null;
    }

    $date = $date ?: date('Y-m-d');

    try {
        $stmt = $pdo->prepare("
            SELECT GoalType, Target, Completed,
                   ROUND((Completed / Target) * 100) as Progress
            FROM UserDailyGoals
            WHERE UserId = ? AND Date = ?
        ");
        $stmt->execute([$userId, $date]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erreur récupération objectifs quotidiens: " . $e->getMessage());
        return null;
    }
}

/**
 * Met à jour la progression d'un objectif quotidien
 */
function updateDailyGoalProgress($userId, $date = null)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return false;
    }

    $date = $date ?: date('Y-m-d');

    try {
        // Compter les exercices complétés aujourd'hui
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM ExerciseResponses
            WHERE UserId = ? 
            AND Correct = 1 
            AND DATE(SubmittedAt) = ?
        ");
        $stmt->execute([$userId, $date]);
        $result = $stmt->fetch();
        $completed = (int) ($result['count'] ?? 0);

        // Initialiser si nécessaire
        initializeDailyGoal($userId, $date);

        // Mettre à jour
        $stmt = $pdo->prepare("
            UPDATE UserDailyGoals 
            SET Completed = ? 
            WHERE UserId = ? AND Date = ? AND GoalType = 'exercises'
        ");
        $stmt->execute([$completed, $userId, $date]);

        return true;
    } catch (Exception $e) {
        error_log("Erreur mise à jour objectif quotidien: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les objectifs hebdomadaires
 */
function getWeeklyGoals($userId)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return null;
    }

    try {
        // Obtenir le lundi de la semaine actuelle
        $monday = date('Y-m-d', strtotime('monday this week'));

        $stmt = $pdo->prepare("
            SELECT GoalType, Target, Completed,
                   ROUND((Completed / Target) * 100) as Progress
            FROM UserWeeklyGoals
            WHERE UserId = ? AND WeekStart = ?
        ");
        $stmt->execute([$userId, $monday]);
        $goals = $stmt->fetchAll();

        // Créer les objectifs s'ils n'existent pas
        if (empty($goals)) {
            $stmt = $pdo->prepare("
                INSERT INTO UserWeeklyGoals (UserId, WeekStart, GoalType, Target, Completed)
                VALUES (?, ?, 'exercises', 15, 0)
            ");
            $stmt->execute([$userId, $monday]);

            return getWeeklyGoals($userId);
        }

        // Mettre à jour le progrès
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM ExerciseResponses
            WHERE UserId = ? 
            AND Correct = 1 
            AND DATE(SubmittedAt) >= ?
        ");
        $stmt->execute([$userId, $monday]);
        $result = $stmt->fetch();
        $completed = (int) ($result['count'] ?? 0);

        $stmt = $pdo->prepare("
            UPDATE UserWeeklyGoals 
            SET Completed = ? 
            WHERE UserId = ? AND WeekStart = ? AND GoalType = 'exercises'
        ");
        $stmt->execute([$completed, $userId, $monday]);

        // Re-récupérer avec les données mises à jour
        $stmt = $pdo->prepare("
            SELECT GoalType, Target, Completed,
                   ROUND((Completed / Target) * 100) as Progress
            FROM UserWeeklyGoals
            WHERE UserId = ? AND WeekStart = ?
        ");
        $stmt->execute([$userId, $monday]);
        return $stmt->fetchAll();

    } catch (Exception $e) {
        error_log("Erreur récupération objectifs hebdomadaires: " . $e->getMessage());
        return null;
    }
}

/**
 * Met à jour le streak de connexion
 */
function updateLoginStreak($userId)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return false;
    }

    try {
        $today = date('Y-m-d');

        // Vérifier si déjà connecté aujourd'hui
        $stmt = $pdo->prepare("SELECT Id FROM UserLoginHistory WHERE UserId = ? AND LoginDate = ?");
        $stmt->execute([$userId, $today]);
        if ($stmt->fetch()) {
            return true; // Déjà enregistré aujourd'hui
        }

        // Enregistrer la connexion d'aujourd'hui
        $stmt = $pdo->prepare("
            INSERT INTO UserLoginHistory (UserId, LoginDate)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE LoginDate = LoginDate
        ");
        $stmt->execute([$userId, $today]);

        // Récupérer ou créer le streak
        $stmt = $pdo->prepare("SELECT * FROM UserStreak WHERE UserId = ?");
        $stmt->execute([$userId]);
        $streak = $stmt->fetch();

        $yesterday = date('Y-m-d', strtotime('-1 day'));

        if (!$streak) {
            // Créer un nouveau streak
            $currentStreak = 1;
            $longestStreak = 1;
            $stmt = $pdo->prepare("
                INSERT INTO UserStreak (UserId, CurrentStreak, LongestStreak, LastLoginDate)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $currentStreak, $longestStreak, $today]);
        } else {
            $lastLogin = $streak['LastLoginDate'];
            $currentStreak = (int) $streak['CurrentStreak'];
            $longestStreak = (int) $streak['LongestStreak'];

            if ($lastLogin === $yesterday) {
                // Streak continue
                $currentStreak++;
            } elseif ($lastLogin !== $today) {
                // Streak rompu
                $currentStreak = 1;
            }

            if ($currentStreak > $longestStreak) {
                $longestStreak = $currentStreak;
            }

            $stmt = $pdo->prepare("
                UPDATE UserStreak 
                SET CurrentStreak = ?, LongestStreak = ?, LastLoginDate = ?
                WHERE UserId = ?
            ");
            $stmt->execute([$currentStreak, $longestStreak, $today, $userId]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Erreur mise à jour streak: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère le streak de l'utilisateur
 */
function getUserStreak($userId)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM UserStreak WHERE UserId = ?");
        $stmt->execute([$userId]);
        $streak = $stmt->fetch();

        if (!$streak) {
            return [
                'current' => 0,
                'longest' => 0,
                'last_login' => null,
            ];
        }

        return [
            'current' => (int) $streak['CurrentStreak'],
            'longest' => (int) $streak['LongestStreak'],
            'last_login' => $streak['LastLoginDate'],
        ];
    } catch (Exception $e) {
        error_log("Erreur récupération streak: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupère l'historique de progression (pour graphiques)
 */
function getProgressHistory($userId, $days = 30)
{
    global $pdo;

    if (!$pdo || !$userId) {
        error_log("getProgressHistory: PDO ou userId manquant - pdo: " . ($pdo ? 'OK' : 'NULL') . ", userId: " . ($userId ?? 'NULL'));
        return [];
    }

    try {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        // Vérifier si la table existe
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'UserProgressHistory'");
        if ($tableCheck->rowCount() === 0) {
            error_log("getProgressHistory: Table UserProgressHistory n'existe pas");
            return [];
        }

        $stmt = $pdo->prepare("
            SELECT Date, XP, ExercisesCompleted, Cristaux
            FROM UserProgressHistory
            WHERE UserId = ? AND Date >= ?
            ORDER BY Date ASC
        ");
        $stmt->execute([$userId, $startDate]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("getProgressHistory: " . count($results) . " enregistrements trouvés pour userId=$userId sur $days jours");

        return $results;
    } catch (PDOException $e) {
        error_log("Erreur PDO récupération historique: " . $e->getMessage());
        return [];
    } catch (Exception $e) {
        error_log("Erreur récupération historique: " . $e->getMessage());
        return [];
    }
}

/**
 * Met à jour l'historique de progression (à appeler quotidiennement)
 */
function updateProgressHistory($userId, $date = null)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return false;
    }

    $date = $date ?: date('Y-m-d');

    try {
        // Récupérer les stats du jour
        $progress = getUserProgress($userId);
        $xp = $progress['xp'] ?? 0;

        // Compter les exercices complétés aujourd'hui
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count
            FROM ExerciseResponses
            WHERE UserId = ? AND Correct = 1 AND DATE(SubmittedAt) = ?
        ");
        $stmt->execute([$userId, $date]);
        $result = $stmt->fetch();
        $exercisesCompleted = (int) ($result['count'] ?? 0);

        $cristaux = $progress['cristaux'] ?? 0;

        // Insérer ou mettre à jour
        $stmt = $pdo->prepare("
            INSERT INTO UserProgressHistory (UserId, Date, XP, ExercisesCompleted, Cristaux)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                XP = VALUES(XP),
                ExercisesCompleted = VALUES(ExercisesCompleted),
                Cristaux = VALUES(Cristaux)
        ");
        $stmt->execute([$userId, $date, $xp, $exercisesCompleted, $cristaux]);

        return true;
    } catch (Exception $e) {
        error_log("Erreur mise à jour historique: " . $e->getMessage());
        return false;
    }
}

/**
 * Crée une notification
 */
function createNotification($userId, $type, $title, $message, $icon = null, $link = null)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO UserNotifications (UserId, Type, Title, Message, Icon, Link)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $type, $title, $message, $icon, $link]);
        return true;
    } catch (Exception $e) {
        error_log("Erreur création notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les notifications non lues
 */
function getUnreadNotifications($userId, $limit = 10)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return [];
    }

    try {
        // Attention: `Read` est un mot réservé MySQL, utiliser des backticks
        // et ne pas binder LIMIT (MySQL n'accepte pas LIMIT ?)
        $limitInt = max(1, intval($limit));
        $stmt = $pdo->prepare("
                SELECT * FROM UserNotifications
                WHERE UserId = ? AND `Read` = 0
                ORDER BY CreatedAt DESC
                LIMIT $limitInt
            ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Erreur récupération notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Marque une notification comme lue
 */
function markNotificationAsRead($notificationId)
{
    global $pdo;

    if (!$pdo || !$notificationId) {
        return false;
    }

    try {
        // Échapper `Read` avec des backticks
        $stmt = $pdo->prepare("UPDATE UserNotifications SET `Read` = 1 WHERE Id = ?");
        $stmt->execute([$notificationId]);
        return true;
    } catch (Exception $e) {
        error_log("Erreur marquage notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie et crée des notifications automatiques (badges, niveaux, objectifs)
 */
function checkAndCreateNotifications($userId)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return;
    }

    try {
        $progress = getUserProgress($userId);
        $xp = $progress['xp'] ?? 0;
        $level = getUserLevel($xp);
        $badges = $progress['badges'] ?? [];
        $badgesCount = count($badges);

        // Vérifier si nouveau niveau atteint (à simplifier - nécessite de tracker le niveau précédent)
        // Pour l'instant, on vérifie juste les badges récents

        // Vérifier les badges récents (débloqués dans les 24h)
        $stmt = $pdo->prepare("
            SELECT a.Name, a.Description, ua.UnlockedAt
            FROM UserAchievements ua
            INNER JOIN Achievements a ON ua.AchievementId = a.Id
            WHERE ua.UserId = ? 
            AND ua.UnlockedAt >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            AND NOT EXISTS (
                SELECT 1 FROM UserNotifications n 
                WHERE n.UserId = ? 
                AND n.Type = 'badge' 
                AND n.Message LIKE CONCAT('%', a.Name, '%')
            )
        ");
        $stmt->execute([$userId, $userId]);
        $recentBadges = $stmt->fetchAll();

        foreach ($recentBadges as $badge) {
            createNotification(
                $userId,
                'badge',
                '🏆 Nouveau badge débloqué !',
                "Félicitations ! Tu as débloqué le badge : {$badge['Name']}",
                '🏆',
                null,
            );
        }

        // Vérifier les objectifs quotidiens atteints
        $dailyGoals = getDailyGoals($userId);
        if ($dailyGoals) {
            foreach ($dailyGoals as $goal) {
                if ($goal['Completed'] >= $goal['Target'] && $goal['Progress'] >= 100) {
                    // Vérifier si notification déjà créée
                    $stmt = $pdo->prepare("
                        SELECT Id FROM UserNotifications
                        WHERE UserId = ? 
                        AND Type = 'goal' 
                        AND Title LIKE '%Objectif quotidien%'
                        AND DATE(CreatedAt) = CURDATE()
                    ");
                    $stmt->execute([$userId]);

                    if (!$stmt->fetch()) {
                        createNotification(
                            $userId,
                            'goal',
                            '🎯 Objectif quotidien atteint !',
                            "Bravo ! Tu as complété ton objectif du jour : {$goal['Completed']}/{$goal['Target']} exercices",
                            '🎯',
                            null,
                        );
                    }
                }
            }
        }

    } catch (Exception $e) {
        error_log("Erreur vérification notifications: " . $e->getMessage());
    }
}

/**
 * Détermine le "temps" météo basé sur les performances
 */
function getWeatherMood($userId)
{
    $progress = getUserProgress($userId);
    $streak = getUserStreak($userId);
    $dailyGoals = getDailyGoals($userId);

    $score = 0;

    // Score basé sur le streak
    if ($streak) {
        if ($streak['current'] >= 7) {
            $score += 3;
        } elseif ($streak['current'] >= 3) {
            $score += 2;
        } elseif ($streak['current'] >= 1) {
            $score += 1;
        }
    }

    // Score basé sur les objectifs quotidiens
    if ($dailyGoals) {
        foreach ($dailyGoals as $goal) {
            $progressPercent = $goal['Progress'] ?? 0;
            if ($progressPercent >= 100) {
                $score += 2;
            } elseif ($progressPercent >= 50) {
                $score += 1;
            }
        }
    }

    // Score basé sur les exercices récents
    $exercisesCompleted = $progress['exercises_completed'] ?? 0;
    if ($exercisesCompleted >= 10) {
        $score += 2;
    } elseif ($exercisesCompleted >= 5) {
        $score += 1;
    }

    // Déterminer le temps
    if ($score >= 6) {
        return ['icon' => '☀️', 'mood' => 'Excellent', 'color' => '#fbbf24', 'message' => 'Tu es en feu ! Continue comme ça !'];
    } elseif ($score >= 4) {
        return ['icon' => '🌤️', 'mood' => 'Très bien', 'color' => '#60a5fa', 'message' => 'Tu progresses bien, continue !'];
    } elseif ($score >= 2) {
        return ['icon' => '⛅', 'mood' => 'Bon', 'color' => '#93c5fd', 'message' => 'Bon travail, tu peux faire encore mieux !'];
    } else {
        return ['icon' => '🌧️', 'mood' => 'Peut mieux faire', 'color' => '#94a3b8', 'message' => 'Il est temps de se remettre au travail !'];
    }
}

?>

