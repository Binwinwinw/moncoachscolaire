# 📁 Nettoyage et Réorganisation des Fichiers - 27 décembre 2025

## ✅ Résumé de l'organisation

### 🎯 Objectif réalisé
Réorganiser la structure du projet MonCoachScolaire en rangeant les fichiers PHP dans leurs dossiers appropriés.

---

## 📂 Structure actuelle

### ✅ À la racine (gardés volontairement)
```
index.php              → Point d'entrée principal du routeur
demo_login.php         → Page de connexion démo
contact.php            → Page de contact
```

### 📁 `src/config/` - Configuration
```
config.php             → Configuration principale + fonctions utilitaires
site_boot.php          → Bootstrap du site
```

### 📁 `src/includes/` - Composants partagés
```
topbar.php             → Barre de navigation (déplacée)
sidebar.php            → Sidebar (déplacée)
footer.php             → Pied de page (déplacé)
admin_auth.php         → Authentification admin
demo_security.php      → Gestion de la mode démo
login_security.php     → Sécurité de connexion
exercice_card.php      → Composant carte d'exercice
exercice_loader.php    → Chargement des exercices
ExerciseValidator.php  → Validation des exercices
exercise_interactive_generator.php  → Générateur d'exercices interactifs
course_content.php     → Contenu des cours
course_markdown_loader.php → Chargement des cours en Markdown
level_navigation.php   → Navigation par niveau
dashboard_extensions.php → Extensions de dashboard
progress_helpers.php   → Helpers de progression
progress_display.php   → Affichage de progression
quiz_generator.php     → Générateur de quiz
gamification.php       → Système de gamification
colibri_mascot.php     → Gestion de la mascotte
coach_webm_events.php  → Événements vidéo WebM
coach_webm_include.php → Inclusion vidéo WebM
```

### 📁 `src/pages/` - Pages principales
```
dashboard.php          → Dashboard utilisateur (déplacé)
dashboard_admin.php    → Dashboard admin (déplacé)
dashboard_parent.php   → Dashboard parent (déplacé)
login.php              → Connexion (déplacé)
logout.php             → Déconnexion (déplacé)
register.php           → Inscription (déplacé)
landingpage.php        → Page d'accueil (déplacée)
exercices.php          → Liste des exercices (déplacée)
cours.php              → Cours (déplacé)
quiz.php               → Quiz (déplacé)
progression.php        → Progression (déplacée)
suivi_enfant.php       → Suivi enfant (déplacé)
parents.php            → Pages parent (déplacée)
view_exercise.php      → Visualisation d'exercice (déplacée)
view_course.php        → Visualisation de cours (déplacée)
maintenance.php        → Page maintenance (déplacée)
```

### 📁 `src/pages/college/` - Cours par niveau
```
6eme/exercices-6eme.php
5eme/exercices-5eme.php
4eme/exercices-4eme.php
3eme/exercices-3eme.php
```

### 📁 `src/pages/lycee/` - Cours lycée
```
seconde/
premiere/
terminale/
bac/
```

### 📁 `src/api/` - APIs (inchangé)
```
courses.php
get_demo_content.php
get_exercises.php
get_progress_chart.php
save_exercise_progress.php
save_progress.php
save-progress.php
track-demo-action.php
admin/
  - exercise_quality.php   → Analyse qualité des exercices (fusionné : summary, list, by_level_subject, placeholders)
  - sanitize_exercises_placeholders.php
  - validate_exercise.php
```

---

## 🔧 Fichiers corrigés

### Corrections des chemins require_once
Tous les fichiers déplacés vers `src/pages/` ont eu leurs chemins mis à jour :

- ✅ `login.php` → `require_once __DIR__ . '/../config/config.php'`
- ✅ `dashboard.php` → `require_once __DIR__ . '/../config/config.php'`
- ✅ `dashboard_admin.php` → chemins corrigés
- ✅ `dashboard_parent.php` → chemins corrigés
- ✅ `landingpage.php` → chemins corrigés
- ✅ `logout.php` → chemins corrigés
- ✅ `maintenance.php` → chemins corrigés
- ✅ `parents.php` → chemins corrigés
- ✅ `progression.php` → chemins corrigés
- ✅ `quiz.php` → chemins corrigés
- ✅ `register.php` → chemins corrigés
- ✅ `suivi_enfant.php` → chemins corrigés
- ✅ `view_course.php` → chemins corrigés
- ✅ `view_exercise.php` → chemins corrigés
- ✅ `cours.php` → chemins corrigés

**Total : 14 fichiers corrigés**

---

## 📊 Statistiques de l'organisation

### Dossiers créés/utilisés
| Dossier | Type | Fichiers |
|---------|------|----------|
| `src/` | Parent | |
| `src/config/` | Configuration | 2 fichiers |
| `src/includes/` | Composants | 20 fichiers |
| `src/pages/` | Pages principales | 15+ fichiers |
| `src/pages/college/` | Cours collège | 4 dossiers |
| `src/pages/lycee/` | Cours lycée | 4 dossiers |
| `src/api/` | APIs | 8+ fichiers |

### Fichiers à la racine
- **Conservés** : 3 (index.php, demo_login.php, contact.php)
- **Déplacés** : 18+

---

## 🔄 Flux de chargement

### 1. Accès à l'application
```
1. index.php (routeur racine)
   ↓
2. Charge src/config/config.php
   ↓
3. Charge src/config/site_boot.php
   ↓
4. Route vers src/pages/{page}.php
   ↓
5. Charge src/includes/{composants}.php
```

### 2. Structure des requêtes

```
GET /index.php?page=dashboard
  ↓ (routeur)
Charge: src/pages/dashboard.php
  ↓
src/pages/dashboard.php
  ├─ require_once '../config/config.php'
  ├─ require_once '../config/site_boot.php'
  ├─ require_once '../includes/gamification.php'
  ├─ require_once '../includes/progress_display.php'
  └─ include '../includes/topbar.php'

GET /index.php?page=login [POST]
  ↓ (routeur)
Charge: src/pages/login.php
  ├─ require_once '../config/config.php'
  ├─ require_once '../includes/login_security.php'
  ├─ require_once '../includes/dashboard_extensions.php'
```

---

## ⚠️ Points importants

### 1. Chemins mis à jour
- Tous les `require_once __DIR__ . '/config.php'` → `require_once __DIR__ . '/../config/config.php'`
- Tous les `require_once __DIR__ . '/includes/` → `require_once __DIR__ . '/../includes/`
- Tous les `include __DIR__ . '/topbar.php'` → `include __DIR__ . '/../includes/topbar.php'`

### 2. index.php (routeur)
- Doit charger depuis : `$root . '/src/pages/...'`
- Doit charger depuis : `$root . '/src/config/...'`
- Doit charger depuis : `$root . '/src/includes/...'`

### 3. .htaccess et mod_rewrite
- Apache doit pouvoir accéder aux fichiers dans `src/`
- Les fichiers source ne doivent pas être exposés directement
- Le routeur `index.php` reste le point d'entrée

---

## 🎓 Avantages de cette organisation

✅ **Clarté** : Séparation claire des responsabilités
✅ **Maintenabilité** : Facile de trouver les fichiers
✅ **Sécurité** : Source code séparé du public/assets
✅ **Scalabilité** : Structure prête pour la croissance
✅ **PSR** : Suit les conventions PHP Standard Recommendations

---

## 📝 Prochaines étapes optionnelles

1. **Créer `public/`** - Pour les assets (CSS, JS, images)
   ```
   public/
   ├─ assets/
   │  ├─ css/
   │  ├─ js/
   │  └─ img/
   ```

2. **Déplacer `.htaccess`** - Depuis racine vers `public/`

3. **Créer `bootstrap/`** - Pour l'initialisation
   ```
   bootstrap/
   ├─ autoload.php
   └─ app.php
   ```

4. **Namespaces PHP** - Ajouter des namespaces pour les classes

---

## ✨ Résultat final

### Avant
```
moncoachscolaire/
├─ config.php (racine) ❌
├─ site_boot.php (racine) ❌
├─ topbar.php (racine) ❌
├─ footer.php (racine) ❌
├─ login.php (racine) ❌
├─ dashboard.php (racine) ❌
├─ exercices.php (racine) ❌
├─ ...
├─ includes/
├─ api/
└─ src/
   ├─ config/
   ├─ includes/
   ├─ pages/
   └─ api/
```

### Après
```
moncoachscolaire/
├─ index.php ✅ (routeur principal)
├─ demo_login.php ✅
├─ contact.php ✅
├─ src/
│  ├─ config/
│  │  ├─ config.php ✅
│  │  └─ site_boot.php ✅
│  ├─ includes/
│  │  ├─ topbar.php ✅
│  │  ├─ footer.php ✅
│  │  └─ ...
│  ├─ pages/
│  │  ├─ login.php ✅
│  │  ├─ dashboard.php ✅
│  │  ├─ exercices.php ✅
│  │  ├─ college/
│  │  ├─ lycee/
│  │  └─ ...
│  └─ api/
├─ public/ (future)
├─ bootstrap/ (future)
├─ db/
├─ docs/
├─ tests/
├─ tools/
└─ vendor/
```

---

**Date** : 27 décembre 2025  
**Statut** : ✅ Terminé  
**Fichiers réorganisés** : 18+
