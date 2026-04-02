// Exercises UI interactions: collapsible cards, accessible toggles, simple animations
document.addEventListener('DOMContentLoaded', function() {
    // Toggle card content
    document.querySelectorAll('.exercise-card .card-toggle').forEach(btn => {
        // Ensure aria-controls exists and target element is present
        const targetId = btn.getAttribute('aria-controls');
        const target = targetId ? document.getElementById(targetId) : null;
        btn.addEventListener('click', function(e) {
            const card = btn.closest('.exercise-card');
            if (!card) return;
            const content = target || card.querySelector('.card-content');
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', (!expanded).toString());
            if (!expanded) {
                content.style.maxHeight = content.scrollHeight + 'px';
                content.setAttribute('aria-hidden', 'false');
                card.classList.add('open');
                announceForA11y('Contenu affiché: ' + (card.querySelector('.card-title') ? card.querySelector('.card-title').textContent.trim() : 'exercice'));
            } else {
                content.style.maxHeight = null;
                content.setAttribute('aria-hidden', 'true');
                card.classList.remove('open');
                announceForA11y('Contenu masqué');
            }
        });
    });

    // Live region for screen reader announcements
    function ensureLiveRegion(){
        let lr = document.getElementById('exercises-live-region');
        if (!lr) {
            lr = document.createElement('div');
            lr.id = 'exercises-live-region';
            lr.setAttribute('aria-live','polite');
            lr.style.position = 'absolute';
            lr.style.left = '-9999px';
            document.body.appendChild(lr);
        }
        return lr;
    }

    function announceForA11y(msg){
        const lr = ensureLiveRegion();
        lr.textContent = '';
        setTimeout(()=>{ lr.textContent = msg; }, 100);
    }

    // Ensure keyboard accessibility on Enter/Space
    document.querySelectorAll('.exercise-card .card-toggle').forEach(btn => {
        btn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                btn.click();
            }
        });
    });

    // Simple action handlers (start / verify)
    document.body.addEventListener('click', function(e) {
        const start = e.target.closest('[data-action="start-exercise"]');
        if (start) {
            e.preventDefault();
            const id = start.dataset.id;
            // Navigate to exercise detail page
            if (id) {
                window.location.href = window.location.pathname + '?page=view_exercise&id=' + encodeURIComponent(id);
            }
        }
        const verify = e.target.closest('[data-action="verify-exercise"]');
        if (verify) {
            e.preventDefault();
            // For now, show a small toast / alert
            const card = verify.closest('.exercise-card');
            if (card) {
                const toast = document.createElement('div');
                toast.className = 'exercise-toast';
                toast.textContent = 'Vérification envoyée (simulation)';
                document.body.appendChild(toast);
                setTimeout(() => { toast.classList.add('visible'); }, 10);
                setTimeout(() => { toast.classList.remove('visible'); setTimeout(() => toast.remove(), 300); }, 2000);
            }
        }
    });
});
