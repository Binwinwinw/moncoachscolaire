


# Schéma officiel des exercices (JSON)

---
## Scripts officiels d’import/export et dédoublonnage

| Script PHP                                 | Rôle métier / fonction principale                                                                 |
|--------------------------------------------|---------------------------------------------------------------------------------------------------|
| export_exercises_from_db.php               | Extraction complète des exercices depuis la BDD vers un fichier JSON conforme au schéma officiel  |
| collect_exercises.php                      | Collecte et fusion de tous les fichiers JSON migrés en un fichier unifié, normalisé               |
| deduplicate_exercises.php                  | Analyse, détection et suppression des doublons entre BDD et JSON migrés, génération du rapport    |
| import_unified_exercises.php               | Import du fichier JSON unifié dans la BDD, avec validation et mise à jour des exercices           |
| ExerciseParser.php                         | Parsing, validation et structuration des exercices selon le schéma officiel                       |

> Chaque script est identifiable par son nom et son rôle métier. Toute évolution doit être documentée ici.
---

---
## En-tête du schéma JSON

Le fichier `schema_parsing_exercice.json` contient en tête un bloc de documentation (non valide JSON, mais lisible pour les développeurs) :

```
/*
 * =============================================================
 *  Schéma officiel des exercices JSON — MonCoachScolaire
 * =============================================================
 *
 * Ce fichier définit la structure officielle des champs d’un exercice.
 *
 * Référence croisée :
 *   - Documentation Markdown : schema_parsing_exercice.md (même dossier)
 *   - Usage : création, validation, import/export d’exercices
 *   - À jour au : 20 janvier 2026
 *
 * Toute évolution doit être synchronisée avec le Markdown et les outils PHP.
 *
 * Important : ne rien supprimer sans validation, ajouter les nouveaux champs en respectant la compatibilité ascendante.
 *
 * Champs additionnels :
 *   Certains fichiers d’exercices peuvent contenir des champs supplémentaires (Type, exam_prep, processed, course_id, LinkedCourses, InteractiveConfig…).
 *   Ces champs sont tolérés et doivent être préservés par les outils, mais ceux listés dans ce schéma sont obligatoires.
 *
 * =============================================================
 */
```

**Règles :**
- Ce bloc rappelle la référence, la synchronisation, la date de mise à jour et la gestion des champs additionnels.
- Les commentaires ne sont pas autorisés dans le JSON lui-même : ce bloc est à usage documentaire, à lire dans le Markdown.
- Toute modification du schéma doit être documentée ici et synchronisée dans le JSON et les outils PHP.
---

---
**Ce fichier est la référence unique du schéma des exercices pour MonCoachScolaire.**

- Synchronisé avec : `schema_parsing_exercice.json` (même dossier)
- À jour au : 20 janvier 2026
- Toute modification doit être répercutée dans le JSON et les outils PHP associés.
- Ne rien supprimer sans validation, ajouter les nouveaux champs en respectant la compatibilité ascendante.
---

Depuis janvier 2026, ce fichier documente le schéma officiel des champs de la table `exercises` de la base de données MonCoachScolaire.

- **Emplacement original JSON** : `db/json/schema_parsing_exercice.json`
- **Usage** : référence unique pour la création, la validation et l’import d’exercices.
- **À jour au** : 20 janvier 2026



## Structure complète des champs (alignée sur la table SQL)

| Champ              | Description                                                        | Exemple/valeurs possibles                                  |
|--------------------|--------------------------------------------------------------------|------------------------------------------------------------|
| Id                 | Identifiant unique auto-incrémenté                                | 1                                                          |
| Subject            | Matière                                                            | "Francais", "Mathématiques"                               |
| Level              | Niveau scolaire                                                    | "6eme", "5eme", "Terminale"                              |
| Title              | Titre de l'exercice                                                | "Exercice 1 : Accords..."                                 |
| Content            | Énoncé de l'exercice                                               | "Consigne ou énoncé principal"                            |
| structure_type     | Structure (simple, multi-parties)                                 | "simple", "multi-parties"                                 |
| pattern_detected   | Pattern détecté (si parsing)                                      | null, "lettres_min_parenthese"                            |
| sub_questions      | Sous-questions (JSON)                                             | null, [{"id":"a","ordre":1,"enonce":"..."}]           |
| Type               | Type d’exercice (optionnel)                                       | null, "qcm", "texte"                                      |
| Answer             | Réponse attendue ou correction                                    | ["réponse"], ["a", "b"]                                  |
| InteractiveConfig  | Config interactive (JSON optionnel)                               | null, {"timer":30}                                        |
| Tips               | Conseils ou astuces pour l’élève                                  | "Pense à relire..."                                       |
| Domain             | Domaine pédagogique                                               | "Grammaire", "Calcul"                                     |
| Competence         | Compétence visée                                                  | "Accorder les adjectifs"                                  |
| Difficulty         | Niveau de difficulté                                              | "facile", "moyen", "difficile"                           |
| exam_prep          | Préparation examen (optionnel)                                    | null, "BAC", "BREVET"                                     |
| Identifier         | Identifiant unique de l’exercice                                  | "MATHEMATIQUES-6EME-ADDITION-001"                         |
| AnswerType         | Type de réponse attendue                                          | "texte", "qcm", "association"                            |
| Choices            | Choix possibles (QCM, association) ou null pour texte libre        | null, ["choix1", "choix2"]                                |
| Instruction        | Consigne détaillée pour l’élève                                   | "Lis attentivement..."                                    |
| is_active          | Exercice activé ou non                                            | true / false                                               |
| XP_Points          | Points d’expérience attribués à la réussite                       | 5, 10, ...                                                 |
| Coherence          | Indique si l’exercice est cohérent avec le programme scolaire     | true / false                                               |
| processed          | Indique si l’exercice a été parsé/traité                          | 0, 1                                                       |
| course_id          | ID du cours lié (optionnel)                                      | null, 42                                                   |
| LinkedCourses      | Cours liés (JSON array optionnel)                                | null, [1,2,3]                                              |

> **Note** : Certains champs sont optionnels ou contextuels selon l’usage (interop, tracking, parsing…).


## Exemple de structure JSON

```json
[
  {
    "Id": 1,
    "Subject": "Matières",
    "Level": "Niveaux scolaires",
    "Title": "Titre de l’exercice",
    "Content": "Enoncé de l’exercice, consigne, question…",
    "structure_type": "questionnaire",
    "pattern_detected": "exercice_choix_multiple",
    "sub_questions": "Si applicable, liste des sous-questions pour les exercices complexes",
    "Type": "Type d’exercice (ex : QCM, vrai/faux, rédaction…)",
    "Answer": "Réponse attendue (texte, choix, etc.)",
    "InteractiveConfig": "Configuration spécifique pour les exercices interactifs (si applicable)",
    "Tips": "Conseils ou indices pour aider l’élève à résoudre l’exercice",
    "Domain": "Domaine de compétence ciblé (ex : grammaire, mathématiques, histoire…)",
    "Competence": "Compétence spécifique visée par l’exercice (ex : accorder les adjectifs)",
    "Difficulty": "Niveau de difficulté (ex : facile, moyen, difficile)",
    "exam_prep": "Indique si l’exercice est destiné à la préparation d’examens (true/false)",
    "Identifier": "Un identifiant unique pour l’exercice, utilisé pour le suivi et les statistiques",
    "AnswerType": "Type de réponse attendu (ex : texte libre, choix multiple, etc.)",
    "Choices": "Liste des choix possibles pour les exercices à choix multiple (si applicable)",
    "Instruction": "Instruction détaillée pour l’élève, expliquant comment répondre à l’exercice",
    "is_active": "Indique si l’exercice est actif et disponible pour les élèves (true/false)",
    "XP_Points": "Points d'expérience attribués pour la réussite de l’exercice",
    "Coherence": "Indique si l’exercice est cohérent avec le reste du cours (true/false)",
    "processed": "Indique si l’exercice a été traité par le parser (true/false)",
    "course_id": "Identifiant du cours associé à l’exercice (si applicable)",
    "LinkedCourses": "Liste des cours liés à l’exercice (si applicable)"
  }
]

```

> **Important** : Ce schéma fait foi pour tout développement ou modification liée aux exercices. Toujours vérifier sa dernière version avant toute intervention sur la BDD ou les scripts associés.
