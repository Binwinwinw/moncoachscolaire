# 🔍 Diagnostic Mascotte en Production

**Date** : 23 décembre 2025  
**Problème** : Les sprites et vidéos de la mascotte ne fonctionnent pas en production alors qu'ils fonctionnent en local.

---

## 📋 Causes Possibles

### 1. **Détection de `baseUrl` incorrecte**

**Symptôme** : Les chemins sont mal construits (ex: `/moncoachscolaire/assets/...` au lieu de `/assets/...`)

**Vérification** :
- Ouvrir la console du navigateur (F12)
- Chercher les logs : `🔧 BaseUrl détecté pour mascotte:`
- Vérifier que `baseUrl` est bien `''` (chaîne vide) en production

**Solution** : Le code utilise `window.baseUrl` défini dans `footer.php`. Vérifier que `$baseUrl` est bien vide en production.

---

### 2. **Chemins relatifs vs absolus**

**Symptôme** : Les fichiers ne sont pas trouvés (erreur 404)

**Chemins attendus en production** :
- Sprites : `/assets/img/coach/colibri-sprites/cartoon/colibri-neutre.png`
- Vidéos : `/assets/img/coach/optimized/cartoon_volant.mp4`

**Chemins attendus en local** :
- Sprites : `/moncoachscolaire/assets/img/coach/colibri-sprites/cartoon/colibri-neutre.png`
- Vidéos : `/moncoachscolaire/assets/img/coach/optimized/cartoon_volant.mp4`

---

### 3. **Fichiers manquants en production**

**Vérification** :
1. Vérifier que les dossiers existent :
   - `assets/img/coach/colibri-sprites/cartoon/`
   - `assets/img/coach/colibri-sprites/realiste/`
   - `assets/img/coach/optimized/`

2. Vérifier que les fichiers sont uploadés :
   - Sprites : `colibri-neutre.png`, `colibri-heureux.png`, etc.
   - Vidéos : `cartoon_volant.mp4`, `realiste_volant.mp4`, etc.

---

### 4. **Permissions fichiers**

**Symptôme** : Erreur 403 (Forbidden)

**Solution** :
```bash
# Vérifier les permissions
chmod 644 assets/img/coach/colibri-sprites/**/*.png
chmod 644 assets/img/coach/optimized/*.mp4
chmod 644 assets/img/coach/optimized/*.webm
```

---

### 5. **Case sensitivity (Linux)**

**Symptôme** : Erreur 404 sur Linux (production) mais fonctionne sur Windows (local)

**Cause** : Linux est case-sensitive, Windows ne l'est pas.

**Vérification** :
- Les noms de fichiers doivent correspondre exactement :
  - `colibri-sprites` (pas `Colibri-Sprites`)
  - `cartoon` (pas `Cartoon`)
  - `realiste` (pas `Realiste`)

---

### 6. **Configuration `.htaccess`**

**Symptôme** : Certains fichiers sont bloqués

**Vérification** : Vérifier que `.htaccess` n'interdit pas l'accès aux fichiers images/vidéos.

---

## 🔧 Diagnostic dans la Console

### Logs à vérifier

1. **BaseUrl détecté** :
```javascript
🔧 BaseUrl détecté pour mascotte: {
    baseUrl: '',  // Doit être '' en production
    windowBaseUrl: '',  // Doit être '' en production
    locationPathname: '/index.php',
    locationHref: 'https://moncoachscolaire.fr/index.php?page=dashboard',
    locationOrigin: 'https://moncoachscolaire.fr'
}
```

2. **Chargement sprite** :
```javascript
🖼️ Chargement sprite: {
    pose: 'neutre',
    folder: 'cartoon',
    baseUrl: '',
    basePath: '/assets',
    src: '/assets/img/coach/colibri-sprites/cartoon/colibri-neutre.png',
    fullUrl: 'https://moncoachscolaire.fr/assets/img/coach/colibri-sprites/cartoon/colibri-neutre.png'
}
```

3. **Erreur sprite** (si problème) :
```javascript
❌ Erreur chargement sprite: {
    src: '/assets/img/coach/colibri-sprites/cartoon/colibri-neutre.png',
    fullUrl: 'https://moncoachscolaire.fr/assets/img/coach/colibri-sprites/cartoon/colibri-neutre.png',
    baseUrl: '',
    pose: 'neutre',
    folder: 'cartoon',
    error: 'Image non trouvée ou chemin incorrect'
}
```

4. **Vidéo aléatoire** :
```javascript
🎬 Lecture vidéo aléatoire: {
    videoPath: '/assets/img/coach/optimized/cartoon_volant',
    fullUrl: 'https://moncoachscolaire.fr/assets/img/coach/optimized/cartoon_volant',
    baseUrl: '',
    randomIndex: 0,
    totalVideos: 3
}
```

---

## ✅ Checklist de Vérification

### 1. Vérifier `window.baseUrl` en production

**Dans la console du navigateur** :
```javascript
console.log('window.baseUrl:', window.baseUrl);
// Doit afficher : '' (chaîne vide) en production
```

**Si `window.baseUrl` est `undefined` ou a une valeur incorrecte** :
- Vérifier `footer.php` ligne 213
- Vérifier que `$baseUrl` est bien défini dans `config.php`

---

### 2. Tester les chemins directement

**Dans la console du navigateur** :
```javascript
// Tester un sprite
const img = new Image();
img.onload = () => console.log('✅ Sprite accessible');
img.onerror = () => console.error('❌ Sprite non accessible');
img.src = '/assets/img/coach/colibri-sprites/cartoon/colibri-neutre.png';

// Tester une vidéo
const video = document.createElement('video');
video.oncanplay = () => console.log('✅ Vidéo accessible');
video.onerror = () => console.error('❌ Vidéo non accessible');
video.src = '/assets/img/coach/optimized/cartoon_volant.mp4';
```

---

### 3. Vérifier les fichiers sur le serveur

**Via FTP/SSH** :
```bash
# Vérifier l'existence des dossiers
ls -la assets/img/coach/colibri-sprites/
ls -la assets/img/coach/colibri-sprites/cartoon/
ls -la assets/img/coach/colibri-sprites/realiste/
ls -la assets/img/coach/optimized/

# Vérifier les permissions
ls -l assets/img/coach/colibri-sprites/cartoon/*.png
ls -l assets/img/coach/optimized/*.mp4
```

---

### 4. Tester les URLs directement

**Dans le navigateur** :
- `https://moncoachscolaire.fr/assets/img/coach/colibri-sprites/cartoon/colibri-neutre.png`
- `https://moncoachscolaire.fr/assets/img/coach/optimized/cartoon_volant.mp4`

**Si erreur 404** : Les fichiers n'existent pas ou les chemins sont incorrects.

**Si erreur 403** : Problème de permissions.

---

## 🛠️ Solutions

### Solution 1 : Vérifier `window.baseUrl`

Si `window.baseUrl` n'est pas défini correctement, ajouter dans `footer.php` :

```php
<script>
    // Forcer baseUrl en production si non défini
    if (typeof window.baseUrl === 'undefined' || window.baseUrl === null) {
        // En production (domaine racine), baseUrl doit être ''
        window.baseUrl = '<?php echo isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>';
        console.log('🔧 BaseUrl forcé:', window.baseUrl);
    }
</script>
```

---

### Solution 2 : Utiliser des chemins absolus

Si les chemins relatifs ne fonctionnent pas, utiliser `window.location.origin` :

```javascript
const basePath = window.location.origin + (this.baseUrl || '') + '/assets';
```

---

### Solution 3 : Vérifier les fichiers

S'assurer que tous les fichiers sont uploadés :
- Sprites dans `assets/img/coach/colibri-sprites/cartoon/` et `realiste/`
- Vidéos dans `assets/img/coach/optimized/`

---

### Solution 4 : Vérifier `.htaccess`

S'assurer que `.htaccess` n'interdit pas l'accès aux fichiers :

```apache
# Autoriser l'accès aux images et vidéos
<FilesMatch "\.(png|jpg|jpeg|gif|mp4|webm|avif)$">
    Require all granted
</FilesMatch>
```

---

## 📊 Logs de Diagnostic

Le code JavaScript a été amélioré pour afficher des logs détaillés :

1. **BaseUrl détecté** : Affiche la détection du baseUrl
2. **Chargement sprite** : Affiche le chemin complet du sprite
3. **Erreur sprite** : Affiche les détails de l'erreur si le sprite ne charge pas
4. **Vidéo aléatoire** : Affiche le chemin complet de la vidéo
5. **Erreur vidéo** : Affiche les détails de l'erreur si la vidéo ne charge pas

**Action** : Ouvrir la console du navigateur (F12) et vérifier ces logs pour identifier le problème exact.

---

## 🎯 Prochaines Étapes

1. **Ouvrir la console** du navigateur en production
2. **Vérifier les logs** de la mascotte
3. **Tester les URLs** directement dans le navigateur
4. **Vérifier les fichiers** sur le serveur
5. **Corriger** selon les erreurs trouvées

---

**Document généré automatiquement**  
**Dernière mise à jour** : 23 décembre 2025
