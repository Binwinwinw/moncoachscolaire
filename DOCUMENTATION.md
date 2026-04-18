# 📊 TABLEAU DE BORD & SUIVI DU PROJET

> [!IMPORTANT]
> Pour consulter l'état d'avancement réel, les blocages et les améliorations prioritaires, se référer aux fichiers de bord dynamiques :
>
> - 📅 **Journal de Reprise** : [dev/JOURNAL_REPRISE.md](dev/JOURNAL_REPRISE.md) (Historique daté des interventions)
> - 🐛 **Suivi Bugs & Améliorations** : [dev/SUIVI_BUGS_AMELIORATIONS.md](dev/SUIVI_BUGS_AMELIORATIONS.md) (Backlog actif et priorités)

## [02/04/2026] Pivot produit — Quiz AI comme voie principale

Decision validee:

- Le bouton **Quiz AI** cote eleve devient la voie standard de generation de quiz.
- La generation manuelle massive par scripts n'est plus un objectif produit.
- Les scripts quiz restent utiles pour maintenance/migration/dry-run, mais ne pilotent plus la strategie pedagogique.

Contexte qualite retenu:

- Le lot historique de 1643 quiz a montre un ecart majeur entre volume et qualite (placeholders, contenu peu exploitable en production).
- La priorite produit est recentree sur la qualite reelle pour l'eleve: quiz dynamiques pertinents, nouveaux exercices, nouveaux cours.

Sources de verite:

- [dev/JOURNAL_REPRISE.md](dev/JOURNAL_REPRISE.md)
- [dev/SUIVI_BUGS_AMELIORATIONS.md](dev/SUIVI_BUGS_AMELIORATIONS.md)

## [18/04/2026] Gouvernance quiz runtime — nettoyage et fiabilisation

Mise à jour documentaire :

- le template de génération Python a été réaligné sur la structure JSON réellement consommée par l'application
- les sorties validées du lot pilote ont été recopiées dans les données runtime
- une campagne de nettoyage a supprimé les quizzes placeholders manifestes encore présents dans la base applicative

À retenir :

- pour l'état d'avancement, suivre [dev/SUIVI_BUGS_AMELIORATIONS.md](dev/SUIVI_BUGS_AMELIORATIONS.md)
- pour l'historique détaillé des interventions, suivre [dev/JOURNAL_REPRISE.md](dev/JOURNAL_REPRISE.md)
- les futurs générateurs doivent partir des templates du dossier quiz generator déjà réalignés
- **pattern de fin de lot quiz** : après vérification visuelle et approbation, copier les fichiers vers `src/data/quiz/` et `src/data/quiz_answers/`, puis supprimer le dossier temporaire de génération pour laisser le dépôt propre

## [02/04/2026] Priorites actives pour la suite

Le tableau de pilotage a ete consolide dans [dev/SUIVI_BUGS_AMELIORATIONS.md](dev/SUIVI_BUGS_AMELIORATIONS.md) avec trois sections stables : priorites actives, realise, abandonne / remplace.

Axes actifs a retenir:

- robustesse Quiz AI
- anti-repetition quiz
- smoke test E2E connecte
- nouveaux exercices et nouveaux cours
- mode sombre (a cadrer)

Regle documentaire: le detail des statuts reste dans [dev/SUIVI_BUGS_AMELIORATIONS.md](dev/SUIVI_BUGS_AMELIORATIONS.md). Les autres documents gardent uniquement un resume de pilotage et un lien vers cette source.

## [04/04/2026] Architecture cible - Cours IA cibles et explications d'exercices

Objectif pedagogique:

- reutiliser le procede Quiz AI pour produire des contenus vraiment utiles a la reussite
- separer clairement le besoin "mini-cours cible" du besoin "explication de correction"
- permettre un parcours de progression vers 100% sans transformer l'IA en correcteur metier

Decision d'architecture:

- l'IA peut etre auteur pedagogique et reformulateur, mais pas source de verite pour les reponses des exercices existants
- la correction officielle reste cote serveur, a partir des donnees de reference du projet (BDD, JSON prives, logique metier)
- l'IA intervient ensuite pour expliquer, reformuler, guider la reprise et proposer un mini-entrainement cible

Separations fonctionnelles a respecter:

### 1) Mini-cours IA cible

Usage:

- generer un cours court et precis a partir d'une notion ou competence reelle
- exemple: "developper et reduire une expression litterale en 4eme" plutot que "faire un cours de mathematiques 4eme"

Contrat attendu:

- entree: niveau, matiere, competence, notion, exercice_source optionnel
- sortie: titre, objectif, prerequis, explication structuree, methode, exemple, erreurs frequentes, mini-verification

Implementation cible:

- nouvel endpoint dedie de type `generate_precise_course`
- rendu dans le modal de cours existant via [src/components/course_modal.php](src/components/course_modal.php)
- fallback provider et validation JSON alignes sur [src/api/ia/generate_quiz.php](src/api/ia/generate_quiz.php)

### 2) Explication d'exercice / correction pedagogique

Usage:

- expliquer pourquoi une reponse est correcte ou incorrecte
- transformer une correction brute en aide pedagogique actionnable
- guider l'eleve pour reussir a 100% au second passage

Contrat attendu:

- entree: niveau, matiere, type d'exercice, enonce, reponse attendue, reponse eleve, correction officielle, competence optionnelle
- sortie: explication courte, explication detaillee, erreur probable, methode a retenir, conseil de reprise, micro-question de verification

Implementation cible:

- nouvel endpoint dedie de type `generate_exercise_explanation`
- consommation depuis les feedbacks d'exercices dans [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js)
- affichage en deux niveaux:
  - aide courte inline sous la question ou dans le feedback
  - aide detaillee dans le modal de cours existant

### 3) Regle non negociable - IA pedagogue, pas correcteur

Pour tous les exercices deja connus du projet:

- ne jamais demander a l'IA de deviner la bonne reponse
- toujours transmettre a l'IA la reponse attendue et la correction officielle deja validees
- limiter le role de l'IA a la reformulation, a l'explication et a la guidance

Conséquence directe:

- les endpoints de correction existants restent prioritaires pour dire juste/faux
- les futurs endpoints IA s'appuient sur ces sorties, ils ne les remplacent pas

### 4) Extension du schema Quiz AI

Pour les quiz generes dynamiquement par IA, le schema cible doit evoluer.

Au-dela de:

- `question`
- `choices`
- `correct`

Ajouter a terme:

- `explanation`
- `competence`
- `common_trap`
- `retry_tip`
- `course_hint`

But:

- eviter un second appel IA quand l'explication peut etre fournie des la generation
- rendre les feedbacks plus riches dans [public/assets/js/interactive-exercises.js](public/assets/js/interactive-exercises.js)

### 5) Plan d'execution recommande

Ordre recommande pour implementation sans blocage:

1. creer `generate_exercise_explanation` avec le meme socle de robustesse que `generate_quiz`
2. brancher un bouton "Comprendre mon erreur" dans les feedbacks d'exercices
3. reutiliser [src/components/course_modal.php](src/components/course_modal.php) pour l'explication detaillee
4. creer `generate_precise_course` pour les notions et competences cibles
5. enrichir ensuite le schema de sortie de `generate_quiz` avec les champs d'explication

Definition de fini du lot:

- un exercice faux peut afficher une explication pedagogique exploitable sans casser la correction existante
- un cours cible peut etre genere depuis une competence ou un exercice source
- le front distingue clairement correction courte et aide detaillee
- les providers IA restent encapsules dans des endpoints robustes avec fallback et JSON valide

## [06/03/2026] Cloture lot diagnostic securise (VALIDE ET TERMINE)

Cette partie est officiellement validee et terminee.

Perimetre cloture:

- Reponses/corrections retirees des JSON publics `public/quiz/*.json`.
- Separation des donnees server-side:
  - `src/data/quiz/` (questions sans reponses)
  - `src/data/quiz_answers/` (reponses/corrections privees)
- Correction cote serveur operationnelle via `src/api/diagnostic/submit.php`.
- Front branche sur soumission serveur via `public/assets/js/diagnostic.js`.
- Quiz manquant corrige: creation de `public/quiz/22.json` (et coherence `src/data/quiz/22.json` + `src/data/quiz_answers/22.json`).

Validation:

- Smoke tests endpoint OK (quiz 12, 22, 100, 125).
- Verification de securite OK: pas de champs `answer`/`correction` dans `public/quiz/*`.

Statut:

- Lot marque comme clos au 06/03/2026.

## [19/03/2026] 🔒 Rapport Audit de Sécurité

Un audit complet (PHP, API, fichiers, dépendances) a été réalisé.

- **Résultats** : `composer audit` OK, vulnérabilités potentielles sur l'exposition de `.env`, `.sql` et `.log`.
- **Actions** : Patch `.gitignore` recommandé, renforcement des accès serveurs et audit des endpoints API.
- **Détails** : Voir [dev/JOURNAL_REPRISE.md](dev/JOURNAL_REPRISE.md#19032026--scan-de-securite-automatise).

## Sécurité Git et protection des données

Un hook pre-commit bloque tout commit de fichier sensible (.env, config.php, .sql, etc.).
Voir la doc détaillée : [dev/tools/git-hooks/README_git-hooks.md](dev/tools/git-hooks/README_git-hooks.md)

### [01/04/2026] Règle de publication GitHub (anti-fuite, non négociable)

Pour toute publication (branche, PR, push), appliquer la stratégie suivante :

- `allowlist d'abord` : le `.gitignore` ignore tout par défaut (`/*`) puis réautorise explicitement uniquement les dossiers/fichiers attendus.
- `denylist de sécurité` : secrets, dumps SQL, backups, logs, artefacts de tests, index/outils locaux et mémoire interne restent toujours ignorés.
- `private first` : en cas de doute, pousser en dépôt privé avant toute ouverture publique.
- `historique propre` : si un secret a déjà été committé, le retirer du fichier courant ne suffit pas; nettoyer l'historique Git et régénérer la clé.

Fichier de référence opérationnelle : `.gitignore` à la racine du projet.

## Note technique — Vérification PDO dans l’API diagnostic (diagnostic.php)

Dans `src/api/diagnostic.php`, une vérification explicite de la connexion PDO est effectuée :

```php
if (!isset($pdo) || !$pdo instanceof PDO) {
  json_error('Erreur de connexion à la base de données', 500);
}
```

Cette vérification permet de garantir que l’API retourne une erreur JSON propre si la connexion à la base échoue, sans exposer de détails techniques côté utilisateur.

👉 Cette logique est réservée à la documentation technique (dev, audit, debug) : elle ne doit pas être documentée côté utilisateur final, car elle ne concerne que la robustesse backend et la gestion des erreurs API.

Pour toute évolution, voir aussi : `dev/JOURNAL_REPRISE.md` (journal daté) et `.github/CONTEXT_PRODUIT.md` (contexte produit).

## Schéma JSON des quiz diagnostics (référence)

Cette section formalise la structure actuelle des quiz diagnostics pour éviter les régressions de sécurité.

### 1) Fichier public (questions uniquement)

Emplacements:

- `public/quiz/{id}.json`
- `src/data/quiz/{id}.json`

Contraintes:

- Aucun champ `answer` ni `correction` dans `quiz.questions[*]`.
- Le front lit ce fichier pour afficher les questions.

Exemple minimal:

```json
{
  "contents": {
    "title": "Quiz Diagnostic 6eme Anglais",
    "level": "6eme",
    "subject": "Anglais"
  },
  "quiz": {
    "title": "Diagnostic Anglais 6eme",
    "question_count": 8,
    "passing_score": 70,
    "questions": [
      {
        "id": 1,
        "type": "qcm",
        "question": "I ... a student.",
        "choices": ["am", "is", "are"]
      },
      {
        "id": 2,
        "type": "vrai-faux",
        "question": "We ... teachers."
      }
    ]
  }
}
```

### 2) Fichier privé réponses (correction serveur)

Emplacement:

- `src/data/quiz_answers/{id}.json`

Contraintes:

- Une entrée `answers[*]` par question.
- Mapping robuste par `question_id` (prioritaire) et `index` (fallback).

Exemple minimal:

```json
{
  "contents": {
    "title": "Quiz Diagnostic 6eme Anglais",
    "level": "6eme",
    "subject": "Anglais"
  },
  "quiz": {
    "title": "Diagnostic Anglais 6eme",
    "question_count": 8,
    "answers": [
      {
        "index": 0,
        "question_id": 1,
        "type": "qcm",
        "answer": "am",
        "correction": "I = 1ere personne singulier"
      },
      {
        "index": 1,
        "question_id": 2,
        "type": "vrai-faux",
        "answer": true,
        "correction": "We = pluriel -> are"
      }
    ]
  }
}
```

### 3) Contrat API de soumission/correction

Endpoint:

- `src/api/diagnostic/submit.php`

Payload attendu:

```json
{
  "quiz_id": 22,
  "answers": {
    "q0": "am",
    "q1": "vrai"
  },
  "duration_seconds": 120
}
```

Réponse type:

```json
{
  "success": true,
  "data": {
    "quiz_id": 22,
    "score": 75,
    "passed": true,
    "correct_count": 6,
    "total_questions": 8,
    "xp_gained": 15,
    "xp_total": 120,
    "feedback": {
      "message": "Bravo !",
      "strengths": ["Q1", "Q2"],
      "to_review": ["Q5"]
    }
  }
}
```

### 4) Invariants à maintenir

- Parité de fichiers: `{id}.json` doit exister dans:
  - `public/quiz/`
  - `src/data/quiz/`
  - `src/data/quiz_answers/`
- `quiz.question_count` doit correspondre à:
  - `quiz.questions.length` dans le fichier public
  - `quiz.answers.length` dans le fichier privé
- Le type d'entrée (`qcm`, `vrai-faux`, `texte`, `calcul`) doit rester cohérent entre question et réponse.

### 5) Rappel sécurité

- `public/quiz/*` ne doit jamais contenir `answer` ou `correction`.
- Toute correction se fait côté serveur uniquement.

## Workflow d’affichage des exercices (22/02/2026)

### 1. Chargement des pages exercices

- Chaque page d’exercices (ex : `exercices-6eme.php`, `exercices-college.php`, `exercices-lycee.php`, etc.) initialise le contexte (niveau, matière, accès).
- Les pages incluent les fichiers nécessaires :
  - `exercice_loader.php` (chargement des exercices)
  - `exercice_card.php` (affichage d’une carte exercice)
  - `level_navigation.php` (navigation par niveau)

### 2. Vérification des droits d’accès

- Variables : `$is_logged_in`, `$is_admin`, `$is_demo`.
- Si accès limité : aperçu ou exercice aléatoire, panneau coach, CTA inscription.

### 3. Chargement des exercices

- Fonction `getExercisesByLevel` (dans `exercice_loader.php`) : récupère les exercices du niveau et matière.
- Pour le lycée : fusion des niveaux via paramètre GET ou URL (`exercices-lycee.php`).

### 4. Affichage des exercices

- Utilisateurs connectés : boucle sur la liste d’exercices (`$exList`), affichage via balise `<article>` ou `renderExercisePreview` (`exercice_card.php`).
- Visiteurs : aperçu limité ou exercice aléatoire via `renderExercisePreview`.

### 5. Structure des cartes exercices

- Cartes stylisées (dans `exercice_card.php` ou directement dans la page), avec badges difficulté/matière, boutons « Commencer », « Ouvrir », résumé du contenu.
- Hooks JS/CSS conservés pour l’interactivité (`exercises.js`, `dynamic-exercises.js`).

### 6. Validation et interaction

- Boutons « Vérifier mes réponses » : validation via `ExerciseValidator.php` ou `exercise_interactive_generator.php`.
- Actions JS : ouverture, démarrage, vérification.

### 7. Navigation et filtres

---

## TODO & Avancement (26/02/2026)

---

### [26/02/2026] Harmonisation UI/UX validée sur tous les cycles

#### [04/03/2026] Harmonisation UI/UX guides-remediation (boutons, palette, hooks, accessibilité) — VALIDÉE DÉFINITIVEMENT

Tous les guides-remediation (collège, lycée, bac) sont harmonisés : palette conforme, hooks front conservés, accessibilité validée, tests manuels réalisés sur chaque page. Plus aucune action requise à ce niveau du projet.

#### [01/04/2026] Trace consolidée — Harmonisation couleurs par niveau (guides de remédiation)

Cette phase est désormais documentée explicitement comme règle stable du projet.

- Mapping couleur niveau :
  - Collège -> vert
  - Lycée -> violet/pourpre
  - Bac -> doré
- Implémentation attendue : résolution de palette via le helper `get_theme_variant_by_level` (pas de hardcode de couleurs niveau).
- Point d'ancrage helper : `src/config/site_boot.php`.
- Périmètre : guides remédiation collège, lycée, bac et hubs associés.

---

---

### [IMPORTANT - 26/02/2026]

Décision : Toute fonctionnalité, page, parser ou contenu lié au niveau scolaire "primaire" est définitivement hors scope du projet. Aucun développement, test ou documentation ne doit concerner le primaire. Seuls collège, lycée et bac sont maintenus et développés.

## Consigne : Si une tâche, un lot ou un fichier fait référence au primaire, il doit être ignoré ou supprimé du backlog.

Voir aussi : dev/JOURNAL_REPRISE.md (journal détaillé)

### Lots déjà réalisés

- Migration UI/UX guides collège, lycée, bac (suppression cards, grille boutons, modales, palette, accessibilité, hooks front)
- Landing page Tailwind (classes ajoutées, hooks conservés, build CSS)
- Inventaires pages/endpoints/hooks (dev/reports/)
- Documentation factorisée (index, contexte produit, règles IA, prompts)
- Standardisation API, tests manuels parcours élève
- **[06/03/2026] Diagnostic sécurisé** : séparation questions/réponses, correction serveur.
- **[07/03/2026] Restructuration Dashboard Élève** : layout 4x3 Tailwind, harmonisation headers.
- **[08/03/2026] Enrichissement Quiz Phase 1** : 1558/1559 quiz enrichis, qualité améliorée de 85%.
- **[10/03/2026] Pipeline Diagnostic renforcé** : Score réel, révision ciblée, anti-farming XP.
- **[17/03/2026] Règle Anti-répétition** : Logique de 20 quiz distincts par matière/niveau.

- Migration Tailwind et harmonisation UI/UX sur toutes les pages admin principales (audit.php, integrations.php, exercices_admin.php, dashboard_admin.php) :
  - Header, navigation, cards, tables, boutons, modales harmonisés Tailwind
  - Suppression des styles inline et des classes custom
  - Hooks JS et compatibilité front conservés
  - Vérification et gestion des erreurs PDO (connexion BD)
  - Tests visuels et fonctionnels validés

Voir journal daté [dev/JOURNAL_REPRISE.md](dev/JOURNAL_REPRISE.md) pour la traçabilité complète.

### Lots à terminer (Actifs)

- [ ] **Phase 1B** : Remplacement des placeholders résiduels dans les quiz (Priorité Haute).
- [ ] **Diagnostic sécurisé** : Valider l'anti-répétition sur un large volume de tentatives.
- [ ] **Validation E2E** : Smoke tests Playwright complets post-reboot.
- [ ] **Mode Sombre** : Étude de cadrage et impact CSS (Priorité Moyenne).

### En attente / Suspendu

- [ ] **Phase 2 Enrichissement** : Sources vérifiées (Éduscol). Suspendu en attente de GO produit.
- [ ] **Optimisation BD** : Requêtes, index, cache.

---

- Filtres par matière (`subject-filter`), navigation par niveau (`level_navigation.php`).

---

**Résumé** :
Les pages exercices chargent les exercices via `exercice_loader.php`, affichent chaque exercice via `exercice_card.php` ou `renderExercisePreview`, adaptent l’affichage selon l’accès, et proposent des interactions via JS/CSS. Le workflow est centralisé sur la logique PHP (chargement, droits, affichage) et stylisé via hooks front.

**Pour plus de détails techniques** :

- Voir `exercice_loader.php` (chargement)
- Voir `exercice_card.php` (affichage)
- Voir `ExerciseValidator.php` (validation)
- Voir les pages spécifiques pour la logique d’accès et navigation.

> 📖 Pour en savoir plus : Voir [README.md](../README.md), [DOCUMENTATION.md](../DOCUMENTATION.md), [dev/tools/README.md](../dev/tools/README.md)
> **Ajout du 22/02/2026 :** Règle de feedback et correction constructive

Tout contributeur (humain ou IA) doit signaler explicitement toute confusion sur la page ou le module à modifier, et proposer la correction sur le bon fichier.
Objectif : éviter les erreurs de contexte, garantir la réussite du projet par un échange constructif et une traçabilité documentaire (datation, journal).

## Règle IA : feedback direct en cas de confusion

Si l’utilisateur demande une modification sur une page qui n’est pas celle réellement concernée (ex : confusion entre exercices-college.php et exercices.php), il faut le signaler explicitement et proposer la correction sur le bon fichier.

Objectif : éviter les erreurs de contexte, garantir la réussite du projet par un échange constructif.

Exemple :

- « Attention, la page demandée n’est pas celle qui gère l’affichage principal des exercices. Je corrige sur src/pages/system/exercices.php. »

## 🗓️ Documentation refonte pages Exercices — 20 février 2026

### Objectif

Harmoniser l’accès et la navigation entre tous les exercices, pour chaque niveau scolaire (collège, lycée, bac), et garantir une expérience cohérente pour visiteurs et élèves connectés.

### Détails des modifications

- Ajout d’un bouton “Tous les exercices” (📝) sur toutes les pages exercices (collège, lycée, bac), avec fond bleu, texte blanc, bordure bleu foncé, hover bleu foncé.
- Correction du mapping niveau scolaire côté PHP/JS pour garantir la détection correcte du niveau et l’affichage d’un exercice aléatoire pour l’élève connecté.
- Logique front :
  - Visiteur : message explicatif (“Cliquez sur un niveau scolaire pour découvrir un exercice interactif adapté !”), aucun exercice affiché par défaut, exercice affiché après sélection d’un niveau.
  - Élève connecté : exercice aléatoire du niveau affiché automatiquement.
- Navigation améliorée : accès direct à la page générale des exercices depuis chaque page spécifique.
- Hooks front conservés, aucune régression JS.

### Fichiers impactés

- src/pages/eleve/college/6eme/exercices-6eme.php
- src/pages/eleve/college/5eme/exercices-5eme.php
- src/pages/eleve/college/4eme/exercices-4eme.php
- src/pages/eleve/college/3eme/exercices-3eme.php
- src/pages/eleve/lycee/2nde/exercices-2nde.php
- src/pages/eleve/lycee/1ere/exercices-1ere.php
- src/pages/eleve/lycee/terminale/exercices-terminale.php
- src/pages/eleve/bac/exercices-bac.php
- src/pages/system/exercices.php
- public/assets/js/exercices.js

### Comment tester

1. Accéder à chaque page exercices (collège, lycée, bac) : vérifier la présence et la visibilité du bouton “Tous les exercices”.
2. Tester la logique d’affichage pour visiteur (message explicatif, exercice après sélection) et élève connecté (exercice auto).
3. Vérifier la navigation entre pages exercices.
4. Vérifier la cohérence du mapping niveau (exercice affiché pour chaque niveau).
5. Vérifier l’absence de régression JS/front.

### Rollback

Supprimer le bouton “Tous les exercices” sur chaque page, restaurer le mapping niveau initial côté PHP/JS.

### Références

Voir aussi :

- .github/CONTEXT_PRODUIT.md (contexte produit)
- dev/JOURNAL_REPRISE.md (journal daté)
- dev/reports/audit-exercices-20260207.md (audit technique)
- **db/all_exercises_clean_enriched.normalized.json.sql** (source de vérité exercices depuis le 21/02/2026)

## Migration UI/UX guides collège, lycée, bac [19/02/2026]

### Objectif

Harmoniser la présentation de tous les guides de remédiation (collège, lycée, bac) : suppression des cards accordéon, ajout grille de boutons matières avec modales accessibles, bloc « Plan d’action personnalisé » modernisé, palette couleur adaptée à chaque niveau.

### Détails des modifications

- Suppression des cards accordéon sur toutes les pages guides.
- Ajout d’une grille de boutons matières (responsive, accessible) : chaque bouton ouvre une modale dédiée.
- Modales : contenu détaillé par matière, focus automatique, fermeture par ESC ou clic fond.
- Bloc « Plan d’action personnalisé » : harmonisé, palette adaptée (vert collège, violet lycée, doré bac), boutons d’inscription et connexion.
- Correction palette couleur :
  - Collège : vert (emerald)
  - Lycée : violet (violet)
  - Bac : doré (amber)
- Centrage du titre et sous-titre sur la page BAC.
- Hooks front conservés (classes, ids, data-attributes), aucune régression JS.
- Accessibilité : focus, aria-modal, navigation clavier.

### Fichiers impactés

- src/pages/eleve/college/\*/guide-remediation.php
- src/pages/eleve/lycee/2nde/guide-remediation.php
- src/pages/eleve/lycee/1ere/guide-remediation.php
- src/pages/eleve/lycee/terminale/guide-remediation.php
- src/pages/eleve/bac/guide-remediation.php

### Comment tester

1. Vérifier l’affichage sur chaque guide (collège, lycée, bac).
2. Tester l’ouverture/fermeture des modales (clavier, souris).
3. Vérifier la cohérence des couleurs selon le niveau.
4. Tester les boutons d’inscription/connexion.
5. Vérifier l’accessibilité (focus, aria-modal).

### Rollback

Restaurer les versions précédentes des fichiers guides-remediation.php concernés.

### Références

Voir aussi :

- .github/CONTEXT_PRODUIT.md (contexte produit)
- .github/copilot-plan.md (roadmap UI/UX)
- dev/JOURNAL_REPRISE.md (journal de migration)

## Inclusion du dashboard élève et CSS

Depuis la correction du 17/02/2026, la page `eleve/dashboard` n’est plus dans `$pages_sensibles` du routeur (`public/index.php`).
Cela garantit que le head HTML (et donc le CSS) est toujours généré par le routeur, même pour le dashboard élève.
Ne pas remettre `eleve/dashboard` dans `$pages_sensibles` sauf cas très particulier.

Voir aussi `.github/PROJECT_CONTEXT.md` pour le rappel de la règle.

## 🗂️ Migration CSS → Tailwind : Login & Register (février 2026)

### Objectif

Traduire toutes les classes CSS custom des pages login et register en classes utilitaires Tailwind directement dans le HTML, pour supprimer login.css et register.css.

### Suivi de migration (correspondances)

#### login.css → Tailwind

| Classe CSS              | Classes Tailwind équivalentes                                                                                                                                                                                              | Statut   |
| ----------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------- |
| .login-container        | max-w-md w-full bg-white rounded-xl shadow-xl p-8 mx-auto my-8                                                                                                                                                             | À migrer |
| .login-header           | text-center mb-8                                                                                                                                                                                                           | À migrer |
| .login-header h1        | text-3xl font-bold text-blue-600 mb-2                                                                                                                                                                                      | À migrer |
| .login-header p         | text-gray-600 text-lg                                                                                                                                                                                                      | À migrer |
| .login-form             | flex flex-col gap-6                                                                                                                                                                                                        | À migrer |
| .form-group             | flex flex-col mb-4                                                                                                                                                                                                         | À migrer |
| .form-group label       | font-semibold text-gray-700 mb-2 text-base                                                                                                                                                                                 | À migrer |
| .form-group input       | px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100                                                              | À migrer |
| .login-btn              | bg-gradient-to-r from-blue-600 to-blue-500 text-white border-none py-4 px-8 rounded-lg text-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 mt-4 | À migrer |
| .login-links            | text-center mt-8 pt-8 border-t border-gray-200                                                                                                                                                                             | À migrer |
| .login-links a          | text-blue-600 hover:text-blue-800 font-medium                                                                                                                                                                              | À migrer |
| .form-error             | text-red-600 text-sm mt-1 min-h-5 block                                                                                                                                                                                    | À migrer |
| .form-help              | text-gray-500 text-xs mt-1 italic block                                                                                                                                                                                    | À migrer |
| .required-asterisk      | text-red-500 font-bold ml-1                                                                                                                                                                                                | À migrer |
| .error-message          | bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-500 rounded-lg p-4 mb-6 flex items-center gap-3 text-red-800 animate-pulse                                                                                     | À migrer |
| .success-message        | bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-500 rounded-lg p-4 mb-6 flex items-center gap-3 text-green-800                                                                                           | À migrer |
| .password-input-wrapper | relative                                                                                                                                                                                                                   | À migrer |
| .toggle-password        | absolute right-3 top-1/2 transform -translate-y-1/2 bg-transparent border-none cursor-pointer p-2 text-xl opacity-60 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center                    | À migrer |
| .eye-icon-hidden        | (SVG ou utilitaire custom)                                                                                                                                                                                                 | À migrer |

#### register.css → Tailwind

| Classe CSS                            | Classes Tailwind équivalentes                                                                                                                                                                                                                     | Statut   |
| ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------- |
| .register-container                   | max-w-lg w-full bg-white rounded-xl shadow-xl p-8 mx-auto my-8                                                                                                                                                                                    | À migrer |
| .register-header                      | text-center mb-8                                                                                                                                                                                                                                  | À migrer |
| .register-header h1                   | text-3xl font-bold text-blue-600 mb-2                                                                                                                                                                                                             | À migrer |
| .register-header p                    | text-gray-600 text-lg                                                                                                                                                                                                                             | À migrer |
| .register-form                        | grid grid-cols-1 md:grid-cols-2 gap-6                                                                                                                                                                                                             | À migrer |
| .form-group                           | flex flex-col mb-4                                                                                                                                                                                                                                | À migrer |
| .form-group.full-width                | col-span-full                                                                                                                                                                                                                                     | À migrer |
| .form-group label                     | font-semibold text-gray-700 mb-2 text-base                                                                                                                                                                                                        | À migrer |
| .form-group input, .form-group select | px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100                                                                                     | À migrer |
| .register-btn                         | bg-gradient-to-r from-emerald-600 to-emerald-500 text-white border-none py-4 px-8 rounded-lg text-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/30 mt-4 col-span-full | À migrer |
| .register-links                       | text-center mt-8 pt-8 border-t border-gray-200 col-span-full                                                                                                                                                                                      | À migrer |
| .register-links a                     | text-blue-600 hover:text-blue-800 font-medium                                                                                                                                                                                                     | À migrer |
| .benefits                             | bg-gradient-to-r from-sky-50 to-sky-100 border border-sky-400 rounded-lg p-6 mb-8 text-center                                                                                                                                                     | À migrer |
| .benefits h3                          | text-sky-900 mb-4                                                                                                                                                                                                                                 | À migrer |
| .benefits ul                          | list-none p-0 grid grid-cols-1 md:grid-cols-2 gap-4 m-0                                                                                                                                                                                           | À migrer |
| .benefits li                          | text-gray-700 font-medium before:content-['✅'] before:text-emerald-600                                                                                                                                                                           | À migrer |
| .register-error                       | bg-red-50 text-red-600 p-4 rounded-lg mb-4 border-l-4 border-red-600 col-span-full                                                                                                                                                                | À migrer |
| .account-type-selection               | my-8                                                                                                                                                                                                                                              | À migrer |
| .account-type-buttons                 | grid grid-cols-1 md:grid-cols-2 gap-6 mt-8                                                                                                                                                                                                        | À migrer |
| .account-type-btn                     | bg-white border-4 border-gray-200 rounded-xl p-8 cursor-pointer transition-all duration-300 text-center flex flex-col items-center gap-3 hover:-translate-y-1 hover:shadow-lg hover:border-blue-600                                               | À migrer |
| .account-type-btn.student-btn:hover   | border-blue-600 bg-gradient-to-r from-blue-50 to-blue-100                                                                                                                                                                                         | À migrer |
| .account-type-btn.parent-btn:hover    | border-emerald-600 bg-gradient-to-r from-emerald-50 to-emerald-100                                                                                                                                                                                | À migrer |
| .btn-icon                             | text-3xl mb-2                                                                                                                                                                                                                                     | À migrer |
| .btn-title                            | text-xl font-bold text-slate-800                                                                                                                                                                                                                  | À migrer |
| .btn-desc                             | text-base text-slate-400                                                                                                                                                                                                                          | À migrer |
| .form-error                           | text-red-600 text-sm mt-1 min-h-5 block                                                                                                                                                                                                           | À migrer |
| .form-help                            | text-gray-500 text-xs mt-1 italic block                                                                                                                                                                                                           | À migrer |
| .required-asterisk                    | text-red-500 font-bold ml-1                                                                                                                                                                                                                       | À migrer |

---

**Statut** : Mettre à jour la colonne Statut à chaque migration de bloc dans le HTML. Une fois toutes les lignes passées à "Fait", login.css et register.css peuvent être supprimés.

## 🛠️ Problème d'affichage après migration TailwindCSS (février 2026)

### Symptômes

- Les pages login, register et dashboard n'affichent plus correctement leur design après la migration Tailwind.
- Perte de couleurs, de layout, de styles sur les inputs/boutons, ou affichage "brut".

### Causes identifiées

1. **Reset Tailwind** : tailwind.css applique un reset CSS fort qui efface les styles par défaut du navigateur et de l'ancien style.css.
2. **Suppression du CSS global** : style.css supprimé, donc les styles de base (body, typographie, layout) ne sont plus appliqués.
3. **Spécificité CSS** : login.css/register.css ne sont pas assez spécifiques pour écraser les utilitaires Tailwind ou le reset.
4. **Dépendance à Tailwind** : dashboard.php n'a plus de CSS custom, donc tout doit être stylé via classes Tailwind.

### Actions correctives à mener

- **Vérifier que chaque page a une classe unique sur le body** (ex : .login-page, .register-page) et que le CSS custom est scoppé dessus.
- **Renforcer la spécificité des sélecteurs dans login.css/register.css** :
  - Utiliser .login-page input, .register-page button, etc.
  - Ajouter !important uniquement si nécessaire.
- **Ajouter des classes Tailwind dans le HTML** pour tous les éléments structurants (inputs, boutons, containers).
- **Ne pas utiliser de classes génériques non scoppées** (ex : .btn, .input) dans le CSS custom sans préfixe ou scope.
- **Tester chaque page après modification** pour vérifier que le design est conforme et qu'il n'y a pas de conflit entre Tailwind et le CSS custom.

### Bonnes pratiques post-migration

- Privilégier Tailwind pour la majorité des styles.
- Limiter le CSS custom aux cas très spécifiques, toujours scoppé par page.
- Documenter toute règle custom ajoutée ou modifiée.

---

## Pour toute modification CSS, se référer à cette section et vérifier la compatibilité avec Tailwind avant déploiement.

## 🔗 Liens documentaires & workflow d’ajout d’exercices

Pour garantir la continuité et permettre à tout nouvel assistant d’ajouter ou de modifier des exercices :

### Références croisées essentielles

- [docs/INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md) : schéma des exercices, formats attendus, workflow d’import/export.
- [dev/tools/exercises/collect_exercises_v2.php](dev/tools/exercises/collect_exercises_v2.php) : collecte et normalisation.
- [dev/tools/exercises/deduplicate_exercises_v3.php](dev/tools/exercises/deduplicate_exercises_v3.php) : fusion et déduplication.
- [dev/tools/exercises/import_final_exercises.php](dev/tools/exercises/import_final_exercises.php) : import en base.
- [dev/tools/exercises/fix_failed_exercises.php](dev/tools/exercises/fix_failed_exercises.php) : correction automatique.
- [db/json/exercises_from_database.json](db/json/exercises_from_database.json) : export BDD.
- [db/json/unified_exercises.json](db/json/unified_exercises.json) : fusion JSON.
- [db/json/exercises_final_deduplicated.json](db/json/exercises_final_deduplicated.json) : résultat final.
- [dev/reports/audit-exercices-20260207.md](dev/reports/audit-exercices-20260207.md) : audit technique.
- [docs/SPRINTS_SUMMARY.md](docs/SPRINTS_SUMMARY.md), [CONTEXT_INDEX.md](CONTEXT_INDEX.md) : suivi des chantiers et index de contexte.

### Workflow d’ajout d’un exercice

1. Lire le schéma dans [docs/INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md).
2. Ajouter l’exercice dans le fichier JSON de vérité ([db/json/unified_exercises.json](db/json/unified_exercises.json)).
3. Lancer la collecte/normalisation ([dev/tools/exercises/collect_exercises_v2.php](dev/tools/exercises/collect_exercises_v2.php)).
4. Fusionner/dédupliquer avec l’existant ([dev/tools/exercises/deduplicate_exercises_v3.php](dev/tools/exercises/deduplicate_exercises_v3.php)).
5. Importer en base ([dev/tools/exercises/import_final_exercises.php](dev/tools/exercises/import_final_exercises.php)).
6. Corriger les erreurs éventuelles ([dev/tools/exercises/fix_failed_exercises.php](dev/tools/exercises/fix_failed_exercises.php)).
7. Valider via dry-run, logs et rapport ([import_final.log](dev/reports/import_final.log)).
8. Mettre à jour la documentation et l’audit ([dev/reports/audit-exercices-20260207.md](dev/reports/audit-exercices-20260207.md)).

### Checklist pour la continuité

- Toujours référencer le schéma et les scripts dans chaque ajout.
- Documenter toute modification ou correction dans les rapports et la documentation.
- Vérifier la conformité via dry-run et logs.
- Mettre à jour les fichiers de vérité et la documentation technique.
- Archiver les scripts de migration une fois le chantier terminé.

---

# 📚 MonCoachScolaire - Documentation Complète

## 🧩 Structuration et affichage universel des exercices (février 2026)

### Objectif

Permettre l’affichage robuste de tous les exercices, quelle que soit la matière ou la complexité, en s’appuyant sur :

- Un schéma JSON officiel (voir `db/json/schema_officiel_bdd_exercises_20260215.json`)
- Un parser non destructif (préserve les champs existants, complète les manquants)
- Un composant front universel, capable d’extraire et d’afficher dynamiquement questions, sous-questions, consignes, corrections, etc.

### Cas traités

- Exercices simples (QCM, texte, mathématiques classiques)
- Exercices à sous-questions multiples (français, sciences)
- Exercices à structure non standard (matières complexes : philosophie, SVT, physique)

### Règles et fallback

- Toujours tenter d’extraire les sous-questions du champ `Content` ou de `sub_questions` si structuré.
- Si la structure est trop complexe ou non détectable, afficher le contenu brut avec un message d’avertissement (fallback manuel).
- Prévoir une saisie/correction manuelle pour les matières non structurables.

### Hooks front à standardiser

- Utiliser des classes/data-attributes pour chaque bloc question/sous-question (`data-question-id`, `data-part-id`, etc.)
- Boutons de validation, affichage de correction, navigation entre sous-questions
- Badge de difficulté, matière, compétence

### Documentation continue

- Toute évolution du parser, du schéma ou du composant d’affichage doit être documentée ici et dans le journal de reprise (`dev/JOURNAL_REPRISE.md`).
- Règle : chaque nouvelle entrée du journal de reprise doit être datée (voir section dédiée dans le journal).

### Prochaines étapes

- Finaliser le plan technique de la page d’affichage universelle (front + fallback)
- Lister les cas limites et matières nécessitant un traitement manuel
- Continuer l’enrichissement documentaire et la synchronisation avec la roadmap technique

> 📖 Pour en savoir plus : Voir [dev/JOURNAL_REPRISE.md](dev/JOURNAL_REPRISE.md), [docs/INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md), [db/json/schema_officiel_bdd_exercises_20260215.json](db/json/schema_officiel_bdd_exercises_20260215.json)

> Plateforme éducative interactive pour l'accompagnement scolaire du collège au lycée

## 🟢 Refonte UI Collège-Accueil — 13 février 2026

- Cards visiteurs collège (4 niveaux) : migration Tailwind, palette verte, boutons harmonisés, hooks conservés.
- Titre principal modernisé (gradient vert, wording landingpage).
- Compression hauteur cards, accessibilité maintenue.
- À faire : appliquer ce design aux cards “niveau” élèves connectés/admin, harmoniser boutons sur pages collège, vérifier cohérence couleurs, ajouter test visuel et checklist accessibilité.
- Prévoir harmonisation UI sur lycee-accueil et bac-accueil (palette, boutons, wording).

---

## 🗓️ Mise à jour documentation — 12 février 2026

Cette mise à jour documente l’état réel du projet **sans supprimer l’historique** :

- Arborescence actuelle (src/, dev/tools/, src/api/\*)
- Emplacements réels des scripts d’import/export
- Rappels sur le système hybride des cours (Markdown ↔ BDD)
- Inventaires techniques disponibles (`dev/reports/pages_inventory.md`, `dev/reports/api_inventory.md`, `dev/reports/hooks_inventory.md`)
- Synchronisation de la roadmap Copilot avec les livrables déjà présents

## 🧭 Organisation du document

Ce document est organisé en **deux parties** :

- **Partie 1** : Documentation générale (vue d’ensemble, architecture, usage).
- **Partie 2** : **Annexe technique** (historique, schémas détaillés, workflows avancés).

## 📅 Dates de référence (distinctes)

- **Documentation générale** : 12 février 2026 (synchronisation roadmap + inventaires + état réel).
- **Historique des versions** : 3 février 2026 (v2.2.0) et 4 février 2026 (v2.2.1).
- **Annexe technique** : 4 février 2026 (état technique consolidé).

## 🗓️ Journal des documentations (entrées datées)

> Chaque entrée correspond à une action/documentation distincte, avec sa date propre (pas une date unique globale).

- **Documentation générale** : 12 février 2026 (synchronisation roadmap + inventaires + état réel).
- **Historique des versions** : 3 février 2026 (v2.2.0) et 4 février 2026 (v2.2.1).
- **Annexe technique** : 4 février 2026 (état technique consolidé).
- **Système d’exercices — correctifs “multi-parties”** : 5–6 février 2026 (normalisation + correctifs d’affichage, scripts de conversion, correction syntaxe PHP).
- **Audit documentaire** : 12 février 2026 (roadmap alignée avec les inventaires et chantiers en cours).

---

## 🗓️ Audit CSS & migration Tailwind — 15 février 2026

- **Objectif** : Préparer la migration vers Tailwind CSS via un audit complet des styles existants, automatiser l’analyse, générer des rapports exploitables et garantir l’absence de warnings PHP.
- **Script principal** : [dev/tools/css/audit_css_complet.php](dev/tools/css/audit_css_complet.php) — scanne tous les fichiers CSS du dossier `public/assets/css`, détecte les patterns clés (flex, grid, couleurs, animations, hacks IE11), évalue la compatibilité Tailwind, et génère 4 rapports :
  - [dev/reports/css_audit_summary.txt](dev/reports/css_audit_summary.txt) (synthèse)
  - [dev/reports/css_audit_detailed.json](dev/reports/css_audit_detailed.json) (détail complet)
  - [dev/reports/css_migration_plan.md](dev/reports/css_migration_plan.md) (plan de migration par phases)
  - [dev/reports/css_examples_migration.md](dev/reports/css_examples_migration.md) (exemples avant/après)
- **Robustesse** : Toutes les expressions régulières ont été corrigées pour éviter les warnings PCRE, notamment la détection des hacks IE11 (split en deux patterns pour fiabilité).
- **Vérification UI** : Le fond d’écran unifié est bien appliqué à toutes les pages cours (template principal déjà conforme, aucun patch requis).
- **Documentation** : Cette entrée documente l’ensemble du process : automatisation, corrections, résultats, et liens directs vers les rapports générés.
- **Références croisées** : Voir aussi [dev/tools/README.md](dev/tools/README.md) (scripts), [CONTEXT_INDEX.md](CONTEXT_INDEX.md) (index), [dev/reports/css_audit_summary.txt](dev/reports/css_audit_summary.txt) (synthèse), [dev/reports/css_migration_plan.md](dev/reports/css_migration_plan.md) (plan).

**Résultat** : Script robuste, zéro warning PHP, rapports exploitables pour la migration Tailwind, documentation à jour.

## 📖 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)

7. [Documentation Technique](#documentation-technique)
8. [Scripts, outils & tests (index)](dev/tools/README.md)
9. [Sécurité](#sécurité)
10. [Maintenance](#maintenance)
11. [Annexe technique (historique)](#annexe-technique-historique)
12. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **1088+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique         | Valeur |
| ---------------- | ------ |
| Exercices totaux | 1088   |

---

## 🗂️ Migration exercices 2026 — Import, déduplication, correction

### Problèmes rencontrés

- Normalisation des champs JSON/BDD (27 champs attendus)
- Scripts d’inclusion : absence de getConnection() dans src/database/connection.php
- Dédoublonnage : détection d’identifiant incorrecte, 0 exercice unique
- Contraintes SQL : 12 exercices rejetés (exercises.Choices)

### Solutions apportées

- Refactorisation des scripts (collect, deduplicate, import) pour conformité BDD
- Ajout de la fonction getConnection() dans connection.php
- Debug et correction du script deduplicate_exercises_v3.php
- Correction automatique via fix_failed_exercises.php

### Scripts et outils utilisés

- dev/tools/exercises/collect_exercises_v2.php
- dev/tools/exercises/deduplicate_exercises_v3.php
- dev/tools/exercises/import_final_exercises.php
- dev/tools/exercises/fix_failed_exercises.php
- src/database/ExerciseNormalizer.php
- Logs d’import et de correction (import_final.log)

### Fichiers de vérité

- exercises_from_database.json (export BDD)
- unified_exercises.json (fusion JSON)
- exercises_final_deduplicated.json (résultat dédoublonné)

### Tests & validations

- Dry-run (import_final_exercises.php --dry-run)
- Analyse des logs SQL
- Correction ciblée (fix_failed_exercises.php)
- Rapport final d’import

### Résultats

- 1245 exercices importés, tous conformes
- 12 erreurs initiales corrigées automatiquement
- Base complète, aucun exercice ignoré

### Prochaines actions

- Archivage des scripts de migration
- Vérification en base (affichage, conformité Choices)
- Automatisation de la validation future

---

| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-12)

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

| Technologie   | Version | Usage                   |
| ------------- | ------- | ----------------------- |
| PHP           | 8.x     | Backend                 |
| MySQL/MariaDB | 10.x    | Base de données         |
| JavaScript    | ES6+    | Frontend interactif     |
| Bootstrap     | 5.x     | UI/UX                   |
| Apache        | 2.4     | Serveur web             |
| Composer      | 2.x     | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 1088 exercices structurés par niveau et matière
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

| Rôle   | URL                     | Identifiants par défaut |
| ------ | ----------------------- | ----------------------- |
| Admin  | `/dashboard_admin.php`  | admin / admin123        |
| Parent | `/dashboard_parent.php` | parent1 / pass123       |
| Élève  | `/dashboard.php`        | demo / demo123          |

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

### Audit de code automatisé

- Utilisez `npx gitnexus analyze` ou `npm run audit` pour lancer l’audit de code.
- Voir README pour la commande rapide.

### Scripts, outils & tests (centralisation)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste détaillée supprimée, remplacée par une référence croisée unique. -->

Résumé :

- Scripts d'automatisation : `dev/tools/`
- Scripts de test/debug : `dev/tools/tests/`
- Rapports d'audit : `dev/reports/`
- Documentation : `docs/`

---

### Exemples d'usage (voir README central pour plus)

```bash
# Import d'exercices (exemple)
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Export d'exercices (exemple)
php dev/tools/exercises/export_exercises.php json

# Maintenance (exemple)
php dev/tools/maintenance/enable_maintenance.php "Message"
php dev/tools/maintenance/disable_maintenance.php

# Lancer tous les tests unitaires
./vendor/bin/phpunit
```

> Pour tous les scripts, options et tests disponibles, se référer à [dev/tools/README.md](dev/tools/README.md).

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

| Document                                                        | Description                 |
| --------------------------------------------------------------- | --------------------------- |
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)           | Import/export exercices     |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)               | Gestion système de cours    |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)       | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md)                         | Configuration sécurité      |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)                   | Configuration Apache        |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)                 | Intégration mascotte        |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive           |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md)                     | Gestion des URLs            |

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
- Documentation de la structure réelle (src/, dev/tools/, src/api/\*).
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
   - Test spécifique background : `npx playwright test dev/tools/tests/e2e/background.spec.js` attend que le `::before` pseudo-element ait une `background-image`.
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
src/includes/\*.php (Modèles/Services)
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
```

### Tableau des rôles

| Rôle         | Description    |
| ------------ | -------------- |
| `eleve`      | Élève          |
| `parent`     | Parent         |
| `professeur` | Professeur     |
| `admin`      | Administrateur |

---

## 📑 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)

7. [Documentation Technique](#documentation-technique)
8. [Scripts, outils & tests (index)](dev/tools/README.md)
9. [Sécurité](#sécurité)
10. [Maintenance](#maintenance)
11. [Annexe technique (historique)](#annexe-technique-historique)
12. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **1088+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique         | Valeur |
| ---------------- | ------ |
| Exercices totaux | 1088   |

---

## 🗂️ Migration exercices 2026 — Import, déduplication, correction

### Problèmes rencontrés

- Normalisation des champs JSON/BDD (27 champs attendus)
- Scripts d’inclusion : absence de getConnection() dans src/database/connection.php
- Dédoublonnage : détection d’identifiant incorrecte, 0 exercice unique
- Contraintes SQL : 12 exercices rejetés (exercises.Choices)

### Solutions apportées

- Refactorisation des scripts (collect, deduplicate, import) pour conformité BDD
- Ajout de la fonction getConnection() dans connection.php
- Debug et correction du script deduplicate_exercises_v3.php
- Correction automatique via fix_failed_exercises.php

### Scripts et outils utilisés

- dev/tools/exercises/collect_exercises_v2.php
- dev/tools/exercises/deduplicate_exercises_v3.php
- dev/tools/exercises/import_final_exercises.php
- dev/tools/exercises/fix_failed_exercises.php
- src/database/ExerciseNormalizer.php
- Logs d’import et de correction (import_final.log)

### Fichiers de vérité

- exercises_from_database.json (export BDD)
- unified_exercises.json (fusion JSON)
- exercises_final_deduplicated.json (résultat dédoublonné)

### Tests & validations

- Dry-run (import_final_exercises.php --dry-run)
- Analyse des logs SQL
- Correction ciblée (fix_failed_exercises.php)
- Rapport final d’import

### Résultats

- 1245 exercices importés, tous conformes
- 12 erreurs initiales corrigées automatiquement
- Base complète, aucun exercice ignoré

### Prochaines actions

- Archivage des scripts de migration
- Vérification en base (affichage, conformité Choices)
- Automatisation de la validation future

---

| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-12)

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

| Technologie   | Version | Usage                   |
| ------------- | ------- | ----------------------- |
| PHP           | 8.x     | Backend                 |
| MySQL/MariaDB | 10.x    | Base de données         |
| JavaScript    | ES6+    | Frontend interactif     |
| Bootstrap     | 5.x     | UI/UX                   |
| Apache        | 2.4     | Serveur web             |
| Composer      | 2.x     | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 1088 exercices structurés par niveau et matière
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

| Rôle   | URL                     | Identifiants par défaut |
| ------ | ----------------------- | ----------------------- |
| Admin  | `/dashboard_admin.php`  | admin / admin123        |
| Parent | `/dashboard_parent.php` | parent1 / pass123       |
| Élève  | `/dashboard.php`        | demo / demo123          |

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

### Scripts, outils & tests (centralisation)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste détaillée supprimée, remplacée par une référence croisée unique. -->

Résumé :

- Scripts d'automatisation : `dev/tools/`
- Scripts de test/debug : `dev/tools/tests/`
- Rapports d'audit : `dev/reports/`
- Documentation : `docs/`

---

### Exemples d'usage (voir README central pour plus)

```bash
# Import d'exercices (exemple)
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Export d'exercices (exemple)
php dev/tools/exercises/export_exercises.php json

# Maintenance (exemple)
php dev/tools/maintenance/enable_maintenance.php "Message"
php dev/tools/maintenance/disable_maintenance.php

# Lancer tous les tests unitaires
./vendor/bin/phpunit
```

> Pour tous les scripts, options et tests disponibles, se référer à [dev/tools/README.md](dev/tools/README.md).

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

| Document                                                        | Description                 |
| --------------------------------------------------------------- | --------------------------- |
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)           | Import/export exercices     |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)               | Gestion système de cours    |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)       | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md)                         | Configuration sécurité      |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)                   | Configuration Apache        |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)                 | Intégration mascotte        |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive           |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md)                     | Gestion des URLs            |

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
- Documentation de la structure réelle (src/, dev/tools/, src/api/\*).
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
   - Test spécifique background : `npx playwright test dev/tools/tests/e2e/background.spec.js` attend que le `::before` pseudo-element ait une `background-image`.
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
src/includes/\*.php (Modèles/Services)
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
```

### Tableau des rôles

| Rôle         | Description    |
| ------------ | -------------- |
| `eleve`      | Élève          |
| `parent`     | Parent         |
| `professeur` | Professeur     |
| `admin`      | Administrateur |

---

## 📑 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)

7. [Documentation Technique](#documentation-technique)
8. [Scripts, outils & tests (index)](dev/tools/README.md)
9. [Sécurité](#sécurité)
10. [Maintenance](#maintenance)
11. [Annexe technique (historique)](#annexe-technique-historique)
12. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **1088+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique         | Valeur |
| ---------------- | ------ |
| Exercices totaux | 1088   |

---

## 🗂️ Migration exercices 2026 — Import, déduplication, correction

### Problèmes rencontrés

- Normalisation des champs JSON/BDD (27 champs attendus)
- Scripts d’inclusion : absence de getConnection() dans src/database/connection.php
- Dédoublonnage : détection d’identifiant incorrecte, 0 exercice unique
- Contraintes SQL : 12 exercices rejetés (exercises.Choices)

### Solutions apportées

- Refactorisation des scripts (collect, deduplicate, import) pour conformité BDD
- Ajout de la fonction getConnection() dans connection.php
- Debug et correction du script deduplicate_exercises_v3.php
- Correction automatique via fix_failed_exercises.php

### Scripts et outils utilisés

- dev/tools/exercises/collect_exercises_v2.php
- dev/tools/exercises/deduplicate_exercises_v3.php
- dev/tools/exercises/import_final_exercises.php
- dev/tools/exercises/fix_failed_exercises.php
- src/database/ExerciseNormalizer.php
- Logs d’import et de correction (import_final.log)

### Fichiers de vérité

- exercises_from_database.json (export BDD)
- unified_exercises.json (fusion JSON)
- exercises_final_deduplicated.json (résultat dédoublonné)

### Tests & validations

- Dry-run (import_final_exercises.php --dry-run)
- Analyse des logs SQL
- Correction ciblée (fix_failed_exercises.php)
- Rapport final d’import

### Résultats

- 1245 exercices importés, tous conformes
- 12 erreurs initiales corrigées automatiquement
- Base complète, aucun exercice ignoré

### Prochaines actions

- Archivage des scripts de migration
- Vérification en base (affichage, conformité Choices)
- Automatisation de la validation future

---

| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-12)

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

| Technologie   | Version | Usage                   |
| ------------- | ------- | ----------------------- |
| PHP           | 8.x     | Backend                 |
| MySQL/MariaDB | 10.x    | Base de données         |
| JavaScript    | ES6+    | Frontend interactif     |
| Bootstrap     | 5.x     | UI/UX                   |
| Apache        | 2.4     | Serveur web             |
| Composer      | 2.x     | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 1088 exercices structurés par niveau et matière
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

| Rôle   | URL                     | Identifiants par défaut |
| ------ | ----------------------- | ----------------------- |
| Admin  | `/dashboard_admin.php`  | admin / admin123        |
| Parent | `/dashboard_parent.php` | parent1 / pass123       |
| Élève  | `/dashboard.php`        | demo / demo123          |

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

### Scripts, outils & tests (centralisation)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste détaillée supprimée, remplacée par une référence croisée unique. -->

Résumé :

- Scripts d'automatisation : `dev/tools/`
- Scripts de test/debug : `dev/tools/tests/`
- Rapports d'audit : `dev/reports/`
- Documentation : `docs/`

---

### Exemples d'usage (voir README central pour plus)

```bash
# Import d'exercices (exemple)
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Export d'exercices (exemple)
php dev/tools/exercises/export_exercises.php json

# Maintenance (exemple)
php dev/tools/maintenance/enable_maintenance.php "Message"
php dev/tools/maintenance/disable_maintenance.php

# Lancer tous les tests unitaires
./vendor/bin/phpunit
```

> Pour tous les scripts, options et tests disponibles, se référer à [dev/tools/README.md](dev/tools/README.md).

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

| Document                                                        | Description                 |
| --------------------------------------------------------------- | --------------------------- |
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)           | Import/export exercices     |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)               | Gestion système de cours    |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)       | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md)                         | Configuration sécurité      |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)                   | Configuration Apache        |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)                 | Intégration mascotte        |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive           |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md)                     | Gestion des URLs            |

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
- Documentation de la structure réelle (src/, dev/tools/, src/api/\*).
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
   - Test spécifique background : `npx playwright test dev/tools/tests/e2e/background.spec.js` attend que le `::before` pseudo-element ait une `background-image`.
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
src/includes/\*.php (Modèles/Services)
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
```

### Tableau des rôles

| Rôle         | Description    |
| ------------ | -------------- |
| `eleve`      | Élève          |
| `parent`     | Parent         |
| `professeur` | Professeur     |
| `admin`      | Administrateur |

---

## 📑 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)

7. [Documentation Technique](#documentation-technique)
8. [Scripts, outils & tests (index)](dev/tools/README.md)
9. [Sécurité](#sécurité)
10. [Maintenance](#maintenance)
11. [Annexe technique (historique)](#annexe-technique-historique)
12. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **1088+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique         | Valeur |
| ---------------- | ------ |
| Exercices totaux | 1088   |

---

## 🗂️ Migration exercices 2026 — Import, déduplication, correction

### Problèmes rencontrés

- Normalisation des champs JSON/BDD (27 champs attendus)
- Scripts d’inclusion : absence de getConnection() dans src/database/connection.php
- Dédoublonnage : détection d’identifiant incorrecte, 0 exercice unique
- Contraintes SQL : 12 exercices rejetés (exercises.Choices)

### Solutions apportées

- Refactorisation des scripts (collect, deduplicate, import) pour conformité BDD
- Ajout de la fonction getConnection() dans connection.php
- Debug et correction du script deduplicate_exercises_v3.php
- Correction automatique via fix_failed_exercises.php

### Scripts et outils utilisés

- dev/tools/exercises/collect_exercises_v2.php
- dev/tools/exercises/deduplicate_exercises_v3.php
- dev/tools/exercises/import_final_exercises.php
- dev/tools/exercises/fix_failed_exercises.php
- src/database/ExerciseNormalizer.php
- Logs d’import et de correction (import_final.log)

### Fichiers de vérité

- exercises_from_database.json (export BDD)
- unified_exercises.json (fusion JSON)
- exercises_final_deduplicated.json (résultat dédoublonné)

### Tests & validations

- Dry-run (import_final_exercises.php --dry-run)
- Analyse des logs SQL
- Correction ciblée (fix_failed_exercises.php)
- Rapport final d’import

### Résultats

- 1245 exercices importés, tous conformes
- 12 erreurs initiales corrigées automatiquement
- Base complète, aucun exercice ignoré

### Prochaines actions

- Archivage des scripts de migration
- Vérification en base (affichage, conformité Choices)
- Automatisation de la validation future

---

| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-12)

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

| Technologie   | Version | Usage                   |
| ------------- | ------- | ----------------------- |
| PHP           | 8.x     | Backend                 |
| MySQL/MariaDB | 10.x    | Base de données         |
| JavaScript    | ES6+    | Frontend interactif     |
| Bootstrap     | 5.x     | UI/UX                   |
| Apache        | 2.4     | Serveur web             |
| Composer      | 2.x     | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 1088 exercices structurés par niveau et matière
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

| Rôle   | URL                     | Identifiants par défaut |
| ------ | ----------------------- | ----------------------- |
| Admin  | `/dashboard_admin.php`  | admin / admin123        |
| Parent | `/dashboard_parent.php` | parent1 / pass123       |
| Élève  | `/dashboard.php`        | demo / demo123          |

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

### Scripts, outils & tests (centralisation)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste détaillée supprimée, remplacée par une référence croisée unique. -->

Résumé :

- Scripts d'automatisation : `dev/tools/`
- Scripts de test/debug : `dev/tools/tests/`
- Rapports d'audit : `dev/reports/`
- Documentation : `docs/`

---

### Exemples d'usage (voir README central pour plus)

```bash
# Import d'exercices (exemple)
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Export d'exercices (exemple)
php dev/tools/exercises/export_exercises.php json

# Maintenance (exemple)
php dev/tools/maintenance/enable_maintenance.php "Message"
php dev/tools/maintenance/disable_maintenance.php

# Lancer tous les tests unitaires
./vendor/bin/phpunit
```

> Pour tous les scripts, options et tests disponibles, se référer à [dev/tools/README.md](dev/tools/README.md).

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

| Document                                                        | Description                 |
| --------------------------------------------------------------- | --------------------------- |
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)           | Import/export exercices     |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)               | Gestion système de cours    |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)       | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md)                         | Configuration sécurité      |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)                   | Configuration Apache        |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)                 | Intégration mascotte        |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive           |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md)                     | Gestion des URLs            |

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
- Documentation de la structure réelle (src/, dev/tools/, src/api/\*).
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
   - Test spécifique background : `npx playwright test dev/tools/tests/e2e/background.spec.js` attend que le `::before` pseudo-element ait une `background-image`.
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
src/includes/\*.php (Modèles/Services)
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
```

### Tableau des rôles

| Rôle         | Description    |
| ------------ | -------------- |
| `eleve`      | Élève          |
| `parent`     | Parent         |
| `professeur` | Professeur     |
| `admin`      | Administrateur |

---

## 📑 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)

7. [Documentation Technique](#documentation-technique)
8. [Scripts, outils & tests (index)](dev/tools/README.md)
9. [Sécurité](#sécurité)
10. [Maintenance](#maintenance)
11. [Annexe technique (historique)](#annexe-technique-historique)
12. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **1088+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique         | Valeur |
| ---------------- | ------ |
| Exercices totaux | 1088   |

---

## 🗂️ Migration exercices 2026 — Import, déduplication, correction

### Problèmes rencontrés

- Normalisation des champs JSON/BDD (27 champs attendus)
- Scripts d’inclusion : absence de getConnection() dans src/database/connection.php
- Dédoublonnage : détection d’identifiant incorrecte, 0 exercice unique
- Contraintes SQL : 12 exercices rejetés (exercises.Choices)

### Solutions apportées

- Refactorisation des scripts (collect, deduplicate, import) pour conformité BDD
- Ajout de la fonction getConnection() dans connection.php
- Debug et correction du script deduplicate_exercises_v3.php
- Correction automatique via fix_failed_exercises.php

### Scripts et outils utilisés

- dev/tools/exercises/collect_exercises_v2.php
- dev/tools/exercises/deduplicate_exercises_v3.php
- dev/tools/exercises/import_final_exercises.php
- dev/tools/exercises/fix_failed_exercises.php
- src/database/ExerciseNormalizer.php
- Logs d’import et de correction (import_final.log)

### Fichiers de vérité

- exercises_from_database.json (export BDD)
- unified_exercises.json (fusion JSON)
- exercises_final_deduplicated.json (résultat dédoublonné)

### Tests & validations

- Dry-run (import_final_exercises.php --dry-run)
- Analyse des logs SQL
- Correction ciblée (fix_failed_exercises.php)
- Rapport final d’import

### Résultats

- 1245 exercices importés, tous conformes
- 12 erreurs initiales corrigées automatiquement
- Base complète, aucun exercice ignoré

### Prochaines actions

- Archivage des scripts de migration
- Vérification en base (affichage, conformité Choices)
- Automatisation de la validation future

---

| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-12)

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

| Technologie   | Version | Usage                   |
| ------------- | ------- | ----------------------- |
| PHP           | 8.x     | Backend                 |
| MySQL/MariaDB | 10.x    | Base de données         |
| JavaScript    | ES6+    | Frontend interactif     |
| Bootstrap     | 5.x     | UI/UX                   |
| Apache        | 2.4     | Serveur web             |
| Composer      | 2.x     | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 1088 exercices structurés par niveau et matière
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

| Rôle   | URL                     | Identifiants par défaut |
| ------ | ----------------------- | ----------------------- |
| Admin  | `/dashboard_admin.php`  | admin / admin123        |
| Parent | `/dashboard_parent.php` | parent1 / pass123       |
| Élève  | `/dashboard.php`        | demo / demo123          |

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

### Scripts, outils & tests (centralisation)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste détaillée supprimée, remplacée par une référence croisée unique. -->

Résumé :

- Scripts d'automatisation : `dev/tools/`
- Scripts de test/debug : `dev/tools/tests/`
- Rapports d'audit : `dev/reports/`
- Documentation : `docs/`

---

### Exemples d'usage (voir README central pour plus)

```bash
# Import d'exercices (exemple)
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Export d'exercices (exemple)
php dev/tools/exercises/export_exercises.php json

# Maintenance (exemple)
php dev/tools/maintenance/enable_maintenance.php "Message"
php dev/tools/maintenance/disable_maintenance.php

# Lancer tous les tests unitaires
./vendor/bin/phpunit
```

> Pour tous les scripts, options et tests disponibles, se référer à [dev/tools/README.md](dev/tools/README.md).

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

| Document                                                        | Description                 |
| --------------------------------------------------------------- | --------------------------- |
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)           | Import/export exercices     |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)               | Gestion système de cours    |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)       | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md)                         | Configuration sécurité      |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)                   | Configuration Apache        |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)                 | Intégration mascotte        |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive           |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md)                     | Gestion des URLs            |

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
- Documentation de la structure réelle (src/, dev/tools/, src/api/\*).
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
   - Test spécifique background : `npx playwright test dev/tools/tests/e2e/background.spec.js` attend que le `::before` pseudo-element ait une `background-image`.
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
src/includes/\*.php (Modèles/Services)
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
```

### Tableau des rôles

| Rôle         | Description    |
| ------------ | -------------- |
| `eleve`      | Élève          |
| `parent`     | Parent         |
| `professeur` | Professeur     |
| `admin`      | Administrateur |

---

## 📑 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)

7. [Documentation Technique](#documentation-technique)
8. [Scripts, outils & tests (index)](dev/tools/README.md)
9. [Sécurité](#sécurité)
10. [Maintenance](#maintenance)
11. [Annexe technique (historique)](#annexe-technique-historique)
12. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **1088+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique         | Valeur |
| ---------------- | ------ |
| Exercices totaux | 1088   |

---

## 🗂️ Migration exercices 2026 — Import, déduplication, correction

### Problèmes rencontrés

- Normalisation des champs JSON/BDD (27 champs attendus)
- Scripts d’inclusion : absence de getConnection() dans src/database/connection.php
- Dédoublonnage : détection d’identifiant incorrecte, 0 exercice unique
- Contraintes SQL : 12 exercices rejetés (exercises.Choices)

### Solutions apportées

- Refactorisation des scripts (collect, deduplicate, import) pour conformité BDD
- Ajout de la fonction getConnection() dans connection.php
- Debug et correction du script deduplicate_exercises_v3.php
- Correction automatique via fix_failed_exercises.php

### Scripts et outils utilisés

- dev/tools/exercises/collect_exercises_v2.php
- dev/tools/exercises/deduplicate_exercises_v3.php
- dev/tools/exercises/import_final_exercises.php
- dev/tools/exercises/fix_failed_exercises.php
- src/database/ExerciseNormalizer.php
- Logs d’import et de correction (import_final.log)

### Fichiers de vérité

- exercises_from_database.json (export BDD)
- unified_exercises.json (fusion JSON)
- exercises_final_deduplicated.json (résultat dédoublonné)

### Tests & validations

- Dry-run (import_final_exercises.php --dry-run)
- Analyse des logs SQL
- Correction ciblée (fix_failed_exercises.php)
- Rapport final d’import

### Résultats

- 1245 exercices importés, tous conformes
- 12 erreurs initiales corrigées automatiquement
- Base complète, aucun exercice ignoré

### Prochaines actions

- Archivage des scripts de migration
- Vérification en base (affichage, conformité Choices)
- Automatisation de la validation future

---

| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-12)

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

| Technologie   | Version | Usage                   |
| ------------- | ------- | ----------------------- |
| PHP           | 8.x     | Backend                 |
| MySQL/MariaDB | 10.x    | Base de données         |
| JavaScript    | ES6+    | Frontend interactif     |
| Bootstrap     | 5.x     | UI/UX                   |
| Apache        | 2.4     | Serveur web             |
| Composer      | 2.x     | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 1088 exercices structurés par niveau et matière
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

| Rôle   | URL                     | Identifiants par défaut |
| ------ | ----------------------- | ----------------------- |
| Admin  | `/dashboard_admin.php`  | admin / admin123        |
| Parent | `/dashboard_parent.php` | parent1 / pass123       |
| Élève  | `/dashboard.php`        | demo / demo123          |

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

### Scripts, outils & tests (centralisation)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste détaillée supprimée, remplacée par une référence croisée unique. -->

Résumé :

- Scripts d'automatisation : `dev/tools/`
- Scripts de test/debug : `dev/tools/tests/`
- Rapports d'audit : `dev/reports/`
- Documentation : `docs/`

---

### Exemples d'usage (voir README central pour plus)

```bash
# Import d'exercices (exemple)
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Export d'exercices (exemple)
php dev/tools/exercises/export_exercises.php json

# Maintenance (exemple)
php dev/tools/maintenance/enable_maintenance.php "Message"
php dev/tools/maintenance/disable_maintenance.php

# Lancer tous les tests unitaires
./vendor/bin/phpunit
```

> Pour tous les scripts, options et tests disponibles, se référer à [dev/tools/README.md](dev/tools/README.md).

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

| Document                                                        | Description                 |
| --------------------------------------------------------------- | --------------------------- |
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)           | Import/export exercices     |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)               | Gestion système de cours    |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)       | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md)                         | Configuration sécurité      |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)                   | Configuration Apache        |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)                 | Intégration mascotte        |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive           |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md)                     | Gestion des URLs            |

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
- Documentation de la structure réelle (src/, dev/tools/, src/api/\*).
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
   - Test spécifique background : `npx playwright test dev/tools/tests/e2e/background.spec.js` attend que le `::before` pseudo-element ait une `background-image`.
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
src/includes/\*.php (Modèles/Services)
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
```

### Tableau des rôles

| Rôle         | Description    |
| ------------ | -------------- |
| `eleve`      | Élève          |
| `parent`     | Parent         |
| `professeur` | Professeur     |
| `admin`      | Administrateur |

---

## 📑 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)

7. [Documentation Technique](#documentation-technique)
8. [Scripts, outils & tests (index)](dev/tools/README.md)
9. [Sécurité](#sécurité)
10. [Maintenance](#maintenance)
11. [Annexe technique (historique)](#annexe-technique-historique)
12. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **1088+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique         | Valeur |
| ---------------- | ------ |
| Exercices totaux | 1088   |

---

## 🗂️ Migration exercices 2026 — Import, déduplication, correction

### Problèmes rencontrés

- Normalisation des champs JSON/BDD (27 champs attendus)
- Scripts d’inclusion : absence de getConnection() dans src/database/connection.php
- Dédoublonnage : détection d’identifiant incorrecte, 0 exercice unique
- Contraintes SQL : 12 exercices rejetés (exercises.Choices)

### Solutions apportées

- Refactorisation des scripts (collect, deduplicate, import) pour conformité BDD
- Ajout de la fonction getConnection() dans connection.php
- Debug et correction du script deduplicate_exercises_v3.php
- Correction automatique via fix_failed_exercises.php

### Scripts et outils utilisés

- dev/tools/exercises/collect_exercises_v2.php
- dev/tools/exercises/deduplicate_exercises_v3.php
- dev/tools/exercises/import_final_exercises.php
- dev/tools/exercises/fix_failed_exercises.php
- src/database/ExerciseNormalizer.php
- Logs d’import et de correction (import_final.log)

### Fichiers de vérité

- exercises_from_database.json (export BDD)
- unified_exercises.json (fusion JSON)
- exercises_final_deduplicated.json (résultat dédoublonné)

### Tests & validations

- Dry-run (import_final_exercises.php --dry-run)
- Analyse des logs SQL
- Correction ciblée (fix_failed_exercises.php)
- Rapport final d’import

### Résultats

- 1245 exercices importés, tous conformes
- 12 erreurs initiales corrigées automatiquement
- Base complète, aucun exercice ignoré

### Prochaines actions

- Archivage des scripts de migration
- Vérification en base (affichage, conformité Choices)
- Automatisation de la validation future

---

| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-12)

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

| Technologie   | Version | Usage                   |
| ------------- | ------- | ----------------------- |
| PHP           | 8.x     | Backend                 |
| MySQL/MariaDB | 10.x    | Base de données         |
| JavaScript    | ES6+    | Frontend interactif     |
| Bootstrap     | 5.x     | UI/UX                   |
| Apache        | 2.4     | Serveur web             |
| Composer      | 2.x     | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 1088 exercices structurés par niveau et matière
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

| Rôle   | URL                     | Identifiants par défaut |
| ------ | ----------------------- | ----------------------- |
| Admin  | `/dashboard_admin.php`  | admin / admin123        |
| Parent | `/dashboard_parent.php` | parent1 / pass123       |
| Élève  | `/dashboard.php`        | demo / demo123          |

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

### Scripts, outils & tests (centralisation)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste détaillée supprimée, remplacée par une référence croisée unique. -->

Résumé :

- Scripts d'automatisation : `dev/tools/`
- Scripts de test/debug : `dev/tools/tests/`
- Rapports d'audit : `dev/reports/`
- Documentation : `docs/`

---

### Exemples d'usage (voir README central pour plus)

```bash
# Import d'exercices (exemple)
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Export d'exercices (exemple)
php dev/tools/exercises/export_exercises.php json

# Maintenance (exemple)
php dev/tools/maintenance/enable_maintenance.php "Message"
php dev/tools/maintenance/disable_maintenance.php

# Lancer tous les tests unitaires
./vendor/bin/phpunit
```

> Pour tous les scripts, options et tests disponibles, se référer à [dev/tools/README.md](dev/tools/README.md).

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

| Document                                                        | Description                 |
| --------------------------------------------------------------- | --------------------------- |
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)           | Import/export exercices     |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)               | Gestion système de cours    |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)       | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md)                         | Configuration sécurité      |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)                   | Configuration Apache        |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)                 | Intégration mascotte        |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive           |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md)                     | Gestion des URLs            |

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
- Documentation de la structure réelle (src/, dev/tools/, src/api/\*).
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
   - Test spécifique background : `npx playwright test dev/tools/tests/e2e/background.spec.js` attend que le `::before` pseudo-element ait une `background-image`.
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
src/includes/\*.php (Modèles/Services)
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
```

### Tableau des rôles

| Rôle         | Description    |
| ------------ | -------------- |
| `eleve`      | Élève          |
| `parent`     | Parent         |
| `professeur` | Professeur     |
| `admin`      | Administrateur |

---

## 📑 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Fonctionnalités](#fonctionnalités)
4. [Installation & Configuration](#installation--configuration)
5. [Utilisation](#utilisation)
6. [Développement](#développement)

7. [Documentation Technique](#documentation-technique)
8. [Scripts, outils & tests (index)](dev/tools/README.md)
9. [Sécurité](#sécurité)
10. [Maintenance](#maintenance)
11. [Annexe technique (historique)](#annexe-technique-historique)
12. [Mises à jour & versions](#historique-des-versions)

---

## 🎯 Vue d'ensemble

**MonCoachScolaire** est une plateforme web complète d'accompagnement scolaire offrant :

- 📝 **1088+ exercices interactifs** (Mathématiques, Français, Anglais, Sciences, etc.)
- 🎓 **Cours structurés** du collège (6ème) au lycée (Terminale/BAC)
- 👥 **Dashboards multi-rôles** (Élève, Parent, Administrateur)
- 🤖 **Mascotte interactive** (Colibri) avec animations WebM
- 📊 **Suivi de progression** personnalisé
- 🔐 **Système d'authentification** sécurisé avec gestion de rôles

### Statistiques clés

| Métrique         | Valeur |
| ---------------- | ------ |
| Exercices totaux | 1088   |

---

## 🗂️ Migration exercices 2026 — Import, déduplication, correction

### Problèmes rencontrés

- Normalisation des champs JSON/BDD (27 champs attendus)
- Scripts d’inclusion : absence de getConnection() dans src/database/connection.php
- Dédoublonnage : détection d’identifiant incorrecte, 0 exercice unique
- Contraintes SQL : 12 exercices rejetés (exercises.Choices)

### Solutions apportées

- Refactorisation des scripts (collect, deduplicate, import) pour conformité BDD
- Ajout de la fonction getConnection() dans connection.php
- Debug et correction du script deduplicate_exercises_v3.php
- Correction automatique via fix_failed_exercises.php

### Scripts et outils utilisés

- dev/tools/exercises/collect_exercises_v2.php
- dev/tools/exercises/deduplicate_exercises_v3.php
- dev/tools/exercises/import_final_exercises.php
- dev/tools/exercises/fix_failed_exercises.php
- src/database/ExerciseNormalizer.php
- Logs d’import et de correction (import_final.log)

### Fichiers de vérité

- exercises_from_database.json (export BDD)
- unified_exercises.json (fusion JSON)
- exercises_final_deduplicated.json (résultat dédoublonné)

### Tests & validations

- Dry-run (import_final_exercises.php --dry-run)
- Analyse des logs SQL
- Correction ciblée (fix_failed_exercises.php)
- Rapport final d’import

### Résultats

- 1245 exercices importés, tous conformes
- 12 erreurs initiales corrigées automatiquement
- Base complète, aucun exercice ignoré

### Prochaines actions

- Archivage des scripts de migration
- Vérification en base (affichage, conformité Choices)
- Automatisation de la validation future

---

| Niveaux couverts | 8 (6ème → BAC) |
| Matières | 9 (Math, Français, Anglais, Sciences, etc.) |
| Utilisateurs actifs | Gestion multi-utilisateurs |
| Type de déploiement | Hybride (local/production) |

---

## 🏗️ Architecture

### Structure réelle (2026-02-12)

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

| Technologie   | Version | Usage                   |
| ------------- | ------- | ----------------------- |
| PHP           | 8.x     | Backend                 |
| MySQL/MariaDB | 10.x    | Base de données         |
| JavaScript    | ES6+    | Frontend interactif     |
| Bootstrap     | 5.x     | UI/UX                   |
| Apache        | 2.4     | Serveur web             |
| Composer      | 2.x     | Gestion dépendances PHP |

---

## ✨ Fonctionnalités

### 🎓 Gestion des exercices

- **Bibliothèque d'exercices** : 1088 exercices structurés par niveau et matière
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

| Rôle   | URL                     | Identifiants par défaut |
| ------ | ----------------------- | ----------------------- |
| Admin  | `/dashboard_admin.php`  | admin / admin123        |
| Parent | `/dashboard_parent.php` | parent1 / pass123       |
| Élève  | `/dashboard.php`        | demo / demo123          |

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

### Scripts, outils & tests (centralisation)

> 📖 **Pour en savoir plus :** Voir [dev/tools/README.md](dev/tools/README.md)

<!-- Déduplication appliquée le 2026-02-14 : Liste détaillée supprimée, remplacée par une référence croisée unique. -->

Résumé :

- Scripts d'automatisation : `dev/tools/`
- Scripts de test/debug : `dev/tools/tests/`
- Rapports d'audit : `dev/reports/`
- Documentation : `docs/`

---

### Exemples d'usage (voir README central pour plus)

```bash
# Import d'exercices (exemple)
php dev/tools/exercises/import_exercises.php exercices/fichier.sql

# Export d'exercices (exemple)
php dev/tools/exercises/export_exercises.php json

# Maintenance (exemple)
php dev/tools/maintenance/enable_maintenance.php "Message"
php dev/tools/maintenance/disable_maintenance.php

# Lancer tous les tests unitaires
./vendor/bin/phpunit
```

> Pour tous les scripts, options et tests disponibles, se référer à [dev/tools/README.md](dev/tools/README.md).

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

| Document                                                        | Description                 |
| --------------------------------------------------------------- | --------------------------- |
| [INTEGRATION_SOURCES.md](docs/INTEGRATION_SOURCES.md)           | Import/export exercices     |
| [INTEGRATION_COURS.md](docs/INTEGRATION_COURS.md)               | Gestion système de cours    |
| [GUIDE-DASHBOARD-ADMIN.md](docs/GUIDE-DASHBOARD-ADMIN.md)       | Utilisation dashboard admin |
| [SECURITE-ENV.md](docs/SECURITE-ENV.md)                         | Configuration sécurité      |
| [HTACCESS_CONFIG.md](docs/HTACCESS_CONFIG.md)                   | Configuration Apache        |
| [MASCOTTE-COLIBRI.md](docs/MASCOTTE-COLIBRI.md)                 | Intégration mascotte        |
| [RESPONSIVE-DESIGN-SYSTEM.md](docs/RESPONSIVE-DESIGN-SYSTEM.md) | Design responsive           |
| [URL_MANAGEMENT.md](docs/URL_MANAGEMENT.md)                     | Gestion des URLs            |

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

## 🛡️ Sécurité
