# 🔴 Erreurs 403 Dashboard Admin - RÉSOLUTION

## 🎯 SOLUTION RAPIDE

**Les erreurs 403 sont liées à l'AUTHENTIFICATION, pas à un bug.**

### Option 1 : Connexion rapide (DEV)
1. Visite : `http://localhost/moncoachscolaire/login_admin_quick.php`
2. Tu seras automatiquement connecté en tant qu'admin
3. Tu seras redirigé vers le dashboard_admin
4. Les erreurs 403 devraient disparaître

### Option 2 : Connexion normale
1. Va à : `http://localhost/moncoachscolaire/public/index.php?page=login`
2. Connecte-toi avec : **username: `zinzin`** (ou un admin de la BD)
3. Va à : `http://localhost/moncoachscolaire/public/index.php?page=dashboard_admin`
4. Les erreurs 403 devraient disparaître

---

## 📊 Diagnostique Technique

### Ce qui se passe :

```
1. Tu accèdes : /public/index.php?page=dashboard_admin
2. La page charge : src/pages/dashboard_admin.php
3. JavaScript charge les données via :
   → GET /api/admin/stats.php
   → GET /api/admin/users.php
   → GET /api/admin/logs.php
   → GET /api/admin/exercise_quality.php
4. Les APIs reçoivent les requêtes
5. Les APIs vérifient : isAdmin() ?
6. Si tu n'es pas connecté en admin → ERROR 403
```

### Pourquoi 403 ?

```php
// Dans src/api/admin/stats.php (ligne ~80):
if (!isAdmin()) {
    sendJsonError('Accès refusé. Administrateur requis.', 403);
}
```

C'est une **vérification de sécurité volontaire** pour protéger les données sensibles.

---

## 🔧 Vérifications

### 1️⃣ Vérifie que tu es connecté
- F12 (Dev Tools) → Application → Cookies
- Cherche le cookie `PHPSESSID`
- S'il n'existe pas, tu n'es pas connecté

### 2️⃣ Vérifie que tu es admin
```bash
# Exécute dans le terminal:
php test_session_admin.php

# Attendu:
✅ Utilisateur IS ADMIN
```

### 3️⃣ Vérifie que les APIs fonctionnent
```bash
# Visite:
http://localhost/moncoachscolaire/test_api_admin.html

# Cela teste chaque API et affiche le statut HTTP
```

### 4️⃣ Créer un admin s'il n'y en a pas
```bash
# Base de données:
INSERT INTO Users (Username, Email, PasswordHash, Role) 
VALUES ('admin', 'admin@test.com', 'hash', 'admin');
```

---

## 📁 Fichiers impliqués

| Fichier | Rôle |
|---------|------|
| `src/pages/dashboard_admin.php` | Page principale du dashboard |
| `public/assets/js/admin-dashboard.js` | JavaScript qui appelle les APIs |
| `src/api/admin/stats.php` | API pour les statistiques |
| `src/api/admin/users.php` | API pour les utilisateurs |
| `src/api/admin/logs.php` | API pour les logs |
| `src/api/admin/exercise_quality.php` | API pour la qualité des exercices |
| `src/includes/admin_auth.php` | Système d'authentification admin |
| `api_proxy.php` | Routeur des APIs en production |
| `.htaccess` | Règles de routage |

---

## ✅ Vérifications Effectuées (Local)

### Ce qui a été testé :
- ✅ `api_proxy.php` existe
- ✅ Tous les fichiers API existent
- ✅ La fonction `isAdmin()` fonctionne
- ✅ Les APIs retournent correctement les données (quand authentifié)
- ✅ Le routage `.htaccess` fonctionne

### Conclusion :
Le système est **CORRECT**. Les erreurs 403 sont **ATTENDUES** si tu n'es pas connecté en admin.

---

## 🚀 Pour la PRODUCTION (Hostinger)

### Actions requises :

1. **Vérifier qu'il y a au moins un admin en base:**
```sql
SELECT * FROM Users WHERE Role = 'admin';
```

2. **Créer un admin si nécessaire:**
```sql
INSERT INTO Users (Username, Email, PasswordHash, Role) 
VALUES ('admin', 'admin@moncoachscolaire.fr', '$2y$10$[HASH]', 'admin');
```

3. **S'assurer que les fichiers sont uploadés:**
   - [ ] `api_proxy.php`
   - [ ] `.htaccess` (avec les règles de routage API)
   - [ ] `src/api/admin/*.php`
   - [ ] `src/includes/admin_auth.php`

4. **Tester:**
   - [ ] Connecte-toi en admin sur le site
   - [ ] Va sur `/public/index.php?page=dashboard_admin`
   - [ ] Vérifie que les erreurs 403 ont disparu

---

## 🎓 Apprentissage

### Le système d'authentification admin :

1. **Authentification (Login)**
   - L'utilisateur se connecte
   - `$_SESSION['user_id']` est défini
   - Un cookie `PHPSESSID` est créé

2. **Autorisation (Admin Check)**
   - Quand une API est appelée, elle vérifie `isAdmin()`
   - `isAdmin()` cherche dans la session et la base de données
   - Si l'utilisateur n'est pas admin → retour 403

3. **Session Persistence**
   - Le navigateur envoie automatiquement le cookie `PHPSESSID`
   - Le serveur retrouve la session correspondante
   - L'authentification est vérifiée

C'est par conception, et c'est bon pour la **sécurité**.

---

## 📞 Support

Si après avoir suivi ces étapes tu as toujours des erreurs 403 :

1. Visite: `http://localhost/moncoachscolaire/test_api_admin.html`
2. Note les statuts HTTP pour chaque API
3. Vérifie le cookie PHPSESSID dans les DevTools
4. Cherche les erreurs dans la console JavaScript (F12)

---

**Fin de la documentation**
