document.addEventListener('DOMContentLoaded', function(){
  // Toggle collapsible content for each exercise card
  document.querySelectorAll('.exercise-card').forEach(function(card){
    const toggle = card.querySelector('.exercise-toggle');
    const content = card.querySelector('.collapsible');
    // initialize based on aria-expanded
    if (!toggle || !content) return;
    const setOpen = function(open){
      if (open) {
        content.classList.add('open');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', "Masquer les détails de l'exercice");
        content.setAttribute('aria-hidden', 'false');
      } else {
        content.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', "Afficher les détails de l'exercice");
        content.setAttribute('aria-hidden', 'true');
      }
      // update sr-only text for screen readers
      const sr = toggle.querySelector('.sr-only');
      if (sr) sr.textContent = open ? 'Masquer les détails' : 'Afficher les détails';
    };
    // click handler
    toggle.addEventListener('click', function(e){
      e.stopPropagation();
      setOpen(toggle.getAttribute('aria-expanded') !== 'true');
    });
    // keyboard: Enter/Space on the card toggles
    card.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        setOpen(toggle.getAttribute('aria-expanded') !== 'true');
      }
    });

    // Quick actions: duplicate and toggle active
    const btnDup = card.querySelector('.btn-duplicate');
    const btnToggle = card.querySelector('.btn-toggle-active');
    if (btnDup) {
      btnDup.addEventListener('click', async function(){
        const id = this.dataset.exerciseId;
        this.disabled = true;
        const res = await fetch('/src/api/admin/exercises_actions.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action: 'duplicate', id: parseInt(id)})});
        const json = await res.json();
        if (json && json.success) {
          this.textContent = '✅ Dupliqué';
        } else {
          this.textContent = 'Erreur';
        }
        setTimeout(()=>{ this.disabled=false; this.textContent='📄 Dupliquer'; }, 1200);
      });
    }
    if (btnToggle) {
      btnToggle.addEventListener('click', async function(){
        const id = parseInt(this.dataset.exerciseId);
        this.disabled = true;
        const res = await fetch('/src/api/admin/exercises_actions.php', {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action: 'toggle_active', id: id})});
        const json = await res.json();
        if (json && json.success) {
          const active = json.is_active == 1;
          this.dataset.active = active? '1' : '0';
          if (active) { this.classList.remove('inactive'); this.textContent = '🔓 Actif'; }
          else { this.classList.add('inactive'); this.textContent = '🔒 Inactif'; }
        } else {
          this.textContent = 'Erreur';
        }
        setTimeout(()=>{ this.disabled=false; }, 800);
      });
    }
  });
});
