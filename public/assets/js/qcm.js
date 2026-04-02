/* Simple QCM interactivity helper for MonCoachScolaire
   - Looks for .qcm-question elements and toggles selection
   - On .qcm-check-button click, grades the set and reveals feedback
  - Optionally posts progress to /api/exercices/save-progress
*/
(function () {
  function initQcm(root = document) {
    root.querySelectorAll('.qcm-question').forEach(q => {
      q.querySelectorAll('[data-qcm-option]').forEach(opt => {
        opt.addEventListener('click', function () {
          // Radio behavior: toggle active class and unselect siblings
          q.querySelectorAll('[data-qcm-option]').forEach(o => o.classList.remove('qcm-selected'));
          this.classList.add('qcm-selected');
        });
      });
    });

    root.querySelectorAll('.qcm-check-button').forEach(btn => {
      btn.addEventListener('click', async function () {
        const container = this.closest('.qcm');
        const questions = container.querySelectorAll('.qcm-question');
        let correctCount = 0;
        let total = questions.length;

        questions.forEach(q => {
          const selected = q.querySelector('.qcm-selected');
          const answer = q.dataset.qcmAnswer || '';
          if (selected && selected.dataset.qcmOption === answer) {
            correctCount++;
            selected.classList.add('qcm-correct');
          } else if (selected) {
            selected.classList.add('qcm-wrong');
            // reveal correct option
            const correctEl = q.querySelector('[data-qcm-option="' + answer + '"]');
            if (correctEl) correctEl.classList.add('qcm-correct');
          } else {
            // no selection
            const correctEl = q.querySelector('[data-qcm-option="' + answer + '"]');
            if (correctEl) correctEl.classList.add('qcm-correct');
          }
        });

        const scoreEl = container.querySelector('.qcm-score');
        if (scoreEl) {
          scoreEl.textContent = `${correctCount} / ${total}`;
          scoreEl.classList.add('visible');
        }

        // Optional: send to API if save button exists
        const saveBtn = container.querySelector('.qcm-save-button');
        if (saveBtn && saveBtn.dataset.exerciseId) {
          const payload = {
            exerciseId: parseInt(saveBtn.dataset.exerciseId, 10) || null,
            score: Math.round((correctCount / total) * 100),
            correct: correctCount === total ? 1 : 0
          };

          try {
            const headers = { 'Content-Type': 'application/json' };
            if (window.csrfToken) {
              headers['X-CSRF-Token'] = window.csrfToken;
              payload.csrf_token = window.csrfToken;
            }

            const res = await fetch('/api/exercices/save-progress', {
              method: 'POST',
              headers,
              credentials: 'same-origin',
              body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (res.ok) {
              // show feedback
              saveBtn.textContent = 'Enregistré ✅';
              saveBtn.disabled = true;
            } else {
              saveBtn.textContent = json.error || 'Erreur';
            }
          } catch (e) {
            saveBtn.textContent = 'Erreur réseau';
          }
        }
      });
    });
  }

  // Initialize when DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initQcm(document));
  } else initQcm(document);

  // expose to global for tests to call
  window.__MCS_QCM = { initQcm };
})();
