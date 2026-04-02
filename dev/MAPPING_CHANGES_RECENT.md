# Changements récents (automatique)

- [x] Remplacement de `site_url('dashboard')` → `site_url('eleve/dashboard')` dans les pages élèves (college/*, lycee/*, bac/*) et harmonisation des fallback `/eleve/dashboard`
- [x] `src/includes/admin_auth.php` : redirection vers dashboard spécifique selon le rôle (`admin/dashboard_admin`, `parents/dashboard_parent`, `eleve/dashboard`)
- [x] `src/pages/system/progression.php` : correction du redirect catch vers `parents/dashboard_parent`

Fait le : 2026-02-01

Notes :
- Ces changements couvrent la majorité des liens internes vers le "dashboard" non préfixé. Si vous voulez, je peux intégrer ces notes dans `dev/MAPPING_LIENS_A_CORRIGER.md` ou ajouter des lignes détaillées par fichier.
