# 📋 Template: Structure de Projet Web PHP Professionnel

**⚠️ DOCUMENT CONFIDENTIEL - NE PAS PARTAGER PUBLIQUEMENT**

Guide basé sur expérience pratique. À adapter selon les besoins du projet.

> **Avertissement Sécurité:** Ce document décrit la structure complète incluant les points sensibles (fichiers `.env`, scripts de maintenance, tests de sécurité, etc.). Ne pas exposer cette documentation publiquement ni la committer dans un repo public, car elle fournirait un blueprint d'attaque.

---

## 🎯 Architecture Globale

```
projet/
├── public/                    # Point d'entrée public (serveur web)
│   ├── index.php             # Routeur principal
│   └── assets/
│       ├── css/
│       ├── js/
│       └── img/
│
├── src/                       # Code source métier (privé)
│   ├── api/                  # Endpoints API
│   ├── config/               # Configuration runtime
│   ├── includes/             # Fonctions/helpers partagées
│   ├── pages/                # Pages dynamiques
│   ├── database/             # Connexion BD
│   └── models/               # Classes métier
│
├── tests/                     # Tests et vérifications
│   ├── test_*.php            # Tests fonctionnels
│   ├── verify_*.php          # Vérifications post-exécution
│   └── debug_*.php           # Scripts debug
│
├── tools/                     # Utilitaires et scripts CLI
│   ├── import_*.php          # Scripts d'importation données
│   ├── migrate_*.php         # Scripts de migration
│   ├── normalize_*.php       # Nettoyage/normalisation
│   ├── fix_*.php             # Corrections/patches
│   └── analyze_*.php         # Analyse et diagnostic
│
├── docs/                      # Documentation
│   ├── *.md                  # Guides et explications
│   ├── STRUCTURE_PROJET.md   # Ce fichier
│   ├── STRATEGIE_*.md        # Stratégies système
│   ├── RESOLUTION_*.md       # Résolutions incidents
│   └── ANALYSE_*.md          # Analyses détaillées
│
├── db/                        # Schémas et migrations BD
│   ├── *.sql                 # Schémas, migrations
│   ├── connection.php        # Wrapper connexion
│   └── backups/              # Sauvegardes
│
├── backups/                   # Sauvegardes projet
├── vendor/                    # Dépendances Composer
├── node_modules/              # Dépendances npm
│
├── .env                       # Config locale (git-ignoré)
├── .env.production            # Config production (git-ignoré)
├── .gitignore                 # Fichiers à ignorer
├── .htaccess                  # Config Apache
├── composer.json              # Dépendances PHP
├── package.json               # Dépendances JavaScript
├── phpunit.xml                # Config tests
├── README.md                  # Infos projet
├── DOCUMENTATION.md           # Docs système
└── index.php                  # Routeur racine (optionnel)
```

---

## 📂 Détail des Dossiers

### 🌐 `/public/`
**Rôle:** Point d'entrée public, accessible par le navigateur.

**Structure recommandée:**
```
public/
├── index.php              # Routeur principal
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── theme.css
│   ├── js/
│   │   ├── app.js
│   │   └── vendor/        # Librairies externes
│   └── img/
│       ├── icons/
│       ├── logos/
│       └── media/
└── (autres fichiers publics)
```

**Bonnes pratiques:**
- ✅ Seul dossier accessible directement
- ✅ Utiliser un routeur (index.php) plutôt que .htaccess
- ✅ Minimiser la logique dans index.php
- ✅ Déléguer au `/src/`

### 🔒 `/src/`
**Rôle:** Code applicatif privé, logique métier.

**Structure par domaine:**
```
src/
├── api/
│   ├── exercises.php        # GET /api/exercises
│   ├── courses.php          # GET /api/courses
│   ├── admin/
│   │   ├── exercise_quality.php
│   │   └── stats.php
│   └── router.php           # Routeur API
│
├── config/
│   ├── constants.php        # Constantes app
│   ├── settings.php         # Paramètres
│   └── paths.php            # Chemins de base
│
├── includes/
│   ├── helpers.php          # Fonctions utilitaires
│   ├── functions.php        # Fonctions métier
│   ├── admin_auth.php       # Auth/autorisation
│   └── logger.php           # Logging
│
├── pages/
│   ├── college/
│   │   ├── 6eme/
│   │   │   └── exercices-6eme.php
│   │   └── ...
│   ├── lycee/
│   │   ├── seconde/
│   │   └── ...
│   └── admin/
│
├── database/
│   └── connection.php       # PDO connexion
│
└── models/
    ├── User.php
    ├── Exercise.php
    └── Course.php
```

**Conventions:**
- ✅ Nommer les fichiers par fonctionnalité (exercises.php, courses.php)
- ✅ Un namespace par domaine logique
- ✅ Pas de logique métier dans public/index.php
- ✅ Utiliser des classes pour les models

### 🧪 `/tests/`
**Rôle:** Vérification et validation, tests de développement.

**Fichiers:**
```
tests/
├── test_*.php             # Tests fonctionnels
├── verify_*.php           # Vérifications post-exécution
├── debug_*.php            # Scripts de debug
├── ConfigurationTest.php  # PHPUnit tests
└── ExerciseTest.php       # PHPUnit tests
```

**Convention de nommage:**
- `test_api_exercises.php` - Tester l'API /api/exercises
- `test_auth.php` - Tester l'authentification
- `verify_final_state.php` - Vérifier après changement majeur
- `debug_routes.php` - Déboguer le routeur

**Bonnes pratiques:**
- ✅ Créer des fichiers de test pour chaque changement
- ✅ Les tester en local avant production
- ✅ Les garder pour la documentation
- ✅ Nettoyer les tests obsolètes

### 🛠️ `/tools/`
**Rôle:** Scripts utilitaires CLI, gestion des données, maintenance.

**Par catégorie:**
```
tools/
├── import_*.php           # Import/restauration données
│   ├── import_487_exercises.php
│   ├── import_courses_exercises.php
│   └── import_exercises_from_json.php
│
├── migrate_*.php          # Migrations BD
│   ├── migrate_roles_to_enum.php
│   └── migrate_to_production.php
│
├── normalize_*.php        # Nettoyage données
│   ├── normalize_exercise_levels.php
│   └── sanitize_exercises.php
│
├── fix_*.php              # Corrections/patches
│   ├── fix_database_schema.php
│   ├── fix_is_active.php
│   └── fix_exercises_categorization.php
│
├── analyze_*.php          # Analyse/diagnostic
│   ├── analyze_roles.php
│   ├── analyze_database.php
│   └── check_exercises_counts.php
│
└── create_*.php           # Création ressources
    ├── create_courses_tables.php
    └── create_admin_user.php
```

**Usage:**
```bash
# Import
php tools/import_487_exercises.php --dry-run
php tools/import_487_exercises.php --update --force

# Migration
php tools/migrate_roles_to_enum.php

# Normalisation
php tools/normalize_exercise_levels.php

# Analyse
php tools/analyze_database.php
```

**Bonnes pratiques:**
- ✅ Supporter `--dry-run` pour vérifier avant
- ✅ Supporter `--force` pour ignorer avertissements
- ✅ Afficher des stats avant/après
- ✅ Logguer les changements
- ✅ Permettre l'annulation (transactions BD)

### 📚 `/docs/`
**Rôle:** Documentation technique, guides, analyses.

**Organisation:**
```
docs/
├── STRUCTURE_PROJET.md        # This file
├── STRATEGIE_*.md             # Stratégies système
├── RESOLUTION_*.md            # Résolutions incidents
├── ANALYSE_*.md               # Analyses détaillées
├── GUIDE_*.md                 # Guides d'utilisation
│
└── (sous-dossiers par domaine)
    ├── api/
    ├── database/
    ├── security/
    └── exercises/
```

**Conventions de nommage:**
- `STRATEGIE_ROLES.md` - Stratégie du système de rôles
- `RESOLUTION_487_EXERCICES.md` - Résolution du problème 487 exercices
- `ANALYSE_ROLES_COMPLETE.md` - Analyse approfondie
- `GUIDE_SOURCES_EXERCICES.md` - Guide utilisateur

**Bonnes pratiques:**
- ✅ Créer un `.md` pour chaque changement majeur
- ✅ Documenter les décisions architecturales
- ✅ Inclure les étapes de reproduction
- ✅ Mettre à jour après résolution

### 🗄️ `/db/`
**Rôle:** Schémas, migrations, connexions BD.

**Fichiers essentiels:**
```
db/
├── connection.php              # Wrapper PDO
├── mysql_schema.sql            # Schéma principal
├── mysql_schema_extended.sql   # Extensions
│
├── backups/
│   ├── schema_2025-01-01.sql
│   └── data_2025-01-01.sql
│
└── migrations/
    ├── 001_create_users_table.sql
    └── 002_add_is_active_column.sql
```

**connection.php pattern:**
```php
<?php
// Charger config
$envDbHost = getenv('DB_HOST');
$envDbName = getenv('DB_DATABASE');
$envDbUser = getenv('DB_USERNAME');
$envDbPass = getenv('DB_PASSWORD');

// Créer PDO
$pdo = new PDO(
    "mysql:host=$envDbHost;dbname=$envDbName",
    $envDbUser,
    $envDbPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
?>
```

---

## ⚙️ Fichiers de Configuration

### `.env` (Local Development)
```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=localhost
DB_DATABASE=moncoachscolaire
DB_USERNAME=root
DB_PASSWORD=root
API_URL=http://localhost/moncoachscolaire
```

**À ignorer:** `✅ Jamais committer en git`

### `.env.production` (Production)
```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=db.moncoachscolaire.fr
DB_DATABASE=prod_db
DB_USERNAME=prod_user
DB_PASSWORD=***secure***
API_URL=https://moncoachscolaire.fr
```

**À ignorer:** `✅ Jamais committer en git`

---

## 🚨 LE `.gitignore`: FONDAMENTAL POUR LA SÉCURITÉ

### Qu'est-ce que `.gitignore`?

**Rôle:** Dire à Git **QUELS FICHIERS NE PAS ENVOYER** lors d'un `git push`.

```
git add .          ← Prépare TOUT
git commit         ← Enregistre TOUT
git push           ← SAUF ce qui est dans .gitignore
                      ↓
                   Fichiers ignorés restent locaux
```

### Pourquoi C'est Critique?

```
SANS .gitignore:
  git push
  ↓
  .env.production (secrets) → GitHub ❌
  tools/ (scripts internes) → GitHub ❌
  node_modules/ (100 MB) → GitHub ❌
  
AVEC .gitignore correct:
  git push
  ↓
  Seulement le code "safe" → GitHub ✅
  Secrets restent locaux ✅
```

### Exemple: Le Moment Critique

```
Tu crées .env.production avec secrets:
  DB_PASSWORD=root123
  API_KEY=sk-1234567890

Scenario 1: SANS .gitignore
  git add .
  git commit -m "Add config"
  git push           ← [Publish branch]
  ↓
  .env.production uploadsé sur GitHub
  TOUT LE MONDE voit: DB_PASSWORD=root123 ❌❌❌

Scenario 2: AVEC .gitignore
  (ajouter ".env.production" au .gitignore d'abord)
  git add .
  git commit -m "Add config"
  git push           ← [Publish branch]
  ↓
  .env.production IGNORÉ, ne monte pas
  Reste seulement sur ton disque ✅
```

### `.gitignore` Complet

```ignore
# ═══════════════════════════════════════════
# 🔐 SÉCURITÉ - FICHIERS SENSIBLES (CRITIQUE)
# ═══════════════════════════════════════════

# Variables d'environnement (SECRETS!)
.env
.env.production
.env.local
.env.*.local
.env.development
.env.testing

# Fichiers sensibles
.aws/
.ssh/
.htpasswd
private_key
id_rsa
*.pem

# ═════════════════════════════════════════
# 🛠️  OUTILS DE DÉVELOPPEMENT (À IGNORER)
# ═════════════════════════════════════════

# Scripts d'administration/maintenance (optionnel)
tools/
tests/

# Python Virtual Environment
.venv/
venv/
env/
ENV/
__pycache__/
*.pyc
*.pyo
*.pyd
.Python
pip-log.txt

# ═════════════════════════════════════════
# 📦 DÉPENDANCES (PEUVENT ÊTRE RÉGÉNÉRÉES)
# ═════════════════════════════════════════

# Composer (PHP)
vendor/
composer.lock

# NPM (JavaScript)
node_modules/
package-lock.json
npm-debug.log
yarn-error.log

# ═════════════════════════════════════════
# 🔧 IDE & ÉDITEURS (PERSONNEL)
# ═════════════════════════════════════════

.vscode/
.idea/
*.swp
*.swo
*~
.DS_Store
Thumbs.db
.sublime-project
.sublime-workspace

# ═════════════════════════════════════════
# 📊 CACHE & BUILD (GÉNÉRÉ)
# ═════════════════════════════════════════

.phpunit.cache/
.phpunit.result.cache
.cache/
*.log
.coverage
.coverage.*
htmlcov/
dist/
build/

# ═════════════════════════════════════════
# 💾 DONNÉES SENSIBLES (OPTIONNEL)
# ═════════════════════════════════════════

# Décommenter si besoin:
# backups/
# db/*.sql
# uploads/
# temp/
```

### Checklist: Fichiers à TOUJOURS Ignorer

```
🔴 CRITIQUE - Toujours ignorer:
  ✓ .env*                     (tous les fichiers .env)
  ✓ *.pem, *.key, *.crt      (certificats/clés privées)
  ✓ .aws/, .ssh/              (configurations cloud/ssh)
  ✓ secrets.json              (fichiers de secrets)
  ✓ passwords.txt, keys.txt   (fichiers texte sensibles)

🟡 FORTEMENT RECOMMANDÉ:
  ✓ vendor/ (Composer)
  ✓ node_modules/ (NPM)
  ✓ .venv/ (Python)
  ✓ .vscode/, .idea/          (IDE - personnel à chacun)
  ✓ tools/ (scripts internes)
  ✓ tests/ (tests de dev)

🟢 OPTIONNEL:
  ✓ backups/
  ✓ uploads/
  ✓ temp/
```

### Pièges Courants

**❌ Piège 1: Oublier `.env` dans `.gitignore`**
```bash
# Mauvais:
.gitignore
├─ .env.production  ← Seulement production
├─ (pas .env!)

# Bon:
.gitignore
├─ .env
├─ .env.production
├─ .env.*.local
```

**❌ Piège 2: Committer avant de créer `.gitignore`**
```bash
# MAUVAIS ORDRE:
git add .
git commit -m "Initial"
git push              # Tout est commité!
echo ".env" >> .gitignore  # Trop tard!

# BON ORDRE:
echo ".env" >> .gitignore  # D'abord!
git add .gitignore
git commit -m "Add .gitignore"
git push              # Sûr maintenant
```

**❌ Piège 3: Utiliser des wildcards trop larges**
```bash
# Trop restrictif:
*.php              # Ignorerait TOUS les fichiers PHP!

# Correct:
tools/             # Ignorer un dossier spécifique
tests/             # Ignorer un dossier spécifique
```

### Bonnes Pratiques

```
1. Créer .gitignore AVANT le premier commit
   ✓ Évite d'exposer les secrets d'emblée

2. Vérifier avant de push
   git status
   # Ne doit montrer QUE les fichiers voulus
   # Ne pas voir: .env, node_modules/, vendor/

3. Tester quels fichiers seront envoyés
   git diff --cached --name-only
   # Vérifier que c'est OK avant git push

4. Documenter pourquoi chaque fichier est ignoré
   # Configuration sensible
   .env
   
   # Dépendances (peuvent être régénérées)
   vendor/
```

### Créer `.gitignore` de Base

```bash
# Créer le fichier avec les essentiels
cat > .gitignore << 'EOF'
# Configuration sensible
.env
.env.production
.env.*.local

# Dépendances
vendor/
node_modules/
composer.lock
package-lock.json

# IDE
.vscode/
.idea/

# Python
.venv/
venv/
__pycache__/

# Cache
.phpunit.cache/
.cache/

# OS
.DS_Store
Thumbs.db
EOF

# Puis commiter
git add .gitignore
git commit -m "Add .gitignore with security rules"
```

### Vérification Post-Creation

```bash
# Vérifier que .gitignore fonctionne
git status
# Ne doit montrer: .env.production, vendor/, node_modules/, etc.

# Lister tous les fichiers qui SERAIENT envoyés
git diff --cached --name-only
# Vérifier que c'est safe

# Lister les fichiers ignorés
git check-ignore -v .env.production
git check-ignore -v vendor/
```

---

## 🎯 Conventions de Nommage

### Fichiers PHP
```
Routeur/Entry:     index.php
API:               {resource}.php        (exercises.php, courses.php)
Pages:             {page}-{level}.php    (exercices-6eme.php)
Helpers:           {domain}.php          (helpers.php, auth.php)
Models:            {Model}.php           (User.php, Exercise.php)
Tests:             test_{feature}.php    (test_auth.php)
Tools:             {action}_{domain}.php (import_exercises.php)
```

### Classes
```
Namespace:         src\Models, src\Api, src\Includes
Classes:           PascalCase (UserManager, ExerciseController)
Methods:           camelCase (getUserById, createExercise)
Properties:        camelCase ($userId, $exerciseTitle)
Constants:         UPPER_SNAKE_CASE (MAX_UPLOAD_SIZE)
```

### Base de Données
```
Tables:            PascalCase (Users, Exercises, UserProgress)
Columns:           camelCase (userId, exerciseTitle, isActive)
Primary Keys:      Id (int auto-increment)
Foreign Keys:      {table}Id (userId, exerciseId)
Boolean flags:     is{Name} (isActive, isDeleted)
Timestamps:        CreatedAt, UpdatedAt
```

---

## 🔐 Sécurité et Bonnes Pratiques

### Structure de Sécurité
```
public/              ← Accessible
├── assets/          ← CSS, JS, images
└── index.php        ← Routeur SEUL

src/                 ← PRIVÉ (via require)
├── api/             ← Endpoints protégés
├── database/        ← Connexions (jamais expose)
└── includes/        ← Auth, helpers
```

### Séparation des Responsabilités
- ✅ **Routeur** (`index.php`) → Entrée/routing uniquement
- ✅ **API** (`src/api/`) → Logique métier + données
- ✅ **Views** (`src/pages/`) → Présentation
- ✅ **Helpers** (`src/includes/`) → Utilitaires réutilisables
- ✅ **Models** (`src/models/`) → Classes métier

### Gestion des Environnements
```php
// Toujours charger .env AVANT d'utiliser les variables
require_once __DIR__ . '/../src/config/load_env.php';

// Accéder via getenv()
$dbHost = getenv('DB_HOST');
$appEnv = getenv('APP_ENV');

// Jamais hardcoder les secrets
// ❌ BAD: $password = 'MySecretPassword123';
// ✅ GOOD: $password = getenv('DB_PASSWORD');
```

---

## 📊 Exemple: Flux d'une Requête

```
1. Utilisateur visite: http://example.com/api/exercises
   ↓
2. Apache route vers public/index.php
   ↓
3. index.php parse l'URL → /api/exercises
   ↓
4. Routeur délègue à src/api/router.php
   ↓
5. src/api/exercises.php récupère en BD
   ↓
6. Retourne JSON
   ↓
7. Navigateur affiche les données
```

**Fichiers impliqués:**
```
public/index.php          ← Point d'entrée
src/api/router.php        ← Routage
src/api/exercises.php     ← Logique
src/database/connection.php ← BD
src/includes/helpers.php  ← Utilitaires
```

---

## 🚀 Démarrage d'un Nouveau Projet

### 1. Créer la structure
```bash
mkdir -p {public/assets/{css,js,img},src/{api,config,includes,pages,database,models},tests,tools,docs,db/backups}
touch public/index.php
touch .env .env.production .gitignore composer.json
```

### 2. Fichiers minimaux
```php
// public/index.php
<?php
require_once __DIR__ . '/../src/config/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/moncoachscolaire', '', $path); // Adapter le basePath

// Routes
if (strpos($path, '/api/') === 0) {
    require_once __DIR__ . '/../src/api/router.php';
} else {
    require_once __DIR__ . '/../src/pages/home.php';
}
?>
```

### 3. Configuration bootstrap
```php
// src/config/bootstrap.php
<?php
// Charger .env
require_once __DIR__ . '/load_env.php';

// Connexion BD
require_once __DIR__ . '/../database/connection.php';

// Helpers
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
?>
```

### 4. Premier commit
```bash
git add .gitignore .env.example composer.json README.md
git commit -m "Initial: Project structure setup"
```

---

## 📋 Checklist: Nouveau Projet

- [ ] Créer la structure de base
- [ ] Initialiser git avec `.gitignore`
- [ ] Créer `.env` et `.env.production`
- [ ] Configurer `composer.json`
- [ ] Créer `src/config/bootstrap.php`
- [ ] Créer `src/database/connection.php`
- [ ] Créer `public/index.php` (routeur)
- [ ] Créer `README.md`
- [ ] Écrire `docs/STRATEGIE_*.md` (architecture)
- [ ] Premier test fonctionnel
- [ ] Documenter les conventions du projet

---

## 💡 Conseils Basés sur l'Expérience

### ✅ À Faire
1. **Séparer public/privé dès le départ** → Évite les refactorings complexes
2. **Créer des tools pour les changements de données** → Reproductible et versionnable
3. **Documenter lors du développement** → Pas après
4. **Tester chaque changement majeur** → Crédibilité du code
5. **Utiliser des transactions BD** → Atomicité garantie
6. **Supporter `--dry-run` dans les tools** → Sécurité avant changement
7. **Garder les tests post-exécution** → Preuve fonctionnelle

### ❌ À Éviter
1. **Logique métier dans index.php** → Difficile à tester
2. **Hardcoder les chemins ou secrets** → Erreurs de déploiement
3. **Ignorer les migrations BD** → Perte de données
4. **Committer .env ou vendor/** → Repos énorme + secrets exposés
5. **Fichiers test à la racine** → Pollution du workspace
6. **Trop de niveaux de dossiers** → Navigation difficile
7. **Pas de documentation** → Incompréhension après 3 mois

---

## 🔍 Troubleshooting Structure

### Problème: Chemins cassés après déploiement
**Cause:** Hardcoder `/var/www/html` au lieu d'utiliser `__DIR__`
```php
// ❌ BAD
$dbPath = '/var/www/html/db/connection.php';

// ✅ GOOD
$dbPath = __DIR__ . '/../../db/connection.php';
```

### Problème: Impossible de requêter l'API
**Cause:** Oublier le routeur dans `public/index.php`
```php
// ✅ GOOD
if (strpos($path, '/api/') === 0) {
    require_once __DIR__ . '/../src/api/router.php';
}
```

### Problème: Secrets exposés en production
**Cause:** Committer `.env` ou hardcoder passwords
```bash
# ✅ GOOD
echo ".env" >> .gitignore
cp .env .env.example  # Sans les secrets
```

---

## 📖 Ressources

- **PHP PSR-4 Autoloading:** https://www.php-fig.org/psr/psr-4/
- **Laravel Directory Structure:** https://laravel.com/docs/structure
- **Symfony Best Practices:** https://symfony.com/doc/current/best_practices.html
- **OWASP Security Guide:** https://owasp.org/www-project-secure-coding-practices-quick-reference-guide/

---

**Dernière mise à jour:** 1 janvier 2026  
**Basé sur expérience:** MonCoachScolaire + projets PHP professionnels
