<?php
// Composant d'invitation parent/enfant
$apiBase = function_exists('site_url') ? site_url('api/parent_family') : '/api/parent_family';
?>
<section class="mb-10" id="family-invite-section">
    <h2 class="text-xl md:text-2xl font-bold text-indigo-800 mb-5 flex items-center gap-2">
        <span class="text-lg">🧩</span> Rattachement parent/élève
    </h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-2xl shadow p-6 bg-white">
            <h3 class="text-lg font-semibold mb-3">Générer un code pour votre enfant</h3>
            <p class="text-sm text-slate-600 mb-4">Un code de 6 caractères est valide 24h. Partagez-le à l'élève pour qu'il le saisisse dans son espace.</p>
            <button id="generate-family-code" class="px-5 py-3 rounded-lg bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition">Générer un code</button>
            <p id="family-code-output" class="mt-3 text-base font-bold text-slate-800"></p>
            <p id="family-code-expiry" class="text-sm text-slate-500"></p>
            <div id="family-code-warning" class="mt-3 text-sm text-red-600"></div>
        </div>

        <div class="rounded-2xl shadow p-6 bg-white">
            <h3 class="text-lg font-semibold mb-3">Codes générés récemment</h3>
            <div id="family-codes-list" class="space-y-2 text-sm text-slate-700">Chargement…</div>
        </div>
    </div>
</section>

<script>
(function () {
    const apiUrl = <?php echo json_encode($apiBase, JSON_UNESCAPED_SLASHES); ?>;
    const codeOutput = document.getElementById('family-code-output');
    const codeExpiry = document.getElementById('family-code-expiry');
    const codeWarning = document.getElementById('family-code-warning');
    const codesList = document.getElementById('family-codes-list');

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
        codesList.textContent = 'Chargement…';
        try {
            const data = await request('my_codes', 'GET');
            if (!data.codes.length) {
                codesList.textContent = 'Aucun code généré pour le moment.';
                return;
            }

            codesList.innerHTML = data.codes.map(code => {
                const status = code.status === 'pending' ? 'En attente' : code.status === 'accepted' ? 'Accepté' : code.status;
                const expires = code.expired_at ? new Date(code.expired_at).toLocaleString('fr-FR') : '—';
                const acceptedAt = code.accepted_at ? new Date(code.accepted_at).toLocaleString('fr-FR') : '—';
                return '<div class="rounded-lg border p-3 bg-slate-50">' +
                    '<div class="font-semibold">' + code.invite_token + ' <span class="text-xs text-slate-500">(' + status + ')</span></div>' +
                    '<div class="text-xs text-slate-500">Créé : ' + new Date(code.created_at).toLocaleString('fr-FR') + '</div>' +
                    '<div class="text-xs text-slate-500">Expire : ' + expires + '</div>' +
                    '<div class="text-xs text-slate-500">Accepté : ' + acceptedAt + '</div>' +
                    '</div>';
            }).join('');
        } catch (err) {
            codesList.innerHTML = '<div class="text-red-600">Erreur : ' + err.message + '</div>';
        }
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
    loadCodes();
})();
</script>
