# 🔧 DIAGNOSTIC - Problème "Les matières ne sont pas disponibles"

## ✅ Ce qui fonctionne

1. **Base de données** : 32 exercices pour 6ème
   - Français: 15
   - Mathématiques: 15
   - Histoire-Géo: 1
   - SVT: 1

2. **API Backend** : `/api/get_exercises.php` fonctionne
   ```
   Test: http://localhost/moncoachscolaire/api/get_exercises.php?action=subjects&level=6%C3%A8me
   Statut: 200 OK
   Retour: 4 matières (Français, Maths, Histoire-Géo, SVT)
   ```

3. **Règle htaccess** : `/api/*` redirige vers `/src/api/*`
   - En local: `RewriteRule ^api/(.*)$ /moncoachscolaire/src/api/$1`
   - En production: `RewriteRule ^api/(.*)$ api_proxy.php`

4. **JavaScript** : Erreur "body stream" corrigée
   - Avant: `response.text()` puis `response.json()` ❌
   - Après: `response.text()` puis `JSON.parse(text)` ✅

## ❌ Problème actuel

**Message d'erreur** : "Les matières ne sont pas disponibles pour ce niveau"

**Cause possible** : L'API retourne HTTP 404 quand appelée depuis JavaScript

## 🔍 Diagnostic étape par étape

### Étape 1 : Vérifier que la page charge correctement

Ouvrir dans le navigateur :
```
http://localhost/moncoachscolaire/public/index.php?page=college/6eme/exercices-6eme
```

**Vérifier dans F12 Console :**
- ✅ `window.baseUrl` est défini (devrait être `/moncoachscolaire`)
- ✅ Script `dynamic-exercises.js` chargé sans erreur
- ✅ Message "🎯 Initialisation du système d'exercices dynamique..."

### Étape 2 : Vérifier l'URL de l'API générée

**Dans la console F12, chercher :**
```
📡 Requête API: ...
```

**URL attendue :**
```
/moncoachscolaire/api/get_exercises.php?action=subjects&level=6ème
```

**Si l'URL est différente :** Problème de construction d'URL

### Étape 3 : Vérifier la réponse HTTP

**Dans F12 Network :**
1. Rafraîchir la page (F5)
2. Filtrer par "get_exercises"
3. Cliquer sur la requête
4. Regarder l'onglet "Response"

**Statuts possibles :**
- **200 OK** : API fonctionne → Problème de parsing JS
- **404 Not Found** : API non trouvée → Problème de routing ou baseUrl
- **500 Error** : Erreur serveur → Problème PHP dans l'API
- **403 Forbidden** : Accès refusé → Problème htaccess

### Étape 4 : Tests de comparaison

**Test 1** : API directe dans le navigateur
```
http://localhost/moncoachscolaire/api/get_exercises.php?action=subjects&level=6%C3%A8me
```
✅ Devrait afficher le JSON avec 4 matières

**Test 2** : Page de test simplifiée
```
http://localhost/moncoachscolaire/test_exercices_load.php
```
✅ Devrait afficher les variables de debug ET charger les matières

**Test 3** : Page réelle
```
http://localhost/moncoachscolaire/public/index.php?page=college/6eme/exercices-6eme
```
❌ Affiche "Les matières ne sont pas disponibles"

## 🛠️ Solutions possibles

### Solution 1 : Forcer le chemin absolu de l'API

Dans `src/pages/college/6eme/exercices-6eme.php` ligne 589 :

**Avant :**
```javascript
apiEndpoint: baseUrl ? baseUrl + '/api/get_exercises.php' : 'api/get_exercises.php'
```

**Après :**
```javascript
apiEndpoint: baseUrl + '/src/api/get_exercises.php'
```

⚠️ **Attention** : Ceci bypasse le routing htaccess

### Solution 2 : Vérifier que htaccess fonctionne

Créer un fichier test `api/test.php` :
```php
<?php
echo json_encode(['test' => 'OK', 'path' => __FILE__]);
```

Tester :
```
http://localhost/moncoachscolaire/api/test.php
```

Devrait afficher :
```json
{"test":"OK","path":"D:\\Hostinger\\public_html\\moncoachscolaire\\src\\api\\test.php"}
```

### Solution 3 : Ajouter un fallback dans dynamic-exercises.js

Modifier `public/assets/js/dynamic-exercises.js` pour essayer plusieurs endpoints :

```javascript
async loadSubjects() {
    const endpoints = [
        `${this.options.apiEndpoint}?action=subjects&level=${encodeURIComponent(this.options.level)}`,
        `/moncoachscolaire/src/api/get_exercises.php?action=subjects&level=${encodeURIComponent(this.options.level)}`
    ];
    
    for (const url of endpoints) {
        try {
            console.log('📡 Essai:', url);
            const response = await fetch(url);
            if (response.ok) {
                // Traiter la réponse
                break;
            }
        } catch (e) {
            console.error('Échec:', url, e);
        }
    }
}
```

## 📝 Actions immédiates

1. **Ouvrir la vraie page d'exercices** dans le navigateur
2. **Ouvrir F12 Console** et chercher les logs
3. **Noter l'URL exacte** de la requête API qui échoue
4. **Vérifier le statut HTTP** dans l'onglet Network
5. **Me communiquer** ces informations

## 🎯 Information recherchée

Pour résoudre le problème, j'ai besoin de savoir :

1. **Quelle URL l'API est-elle appelée** ?
   - Exemple: `/moncoachscolaire/api/...` ou `/api/...` ou autre ?

2. **Quel statut HTTP est retourné** ?
   - 200, 404, 500, 403 ?

3. **Le message dans la console F12** ?
   - "📡 Requête API: ..." 
   - "❌ Erreur lors du chargement: ..."

4. **La réponse brute du serveur** (dans Network tab) ?
   - JSON, HTML d'erreur, ou vide ?
