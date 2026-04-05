<?php
// Widgets stats parent avec données réelles et conteneurs Chart.js stables.

$progressions = isset($progressions) && is_array($progressions) ? $progressions : [];
$matieresFortes = isset($matieresFortes) && is_array($matieresFortes) ? $matieresFortes : [];

$childLabels = [];
$childScores = [];
$totalQuiz = 0;
$totalPassed = 0;

foreach ($progressions as $progression) {
    $label = trim((string) ($progression['nom'] ?? 'Enfant'));
    $score = round((float) ($progression['pourcent'] ?? 0), 1);
    $quizCount = max(0, (int) ($progression['total_quiz'] ?? 0));
    $passedCount = max(0, (int) ($progression['passés'] ?? 0));

    $childLabels[] = $label !== '' ? $label : 'Enfant';
    $childScores[] = max(0, min(100, $score));
    $totalQuiz += $quizCount;
    $totalPassed += min($passedCount, $quizCount > 0 ? $quizCount : $passedCount);
}

$totalReview = max(0, $totalQuiz - $totalPassed);
$averageProgress = !empty($childScores)
    ? round(array_sum($childScores) / count($childScores), 1)
    : 0;

$subjectBuckets = [];
foreach ($matieresFortes as $subjectRow) {
    $subjectName = trim((string) ($subjectRow['nom'] ?? 'Général'));
    $subjectScore = round((float) ($subjectRow['score'] ?? 0), 1);
    $subjectExercises = max(1, (int) ($subjectRow['exos'] ?? 1));

    if (!isset($subjectBuckets[$subjectName])) {
        $subjectBuckets[$subjectName] = [
            'weighted_score' => 0,
            'attempts' => 0,
        ];
    }

    $subjectBuckets[$subjectName]['weighted_score'] += $subjectScore * $subjectExercises;
    $subjectBuckets[$subjectName]['attempts'] += $subjectExercises;
}

$subjectLabels = [];
$subjectScores = [];
foreach ($subjectBuckets as $subjectName => $bucket) {
    $attempts = max(1, (int) $bucket['attempts']);
    $subjectLabels[] = $subjectName;
    $subjectScores[] = round($bucket['weighted_score'] / $attempts, 1);
}

if (empty($subjectLabels)) {
    $subjectLabels = ['Aucune matière disponible'];
    $subjectScores = [0];
}

$hasLinkedChildren = !empty($childLabels);

$chartProgressPayload = [
    'labels' => $hasLinkedChildren ? $childLabels : ['Aucun enfant rattaché'],
    'data' => !empty($childScores) ? $childScores : [0],
];

$chartSuccessPayload = [
    'labels' => ['Quiz réussis', 'Quiz à revoir'],
    'data' => [max(0, $totalPassed), max(0, $totalReview)],
];

$chartSubjectsPayload = [
    'labels' => $subjectLabels,
    'data' => $subjectScores,
];
?>
<section class="mb-10">
    <h2 class="text-xl md:text-2xl font-bold text-indigo-800 mb-5 flex items-center gap-2">
        <span class="text-lg">📊</span> Statistiques de suivi
    </h2>
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="rounded-2xl shadow p-6 flex flex-col mcs-card-bg">
            <h3 class="text-base font-bold text-slate-800 mb-4">Progression par enfant</h3>
            <div class="parent-chart-shell parent-chart-shell--bar">
                <canvas id="chart-parent-progress"></canvas>
            </div>
            <div class="mt-3 text-xs text-gray-700">
                <?php if ($hasLinkedChildren): ?>
                    Progression moyenne observée : <?php echo htmlspecialchars((string) $averageProgress); ?>%
                <?php else: ?>
                    La progression apparaîtra ici dès qu’un enfant sera rattaché à ce compte.
                <?php endif; ?>
            </div>
        </div>
        <div class="rounded-2xl shadow p-6 flex flex-col mcs-card-bg">
            <h3 class="text-base font-bold text-slate-800 mb-4">Répartition des quiz</h3>
            <div class="parent-chart-shell parent-chart-shell--doughnut">
                <canvas id="chart-parent-success"></canvas>
            </div>
            <div class="mt-3 text-xs text-gray-700">
                <?php if ($hasLinkedChildren): ?>
                    <?php echo htmlspecialchars((string) $totalPassed); ?> quiz réussis sur <?php echo htmlspecialchars((string) $totalQuiz); ?> tentative(s)
                <?php else: ?>
                    Les quiz réussis et les quiz à revoir seront comptés automatiquement après les premières activités.
                <?php endif; ?>
            </div>
        </div>
        <div class="rounded-2xl shadow p-6 flex flex-col mcs-card-bg">
            <h3 class="text-base font-bold text-slate-800 mb-4">Matières les plus solides</h3>
            <div class="parent-chart-shell parent-chart-shell--bar">
                <canvas id="chart-parent-subjects"></canvas>
            </div>
            <div class="mt-3 text-xs text-gray-700">
                <?php if ($hasLinkedChildren): ?>
                    Les scores sont calculés à partir des résultats réels agrégés du dashboard parent.
                <?php else: ?>
                    Les matières les plus solides seront calculées à partir des résultats réels dès qu’un enfant commencera des quiz.
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        const progressPayload = <?php echo json_encode($chartProgressPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const successPayload = <?php echo json_encode($chartSuccessPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        const subjectsPayload = <?php echo json_encode($chartSubjectsPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        function initParentDashboardCharts() {
            if (typeof window.Chart === 'undefined') {
                return;
            }

            window.__parentDashboardCharts = window.__parentDashboardCharts || {};

            Object.keys(window.__parentDashboardCharts).forEach(function (chartKey) {
                const chartInstance = window.__parentDashboardCharts[chartKey];
                if (chartInstance && typeof chartInstance.destroy === 'function') {
                    chartInstance.destroy();
                }
            });

            const progressCanvas = document.getElementById('chart-parent-progress');
            const successCanvas = document.getElementById('chart-parent-success');
            const subjectsCanvas = document.getElementById('chart-parent-subjects');

            if (progressCanvas) {
                window.__parentDashboardCharts.progress = new window.Chart(progressCanvas, {
                    type: 'bar',
                    data: {
                        labels: progressPayload.labels,
                        datasets: [{
                            label: 'Progression moyenne',
                            data: progressPayload.data,
                            backgroundColor: '#4f46e5',
                            hoverBackgroundColor: '#4338ca',
                            borderRadius: 10,
                            maxBarThickness: 42,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        resizeDelay: 150,
                        animation: false,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    callback: function (value) {
                                        return value + '%';
                                    },
                                },
                            },
                        },
                    },
                });
            }

            if (successCanvas) {
                window.__parentDashboardCharts.success = new window.Chart(successCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: successPayload.labels,
                        datasets: [{
                            data: successPayload.data,
                            backgroundColor: ['#10b981', '#f59e0b'],
                            borderColor: '#ffffff',
                            borderWidth: 2,
                            hoverOffset: 4,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        resizeDelay: 150,
                        animation: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                },
                            },
                        },
                    },
                });
            }

            if (subjectsCanvas) {
                window.__parentDashboardCharts.subjects = new window.Chart(subjectsCanvas, {
                    type: 'bar',
                    data: {
                        labels: subjectsPayload.labels,
                        datasets: [{
                            label: 'Score moyen',
                            data: subjectsPayload.data,
                            backgroundColor: '#7c3aed',
                            hoverBackgroundColor: '#6d28d9',
                            borderRadius: 10,
                            maxBarThickness: 42,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        resizeDelay: 150,
                        animation: false,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            x: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    callback: function (value) {
                                        return value + '%';
                                    },
                                },
                            },
                        },
                    },
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initParentDashboardCharts, { once: true });
            return;
        }

        initParentDashboardCharts();
    })();
    </script>
    <style>
    .parent-chart-shell {
        position: relative;
        width: 100%;
        min-height: 220px;
        height: 220px;
        max-height: 220px;
    }

    .parent-chart-shell--doughnut {
        min-height: 240px;
        height: 240px;
        max-height: 240px;
    }

    .parent-chart-shell canvas {
        display: block;
        width: 100% !important;
        height: 100% !important;
    }
    </style>
</section>

