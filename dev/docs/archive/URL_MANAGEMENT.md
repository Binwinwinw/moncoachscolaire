# Gestion des URLs - Système Hybride Local/Production

## 📋 Vue d'ensemble

L'application détecte automatiquement l'environnement (local vs production) et génère les URLs appropriées pour tous les liens, assets (CSS, JS, images) et API.

## 🔧 Fonctions disponibles

### 1. `detectBaseUrl()`
Détecte automatiquement la base URL selon l'environnement :
- **Local** (localhost) : `/moncoachscolaire` si dans un sous-dossier, sinon `''`
- **Production** : `''` (site servi à la racine)

### 2. `site_url($page, $query = [])`
Génère une URL de page via le routeur :
```php
site_url('login')                    // /index.php?page=login
site_url('college/6eme/exercices-6eme')  // /index.php?page=college/6eme/exercices-6eme
site_url('cours', ['niveau' => 'seconde'])  // /index.php?page=cours&niveau=seconde
```

### 3. `asset_url($path)`
Génère une URL pour les assets (CSS, JS, images) :
```php
asset_url('assets/css/style.css')    // /assets/css/style.css (ou /moncoachscolaire/assets/css/style.css en local)
asset_url('assets/js/app.js')        // /assets/js/app.js
asset_url('assets/img/logo.png')     // /assets/img/logo.png
```

### 4. `absolute_url($path)`
Génère une URL absolue complète (avec protocole et domaine) :
```php
absolute_url('login')  // https://moncoachscolaire.fr/index.php?page=login
```

## 🌍 Détection d'environnement

L'environnement est détecté automatiquement dans `config.php` :

1. **Vérification de `APP_ENV`** dans les variables d'environnement
2. **Détection automatique** basée sur `HTTP_HOST` :
   - Si `localhost` ou `127.0.0.1` → **local**
   - Sinon → **production**

## 📝 Utilisation dans les fichiers

### PHP (Backend)

**Pour les liens de pages :**
```php
<a href="<?php echo site_url('login'); ?>">Se connecter</a>
<a href="<?php echo site_url('college/6eme/exercices-6eme'); ?>">Exercices 6ème</a>
```

**Pour les assets (CSS, JS, images) :**
```php
<link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
<script src="<?php echo asset_url('assets/js/app.js'); ?>"></script>
<img src="<?php echo asset_url('assets/img/logo.png'); ?>" alt="Logo">
```

**Pour les formulaires :**
```php
<form action="<?php echo site_url('register'); ?>" method="POST">
```

### JavaScript (Frontend)

La variable `window.baseUrl` est automatiquement définie dans le `<head>` de chaque page :

```javascript
// Utiliser window.baseUrl pour construire des URLs
const apiUrl = window.baseUrl + '/api/users.php';
const imageUrl = window.baseUrl + '/assets/img/photo.jpg';

// Ou utiliser directement dans fetch
fetch(window.baseUrl + '/api/data.php')
    .then(response => response.json())
    .then(data => console.log(data));
```

## 🔄 Migration des liens existants

### Avant (❌ à éviter)
```php
<a href="/index.php?page=login">Se connecter</a>
<link rel="stylesheet" href="/assets/css/style.css">
<script src="/assets/js/app.js"></script>
```

### Après (✅ recommandé)
```php
<a href="<?php echo site_url('login'); ?>">Se connecter</a>
<link rel="stylesheet" href="<?php echo asset_url('assets/css/style.css'); ?>">
<script src="<?php echo asset_url('assets/js/app.js'); ?>"></script>
```

## 📂 Fichiers modifiés

Les fonctions sont définies dans :
- `config.php` : Fonctions principales (`detectBaseUrl()`, `site_url()`, `asset_url()`, `absolute_url()`)
- `site_boot.php` : Redéfinition de `site_url()` pour compatibilité

Les fichiers utilisant ces fonctions :
- `index.php` : Génération des liens CSS et exposition de `window.baseUrl`
- `topbar.php` : Liens de navigation
- `footer.php` : Assets de la mascotte
- `dashboard_admin.php`, `dashboard_parent.php`, `dashboard.php` : Assets CSS/JS

## ⚙️ Configuration

### Variables d'environnement

Dans `.env` (local) ou `.env.production` (production) :
```env
APP_ENV=local          # ou 'production'
DB_HOST=127.0.0.1      # local
DB_HOST=localhost      # production
```

### Détection automatique

Si `APP_ENV` n'est pas défini, l'application détecte automatiquement :
- `HTTP_HOST` contient `localhost` ou `127.0.0.1` → **local**
- Sinon → **production**

## 🎯 Bonnes pratiques

1. **Toujours utiliser `site_url()`** pour les liens de pages
2. **Toujours utiliser `asset_url()`** pour les assets (CSS, JS, images)
3. **Ne jamais hardcoder** les chemins avec `/moncoachscolaire` ou `/`
4. **Utiliser `window.baseUrl`** dans JavaScript pour les appels API
5. **Tester en local ET en production** pour vérifier que les URLs fonctionnent

## 🐛 Dépannage

### Les liens ne fonctionnent pas en production
- Vérifier que `detectBaseUrl()` retourne bien `''` en production
- Vérifier que `APP_ENV=production` est défini dans `.env.production`

### Les assets ne se chargent pas
- Vérifier que `asset_url()` est utilisé partout
- Vérifier que `window.baseUrl` est défini dans le `<head>`

### Les liens ne fonctionnent pas en local
- Vérifier que le script est bien dans `/moncoachscolaire/`
- Vérifier que `detectBaseUrl()` détecte correctement le sous-dossier
