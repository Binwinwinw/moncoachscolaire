<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
// Minimal admin UI for External API Integrations
// Start session safely
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
// Charger admin_auth de façon robuste
if (is_file(dirname(__DIR__, 2) . '/includes/admin_auth.php')) {
    require_once dirname(__DIR__, 2) . '/includes/admin_auth.php';
    requireAdmin();
} else {
    // fallback: try legacy path
    require_once __DIR__ . '/../../../includes/admin_auth.php';
    requireAdmin();
}
if (!function_exists('asset_url')) {
    function asset_url($p)
    {
        return '/' . $p;
    }
}
?>
<main class="max-w-4xl mx-auto my-8 p-6 bg-white rounded-2xl shadow-lg">
  <div class="mb-8 border-b pb-4">
    <h1 class="text-2xl font-bold text-slate-800 mb-2">Intégrations API tierces</h1>
    <p class="text-slate-600">Gérer les connexions et intégrations externes</p>
  </div>

  <div class="mb-6 flex justify-between items-center">
    <button id="btn-new" class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition">➕ Nouvelle intégration</button>
  </div>

  <div id="integrations-list"></div>

  <div id="integration-modal" class="fixed top-0 left-0 w-full h-full flex items-center justify-center bg-black bg-opacity-40 z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-xl">
      <h3 id="modal-title" class="text-xl font-semibold mb-4">Nouvelle intégration</h3>
      <form id="integration-form" class="space-y-4">
        <input type="hidden" name="id" id="int-id">
        <div><label class="block mb-1 font-medium">Nom: <input name="name" id="int-name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400" /></label></div>
        <div><label class="block mb-1 font-medium">Type: <input name="type" id="int-type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400" /></label></div>
        <div><label class="block mb-1 font-medium">Config (JSON): <textarea id="int-config" name="config" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400 min-h-[120px]"></textarea></label></div>
        <div class="flex items-center gap-2"><input type="checkbox" id="int-enabled" class="form-checkbox" /> <label for="int-enabled" class="font-medium">Activée</label></div>
        <div class="flex gap-2 mt-2">
          <button id="btn-save" class="bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-green-700 transition">Enregistrer</button>
          <button id="btn-cancel" class="bg-gray-400 text-white px-4 py-2 rounded-lg shadow hover:bg-gray-500 transition">Annuler</button>
          <button id="btn-test" class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 transition">Tester la connexion</button>
        </div>
      </form>
      <div id="test-result" class="mt-3 text-gray-600"></div>
    </div>
  </div>

  <script>
    // API base dynamic, use site_url if available
    const apiBase = <?php echo json_encode(function_exists('site_url') ? site_url('api/admin/external_api_integrations') : '/api/admin/external_api_integrations'); ?>;

    async function listIntegrations(){
      const res = await fetch(apiBase);
      const json = await res.json();
      const root = document.getElementById('integrations-list');
      if(!json.data || !json.data.length){ root.innerHTML = '<p>Aucune intégration.</p>'; return; }
      let html = '<table class="admin-table"><thead><tr><th>Nom</th><th>Type</th><th>État</th><th>Dernier statut</th><th>Actions</th></tr></thead><tbody>';
      json.data.forEach(it => {
        html += `<tr>
          <td class="px-3 py-2 border-b">${it.name}</td>
          <td class="px-3 py-2 border-b">${it.type||''}</td>
          <td class="px-3 py-2 border-b">${it.enabled?'<span class=\'text-green-700 font-semibold\'>Activée</span>':'<span class=\'text-gray-500\'>Inactivée</span>'}</td>
          <td class="px-3 py-2 border-b">${it.last_status||''} ${it.last_checked_at?('<br/><small class=\'text-gray-400\'>' + it.last_checked_at + '</small>'):''}</td>
          <td class="px-3 py-2 border-b flex gap-2">
            <button class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition" onclick="openEdit(${it.id})">✏️</button>
            <button class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition" onclick="deleteIntegration(${it.id})">🗑️</button>
            <button class="bg-indigo-500 text-white px-2 py-1 rounded hover:bg-indigo-600 transition" onclick="testIntegration(${it.id})">🔗 Test</button>
          </td>
        </tr>`;
      });
      html += '</tbody></table>';
      root.innerHTML = `<table class="min-w-full border rounded-lg shadow text-sm"><thead class="bg-gray-100"><tr><th class="px-3 py-2">Nom</th><th class="px-3 py-2">Type</th><th class="px-3 py-2">État</th><th class="px-3 py-2">Dernier statut</th><th class="px-3 py-2">Actions</th></tr></thead>${html}</table>`;
    }

    function openEdit(id){
      fetch(apiBase + '?id=' + id).then(r=>r.json()).then(j=>{
        const it = j.data;
        document.getElementById('int-id').value = it.id;
        document.getElementById('int-name').value = it.name;
        document.getElementById('int-type').value = it.type || '';
        document.getElementById('int-config').value = it.config || '';
        document.getElementById('int-enabled').checked = !!it.enabled;
        document.getElementById('modal-title').textContent = 'Modifier ' + it.name;
        document.getElementById('integration-modal').style.display = 'block';
      });
    }

    function resetModal(){
      document.getElementById('int-id').value = '';
      document.getElementById('int-name').value = ''; document.getElementById('int-type').value = ''; document.getElementById('int-config').value = '';
      document.getElementById('int-enabled').checked = false;
      document.getElementById('test-result').textContent = '';
      document.getElementById('modal-title').textContent = 'Nouvelle intégration';
    }

    document.getElementById('btn-new').addEventListener('click', function(){ resetModal(); document.getElementById('integration-modal').style.display='block'; });
    document.getElementById('btn-cancel').addEventListener('click', function(e){ e.preventDefault(); document.getElementById('integration-modal').style.display='none'; });

    document.getElementById('integration-form').addEventListener('submit', async function(e){
      e.preventDefault();
      const id = document.getElementById('int-id').value;
      const payload = { name: document.getElementById('int-name').value, type: document.getElementById('int-type').value, config: JSON.parse(document.getElementById('int-config').value || '{}'), enabled: document.getElementById('int-enabled').checked };
      const res = await fetch(apiBase + (id?('?id='+id):''), { method: id ? 'PUT' : 'POST', headers: { 'Content-Type':'application/json' }, body: JSON.stringify(payload)});
      const json = await res.json();
      document.getElementById('integration-modal').style.display='none';
      listIntegrations();
    });

    async function deleteIntegration(id){ if (!confirm('Supprimer cette intégration ?')) return; await fetch(apiBase + '?id='+id, { method: 'DELETE' }); listIntegrations(); }
    async function testIntegration(id){ document.getElementById('test-result').textContent = 'Test en cours...'; const res = await fetch(apiBase + '?action=test&id=' + id, { method: 'POST' }); const json = await res.json(); document.getElementById('test-result').textContent = JSON.stringify(json.result || json); listIntegrations(); }

    document.addEventListener('DOMContentLoaded', listIntegrations);
  </script>
</main>

