<?php

/**
 * Système d'auto-attribution des badges
 * Vérifie les critères et attribue les badges automatiquement
 */

require_once dirname(__DIR__) . '/database/connection.php';

/**
 * Vérifie et attribue les badges automatiques pour un utilisateur
 * @param int $userId ID utilisateur
 * @return array Badges nouvellement attribués
 */
function checkAndAwardBadges($userId)
{
    global $pdo;

    if (!$pdo || !$userId) {
        return [];
    }

    try {
        // Récupérer stats utilisateur
        $stmt = $pdo->prepare("SELECT XP FROM userprogress WHERE UserId = ?");
        $stmt->execute([$userId]);
        $progress = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalXp = (int) ($progress['XP'] ?? 0);

        // Compter exercices complétés
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM mastery WHERE user_id = ? AND xp_earned > 0");
        $stmt->execute([$userId]);
        $exercisesCompleted = (int) $stmt->fetch()['count'];

        // Compter badges déjà gagnés
        $stmt = $pdo->prepare("SELECT badge_id FROM userbadge WHERE user_id = ?");
        $stmt->execute([$userId]);
        $earnedBadgeIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'badge_id');

        // Récupérer tous les badges avec critères
        $stmt = $pdo->query("SELECT id, name, slug, criteria FROM badge ORDER BY id");
        $allBadges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $newBadges = [];

        foreach ($allBadges as $badge) {
            // Skip si déjà gagné
            if (in_array($badge['id'], $earnedBadgeIds)) {
                continue;
            }

            $earned = false;
            $criteria = $badge['criteria'] ?? $badge['slug'] ?? '';

            // Parser critères selon format/slug
            switch ($criteria) {
                case 'first_exercise':
                    $earned = $exercisesCompleted >= 1;
                    break;

                case 'streak_5_80pct':
                case 'winning_streak_5':
                    // Série de 5 exercices avec >80% (TODO: nécessite analyse séquentielle)
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM exerciseresponses WHERE UserId = ? AND Score >= 80 ORDER BY SubmittedAt DESC LIMIT 5");
                    $stmt->execute([$userId]);
                    $earned = ($stmt->fetch()['count'] >= 5 && $exercisesCompleted >= 5);
                    break;

                case 'score_100':
                case 'perfect_score':
                    // 10 scores parfaits
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM mastery WHERE user_id = ? AND best_score = 100");
                    $stmt->execute([$userId]);
                    $perfect = (int) $stmt->fetch()['count'];
                    $earned = $perfect >= 10;
                    break;

                case 'mastery_expert':
                    // 20 exercices complétés
                    $earned = $exercisesCompleted >= 20;
                    break;

                case 'xp_1000':
                    $earned = $totalXp >= 1000;
                    break;

                case 'notion_master':
                    // Maîtrise de 5 notions différentes (TODO: définir critère)
                    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT en.notion_id) as count FROM exercisenotion en JOIN mastery m ON en.exercise_id = m.exercise_id WHERE m.user_id = ? AND m.xp_earned > 0");
                    $stmt->execute([$userId]);
                    $notionCount = (int) $stmt->fetch()['count'];
                    $earned = $notionCount >= 5;
                    break;

                case 'speed_master':
                    // Temps moyen < 60s sur 15 exercices minimum
                    $stmt = $pdo->prepare("SELECT AVG(total_time_seconds / attempts) as avg_time, COUNT(*) as count FROM mastery WHERE user_id = ? AND attempts > 0");
                    $stmt->execute([$userId]);
                    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
                    $avgTime = (float) ($stats['avg_time'] ?? 999);
                    $count = (int) ($stats['count'] ?? 0);
                    $earned = ($avgTime < 60 && $count >= 15);
                    break;

                case 'persistent':
                    // 30 exercices complétés
                    $earned = $exercisesCompleted >= 30;
                    break;

                case 'explore_5_subjects':
                    // 5 matières différentes
                    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT e.Subject) as count FROM mastery m JOIN exercises e ON m.exercise_id = e.Id WHERE m.user_id = ? AND m.xp_earned > 0");
                    $stmt->execute([$userId]);
                    $subjectCount = (int) $stmt->fetch()['count'];
                    $earned = $subjectCount >= 5;
                    break;

                default:
                    // Fallback : vérifier formats génériques
                    if (preg_match('/xp:(\d+)/', $criteria, $m)) {
                        $required = (int) $m[1];
                        $earned = $totalXp >= $required;
                    } elseif (preg_match('/exercises:(\d+)/', $criteria, $m)) {
                        $required = (int) $m[1];
                        $earned = $exercisesCompleted >= $required;
                    }
                    break;
            }

            if ($earned) {
                // Attribuer badge
                $insert = $pdo->prepare("INSERT INTO userbadge (user_id, badge_id, earned_at) VALUES (?, ?, NOW())");
                $insert->execute([$userId, $badge['id']]);

                $newBadges[] = [
                    'id' => $badge['id'],
                    'name' => $badge['name'],
                    'slug' => $badge['slug'],
                ];
            }
        }

        return $newBadges;

    } catch (Exception $e) {
        error_log("Erreur checkAndAwardBadges: " . $e->getMessage());
        return [];
    }
}

/**
 * Met à jour les critères des badges existants
 */
function updateBadgeCriteria()
{
    global $pdo;

    if (!$pdo) {
        return false;
    }

    try {
        $badges = [
            ['slug' => 'debuter', 'criteria' => 'exercises:1'],
            ['slug' => 'apprenti', 'criteria' => 'exercises:5'],
            ['slug' => 'expert', 'criteria' => 'exercises:20'],
            ['slug' => 'champion', 'criteria' => 'exercises:50'],
            ['slug' => 'maitre', 'criteria' => 'xp:500'],
            ['slug' => 'erudit', 'criteria' => 'xp:1000'],
            ['slug' => 'legende', 'criteria' => 'xp:2500'],
            ['slug' => 'perfectionniste', 'criteria' => 'perfect:10'],
            ['slug' => 'rapide', 'criteria' => 'avgtime:60'],
            ['slug' => 'assidu', 'criteria' => 'streak:7'],
        ];

        foreach ($badges as $badge) {
            $upd = $pdo->prepare("UPDATE badge SET criteria = ? WHERE slug = ?");
            $upd->execute([$badge['criteria'], $badge['slug']]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Erreur updateBadgeCriteria: " . $e->getMessage());
        return false;
    }
}

// Si exécuté directement, mettre à jour les critères
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "🏆 Mise à jour des critères de badges\n";
    echo "=====================================\n\n";

    if (updateBadgeCriteria()) {
        echo "✓ Critères mis à jour\n";

        // Afficher les critères
        $stmt = $pdo->query("SELECT slug, name, criteria FROM badge ORDER BY id");
        $badges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "\nBadges et critères:\n";
        foreach ($badges as $badge) {
            echo sprintf("  %-20s %-30s %s\n", $badge['slug'], $badge['name'], $badge['criteria'] ?? 'N/A');
        }
    } else {
        echo "❌ Erreur lors de la mise à jour\n";
    }
}
