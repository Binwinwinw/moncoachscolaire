<?php
// Helpers obligatoires (migration architecture)
if (!function_exists('get_validated_param')) {
    require_once dirname(__DIR__, 2) . '/includes/page_guards.php';
}
if (!function_exists('safe_redirect')) {
    require_once dirname(__DIR__, 2) . '/includes/redirect_helpers.php';
}
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
    require_once __DIR__ . '/../../../includes/admin_auth.php';
    requireAdmin();
}
// Minimal admin audit page
if (!function_exists('asset_url')) {
    function asset_url($path)
    {
        return '/' . $path;
    }
}
?><main class="max-w-5xl mx-auto my-8 p-6 bg-white rounded-2xl shadow-lg">
  <div class="mb-8 border-b pb-4">
    <h1 class="text-2xl font-bold text-slate-800 mb-2">Historique des actions admin</h1>
    <p class="text-slate-600">Journal des opérations effectuées par les administrateurs</p>
  </div>

  <div class="mb-6">
    <div class="flex flex-wrap gap-3 items-center">
      <input id="filter-search" placeholder="Recherche (admin, action, ressource)" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400" />
      <input type="date" id="filter-from" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400" />
      <input type="date" id="filter-to" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400" />
      <select id="filter-action" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:border-blue-400"><option value="">Toutes actions</option></select>
      <button class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 transition" id="btn-export">Exporter CSV</button>
      <button class="bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-green-700 transition" id="btn-filter">Filtrer</button>
    </div>
  </div>

  <div id="audit-table"></div>
  <div id="audit-pagination" class="mt-4 flex gap-3 items-center"></div>

  <script>
    let currentPage = 1;
    let totalPages = 1;

    function buildQuery(params){
      const qs = new URLSearchParams(params);
      return qs.toString() ? ('?' + qs.toString()) : '';
    }

    async function loadAudit(page = 1){
      const search = document.getElementById('filter-search').value.trim();
      const from = document.getElementById('filter-from').value;
      const to = document.getElementById('filter-to').value;
      const action = document.getElementById('filter-action').value;
      const params = { page: page, limit: 30 };
      if (search) params.search = search;
      if (from) params.from = from;
      if (to) params.to = to;
      if (action) params.action = action;

      const apiBase = <?php echo json_encode(function_exists('site_url') ? site_url('api/admin/admin_audit') : '/api/admin/admin_audit'); ?>;
      const res = await fetch(apiBase + buildQuery(params));
      const json = await res.json();
      const root = document.getElementById('audit-table');
      if(!json.data || !json.data.length){ root.innerHTML = '<p>Aucun résultat.</p>'; document.getElementById('audit-pagination').innerHTML = ''; return; }
      let html = '<table class="admin-table"><thead><tr><th>ID</th><th>Admin</th><th>Action</th><th>Ressource</th><th>Meta</th><th>IP</th><th>Quand</th></tr></thead><tbody>';
      json.data.forEach(r => {
        let meta = r.meta ? (typeof r.meta === 'string' ? r.meta : JSON.stringify(r.meta)) : '';
        html += `<tr>
          <td class="px-3 py-2 border-b">${r.id}</td>
          <td class="px-3 py-2 border-b">${r.username||r.admin_id}</td>
          <td class="px-3 py-2 border-b">${r.action}</td>
          <td class="px-3 py-2 border-b">${r.resource||''}</td>
          <td class="px-3 py-2 border-b">${meta}</td>
          <td class="px-3 py-2 border-b">${r.ip||''}</td>
          <td class="px-3 py-2 border-b">${r.created_at}</td>
        </tr>`;
      });
      html += '</tbody></table>';
      root.innerHTML = `<table class="min-w-full border rounded-lg shadow text-sm"><thead class="bg-gray-100"><tr><th class="px-3 py-2">ID</th><th class="px-3 py-2">Admin</th><th class="px-3 py-2">Action</th><th class="px-3 py-2">Ressource</th><th class="px-3 py-2">Meta</th><th class="px-3 py-2">IP</th><th class="px-3 py-2">Quand</th></tr></thead>${html}</table>`;

      // Pagination
      currentPage = json.meta.page || 1;
      totalPages = json.meta.pages || 1;
      const pag = document.getElementById('audit-pagination');
      let pagHtml = '';
      if (currentPage > 1) pagHtml += `<button class="btn" onclick="loadAudit(${currentPage-1})">« Préc</button>`;
      pagHtml += `<span> Page ${currentPage} / ${totalPages} </span>`;
      if (currentPage < totalPages) pagHtml += `<button class="btn" onclick="loadAudit(${currentPage+1})">Suiv »</button>`;
      pag.innerHTML = pagHtml;
    }

    function buildExportUrl(){
      const search = document.getElementById('filter-search').value.trim();
      const from = document.getElementById('filter-from').value;
      const to = document.getElementById('filter-to').value;
      const action = document.getElementById('filter-action').value;
      const params = {};
      if (search) params.search = search;
      if (from) params.from = from;
      if (to) params.to = to;
      if (action) params.action = action;
      return apiBase + '?action=export&' + (new URLSearchParams(params)).toString();
    }

    document.addEventListener('DOMContentLoaded', async function(){
      document.getElementById('btn-filter').addEventListener('click', function(){ loadAudit(1); });
      document.getElementById('btn-export').addEventListener('click', function(){ window.location.href = buildExportUrl(); });
      // Populate action filter dynamically
      const actRes = await fetch(apiBase);      const actJson = await actRes.json();
      const actions = new Set();
      (actJson.data || []).forEach(r => { if (r.action) actions.add(r.action); });
      const sel = document.getElementById('filter-action');
      actions.forEach(a => { const opt = document.createElement('option'); opt.value = a; opt.textContent = a; sel.appendChild(opt); });
      loadAudit(1);
    });
  </script>
</main>
