<?php
/**
 * Enrichissement dashboard élève avec Sprint 1 & 2
 * Widgets : XP/Badges (Sprint 1), Diagnostics (Sprint 2), Graphiques Chart.js
 *
 * Usage: require_once dans dashboard.php
 */

// Récupérer statistiques utilisateur (Sprint 1 & 2)
$user_stats_data = null;
$notion_progress_data = null;

if ($user_id) {
    // Simuler appel API user_stats
    try {
        if (is_file(dirname(__DIR__, 2) . '/api/user_stats.php')) {
            // Capturer output de l'API
            ob_start();
            $_SESSION['user_id'] = $user_id; // S'assurer que la session est définie
            include dirname(__DIR__, 2) . '/api/user_stats.php';
            $json_output = ob_get_clean();
            $user_stats_data = json_decode($json_output, true);
        }
    } catch (Exception $e) {
        error_log("Erreur chargement user_stats: " . $e->getMessage());
    }

    // Récupérer progression par notion
    try {
        $stmt = $pdo->prepare("
            SELECT
                n.id, n.name, n.level, n.subject,
                COUNT(DISTINCT CASE WHEN m.BestScore >= 80 THEN m.ExerciseId END) as mastered_count,
                COUNT(DISTINCT m.ExerciseId) as attempted_count,
                AVG(m.BestScore) as avg_score
            FROM notion n
            LEFT JOIN exercisenotion en ON n.id = en.notion_id
            LEFT JOIN mastery m ON en.exercise_id = m.ExerciseId AND m.UserId = ?
            WHERE n.level = ?
            GROUP BY n.id
            HAVING attempted_count > 0
            ORDER BY avg_score DESC
            LIMIT 10
        ");
        $stmt->execute([$user_id, $user_level]);
        $notion_progress_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur progression notions: " . $e->getMessage());
        $notion_progress_data = [];
    }
}

// Récupérer diagnostics récents (Sprint 2)
$recent_diagnostics = [];
if ($user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                q.id as quiz_id,
                q.title,
                n.name as notion_name,
                qr.score,
                qr.passed,
                qr.CreatedAt as created_at
            FROM quizresult qr
            JOIN quiz q ON qr.QuizId = q.id
            LEFT JOIN notion n ON q.notion_id = n.id
            WHERE qr.UserId = ? AND q.type = 'diagnostic'
            ORDER BY qr.CreatedAt DESC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $recent_diagnostics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur récupération diagnostics: " . $e->getMessage());
    }
}

// Récupérer badges gagnés (Sprint 1)
$earned_badges = [];
if ($user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT b.name, b.slug, b.description, b.icon, ub.EarnedAt as earned_at
            FROM userbadge ub
            JOIN badge b ON ub.badge_id = b.id
            WHERE ub.UserId = ?
            ORDER BY ub.EarnedAt DESC
            LIMIT 6
        ");
        $stmt->execute([$user_id]);
        $earned_badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur récupération badges: " . $e->getMessage());
    }
}

// Calculer XP total et niveau (Sprint 1)
$total_xp = 0;
$user_level_xp = 1;
$level_name = 'Débutant';
$progress_to_next = 0;
$next_level_xp = 100;

if ($user_id) {
    try {
        $stmt = $pdo->prepare("SELECT XP FROM userprogress WHERE UserId = ?");
        $stmt->execute([$user_id]);
        $total_xp = $stmt->fetchColumn() ?: 0;

        // Calcul niveau basé sur XP
        $levels = [
            ['threshold' => 0, 'level' => 1, 'name' => 'Débutant'],
            ['threshold' => 100, 'level' => 2, 'name' => 'Apprenti'],
            ['threshold' => 500, 'level' => 3, 'name' => 'Confirmé'],
            ['threshold' => 1000, 'level' => 4, 'name' => 'Expert'],
            ['threshold' => 2500, 'level' => 5, 'name' => 'Maître'],
            ['threshold' => 5000, 'level' => 6, 'name' => 'Grand Maître'],
        ];

        foreach ($levels as $i => $level) {
            if ($total_xp >= $level['threshold']) {
                $user_level_xp = $level['level'];
                $level_name = $level['name'];
                $next_threshold = $levels[$i + 1]['threshold'] ?? ($level['threshold'] + 5000);
                $xp_in_level = $total_xp - $level['threshold'];
                $xp_needed = $next_threshold - $level['threshold'];
                $progress_to_next = ($xp_needed > 0) ? round(($xp_in_level / $xp_needed) * 100, 1) : 100;
                $next_level_xp = $next_threshold;
            }
        }
    } catch (Exception $e) {
        error_log("Erreur calcul XP: " . $e->getMessage());
    }
}

// Préparer données pour graphiques Chart.js
$xp_history = [];
if ($user_id) {
    try {
        // XP des 7 derniers jours
        $stmt = $pdo->prepare("
            SELECT
                DATE(SubmittedAt) as date,
                SUM(XpEarned) as daily_xp
            FROM exerciseresponses
            WHERE UserId = ? AND SubmittedAt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(SubmittedAt)
            ORDER BY date ASC
        ");
        $stmt->execute([$user_id]);
        $xp_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur historique XP: " . $e->getMessage());
    }
}
?>

<!-- Widget XP & Niveau (Sprint 1) -->
<div class="dashboard-card card-xp-level">
    <div class="card-header">
        <h3>⭐ Ton Niveau d'Excellence</h3>
    </div>
    <div class="card-body">
        <div class="xp-display">
            <div class="xp-circle">
                <div class="xp-circle-inner">
                    <div class="xp-level-number"><?php echo $user_level_xp; ?></div>
                    <div class="xp-level-name"><?php echo $level_name; ?></div>
                </div>
                <svg class="xp-progress-ring" width="150" height="150">
                    <circle class="progress-ring-bg" cx="75" cy="75" r="65" />
                    <circle class="progress-ring-fill" cx="75" cy="75" r="65"
                           />
                </svg>
            </div>
            <div class="xp-stats">
                <div class="xp-stat-item">
                    <span class="xp-stat-label">XP Total</span>
                    <span class="xp-stat-value"><?php echo number_format($total_xp); ?></span>
                </div>
                <div class="xp-stat-item">
                    <span class="xp-stat-label">Prochain Niveau</span>
                    <span class="xp-stat-value"><?php echo number_format($next_level_xp - $total_xp); ?> XP</span>
                </div>
                <div class="xp-progress-bar">
                    <div class="xp-progress-fill"></div>
                </div>
                <div class="xp-progress-label"><?php echo $progress_to_next; ?>% vers niveau <?php echo $user_level_xp + 1; ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Widget Badges (Sprint 1) -->
<div class="dashboard-card card-badges">
    <div class="card-header">
        <h3>🏆 Tes Badges</h3>
        <span class="badge-count"><?php echo count($earned_badges); ?> gagnés</span>
    </div>
    <div class="card-body">
        <?php if (!empty($earned_badges)): ?>
            <div class="badges-grid">
                <?php foreach ($earned_badges as $badge): ?>
                    <div class="badge-item" title="<?php echo htmlspecialchars($badge['description']); ?>">
                        <div class="badge-icon"><?php echo $badge['icon'] ?? '🏅'; ?></div>
                        <div class="badge-name"><?php echo htmlspecialchars($badge['name']); ?></div>
                        <div class="badge-date"><?php echo date('d/m/Y', strtotime($badge['earned_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo site_url('badges'); ?>" class="btn btn-secondary btn-small">Voir tous les badges →</a>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">🎯</span>
                <p>Complète des exercices pour gagner ton premier badge !</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Widget Diagnostics Récents (Sprint 2) -->
<div class="dashboard-card card-diagnostics">
    <div class="card-header">
        <h3>🎯 Mes Diagnostics</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($recent_diagnostics)): ?>
            <div class="diagnostics-list">
                <?php foreach ($recent_diagnostics as $diag): ?>
                    <div class="diagnostic-item <?php echo $diag['passed'] ? 'passed' : 'failed'; ?>">
                        <div class="diagnostic-info">
                            <div class="diagnostic-notion"><?php echo htmlspecialchars($diag['notion_name'] ?? 'Notion'); ?></div>
                            <div class="diagnostic-date"><?php echo date('d/m H:i', strtotime($diag['created_at'])); ?></div>
                        </div>
                        <div class="diagnostic-score">
                            <span class="score-value"><?php echo round($diag['score'], 1); ?>%</span>
                            <?php if ($diag['passed']): ?>
                                <span class="score-badge">✓ Réussi</span>
                            <?php else: ?>
                                <span class="score-badge">À revoir</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo site_url('diagnostic'); ?>" class="btn btn-primary btn-small">Nouveau diagnostic →</a>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">🎯</span>
                <p>Passe ton premier diagnostic pour évaluer tes compétences !</p>
                <a href="<?php echo site_url('diagnostic'); ?>" class="btn btn-primary">Commencer →</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Widget Progression par Notion -->
<div class="dashboard-card card-notion-progress">
    <div class="card-header">
        <h3>📊 Progression par Notion</h3>
    </div>
    <div class="card-body">
        <?php if (!empty($notion_progress_data)): ?>
            <div class="notion-progress-list">
                <?php foreach (array_slice($notion_progress_data, 0, 5) as $notion): ?>
                    <div class="notion-progress-item">
                        <div class="notion-info">
                            <div class="notion-name"><?php echo htmlspecialchars($notion['name']); ?></div>
                            <div class="notion-meta">
                                <?php echo $notion['mastered_count']; ?>/<?php echo $notion['attempted_count']; ?> maîtrisés
                            </div>
                        </div>
                        <div class="notion-score">
                            <?php
                            $avg = round($notion['avg_score'] ?? 0, 1);
                    $color = $avg >= 80 ? '#10b981' : ($avg >= 60 ? '#f59e0b' : '#ef4444');
                    ?>
                            <div class="score-bar">
                                <div class="score-bar-fill"></div>
                            </div>
                            <span class="score-value"><?php echo $avg; ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">📝</span>
                <p>Commence des exercices pour voir ta progression !</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Graphique XP 7 jours (Chart.js) -->
<div class="dashboard-card card-xp-chart">
    <div class="card-header">
        <h3>📈 Progression XP (7 jours)</h3>
    </div>
    <div class="card-body">
        <canvas id="xpChart" width="400" height="200"></canvas>
    </div>
</div>

<style>
/* Styles des nouveaux widgets */
.card-xp-level .xp-display {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 1rem;
}

.xp-circle {
    position: relative;
    width: 150px;
    height: 150px;
}

.xp-circle-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.xp-level-number {
    font-size: 3rem;
    font-weight: 700;
    color: #3b82f6;
}

.xp-level-name {
    font-size: 0.9rem;
    color: #64748b;
    font-weight: 600;
}

.xp-progress-ring {
    transform: rotate(-90deg);
}

.progress-ring-bg {
    fill: none;
    stroke: #e2e8f0;
    stroke-width: 8;
}

.progress-ring-fill {
    fill: none;
    stroke: #3b82f6;
    stroke-width: 8;
    stroke-dasharray: 408;
    stroke-linecap: round;
    transition: stroke-dashoffset 0.5s ease;
}

.xp-stats {
    flex: 1;
}

.xp-stat-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.xp-stat-label {
    color: #64748b;
    font-size: 0.9rem;
}

.xp-stat-value {
    font-weight: 700;
    color: #1e293b;
    font-size: 1.1rem;
}

.xp-progress-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin: 1rem 0 0.5rem 0;
}

.xp-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    transition: width 0.5s ease;
}

.xp-progress-label {
    text-align: center;
    color: #64748b;
    font-size: 0.85rem;
}

.badges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.badge-item {
    text-align: center;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    transition: transform 0.2s;
}

.badge-item:hover {
    transform: translateY(-4px);
}

.badge-icon {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.badge-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.badge-date {
    font-size: 0.75rem;
    color: #94a3b8;
}

.diagnostics-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.diagnostic-item {
    display: flex;
    justify-content: space-between;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #e2e8f0;
}

.diagnostic-item.passed {
    border-left-color: #10b981;
    background: #f0fdf4;
}

.diagnostic-item.failed {
    border-left-color: #ef4444;
    background: #fef2f2;
}

.diagnostic-notion {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.diagnostic-date {
    font-size: 0.85rem;
    color: #64748b;
}

.diagnostic-score {
    text-align: right;
}

.score-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
}

.diagnostic-item.passed .score-value {
    color: #10b981;
}

.diagnostic-item.failed .score-value {
    color: #ef4444;
}

.score-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
}

.diagnostic-item.passed .score-badge {
    background: #10b981;
    color: white;
}

.diagnostic-item.failed .score-badge {
    background: #ef4444;
    color: white;
}

.notion-progress-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.notion-progress-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.notion-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.notion-meta {
    font-size: 0.85rem;
    color: #64748b;
}

.notion-score {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 120px;
}

.score-bar {
    flex: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 3px;
    overflow: hidden;
}

.score-bar-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.5s ease;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Graphique XP 7 jours
const xpData = <?php echo json_encode($xp_history); ?>;
const labels = xpData.map(d => {
    const date = new Date(d.date);
    return date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric' });
});
const data = xpData.map(d => d.daily_xp);

const ctx = document.getElementById('xpChart');
if (ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'XP gagnés',
                data: data,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 10 }
                }
            }
        }
    });
}
</script>

