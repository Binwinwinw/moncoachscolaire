# 📚 Guide: Structure de Projet Web PHP - Version Publique

Guide de bonnes pratiques architecture PHP. Version sécurisée (sans détails sensibles).

---

## 🎯 Principes Fondamentaux

### 1. **Séparation Public / Privé**
```
public/          ← Accessible via navigateur
  ├── index.php  ← Routeur unique
  └── assets/    ← CSS, JS, images

src/             ← Code privé (jamais accessible directement)
  ├── api/       ← Logique métier
  ├── config/    ← Configuration
  └── database/  ← Connexions BD
```

**Pourquoi:** Protéger le code source et la logique métier.

### 2. **Routeur Unique**
```php
// public/index.php
<?php
// Tous les appels passent par un seul fichier
// Permet de centraliser la sécurité et le logging

if (strpos($path, '/api/') === 0) {
    // API calls
} else {
    // Page calls
}
?>
```

**Avantage:** Contrôle centralisé, pas de fichiers `.php` directs accessibles.

### 3. **Dossiers Organisés par Domaine**

```
src/
├── api/         → Endpoints et logique
├── pages/       → Views HTML
├── config/      → Paramètres et constantes
├── includes/    → Fonctions réutilisables
└── models/      → Classes métier
```

---

## 📂 Structure Recommandée

```
projet/
├── public/              # Point d'entrée PUBLIC
│   ├── index.php       # Routeur principal
│   └── assets/
│       ├── css/
│       ├── js/
│       └── img/
│
├── src/                # Code PRIVÉ
│   ├── api/            # Endpoints
│   ├── pages/          # Pages dynamiques
│   ├── config/         # Configuration
│   ├── includes/       # Helpers
│   ├── database/       # Connexions
│   └── models/         # Classes
│
├── docs/               # Documentation PUBLIC
│   ├── API.md
│   ├── INSTALLATION.md
│   └── FEATURES.md
│
├── README.md           # Info projet
├── composer.json       # Dépendances
└── .gitignore          # Fichiers ignorés
```

---

## 🔐 Points de Sécurité Critiques

### ✅ À Faire
1. **Mettre un seul point d'entrée** (`public/index.php`)
2. **Valider TOUTES les entrées utilisateur**
3. **Utiliser des requêtes paramétrées** (PDO prepared statements)
4. **Logger les erreurs** sans les exposer
5. **Implémenter l'authentification** proprement
6. **Versionner le code** avec git

### ❌ À Éviter
1. **Exposer les erreurs PHP** directement
2. **Inclure des fichiers basés sur l'input utilisateur**
3. **Stocker les secrets** en dur dans le code
4. **Laisser des fichiers `.php` publics** sans protection
5. **Déboguer en production**
6. **Committer les fichiers sensibles**

---

## 🚀 Démarrage Rapide

### 1. Structure minimale
```bash
mkdir -p {public/assets,src/{api,config,pages,database}}
touch public/index.php
touch composer.json README.md
```

### 2. Routeur basique
```php
// public/index.php
<?php
// Charger configuration
require_once __DIR__ . '/../src/config/bootstrap.php';

// Parser l'URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Router
if (strpos($path, '/api/') === 0) {
    require_once __DIR__ . '/../src/api/router.php';
} else {
    http_response_code(404);
    echo "Page not found";
}
?>
```

### 3. Configuration bootstrap
```php
// src/config/bootstrap.php
<?php
// Démarrer la session
session_start();

// Charger les helpers
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../includes/helpers.php';

// Constantes
define('BASE_URL', 'https://monprojet.fr');
define('API_VERSION', 'v1');
?>
```

---

## 📝 Conventions de Nommage

### Fichiers
```
Routes:        /api/{resource}           → src/api/{resource}.php
Pages:         /mon/projet/dapplication   → src/pages/dapplication.php
Classes:       src/models/{Model}.php    → Users, Exercises, Courses
```

### Code
```
Fonctions:     camelCase()               → getUserById()
Variables:     $camelCase                → $userId, $userName
Constantes:    UPPER_SNAKE_CASE          → MAX_UPLOAD_SIZE, API_VERSION
Classes:       PascalCase                → User, ExerciseManager
```

---

## 🔍 Anti-patterns Courants

### ❌ Mauvais: Inclusion directe
```php
// public/page.php
<?php
include $_GET['page'] . '.php';  // DANGER! SQL injection possible
?>
```

### ✅ Bon: Routeur centralisé
```php
// public/index.php
<?php
$allowed = ['home', 'about', 'contact'];
$page = $_GET['page'] ?? 'home';

if (in_array($page, $allowed)) {
    include __DIR__ . "/../src/pages/$page.php";
}
?>
```

### ❌ Mauvais: Secrets en dur
```php
$password = 'MySecretPassword123';
```

### ✅ Bon: Variables d'environnement
```php
$password = getenv('DB_PASSWORD');
```

---

## 📊 Flux d'une Requête Typique

```
1. Utilisateur → http://example.com/api/exercises
2. Serveur    → Appelle public/index.php
3. Routeur    → Vérifie le chemin (/api/...)
4. Handler    → Charge src/api/exercises.php
5. Logique    → Requête en BD via src/database/
6. Réponse    → Retourne JSON
7. Browser    → Affiche les données
```

**Fichiers impliqués:**
- `public/index.php` - Routeur
- `src/api/exercises.php` - Logique
- `src/database/connection.php` - BD
- `src/includes/helpers.php` - Utilitaires

---

## ✅ Checklist: Nouveau Projet

- [ ] Créer structure `/public/` et `/src/`
- [ ] Implémenter `public/index.php` (routeur)
- [ ] Créer `src/config/bootstrap.php`
- [ ] Ajouter authentification de base
- [ ] Valider les entrées utilisateur
- [ ] Configurer `.gitignore`
- [ ] Écrire `README.md`
- [ ] Documenter les endpoints API
- [ ] Tester le routage

---

## 📚 Ressources

- **PHP Best Practices:** https://www.php-fig.org/
- **OWASP:** https://owasp.org/ (Sécurité web)
- **Laravel Structure:** https://laravel.com/docs/structure
- **Symfony Docs:** https://symfony.com/doc/current/best_practices.html

---

## 📞 Aide & Support

Pour des questions sur:
- **Architecture:** Consulter un Senior Developer
- **Sécurité:** Faire une audit de sécurité
- **Performance:** Profiler et optimiser
- **Déploiement:** Suivre les guidelines DevOps

---

**Dernière mise à jour:** 1 janvier 2026  
**Version:** 1.0 - Public safe
