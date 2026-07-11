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


<main class="min-h-screen px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex max-w-6xl flex-col gap-8">
        <section class="rounded-[2rem] border border-slate-200/70 bg-white/90 p-6 shadow-[0_24px_70px_-30px_rgba(15,23,42,0.35)] backdrop-blur-sm sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-700">Suivi personnel</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Mes Statistiques</h1>
                    <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-base">Visualise tes progrès, repère tes habitudes et identifie les prochaines étapes à travailler avec clarté.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($periods as $key => $p): ?>
                    <a href="?period=<?= $key ?>"
                       class="rounded-full border px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-blue-500 <?= $period === $key ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' ?>">
                        <?= $p['label'] ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="flex flex-col items-start rounded-[1.5rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
                <div class="mb-3 text-3xl">⏱️</div>
                <div class="text-2xl font-bold text-blue-700"><?= formatDuration($stats['global']['total_time'] ?? 0) ?></div>
                <div class="mt-1 text-sm text-slate-500">Temps d'étude</div>
            </div>
            <div class="flex flex-col items-start rounded-[1.5rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
                <div class="mb-3 text-3xl">🔥</div>
                <div class="text-2xl font-bold text-orange-600"><?= $stats['streak'] ?> jours</div>
                <div class="mt-1 text-sm text-slate-500">Série actuelle</div>
            </div>
            <div class="flex flex-col items-start rounded-[1.5rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
                <div class="mb-3 text-3xl">✏️</div>
                <div class="text-2xl font-bold text-green-600"><?= $stats['global']['total_exercises'] ?? 0 ?></div>
                <div class="mt-1 text-sm text-slate-500">Exercices complétés</div>
            </div>
            <div class="flex flex-col items-start rounded-[1.5rem] border border-slate-200 bg-white/90 p-5 shadow-sm">
                <div class="mb-3 text-3xl">📈</div>
                <div class="text-2xl font-bold text-purple-600"><?= round($stats['global']['avg_score'] ?? 0) ?>%</div>
                <div class="mt-1 text-sm text-slate-500">Score moyen</div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <article class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm sm:p-7">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">📊 Temps d'étude quotidien</h2>
                        <p class="mt-1 text-sm text-slate-500">Une lecture simple de ton rythme d'entraînement sur les jours passés.</p>
                    </div>
                </div>
                <div class="rounded-[1.25rem] border border-slate-100 bg-slate-50/70 p-4">
                    <canvas id="study-time-chart"></canvas>
                </div>
            </article>

            <article class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm sm:p-7">
                <h2 class="text-xl font-semibold text-slate-900">🔥 Activité de l'année</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Un aperçu visuel de ta régularité sur l'année.</p>
                <div class="mt-5 flex flex-col gap-4">
                    <div class="grid grid-cols-53 gap-0.5" id="activity-heatmap"></div>
                    <div class="flex items-center gap-1 text-xs text-slate-500">
                        <span>Moins</span>
                        <div class="h-4 w-4 rounded border border-slate-200 bg-slate-100"></div>
                        <div class="h-4 w-4 rounded border border-slate-200 bg-blue-100"></div>
                        <div class="h-4 w-4 rounded border border-slate-200 bg-blue-300"></div>
                        <div class="h-4 w-4 rounded border border-slate-200 bg-blue-500"></div>
                        <div class="h-4 w-4 rounded border border-slate-200 bg-blue-700"></div>
                        <span>Plus</span>
                    </div>
                </div>
            </article>
        </section>

        <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm sm:p-7">
            <h2 class="text-xl font-semibold text-slate-900">📚 Matières les plus étudiées</h2>
            <div class="mt-5 flex flex-col gap-3">
            <?php foreach ($stats['top_subjects'] as $subject): ?>
            <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                <span class="w-32 text-sm font-medium text-slate-700"><?= htmlspecialchars($subject['Subject']) ?></span>
                <div class="flex-1 h-3 overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-blue-700 transition-all duration-500"></div>
                </div>
                <span class="w-16 text-right text-xs font-medium text-slate-500"><?= formatDuration($subject['total_time']) ?></span>
            </div>
            <?php endforeach; ?>
            </div>
        </section>

        <?php if (!empty($goals)): ?>
        <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm sm:p-7">
            <h2 class="text-xl font-semibold text-slate-900">🎯 Mes Objectifs</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
            <?php foreach ($goals as $goal): ?>
            <div class="flex flex-col gap-2 rounded-[1.25rem] border border-slate-100 bg-slate-50/70 p-4">
                <h3 class="font-semibold text-blue-700"><?= formatGoalTitle($goal['GoalType']) ?></h3>
                <div class="mb-2 h-3 w-full overflow-hidden rounded-full bg-slate-200">
                    <div class="h-full rounded-full bg-gradient-to-r from-green-400 to-green-600 transition-all duration-500"></div>
                </div>
                <span class="text-sm text-slate-600"><?= $goal['CurrentValue'] ?> / <?= $goal['TargetValue'] ?></span>
                <p class="text-sm text-slate-500">⏳ <?= round($goal['remaining_days']) ?> jour<?= $goal['remaining_days'] > 1 ? 's' : '' ?> restant<?= $goal['remaining_days'] > 1 ? 's' : '' ?></p>
            </div>
            <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="rounded-[2rem] border border-slate-200 bg-white/90 p-6 shadow-sm sm:p-7">
            <h2 class="text-xl font-semibold text-slate-900">📅 Activité récente</h2>
            <div class="mt-5 flex flex-col gap-3">
                <?php foreach ($timeline as $event): ?>
                <div class="flex items-center gap-3 rounded-[1.25rem] border border-slate-100 bg-slate-50/70 p-4">
                    <div class="flex-shrink-0 text-2xl"><?= getEventIcon($event['EventType']) ?></div>
                    <div class="flex-1">
                        <p class="text-sm text-slate-700"><?= formatEventText($event) ?></p>
                        <span class="mt-1 text-xs text-slate-500"><?= timeAgo($event['CreatedAt']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
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

