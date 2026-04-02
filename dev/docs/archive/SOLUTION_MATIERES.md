# ✅ SOLUTION - "Les matières ne sont pas disponibles"

## 🐛 Problème identifié

**Symptôme** : Message "Les matières ne sont pas disponibles pour ce niveau" sur toutes les pages d'exercices.

**Cause racine** : 
Les pages d'exercices redéfinissaient `window.baseUrl` **après** qu'`index.php` l'ait déjà défini correctement.

### Flux du problème

1. **index.php** (ligne 491) définit `window.baseUrl` correctement dans le `<head>` :
   ```javascript
   <script>window.baseUrl = "/moncoachscolaire";</script>
   ```

2. Les pages `exercices-*.php` **écrasaient** cette valeur :
   ```javascript
   window.baseUrl = '<?php echo isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>';
   ```

3. Si `$baseUrl` n'était pas disponible dans le scope de la page incluse, `window.baseUrl` devenait `''` (vide).

4. L'API endpoint calculé devenait donc :
   ```javascript
   apiEndpoint: '' ? '' + '/api/get_exercises.php' : 'api/get_exercises.php'
   // Résultat: 'api/get_exercises.php' (chemin relatif incorrect)
   ```

5. Le fetch appelait `/public/api/get_exercises.php` au lieu de `/moncoachscolaire/api/get_exercises.php` → **404**

6. JavaScript affichait : "Les matières ne sont pas disponibles pour ce niveau"

## ✅ Correction appliquée

### Fichiers modifiés (7 fichiers)

1. `src/pages/college/6eme/exercices-6eme.php`
2. `src/pages/college/5eme/exercices-5eme.php`
3. `src/pages/college/4eme/exercices-4eme.php`
4. `src/pages/college/3eme/exercices-3eme.php`
5. `src/pages/lycee/seconde/exercices-seconde.php`
6. `src/pages/lycee/premiere/exercices-premiere.php`
7. `src/pages/lycee/terminale/exercices-terminale.php`

### Modification 1 : Protection de window.baseUrl

**Avant** :
```javascript
<script>
    // Définir baseUrl pour le JavaScript
    window.baseUrl = '<?php echo isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>';
```

**Après** :
```javascript
<script>
    // S'assurer que baseUrl est défini (ne pas écraser s'il existe déjà depuis index.php)
    if (typeof window.baseUrl === 'undefined') {
        window.baseUrl = '<?php echo isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>';
    }
    console.log('🔧 baseUrl détecté:', window.baseUrl);
```

### Modification 2 : Log de l'API endpoint

**Avant** :
```javascript
const baseUrl = window.baseUrl || '';
try {
    window.dynamicExerciseSystem = new DynamicExerciseSystem({
        containerSelector: '[data-dynamic-exercises]',
        level: '6ème',
        apiEndpoint: baseUrl ? baseUrl + '/api/get_exercises.php' : 'api/get_exercises.php'
    });
```

**Après** :
```javascript
const baseUrl = window.baseUrl || '';
const apiEndpoint = baseUrl ? baseUrl + '/api/get_exercises.php' : '/api/get_exercises.php';
console.log('📡 API Endpoint:', apiEndpoint);

try {
    window.dynamicExerciseSystem = new DynamicExerciseSystem({
        containerSelector: '[data-dynamic-exercises]',
        level: '6ème',
        apiEndpoint: apiEndpoint
    });
```

**Améliorations** :
- ✅ Vérification `typeof window.baseUrl` avant écrasement
- ✅ Log de la valeur détectée pour debugging
- ✅ Log de l'API endpoint calculé
- ✅ Fallback sur `/api/get_exercises.php` (chemin absolu) au lieu de `api/get_exercises.php` (relatif)

## 🧪 Test de validation

### Test 1 : Vérifier dans la console F12

Ouvrir une page d'exercices et regarder la console :

```
🔧 baseUrl détecté: /moncoachscolaire
🎯 Initialisation du système d'exercices dynamique...
📡 API Endpoint: /moncoachscolaire/api/get_exercises.php
📡 Requête API: /moncoachscolaire/api/get_exercises.php?action=subjects&level=6ème
📚 Niveau utilisé: 6ème
📦 Réponse API: {success: true, level: "6ème", subjects: Array(4)}
✅ 4 matière(s) trouvée(s)
✅ Système initialisé avec succès
```

### Test 2 : Vérifier dans l'onglet Network

1. Ouvrir F12 > Network
2. Filtrer par "get_exercises"
3. Rafraîchir la page
4. Vérifier :
   - **URL** : `/moncoachscolaire/api/get_exercises.php?action=subjects&level=6ème`
   - **Statut** : `200 OK`
   - **Réponse** : JSON avec 4 matières

### Test 3 : Vérifier visuellement

Les boutons de matières doivent s'afficher :
- 📚 Français (15 exercices)
- 🧮 Mathématiques (15 exercices)
- 🗺️ Histoire-Géographie (1 exercice)
- 🔬 SVT (1 exercice)

## 📋 URLs de test

### Local (XAMPP)
```
http://localhost/moncoachscolaire/public/index.php?page=college/6eme/exercices-6eme
http://localhost/moncoachscolaire/public/index.php?page=college/5eme/exercices-5eme
http://localhost/moncoachscolaire/public/index.php?page=college/4eme/exercices-4eme
http://localhost/moncoachscolaire/public/index.php?page=college/3eme/exercices-3eme
http://localhost/moncoachscolaire/public/index.php?page=lycee/seconde/exercices-seconde
http://localhost/moncoachscolaire/public/index.php?page=lycee/premiere/exercices-premiere
http://localhost/moncoachscolaire/public/index.php?page=lycee/terminale/exercices-terminale
```

### Production (Hostinger)
```
https://moncoachscolaire.fr/public/index.php?page=college/6eme/exercices-6eme
(etc.)
```

## 🚀 Déploiement en production

Les fichiers modifiés doivent être uploadés via SFTP/FTP :

```
src/pages/college/
  ├── 6eme/exercices-6eme.php
  ├── 5eme/exercices-5eme.php
  ├── 4eme/exercices-4eme.php
  └── 3eme/exercices-3eme.php

src/pages/lycee/
  ├── seconde/exercices-seconde.php
  ├── premiere/exercices-premiere.php
  └── terminale/exercices-terminale.php
```

**Note** : Aucune modification de base de données nécessaire.

## 🔗 Problèmes résolus

1. ✅ **Dashboard admin 403** → Expliqué (nécessite authentification)
2. ✅ **Cours manquants** → Tables créées + 10 cours insérés
3. ✅ **JavaScript body stream** → Lecture unique avec `text()` puis `JSON.parse()`
4. ✅ **Matières non disponibles** → Protection de `window.baseUrl`

## 📝 Prochaines étapes

1. **Tester toutes les pages d'exercices** (6ème à Terminale)
2. **Upload en production** via SFTP
3. **Vérifier en production** que l'API fonctionne avec `api_proxy.php`
4. **Créer plus d'exercices** dans la base de données pour enrichir le contenu

## 💡 Leçons apprises

**Problème de scope** : Les variables PHP définies dans le scope global ne sont pas toujours disponibles dans les fichiers inclus via `include`. 

**Solution** : 
- Utiliser `$GLOBALS['baseUrl']` dans les pages incluses
- OU laisser `index.php` définir les variables globales JavaScript et ne pas les redéfinir
- OU utiliser des fonctions helper comme `detectBaseUrl()` au lieu de variables

**Meilleure pratique** : 
```php
// Dans les pages incluses
$baseUrl = isset($GLOBALS['baseUrl']) ? $GLOBALS['baseUrl'] : (function_exists('detectBaseUrl') ? detectBaseUrl() : '');
```

Ou en JavaScript :
```javascript
// Ne jamais écraser une variable globale si elle existe déjà
if (typeof window.baseUrl === 'undefined') {
    window.baseUrl = '...';
}
```
