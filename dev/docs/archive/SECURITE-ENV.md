# Sécurité du Fichier .env.production

## 🔒 État Actuel de la Sécurité

### ✅ Protections Actives

1. **`.htaccessbak`** (à renommer en `.htaccess` en production) :
   - ✅ Bloque l'accès direct au fichier `.env.production`
   - ✅ Protection via `<FilesMatch>` et `RewriteRule`
   - ✅ Empêche l'affichage du contenu via HTTP

2. **`.gitignore`** :
   - ✅ `.env.production` est maintenant dans `.gitignore`
   - ✅ Empêche le commit accidentel dans Git

3. **Permissions serveur** :
   - Recommandé : `600` (lecture/écriture pour le propriétaire uniquement)
   - Alternative : `644` (lecture pour tous, écriture pour propriétaire)

### ⚠️ Points d'Attention

1. **`debug_env_loading.php`** :
   - ✅ **Sécurisé** : Vérifie maintenant que l'utilisateur est admin
   - ✅ Masque les valeurs sensibles (mots de passe, secrets)
   - ⚠️ **Recommandation** : Supprimer ce fichier en production ou le protéger davantage

2. **`api/admin/debug.php`** :
   - ✅ Ne montre que l'existence du fichier, pas le contenu
   - ✅ Accessible uniquement aux admins

---

## 🛡️ Mesures de Sécurité Recommandées

### 1. Permissions du Fichier

```bash
# Sur le serveur (SSH)
chmod 600 .env.production
chown www-data:www-data .env.production  # Adaptez selon votre serveur
```

### 2. Vérifier que `.htaccess` est Actif

En production, renommez `.htaccessbak` en `.htaccess` :

```bash
mv .htaccessbak .htaccess
```

### 3. Supprimer les Fichiers de Debug en Production

```bash
# Supprimer le fichier de debug (optionnel mais recommandé)
rm debug_env_loading.php
```

OU protéger l'accès via `.htaccess` :

```apache
<FilesMatch "debug_env_loading\.php">
  Order allow,deny
  Deny from all
</FilesMatch>
```

### 4. Vérifier que le Fichier n'est pas dans Git

```bash
# Vérifier que .env.production n'est pas tracké
git ls-files | grep .env.production

# Si présent, le retirer :
git rm --cached .env.production
```

---

## 🔍 Vérification de Sécurité

### Test 1 : Accès Direct au Fichier

Essayez d'accéder directement :
```
https://moncoachscolaire.fr/.env.production
```

**Résultat attendu** : Erreur 403 (Forbidden) ou 404 (Not Found)

### Test 2 : Vérifier les Permissions

```bash
ls -la .env.production
```

**Résultat attendu** : `-rw-------` (600) ou `-rw-r--r--` (644)

### Test 3 : Vérifier .gitignore

```bash
git check-ignore .env.production
```

**Résultat attendu** : `.env.production` (le fichier est ignoré)

---

## 📋 Checklist de Sécurité

- [x] `.env.production` dans `.gitignore`
- [x] Protection `.htaccess` configurée
- [x] `debug_env_loading.php` sécurisé (admin uniquement)
- [x] `api/admin/debug.php` ne montre pas le contenu
- [ ] Permissions fichier : `600` ou `644`
- [ ] `.htaccessbak` renommé en `.htaccess` en production
- [ ] Fichier non tracké dans Git
- [ ] Test d'accès direct effectué

---

## 🚨 En Cas de Compromission

Si le fichier `.env.production` a été exposé :

1. **Changer immédiatement** :
   - Mot de passe de la base de données
   - Tous les mots de passe admin
   - Toutes les clés API/secret

2. **Vérifier les logs** :
   - Logs d'accès Apache/Nginx
   - Logs d'erreur PHP
   - Logs de la base de données

3. **Renforcer la sécurité** :
   - Mettre à jour les permissions
   - Vérifier que `.htaccess` est actif
   - Supprimer les fichiers de debug

---

## 📝 Notes Importantes

- Le fichier `.env.production` contient des **informations sensibles** (mots de passe DB, clés API)
- **Ne jamais** commiter ce fichier dans Git
- **Toujours** utiliser `.htaccess` pour bloquer l'accès HTTP
- **Vérifier régulièrement** que les permissions sont correctes
- En **développement local**, le fichier peut être moins protégé, mais en **production**, la sécurité est critique

---

*Documentation de sécurité pour le fichier .env.production*
