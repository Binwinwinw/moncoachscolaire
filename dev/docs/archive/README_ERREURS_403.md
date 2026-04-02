# 🎯 ERREURS 403 DASHBOARD ADMIN - PLAN D'ACTION

## ⚡ Résumé du Problème

**Les erreurs 403 = Accès refusé** (par sécurité, car tu n'es pas connecté en admin)

```
❌ Erreur lors du chargement des statistiques: Erreur HTTP 403
❌ Erreur chargement qualité: Erreur HTTP 403
❌ Erreur lors du chargement des utilisateurs: Erreur HTTP 403
❌ Erreur lors du chargement des logs: Erreur HTTP 403
```

## ✅ Solution (LOCAL)

### OPTION 1 : Connexion Rapide (Recommandée pour tester)

1. **Visite cette URL dans le navigateur:**
   ```
   http://localhost/moncoachscolaire/login_admin_quick.php
   ```

2. **Tu seras automatiquement connecté en admin**

3. **Tu seras redirigé vers le dashboard_admin**

4. **Les erreurs 403 devraient disparaître** ✅

### OPTION 2 : Connexion Normale

1. Visite: `http://localhost/moncoachscolaire/public/index.php?page=login`
2. Connecte-toi avec un compte admin (ex: `zinzin`)
3. Va à: `http://localhost/moncoachscolaire/public/index.php?page=dashboard_admin`
4. Ça devrait fonctionner ✅

## 📊 Vérification

### Test 1 : Vérifier que les APIs fonctionnent
```
Visite: http://localhost/moncoachscolaire/test_api_admin.html
Cela affiche le statut HTTP de chaque API
```

### Test 2 : Comprendre le problème
```
Visite: http://localhost/moncoachscolaire/test_session_admin.php
Cela montre si tu es connecté en admin
```

### Test 3 : Voir les données en action
```
Visite: http://localhost/moncoachscolaire/demo_dashboard_api.php
Cela charge et affiche les données réelles des APIs
(Après avoir utilisé login_admin_quick.php)
```

## 🔍 Diagnostique Technique

### Pourquoi 403 ?

Les APIs admin (dans `src/api/admin/*.php`) contiennent cette vérification :

```php
if (!isAdmin()) {
    return error(403, "Accès refusé. Administrateur requis.");
}
```

C'est une **sécurité intentionnelle**. Les APIs protègent :
- Les statistiques du site
- Les données des utilisateurs
- Les logs administrateur
- Les analyses de qualité

### Flux normal :

```
1. Tu ouvres : /public/index.php?page=dashboard_admin
2. La page charge et affiche l'interface
3. JavaScript fait : fetch('/api/admin/stats.php')
4. Le navigateur envoie le cookie PHPSESSID automatiquement
5. L'API vérifie : es-tu admin ?
6. Si OUI → Retourne les données ✅
7. Si NON → Retourne 403 ❌
```

## 📁 Fichiers impliqués

### Pages
- `src/pages/dashboard_admin.php` - Interface du dashboard
- `login_admin_quick.php` - Connexion rapide (DEV)

### APIs
- `src/api/admin/stats.php` - Statistiques
- `src/api/admin/users.php` - Utilisateurs
- `src/api/admin/logs.php` - Logs
- `src/api/admin/exercise_quality.php` - Qualité des exercices (fusionné)

### Système
- `src/includes/admin_auth.php` - Authentification admin
- `api_proxy.php` - Routeur des APIs
- `.htaccess` - Règles de routage
- `public/assets/js/admin-dashboard.js` - JavaScript frontend

## 🚀 Pour la PRODUCTION (Hostinger)

### Actions requises :

1. **Vérifier qu'il y a un admin:**
   ```sql
   SELECT * FROM Users WHERE Role = 'admin';
   ```

2. **Uploader tous les fichiers:**
   - ✅ `api_proxy.php`
   - ✅ `src/api/admin/*.php`
   - ✅ `src/includes/admin_auth.php`
   - ✅ `.htaccess` (avec règles API)
   - ✅ `src/pages/dashboard_admin.php`

3. **Tester en production:**
   - [ ] Connecte-toi au site (en admin)
   - [ ] Va à: `https://moncoachscolaire.fr/public/index.php?page=dashboard_admin`
   - [ ] Vérifie que les erreurs 403 ont disparu
   - [ ] Vérifie que tu vois les données (stats, users, etc.)

## 📌 Important

### ❌ NE PAS faire :

- ❌ Ne pas désactiver l'authentification admin (sécurité!)
- ❌ Ne pas mettre le dashboard_admin en accès public
- ❌ Ne pas uploader des fichiers test en production

### ✅ À faire :

- ✅ S'assurer qu'il existe un compte admin
- ✅ Se connecter en admin avant d'accéder au dashboard
- ✅ Vérifier que les cookies sont activés dans le navigateur

## 📞 Si tu as toujours des problèmes

1. Ouvre la console JavaScript (F12)
2. Va au tab "Network"
3. Clique sur un onglet du dashboard
4. Cherche les requêtes à `/api/admin/*`
5. Clique dessus et regarde la réponse
6. Cela te montrera exactement pourquoi c'est 403

Le code 403 signifie **TOUJOURS** : "Pas d'authentification" ou "Pas d'autorisation"

---

## ✨ Résumé

| Étape | Action | URL |
|-------|--------|-----|
| 1 | Te connecter rapidement | `/login_admin_quick.php` |
| 2 | Vérifier ton statut | `/test_session_admin.php` |
| 3 | Tester les APIs | `/test_api_admin.html` |
| 4 | Voir le dashboard | `/public/index.php?page=dashboard_admin` |
| 5 | Lire la doc complète | `/SOLUTION_ERREURS_403.md` |

**C'est tout! Les erreurs 403 vont disparaître dès que tu seras connecté en admin.** ✅
