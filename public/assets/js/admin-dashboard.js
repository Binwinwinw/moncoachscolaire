                    /**
                     * Analyse qualité exercices (frontend)
                     */
                    function initExerciseQualityModule() {
                        const btnRefresh = document.getElementById('btn-exquality-refresh');
                        if (btnRefresh) btnRefresh.addEventListener('click', function() {
                            loadExerciseQualitySummary();
                            loadExerciseQualityList();
                        });
                        if (!document.getElementById('exquality-summary') && !document.getElementById('exquality-list')) return;
                        loadExerciseQualitySummary();
                        loadExerciseQualityList();
                    }

                    function loadExerciseQualitySummary() {
                        const url = apiUrl('exercise_quality.php', { type: 'summary' });
                        fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                            .then(data => {
                                const container = document.getElementById('exquality-summary');
                                if (!container) return;
                                if (data.success) {
                                    const d = data.data;
                                    container.innerHTML = `<ul>
                                        <li>Total exercices : <strong>${d.total}</strong></li>
                                        <li>Coherents : <strong>${d.coherents}</strong></li>
                                        <li>Faciles : <strong>${d.faciles}</strong></li>
                                        <li>Difficiles : <strong>${d.difficiles}</strong></li>
                                    </ul>`;
                                } else {
                                    container.textContent = 'Erreur: ' + (data.error || 'Inconnue');
                                }
                            });
                    }

                    function loadExerciseQualityList() {
                        const url = apiUrl('exercise_quality.php', { type: 'list' });
                        fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                            .then(data => {
                                const container = document.getElementById('exquality-list');
                                if (!container) return;
                                if (data.success) {
                                    container.innerHTML = data.data.map(e => `
                                        <div class='exquality-item${e.Coherence ? '' : ' incoherent'}'>
                                            <strong>${e.Title}</strong> <span class='exquality-id'>(${e.Identifier})</span>
                                            <span class='exquality-diff'>${e.Difficulty}</span>
                                            <span class='exquality-xp'>XP: ${e.XP_Points}</span>
                                            <span class='exquality-coh'>${e.Coherence ? '✔️' : '❌'}</span>
                                        </div>
                                    `).join('');
                                } else {
                                    container.textContent = 'Erreur: ' + (data.error || 'Inconnue');
                                }
                            });
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        // ...existing code...
                        initExerciseQualityModule();
                    });
                /**
                 * Gestion des ressources pédagogiques (frontend)
                 */
                function initResourcesModule() {
                    const btnRefresh = document.getElementById('btn-resources-refresh');
                    const btnAdd = document.getElementById('btn-resources-add');
                    if (!document.getElementById('resources-list')) return;
                    if (btnRefresh) btnRefresh.addEventListener('click', loadResourcesList);
                    if (btnAdd) btnAdd.addEventListener('click', showAddResourceForm);
                    loadResourcesList();
                }

                function loadResourcesList() {
                    const url = apiUrl('resources.php', { type: 'list' });
                    fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                        .then(data => {
                            const container = document.getElementById('resources-list');
                            if (!container) return;
                            if (data.success) {
                                container.innerHTML = data.data.map(r => `
                                    <div class='resource-item${r.Actif ? '' : ' inactive'}'>
                                        <strong>${r.Titre}</strong> <span class='resource-type'>(${r.Type})</span>
                                        <a href='${r.Lien}' target='_blank'>Lien</a>
                                        <span class='resource-date'>${r.Date_Ajout}</span>
                                        <button class='btn-toggle-resource' data-id='${r.id}'>${r.Actif ? 'Désactiver' : 'Activer'}</button>
                                    </div>
                                `).join('');
                                // Ajout des listeners pour activation/désactivation
                                container.querySelectorAll('.btn-toggle-resource').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        toggleResource(btn.dataset.id);
                                    });
                                });
                            } else {
                                container.textContent = 'Erreur: ' + (data.error || 'Inconnue');
                            }
                        });
                }

                function showAddResourceForm() {
                    const form = document.getElementById('resources-add-form');
                    if (form) form.style.display = 'block';
                    // Ajout du submit listener si pas déjà fait
                    if (form && !form.dataset.listener) {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            const titre = form.querySelector('[name="Titre"]').value;
                            const type = form.querySelector('[name="Type"]').value;
                            const lien = form.querySelector('[name="Lien"]').value;
                            addResource({ Titre: titre, Type: type, Lien: lien });
                        });
                        form.dataset.listener = '1';
                    }
                }

                function addResource(data) {
                    const url = apiUrl('resources.php', { type: 'add' });
                    const payload = Object.assign({}, data, { csrf_token: window.csrfToken || '' });
                    fetchJson(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-Token': window.csrfToken || ''
                        },
                        body: JSON.stringify(payload)
                    }).then(resp => {
                        if (resp.success) {
                            loadResourcesList();
                            const form = document.getElementById('resources-add-form');
                            if (form) form.reset();
                            if (form) form.style.display = 'none';
                        } else {
                            alert('Erreur ajout: ' + (resp.error || 'Inconnue'));
                        }
                    });
                }

                function toggleResource(id) {
                    const url = apiUrl('resources.php', { type: 'toggle' });
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('csrf_token', window.csrfToken || '');
                    fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'X-CSRF-Token': window.csrfToken || '' },
                        body: formData
                    }).then(resp => resp.json()).then(data => {
                        if (data.success) {
                            loadResourcesList();
                        } else {
                            alert('Erreur activation: ' + (data.error || 'Inconnue'));
                        }
                    });
                }

                document.addEventListener('DOMContentLoaded', function() {
                    // ...existing code...
                    initResourcesModule();
                });
            /**
             * Monitoring sécurité (frontend)
             */
            function initSecurityMonitoring() {
                const btnRefresh = document.getElementById('btn-security-refresh');
                if (!btnRefresh) return;
                btnRefresh.addEventListener('click', function() {
                    loadSecurityAlerts();
                    loadSecurityLogins();
                });
                // Chargement initial
                loadSecurityAlerts();
                loadSecurityLogins();
            }

            function loadSecurityAlerts() {
                const url = apiUrl('security.php', { type: 'alerts' });
                fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(data => {
                        const container = document.getElementById('security-alerts');
                        if (!container) return;
                        if (data.success) {
                            container.innerHTML = data.data.map(a => `<div class='alert-item'><strong>${a.Type}</strong> : ${a.Message} <span class='alert-date'>${a.Date}</span></div>`).join('');
                        } else {
                            container.textContent = 'Erreur: ' + (data.error || 'Inconnue');
                        }
                    })
                    .catch(err => {
                        const container = document.getElementById('security-alerts');
                        if (container) container.textContent = 'Erreur: ' + err.message;
                    });
            }

            function loadSecurityLogins() {
                const url = apiUrl('security.php', { type: 'logins' });
                fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(data => {
                        const container = document.getElementById('security-logins');
                        if (!container) return;
                        if (data.success) {
                            container.innerHTML = data.data.map(l => `<div class='login-item'>${l.Success ? '✅' : '❌'} ${l.IP} <span class='login-date'>${l.Date}</span></div>`).join('');
                        } else {
                            container.textContent = 'Erreur: ' + (data.error || 'Inconnue');
                        }
                    })
                    .catch(err => {
                        const container = document.getElementById('security-logins');
                        if (container) container.textContent = 'Erreur: ' + err.message;
                    });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // ...existing code...
                initSecurityMonitoring();
            });
        /**
         * Reporting personnalisé (frontend)
         */
        function initReporting() {
            const btnReport = document.getElementById('btn-report-generate');
            if (!btnReport) return;
            btnReport.addEventListener('click', function() {
                const type = document.getElementById('report-type')?.value || 'progression';
                const period = document.getElementById('report-period')?.value || '30d';
                const url = apiUrl('report.php', { type, period });
                fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(data => {
                        const status = document.getElementById('reporting-status');
                        const chart = document.getElementById('reporting-chart');
                        if (data.success) {
                            status.textContent = 'Rapport généré avec succès.';
                            chart.textContent = JSON.stringify(data.data, null, 2);
                        } else {
                            status.textContent = 'Erreur: ' + (data.error || 'Inconnue');
                            chart.textContent = '';
                        }
                    })
                    .catch(err => {
                        const status = document.getElementById('reporting-status');
                        status.textContent = 'Erreur: ' + err.message;
                    });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // ...existing code...
            initReporting();
        });
    /**
     * Export avancé (frontend)
     */
    function initExportAdvanced() {
        const btnExport = document.getElementById('btn-export-download');
        if (!btnExport) return;
        btnExport.addEventListener('click', function() {
            const type = document.getElementById('export-type')?.value || 'users';
            const format = document.getElementById('export-format')?.value || 'csv';
            const url = apiUrl('export.php', { type, format });
            window.open(url, '_blank');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // ...existing code...
        initExportAdvanced();
    });
/**
 * Dashboard Administrateur - JavaScript
 * Gestion des interactions, API calls, et mise à jour en temps réel
 */

(function() {
    'use strict';

    // Configuration
    // window.baseUrl défini par le serveur :
    // - LOCAL: '/moncoachscolaire/public' (pour les assets)
    // - PROD: '' (pour les assets)
    // Pour les APIs :
    // - PROD: /api/admin (avec .htaccess qui rewrite)
    // - LOCAL: /moncoachscolaire/public/index.php?page=api/admin (direct au routeur)
    let API_BASE = '/api/admin';
    let USE_DIRECT_ROUTER = false; // true en LOCAL si .htaccess ne fonctionne pas

    if (typeof window.baseUrl !== 'undefined' && window.baseUrl) {
        // LOCAL: window.baseUrl = '/moncoachscolaire/public'
        const baseWithoutPublic = window.baseUrl.replace(/\/public$/, '');

        // En local, utiliser directement le routeur (pas de .htaccess)
        USE_DIRECT_ROUTER = true;
        API_BASE = baseWithoutPublic + '/public/index.php?page=api/admin';
        console.log('🔧 API_BASE (direct router, LOCAL):', API_BASE);
    } else {
        // PROD: window.baseUrl = '' → API_BASE = '/api/admin'
        console.log('🔧 API_BASE (PROD):', API_BASE);
    }

    // Helper function pour construire les URLs d'API
    function apiUrl(endpoint, params = {}) {
        // Retirer .php de l'endpoint si présent (car on utilise le routeur)
        endpoint = endpoint.replace(/\.php$/, '');

        if (USE_DIRECT_ROUTER) {
            // LOCAL: /moncoachscolaire/public/index.php?page=api/admin/ENDPOINT&param1=val1
            // ATTENTION: ne pas utiliser le mot "page" dans params car conflit avec ?page=
            const filteredParams = {};
            for (const [key, value] of Object.entries(params)) {
                // Renommer "page" en "p" pour éviter le conflit
                const paramKey = key === 'page' ? 'p' : key;
                filteredParams[paramKey] = value;
            }
            const query = new URLSearchParams(filteredParams).toString();
            return `${API_BASE}/${endpoint}${query ? '&' + query : ''}`;
        } else {
            // PROD: /api/admin/ENDPOINT?param1=val1
            const query = new URLSearchParams(params).toString();
            return `${API_BASE}/${endpoint}${query ? '?' + query : ''}`;
        }
    }

    // Log final pour debug
    console.log('🔧 Configuration API:', {
        windowBaseUrl: typeof window.baseUrl !== 'undefined' ? window.baseUrl : 'undefined',
        apiBase: API_BASE,
        locationHref: window.location.href
    });

    // Helper: fetch JSON avec gestion d'erreurs propre (évite fuite HTML)
    function fetchJson(url, options = {}) {
        return fetch(url, options).then(async (response) => {
            if (!response.ok) {
                let message = `Erreur HTTP ${response.status}`;
                try {
                    const text = await response.text();
                    console.error('❌ Réponse non-OK:', text.substring(0, 200));
                    try {
                        const err = JSON.parse(text);
                        if (err && err.error) {
                            if (typeof err.error === 'string') {
                                message = err.error;
                            } else {
                                message = err.error.message || JSON.stringify(err.error);
                            }
                        }
                    } catch (_) {}
                } catch (_) {}
                throw new Error(message);
            }
            try {
                const text = await response.text();
                return JSON.parse(text);
            } catch (e) {
                console.error('❌ Erreur parsing JSON:', e);
                throw new Error('Réponse invalide du serveur (non-JSON)');
            }
        });
    }

    // Exposer les helpers pour les modules globaux en amont du fichier
    window.apiUrl = apiUrl;
    window.API_BASE = API_BASE;
    window.fetchJson = fetchJson;
    const REFRESH_INTERVAL = 30000; // 30 secondes
    let refreshTimer = null;
    let currentSection = 'overview';
    let currentUserPage = 1;
    let currentLogPage = 1;
    let qualityData = { rows: [], levels: [], subjects: [], totals: {} };
    const QUALITY_ALERT = {
        shortPctWarn: 20, // % de réponses courtes déclenchant un avertissement
        shortPctInfo: 5,  // seuil d’info
        emptyWarn: 1      // nombre de réponses vides déclenchant alerte
    };

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        initNavigation();
        initUserManagement();
        initLogs();
        initDebug();
        initConfig();
        initParentsManagement();
        initQuality();

        // Vérifier quelle section est active par défaut
        const activeSection = document.querySelector('.admin-section.active');
        if (activeSection) {
            const sectionId = activeSection.id;
            if (sectionId === 'section-users') {
                // Si la section users est active, charger les utilisateurs
                loadUsers();
            } else if (sectionId === 'section-overview') {
                loadOverview();
            } else if (sectionId === 'section-logs') {
                loadLogs();
            } else if (sectionId === 'section-parents') {
                loadParentsData();
            }
        } else {
            // Par défaut, charger la vue d'ensemble
            loadOverview();
        }

        // Démarrer le rafraîchissement automatique
        startAutoRefresh();
    });

    /**
     * Navigation entre les sections
     */
    function initNavigation() {
        const navItems = document.querySelectorAll('.admin-nav-item');
        const sections = document.querySelectorAll('.admin-section');

        navItems.forEach(item => {
            item.addEventListener('click', function() {
                const targetSection = this.dataset.section;

                // Mettre à jour la navigation
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');

                // Afficher la section
                sections.forEach(section => {
                    section.classList.remove('active');
                });

                const targetElement = document.getElementById(`section-${targetSection}`);
                if (targetElement) {
                    targetElement.classList.add('active');
                    currentSection = targetSection;

                    // Charger les données de la section
                    switch(targetSection) {
                        case 'overview':
                            loadOverview();
                            break;
                        case 'users':
                            loadUsers();
                            break;
                        case 'logs':
                            loadLogs();
                            break;
                        case 'parents':
                            loadParentsData();
                            break;
                        case 'quality':
                            loadQualityLive();
                            // Charger l'échantillon par niveau dans l'onglet Qualité
                            loadExercisesQuality();
                            break;
                        case 'debug':
                            // Ne pas charger automatiquement
                            break;
                        case 'system':
                            // Ne pas charger automatiquement
                            break;
                    }
                }
            });
        });
    }

    /**
     * Charger la vue d'ensemble
     */
    function loadOverview() {
        const statsUrl = apiUrl('stats');
        console.log('🔄 Chargement des statistiques...', statsUrl);

        fetchJson(statsUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(data => {
                console.log('✅ Données stats reçues:', data);
                if (data.success) {
                    updateStats(data.data);
                    updateSystemStatus(data.data);
                    loadRecentActivity();
                } else {
                    console.error('❌ Erreur API stats:', data.error);
                    showMessage(data.error || 'Erreur lors du chargement des statistiques', 'error');
                }
            })
            .catch(error => {
                console.error('❌ Erreur chargement stats:', error);
                let userMessage = 'Erreur lors du chargement des statistiques';
                if (String(error.message).includes('404')) {
                    userMessage = 'Statistiques indisponibles (404)';
                } else if (String(error.message).includes('500')) {
                    userMessage = 'Erreur serveur (500)';
                }
                showMessage(userMessage, 'error');
            });
    }

    /**
     * Charger la qualité des exercices (échantillon par niveau)
     */
    function loadExercisesQuality() {
        const url = apiUrl('exercise_quality.php', { type: 'placeholders' });
        console.log('🔎 Chargement qualité exercices...', url);

        fetchJson(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(data => {
                if (!data.success) throw new Error(data.error || 'Erreur API');
                const tbody = document.getElementById('exercises-quality-body');
                if (!tbody) return;
                tbody.innerHTML = '';

                data.data.forEach(row => {
                    const tr = document.createElement('tr');
                    if (!row.hasExercise) {
                        tr.innerHTML = `
                            <td>${escapeHtml(row.level)}</td>
                            <td colspan="4" class="text-muted">Aucun exercice trouvé</td>
                        `;
                    } else {
                        const ex = row.exercise;
                        const placeholderBadge = (ex.placeholdersInContent || ex.placeholdersInAnswer)
                            ? '<span class="badge badge-warn">⚠️ détectés</span>'
                            : '<span class="badge badge-ok">✅ aucun</span>';
                        const coursesBadge = ex.linkedCoursesCount > 0
                            ? `<span class="badge badge-ok">${ex.linkedCoursesCount}</span>`
                            : '<span class="badge badge-muted">0</span>';

                        // Calcul du statut global
                        let statusBadge = '';
                        if (ex.placeholdersInContent || ex.placeholdersInAnswer) {
                            if (ex.linkedCoursesCount === 0) {
                                statusBadge = '<span class="badge badge-danger">🔴 À corriger</span>';
                            } else {
                                statusBadge = '<span class="badge badge-warn">🟡 Révision requise</span>';
                            }
                        } else {
                            if (ex.linkedCoursesCount === 0) {
                                statusBadge = '<span class="badge badge-warn">🟡 Lier à un cours</span>';
                            } else {
                                statusBadge = '<span class="badge badge-ok">🟢 OK</span>';
                            }
                        }

                        tr.innerHTML = `
                            <td>${escapeHtml(row.level)}</td>
                            <td>${escapeHtml(ex.subject)}</td>
                            <td title="ID #${ex.id}">${escapeHtml(ex.title)}</td>
                            <td>${placeholderBadge}</td>
                            <td>${coursesBadge}</td>
                            <td>${statusBadge}</td>
                        `;
                    }
                    tbody.appendChild(tr);
                });
                // Ajouter le bouton de nettoyage
                const panel = document.getElementById('table-exercises-quality');
                if (panel && !document.getElementById('btn-clean-placeholders')) {
                    const btn = document.createElement('button');
                    btn.id = 'btn-clean-placeholders';
                    btn.className = 'btn-admin-secondary';
                    btn.style.marginTop = '10px';
                    btn.innerHTML = '🧽 Nettoyer placeholders';
                    panel.parentElement.appendChild(btn);
                    btn.addEventListener('click', () => {
                        btn.disabled = true;
                        btn.textContent = '🧽 Nettoyage en cours...';
                        fetchJson(apiUrl('sanitize_exercises_placeholders'), { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-CSRF-Token': window.csrfToken || '' } })
                            .then(d => {
                                if (!d.success) throw new Error(d.error || 'Erreur API');
                                showMessage(`Nettoyage terminé: ${d.data.updated} exercices mis à jour`, 'success');
                                loadExercisesQuality();
                            })
                            .catch(err => {
                                console.error('❌ Erreur nettoyage:', err);
                                showMessage('Erreur nettoyage: ' + err.message, 'error');
                            })
                            .finally(() => {
                                btn.disabled = false;
                                btn.textContent = '🧽 Nettoyer placeholders';
                            });
                    });

                    // Boutons export
                    const btnCsv = document.getElementById('btn-export-exq-csv');
                    const btnJson = document.getElementById('btn-export-exq-json');

                    if (btnCsv && !btnCsv.dataset.bound) {
                        btnCsv.dataset.bound = '1';
                        btnCsv.addEventListener('click', () => {
                            try {
                                const rows = [];
                                const tableRows = document.querySelectorAll('#exercises-quality-body tr');
                                rows.push(['Niveau','Matière','Exercice','Placeholders','Cours liés','Statut']);
                                tableRows.forEach(tr => {
                                    const cols = tr.querySelectorAll('td');
                                    if (cols.length === 6) {
                                        rows.push(Array.from(cols).map(td => td.textContent.trim()));
                                    }
                                });
                                const csv = rows.map(r => r.map(cell => '"' + cell.replace(/"/g,'""') + '"').join(',')).join('\n');
                                const blob = new Blob([csv], {type: 'text/csv;charset=utf-8;'});
                                const url = URL.createObjectURL(blob);
                                const a = document.createElement('a');
                                a.href = url;
                                a.download = 'exercises_quality.csv';
                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                URL.revokeObjectURL(url);
                            } catch (e) {
                                console.error('Export CSV error', e);
                                showMessage('Erreur export CSV', 'error');
                            }
                        });
                    }

                    if (btnJson && !btnJson.dataset.bound) {
                        btnJson.dataset.bound = '1';
                        btnJson.addEventListener('click', () => {
                            try {
                                fetchJson(apiUrl('exercise_quality.php', { type: 'placeholders' }), {method:'GET', credentials:'same-origin', headers: { 'Accept': 'application/json' }})
                                    .then(d => {
                                        const blob = new Blob([JSON.stringify(d.data, null, 2)], {type:'application/json'});
                                        const urlDl = URL.createObjectURL(blob);
                                        const a = document.createElement('a');
                                        a.href = urlDl;
                                        a.download = 'exercises_quality.json';
                                        document.body.appendChild(a);
                                        a.click();
                                        document.body.removeChild(a);
                                        URL.revokeObjectURL(urlDl);
                                    });
                            } catch (e) {
                                console.error('Export JSON error', e);
                                showMessage('Erreur export JSON', 'error');
                            }
                        });
                    }
                }
            })
            .catch(err => {
                console.error('❌ Erreur exercices quality:', err);
                const tbody = document.getElementById('exercises-quality-body');
                if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-error">Erreur chargement</td></tr>';
            });
    }

    /**
     * Section Qualité (live): initialisation
     */
    function initQuality() {
        const btn = document.getElementById('quality-refresh');
        if (btn) {
            btn.addEventListener('click', loadQualityLive);
        }

        const exportCsv = document.getElementById('quality-export-csv');
        const exportJson = document.getElementById('quality-export-json');
        if (exportCsv) exportCsv.addEventListener('click', () => exportQuality('csv'));
        if (exportJson) exportJson.addEventListener('click', () => exportQuality('json'));

        const levelSel = document.getElementById('quality-filter-level');
        const subjSel = document.getElementById('quality-filter-subject');
        if (levelSel) levelSel.addEventListener('change', renderQualityTable);
        if (subjSel) subjSel.addEventListener('change', renderQualityTable);
    }

    /**
     * Charger les stats live de qualité
     */
    function loadQualityLive() {
        const url = apiUrl('exercise_quality.php', { type: 'by_level_subject' });
        console.log('🔄 Chargement qualité live...', url);

        fetchJson(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        }).then(data => {
                if (!data.success) throw new Error(data.error || 'Erreur API');
                qualityData = {
                    rows: data.byLevelSubject || [],
                    levels: data.levels || [],
                    subjects: data.subjects || [],
                    totals: data.totals || {}
                };
                fillQualityFilters();
                renderQualityTable();
            }).catch(err => {
                console.error('❌ Erreur qualité live:', err);
                showMessage('Erreur chargement qualité: ' + err.message, 'error');
                const tbody = document.getElementById('quality-table-body');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-muted">${escapeHtml(err.message)}</td></tr>`;
                }
            });
    }

    function fillQualityFilters() {
        const levelSel = document.getElementById('quality-filter-level');
        const subjSel = document.getElementById('quality-filter-subject');
        if (levelSel && !levelSel.dataset.filled) {
            qualityData.levels.forEach(lvl => {
                const opt = document.createElement('option');
                opt.value = lvl;
                opt.textContent = lvl;
                levelSel.appendChild(opt);
            });
            levelSel.dataset.filled = '1';
        }
        if (subjSel && !subjSel.dataset.filled) {
            qualityData.subjects.forEach(subj => {
                const opt = document.createElement('option');
                opt.value = subj;
                opt.textContent = subj;
                subjSel.appendChild(opt);
            });
            subjSel.dataset.filled = '1';
        }
    }

    function renderQualityTable() {
        const tbody = document.getElementById('quality-table-body');
        if (!tbody) return;

        const levelSel = document.getElementById('quality-filter-level');
        const subjSel = document.getElementById('quality-filter-subject');
        const level = levelSel ? levelSel.value : '';
        const subject = subjSel ? subjSel.value : '';

        const rows = (qualityData.rows || []).filter(r => {
            return (!level || r.level === level) && (!subject || r.subject === subject);
        });

        let totalActive = 0;
        let totalShort = 0;
        let totalEmpty = 0;

        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-muted">Aucun exercice trouvé pour ce filtre</td></tr>';
        } else {
            const fragments = [];
            rows.forEach(r => {
                totalActive += r.active;
                totalShort += r.short_answers;
                totalEmpty += r.empty_answers;
                // Générer le contenu de la colonne Cours liés
                let coursLies = '';
                if (typeof r.cours_lies !== 'undefined') {
                    if (r.cours_lies === true || r.cours_lies === 'OK') {
                        coursLies = '<span class="badge badge-ok">✅ OK</span>';
                    } else if (typeof r.cours_lies === 'string' && r.cours_lies.length > 0) {
                        coursLies = `<span class="badge badge-info">${escapeHtml(r.cours_lies)}</span>`;
                    } else {
                        coursLies = '<span class="badge badge-error">❌</span>';
                    }
                } else {
                    coursLies = '<span class="badge badge-error">❓</span>';
                }
                const alertBadge = buildQualityAlertBadge(r);
                fragments.push(`
                    <tr>
                        <td>${escapeHtml(r.level)}</td>
                        <td>${escapeHtml(r.subject)}</td>
                        <td>${r.total}</td>
                        <td>${r.active}</td>
                        <td>${r.short_answers}</td>
                        <td>${r.empty_answers}</td>
                        <td>${r.min_answer || 0} / ${r.avg_answer || 0} / ${r.max_answer || 0}</td>
                        <td>${coursLies}</td>
                        <td>${alertBadge}</td>
                    </tr>
                `);
            });
            tbody.innerHTML = fragments.join('');
        }

        const totalActiveEl = document.getElementById('quality-total-active');
        const totalShortEl = document.getElementById('quality-total-short');
        const totalEmptyEl = document.getElementById('quality-total-empty');

        if (totalActiveEl) totalActiveEl.textContent = totalActive;
        if (totalShortEl) totalShortEl.textContent = totalShort;
        if (totalEmptyEl) totalEmptyEl.textContent = totalEmpty;
    }

    function buildQualityAlertBadge(row) {
        const total = row.total || 0;
        const shortPct = total > 0 ? Math.round((row.short_answers / total) * 100) : 0;
        const hasEmpty = row.empty_answers > 0;

        if (hasEmpty) {
            return '<span class="badge badge-error">❌ Vides</span>';
        }
        if (shortPct >= QUALITY_ALERT.shortPctWarn) {
            return `<span class="badge badge-warn">⚠️ Courtes ${shortPct}%</span>`;
        }
        if (shortPct >= QUALITY_ALERT.shortPctInfo) {
            return `<span class="badge badge-info">ℹ️ Courtes ${shortPct}%</span>`;
        }
        return '<span class="badge badge-ok">✅ OK</span>';
    }

    function exportQuality(format = 'csv') {
        const levelSel = document.getElementById('quality-filter-level');
        const subjSel = document.getElementById('quality-filter-subject');
        const level = levelSel ? levelSel.value : '';
        const subject = subjSel ? subjSel.value : '';

        const rows = (qualityData.rows || []).filter(r => {
            return (!level || r.level === level) && (!subject || r.subject === subject);
        });

        if (format === 'json') {
            const blob = new Blob([JSON.stringify(rows, null, 2)], { type: 'application/json' });
            downloadBlob(blob, 'exercises_quality.json');
            return;
        }

        // CSV
        const header = ['Level','Subject','Total','Active','ShortAnswers','EmptyAnswers','MinAnswer','AvgAnswer','MaxAnswer'];
        const lines = [header.join(',')];
        rows.forEach(r => {
            lines.push([
                r.level,
                r.subject,
                r.total,
                r.active,
                r.short_answers,
                r.empty_answers,
                r.min_answer || 0,
                r.avg_answer || 0,
                r.max_answer || 0
            ].map(v => (''+v).replace(/,/g,' ')).join(','));
        });
        const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
        downloadBlob(blob, 'exercises_quality.csv');
    }

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    /**
     * Mettre à jour les statistiques
     */
    function updateStats(stats) {
        // Utilisateurs
        if (stats.users) {
            setStatValue('stat-users-total', stats.users.total || 0);
            setStatValue('stat-users-students', stats.users.students || 0);
            setStatValue('stat-users-admins', stats.users.admins || 0);
            setStatValue('stat-new-users-7d', stats.users.newLast7Days || 0);
            setStatValue('stat-new-users-30d', stats.users.newLast30Days || 0);
        }

        // Progression
        if (stats.progress) {
            setStatValue('stat-total-xp', formatNumber(stats.progress.totalXP || 0));
            setStatValue('stat-avg-xp', formatNumber(stats.progress.averageXP || 0));
        }

        // Exercices
        if (stats.exercises) {
            setStatValue('stat-exercises-total', stats.exercises.totalResponses || 0);
            setStatValue('stat-avg-score', stats.exercises.averageScore ? stats.exercises.averageScore + '%' : '-');
        }

        // Logs
        if (stats.logs) {
            setStatValue('stat-logs-24h', stats.logs.last24Hours || 0);
            setStatValue('stat-logs-total', stats.logs.adminActions || 0);
        }

        // Système
        if (stats.system && stats.system.memoryUsage) {
            setStatValue('stat-memory', stats.system.memoryUsage.current);
            setStatValue('stat-memory-peak', stats.system.memoryUsage.peak);
        }
    }

    /**
     * Mettre à jour l'état du système
     */
    function updateSystemStatus(stats) {
        const container = document.getElementById('system-status');
        if (!container) return;

        const statusItems = [
            {
                label: 'Base de données',
                value: stats.system?.database?.connected ? 'Connectée' : 'Déconnectée',
                status: stats.system?.database?.connected ? 'success' : 'error'
            },
            {
                label: 'PHP Version',
                value: stats.system?.phpVersion || 'N/A',
                status: 'success'
            },
            {
                label: 'Mémoire',
                value: stats.system?.memoryUsage?.current || 'N/A',
                status: 'success'
            },
            {
                label: 'Heure serveur',
                value: stats.system?.serverTime || 'N/A',
                status: 'success'
            }
        ];

        container.innerHTML = statusItems.map(item => `
            <div class="status-item">
                <span class="status-label">${item.label}</span>
                <span class="status-value ${item.status}">${item.value}</span>
            </div>
        `).join('');
    }

    /**
     * Charger l'activité récente
     */
    function loadRecentActivity() {
        fetchJson(apiUrl('logs', { type: 'admin', limit: 10 }), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(data => {
                if (data.success && data.data) {
                    displayRecentActivity(data.data);
                }
            })
            .catch(error => {
                console.error('Erreur chargement activité:', error);
            });
    }

    /**
     * Afficher l'activité récente
     */
    function displayRecentActivity(activities) {
        const container = document.getElementById('recent-activity');
        if (!container) return;

        if (activities.length === 0) {
            container.innerHTML = '<div class="activity-loading">Aucune activité récente</div>';
            return;
        }

        container.innerHTML = activities.slice(0, 10).map(activity => {
            const icon = getActivityIcon(activity.type || activity.action);
            const time = formatTime(activity.timestamp || activity.CreatedAt);
            const message = activity.details || activity.message || activity.Action || 'Action';

            return `
                <div class="activity-item">
                    <div class="activity-icon">${icon}</div>
                    <div class="activity-content">
                        <div class="activity-title">${escapeHtml(message)}</div>
                        <div class="activity-time">${time}</div>
                    </div>
                </div>
            `;
        }).join('');
    }

    /**
     * Gestion des utilisateurs
     */
    function initUserManagement() {
        // Recherche
        const searchInput = document.getElementById('user-search');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentUserPage = 1;
                    loadUsers();
                }, 500);
            });
        }

        // Filtre rôle
        const roleFilter = document.getElementById('user-role-filter');
        if (roleFilter) {
            roleFilter.addEventListener('change', function() {
                currentUserPage = 1;
                loadUsers();
            });
        }

        // Gérer l'affichage du champ niveau selon le rôle sélectionné dans le formulaire
        const userRoleSelect = document.getElementById('user-role');
        const userLevelGroup = document.getElementById('user-level-group');
        if (userRoleSelect && userLevelGroup) {
            const toggleLevelField = () => {
                const role = userRoleSelect.value;
                if (role === 'parent') {
                    userLevelGroup.style.display = 'none';
                    document.getElementById('user-level').value = '';
                } else {
                    userLevelGroup.style.display = 'block';
                    if (!document.getElementById('user-level').value) {
                        document.getElementById('user-level').value = '6ème';
                    }
                }
            };

            userRoleSelect.addEventListener('change', toggleLevelField);
            // Appliquer au chargement initial si on édite un utilisateur
            toggleLevelField();
        }

        // Formulaire utilisateur
        // JS modal utilisateur supprimé
    }

    /**
     * Charger les utilisateurs
     */
    function loadUsers() {
        const search = document.getElementById('user-search')?.value || '';
        const role = document.getElementById('user-role-filter')?.value || '';

        const params = new URLSearchParams({
            page: currentUserPage,
            limit: 20
        });

        if (search) params.append('search', search);
        if (role) params.append('role', role);

        const finalUrl = apiUrl('users', Object.fromEntries(params));
        console.log('🔄 Chargement des utilisateurs...', finalUrl);
        console.log('🔧 API_BASE:', API_BASE);
        console.log('🔧 window.baseUrl:', typeof window.baseUrl !== 'undefined' ? window.baseUrl : 'undefined');

        fetchJson(finalUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(data => {
                console.log('✅ Données reçues:', data);
                if (data.success) {
                    console.log('👥 Utilisateurs:', data.data?.length || 0, 'utilisateurs');
                    displayUsers(data.data || []);
                    displayPagination('users-pagination', data.pagination, (page) => {
                        currentUserPage = page;
                        loadUsers();
                    });
                } else {
                    console.error('❌ Erreur API:', data.error);
                    showMessage(data.error || 'Erreur lors du chargement des utilisateurs', 'error');
                    const tbody = document.getElementById('users-table-body');
                    if (tbody) {
                        tbody.innerHTML = '<tr><td colspan="8" class="loading-cell">Erreur: ' + escapeHtml(data.error || 'Erreur inconnue') + '</td></tr>';
                    }
                }
            })
            .catch(error => {
                console.error('❌ Erreur chargement utilisateurs:', error);
                showMessage('Erreur lors du chargement des utilisateurs: ' + error.message, 'error');
                const tbody = document.getElementById('users-table-body');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="8" class="loading-cell">Erreur de connexion: ' + escapeHtml(error.message) + '</td></tr>';
                }
            });
    }

    /**
     * Afficher les utilisateurs dans le tableau
     */
    function displayUsers(users) {
        const tbody = document.getElementById('users-table-body');
        if (!tbody) return;

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="loading-cell">Aucun utilisateur trouvé</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(user => {
            let roleBadge;
            if (user.Role === 'admin') {
                roleBadge = '<span class="badge badge-danger">Admin</span>';
            } else if (user.Role === 'parent') {
                roleBadge = '<span class="badge badge-info">Parent</span>';
            } else {
                roleBadge = '<span class="badge badge-success">Élève</span>';
            }

            const xp = user.TotalXP ? formatNumber(user.TotalXP) : '0';
            const createdAt = formatDate(user.CreatedAt);

            return `
                <tr>
                    <td>${user.Id}</td>
                    <td><strong>${escapeHtml(user.Username)}</strong></td>
                    <td>${escapeHtml(user.Email)}</td>
                    <td>${roleBadge}</td>
                    <td>${escapeHtml(user.UserLevel || '-')}</td>
                    <td>${xp} XP</td>
                    <td>${createdAt}</td>
                    <td>
                        <button class="btn-edit" onclick="editUser(${user.Id})">✏️ Modifier</button>
                        ${user.Id !== getCurrentUserId() ?
                            `<button class="btn-danger" onclick="deleteUser(${user.Id}, '${escapeHtml(user.Username)}')">🗑️ Supprimer</button>`
                            : ''}
                    </td>
                </tr>
            `;
        }).join('');
    }

    /**
     * Afficher la pagination
     */
    function displayPagination(containerId, pagination, onPageChange) {
        const container = document.getElementById(containerId);
        if (!container || !pagination) return;

        const { page, pages } = pagination;
        if (pages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';

        // Bouton précédent
        html += `<button ${page === 1 ? 'disabled' : ''} onclick="onPageChange(${page - 1})">‹ Précédent</button>`;

        // Pages
        for (let i = 1; i <= pages; i++) {
            if (i === 1 || i === pages || (i >= page - 2 && i <= page + 2)) {
                html += `<button class="${i === page ? 'active' : ''}" onclick="onPageChange(${i})">${i}</button>`;
            } else if (i === page - 3 || i === page + 3) {
                html += `<span>...</span>`;
            }
        }

        // Bouton suivant
        html += `<button ${page === pages ? 'disabled' : ''} onclick="onPageChange(${page + 1})">Suivant ›</button>`;

        container.innerHTML = html;

        // Attacher les événements
        container.querySelectorAll('button').forEach(btn => {
            if (btn.onclick) {
                const onclick = btn.onclick.toString();
                const match = onclick.match(/onPageChange\((\d+)\)/);
                if (match) {
                    btn.onclick = () => onPageChange(parseInt(match[1]));
                }
            }
        });
    }

    /**
     * Afficher le modal de création/édition utilisateur
     */
    // Fonction showCreateUserModal supprimée

    /**
     * Éditer un utilisateur
     */
    // Fonction editUser supprimée

    /**
     * Supprimer un utilisateur
     */
    window.deleteUser = function(userId, username) {
        if (!confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${username}" ?\n\nCette action est irréversible.`)) {
            return;
        }

        fetchJson(`${API_BASE}/users.php?id=${userId}`, {
            method: 'DELETE'
        }).then(data => {
                if (data.success) {
                    showMessage('Utilisateur supprimé avec succès', 'success');
                    loadUsers();
                } else {
                    showMessage(data.error || 'Erreur lors de la suppression', 'error');
                }
            }).catch(error => {
                console.error('Erreur suppression utilisateur:', error);
                showMessage('Erreur lors de la suppression', 'error');
            });
    };

    /**
     * Fermer le modal utilisateur
     */
    // Fonction closeUserModal supprimée

    /**
     * Gérer la soumission du formulaire utilisateur
     */
    // Fonction handleUserSubmit supprimée

    /**
     * Gestion des logs
     */
    function initLogs() {
        // Filtre type de log
        const logTypeFilter = document.getElementById('log-type-filter');
        if (logTypeFilter) {
            logTypeFilter.addEventListener('change', function() {
                currentLogPage = 1;
                loadLogs();
            });
        }
    }

    /**
     * Charger les logs
     */
    function loadLogs() {
        const type = document.getElementById('log-type-filter')?.value || 'all';

        const params = new URLSearchParams({
            type: type,
            page: currentLogPage,
            limit: 50
        });

        const logsUrl = apiUrl('logs', Object.fromEntries(params));
        console.log('🔄 Chargement des logs...', logsUrl);

        fetchJson(logsUrl, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(data => {
                console.log('✅ Données logs reçues:', data);
                if (data.success) {
                    displayLogs(data.data || []);
                } else {
                    console.error('❌ Erreur API logs:', data.error);
                    showMessage(data.error || 'Erreur lors du chargement des logs', 'error');
                }
            })
            .catch(error => {
                console.error('❌ Erreur chargement logs:', error);
                showMessage('Erreur lors du chargement des logs: ' + error.message, 'error');
            });
    }

    /**
     * Afficher les logs
     */
    function displayLogs(logs) {
        const container = document.getElementById('logs-container');
        if (!container) return;

        if (logs.length === 0) {
            container.innerHTML = '<div class="loading-message">Aucun log disponible</div>';
            return;
        }

        container.innerHTML = logs.map(log => {
            const type = log.type || 'system';
            const time = formatTime(log.timestamp || log.CreatedAt);
            const message = log.details || log.message || log.Action || 'Log entry';
            const admin = log.admin || log.AdminUsername || '';

            return `
                <div class="log-entry ${type}">
                    <div class="log-header">
                        <span><strong>${escapeHtml(message)}</strong></span>
                        <span class="log-time">${time}</span>
                    </div>
                    ${admin ? `<div style="font-size: 12px; color: #64748b; margin-top: 4px;">Par: ${escapeHtml(admin)}</div>` : ''}
                </div>
            `;
        }).join('');
    }

    /**
     * Gestion du debug
     */
    function initDebug() {
        // Les boutons sont gérés par les fonctions onclick inline
    }

    /**
     * Charger les informations de debug (toggle: affiche/cache)
     */
    window.loadDebugInfo = function(type) {
        const outputId = `debug-${type}`;
        const output = document.getElementById(outputId);
        if (!output) {
            console.error('❌ Élément debug non trouvé:', outputId);
            return;
        }

        // Vérifier si l'élément est actuellement visible (toggle)
        const isVisible = output.classList.contains('visible');

        // Si l'élément est visible, le cacher
        if (isVisible) {
            output.classList.remove('visible');
            output.textContent = ''; // Vider le contenu pour le prochain affichage
            console.log('🔒 Élément debug caché:', outputId);
            return;
        }

        // Sinon, afficher et charger les données
        output.classList.add('visible');
        output.textContent = 'Chargement...';

        // Correction : router l'appel SMTP/SMTP_LOG vers debug_email.php
        let endpoint = 'debug';
        if (type === 'smtp' || type === 'smtp_log') {
            endpoint = 'debug_email';
        }
        const url = apiUrl(endpoint, { action: type });
        console.log('🔄 Chargement debug info:', url);

        fetchJson(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(data => {
                console.log('✅ Données debug reçues:', data);
                // S'assurer que l'élément reste visible après le chargement
                output.classList.add('visible');

                if (data.success) {
                    const jsonData = JSON.stringify(data.data, null, 2);
                    output.textContent = jsonData;
                } else {
                    output.textContent = 'Erreur: ' + (data.error || 'Inconnue');
                }
            })
            .catch(error => {
                console.error('❌ Erreur chargement debug:', error);
                // S'assurer que l'élément reste visible même en cas d'erreur
                output.classList.add('visible');
                output.textContent = 'Erreur: ' + error.message;
            });
    };

    /**
     * Nettoyer le cache
     */
    window.clearCache = function() {
        const resultDiv = document.getElementById('debug-action-result');
        if (!resultDiv) return;

        // Vérifier si la zone est déjà visible (toggle)
        const isVisible = resultDiv.classList.contains('visible');

        // Si visible et contient déjà des données, vider et cacher
        if (isVisible && resultDiv.textContent.trim() !== '') {
            resultDiv.classList.remove('visible');
            resultDiv.textContent = '';
            return;
        }

        // Afficher la zone et charger
        resultDiv.classList.add('visible');
        resultDiv.textContent = 'Nettoyage en cours...';

        fetchJson(apiUrl('debug', { action: 'cache' }), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `<div class="message message-success">Cache nettoyé: ${data.cleared.join(', ')}</div>`;
                    showMessage('Cache nettoyé avec succès', 'success');
                } else {
                    resultDiv.innerHTML = `<div class="message message-error">Erreur: ${data.error || 'Inconnue'}</div>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<div class="message message-error">Erreur: ${error.message}</div>`;
            });
    };

    /**
     * Gestion de la configuration
     */
    function initConfig() {
        // Initialiser le toggle de maintenance
        const maintenanceToggle = document.getElementById('maintenance-mode-toggle');
        if (maintenanceToggle) {
            // Charger l'état actuel
            loadMaintenanceStatus();

            // Gérer le changement d'état
            maintenanceToggle.addEventListener('change', function() {
                toggleMaintenance(this.checked);
            });
        }
    }

    /**
     * Charge l'état actuel de la maintenance
     */
    function loadMaintenanceStatus() {
        console.log('🔄 Chargement état maintenance...');
        fetchJson(apiUrl('maintenance'), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
        .then(data => {
            const toggle = document.getElementById('maintenance-mode-toggle');
            if (!toggle) {
                console.warn('⚠️ Toggle maintenance non trouvé dans le DOM');
                return;
            }

            if (data.success && data.maintenance !== undefined) {
                const isEnabled = data.maintenance.enabled === true;
                toggle.checked = isEnabled;
                toggle.disabled = false;
                console.log('✅ État maintenance chargé:', isEnabled ? 'ACTIVÉ' : 'DÉSACTIVÉ');
            } else {
                console.error('❌ Réponse invalide:', data);
                showMessage('⚠️ Impossible de charger l\'état de la maintenance', 'warning');
                toggle.disabled = false;
            }
        })
        .catch(error => {
            console.error('❌ Erreur chargement état maintenance:', error);
            const toggle = document.getElementById('maintenance-mode-toggle');
            if (toggle) {
                toggle.disabled = false;
                showMessage('❌ Erreur lors du chargement de l\'état de maintenance: ' + error.message, 'error');
            }
        });
    }

    /**
     * Active ou désactive le mode maintenance
     */
    function toggleMaintenance(enabled) {
        const toggle = document.getElementById('maintenance-mode-toggle');
        if (!toggle) {
            console.error('❌ Toggle maintenance non trouvé');
            return;
        }

        toggle.disabled = true; // Désactiver pendant la requête
        const previousState = toggle.checked;

        console.log('🔄 Changement maintenance:', enabled ? 'ACTIVATION' : 'DÉSACTIVATION');

        fetchJson(apiUrl('maintenance'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': window.csrfToken || ''
            },
            body: JSON.stringify({
                enabled: enabled,
                message: 'L\'application est actuellement en maintenance. Nous serons de retour bientôt !',
                csrf_token: window.csrfToken || ''
            })
        })
        .then(data => {
            if (data.success) {
                const message = enabled
                    ? '✅ Mode maintenance activé avec succès'
                    : '✅ Mode maintenance désactivé avec succès';
                showMessage(message, 'success');
                console.log('✅ Maintenance:', enabled ? 'ACTIVÉE' : 'DÉSACTIVÉE');

                // Recharger l'état pour s'assurer que c'est à jour
                setTimeout(() => {
                    loadMaintenanceStatus();
                }, 500);
            } else {
                throw new Error(data.error || 'Erreur inconnue');
            }
        })
        .catch(error => {
            console.error('❌ Erreur toggle maintenance:', error);
            showMessage('❌ Erreur lors de la modification du mode maintenance: ' + error.message, 'error');
            // Restaurer l'état précédent
            toggle.checked = previousState;
        })
        .finally(() => {
            toggle.disabled = false;
        });
    }

    /**
     * Rafraîchir les statistiques
     */
    window.refreshStats = function() {
        loadOverview();
        showMessage('Statistiques actualisées', 'success');
    };

    /**
     * Rafraîchir les logs
     */
    window.refreshLogs = function() {
        loadLogs();
        showMessage('Logs actualisés', 'success');
    };

    /**
     * Démarrer le rafraîchissement automatique
     */
    function startAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
        }

        refreshTimer = setInterval(() => {
            if (currentSection === 'overview') {
                loadOverview();
            } else if (currentSection === 'logs') {
                loadLogs();
            }
        }, REFRESH_INTERVAL);
    }

    /**
     * Arrêter le rafraîchissement automatique
     */
    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
    }

    /**
     * Utilitaires
     */
    function setStatValue(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(date);
    }

    function formatTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;

        if (diff < 60000) return 'Il y a quelques secondes';
        if (diff < 3600000) return `Il y a ${Math.floor(diff / 60000)} minutes`;
        if (diff < 86400000) return `Il y a ${Math.floor(diff / 3600000)} heures`;

        return new Intl.DateTimeFormat('fr-FR', {
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }

    function getActivityIcon(type) {
        const icons = {
            'admin': '⚙️',
            'user_create': '➕',
            'user_update': '✏️',
            'user_delete': '🗑️',
            'error': '❌',
            'system': '💻'
        };
        return icons[type] || '📝';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getCurrentUserId() {
        // Récupérer depuis une variable globale ou un attribut data
        const body = document.body;
        if (!body || !body.dataset) return 0;
        return parseInt(body.dataset.userId || '0');
    }

    function showMessage(message, type = 'success') {
        // Créer un élément de message temporaire
        const messageDiv = document.createElement('div');
        messageDiv.className = `message message-${type}`;
        messageDiv.textContent = message;
        messageDiv.style.position = 'fixed';
        messageDiv.style.top = '20px';
        messageDiv.style.right = '20px';
        messageDiv.style.zIndex = '10000';
        messageDiv.style.minWidth = '300px';
        messageDiv.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';

        document.body.appendChild(messageDiv);

        setTimeout(() => {
            messageDiv.style.opacity = '0';
            messageDiv.style.transition = 'opacity 0.3s';
            setTimeout(() => messageDiv.remove(), 300);
        }, 3000);
    }

    // Exposer les helpers pour les scripts globaux et actions inline
    window.escapeHtml = escapeHtml;
    window.showMessage = showMessage;

    /**
     * Gestion Parents-Élèves
     */
    function initParentsManagement() {
        // Gestion des onglets
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanels = document.querySelectorAll('.tab-panel');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.dataset.tab;

                // Mettre à jour les boutons
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                // Afficher le panneau
                tabPanels.forEach(panel => panel.classList.remove('active'));
                const targetPanel = document.getElementById(`tab-${targetTab}`);
                if (targetPanel) {
                    targetPanel.classList.add('active');

                    // Charger les données selon l'onglet
                    if (targetTab === 'parents-list') {
                        loadParentsList();
                    } else if (targetTab === 'students-list') {
                        loadStudentsList();
                    } else if (targetTab === 'attach-student') {
                        loadAttachFormData();
                    }
                }
            });
        });

        // Formulaire de rattachement
        const attachForm = document.getElementById('attach-student-form');
        if (attachForm) {
            attachForm.addEventListener('submit', handleAttachStudent);
        }

        // Filtre élèves
        const studentFilter = document.getElementById('student-filter-parent');
        if (studentFilter) {
            studentFilter.addEventListener('change', function() {
                loadStudentsList(this.value);
            });
        }
    }

    function loadParentsData() {
        loadParentsStats();
        loadParentsList();
    }

    function loadParentsStats() {
        console.log('🔄 Chargement stats parents...');

        fetchJson(apiUrl('parents', { type: 'parents' }), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(data => {
                console.log('✅ Stats parents reçues:', data);
                if (data.success) {
                    const parents = data.data || [];
                    setStatValue('stat-parents-total', parents.length);
                } else {
                    console.error('❌ Erreur API parents:', data.error);
                }
            })
            .catch(err => {
                console.error('❌ Erreur stats parents:', err);
                setStatValue('stat-parents-total', 0);
            });

        fetchJson(apiUrl('parents', { type: 'students' }), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(data => {
                console.log('✅ Stats élèves reçues:', data);
                if (data.success) {
                    const students = data.data || [];
                    const attached = students.filter(s => s.ParentId);
                    const unattached = students.filter(s => !s.ParentId);

                    setStatValue('stat-students-total', students.length);
                    setStatValue('stat-attachments-total', attached.length);
                    setStatValue('stat-unattached-total', unattached.length);
                } else {
                    console.error('❌ Erreur API élèves:', data.error);
                }
            })
            .catch(err => {
                console.error('❌ Erreur stats élèves:', err);
                setStatValue('stat-students-total', 0);
                setStatValue('stat-attachments-total', 0);
                setStatValue('stat-unattached-total', 0);
            });
    }

    function loadParentsList() {
        const container = document.getElementById('parents-list');
        if (!container) return;

        container.innerHTML = '<div class="loading-message">Chargement...</div>';

        const apiEndpoint = apiUrl('parents', { type: 'parents' });
        console.log('🔄 Chargement liste parents...', apiEndpoint);

        fetchJson(apiEndpoint, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(data => {
                console.log('✅ Liste parents reçue:', data);
                if (data.success) {
                    renderParentsList(data.data || []);
                } else {
                    console.error('❌ Erreur API parents:', data.error);
                    container.innerHTML = '<div class="error-message">Erreur: ' + escapeHtml(data.error || 'Inconnue') + '</div>';
                }
            })
            .catch(err => {
                console.error('❌ Erreur chargement parents:', err);
                container.innerHTML = '<div class="error-message">Erreur de connexion: ' + escapeHtml(err.message) + '</div>';
            });
    }

    function renderParentsList(parents) {
        const container = document.getElementById('parents-list');
        if (!container) return;

        if (parents.length === 0) {
            container.innerHTML = '<div class="empty-message">Aucun parent enregistré</div>';
            return;
        }

        const html = parents.map(parent => {
            const name = [parent.Prenom, parent.Nom].filter(Boolean).join(' ') || parent.Username;
            const email = parent.Email || '-';
            const phone = parent.Telephone || '-';
            const enfantsCount = parent.enfants_count || 0;

            return `
                <div class="parent-card">
                    <div class="parent-card-header">
                        <div class="parent-card-info">
                            <h3>${escapeHtml(name)}</h3>
                            <p class="parent-email">${escapeHtml(email)}</p>
                        </div>
                        <div class="parent-badge">${enfantsCount} enfant${enfantsCount > 1 ? 's' : ''}</div>
                    </div>
                    <div class="parent-card-details">
                        <div class="detail-item">
                            <span class="detail-label">Téléphone:</span>
                            <span class="detail-value">${escapeHtml(phone)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Inscription:</span>
                            <span class="detail-value">${formatDate(parent.CreatedAt)}</span>
                        </div>
                    </div>
                    <div class="parent-card-actions">
                        <button class="btn-view-children" onclick="viewParentChildren(${parent.Id})">
                            <span>👥</span> Voir les enfants
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
    }

    function loadStudentsList(filter = '') {
        const container = document.getElementById('students-list');
        if (!container) return;

        container.innerHTML = '<div class="loading-message">Chargement...</div>';

        let url = apiUrl('parents', { type: 'students' });
        console.log('🔄 Chargement liste élèves...', url);

        fetchJson(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(data => {
                console.log('✅ Liste élèves reçue:', data);
                if (data.success) {
                    let students = data.data || [];
                    if (filter === 'unattached') {
                        students = students.filter(s => !s.ParentId);
                    }
                    renderStudentsList(students);
                } else {
                    console.error('❌ Erreur API élèves:', data.error);
                    container.innerHTML = '<div class="error-message">Erreur: ' + escapeHtml(data.error || 'Inconnue') + '</div>';
                }
            })
            .catch(err => {
                console.error('❌ Erreur chargement élèves:', err);
                container.innerHTML = '<div class="error-message">Erreur de connexion: ' + escapeHtml(err.message) + '</div>';
            });
    }

    function renderStudentsList(students) {
        const container = document.getElementById('students-list');
        if (!container) return;

        if (students.length === 0) {
            container.innerHTML = '<div class="empty-message">Aucun élève trouvé</div>';
            return;
        }

        const html = students.map(student => {
            const name = [student.Prenom, student.Nom].filter(Boolean).join(' ') || student.Username;
            const email = student.Email || '-';
            const level = student.UserLevel || '-';
            const parentName = student.parent_prenom || student.parent_nom
                ? [student.parent_prenom, student.parent_nom].filter(Boolean).join(' ')
                : student.parent_username || '-';
            const hasParent = !!student.ParentId;

            return `
                <div class="student-card ${hasParent ? 'has-parent' : 'no-parent'}">
                    <div class="student-card-header">
                        <div class="student-card-info">
                            <h3>${escapeHtml(name)}</h3>
                            <p class="student-email">${escapeHtml(email)}</p>
                        </div>
                        <div class="student-badge">
                            ${hasParent ? '🔗 Rattaché' : '🔓 Non rattaché'}
                        </div>
                    </div>
                    <div class="student-card-details">
                        <div class="detail-item">
                            <span class="detail-label">Niveau:</span>
                            <span class="detail-value">${escapeHtml(level)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Parent:</span>
                            <span class="detail-value">${escapeHtml(parentName)}</span>
                        </div>
                    </div>
                    <div class="student-card-actions">
                        ${hasParent ? `
                            <button class="btn-detach" onclick="detachStudent(${student.Id}, '${escapeHtml(name)}')">
                                <span>🔓</span> Détacher
                            </button>
                        ` : `
                            <button class="btn-attach" onclick="showAttachModal(${student.Id}, '${escapeHtml(name)}')">
                                <span>🔗</span> Rattacher
                            </button>
                        `}
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
    }

    function loadAttachFormData() {
        console.log('🔄 Chargement données formulaire rattachement...');

        // Charger les élèves non rattachés
        fetchJson(apiUrl('parents', { type: 'students' }), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(data => {
                console.log('✅ Données élèves reçues:', data);
                if (data.success) {
                    const students = (data.data || []).filter(s => !s.ParentId);
                    const select = document.getElementById('attach-student-select');
                    if (select) {
                        select.innerHTML = '<option value="">-- Choisir un élève --</option>' +
                            students.map(s => {
                                const name = [s.Prenom, s.Nom].filter(Boolean).join(' ') || s.Username;
                                return `<option value="${s.Id}">${escapeHtml(name)} (${escapeHtml(s.UserLevel || '-')})</option>`;
                            }).join('');
                    }
                } else {
                    console.error('❌ Erreur API élèves:', data.error);
                }
            })
            .catch(err => {
                console.error('❌ Erreur chargement élèves:', err);
            });

        // Charger les parents
        fetchJson(apiUrl('parents', { type: 'parents' }), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(data => {
                console.log('✅ Données parents reçues:', data);
                if (data.success) {
                    const parents = data.data || [];
                    const select = document.getElementById('attach-parent-select');
                    if (select) {
                        select.innerHTML = '<option value="">-- Choisir un parent --</option>' +
                            parents.map(p => {
                                const name = [p.Prenom, p.Nom].filter(Boolean).join(' ') || p.Username;
                                return `<option value="${p.Id}">${escapeHtml(name)} (${escapeHtml(p.Email)})</option>`;
                            }).join('');
                    }
                } else {
                    console.error('❌ Erreur API parents:', data.error);
                }
            })
            .catch(err => {
                console.error('❌ Erreur chargement parents:', err);
            });
    }

    function handleAttachStudent(e) {
        e.preventDefault();

        const form = e.target;
        const studentId = form.student_id.value;
        const parentId = form.parent_id.value;

        if (!studentId || !parentId) {
            showMessage('Veuillez sélectionner un élève et un parent', 'error');
            return;
        }

        const apiEndpoint = apiUrl('parents');
        console.log('🔄 Rattachement élève...', apiEndpoint);

        fetchJson(apiEndpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': window.csrfToken || ''
            },
            body: JSON.stringify({ student_id: studentId, parent_id: parentId, csrf_token: window.csrfToken || '' })
        })
        .then(data => {
            console.log('✅ Réponse rattachement:', data);
            if (data.success) {
                showMessage(data.message || 'Élève rattaché avec succès', 'success');
                resetAttachForm();
                loadParentsData();
                // Recharger la liste des élèves
                loadStudentsList();
            } else {
                console.error('❌ Erreur rattachement:', data.error);
                showMessage(data.error || 'Erreur lors du rattachement', 'error');
            }
        })
        .catch(err => {
            console.error('❌ Erreur rattachement:', err);
            showMessage('Erreur de connexion: ' + err.message, 'error');
        });
    }

    function resetAttachForm() {
        const form = document.getElementById('attach-student-form');
        if (form) {
            form.reset();
        }
    }

    // ===== Actions des cartes Système (branchées) =====
    window.loadAdminAudit = function() {
        const url = apiUrl('logs.php', { type: 'admin', limit: 20 });
        fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(data => {
                const total = data?.meta?.total ?? (data?.data ? data.data.length : 0);
                showMessage(`Audit chargé : ${total} entrées`, 'success');
            })
            .catch(err => {
                showMessage('Erreur audit : ' + err.message, 'error');
            });
    };

    window.loadNotifications = function() {
        showMessage('Notifications : endpoint legacy fermé. Fonction à migrer.', 'info');
    };

    window.analyzeQuality = function() {
        if (typeof loadExerciseQualitySummary === 'function') loadExerciseQualitySummary();
        if (typeof loadExerciseQualityList === 'function') loadExerciseQualityList();
        if (typeof loadExercisesQuality === 'function') loadExercisesQuality();
        showMessage('Analyse qualité lancée', 'success');
    };

    window.importResources = function() {
        if (typeof showAddResourceForm === 'function') showAddResourceForm();
        if (typeof loadResourcesList === 'function') loadResourcesList();
        showMessage('Formulaire d\'import prêt', 'info');
    };

    window.exportResources = function() {
        const url = apiUrl('resources.php', { type: 'list' });
        fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Erreur export');
                }
                const rows = data.data || [];
                if (!rows.length) {
                    showMessage('Aucune ressource à exporter', 'info');
                    return;
                }
                const headers = ['id', 'Titre', 'Type', 'Lien', 'Date_Ajout', 'Actif'];
                const csvLines = [headers.join(',')];
                rows.forEach(row => {
                    const line = headers.map(key => {
                        const value = row[key] ?? '';
                        return '"' + String(value).replace(/"/g, '""') + '"';
                    }).join(',');
                    csvLines.push(line);
                });
                const blob = new Blob([csvLines.join('\n')], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'ressources_pedagogiques.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);
                showMessage('Export des ressources terminé', 'success');
            })
            .catch(err => {
                showMessage('Erreur export ressources : ' + err.message, 'error');
            });
    };

    window.refreshSecurity = function() {
        if (typeof loadSecurityAlerts === 'function') loadSecurityAlerts();
        if (typeof loadSecurityLogins === 'function') loadSecurityLogins();
        showMessage('Sécurité actualisée', 'success');
    };

    window.generateReport = function() {
        const type = document.getElementById('report-type')?.value || 'progression';
        const period = document.getElementById('report-period')?.value || '30d';
        const url = apiUrl('report.php', { type, period });
        fetchJson(url, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(data => {
                const status = document.getElementById('reporting-status');
                const chart = document.getElementById('reporting-chart');
                if (data.success) {
                    if (status) status.textContent = 'Rapport généré avec succès.';
                    if (chart) chart.textContent = JSON.stringify(data.data, null, 2);
                    showMessage('Rapport généré', 'success');
                } else {
                    if (status) status.textContent = 'Erreur: ' + (data.error || 'Inconnue');
                    if (chart) chart.textContent = '';
                    showMessage('Erreur génération rapport', 'error');
                }
            })
            .catch(err => {
                const status = document.getElementById('reporting-status');
                if (status) status.textContent = 'Erreur: ' + err.message;
                showMessage('Erreur génération rapport: ' + err.message, 'error');
            });
    };

    function detachStudent(studentId, studentName) {
        if (!confirm(`Êtes-vous sûr de vouloir détacher "${studentName}" de son parent ?`)) {
            return;
        }

        const apiEndpoint = apiUrl('parents', { student_id: studentId });
        console.log('🔄 Détachement élève...', apiEndpoint);

        fetchJson(apiEndpoint, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.csrfToken || ''
            }
        })
        .then(data => {
            console.log('✅ Réponse détachement:', data);
            if (data.success) {
                showMessage(data.message || 'Élève détaché avec succès', 'success');
                loadParentsData();
                loadStudentsList();
            } else {
                console.error('❌ Erreur détachement:', data.error);
                showMessage(data.error || 'Erreur lors du détachement', 'error');
            }
        })
        .catch(err => {
            console.error('❌ Erreur détachement:', err);
            showMessage('Erreur de connexion: ' + err.message, 'error');
        });
    }

    function viewParentChildren(parentId) {
        // Afficher les enfants d'un parent dans une modal ou un panneau
        const apiEndpoint = apiUrl('parents', { type: 'students', parent_id: parentId });
        console.log('🔄 Chargement enfants parent...', apiEndpoint);

        fetchJson(apiEndpoint, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(data => {
                console.log('✅ Enfants parent reçus:', data);
                if (data.success) {
                    const students = data.data || [];
                    if (students.length === 0) {
                        alert('Ce parent n\'a aucun enfant rattaché.');
                    } else {
                        const list = students.map(s => {
                            const name = [s.Prenom, s.Nom].filter(Boolean).join(' ') || s.Username;
                            return `- ${name} (${s.UserLevel || '-'})`;
                        }).join('\n');
                        alert(`Enfants rattachés:\n\n${list}`);
                    }
                } else {
                    console.error('❌ Erreur API enfants:', data.error);
                    showMessage('Erreur lors du chargement: ' + (data.error || 'Inconnue'), 'error');
                }
            })
            .catch(err => {
                console.error('❌ Erreur chargement enfants:', err);
                showMessage('Erreur lors du chargement: ' + err.message, 'error');
            });
    }

    function refreshParentsData() {
        loadParentsData();
        showMessage('Données actualisées', 'success');
    }

    // Fonctions globales pour les boutons onclick
    window.detachStudent = detachStudent;
    window.viewParentChildren = viewParentChildren;
    window.resetAttachForm = resetAttachForm;
    window.showAttachModal = function(studentId, studentName) {
        // Activer l'onglet rattachement et pré-remplir
        const attachTab = document.querySelector('[data-tab="attach-student"]');
        if (attachTab) {
            attachTab.click();
            setTimeout(() => {
                const select = document.getElementById('attach-student-select');
                if (select) {
                    select.value = studentId;
                }
            }, 100);
        }
    };

    // Nettoyer à la fermeture
    window.addEventListener('beforeunload', stopAutoRefresh);

})();
