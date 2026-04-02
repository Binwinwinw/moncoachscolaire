# CONTEXT_BUNDLE.md

_Généré le 2026-02-09T05:37:14.570Z_

---

## Fichier : .github/copilot-instructions.md

```file
# Copilot Instructions — MonCoachScolaire

Objectif : produire des patches fiables sur un repo PHP/CSS/JS, en petits lots, avec diffs + tests + hooks conservés.

## À lire (selon le besoin)
- `.github/PROJECT_CONTEXT.md` : index (où trouver les règles et le contexte).
- `CONTEXT_INDEX.md` : index de contexte (racine) — fichier à lire en priorité après redémarrage.
- `.github/CONTEXT_PRODUIT.md` : contexte produit + workflows métier.
- `.github/REGLES_IA.md` : règles d’implémentation (sécurité, patterns, conventions, checklists).
- `.github/copilot-plan.md` : roadmap (si présent).

## Prompts prêts à l’emploi (recommandé)
Utiliser les prompts dans `.github/prompts/` pour cadrer la sortie Copilot selon le type de tâche :
- `audit-strict.prompt.md` : audit prouvé par le repo ; sinon répondre "NON TROUVÉ".
- `patch-minimal.prompt.md` : patch minimal, compat totale, diff + tests.
- `debug-script.prompt.md` : script CLI (PHP/Node) pour inspecter une réponse API (avec args et HTTP status).
- `mysql-safe.prompt.md` : migration SQL MySQL sûre et rejouable (phpMyAdmin), sans renumérotation PK.

Règle : si tu ne sais pas quel prompt choisir, commencer par `patch-minimal`, puis basculer vers `audit-strict` si tu manques de preuves, ou `debug-script`/`mysql-safe` si la demande est outillée.

## Anti-blocage (OBLIGATOIRE)
Interdit de répondre "I can’t answer" / "NON TROUVÉ" immédiatement si le workspace est accessible.

Avant de conclure :
1) Ouvrir 1 fichier d’ancrage : `public/index.php` (ou `index.php` si absent).
2) Faire max 3 recherches repo (mots-clés courts).
3) Lister 3–8 fichiers candidats (chemins + extrait 1–3 lignes).
4) Ensuite seulement : proposer un patch OU "NON TROUVÉ" avec recherches tentées + info à demander.

Limite stricte : max 3 recherches.

## Front compat (OBLIGATOIRE)
Si HTML/CSS/JS est modifié :
- Lister les hooks (classes/ids/data-attributes) impactés.
- Dire "hooks conservés" ou "hooks modifiés" + raison.
- Ne pas casser les sélecteurs utilisés par le JS : ajouter plutôt que renommer.

## Règles de patch (OBLIGATOIRES)
Toujours fournir :
1) Fichiers impactés
2) Diff unified (ou patch clair)
3) Comment tester (reproductible)
4) Risques + rollback simple
5) Hypothèses (si incertain)

Patch multi-fichiers : 2–3 étapes max (petits lots).

## Format "NON TROUVÉ" (OBLIGATOIRE si blocage)
Si tu ne peux pas conclure après la procédure anti-blocage, réponds exactement avec :
- NON TROUVÉ : ce qui manque / introuvable
- Cherché dans : fichiers/dossiers
- Recherches effectuées : les 3 mots-clés
- Prochaine action demandée à l’humain : le fichier/URL/paramètre nécessaire

## Migration Tailwind (si demandée)
- Incrémental (page par page), pas de big bang.
- Ne casse pas les hooks existants : ajouter des classes Tailwind.
- Après chaque lot : check visuel + 1 test smoke (Playwright si en place).

## Langue & ton
- Répondre en français par défaut.
- Messages UI côté élève : toujours bienveillants et encourageants (jamais de formulation négative).

```

---

## Fichier : CONTEXT_INDEX.md

```file
# CONTEXT_INDEX.md — Index de contexte (MonCoachScolaire)

But de ce fichier : centraliser rapidement les points essentiels à lire après un redémarrage.

## Fichiers essentiels (ordre recommandé)
- `.github/copilot-instructions.md`
- `CONTEXT_INDEX.md` (ce fichier)
- `README.md`
- `DOCUMENTATION.md`
- `package.json`
- `playwright.config.js`
- `.github/workflows/ci.yml`
- `src/components/footer_component.php`
- `dev/tools/scripts/inject-footer.js`
- `public/index.php`

> Ajoutez ici tout fichier clé supplémentaire si vous en trouvez (1 par ligne).

## Règles non négociables (10)
1. Routing centralisé via `index.php?page=...` (ne pas casser les alias legacy).
2. `APP_URL` / `BASEURL` : toujours référencer `site_url()` pour générer les liens.
3. Scoping CSS : éviter `html`/`body` globaux ; utiliser classes de page (`body.landing-page`) pour ciblage.
4. Pas de `!important` dans les nouvelles règles (éviter sauf exception documentée).
5. Tests : les features critiques doivent avoir smoke tests + un E2E simple (Playwright).
6. PHP : PSR-12, PDO prepared statements, pas de `echo` pour JSON endpoints.
7. Sécurité : sessions HttpOnly + SameSite, CSRF token sur tous les formulaires.
8. Scripts & outils : privilégier `dev/tools/` pour scripts d'automatisation, versionnés.
9. Static includes : pour pages statiques, utiliser `public/assets/html/*.html` + script d'injection.
10. Logs & Rollback : garder commits petits, fournir diff + test de rollback simple.

## Travail en cours / Où on s'est arrêté (court)
- Migration footer : composant + fragment + injection terminé (Version 2.2.2).
- Centralisation `exercices` : CTAs redirigés vers `index.php?page=exercices` (progressif).
- Stylelint : règles renforcées, reste ~13 warnings à traiter (priorité secondaire).
- CI : Playwright ajouté — proposer job conditionnel `RUN_E2E` pour exécuter E2E en PR seulement.

## Regénérer le bundle de contexte
1. `npm install` (si nécessaire)
2. `npm run context` — crée/actualise `CONTEXT_BUNDLE.md` à la racine.

## Note rapide pour Copilot
- Après redémarrage, exécuter `npm run context` puis lire `CONTEXT_BUNDLE.md`.

### Prompt Copilot (FR) à coller dans Copilot Chat
```
@workspace
Objectif : recharger le contexte projet après redémarrage.

1) Vérifie que ces fichiers existent et lis-les dans cet ordre :
- .github/copilot-instructions.md
- CONTEXT_INDEX.md
- CONTEXT_BUNDLE.md (si présent, il remplace les fichiers ci-dessus)
- README.md
- DOCUMENTATION.md
- PROJECT_CONTEXT.md (ou PROJECTCONTEXT.md)

2) Donne-moi :
- 10 bullets "règles non négociables" (routing, BASEURL, conventions, CSS scoping, tests)
- 5 bullets "où on en est / derniers chantiers"
- La prochaine action la plus logique (1 seule), avec les fichiers à modifier.

3) Si CONTEXT_INDEX.md ou CONTEXT_BUNDLE.md manque : propose le contenu exact à créer (format Markdown), sans inventer de règles.
```

---

_Fichier généré manuellement le: 2026-02-09 — mettre à jour si objectifs changent._
```

---

## Fichier : README.md

```file
# 🎓 MonCoachScolaire

> Plateforme éducative interactive pour l'accompagnement scolaire du collège au lycée

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Status](https://img.shields.io/badge/status-active-success)](https://github.com)

---

## 🗓️ Mise à jour documentation — 4 février 2026

Cette mise à jour documente l’état réel du projet **sans supprimer l’historique** :
- Arborescence actuelle (src/, dev/tools/, src/api/*)
- Emplacements réels des scripts d’import/export
- Rappels sur le système hybride des cours (Markdown ↔ BDD)

## 🚀 Démarrage rapide

```bash
# Cloner le projet
git clone https://github.com/votre-org/moncoachscolaire.git
cd moncoachscolaire

# Installer les dépendances
composer install

# Configuration
cp .env.example .env
# Éditer .env avec vos paramètres MySQL

# Importer la base de données
php tools/import_schema.php

# Créer un administrateur
php tools/create_admin.php

# Lancer le serveur
php -S localhost:8000
```

### ✅ Commandes à jour (structure actuelle)

```bash
# Importer la base de données
php dev/tools/db/import_schema.php

# Créer un administrateur
php dev/tools/admin/create_admin.php
```

Note : des scripts hérités peuvent encore être référencés sous `tools/` dans l’historique, mais la structure **courante** centralise les scripts dans `dev/tools/`.

Accédez à : **http://localhost:8000**

---


## ✨ Fonctionnalités principales

🎯 **462+ exercices interactifs** couvrant 8 niveaux (6ème → BAC)  
📚 **Cours structurés** par matière et chapitre  
👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)  
🤖 **Mascotte Colibri** avec animations WebM  
📊 **Suivi de progression** personnalisé  
🔐 **Authentification sécurisée** avec gestion de rôles  
📱 **Design responsive** (mobile, tablette, desktop)  
🌐 **Déploiement hybride** (local/production)

---

## 👨‍👩‍👧 Nouveautés Dashboard Parent (2026)

Le dashboard parent propose désormais des fonctionnalités bienveillantes et personnalisées pour accompagner la progression scolaire de chaque enfant :

- Suivi détaillé de la progression (par matière, compétence, période)
- Alertes positives et encouragements personnalisés
- Suggestions d’activités ou d’exercices à refaire à la maison
- Historique des réussites et badges obtenus
- Conseils pédagogiques adaptés au niveau
- Messagerie bienveillante avec l’équipe pédagogique
- Objectifs familiaux et félicitations personnalisées
- Accès à des ressources complémentaires (guides, vidéos, astuces)
- Visualisation du temps de travail (pour encourager l’équilibre)
- Notifications sur les nouveaux contenus ou événements scolaires

**Workflow d’implémentation** :
1. Recueil des besoins parents via retours/tests
2. Prototypage UI/UX bienveillant dans le dashboard_parent
3. Implémentation des modules (progression, alertes, suggestions…)
4. Tests d’affichage et personnalisation par famille/enfant
5. Recueil de feedbacks et ajustements
6. Documentation continue dans copilot-instructions.md
7. Extension possible à d’autres rôles si pertinent

---

---

## 📊 Statistiques

| Métrique | Valeur |
|----------|--------|
| **Exercices** | 462 |
| **Niveaux** | 8 (6ème → BAC) |
| **Matières** | 9 (Math, Français, Anglais, etc.) |
| **Langues** | Français |
| **Backend** | PHP 8+ |
| **Base de données** | MySQL/MariaDB |

---

## 🏗️ Architecture

### Structure réelle (2026-02-04)

```
moncoachscolaire/
├── composer.json
├── package.json
├── README.md
├── DOCUMENTATION.md
├── index.php
├── public/
│   ├── assets/
│   └── index.php
├── src/
│   ├── api/            # admin, cours, demo, exercices, users, parents, public, legacy
│   ├── config/
│   ├── database/
│   ├── includes/
│   ├── pages/
│   └── utils/
├── db/
│   ├── connection.php
│   └── json/
├── dev/
│   ├── tools/
│   ├── db/
│   └── reports/
├── docs/
└── vendor/
```

### Structure historique (legacy)

```
moncoachscolaire/
├── api/              # APIs REST (public + admin)
├── assets/           # CSS, JS, images
├── db/               # Connexion et schémas SQL
├── dev/
│   └── tools/        # Scripts (migration, import, autom)
├── docs/             # Documentation complète
├── includes/         # Fichiers PHP partagés
├── src/              # Sources & Utilitaires
├── tests/            # Tests unitaires
└── vendor/           # Dépendances Composer
```

📖 **Documentation complète** : [DOCUMENTATION.md](DOCUMENTATION.md)

---

## 🎯 Utilisation

### Accès aux dashboards

| Rôle | URL | Identifiants par défaut |
|------|-----|-------------------------|
| 🔧 Admin | `/dashboard_admin.php` | admin / admin123 |
| 👨‍👩‍👧 Parent | `/dashboard_parent.php` | parent1 / pass123 |
| 🎓 Élève | `/dashboard.php` | demo / demo123 |

### Gestion des exercices

Certains scripts/outils nécessitent une session valide (auth). Vous pouvez générer une session de test avec
`test_login_session.php` (voir section “Tests / Auth session” ci‑dessous).

```bash
# Import depuis SQL
php tools/import_exercises.php exercices/fichier.sql

# Validation
php tools/validate_exercises.php

# Statistiques
php tools/stats_exercises.php

# Export JSON/CSV/SQL
php tools/export_exercises.php json
```

### ✅ Commandes à jour (structure actuelle)

```bash
# Import depuis SQL
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Validation
php dev/tools/exercises/validate_exercises.php

# Statistiques
php dev/tools/exercises/stats_exercises.php

# Export JSON/CSV/SQL
php dev/tools/exercises/export_exercises.php json
```

### 🔒 Tests HTTP authentifiés (sécurité)

```bash
# 1) Login BDD + cookie jar + CSRF
MCS_USERNAME=VOTRE_USER MCS_PASSWORD=VOTRE_MDP php dev/tools/tests/test_login_session.php http://localhost/moncoachscolaire/public

# 2) Réutiliser la session pour les tests
MCS_SESSION_ID=VOTRE_SESSION php dev/tools/tests/test_csrf_missing.php http://localhost/moncoachscolaire/public
php dev/tools/tests/test_admin_forbidden.php http://localhost/moncoachscolaire/public
php dev/tools/tests/test_injection_rejected.php http://localhost/moncoachscolaire/public
php dev/tools/tests/test_rate_limit.php http://localhost/moncoachscolaire/public
```

Le script de login écrit :
- cookies : .tmp/mcs.cookies.txt
- JSON : .tmp/mcs.auth.json

---

## 💻 Développement

### Prérequis

- PHP >= 8.0
- MySQL/MariaDB >= 10.x
- Apache >= 2.4 (avec mod_rewrite)
- Composer >= 2.0

### Configuration environnement

Fichier `.env` :

```bash
DB_HOST=localhost
DB_NAME=moncoachscolaire
DB_USER=root
DB_PASS=votreMotDePasse

APP_ENV=local  # local|production
APP_DEBUG=true
```

### Scripts utiles

```bash
# Tests
./vendor/bin/phpunit

# Lister utilisateurs
php tools/list_users.php

# Mode maintenance
php tools/enable_maintenance.php "Message"
php tools/disable_maintenance.php

# Debug session
php tools/debug_session.php
```

---

## 📚 Documentation

### Guides principaux

- **[DOCUMENTATION.md](DOCUMENTATION.md)** - Documentation complète
- **[docs/INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)** - Import/export exercices
- **[docs/GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)** - Utilisation dashboard admin
- **[docs/SECURITE-ENV.md](docs/SECURITE-ENV.md)** - Configuration sécurité
- **[docs/HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)** - Configuration Apache
- **[docs/MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)** - Intégration mascotte

### Documentation complète

📁 **[docs/](docs/)** - Tous les guides et documentations

---

## 🔒 Sécurité

✅ Sessions PHP sécurisées (HttpOnly, SameSite)  
✅ Hachage bcrypt pour mots de passe  
✅ Protection CSRF avec tokens  
✅ Validation/sanitization inputs  
✅ Headers de sécurité (CSP, X-Frame-Options)  
✅ .htaccess hybride (local/production)  
✅ Protection fichiers sensibles (.env, config.php)

**Reporting vulnérabilités** : security@moncoachscolaire.fr

---

## 🤝 Contribution

Les contributions sont les bienvenues ! Suivez ces étapes :

1. **Fork** le projet
2. **Créer** une branche (`git checkout -b feature/AmazingFeature`)
3. **Commit** vos changements (`git commit -m 'Add AmazingFeature'`)
4. **Push** vers la branche (`git push origin feature/AmazingFeature`)
5. **Ouvrir** une Pull Request

### Standards de code

- **PSR-12** pour PHP
- **ESLint** pour JavaScript
- Tests unitaires obligatoires pour nouvelles fonctionnalités

---

## 📄 Licence

Ce projet est sous licence **MIT**. Voir [LICENSE](LICENSE) pour plus de détails.

---

## 👥 Équipe

**Mainteneur principal** : Équipe MonCoachScolaire  
**Email** : contact@moncoachscolaire.fr  
**Support** : support@moncoachscolaire.fr

---

## 🙏 Remerciements

- Tous les contributeurs du projet
- Les enseignants et pédagogues pour leurs retours
- La communauté open-source

---

## 📈 Roadmap

- [ ] Application mobile (React Native)
- [ ] Mode hors-ligne (PWA)
- [ ] Système de badges et gamification
- [ ] Intégration visioconférence
- [ ] API publique pour intégrations tierces
- [ ] Support multilingue (Anglais, Espagnol)

---

## 📞 Support

### Obtenir de l'aide

- 📧 **Email** : support@moncoachscolaire.fr
- 📖 **Documentation** : [docs/](docs/)
- 🐛 **Issues** : [GitHub Issues](https://github.com/votre-org/moncoachscolaire/issues)

### FAQ

**Q : Comment importer de nouveaux exercices ?**  
R : Utilisez `php tools/import_exercises.php fichier.sql`. Voir [docs/INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)

**Q : Comment créer un compte administrateur ?**  
R : `php tools/create_admin.php` ou voir [docs/CREER-COMPTE-ADMIN-RAPIDE.md](docs/CREER-COMPTE-ADMIN-RAPIDE.md)

**Q : Le site est en maintenance, comment le désactiver ?**  
R : `php tools/disable_maintenance.php`

**Q : Comment configurer HTTPS en production ?**  
R : Voir [docs/HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)

---

## 📊 Status badges

![Build Status](https://img.shields.io/badge/build-passing-success)
![Tests](https://img.shields.io/badge/tests-passing-success)
![Coverage](https://img.shields.io/badge/coverage-85%25-yellowgreen)
![Security](https://img.shields.io/badge/security-A+-brightgreen)

---

**Mise à jour documentation** : 4 février 2026

**Version actuelle** : 2.0.0  
**Dernière mise à jour** : 27 décembre 2025

---

<p align="center">
  Fait avec ❤️ par l'équipe MonCoachScolaire
</p>

```

---

## Fichier : DOCUMENTATION.md

```file
# 📚 MonCoachScolaire - Documentation Complète

> Plateforme éducative interactive pour l'accompagnement scolaire du collège au lycée

## 🗓️ Mise à jour documentation — 4 février 2026

Cette mise à jour documente l’état réel du projet **sans supprimer l’historique** :
- Arborescence actuelle (src/, dev/tools/, src/api/*)
- Emplacements réels des scripts d’import/export
- Rappels sur le système hybride des cours (Markdown ↔ BDD)

## 🧭 Organisation du document

Ce document est organisé en **deux parties** :
- **Partie 1** : Documentation générale (vue d’ensemble, architecture, usage).
- **Partie 2** : **Annexe technique** (historique, schémas détaillés, workflows avancés).

## 📅 Dates de référence (distinctes)

- **Documentation générale** : 4 février 2026 (mise à jour structure & scripts).
- **Historique des versions** : 3 février 2026 (v2.2.0) et 4 février 2026 (v2.2.1).
- **Annexe technique** : 4 février 2026 (état technique consolidé).

## 🗓️ Journal des documentations (entrées datées)

> Chaque entrée correspond à une action/documentation distincte, avec sa date propre (pas une date unique globale).

- **Documentation générale** : 4 février 2026 (mise à jour structure & scripts).
- **Historique des versions** : 3 février 2026 (v2.2.0) et 4 février 2026 (v2.2.1).
- **Annexe technique** : 4 février 2026 (état technique consolidé).
- **Système d’exercices — correctifs “multi-parties”** : 5–6 février 2026 (normalisation + correctifs d’affichage, scripts de conversion, correction syntaxe PHP). 

## 📖 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)
7. [Documentation Technique](#documentation-technique)
8. [Sécurité](#sécurité)
9. [Maintenance](#maintenance)
10. [Annexe technique (historique)](#annexe-technique-historique)
11. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **462+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique | Valeur |
|----------|--------|
| Exercices totaux | 462 |
| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-04)

```
moncoachscolaire/
├── composer.json
├── package.json
├── README.md
├── DOCUMENTATION.md
├── index.php
├── public/
│   ├── assets/
│   └── index.php
├── src/
│   ├── api/            # admin, cours, demo, exercices, users, parents, public, legacy
│   ├── config/
│   ├── database/
│   ├── includes/
│   ├── pages/
│   └── utils/
├── db/
│   ├── connection.php
│   └── json/
├── dev/
│   ├── tools/
│   ├── db/
│   └── reports/
├── docs/
└── vendor/
```

### Structure historique (legacy)

```
moncoachscolaire/
├── 📁 api/              # Endpoints API REST
│   ├── admin/           # APIs administrateur
│   └── *.php            # APIs publiques
├── 📁 assets/           # Ressources statiques
│   ├── css/             # Feuilles de style
│   ├── js/              # Scripts JavaScript
│   └── img/             # Images
├── 📁 db/               # Base de données
│   ├── connection.php   # Connexion PDO
│   └── *.sql            # Schémas et migrations
├── 📁 docs/             # Documentation complète
├── 📁 includes/         # Fichiers PHP inclus
├── 📁 public/           # Fichiers publics
├── 📁 src/              # Sources organisées
│   ├── exercices/       # Exercices par niveau
│   ├── cours/           # Cours par matière
│   └── utils/           # Utilitaires (Parsers, Helpers)
├── 📁 tests/            # Tests unitaires
├── 📁 dev/
│   └── tools/           # Outils et scripts d'automatisation
│       ├── courses/     # Gestion des cours (ex: link_exercises)
│       └── import_export/ # Scripts d'import/export
└── 📁 vendor/           # Dépendances Composer
```

### Stack technique

| Technologie | Version | Usage |
|-------------|---------|-------|
| PHP | 8.x | Backend |
| MySQL/MariaDB | 10.x | Base de données |
| JavaScript | ES6+ | Frontend interactif |
| Bootstrap | 5.x | UI/UX |
| Apache | 2.4 | Serveur web |
| Composer | 2.x | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 462 exercices structurés par niveau et matière
- **Formats variés** : QCM, questions ouvertes, exercices à trous
- **Corrections détaillées** : Réponses complètes avec explications
- **Import/Export** : Outils pour importer des exercices depuis SQL, JSON, CSV
- **Validation automatique** : Détection d'incohérences et doublons

📖 Voir : [docs/INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)

### 📚 Système de cours

- **Génération Hybride** : Les cours sont générés dynamiquement à partir des compétences détectées dans les exercices.
- **Identification** : Basé sur le pattern `SUJET-NIVEAU-COMPETENCE`.
- **Contenu HTML riche** : Formatage, images, vidéos.
- **Liaison Automatique** : Script `dev/tools/courses/link_exercises_to_courses.php` pour lier exercices et leçons.

📖 Voir : [docs/INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)

### 👥 Dashboards multi-rôles

#### Dashboard Élève
- Accès aux cours et exercices
- Suivi personnel de progression
- Historique d'activité
- Badges et récompenses

#### Dashboard Parent
- Suivi des enfants liés
- Statistiques de progression
- Historique d'exercices
- Alertes et notifications

#### Dashboard Administrateur
- Gestion utilisateurs
- CRUD exercices/cours
- Statistiques globales
- Logs système
- Mode maintenance

📖 Voir : [docs/GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)

### 🤖 Mascotte Colibri

- **Animations WebM** avec transparence alpha
- **Messages contextuels** adaptatifs
- **Optimisation performance** : Compression vidéo
- **Fallback gracieux** : Support navigateurs anciens

📖 Voir : [docs/MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)

### 🔐 Sécurité

- **Authentification** : Sessions PHP sécurisées
- **Rôles & permissions** : student, parent, admin
- **Protection CSRF** : Tokens anti-forgery
- **Validation inputs** : Filtrage XSS/SQL injection
- **.htaccess hybride** : Règles local/production

📖 Voir : [docs/SECURITE-ENV.md](docs/SECURITE-ENV.md), [docs/HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)

---

## 🚀 Installation & Configuration

### Prérequis

```bash
# Logiciels requis
PHP >= 8.0
MySQL/MariaDB >= 10.x
Apache >= 2.4 (avec mod_rewrite, mod_headers)
Composer >= 2.0
```

### Installation rapide

```bash
# 1. Cloner le projet
git clone https://github.com/votre-org/moncoachscolaire.git
cd moncoachscolaire

# 2. Installer les dépendances
composer install
npm install  # Optionnel pour les assets

# 3. Configuration base de données
cp .env.example .env
# Éditer .env avec vos credentials MySQL

# 4. Importer le schéma
php tools/import_schema.php

# 5. Créer un compte admin
php tools/create_admin.php

# 6. Lancer le serveur local
php -S localhost:8000
```

### ✅ Commandes à jour (structure actuelle)

```bash
# Importer le schéma
php dev/tools/db/import_schema.php

# Créer un compte admin
php dev/tools/admin/create_admin.php
```

Note : des scripts hérités peuvent encore être référencés sous `tools/` dans l’historique, mais la structure **courante** centralise les scripts dans `dev/tools/`.

### Configuration environnement

Fichier `.env` :

```bash
# Base de données
DB_HOST=localhost
DB_NAME=moncoachscolaire
DB_USER=root
DB_PASS=votreMotDePasse

# Application
APP_ENV=local  # local|production
APP_DEBUG=true
APP_URL=http://localhost:8000

# Sécurité
SESSION_LIFETIME=1440  # minutes
COOKIE_SECURE=false    # true en production HTTPS
```

📖 Voir : [docs/INSTRUCTIONS_ENV.md](docs/INSTRUCTIONS_ENV.md)

---

## 📘 Utilisation

### Accès aux différents dashboards

| Rôle | URL | Identifiants par défaut |
|------|-----|-------------------------|
| Admin | `/dashboard_admin.php` | admin / admin123 |
| Parent | `/dashboard_parent.php` | parent1 / pass123 |
| Élève | `/dashboard.php` | demo / demo123 |

### Gestion des exercices

#### Import d'exercices

```bash
# Depuis un fichier SQL
php tools/import_exercises.php exercices/fichier.sql

# Validation post-import
php tools/validate_exercises.php

# Nettoyage doublons
php tools/cleanup_exercises.php
```

#### ✅ Commandes à jour (structure actuelle)

```bash
# Depuis un fichier SQL
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Validation post-import
php dev/tools/exercises/validate_exercises.php

# Nettoyage doublons
php dev/tools/exercises/cleanup_exercises.php
```

#### Export d'exercices

```bash
# Export JSON
php tools/export_exercises.php json

# Export CSV
php tools/export_exercises.php csv

# Export SQL
php tools/export_exercises.php sql
```

#### ✅ Commandes à jour (structure actuelle)

```bash
# Export JSON
php dev/tools/exercises/export_exercises.php json

# Export CSV
php dev/tools/exercises/export_exercises.php csv

# Export SQL
php dev/tools/exercises/export_exercises.php sql
```

📖 Voir : [docs/IMPORT_EXERCISES.md](docs/IMPORT_EXERCISES.md)

### Présentation des exercices (front)

> **Note** : la trame PHP complète est partiellement prouvée par le composant `renderExerciseCard()` ; le contrat DOM/JS est entièrement prouvé par les scripts front.

#### 1) Fichiers impliqués

**Rendu HTML (PHP)**
- Composant carte : [src/includes/exercice_card.php](src/includes/exercice_card.php) — `renderExerciseCard()`.
- Génération HTML via API : [src/api/exercices/get_exercises.php](src/api/exercices/get_exercises.php) (action `exercise_html`).

**Chargement & init front**
- Pages élèves (exemple) : [src/pages/eleve/college/3eme/exercices-3eme.php](src/pages/eleve/college/3eme/exercices-3eme.php) charge `dynamic-exercises.css`, `interactive-exercises.js`, `dynamic-exercises.js`.

**JS interactions**
- [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js) — init + feedback.
- [public/assets/js/dynamic-exercises.js](public/assets/js/dynamic-exercises.js) — chargement dynamique HTML + init JS.
- [public/assets/js/exercices.js](public/assets/js/exercices.js) — vérifs, progression locale, bouton “terminé”.

**CSS (UI carte)**
- [public/assets/css/style.css](public/assets/css/style.css) — styles `.exercise-card-ui` et variantes `ui-age-*`.

#### 2) Contrat DOM (hooks + rôle)

**Carte & identifiants**
- `.exercise-card` + `data-exercise-id` : carte racine + identifiant (utilisé pour score/progression). Voir [public/assets/js/exercices.js](public/assets/js/exercices.js).
- `data-difficulty`, `data-subject` : context score/XP (utilisé côté JS). Voir [public/assets/js/exercices.js](public/assets/js/exercices.js).

**Boutons / corrections**
- `.btn-show-answer`, `.exercise-answer` : verrouillage/déverrouillage correction. Voir [public/assets/js/exercices.js](public/assets/js/exercices.js).
- `.btn-exercise-complete` : marque “terminé” après succès. Voir [public/assets/js/exercices.js](public/assets/js/exercices.js).

**Vérification (legacy)**
- `.btn-check-coloring`, `.btn-check-conjugation`, `.btn-check-qcm`, `.btn-check-math` : boutons de vérification. Voir [public/assets/js/exercices.js](public/assets/js/exercices.js).

**Types interactifs**
- Coloriage : `.word-coloring-exercise`, `.word-coloring-container`, `.coloring-feedback`, `.coloring-word`, `data-sentence`, `data-correct`. Voir [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js).
- Conjugaison : `.conjugation-exercise`, `.conjugation-container`, `.conjugation-feedback`, `data-questions`, `input[data-correct]`. Voir [public/assets/js/exercices.js](public/assets/js/exercices.js) + [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js).
- Maths : `.math-exercise`, `.math-container`, `.math-feedback`, `input[data-correct]`. Voir [public/assets/js/exercices.js](public/assets/js/exercices.js) + [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js).
- QCM : `.qcm-exercise`, `.qcm-question`, `.qcm-feedback`, `input[data-correct="true"]`. Voir [public/assets/js/exercices.js](public/assets/js/exercices.js).

**Feedback & correction**
- `.feedback-success`, `.feedback-good`, `.feedback-needs-work` : blocs de feedback générés. Voir [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js).
- `.btn-show-correction`, `.full-correction` : bascule correction chronologie. Voir [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js).

**Auto-détection**
- `.exercise-auto` + `data-content` + `data-instruction` : auto-detect type. Voir [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js).

**Chargement dynamique**
- `.exercise-display-area` : zone d’injection du HTML d’exo. Voir [public/assets/js/dynamic-exercises.js](public/assets/js/dynamic-exercises.js).
- `.qcm-exercise`, `.math-exercise`, `.conjugation-exercise` : utilisés pour init après injection. Voir [public/assets/js/dynamic-exercises.js](public/assets/js/dynamic-exercises.js).

#### 3) Flux JS (init, events, feedback)

1. **Injection HTML** : `dynamic-exercises.js` charge le HTML via l’API `exercise_html` puis injecte dans `.exercise-display-area`. Voir [public/assets/js/dynamic-exercises.js](public/assets/js/dynamic-exercises.js) + [src/api/exercices/get_exercises.php](src/api/exercices/get_exercises.php).
2. **Init interactions** : `InteractiveExercises.initAll()` initialise les types détectés + compat legacy. Voir [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js).
3. **Vérification & score** : `exercices.js` attache les listeners `.btn-check-*`, calcule le score, débloque la correction et le bouton terminé. Voir [public/assets/js/exercices.js](public/assets/js/exercices.js).
4. **Feedback** : `interactive-exercises.js` génère les blocs `.feedback-*` et correction chronologie. Voir [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js).

#### 4) Checklist de test manuel

- Ouvrir une page élève d’exercices (ex: 3ème) : la zone `[data-dynamic-exercises]` charge un exercice.
- Vérifier que `.exercise-card` est injectée dans `.exercise-display-area`.
- Tester un exercice interactif (QCM/Math/Conjugaison/Coloriage) : bouton `.btn-check-*` → feedback `.feedback-*`.
- Vérifier que la correction se débloque via `.btn-show-answer` après succès ou 5 échecs.
- Vérifier que le bouton `.btn-exercise-complete` passe à “✅ Terminé” après succès.

### Mode maintenance

```bash
# Activer
php tools/enable_maintenance.php "Maintenance en cours..."

# Désactiver
php tools/disable_maintenance.php
```

#### ✅ Commandes à jour (structure actuelle)

```bash
# Activer
php dev/tools/maintenance/enable_maintenance.php "Maintenance en cours..."

# Désactiver
php dev/tools/maintenance/disable_maintenance.php
```

---

## 💻 Développement

### Scripts utiles

```bash
# Statistiques exercices
php tools/stats_exercises.php

# Valider cohérence
php tools/validate_exercises_coherence.php

# Lister utilisateurs
php tools/list_users.php

# Debug session
php tools/debug_session.php
```

Note : les scripts de test ad‑hoc sont désormais centralisés dans `dev/tools/tests/` (ex: `test_direct_api.php`, `test_router.php`).

### Tests

```bash
# Lancer tous les tests
./vendor/bin/phpunit

# Tests spécifiques
./vendor/bin/phpunit tests/ExercisesTest.php

# Avec couverture
./vendor/bin/phpunit --coverage-html coverage/
```

### Standards de code

- **PSR-12** : Style de code PHP
- **ESLint** : Linting JavaScript
- **Prettier** : Formatage automatique

---

## 📚 Documentation Technique

### Guides principaux

| Document | Description |
|----------|-------------|
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md) | Import/export exercices |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md) | Gestion système de cours |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md) | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md) | Configuration sécurité |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md) | Configuration Apache |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md) | Intégration mascotte |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md) | Gestion des URLs |

### Documentation complète

Toute la documentation est disponible dans le dossier [docs/](docs/).

---

## 🔒 Sécurité

### Bonnes pratiques implémentées

✅ **Authentification**
- Sessions PHP sécurisées (HttpOnly, SameSite)
- Hachage bcrypt pour mots de passe
- Timeout automatique

✅ **Autorisation**
- Vérification rôles à chaque requête
- Séparation des permissions (admin/parent/student)
- Protection endpoints API

✅ **Protection données**
- Validation/sanitization inputs
- Préparation requêtes SQL (PDO)
- Headers de sécurité (CSP, X-Frame-Options)

✅ **Infrastructure**
- .htaccess hybride (local/production)
- Protection fichiers sensibles (.env, config.php)
- Rate limiting (optionnel)

### Reporting vulnérabilités

Contactez : security@moncoachscolaire.fr

---

## 🛠️ Maintenance

### Logs

```bash
# Logs Apache
tail -f /var/log/apache2/error.log

# Logs PHP (si configuré)
tail -f /var/log/php/errors.log

# Logs application
tail -f logs/app.log
```

### Backup base de données

```bash
# Backup manuel
php tools/backup_database.php

# Restauration
php tools/restore_database.php backups/backup_20251227.sql
```

### Mises à jour

```bash
# Dépendances PHP
composer update

# Dépendances JS
npm update

# Migrations DB
php tools/migrate.php
```

---

## 🚦 Intégration continue (CI/CD)

Le projet utilise GitHub Actions pour automatiser les tests, le linting et le déploiement.

- Fichier de workflow : `.github/workflows/ci.yml`

## Admin Exercises — Modifications (Jan 2026)
- Le filtre **Classe** a été remplacé par **Matière** pour éviter les doublons (compatibilité ascendante : `?classe=` fonctionne toujours).
- La page d'administration des exercices a été révisée : présentation en cartes accessibles (`<article>`), collapsibles accessibles, actions rapides (dupliquer, activer/désactiver).
- Smoke tests ajoutés : `dev/tools/tests/test_exercices_filter_alias.php`, `dev/tools/tests/test_exercices_subject_normalization.php`, `dev/tools/tests/test_exercices_accessibility.php`.

- Tests automatiques à chaque push/pull request
- Lint PHP et JS
- Import automatique du schéma de base

---

## 🗂️ Organisation & Nettoyage

- Les fichiers techniques (.phpunit.cache, .phpunit.result.cache) sont déplacés dans `dev/` et ignorés par Git
- Les scripts utilitaires sont centralisés dans `dev/tools/`
- Les backups/archives obsolètes sont supprimés régulièrement
- Les fichiers/dossiers avec espaces ou accents sont renommés pour la portabilité

---

## 🧩 Schéma d’architecture technique

Voir : [docs/ARCHITECTURE_MERMAID.md](docs/ARCHITECTURE_MERMAID.md)

---

## 📑 Documentation API

Voir : [docs/API_REFERENCE.md](docs/API_REFERENCE.md)

---

## ✅ Checklist accessibilité & optimisation des assets

Voir : [docs/CHECKLIST_ACCESSIBILITE_ASSETS.md](docs/CHECKLIST_ACCESSIBILITE_ASSETS.md)

---

## 🛡️ Sécurité avancée

- Les fichiers sensibles (.env, .env.production, scripts de migration) sont exclus du versionning
- Les accès aux scripts critiques sont restreints en production
- Audit régulier des dépendances (Composer, npm)

---

## 🧪 Gestion des tests

- PHPUnit installé en dev
- Lancement des tests : `php vendor/bin/phpunit --configuration dev/tests/phpunit.xml`
- Couverture : `php vendor/bin/phpunit --coverage-html coverage/`
- Les tests sont organisés dans `dev/tests/`

---

## 🛠️ Scripts de migration

- Migration vers la production : `php dev/tools/migrate_to_production_env.php`
- Import/export automatisés via scripts PHP

---

## 📦 Mise à jour des dépendances

- Mise à jour Composer : `composer update` puis `composer self-update`
- Mise à jour npm : `npm update`

---

## 📅 Historique des versions

### Version 2.2.1 (2026-02-04)
**Enrichissement Documentation**

- Ajout d’un encart de mise à jour daté dans la documentation principale.
- Documentation de la structure réelle (src/, dev/tools/, src/api/*).
- Ajout des commandes à jour pour import/export et maintenance.
- Conservation de l’historique (sections legacy non supprimées).

### Version 2.2.2 (2026-02-09)
**Migration & hardening : Footer, exercices centralisés, tests E2E, CI, Stylelint**

- **Refactor footer** : extraction du composant `src/components/footer_component.php`, styles `public/assets/css/components/footer.css` et JS `public/assets/js/footer-animations.js`.
- **Footer statique** : création du fragment `public/assets/html/footer-fragment.html` et script d’injection `dev/tools/scripts/inject-footer.js` pour propager le footer sur les pages statiques (ex: `rgpd.html`, `mentions-legales.html`, `politique-cookies.html`, `conditions-utilisation.html`).
- **Lien "Préparer le Bac"** ajouté dans le footer dynamiquement et dans le fragment statique (mise à jour du composant + injection des pages statiques).
- **Centralisation des Exercices** : les CTA visibles (`Mes Exercices`, `Exercices`) redirigent désormais vers la page hub `index.php?page=exercices` (approche progressive — les pages spécialisées par niveau restent disponibles pour compatibilité).
- **Helpers** : `src/includes/footer_helpers.php` (calcule l'URL des exercices selon session/niveau) ; correction d’un warning ($has_access) dans `src/pages/eleve/bac/guide-remediation.php`.
- **Router** : alias simple pour `page=contact` → `users/contact` afin d’éviter les 404 legacy.
- **Tests E2E** : ajout `dev/tools/tests/e2e/exercises-link.spec.js` (landing CTA + footer link vers hub) ; Playwright baseURL rendu configurable via `PLAYWRIGHT_BASE_URL` (`playwright.config.js`).
- **CI** : `.github/workflows/ci.yml` mis à jour pour exécuter `npm run build:includes`, `npm run lint:css`, installer les navigateurs Playwright et lancer les tests E2E (avec `PLAYWRIGHT_BASE_URL=http://127.0.0.1:8080`).
- **Stylelint** : configuration `.stylelintrc.json` renforcée (interdire selecteurs globaux `html`/`body`, avertir sur `!important`), `.stylelintignore` mis à jour; exécution de `stylelint --fix` pour corriger les problèmes auto-fixables.
- **Vérifications & smoke-tests** : scripts de smoke (exercices filters / normalization / accessibility) conservés et exécutés en CI ; ajout d’une stratégie de tests E2E progressive (skip si base URL indisponible).
- **Fichiers modifiés (sélection)** : `src/components/footer_component.php`, `src/includes/footer_helpers.php`, `public/assets/html/footer-fragment.html`, `dev/tools/scripts/inject-footer.js`, `public/assets/css/components/footer.css`, `public/assets/js/footer-animations.js`, `dev/tools/tests/e2e/exercises-link.spec.js`, `playwright.config.js`, `.github/workflows/ci.yml`, `.stylelintrc.json`, `.stylelintignore`, `src/pages/*` (CTA refactor), `public/index.php` (alias contact).

**Vérifier localement** :
1. `npm run build:includes` (injection footer) et vérifier les pages statiques mises à jour.
2. Démarrer serveur local `php -S 127.0.0.1:8081 -t public` et naviguer vers `/` ; cliquer sur CTA Landing et lien footer → doit aboutir à `/index.php?page=exercices`.
3. Lancer `npx playwright test` (ou `npm run test:e2e`) pour exécuter les tests E2E (configurable via `PLAYWRIGHT_BASE_URL`).
4. `npm run context` — génère `CONTEXT_BUNDLE.md` (bundle lisible du contexte projet pour relecture après redémarrage).

**Risques & rollback rapide** :
- Rollback : revert des commits ciblés (footer / inject / ci / tests) via Git si un effet indésirable est détecté.
- Conserver temporairement les pages par niveau pour compatibilité avant une migration globale.

**Prochaines étapes recommandées** :
- Ajouter job CI conditionnel `RUN_E2E` pour exécuter Playwright seulement quand nécessaire (PRs lourds vs main releases).
- Nettoyage progressif des références legacy `college/*/exercices-*` dans tests & outils si on décide d’unifier totalement les URLs.
- Ajouter snapshots visuels Playwright pour verrouiller l’apparence du footer et du hub exercises.

### Version 2.2.0 (2026-02-03)
**Enrichissement Moteur & Contenu**

#### 🚀 Backend & Outils
- **Génération automatique de contenu (Cours)** :
  - Script `dev/tools/courses/fill_missing_content_generic.php` : Comble les 57% de cours manquants avec une structure pédagogique générique (Intro/Objectifs/Métho).
  - Script `dev/tools/courses/enrich_course_content.php` : Lie les exercices existants aux cours via la colonne `example`.
  - Couverture actuelle : 100% des cours ont une explication et des points clés.
- **Importateur d'Exercices V2** :
  - Nouveau script `dev/tools/exercises/import_new_exercises.php`.
  - Support robuste du JSON (conversion automatique des Tableaux -> String pour éviter les erreurs SQL).
  - Typage strict des champs (`AnswerType`, `Choices`, `is_active`).
  - Rapport détaillé d'importation.

#### 🎨 Frontend (Affichage Cours)
- **Mise à jour `src/pages/system/view_course.php`** :
  - Support de l'affichage hybride (Markdown fichiers OU Base de données).
  - Design amélioré pour les sections dynamiques :
    - 🟩 **Points Clés** : Encadré vert avec icône.
    - 🟧 **Exemples** : Encadré orange pour les exercices liés.
  - Priorisation intelligente : Markdown > DB Content > Description simple.

#### 🔧 Maintenance
- Nettoyage de la racine du projet (déplacement des rapports dans `dev/reports/`).

---

## 📅 Dernière mise à jour

**Version** : 2.2.1
**Dernière mise à jour** : 4 février 2026
**Mainteneur** : Équipe MonCoachScolaire

---

## 🙏 Remerciements

- Tous les contributeurs
- Les enseignants pour leurs retours
- La communauté open-source

---

**Version** : 2.1.0  
**Dernière mise à jour** : 14 janvier 2026  
**Mainteneur** : Équipe MonCoachScolaire

## 📎 Annexe technique (historique)

_Note : cette annexe regroupe la documentation technique détaillée. Elle est conservée pour référence et peut contenir des éléments hérités._

# 📘 Documentation Technique - MonCoachScolaire

**Version** : 2.0.0  
**Dernière mise à jour** : 4 février 2026

---

## 📑 Table des matières

1. [Architecture générale](#architecture-générale)
2. [Base de données](#base-de-données)
3. [Système de cours](#système-de-cours)
4. [Système d'exercices](#système-dexercices)
5. [Parcours pédagogiques](#parcours-pédagogiques)
6. [Tracking et statistiques](#tracking-et-statistiques)
7. [API Endpoints](#api-endpoints)
8. [Scripts utilitaires](#scripts-utilitaires)
9. [Génération de contenu IA](#génération-de-contenu-ia)
10. [Sécurité](#sécurité)

---

## 🏗️ Architecture générale

### Stack technique

- **Backend** : PHP 8.1+
- **Base de données** : MySQL 8.0+ / MariaDB 10.5+
- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **Bibliothèques** : Chart.js (graphiques), Font Awesome (icônes)

### Pattern MVC simplifié

Requête HTTP
↓
public/index.php (Routeur)
↓
config/site_boot.php (Init globale)
↓
src/pages/{role}/{page}.php (Contrôleur + Vue)
↓
src/includes/*.php (Modèles/Services)
↓
Base de données (MySQL)

text

### Workflow de session

```php
// 1. Démarrage session sécurisée
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

// 2. Vérification authentification
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// 3. Accès aux données utilisateur
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
🗄️ Base de données
Tables principales
users - Utilisateurs
sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('eleve', 'parent', 'professeur', 'admin') DEFAULT 'eleve',
    niveau VARCHAR(20),
    xp INT DEFAULT 0,
    cristaux INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
exercises - Exercices
sql
CREATE TABLE exercises (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Subject VARCHAR(100) NOT NULL,
    Level VARCHAR(50) NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Content TEXT NOT NULL,
    Instruction TEXT NOT NULL,
    Answer TEXT NOT NULL COMMENT 'JSON array',
    AnswerType ENUM('texte','qcm','qcm_multiple','vrai_faux','association','ordre'),
    Choices TEXT COMMENT 'JSON array ou null',
    Tips TEXT,
    Domain VARCHAR(100),
    Competence VARCHAR(255),
    Difficulty ENUM('facile','moyen','difficile') DEFAULT 'moyen',
    Identifier VARCHAR(100) UNIQUE,
    IsActive TINYINT(1) DEFAULT 1,
    XPPoints INT DEFAULT 10,
    Coherence TINYINT(1) DEFAULT 1,
    LinkedCourses TEXT COMMENT 'JSON array [courseId1, courseId2]',
    Explanation TEXT,
    KeyPoint TEXT,
    Example TEXT,
    Prerequisites TEXT,
    EstimatedTime INT COMMENT 'Minutes',
    Tags TEXT COMMENT 'JSON array',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subject_level (Subject, Level),
    INDEX idx_active (IsActive)
);
courses - Cours
sql
CREATE TABLE courses (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Subject VARCHAR(100) NOT NULL,
    Level VARCHAR(50) NOT NULL,
    Content LONGTEXT NOT NULL COMMENT 'JSON structuré',
    Description TEXT,
    CourseNumber INT,
    Slug VARCHAR(255) UNIQUE,
    Difficulty ENUM('facile','moyen','difficile') DEFAULT 'moyen',
    Duration INT DEFAULT 30 COMMENT 'Minutes',
    IsActive TINYINT(1) DEFAULT 1,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subject_level (Subject, Level),
    INDEX idx_slug (Slug)
);
learning_paths - Parcours pédagogiques
sql
CREATE TABLE learning_paths (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Title VARCHAR(255) NOT NULL,
    Subject VARCHAR(100) NOT NULL,
    Level VARCHAR(50) NOT NULL,
    Description TEXT,
    Duration INT DEFAULT 60,
    Difficulty ENUM('facile','moyen','difficile') DEFAULT 'moyen',
    Steps JSON NOT NULL COMMENT 'Étapes du parcours',
    Prerequisites JSON,
    IsActive TINYINT(1) DEFAULT 1,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subject_level (Subject, Level)
);
study_sessions - Sessions d'étude
sql
CREATE TABLE study_sessions (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    UserId INT NOT NULL,
    ResourceType ENUM('course','exercise','quiz','path') NOT NULL,
    ResourceId INT NOT NULL,
    StartedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    EndedAt TIMESTAMP NULL,
    Duration INT DEFAULT 0 COMMENT 'Secondes',
    Score INT NULL,
    CompletionRate INT DEFAULT 0 COMMENT 'Pourcentage',
    DeviceType VARCHAR(50) DEFAULT 'desktop',
    FOREIGN KEY (UserId) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (UserId),
    INDEX idx_date (StartedAt)
);
daily_stats - Statistiques quotidiennes
sql
CREATE TABLE daily_stats (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    UserId INT NOT NULL,
    Date DATE NOT NULL,
    TotalStudyTime INT DEFAULT 0,
    CoursesViewed INT DEFAULT 0,
    ExercisesCompleted INT DEFAULT 0,
    QuizzesTaken INT DEFAULT 0,
    AverageScore DECIMAL(5,2) DEFAULT 0,
    XPEarned INT DEFAULT 0,
    CristauxEarned INT DEFAULT 0,
    UNIQUE KEY (UserId, Date),
    FOREIGN KEY (UserId) REFERENCES users(id) ON DELETE CASCADE
);
Relations
text
users (1) ───< (N) study_sessions
users (1) ───< (N) daily_stats
users (1) ───< (N) user_path_progress

courses (1) ───< (N) exercises.LinkedCourses (JSON)
learning_paths (1) ───< (N) user_path_progress
📚 Système de cours
Structure JSON d'un cours
Fichier : db/json/schema/cours/schema_parsing_cours.json

json
{
  "title": "Titre du cours",
  "subject": "Mathématiques",
  "level": "3ème",
  "duration": 30,
  "difficulty": "moyen",
  "introduction": "Introduction engageante",
  "objectives": [
    "Objectif 1",
    "Objectif 2"
  ],
  "sections": [
    {
      "id": 1,
      "title": "Section 1",
      "type": "theory",
      "content": "Contenu détaillé",
      "examples": [
        {
          "input": "Exemple",
          "output": "Résultat",
          "explanation": "Explication"
        }
      ],
      "table": {
        "headers": ["Col1", "Col2"],
        "rows": [["A", "B"]]
      }
    }
  ],
  "key_points": [
    "Point clé 1",
    "Point clé 2"
  ],
  "resources": [
    {
      "type": "video",
      "title": "Titre",
      "url": "https://..."
    }
  ],
  "exercises_ids":[1][2][3]
}
Chargement d'un cours
Fichier : src/includes/course_markdown_loader.php

php
function getCourseById($courseId, $parseJson = true) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE Id = ? AND IsActive = 1");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        return null;
    }
    
    // Décoder le JSON si demandé
    if ($parseJson && !empty($course['Content'])) {
        $course['ContentParsed'] = json_decode($course['Content'], true);
    }
    
    return $course;
}
Affichage d'un cours
Fichier : src/pages/system/view_course.php

Le cours est affiché avec :

En-tête (titre, matière, niveau, badges)

Contenu structuré (sections, exemples, tableaux)

Points clés (encadré visuel)

Exercices liés (cartes cliquables)

Tracking automatique du temps passé

✏️ Système d'exercices
Structure JSON d'un exercice
json
{
  "Id": 1088,
  "Subject": "Anglais",
  "Level": "Terminale",
  "Title": "Email simple",
  "Content": "<p>You received an email...</p>",
  "Instruction": "Transform into reported speech",
  "Answer": "[\"She said that...\"]",
  "AnswerType": "texte",
  "Choices": null,
  "Tips": "Remember the backshift rule",
  "Domain": "Grammar",
  "Competence": "Reported Speech",
  "Difficulty": "moyen",
  "Identifier": "ANG-TERM-REPORT-001",
  "LinkedCourses": "",[2][1]
  "XPPoints": 20,
  "EstimatedTime": 10
}
Types d'exercices
Type	Description	Exemple
texte	Réponse libre	Rédaction, transformation
qcm	Choix unique	A, B, C ou D
qcm_multiple	Choix multiples	A+C, B+D, etc.
vrai_faux	Vrai ou Faux	True/False
association	Relier éléments	Drag & drop
ordre	Remettre dans l'ordre	1→2→3→4
Liaison cours ↔ exercices
Depuis un exercice : Afficher le(s) cours associé(s)

php
// Récupérer les cours liés
$linkedCourses = json_decode($exercise['LinkedCourses'], true);

if (!empty($linkedCourses)) {
    foreach ($linkedCourses as $courseId) {
        $course = getCourseById($courseId);
        // Afficher bouton "📖 Voir le cours"
    }
}
Depuis un cours : Afficher les exercices associés

php
function getExercisesForCourse($courseId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM exercises 
        WHERE JSON_CONTAINS(LinkedCourses, ?) 
        AND IsActive = 1
    ");
    $stmt->execute(['"' . $courseId . '"']);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
### 🧩 Rendu des cartes d’exercices (février 2026)

**Date (doc)** : 6 février 2026

Le rendu HTML des exercices s’appuie sur un composant unique `renderExerciseCard($exercise, $options)` qui génère une carte `<article>` accessible, repliable/dépliable via un bouton `.exercise-toggle` et un conteneur `.exercise-content` initialement `aria-hidden="true"`. [file:8]

#### Mode classique (réponse simple)
- L’énoncé est affiché via `cleanExerciseContent($content)`. [file:8]
- La zone de réponse est générée dans `<form class="exercise-form">` selon `AnswerType` et `Choices` (QCM radios, textarea, select, input texte). [file:8]
- Un bouton `.btn-check-answer` est présent côté UI pour déclencher la vérification. [file:8]

#### Mode multi-parties (sous-questions)
- Si `structure_type === 'multi-parties'` et que `sub_questions` contient un JSON valide, le composant bascule sur un rendu multi-blocs. [file:8]
- Chaque sous-question génère un champ `.sub-question-input` (sauf si `readOnly` est activé). [file:8]
- Un bouton `.btn-check-multi-parts` est prévu pour déclencher la vérification et afficher un feedback. [file:8]

#### Mode preview / lecture seule
- Si `options['readOnly']` est vrai, les champs de réponse sont remplacés par un message “Créez un compte pour répondre” / note d’aperçu. [file:8]

#### Progression & récompenses
- La progression est enregistrée côté front via `markExerciseComplete(exerciseId, correct)` qui appelle `api/save_exercise_progress.php` (POST) et met à jour l’UI (état terminé + animations XP/cristaux/badge). [file:8]

> Remarque : le bouton “Vérifier” (réponse) et l’enregistrement de progression sont deux étapes distinctes : “Vérifier” valide la réponse, puis `markExerciseComplete()` persiste la progression. [file:8]

🎯 Parcours pédagogiques
Concept
Un parcours = séquence d'activités pédagogiques structurées :

Cours : Apprentissage théorique

Quiz : Vérification compréhension

Exercice : Application pratique

Assessment : Évaluation finale

Structure JSON d'un parcours
json
{
  "id": 1,
  "title": "Maîtriser le Reported Speech",
  "subject": "Anglais",
  "level": "Terminale",
  "steps": [
    {
      "id": 1,
      "type": "course",
      "resource_id": 1,
      "title": "Introduction",
      "duration": 20,
      "is_mandatory": true,
      "completion_criteria": {
        "type": "read",
        "threshold": 100
      }
    },
    {
      "id": 2,
      "type": "quiz",
      "resource_id": 5,
      "title": "Quiz",
      "duration": 10,
      "completion_criteria": {
        "type": "score",
        "threshold": 70
      }
    }
  ],
  "rewards": {
    "xp": 500,
    "cristaux": 50,
    "badge_id": 15
  }
}
Progression utilisateur
Table : user_path_progress

sql
SELECT 
    CurrentStepIndex,
    CompletedSteps,
    Score,
    TimeSpent
FROM user_path_progress
WHERE UserId = ? AND PathId = ?
Calcul du pourcentage :

php
$progressPercentage = round((count($completedSteps) / count($totalSteps)) * 100);
📊 Tracking et statistiques
Sessions d'étude
Démarrage automatique : src/includes/study_tracker.php

php
$sessionId = startStudySession($userId, 'course', $courseId);
$_SESSION['current_study_session'] = $sessionId;
Heartbeat (toutes les 2 minutes) :

javascript
setInterval(() => {
    fetch('/api/update_session.php', {
        method: 'POST',
        body: JSON.stringify({
            session_id: sessionId,
            completion_rate: calculateScrollPercentage()
        })
    });
}, 2 * 60 * 1000);
Fin de session :

javascript
window.addEventListener('beforeunload', () => {
    navigator.sendBeacon('/api/end_session.php', JSON.stringify({
        session_id: sessionId,
        completion_rate: 100
    }));
});
Calcul de la streak
php
function calculateStreak($userId) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT Date FROM daily_stats 
        WHERE UserId = ? AND TotalStudyTime > 0
        ORDER BY Date DESC
    ");
    $stmt->execute([$userId]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $streak = 0;
    $currentDate = new DateTime();
    
    foreach ($dates as $date) {
        $studyDate = new DateTime($date);
        $diff = $currentDate->diff($studyDate)->days;
        
        if ($diff <= 1) {
            $streak++;
            $currentDate = $studyDate;
        } else {
            break;
        }
    }
    
    return $streak;
}
Heatmap d'activité
php
function getActivityHeatmap($userId, $year) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT Date, TotalStudyTime 
        FROM daily_stats
        WHERE UserId = ? 
          AND YEAR(Date) = ?
    ");
    $stmt->execute([$userId, $year]);
    
    $heatmap = [];
    while ($row = $stmt->fetch()) {
        $level = getHeatmapLevel($row['TotalStudyTime']);
        $heatmap[$row['Date']] = $level;
    }
    
    return $heatmap;
}

function getHeatmapLevel($seconds) {
    $minutes = $seconds / 60;
    if ($minutes === 0) return 0;
    if ($minutes < 15) return 1;
    if ($minutes < 30) return 2;
    if ($minutes < 60) return 3;
    return 4;
}
🔌 API Endpoints
POST /api/end_session.php
Termine une session d'étude.

Request :

json
{
  "session_id": 123,
  "score": 85,
  "completion_rate": 100
}
Response :

json
{
  "success": true,
  "session_id": 123,
  "duration": 1834,
  "completion_rate": 100,
  "message": "Session terminée avec succès"
}
POST /api/update_session.php
Met à jour une session en cours (heartbeat).

Request :

json
{
  "session_id": 123,
  "completion_rate": 45,
  "is_active": true
}
Response :

json
{
  "success": true,
  "session_id": 123,
  "completion_rate": 45
}
🛠️ Scripts utilitaires
Normalisation des exercices
Fichier : src/scripts/normalize_exercises.php

bash
# Analyser les problèmes
php src/scripts/normalize_exercises.php --dry-run

# Corriger automatiquement
php src/scripts/normalize_exercises.php --fix
Fonctions :

Ajoute les champs manquants (Identifier, EstimatedTime, XPPoints)

Valide le format JSON (Answer, Choices, LinkedCourses)

Génère des identifiants uniques

Calcule XP selon difficulté

Normalisation des cours
Fichier : src/scripts/normalize_courses.php

bash
php src/scripts/normalize_courses.php --dry-run
php src/scripts/normalize_courses.php --fix
Fonctions :

Vérifie la structure JSON du Content

Ajoute les champs obligatoires (objectives, sections, key_points)

Lie automatiquement les exercices

Valide les sections

Analyse pour Genspark
Fichier : src/scripts/analyze_exercises_full.php

bash
# Analyser toutes les matières
php src/scripts/analyze_exercises_full.php

# Analyser une matière spécifique
php src/scripts/analyze_exercises_full.php "Anglais" "Terminale"
Output :

db/json/cours/analysis_[date].json : Données structurées

db/json/cours/prompts/prompt_[competence].txt : Prompts Genspark

🤖 Génération de contenu IA
Workflow complet
text
1. ANALYSER exercices
   ↓
2. GÉNÉRER prompts Genspark
   ↓
3. OBTENIR réponse JSON de Genspark
   ↓
4. IMPORTER dans la base
   ↓
5. LIER automatiquement aux exercices
Prompt Genspark
Structure type :

text
Tu es un expert en pédagogie française pour [Matière] niveau [Niveau].

CONTEXTE:
- Compétence ciblée: [Competence]
- [N] exercices à couvrir
- Exemples d'exercices: [Liste avec consignes, conseils]

SCHÉMA JSON:
{
  "title": "...",
  "sections": [...],
  "key_points": [...]
}

CONSIGNES:
- Ton encourageant (tu/toi)
- Exemples concrets
- Explications progressives
- Anticiper les erreurs fréquentes

Réponds UNIQUEMENT avec le JSON valide.
Import des cours générés
bash
php src/scripts/import_genspark_courses.php db/json/cours/genspark_output.json
🔒 Sécurité
Sessions sécurisées
php
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true, // Production seulement
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);
Protection CSRF
php
// Génération
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Vérification
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token invalid');
}
Validation des inputs
php
$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
$level = in_array($_POST['level'], ['6ème', '5ème', ...]) ? $_POST['level'] : null;
Requêtes préparées
php
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

#### 🧯 Dépannage : l’élève ne peut pas répondre

**Date (doc)** : 6 février 2026

Si un élève “ne voit pas où répondre” ou “ne peut pas interagir”, vérifier dans cet ordre :

- **Le contenu est replié** : la zone de détails est rendue avec `.exercise-content` et `aria-hidden="true"` par défaut, donc sans JS/CSS de toggle actif, les champs peuvent être invisibles. [file:8]
- **Mode preview activé** : si `options['readOnly']` est vrai, les champs de réponse sont remplacés par un message d’aperçu (“Créez un compte pour répondre”) dans le mode classique et le mode multi-parties. [file:8]
- **Type de formulaire** : en mode classique, la zone de réponse dépend de `AnswerType` et de `Choices` (QCM radios, textarea, select, input texte), donc un `AnswerType` inattendu ou des `Choices` non décodés peuvent donner un rendu non interactif. [file:8]
- **Boutons de vérification sans handler** : la UI expose `.btn-check-answer` et `.btn-check-multi-parts`, mais il faut s’assurer qu’un script JS écoute ces boutons pour déclencher la correction/feedback. [file:8]
- **Progression ≠ correction** : la progression (XP/cristaux/badges) est enregistrée via `markExerciseComplete(exerciseId, correct)` qui POST vers `api/save_exercise_progress.php`; la “vérification de réponse” doit être gérée séparément puis appeler `markExerciseComplete()` en fonction du résultat. [file:8]

Astuce debug rapide :
- Inspecter la carte : vérifier `data-exercise-id`, puis vérifier si `.exercise-content` est bien visible (aria-hidden=false) et si `readOnly` n’est pas activé côté PHP. [file:8]

📞 Support
Pour toute question technique :

📧 dev@moncoachscolaire.fr

📖 Documentation : /docs

🐛 Issues : GitHub

Document mis à jour le : 4 février 2026

```

---

## Fichier : .github/PROJECT_CONTEXT.md

```file
# PROJECT_CONTEXT (index)

## Contexte produit
Lire : `.github/CONTEXT_PRODUIT.md`

## Règles IA / conventions de code
Lire : `.github/REGLES_IA.md`

## Roadmap / migrations
Lire : `.github/copilot-plan.md` (si présent)

## Règle de base
Toute demande de patch doit sortir :
- Fichiers impactés
- Diff
- Comment tester
- Hooks conservés/modifiés

```

---

## Git log (20 derniers commits)

```\nf123388 2026-02-09 docs: add context bundle verification step (npm run context) (Binwinwinw)
9eb507c 2026-02-09 chore(context): add CONTEXT_INDEX.md + script to build CONTEXT_BUNDLE.md and npm script 'context' (Binwinwinw)
678e018 2026-02-09 docs: add Version 2.2.2 entry documenting migration (footer, exercises hub, E2E, CI, stylelint) (Binwinwinw)
e99b678 2026-02-09 test(e2e): add exercises-link E2E tests + make Playwright baseURL configurable; run Playwright in CI (Binwinwinw)
e68ce99 2026-02-09 feat(nav): point course pages exercise links to central 'exercices' hub (Binwinwinw)
108c651 2026-02-09 feat(nav): route 'Exercices' CTAs to central page 'exercices' (landingpage, college accueil, bac guide, dashboard, footer) (Binwinwinw)
a5db59d 2026-02-09 fix(router): map 'contact' to 'users/contact' to avoid 404 for legacy /index.php?page=contact (Binwinwinw)
a39ba8b 2026-02-09 fix(footer): use 'eleve/bac/bac-accueil' for Préparer le Bac link to match static fragment (Binwinwinw)
2eca51f 2026-02-09 chore(static): inject updated footer fragment into static HTML pages (add Préparer le Bac link) (Binwinwinw)
bf57112 2026-02-09 feat(footer): add 'Préparer le Bac' link to quick links (component + static fragment) (Binwinwinw)
1ef4df5 2026-02-09 fix(bac): initialize  to avoid undefined variable warning (Binwinwinw)
fa9f00a 2026-02-09 stylelint: allow BEM classes in components via override (Binwinwinw)
c3a9a4a 2026-02-09 stylelint: remove duplicate property in footer.css (Binwinwinw)
be88bbf 2026-02-09 stylelint: ignore generated and vendor CSS (tailwind, style.css) (Binwinwinw)
128fd02 2026-02-09 stylelint: fix syntax error in style.css (Binwinwinw)
4bc57c6 2026-02-09 feat: inject footer fragment into static HTML; add footer CSS and fragment (Binwinwinw)
877c89c 2026-02-09 stylelint: auto-fix CSS formatting issues (partial) (Binwinwinw)
73588bc 2026-02-09 ci: add Node setup, run build:includes and stylelint in CI (Binwinwinw)
3268490 2026-02-08 feat: add footer animations and reusable footer component (Binwinwinw)
71d8b7c 2026-02-08 feat: ajouter la page d'atterrissage avec gestion des sessions et affichage dynamique (Binwinwinw)\n```\n\n---

## Usage rapide

- Regénérer : `npm run context`
- Fichier de sortie : `CONTEXT_BUNDLE.md`

