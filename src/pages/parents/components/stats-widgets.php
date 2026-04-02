<?php
// Widgets stats (Chart.js, stats démo si vide)
?>
<section class="mb-10">
    <h2 class="text-xl md:text-2xl font-bold text-indigo-800 mb-5 flex items-center gap-2">
        <span class="text-lg">📊</span> Statistiques de la plateforme
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-2xl shadow p-6 flex flex-col items-center w-full max-w-md mx-auto mcs-card-bg">
            <canvas id="chart-satisfaction" class="w-full max-w-[220px] h-[120px]" style="max-width:220px;max-height:120px;"></canvas>
            <div class="mt-2 text-xs text-gray-700">95% de parents satisfaits</div>
        </div>
        <div class="rounded-2xl shadow p-6 flex flex-col items-center w-full max-w-md mx-auto mcs-card-bg">
            <canvas id="chart-progression" class="w-full max-w-[220px] h-[120px]" style="max-width:220px;max-height:120px;"></canvas>
            <div class="mt-2 text-xs text-gray-700">Progression moyenne : 78%</div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Chart.js démo harmonisé
    new Chart(document.getElementById('chart-satisfaction'), {
        type: 'doughnut',
        data: {
            labels: ['Satisfaits', 'Autres'],
            datasets: [{
                data: [95, 5],
                backgroundColor: ['#6366f1', '#e0e7ff'],
                borderWidth: 0
            }]
        },
        options: { cutout: '75%', plugins: { legend: { display: false } }, responsive: true, maintainAspectRatio: false }
    });
    new Chart(document.getElementById('chart-progression'), {
        type: 'bar',
        data: {
            labels: ['6e', '5e', '4e', '3e', '2nde'],
            datasets: [{
                label: 'Progression',
                data: [80, 75, 78, 77, 80],
                backgroundColor: '#6366f1',
                borderRadius: 6
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 100 } }, responsive: true, maintainAspectRatio: false }
    });
    </script>
</section>
