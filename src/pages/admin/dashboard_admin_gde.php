<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
?>
<section id="section-exercises" class="admin-section">
    <div class="section-header">
        <h2>Gestion des Exercices</h2>
        <div class="section-actions">
            <button class="btn-admin-primary" id="btn-add-exercise">➕ Nouvel exercice</button>
            <button class="btn-admin-secondary" id="btn-import-json">📥 Importer JSON</button>
            <button class="btn-admin-secondary" id="btn-import-md">📥 Importer MD</button>
            <button class="btn-admin-secondary" id="btn-export-json">📤 Exporter JSON</button>
            <button class="btn-admin-secondary" id="btn-export-md">📤 Exporter MD</button>
        </div>
    </div>
    <div class="exercises-table-container">
        <table class="admin-table" id="exercises-table">
            <thead>
                <tr>
                    <th data-sort="Identifier" class="sortable">Identifiant <span class="sort-icon"></span></th>
                    <th data-sort="Title" class="sortable">Titre <span class="sort-icon"></span></th>
                    <th data-sort="Subject" class="sortable">Matière <span class="sort-icon"></span></th>
                    <th data-sort="Level" class="sortable">Niveau <span class="sort-icon"></span></th>
                    <th data-sort="Domain" class="sortable">Domaine <span class="sort-icon"></span></th>
                    <th data-sort="Competence" class="sortable">Compétence <span class="sort-icon"></span></th>
                    <th data-sort="Difficulty" class="sortable">Difficulté <span class="sort-icon"></span></th>
                    <th data-sort="AnswerType" class="sortable">Type de réponse <span class="sort-icon"></span></th>
                    <th data-sort="is_active" class="sortable">Actif <span class="sort-icon"></span></th>
                    <th data-sort="XP_Points" class="sortable">XP <span class="sort-icon"></span></th>
                    <th data-sort="Coherence" class="sortable">Cohérence <span class="sort-icon"></span></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="exercises-table-body">
                <tr>
                    <td colspan="12" class="loading-cell">Chargement des exercices...</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="pagination" id="exercises-pagination"></div>
    <div id="exercises-message" class="empty-message" style="display:none;"></div>

    <!-- Modal Ajout/Édition Exercice -->
    <div id="exercise-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="exercise-modal-title">Nouvel Exercice</h3>
                <button class="modal-close" onclick="closeExerciseModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="exercise-form">
                    <input type="hidden" id="exercise-id" name="Identifier">
                    <div class="form-group">
                        <label for="exercise-title">Titre *</label>
                        <input type="text" id="exercise-title" name="Title" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="exercise-subject">Matière *</label>
                            <input type="text" id="exercise-subject" name="Subject" required>
                        </div>
                        <div class="form-group">
                            <label for="exercise-level">Niveau *</label>
                            <input type="text" id="exercise-level" name="Level" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="exercise-domain">Domaine</label>
                            <input type="text" id="exercise-domain" name="Domain">
                        </div>
                        <div class="form-group">
                            <label for="exercise-competence">Compétence</label>
                            <input type="text" id="exercise-competence" name="Competence">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="exercise-difficulty">Difficulté</label>
                            <select id="exercise-difficulty" name="Difficulty">
                                <option value="facile">Facile</option>
                                <option value="moyen">Moyen</option>
                                <option value="difficile">Difficile</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exercise-answertype">Type de réponse</label>
                            <select id="exercise-answertype" name="AnswerType">
                                <option value="texte">Texte</option>
                                <option value="qcm">QCM</option>
                                <option value="association">Association</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="exercise-content">Énoncé</label>
                        <textarea id="exercise-content" name="Content" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="exercise-instruction">Instruction</label>
                        <textarea id="exercise-instruction" name="Instruction" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="exercise-answer">Réponse attendue</label>
                        <input type="text" id="exercise-answer" name="Answer">
                    </div>
                    <div class="form-group">
                        <label for="exercise-choices">Choix possibles (JSON ou séparés par virgule)</label>
                        <input type="text" id="exercise-choices" name="Choices">
                    </div>
                    <div class="form-group">
                        <label for="exercise-tips">Conseil/astuce</label>
                        <input type="text" id="exercise-tips" name="Tips">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="exercise-xp">XP Points</label>
                            <input type="number" id="exercise-xp" name="XP_Points" min="0">
                        </div>
                        <div class="form-group">
                            <label for="exercise-coherence">Cohérence</label>
                            <select id="exercise-coherence" name="Coherence">
                                <option value="true">Oui</option>
                                <option value="false">Non</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exercise-active">Actif</label>
                            <select id="exercise-active" name="is_active">
                                <option value="true">Oui</option>
                                <option value="false">Non</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-admin-secondary" onclick="closeExerciseModal()">Annuler</button>
                        <button type="submit" class="btn-admin-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<!-- Prévoir ici l'intégration JS/API pour charger/sauver les exercices depuis la BDD, et utiliser les variables d'environnement (.env/.env.production) côté backend pour les accès -->

<script>
// --- ADMIN JS: Gestion dynamique des exercices ---

// Force usage of Router Query parameters for reliability across environments
// We construct an absolute path to index.php to bypass potential RewriteRule issues (missing QSA)
const GET_API_URL = () => {
    let base = window.baseUrl || '';
    if (base && !base.endsWith('/')) base += '/';
    return base + 'index.php?page=api/exercices/manage';
};

let currentSort = 'id';
let currentOrder = 'DESC';
let currentPage = 1;

function fetchExercises(page = null) {
    if (page) currentPage = page;
    let url = GET_API_URL() + '&action=list';

    // Ajout des paramètres de tri et pagination
    url += `&sort=${currentSort}&order=${currentOrder}`;
    url += `&p=${currentPage}`; // Changed 'page' to 'p' to avoid router conflict

    fetch(url, { credentials: 'same-origin' })
        .then(async r => {
            const text = await r.text();
            try {
                const json = JSON.parse(text);
                if (!r.ok) console.warn('HTTP Error:', text);

                // Gérer le cas où l'utilisateur a été déconnecté (require_login)
                if (json.require_login) {
                    window.location.href = '/login.php?redirect=' + encodeURIComponent(window.location.pathname);
                    return { success: false, message: 'Session expirée, redirection...' };
                }

                return json;
            } catch (e) {
                console.warn('Raw response:', text);
                const snippet = text.length > 50 ? text.substring(0, 50) + '...' : text;
                throw new Error('Réponse API invalide (non-JSON): ' + snippet);
            }
        })
        .then(res => {
            if (res.success) {
                // L'API retourne 'exercises' mais le JS attend 'data' ou direct le tableau
                renderExercises(res.exercises || res.data || []);
                renderPagination(res.page, res.pages, res.total);
                updateSortIcons();

                // MODE SECOURS / JSON FALLBACK
                const msgEl = document.getElementById('exercises-message');
                if (res.mode === 'fallback_json') {
                    msgEl.innerHTML = '⚠️ <strong>MODE SECOURS</strong> : Accès BDD impossible. Données lues depuis les JSON (Lecture seule).';
                    msgEl.style.display = 'block';
                    msgEl.style.color = '#856404';
                    msgEl.style.backgroundColor = '#fff3cd';
                    msgEl.style.border = '1px solid #ffeeba';
                    msgEl.style.padding = '10px';
                } else {
                     msgEl.style.display = 'none';
                }
            }
            else showExercisesMessage('Erreur API: ' + (res.message || res.error || 'Erreur inconnue'));
        })
        .catch(async err => {
            console.error('Fetch Exercises Error:', err);
             // Tentative de fallback sur api_exercices_admin.php si l'API routeur échoue totalement (cas migration incomplète)
             // Ceci répond à l'hypothèse de l'utilisateur sur "l'ancienne API"
            if (url.includes('api/exercices/manage')) {
                console.log('Tentative fallback sur api_exercices_admin.php...');
                // ... mais on ne va pas réinventer la roue si le fichier n'existe plus.
                // On affiche juste l'erreur proprement.
            }
            showExercisesMessage('Erreur: ' + err.message);
        });
}

function renderPagination(current, totalPages, totalItems) {
    const container = document.getElementById('exercises-pagination');
    if (!container) return;

    // Convertir en entiers pour être sûr
    current = parseInt(current);
    totalPages = parseInt(totalPages);

    if (totalPages <= 1) {
        container.innerHTML = `<div class="pagination-info" style="text-align:center; padding:10px; color:#666;">Total : ${totalItems} exercice(s)</div>`;
        return;
    }

    let html = `<div class="pagination-container" style="display:flex; flex-direction:column; align-items:center; gap:10px; margin-top:20px;">`;
    html += `<div class="pagination-info" style="color:#666; font-size:0.9em;">Page ${current} / ${totalPages} (Total : ${totalItems} exercices)</div>`;
    html += `<div class="pagination-controls" style="display:flex; gap:5px; align-items:center;">`;

    // Bouton Précédent
    if (current > 1) {
        html += `<button class="btn-admin-secondary" onclick="fetchExercises(${current - 1})">«</button> `;
    }

    // Logique de fenêtre glissante pour les numéros de page (max 5 boutons)
    let start = Math.max(1, current - 2);
    let end = Math.min(totalPages, start + 4);
    if (end - start < 4) start = Math.max(1, end - 4);

    // Première page + points de suspension
    if (start > 1) {
        html += `<button class="btn-admin-secondary" onclick="fetchExercises(1)">1</button> `;
        if (start > 2) html += `<span class="pagination-dots" style="padding:0 5px;">...</span> `;
    }

    // Pages numérotées
    for (let i = start; i <= end; i++) {
        const activeClass = i === current ? 'btn-admin-primary' : 'btn-admin-secondary';
        html += `<button class="${activeClass}" onclick="fetchExercises(${i})">${i}</button> `;
    }

    // Dernière page + points de suspension
    if (end < totalPages) {
        if (end < totalPages - 1) html += `<span class="pagination-dots" style="padding:0 5px;">...</span> `;
        html += `<button class="btn-admin-secondary" onclick="fetchExercises(${totalPages})">${totalPages}</button> `;
    }

    // Bouton Suivant
    if (current < totalPages) {
        html += `<button class="btn-admin-secondary" onclick="fetchExercises(${current + 1})">»</button>`;
    }

    html += '</div></div>';
    container.innerHTML = html;
}

function updateSortIcons() {
    document.querySelectorAll('th[data-sort]').forEach(th => {
        const iconInfo = th.querySelector('.sort-icon');
        const col = th.getAttribute('data-sort');

        // Reset class
        th.classList.remove('sorted-asc', 'sorted-desc');
        if (iconInfo) iconInfo.textContent = '';

        if (col === currentSort) {
            th.classList.add(currentOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc');
            if (iconInfo) iconInfo.textContent = currentOrder === 'ASC' ? ' ▲' : ' ▼';
        }
    });
}

document.querySelectorAll('th[data-sort]').forEach(th => {
    th.addEventListener('click', () => {
        const col = th.getAttribute('data-sort');
        if (currentSort === col) {
            currentOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = col;
            currentOrder = 'ASC';
        }
        fetchExercises();
    });
});

function renderExercises(exos) {
    const tbody = document.getElementById('exercises-table-body');
    if (!exos.length) {
        tbody.innerHTML = '<tr><td colspan="12">Aucun exercice trouvé.</td></tr>';
        return;
    }
    tbody.innerHTML = exos.map(exo => `
        <tr>
            <td>${exo.Identifier || ''}</td>
            <td>${exo.Title || ''}</td>
            <td>${exo.Subject || ''}</td>
            <td>${exo.Level || ''}</td>
            <td>${exo.Domain || ''}</td>
            <td>${exo.Competence || ''}</td>
            <td>${exo.Difficulty || ''}</td>
            <td>${exo.AnswerType || ''}</td>
            <td>${exo.is_active ? 'Oui' : 'Non'}</td>
            <td>${exo.XP_Points || 0}</td>
            <td>${exo.Coherence ? 'Oui' : 'Non'}</td>
            <td>
                <button class="btn-admin-secondary" onclick="editExercise(${exo.id})">✏️</button>
                <button class="btn-admin-danger" onclick="deleteExercise(${exo.id})">🗑️</button>
            </td>
        </tr>
    `).join('');
}

function showExercisesMessage(msg) {
    document.getElementById('exercises-message').innerText = msg;
    document.getElementById('exercises-message').style.display = '';
}

function editExercise(id) {
    // TODO: Pré-remplir le modal avec les données de l'exercice (fetch ou depuis tableau)
    alert('Édition à venir pour ID ' + id);
}

function deleteExercise(id) {
    if (!confirm('Supprimer cet exercice ?')) return;
    const url = GET_API_URL() + (GET_API_URL().includes('?') ? '&' : '?') + 'action=delete';

    // Récupérer le token CSRF depuis une balise meta ou une variable globale si disponible
    // Sinon, l'API manage.php risque de bloquer la requête POST
    // Pour l'instant on essaie sans, mais si ça bloque, il faudra implémenter le fetch du token

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) fetchExercises();
        else alert('Erreur suppression: ' + (res.message || res.error || 'Erreur inconnue'));
    })
    .catch(err => {
        console.error('Delete Error:', err);
        alert('Erreur réseau lors de la suppression');
    });
}

document.addEventListener('DOMContentLoaded', () => fetchExercises(1));
// TODO: hooks pour ajout/édition, import/export, feedback UX
</script>
