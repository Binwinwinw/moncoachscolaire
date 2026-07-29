## Sécurité des commits

Un hook pre-commit protège contre la fuite de fichiers sensibles. Voir [dev/tools/git-hooks/README_git-hooks.md](dev/tools/git-hooks/README_git-hooks.md)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md#exemples-dusage)

<!-- Déduplication appliquée le 2026-02-14 : Exemples d'usage déplacés vers dev/tools/README.md, remplacés ici par une référence croisée. -->

## 🏗️ Architecture

> 📖 **Pour en savoir plus :** Voir [dev/reports/pages_inventory.md](dev/reports/pages_inventory.md)

<!-- Déduplication appliquée le 2026-02-14 : Inventaire technique supprimé, remplacé par une référence croisée vers dev/reports/pages_inventory.md. -->

### Documentation complète et outils/scripts

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste des scripts/outils/tests supprimée, remplacée par une référence croisée unique. -->

# 🎓 MonCoachScolaire

> Plateforme éducative interactive pour l'accompagnement scolaire du collège au lycée

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Status](https://img.shields.io/badge/status-active-success)](https://github.com)

---

## 🗓️ Mise à jour documentation — 12 février 2026

Cette mise à jour documente l’état réel du projet **sans supprimer l’historique** :

- Arborescence actuelle (src/, dev/tools/, src/api/\*)
- Emplacements réels des scripts d’import/export
- Rappels sur le système hybride des cours (Markdown ↔ BDD)
- Inventaires techniques disponibles (`dev/reports/pages_inventory.md`, `dev/reports/api_inventory.md`, `dev/reports/hooks_inventory.md`)
- Synchronisation de la roadmap Copilot avec les livrables déjà présents

Entrées datées: la trace opérationnelle continue est tenue dans `dev/JOURNAL_REPRISE.md` pour éviter les doublons dans ce README.

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

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md#exemples-dusage)

<!-- Déduplication appliquée le 2026-02-14 : Exemples d'usage déplacés vers dev/tools/README.md, remplacés ici par une référence croisée. -->

Accédez à : **http://localhost:8000**

---

## ✨ Fonctionnalités principales

🎯 **Générateur d'exercices interactifs** couvrant 8 niveaux (6ème → BAC)  
📚 **Générateur de Cours structurés** par matière et chapitre  
👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)  
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

| Métrique            | Valeur                            |
| ------------------- | --------------------------------- |
| **Exercices**       | 1088                              |
| **Niveaux**         | 8 (6ème → BAC)                    |
| **Matières**        | 9 (Math, Français, Anglais, etc.) |
| **Langues**         | Français                          |
| **Backend**         | PHP 8+                            |
| **Base de données** | MySQL/MariaDB                     |

---

> 📖 **Pour en savoir plus :** Voir [dev/reports/pages_inventory.md](dev/reports/pages_inventory.md), [dev/reports/api_inventory.md](dev/reports/api_inventory.md), [dev/reports/hooks_inventory.md](dev/reports/hooks_inventory.md)

<!-- Déduplication appliquée le 2026-02-14 : Inventaire technique supprimé, remplacé par des liens directs vers les inventaires sources. -->

---

## 🎯 Utilisation

### Accès aux dashboards

| Rôle      | URL                     | Identifiants par défaut |
| --------- | ----------------------- | ----------------------- |
| 🔧 Admin  | `/dashboard_admin.php`  | admin / admin123        |
| 👨‍👩‍👧 Parent | `/dashboard_parent.php` | parent1 / pass123       |
| 🎓 Élève  | `/dashboard.php`        | demo / demo123          |

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

```bash
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

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md#exemples-dusage)

<!-- Déduplication appliquée le 2026-02-14 : Exemples d'usage déplacés vers dev/tools/README.md, remplacés ici par une référence croisée. -->

Accédez à : **http://localhost:8000**

---

## 🔑 Source de vérité exercices

> **Depuis le 21/02/2026, le fichier db/all_exercises_clean_enriched.normalized.json.sql est la source de vérité pour tous les exercices.**
> Toute extraction, migration ou audit doit utiliser ce fichier.
> Les anciens fichiers JSON ou SQL sont obsolètes.

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

## 🟢 Migration TailwindCSS (février 2026)

Toutes les pages principales élèves sont désormais migrées Tailwind : palette stricte, hooks JS/tests conservés, responsive, audit hooks/pages à jour.

Pour le détail, voir :

- [DOCUMENTATION.md](DOCUMENTATION.md#🟢-migration-tailwindcss--16-février-2026)
- [dev/reports/pages_inventory.md](dev/reports/pages_inventory.md)
- [dev/reports/hooks_inventory.md](dev/reports/hooks_inventory.md)
- [dev/reports/css_migration_plan.md](dev/reports/css_migration_plan.md)

## Audit de code avec gitnexus

Pour analyser la qualité et la structure du projet, lancez :

```sh
npx gitnexus analyze
```

- Le rapport s’affiche dans le terminal.
- Pour automatiser : ajoutez un script npm ou un hook git si besoin.
