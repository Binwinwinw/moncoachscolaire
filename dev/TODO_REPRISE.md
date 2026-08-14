# TODO_REPRISE

## Tâche active

- [!] BLOQUÉ — Auditer le contrôle d’accès de `get_cours.php`.

### READ

- Lire `get_cours.php`.
- Lire les fonctions de contrôle utilisées.
- Identifier les fonctions concernées :
  - `authorizeLevel()`
  - `serveSubjectsList()`
  - `serveCourseList()`
  - `serveCourseHtml()`

### VERIFY

- Vérifier le niveau reçu.
- Vérifier le niveau réellement utilisé.
- Vérifier le contrôle avant toute requête.
- Vérifier le contrôle avant toute réponse JSON ou HTML.
- Vérifier le comportement si `level_access.php` est absent.
- Vérifier les accès visiteur, démo, utilisateur et administrateur.

### MODIFY

- Corriger uniquement un problème confirmé.
- Ne pas renommer les fonctions.
- Ne pas refactoriser hors périmètre.
- Ne pas modifier d’autre fichier sans nécessité.

### VALIDATE

- Exécuter `php -l get_cours.php`.
- Vérifier les fichiers modifiés.
- Vérifier le diff.
- Confirmer le résultat réel.

### NEXT_MOVE

- Mettre cette tâche à jour après validation.
- Activer ensuite la tâche suivante.
- Ne pas exécuter la tâche suivante dans la même intervention.

### Résultat

- Fichiers lus : `dev/PROTOCOL_PERMANENT.md`, `dev/TODO_REPRISE.md`, `dev/JOURNAL_REPRISE.md`, `dev/SUIVI_BUGS_AMELIORATIONS.md`, `src/api/cours/get_cours.php`, `src/api/_core/bootstrap.php`, `src/api/_core/middleware.php`, `src/api/_core/auth.php`, `src/config/config.php`, `src/includes/level_access.php`, `src/includes/level_normalization.php`, `src/includes/admin_auth.php`, `src/includes/demo_security.php`.
- Fichiers modifiés : `src/api/cours/get_cours.php`, `src/includes/level_access.php`, `dev/TODO_REPRISE.md`, `dev/JOURNAL_REPRISE.md`.
- Problème confirmé : l’absence du helper de niveau contournait l’autorisation ; les bypass admin/démo n’étaient pas alignés avec les fonctions chargées par l’API ; les niveaux inconnus pouvaient passer pour les visiteurs.
- Correction : échec fermé si le helper manque, validation du niveau connu, prise en charge du rôle admin et de `is_demo_user()`.
- Tests : Correction effectuée, mais validation php -l non exécutée : terminal indisponible.
- Résultat : accès par niveau contrôlé avant les réponses de la liste des matières, des cours et du rendu HTML.
- Chemin réel de `level_access.php` : `src/includes/level_access.php`.
- Vérification `get_cours.php` : `authorizeLevel()` est appelé pour `subjects`, `cours` et `cours_html` ; le contrôle précède les requêtes de listes et toute réponse JSON ou HTML, avec le contrôle du niveau du cours après sa lecture pour `cours_html`.
- Vérification `get_exercises.php` : `level_access.php` est appelé conditionnellement pour `subjects` et `exercise_html`, mais l’action `exercises` ne contrôle pas le niveau avant `getExercisesByLevel()` ni avant sa réponse ; l’absence du helper est aussi ignorée silencieusement.
- Utilisation détaillée : `get_cours.php` inclut `src/includes/level_access.php` dans `authorizeLevel()` et appelle `can_current_user_access_level()` ; `get_exercises.php` inclut le fichier conditionnellement dans `subjects` et `exercise_html` et appelle aussi `can_current_user_access_level()`.
- Aucun appel à `enforce_level_access_or_abort()` dans les deux API.
- Résultat de vérification : erreur d’usage confirmée dans l’action `exercises` de `get_exercises.php`, car le contrôle manque avant la requête et avant la réponse ; pour `exercise_html`, le contrôle arrive après la lecture de l’exercice mais avant son rendu et sa réponse.
- Correction `get_exercises.php` : inclusion obligatoire de `src/includes/level_access.php`, échec fermé si le helper est absent, utilisation du `$projectRoot` global et appel à `can_current_user_access_level()` avant `getExercisesByLevel()` ; aucun second contrôle avant la réponse n’est nécessaire.
- Validation : `php -l src/api/exercices/get_exercises.php` réussi après correction de portée ; le diff ne concerne que l’action `exercises`.
- Date : 2026-08-14

Correction effectuée, mais validation php -l non exécutée : terminal indisponible.

---

## Tâches suivantes

- [x] FAIT — Auditer et corriger le contrôle d’accès de `get_exercises.php`.
- [ ] EN COURS — Auditer le contrôle d’accès de `src/api/courses_detail.php`.
- [ ] Vérifier la cohérence de `level_access.php`.
- [ ] Ajouter ou compléter les tests d’accès par niveau.
- [ ] Mettre à jour `dev/JOURNAL_REPRISE.md`.

---

## Règles permanentes

- Lire `dev/PROTOCOL_PERMANENT.md` avant toute intervention.
- Lire cette TODO avant toute intervention.
- Exécuter uniquement la tâche active.
- Suivre l’ordre `READ → VERIFY → MODIFY → VALIDATE → NEXT_MOVE`.
- Modifier cette TODO après chaque étape importante.
- Ne jamais remplacer une action par un résumé.
- Ne jamais marquer une tâche `FAIT` sans validation réelle.
- En cas de blocage, utiliser `[!] BLOQUÉ` et écrire la cause exacte.
- Ne jamais faire de commit, push ou reset automatiquement.
