# Workflow d’affichage des exercices (22/02/2026)

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
