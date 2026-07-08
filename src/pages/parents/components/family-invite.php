<?php
// Composant d'invitation parent/enfant
$apiBase = function_exists('site_url') ? site_url('api/parent_family') : '/api/parent_family';
?>
<section class="mb-10" id="family-invite-section">
    <h2 class="mb-5 flex items-center gap-2 text-xl font-bold text-slate-900 md:text-2xl">
        <span class="text-lg">🧩</span> Rattachement parent/élève
    </h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl border border-slate-200 bg-white/90 p-6 shadow-sm">
            <h3 class="mb-3 text-lg font-semibold text-slate-900">Code de rattachement</h3>
            <p class="mb-4 text-sm text-slate-600">Un code de 6 caractères est valide 24h. Partagez-le à votre enfant pour qu'il le saisisse dans son espace.</p>
            <button id="generate-family-code" class="rounded-lg bg-emerald-700 px-5 py-3 font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">Générer un code</button>
            <p id="family-code-output" class="mt-3 text-base font-bold text-slate-900"></p>
            <p id="family-code-expiry" class="text-sm text-slate-500"></p>
            <div id="family-code-warning" class="mt-3 text-sm text-rose-700"></div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white/90 p-6 shadow-sm">
            <h3 class="mb-3 text-lg font-semibold text-slate-900">Codes générés récemment</h3>
            <div id="family-codes-list" class="text-sm text-slate-700">
                <label for="family-code-select" class="mb-2 block text-sm font-medium text-slate-700">Choisir un code</label>
                <select id="family-code-select" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2" aria-describedby="family-code-detail">
                    <option value="">Chargement…</option>
                </select>

                <div class="mt-3 flex items-center gap-2">
                    <button id="copy-selected-code" type="button" class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" disabled>
                        Copier le code
                    </button>
                    <span id="copy-selected-code-feedback" class="text-xs text-slate-500" aria-live="polite"></span>
                </div>

                <div id="family-code-detail" class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600" aria-live="polite">
                    Sélectionnez un code pour afficher son détail.
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const apiUrl = <?php echo json_encode($apiBase, JSON_UNESCAPED_SLASHES); ?>;
    const codeOutput = document.getElementById('family-code-output');
    const codeExpiry = document.getElementById('family-code-expiry');
    const codeWarning = document.getElementById('family-code-warning');
    const codeSelect = document.getElementById('family-code-select');
    const codeDetail = document.getElementById('family-code-detail');
    const copyCodeButton = document.getElementById('copy-selected-code');
    const copyCodeFeedback = document.getElementById('copy-selected-code-feedback');
    let cachedCodes = [];

    function setCopyFeedback(message, isError = false) {
        if (!copyCodeFeedback) {
            return;
        }
        copyCodeFeedback.textContent = message;
        copyCodeFeedback.className = 'text-xs ' + (isError ? 'text-rose-700' : 'text-emerald-700');
    }

    async function copyTextToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            return;
        }

        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.setAttribute('readonly', '');
        textArea.style.position = 'absolute';
        textArea.style.left = '-9999px';
        document.body.appendChild(textArea);
        textArea.select();

        const copied = document.execCommand('copy');
        document.body.removeChild(textArea);

        if (!copied) {
            throw new Error('Copie non supportée sur ce navigateur.');
        }
    }

    async function request(action, method = 'GET', body = null) {
        let url = apiUrl;
        if (method === 'GET') {
            url += (url.includes('?') ? '&' : '?') + 'action=' + encodeURIComponent(action);
        }

        const init = { method, headers: {} };
        if (method === 'POST') {
            init.headers['Content-Type'] = 'application/json';
            init.headers['X-CSRF-Token'] = window.csrfToken || '';
            body = body || { action };
            init.body = JSON.stringify(body);
        }

        const response = await fetch(url, init);

        const contentType = response.headers.get('Content-Type') || '';
        let data;
        if (contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            throw new Error('API response is not JSON: ' + text.slice(0, 200));
        }

        if (!response.ok || !data.success) {
            throw new Error((data.error || 'Erreur API inconnue'));
        }
        return data.data;
    }

    async function loadCodes() {
        if (codeSelect) {
            codeSelect.innerHTML = '<option value="">Chargement…</option>';
            codeSelect.disabled = true;
        }
        try {
            const data = await request('my_codes', 'GET');
            cachedCodes = Array.isArray(data.codes) ? data.codes : [];

            if (!cachedCodes.length) {
                if (codeSelect) {
                    codeSelect.innerHTML = '<option value="">Aucun code généré</option>';
                    codeSelect.disabled = true;
                }
                if (copyCodeButton) {
                    copyCodeButton.disabled = true;
                }
                setCopyFeedback('');
                if (codeDetail) {
                    codeDetail.textContent = 'Aucun code généré pour le moment.';
                }
                return;
            }

            if (codeSelect) {
                const options = cachedCodes.map((code, index) => {
                    const token = code.invite_token || 'Code';
                    const status = code.status === 'pending' ? 'En attente' : code.status === 'accepted' ? 'Accepté' : (code.status || 'Inconnu');
                    const selected = index === 0 ? ' selected' : '';
                    return '<option value="' + index + '"' + selected + '>' + token + ' - ' + status + '</option>';
                }).join('');
                codeSelect.innerHTML = options;
                codeSelect.disabled = false;
            }

            renderSelectedCode();
        } catch (err) {
            if (codeSelect) {
                codeSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                codeSelect.disabled = true;
            }
            if (copyCodeButton) {
                copyCodeButton.disabled = true;
            }
            setCopyFeedback('');
            if (codeDetail) {
                codeDetail.innerHTML = '<span class="text-rose-700">Erreur : ' + err.message + '</span>';
            }
        }
    }

    function renderSelectedCode() {
        if (!codeSelect || !codeDetail) {
            return;
        }

        const index = parseInt(codeSelect.value, 10);
        const code = Number.isInteger(index) ? cachedCodes[index] : null;

        if (!code) {
            if (copyCodeButton) {
                copyCodeButton.disabled = true;
            }
            setCopyFeedback('');
            codeDetail.textContent = 'Sélectionnez un code pour afficher son détail.';
            return;
        }

        if (copyCodeButton) {
            copyCodeButton.disabled = false;
        }
        setCopyFeedback('');

        const status = code.status === 'pending' ? 'En attente' : code.status === 'accepted' ? 'Accepté' : (code.status || 'Inconnu');
        const createdAt = code.created_at ? new Date(code.created_at).toLocaleString('fr-FR') : '—';
        const expiresAt = code.expired_at ? new Date(code.expired_at).toLocaleString('fr-FR') : '—';
        const acceptedAt = code.accepted_at ? new Date(code.accepted_at).toLocaleString('fr-FR') : '—';

        codeDetail.innerHTML =
            '<div class="text-sm font-semibold text-slate-900">Code sélectionné : ' + (code.invite_token || '—') + '</div>' +
            '<div class="mt-1">Statut : <span class="font-medium text-slate-800">' + status + '</span></div>' +
            '<div class="mt-1">Créé : ' + createdAt + '</div>' +
            '<div class="mt-1">Expire : ' + expiresAt + '</div>' +
            '<div class="mt-1">Accepté : ' + acceptedAt + '</div>';
    }

    async function generateCode() {
        codeWarning.textContent = '';
        try {
            const data = await request('generate_code', 'POST', { action: 'generate_code' });
            codeOutput.textContent = 'Code : ' + data.invite_token;
            codeExpiry.textContent = 'Valable jusqu’au ' + new Date(data.expires_at).toLocaleString('fr-FR');
            await loadCodes();
        } catch (err) {
            codeWarning.textContent = err.message;
        }
    }

    document.getElementById('generate-family-code').addEventListener('click', generateCode);
    if (codeSelect) {
        codeSelect.addEventListener('change', renderSelectedCode);
    }
    if (copyCodeButton) {
        copyCodeButton.addEventListener('click', async function () {
            const index = parseInt(codeSelect ? codeSelect.value : '', 10);
            const code = Number.isInteger(index) ? cachedCodes[index] : null;
            const token = code && code.invite_token ? String(code.invite_token) : '';

            if (!token) {
                setCopyFeedback('Aucun code à copier.', true);
                return;
            }

            try {
                await copyTextToClipboard(token);
                setCopyFeedback('Code copié.');
            } catch (error) {
                setCopyFeedback(error.message || 'Impossible de copier le code.', true);
            }
        });
    }
    loadCodes();
})();
</script>
