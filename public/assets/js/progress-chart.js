// progress-chart.js
// Initialise le graphique de progression sur le dashboard élève
// Nécessite Chart.js (inclusion <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> dans le dashboard)

// Récupérer les données PHP injectées dans la page (ex : window.progressData)
const progressData = window.progressData || [];

function buildProgressChart(period = 30) {
    const ctx = document.getElementById('progressChart');
    if (!ctx || !progressData.length) return;

    // Filtrer les données selon la période
    const now = new Date();
    const filtered = progressData.filter(row => {
        const d = new Date(row.Date);
        return (now - d) / (1000 * 60 * 60 * 24) <= period;
    });

    const labels = filtered.map(row => row.Date);
    const xp = filtered.map(row => row.XP);
    const exercises = filtered.map(row => row.ExercisesCompleted);
    const cristaux = filtered.map(row => row.Cristaux);

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'XP',
                    data: xp,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.2)',
                    tension: 0.3,
                },
                {
                    label: 'Exercices',
                    data: exercises,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.2)',
                    tension: 0.3,
                },
                {
                    label: 'Cristaux',
                    data: cristaux,
                    borderColor: '#f59e42',
                    backgroundColor: 'rgba(245,158,66,0.2)',
                    tension: 0.3,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Évolution de ta Progression' }
            }
        }
    });
}

// Initialisation par défaut (30 jours)
document.addEventListener('DOMContentLoaded', function() {
    buildProgressChart(30);
    // Gestion des boutons période
    document.querySelectorAll('.chart-period-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-period-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const days = parseInt(btn.dataset.days, 10) || 30;
            buildProgressChart(days);
        });
    });
});
