<?php
$page_title = 'Mes Statistiques - MonCoachScolaire';
$page_css = 'pages/stats.css';

$redirect_helpers = dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
if (is_file($redirect_helpers)) {
    require_once $redirect_helpers;
}

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/site_boot.php';
require_once dirname(__DIR__, 2) . '/includes/study_tracker.php';

// Vérifier connexion
if (!$is_logged_in) {
    $loginUrl = site_url('login');
    if (function_exists('safe_redirect')) {
        safe_redirect($loginUrl);
    }
    exit;
}

$userId = $_SESSION['user_id'];

// Période sélectionnée
$period = $_GET['period'] ?? '30days';
$periods = [
    '7days' => ['days' => 7, 'label' => '7 derniers jours'],
    '30days' => ['days' => 30, 'label' => '30 derniers jours'],
    '90days' => ['days' => 90, 'label' => '3 derniers mois'],
    'year' => ['days' => 365, 'label' => 'Cette année'],
];

$selectedPeriod = $periods[$period] ?? $periods['30days'];
$startDate = date('Y-m-d', strtotime("-{$selectedPeriod['days']} days"));
$endDate = date('Y-m-d');

// Récupérer les stats
$stats = getUserStats($userId, $startDate, $endDate);
$heatmap = getActivityHeatmap($userId);
$timeline = getActivityTimeline($userId, 10);
$goals = getUserGoals($userId);
?>


<main class="main-content max-w-5xl mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold flex items-center gap-2 mb-4">📊 Mes Statistiques</h1>
        <!-- Sélecteur de période -->
        <div class="flex gap-2 flex-wrap mb-4">
            <?php foreach ($periods as $key => $p): ?>
            <a href="?period=<?= $key ?>"
               class="px-4 py-2 rounded-lg border border-gray-300 bg-white shadow text-sm font-semibold transition <?= $period === $key ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-blue-50' ?>">
                <?= $p['label'] ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- KPIs principaux -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="flex flex-col items-center bg-white rounded-xl shadow p-4">
            <div class="text-3xl mb-2">⏱️</div>
            <div class="text-xl font-bold text-blue-700"><?= formatDuration($stats['global']['total_time'] ?? 0) ?></div>
            <div class="text-xs text-gray-500 mt-1">Temps d'étude</div>
        </div>
        <div class="flex flex-col items-center bg-white rounded-xl shadow p-4">
            <div class="text-3xl mb-2">🔥</div>
            <div class="text-xl font-bold text-orange-600"><?= $stats['streak'] ?> jours</div>
            <div class="text-xs text-gray-500 mt-1">Série actuelle</div>
        </div>
        <div class="flex flex-col items-center bg-white rounded-xl shadow p-4">
            <div class="text-3xl mb-2">✏️</div>
            <div class="text-xl font-bold text-green-600"><?= $stats['global']['total_exercises'] ?? 0 ?></div>
            <div class="text-xs text-gray-500 mt-1">Exercices complétés</div>
        </div>
        <div class="flex flex-col items-center bg-white rounded-xl shadow p-4">
            <div class="text-3xl mb-2">📈</div>
            <div class="text-xl font-bold text-purple-600"><?= round($stats['global']['avg_score'] ?? 0) ?>%</div>
            <div class="text-xs text-gray-500 mt-1">Score moyen</div>
        </div>
    </div>

    <!-- Graphique de temps d'étude -->
    <div class="mb-10">
        <h2 class="text-xl font-semibold mb-2">📊 Temps d'étude quotidien</h2>
        <div class="bg-white rounded-xl shadow p-4">
            <canvas id="study-time-chart"></canvas>
        </div>
    </div>

    <!-- Heatmap GitHub-style -->
    <div class="mb-10">
        <h2 class="text-xl font-semibold mb-2">🔥 Activité de l'année</h2>
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="grid grid-cols-53 gap-0.5" id="activity-heatmap"></div>
            <div class="flex items-center gap-1 mt-2 md:mt-0">
                <span class="text-xs text-gray-400">Moins</span>
                <div class="w-4 h-4 rounded bg-gray-200 border"></div>
                <div class="w-4 h-4 rounded bg-blue-100 border"></div>
                <div class="w-4 h-4 rounded bg-blue-300 border"></div>
                <div class="w-4 h-4 rounded bg-blue-500 border"></div>
                <div class="w-4 h-4 rounded bg-blue-700 border"></div>
                <span class="text-xs text-gray-400">Plus</span>
            </div>
        </div>
    </div>

    <!-- Matières les plus étudiées -->
    <div class="mb-10">
        <h2 class="text-xl font-semibold mb-2">📚 Matières les plus étudiées</h2>
        <div class="flex flex-col gap-2">
        <?php foreach ($stats['top_subjects'] as $subject): ?>
        <div class="flex items-center gap-2">
            <span class="w-32 text-sm font-medium text-gray-700"><?= htmlspecialchars($subject['Subject']) ?></span>
            <div class="flex-1 h-3 bg-gray-200 rounded overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-400 to-blue-700 transition-all duration-500"></div>
            </div>
            <span class="w-16 text-xs text-gray-500 text-right"><?= formatDuration($subject['total_time']) ?></span>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- Objectifs -->
    <?php if (!empty($goals)): ?>
    <div class="mb-10">
        <h2 class="text-xl font-semibold mb-2">🎯 Mes Objectifs</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($goals as $goal): ?>
        <div class="bg-white rounded-xl shadow p-4 flex flex-col gap-2">
            <h3 class="font-semibold text-blue-700 mb-1"><?= formatGoalTitle($goal['GoalType']) ?></h3>
            <div class="w-full h-3 bg-gray-200 rounded overflow-hidden mb-2">
                <div class="h-full bg-gradient-to-r from-green-400 to-green-600 transition-all duration-500"></div>
            </div>
            <span class="text-xs text-gray-500 mb-1"><?= $goal['CurrentValue'] ?> / <?= $goal['TargetValue'] ?></span>
            <p class="text-xs text-gray-400">⏳ <?= round($goal['remaining_days']) ?> jour<?= $goal['remaining_days'] > 1 ? 's' : '' ?> restant<?= $goal['remaining_days'] > 1 ? 's' : '' ?></p>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Timeline d'activité récente -->
    <div class="mb-10">
        <h2 class="text-xl font-semibold mb-2">📅 Activité récente</h2>
        <div class="flex flex-col gap-2">
            <?php foreach ($timeline as $event): ?>
            <div class="flex items-center gap-3 bg-white rounded-lg shadow p-3">
                <div class="text-2xl flex-shrink-0"><?= getEventIcon($event['EventType']) ?></div>
                <div class="flex-1">
                    <p class="text-sm text-gray-700 mb-1"><?= formatEventText($event) ?></p>
                    <span class="text-xs text-gray-400"><?= timeAgo($event['CreatedAt']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// Données pour le graphique
const chartData = <?= json_encode($stats['daily']) ?>;

// Créer le graphique
const ctx = document.getElementById('study-time-chart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.map(d => d.Date),
        datasets: [{
            label: 'Temps d\'étude (min)',
            data: chartData.map(d => Math.round(d.TotalStudyTime / 60)),
            borderColor: '#6366F1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (value) => value + ' min'
                }
            }
        }
    }
});

// Générer la heatmap
const heatmapData = <?= json_encode($heatmap) ?>;
generateHeatmap(heatmapData);

function generateHeatmap(data) {
    const container = document.getElementById('activity-heatmap');
    const year = new Date().getFullYear();

    // Générer tous les jours de l'année
    for (let month = 0; month < 12; month++) {
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        for (let day = 1; day <= daysInMonth; day++) {
            const date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const level = data[date]?.level || 0;
            const time = data[date]?.time || 0;

            const box = document.createElement('div');
            box.className = `heatmap-box level-${level}`;
            box.title = `${date}: ${Math.round(time / 60)} min`;
            container.appendChild(box);
        }
    }
}
</script>

<?php
// Fonctions d'affichage
function formatDuration($seconds)
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if ($hours > 0) {
        return "{$hours}h {$minutes}min";
    }
    return "{$minutes} min";
}

function formatGoalTitle($type)
{
    $titles = [
        'daily_time' => 'Étudier tous les jours',
        'weekly_exercises' => 'Compléter des exercices',
        'path_completion' => 'Terminer un parcours',
        'score_target' => 'Atteindre un score cible',
    ];
    return $titles[$type] ?? $type;
}

function getEventIcon($type)
{
    $icons = [
        'course_view' => '📖',
        'exercise_start' => '✏️',
        'exercise_complete' => '✅',
        'quiz_complete' => '❓',
        'badge_earned' => '🏆',
        'level_up' => '⬆️',
    ];
    return $icons[$type] ?? '📄';
}

function formatEventText($event)
{
    // À personnaliser selon le type d'événement
    return "Événement : " . str_replace('_', ' ', $event['EventType']);
}

function timeAgo($datetime)
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'À l\'instant';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . ' min';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . 'h';
    }
    return floor($diff / 86400) . 'j';
}
?>

