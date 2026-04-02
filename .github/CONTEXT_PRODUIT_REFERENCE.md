---
**Ajout du 07/03/2026 :** Ajout de la référence au suivi des bugs et améliorations pour la reprise opérationnelle.

**Ajout du 01/03/2026 : Synthèse d’avancement et validation des lots**

### Lots validés (FAIT)
- Migration Tailwind sur landing page, guides collège/lycée/bac, dashboard élève, dashboard parent, dashboard admin, maintenance
- Harmonisation UI sur cards “niveau” (élèves connectés/admin), boutons guides/exercices/cours collège
- Correction des routes/pages lycée/bac pour tests Playwright
- Correction des IDs/sélecteurs HTML pour compatibilité tests
- Documentation enrichie dans JOURNAL_REPRISE.md et CONTEXT_PRODUIT.md

### Lots à terminer (EN COURS)
- Finaliser migration Tailwind sur pages secondaires (cours, progression, autres dashboards)
- Harmoniser UI sur composants réutilisables (boutons, cards, badges, notices)
- Relancer et reporter les tests Playwright après 80% de complétion
- Documenter l’avancement à chaque étape (journal, doc technique)

---
> 📖 Pour la synthèse complète, relire tous les journaux et guides techniques : [dev/JOURNAL_REPRISE.md](../dev/JOURNAL_REPRISE.md), [dev/SUIVI_BUGS_AMELIORATIONS.md](../dev/SUIVI_BUGS_AMELIORATIONS.md), [copilot-plan.md](copilot-plan.md), [dev/docs/INDEX.md](../dev/docs/INDEX.md)

## Parsers 1122 exercices [19/02/2026]

**Ajout du 25/02/2026 :** Signalement de l'intégration des pages lycée et bac dans les tests Playwright, impacts sur la structuration des exercices, la validation et la documentation. Voir aussi [docs/SESSION_REPORT_2026.md](../docs/SESSION_REPORT_2026.md) pour le tableau de bord tests.
**Ajout du 19/02/2026 :** Section détaillée sur l'intégration des parsers d'exercices, le script CLI, les fichiers impactés et les outputs (voir ci-dessous).

_(Historique : amorcé en février 2026)_

### Objectif
Automatiser la structuration et la validation des exercices en base via des parsers modulaires (matière/type), robustes et extensibles.

### Architecture & Emplacement des fichiers
- **Registre de parsers** : `dev/tools/exercises/Parsers/ParserRegistry.php`
- **Parsers spécialisés** :
    - Mathématiques : `dev/tools/exercises/Parsers/MathParser.php`
    - Français : `dev/tools/exercises/Parsers/FrancaisParser.php`
    - Générique (fallback) : `dev/tools/exercises/Parsers/GenericParser.php`
- **Interface** : `dev/tools/exercises/Parsers/ExerciceParserInterface.php`
- **Script d'intégration CLI** : `dev/tools/exercises/Parsers/integrate_parsers.php`

### Fonctionnement du script CLI
- **Chemin** : `dev/tools/exercises/Parsers/integrate_parsers.php`
- **Usage** :
    - Exécuter : `php dev/tools/exercises/Parsers/integrate_parsers.php`
    - Charge tous les exercices depuis la table `exercises` (MySQL)
    - Pour chaque exercice :
        - Sélectionne le parser adapté selon la matière/type
        - Parse le JSON (Subject, Content, Type, Level, Title)
        - Met à jour les champs `structure_type` et `sub_questions` en base
    - Affiche la progression (tous les 100 ex.) et un résumé final

### Fichiers impactés
- **Base de données** : Table `exercises` (champs `structure_type`, `sub_questions`)
- **Scripts** : Tous les fichiers du dossier `dev/tools/exercises/Parsers/`
- **Sauvegardes** : Export SQL conseillé avant chaque batch (voir `db/`)

### Output attendu
- **Console** :
    - Affiche la progression : `✅ 100/1122`, `✅ 200/1122`, ...
    - Résumé final : `🎉 1122 exercices intégrés !`
- **Base de données** :
    - Champs `structure_type` et `sub_questions` renseignés pour chaque exercice
- **Logs/erreurs** :
    - Toute erreur de parsing ou d'intégrité est affichée en console (robustesse accrue)

### Bonnes pratiques
- Toujours sauvegarder la base avant batch massif
- Ajouter tout nouveau parser dans `ParserRegistry`
- Documenter les cas limites et le mapping dans ce fichier

> 📖 Pour la logique détaillée et les exemples de code, voir aussi [dev/tools/README.md](../dev/tools/README.md)
## Migration UI/UX guides collège, lycée, bac (février 2026)

### Objectif
Harmoniser la présentation de tous les guides de remédiation (collège, lycée, bac) : suppression des cards accordéon, ajout grille de boutons matières avec modales accessibles, bloc « Plan d’action personnalisé » modernisé, palette couleur adaptée à chaque niveau.

### Principes
- Palette couleur par niveau : vert (collège), violet (lycée), doré (bac)
- Modales accessibles (focus, aria-modal, navigation clavier)
- Hooks front conservés (classes, ids, data-attributes)
- Bloc plan d’action harmonisé, boutons d’inscription/connexion
- Centrage du titre sur la page BAC

### Fichiers impactés
- src/pages/eleve/college/*/guide-remediation.php
- src/pages/eleve/lycee/2nde/guide-remediation.php
- src/pages/eleve/lycee/1ere/guide-remediation.php
- src/pages/eleve/lycee/terminale/guide-remediation.php
- src/pages/eleve/bac/guide-remediation.php

### Accessibilité
Focus automatique sur la modale, fermeture ESC ou clic fond, aria-modal, navigation clavier.

### Test
Vérifier l’affichage, la cohérence des couleurs, l’accessibilité, les hooks JS.

### Rollback
Restaurer les versions précédentes des fichiers guides-remediation.php concernés.

---
**Ajout du 27/02/2026 : Harmonisation UI cards “niveau” validée**
Les cards “niveau” pour élèves connectés et admin sur les pages Collège, Lycée, Bac sont harmonisées :
- Palette adaptée (vert, violet, doré), structure Tailwind, boutons cohérents
- Structure responsive et accessible (contrastes, focus, aria)
- Hooks front conservés
- Conformité validée sur collège-accueil.php, lycee-accueil.php, bac-accueil.php

Ce point est marqué comme FAIT dans la liste d’avancement.

---
**Ajout du 28/02/2026 : Checklist accessibilité Lycée-Accueil validée**
- Contrastes : palette violette, boutons et texte conformes AA+
- Navigation clavier : tous les boutons et liens accessibles au Tab
- Focus visible : bien marqué sur chaque bouton
- Attributs ARIA : à compléter sur modales si manquant
- Fermeture modale : ESC et clic fond fonctionnels
- Labels explicites : tous les boutons et liens
- Responsive : affichage correct sur tous supports
- Hooks front conservés
- Test automatisé : script accessibilité à lancer

Page suivante à vérifier : Bac-Accueil

---
**Ajout du 28/02/2026 : Checklist accessibilité Bac-Accueil validée**
- Contrastes : palette dorée, boutons et texte conformes AA+
- Navigation clavier : tous les boutons et liens accessibles au Tab
- Focus visible : bien marqué sur chaque bouton
- Attributs ARIA : à compléter sur modales si manquant
- Fermeture modale : ESC et clic fond fonctionnels
- Labels explicites : tous les boutons et liens
- Responsive : affichage correct sur tous supports
- Hooks front conservés
- Test automatisé : script accessibilité à lancer

Page suivante à vérifier : guides-remediation
MonCoachScolaire — Contexte et Documentation Projet
=====================================================

📝 Enrichissement documentaire : où documenter ?
-------------------------------------------------
Pour toute documentation ou guide technique, enrichir le fichier adapté selon le type :

- Contexte produit, workflows métier, philosophie, architecture : **CONTEXT_PRODUIT.md**
- Roadmap technique, jalons, lots de travail : **copilot-plan.md**
- Conventions IA, patterns, règles de patch : **REGLES_IA.md**
- Index rapide, centralisation des fichiers essentiels : **PROJECT_CONTEXT.md** et **CONTEXT_INDEX.md**
- Documentation utilisateur ou technique générale : **README.md** et **DOCUMENTATION.md**

Règle anti-doublon : ne jamais dupliquer l’information, toujours référencer le fichier source.

Exemple :
> 📖 Pour en savoir plus : Voir [README.md](../README.md) ou [DOCUMENTATION.md](../DOCUMENTATION.md)

📖 Table des matières
Objectif et Philosophie

Workflows Essentiels

Architecture et Stack Technique

Schéma des Exercices

Dashboards et Rôles

Bonnes Pratiques UX/Pédagogiques

Maintenance et Sécurité

Contribution

🎯 Objectif et Philosophie
MonCoachScolaire est une plateforme éducative conçue pour accompagner les élèves du collège au lycée dans leur apprentissage. L'application propose des exercices interactifs, un système de progression gamifié (XP), et des dashboards distincts pour trois types d'utilisateurs : élèves, parents et administrateurs.

Valeurs fondamentales
Bienveillance : Chaque interaction doit encourager et motiver l'élève

Autonomie : L'élève est acteur de son apprentissage

Progression : Suivi personnalisé et valorisation des efforts

Positivité : Jamais de messages négatifs ou décourageants

Principes pédagogiques
Mémorisation active : Répétition espacée et révision ciblée

Feedback immédiat : Correction instantanée avec explications

Gamification : Système XP, badges, niveaux pour maintenir la motivation

Personnalisation : Adaptation au niveau et au rythme de chaque élève

Accompagnement parental : Suivi de progression sans pression

🔄 Workflows Essentiels
1. Installation & Initialisation
Objectif : Mettre en place l'environnement de développement local

Étapes d'installation
Cloner le projet

bash
git clone https://github.com/votre-repo/moncoachscolaire.git
cd moncoachscolaire
Configuration de l'environnement

Installer XAMPP/WAMP (Windows) ou MAMP (Mac)

Démarrer Apache et MySQL

Créer une base de données moncoachscolaire

Configuration BDD

Copier config/config.example.php vers config/config.php

Modifier les paramètres de connexion :

php
$host = 'localhost';
$dbname = 'moncoachscolaire';
$username = 'root';
$password = '';
Importer le schéma

bash
mysql -u root -p moncoachscolaire < public/database/schema.sql
Créer un compte administrateur

Accéder à /register.php

Créer un compte

Modifier le rôle en BDD : UPDATE users SET role = 'admin' WHERE UserID = 1;

Lancer l'application

Accéder à http://localhost/moncoachscolaire/public/

Se connecter avec le compte admin

2. Création, Import et Validation d'Exercices
Objectif : Alimenter la base d'exercices avec du contenu de qualité

Workflow de création d'exercices
Rédaction de l'exercice

Utiliser le format JSON standardisé (voir section Schéma)

Respecter tous les champs obligatoires

Valider la cohérence avec le programme scolaire

Validation du JSON

Vérifier la syntaxe JSON (JSON validator)

S'assurer que tous les champs sont présents

Tester la logique de l'exercice

Import en base de données

Via l'interface admin : Dashboard Admin > Gestion des Exercices

Ou via script : php scripts/import_exercises.php fichier.json

Contrôle qualité

Dashboard Admin > Qualité exercices

Vérifier les exercices importés

Corriger les incohérences

Activer les exercices validés

Export et sauvegarde

Exporter régulièrement les exercices validés

Garder une copie JSON à jour

Versionner les exercices (Git)

Format JSON d'un exercice
Voir la section Schéma des Exercices pour le format complet.

Schéma de la base de données (Tables Clés)

Table `courses` (Structure 2026)
- **id**: INT (PK)
- **level**: ENUM ('6eme', '5eme', ...)
- **subject**: VARCHAR (Matière)
- **competence**: VARCHAR (Compétence clé, ex: "Thorème de Thalès")
- **section**: VARCHAR (Section du chapitre)
- **key_point**: TEXT (Définition principale)
- **formula**: VARCHAR (Formule mathématique ou règle grammaire)
- **example**: TEXT (Exemple concret tiré d'un exercice)
- **slug**: VARCHAR (Unique, pour URL friendly)
- **is_active**: BOOLEAN

Table `exercisecourselinks`
- **Id**: INT (PK)
- **ExerciseId**: INT (FK -> exercises)
- **CourseId**: INT (FK -> courses)
- **LinkType**: VARCHAR (ex: 'theory', 'practice')

3. Utilisation des Dashboards
Objectif : Comprendre les différents dashboards et leurs fonctionnalités

3.1 Dashboard Élève (dashboard.php)
Accès : Tous les utilisateurs avec rôle student

Fonctionnalités :

Vue d'ensemble de la progression (XP, niveau, badges)

Liste des matières et exercices disponibles

Historique des exercices réalisés

Statistiques personnelles (taux de réussite, temps passé)

Accès direct aux exercices par matière/niveau

Système de récompenses et d'encouragements

Navigation :

L'élève se connecte

Accède à son dashboard

Choisit une matière

Sélectionne un exercice

Répond à l'exercice

Reçoit un feedback immédiat

Gagne des XP en cas de réussite

Peut refaire l'exercice ou continuer

3.2 Dashboard Parent (dashboard_parent.php)
Accès : Utilisateurs avec rôle parent et enfants rattachés

Fonctionnalités principales :

Suivi de progression

Progression détaillée par enfant

Statistiques par matière et compétence

Évolution dans le temps (graphiques)

Comparaison avec les objectifs fixés

Alertes et encouragements

Notifications positives sur les réussites

Suggestions d'activités complémentaires

Rappels bienveillants (sans pression)

Célébration des badges et niveaux atteints

Suggestions pédagogiques

Exercices à refaire ensemble

Ressources complémentaires (vidéos, guides)

Conseils adaptés au niveau de l'enfant

Activités ludiques pour renforcer les acquis

Historique et récompenses

Liste des badges obtenus

Historique des réussites

Points forts identifiés

Domaines à encourager

Communication

Messagerie avec le support pédagogique (optionnel)

Espace pour féliciter l'enfant

Partage d'objectifs familiaux

Ressources parentales

Guides pour accompagner l'apprentissage

Astuces pour aider sans faire à la place

Conseils sur l'organisation du travail

FAQ pédagogique

Paramètres et préférences

Fréquence des notifications

Objectifs personnalisés

Gestion des enfants rattachés

Principes du dashboard parent :

✅ Toujours positif et encourageant

✅ Mettre en avant les réussites

✅ Suggestions constructives (pas de jugement)

✅ Favoriser l'autonomie de l'enfant

❌ Jamais de comparaison négative

❌ Pas de pression excessive

❌ Éviter le contrôle obsessionnel

Workflow d'implémentation (dashboard parent) :

Phase 1 : Recueil des besoins

Interviews parents

Tests utilisateurs

Analyse des attentes

Phase 2 : Prototypage

Maquettes UI/UX

Parcours utilisateur

Validation avec des parents testeurs

Phase 3 : Développement

Module de suivi de progression

Système d'alertes positives

Générateur de suggestions

Historique et statistiques

Phase 4 : Tests et ajustements

Tests d'affichage multi-enfants

Validation de la personnalisation

Tests de performance

Phase 5 : Déploiement

Documentation utilisateur

Tutoriel intégré

Support dédié

Phase 6 : Amélioration continue

Collecte de feedbacks

Ajustements UX

Nouvelles fonctionnalités

3.3 Dashboard Admin (dashboard_admin.php)
Accès : Utilisateurs avec rôle admin

Fonctionnalités :

Vue d'ensemble

Statistiques globales (utilisateurs, exercices, activité)

Graphiques de progression

Activité récente

État du système

Gestion des utilisateurs

CRUD utilisateurs (Create, Read, Update, Delete)

Attribution des rôles

Rattachement parents-élèves

Gestion des accès

Gestion des exercices

Import/export d'exercices

Activation/désactivation

Modification des exercices

Statistiques d'utilisation

Analyse qualité

Contrôle qualité des exercices

Détection d'incohérences

Taux de réussite par exercice

Feedbacks élèves

Logs et monitoring

Logs système

Actions administratives

Erreurs et alertes

Performance

Configuration système

Mode maintenance

Mode debug

Paramètres généraux

Sauvegarde et restauration

4. Correction, Feedback et Progression
Objectif : Fournir un feedback immédiat et encourageant à l'élève

Processus de correction
Soumission de la réponse

L'élève soumet sa réponse via l'interface

Envoi à l'API api/student/submit_answer.php

Comparaison avec la réponse attendue

Récupération du champ Answer de l'exercice

Comparaison selon le AnswerType

Calcul du score (0-100%)

Génération du feedback

Si réponse correcte :

Message positif : "🎉 Bravo ! Excellente réponse !"

Attribution des XP_Points

Mise à jour de la progression

Déverrouillage potentiel de badges

Proposition d'exercice suivant

Si réponse incorrecte :

Message encourageant : "💪 Presque ! Tu y es presque !"

Affichage d'un indice (champ Tips)

Lien vers le cours associé

Proposition de réessayer

Pas de pénalité XP

Mise à jour de la progression

Enregistrement de la tentative

Calcul du taux de réussite

Mise à jour des statistiques

Notification aux parents (si activé)

Ressources complémentaires

Accès au cours lié

Exercices similaires

Vidéos explicatives

Fiches récapitulatives

Règles de feedback
TOUJOURS encourager, même en cas d'erreur

JAMAIS de message négatif ("Faux", "Raté", "Insuffisant")

Fournir des indices progressifs

Permettre de réessayer sans limite

Célébrer chaque réussite

Valoriser l'effort, pas seulement le résultat

5. Maintenance & Sauvegarde
Objectif : Assurer la pérennité et la sécurité des données

Workflows Spéciaux : Génération de Cours
Objectif : Lier automatiquement les exercices aux cours et créer les cours manquants.

Commande :
`php dev/tools/courses/link_exercises_to_courses.php`

Fonctionnement :
1. Analyse chaque identifiant d'exercice (ex: `MATHS-6EME-ADDITION-001`).
2. Cherche un cours existant correspondant au triplet Matière/Niveau/Compétence.
3. Si trouvé : Crée un lien dans `exercisecourselinks`.
4. Si non trouvé : Ajoute à une liste d'attente et génère un fichier SQL dans `dev/db/` pour créer les cours manquants.

Sauvegarde de la base de données
Fréquence recommandée : Quotidienne (automatisée)

Commande de sauvegarde :

bash
mysqldump -u root -p moncoachscolaire > backup_$(date +%Y%m%d).sql
Restauration :

bash
mysql -u root -p moncoachscolaire < backup_20260201.sql
Mode maintenance
Activation :

Dashboard Admin > Configuration Système

Activer "Mode Maintenance"

Seuls les admins peuvent accéder au site

Désactivation :

Effectuer les opérations nécessaires

Désactiver "Mode Maintenance"

Vérifier que le site est accessible

Bonnes pratiques
✅ Toujours créer un backup avant modification de masse

✅ Tester les restaurations régulièrement

✅ Conserver plusieurs versions de backup (7 jours minimum)

✅ Sauvegarder aussi les fichiers uploadés

✅ Versionner le code avec Git

✅ Documenter chaque intervention majeure

6. Sécurité & Bonnes Pratiques
Objectif : Protéger les données et garantir la sécurité de l'application

Règles de sécurité
Authentification et sessions

Mots de passe hashés (bcrypt/argon2)

Sessions sécurisées (httponly, secure)

Timeout de session approprié

Protection contre le brute-force

Injection SQL

TOUJOURS utiliser prepared statements

JAMAIS de concaténation de requêtes SQL

Validation des types de données

XSS (Cross-Site Scripting)

Échapper les sorties : htmlspecialchars()

Validation des inputs

Content Security Policy

CSRF (Cross-Site Request Forgery)

Tokens CSRF pour toutes les actions sensibles

Vérification du referer

SameSite cookies

Gestion des fichiers

.gitignore pour les fichiers sensibles

Pas de config.php versionné

Permissions fichiers appropriées

Validation des données

Côté serveur (prioritaire)

Côté client (confort UX)

Whitelist > Blacklist

Standards de code
PHP : PSR-12 (PHP Standards Recommendations)

JavaScript : ESLint avec config ES6+

CSS : Prettier pour le formatage

Indentation : 4 espaces (pas de tabs)

Tests
Tests unitaires pour la logique critique

Tests d'intégration pour les APIs

Tests de sécurité (injections, XSS)

Tests de performance

7. Contribution & Documentation
Objectif : Faciliter la collaboration et la maintenance du projet

Workflow de contribution
Fork du projet

bash
git clone https://github.com/votre-username/moncoachscolaire.git
Création d'une branche

bash
git checkout -b feature/nouvelle-fonctionnalite
Développement

Respecter les conventions de code

Ajouter des tests si nécessaire

Documenter les changements

Commit

bash
git add .
git commit -m "feat: ajout de [fonctionnalité]"
Format des commits :

feat: nouvelle fonctionnalité

fix: correction de bug

docs: documentation

style: formatage

refactor: refactoring

test: ajout de tests

Push et Pull Request

bash
git push origin feature/nouvelle-fonctionnalite
Créer une PR sur GitHub

Décrire les changements

Attendre la review

Review et merge

Review par un mainteneur

Corrections si nécessaire

Merge dans la branche principale

Documentation
Documenter toute nouvelle fonctionnalité

Mettre à jour ce fichier si nécessaire

Ajouter des commentaires dans le code

Créer des guides utilisateurs si pertinent



## UI : Guides de remédiation lycée (2026)

### Migration harmonisée (février 2026)
Tous les guides de remédiation lycée (Seconde, Première, Terminale) ont été migrés sur le nouveau design :
- Modales matières accessibles
- Grille de boutons matières
- Bloc “Plan d’action personnalisé” harmonisé
- Responsive et accessibilité validés
- Hooks front conservés

#### État d’avancement (février 2026)
- Seconde : terminé et validé
- Première : terminé et validé
- Terminale : terminé et validé

➡️ Guides lycée harmonisés, design unifié, prêts pour le bac.

---
## UI : Guides de remédiation collège (2026)

### Nouvelle refonte design (février 2026)
Une refonte complète de l’UI des guides de remédiation collège a été engagée pour améliorer l’accessibilité, la clarté et l’engagement des élèves. Le nouveau design s’appuie sur :
- Une palette verte harmonisée (inspirée de la landing page)
- Des boutons arrondis, accessibles, avec feedback visuel
- Des blocs matières sous forme de boutons ouvrant un modal explicatif (plus de cards déroulantes)
- Un bloc “Plan d’action personnalisé” incitant à la création de compte
- Compatibilité mobile et responsive systématique
- Hooks front conservés pour la compatibilité JS

#### État d’avancement (février 2026)
- Guide remédiation collège 6ème : terminé et validé (UI, modales, accessibilité, bloc personnalisé)
- Guides 5ème, 4ème, 3ème : à migrer selon le même pattern (en cours)

> 📖 Pour la roadmap détaillée : voir [.github/copilot-plan.md](copilot-plan.md)

#### Principes à respecter
- Toujours privilégier la bienveillance et l’encouragement dans les messages
- Ne jamais dupliquer l’information : mutualiser les composants dès que possible
- Tester l’accessibilité (focus, contraste, navigation clavier)
- Conserver les hooks existants pour éviter toute régression JS

#### Plan de test
- Vérifier l’ouverture des modales sur chaque bouton matière
- Vérifier l’affichage du bloc “Plan d’action personnalisé”
- Vérifier le responsive sur mobile/tablette
- Vérifier la non-régression des hooks front

#### Risques
- Faible : migration incrémentale, rollback possible page par page

---

## UI : Topbar et Footer

### Topbar
- La topbar affiche le titre "MonCoachScolaire" et un badge indiquant le rôle ou le niveau d’aventure de l’utilisateur (élève, parent, admin).
- Le badge est généré dynamiquement selon la session :
    - Parent : badge "Parents"
- .topbar, .topbar-logo, .topbar-title, .topbar-role-badge
- .footer-logo, .brand-title

#### Plan de test
- Vérifier l’affichage du badge selon le rôle et le niveau.
- Vérifier l’alignement du logo et du texte dans le footer.

#### Risques
- Faible : hooks conservés, pas de rupture JS/CSS.
- Rollback : restaurer les fichiers topbar.php et footer_component.php.

#### Conventions couleur badge
- Couleur douce : bg-slate-100, text-slate-700, border-slate-300
- Jamais de couleur flashy ou fluo

Dernière mise à jour : 2026-02-11
