# Routage et Sécurité des Dashboards

## 🎯 Vue d'ensemble

Le système de routage gère automatiquement l'accès aux différents dashboards selon le type d'utilisateur connecté. Chaque dashboard vérifie ses propres permissions avant d'afficher le contenu.

---

## 🔀 Routage Automatique

### Topbar - Bouton "Mon espace"

Le bouton "Mon espace" dans la topbar redirige automatiquement vers le bon dashboard :

- **Admin** → `dashboard_admin` (avec icône ⚙️)
- **Parent** → `dashboard_parent` (avec icône 👨‍👩‍👧‍👦)
- **Élève** → `dashboard` (standard)

### Détection du Type d'Utilisateur

La topbar détecte automatiquement le type d'utilisateur :

1. **Admin** : Vérifie `isAdmin()` via `includes/admin_auth.php`
2. **Parent** : Vérifie `$_SESSION['parent_id']`
3. **Élève** : Vérifie `$_SESSION['user_id']` et `$_SESSION['logged_in']`

---

## 🛡️ Sécurité du Routeur

### Dashboard Admin (`dashboard_admin`)

**Routeur** (`index.php`) :
- Charge `admin_auth.php` si nécessaire
- Laisse passer la requête
- **Sécurité** : `dashboard_admin.php` utilise `requireAdmin()` qui redirige si non-admin

**Accès** :
- ✅ Utilisateurs avec `Role = 'admin'` dans la table `Users`
- ❌ Tous les autres utilisateurs → Redirection vers `dashboard` ou `login`

### Dashboard Parent (`dashboard_parent`)

**Routeur** (`index.php`) :
- Vérifie la session parent
- Laisse passer la requête
- **Sécurité** : `dashboard_parent.php` vérifie `$_SESSION['parent_id']` et redirige si absent

**Accès** :
- ✅ Utilisateurs avec `$_SESSION['parent_id']` défini
- ❌ Tous les autres → Redirection vers `login_parents`

### Dashboard Élève (`dashboard`)

**Routeur** (`index.php`) :
- Pas de vérification spéciale
- **Sécurité** : `dashboard.php` vérifie `$_SESSION['user_id']` et `$_SESSION['logged_in']`

**Accès** :
- ✅ Utilisateurs connectés (élèves)
- ❌ Non connectés → Redirection vers `login`
- ❌ Compte démo → Redirection vers `demo`

---

## 🔒 Sécurité .htaccess

Le fichier `.htaccessbak` (à renommer en `.htaccess` en production) inclut :

### Protections Actives

1. **Fichiers sensibles** :
   - `.env`, `.ini`, `.log`, `.sql`, `.bak`, `.md`
   - `config.php`, `debug_env_loading.php`

2. **Dossiers protégés** :
   - `.git`, `.vscode`, `.assistant`
   - `backups`, `dev`, `docs`, `tools`, `tests`, `db`, `includes`

3. **APIs** :
   - Accès autorisé mais sécurité gérée par PHP
   - Chaque endpoint vérifie l'authentification

4. **Headers de sécurité** :
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: SAMEORIGIN`
   - `X-XSS-Protection: 1; mode=block`
   - `Referrer-Policy: strict-origin-when-cross-origin`

5. **Sessions PHP** :
   - `session.cookie_httponly = 1`
   - `session.cookie_secure = 1` (HTTPS requis)
   - `session.use_only_cookies = 1`

---

## 📋 Flux d'Authentification

### Connexion Admin

1. Utilisateur se connecte via `login.php`
2. `login.php` vérifie le rôle dans la BDD
3. Si `Role = 'admin'` → `$_SESSION['user_role'] = 'admin'`
4. Topbar détecte admin via `isAdmin()`
5. Bouton "Mon espace" → `dashboard_admin`
6. `dashboard_admin.php` vérifie via `requireAdmin()`

### Connexion Parent

1. Parent se connecte via `login_parents.php`
2. `login_parents.php` définit `$_SESSION['parent_id']`
3. Topbar détecte parent via `$_SESSION['parent_id']`
4. Bouton "Mon espace" → `dashboard_parent`
5. `dashboard_parent.php` vérifie `$_SESSION['parent_id']`

### Connexion Élève

1. Élève se connecte via `login.php`
2. `login.php` définit `$_SESSION['user_id']` et `$_SESSION['logged_in']`
3. Topbar détecte élève via `$_SESSION['user_id']`
4. Bouton "Mon espace" → `dashboard`
5. `dashboard.php` vérifie `$_SESSION['user_id']` et `$_SESSION['logged_in']`

---

## ✅ Checklist de Sécurité

- [x] Topbar détecte automatiquement le type d'utilisateur
- [x] Bouton "Mon espace" redirige vers le bon dashboard
- [x] Routeur permet l'accès aux dashboards sans redirection automatique
- [x] Chaque dashboard vérifie ses propres permissions
- [x] `.htaccessbak` configuré pour la production
- [x] Protection des fichiers sensibles
- [x] Protection des dossiers système
- [x] Headers de sécurité configurés
- [x] Sessions sécurisées

---

## 🚀 Déploiement Production

Pour activer la sécurité `.htaccess` en production :

1. Renommer `.htaccessbak` en `.htaccess`
2. Vérifier que les modules Apache sont activés :
   - `mod_rewrite`
   - `mod_headers`
3. Décommenter la règle HTTPS si nécessaire (ligne 56-58)
4. Tester l'accès aux dashboards avec chaque type d'utilisateur

---

*Documentation du système de routage et de sécurité des dashboards*
