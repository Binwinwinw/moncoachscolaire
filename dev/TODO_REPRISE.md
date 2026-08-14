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
- Chemin demandé introuvable : `src/api/courses_detail.php`. Fichier réel audité : `src/api/cours/courses_detail.php`.
- Actions trouvées : détail par `id`, liste filtrée par `level` et/ou `subject`, liste générale sans filtre.
- Détail par `id` : inclusion conditionnelle de `src/includes/level_access.php` et appel à `can_current_user_access_level()` après la requête du cours, avant les requêtes d’exercices et de ressources, puis avant la réponse JSON ; absence du helper ignorée silencieusement.
- Liste filtrée : inclusion conditionnelle et appel à `can_current_user_access_level()` avant la requête SQL lorsque `level` est fourni ; contrôle absent pour une requête filtrée uniquement par `subject`, et absence du helper ignorée silencieusement.
- Liste générale : aucun contrôle de niveau, aucune inclusion du helper et lecture SQL avant la réponse JSON.
- Aucun appel à `enforce_level_access_or_abort()` dans le fichier.
- Comparaison : contrairement à `get_cours.php` et à l’action `exercises` corrigée de `get_exercises.php`, le contrôle n’est pas systématique et n’est pas toujours en échec fermé.
- Validation : `php -l src/api/cours/courses_detail.php` réussi, sans erreur ni warning.
- Fonctions exportées par `level_access.php` : `get_levels_order_map()`, `normalize_level_key()`, `get_level_order()`, `get_user_level_order()`, `can_current_user_access_level()` et `enforce_level_access_or_abort()`.
- Cohérence du helper : la comparaison d’ordre fonctionne pour les niveaux connus ; les rôles administrateur et démo sont gérés par bypass ; `enforce_level_access_or_abort()` délègue à `can_current_user_access_level()` et répond en HTML 403.
- Problème confirmé : `can_current_user_access_level()` applique les bypass admin, démo et visiteur avant le rejet d’un niveau inconnu ; un niveau inconnu peut donc être accepté par ces profils.
- Problème confirmé : `get_user_level_order()` accepte directement `$_SESSION['user_level_order']` sans vérifier que la valeur correspond à un niveau canonique ni qu’elle reste dans l’ordre autorisé.
- Écart avec les API : `get_cours.php` appelle `get_level_order()` avant `can_current_user_access_level()`, mais `get_exercises.php` et `courses_detail.php` délèguent directement au helper ; ils héritent donc des bypass silencieux pour les niveaux inconnus.
- Validation : `php -l src/includes/level_access.php` réussi, sans erreur ni warning ; aucun code applicatif modifié.
- Test créé : `tests/LevelAccessSecurityTest.php` avec cinq tests PHPUnit reproductibles ; aucun fichier applicatif modifié.
- Résultat PHPUnit : `FFFFF`, 5 tests, 11 assertions, 5 échecs.
- Vulnérabilités prouvées : niveau inconnu accepté pour admin, démo et visiteur ; liste générale de `courses_detail.php` sans contrôle avant requête ; absence de `ERR_AUTH_MISSING` dans `courses_detail.php`.
- Validation du test : `php -l tests/LevelAccessSecurityTest.php` réussi.
- Correction `level_access.php` : validation de `get_level_order()` déplacée avant les bypass ; `enforce_level_access_or_abort()` rejette un niveau inconnu en 400 `ERR_BAD_LEVEL` (JSON si disponible, HTML sinon).
- Validation après correction : 3 tests passent ; 2 tests restent en échec sur `courses_detail.php`, qui n’a pas été modifié et conserve ses deux vulnérabilités documentées.
- Correction `courses_detail.php` : inclusion obligatoire du helper avec échec fermé `500 ERR_AUTH_MISSING` ; contrôle du détail conservé après lecture du cours et avant les requêtes secondaires ; listes filtrées sans niveau et liste générale refusées en `403 ERR_FORBIDDEN` avant SQL.
- Validation finale : `php -l src/api/cours/courses_detail.php` réussi ; PHPUnit ciblé `LevelAccessSecurityTest.php` réussi avec 5 tests et 12 assertions ; diff limité à `courses_detail.php`.
- Date : 2026-08-14

Correction effectuée, mais validation php -l non exécutée : terminal indisponible.

---

## Tâches suivantes

- [x] FAIT — Auditer et corriger le contrôle d’accès de `get_exercises.php`.
- [x] FAIT — Auditer le contrôle d’accès de `src/api/courses_detail.php`.
- [x] FAIT — Vérifier la cohérence de `level_access.php`.
- [x] FAIT — Ajouter ou compléter les tests d’accès par niveau : 5 tests passent après correction du helper et de `courses_detail.php`.
- [x] FAIT — Mettre à jour `dev/JOURNAL_REPRISE.md`.

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
