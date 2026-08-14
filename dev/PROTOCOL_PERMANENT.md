# PROTOCOL_PERMANENT

## Objectif

Ce protocole définit la méthode obligatoire pour reprendre et faire progresser le projet MonCoachScolaire.

Fichiers obligatoires :

- `dev/PROTOCOL_PERMANENT.md` : protocole de travail ;
- `dev/TODO_REPRISE.md` : tâche active et prochaines tâches ;
- `dev/JOURNAL_REPRISE.md` : historique des actions ;
- `dev/SUIVI_BUGS_AMELIORATIONS.md` : bugs et améliorations.

La liste des tâches se trouve dans :

`dev/TODO_REPRISE.md`

Ce fichier est un fichier de travail vivant. Il doit rester modifiable à chaque étape.

---

## Cycle obligatoire

Chaque intervention doit suivre cet ordre exact :

1. READ
2. VERIFY_BEFORE
3. MODIFY
4. VERIFY_AFTER
5. VALIDATE
6. UPDATE_TODO
7. UPDATE_JOURNAL
8. UPDATE_BUGS_IF_NEEDED
9. NEXT_MOVE

Ne jamais remplacer une étape par un résumé.

---

## VERIFY_BEFORE

- confirmer le problème ;
- vérifier le périmètre ;
- vérifier les fichiers concernés ;
- confirmer qu’une modification est nécessaire ;
- définir le résultat attendu.

---

## VERIFY_AFTER

- relire les fichiers modifiés ;
- vérifier le diff ;
- confirmer que la modification correspond à l’objectif ;
- vérifier qu’aucun fichier hors périmètre n’a été modifié ;
- rechercher les effets secondaires évidents.

---

## VALIDATE

- exécuter la syntaxe ;
- exécuter les tests ;
- effectuer les vérifications fonctionnelles ;
- corriger les erreurs puis recommencer VERIFY_AFTER et VALIDATE.

---

## 1. READ

Lire obligatoirement :

- `dev/PROTOCOL_PERMANENT.md`
- `dev/TODO_REPRISE.md`

Identifier :

- la première tâche marquée `[ ] EN COURS` ;
- sinon, la première tâche `[ ]` non terminée ;
- les fichiers concernés ;
- l’objectif ;
- la validation attendue.

Lire uniquement les fichiers nécessaires à la tâche active.

Ne pas changer de tâche pendant cette intervention.

---

## 2. VERIFY

Vérifier l’état réel avant toute modification :

- inspecter le code concerné ;
- rechercher les fonctions et appels associés ;
- vérifier les dépendances ;
- vérifier les tests disponibles ;
- reproduire le problème si possible ;
- comparer le comportement attendu et le comportement réel ;
- vérifier l’état Git et les modifications locales.

À la fin de cette étape, décider :

- `MODIFICATION_NÉCESSAIRE` ;
- `AUCUNE_MODIFICATION_NÉCESSAIRE` ;
- `BLOQUÉ`.

Ne jamais modifier le code uniquement sur une hypothèse.

Si aucune modification n’est nécessaire, documenter la vérification dans `dev/TODO_REPRISE.md`, puis passer à `VALIDATE`.

---

## 3. MODIFY

Si une modification est nécessaire :

- modifier uniquement les fichiers du périmètre ;
- appliquer la correction minimale ;
- conserver le comportement fonctionnel existant ;
- réutiliser les helpers existants ;
- ne pas créer de doublons ;
- ne pas faire de refactorisation générale ;
- ne pas modifier les fichiers sans rapport ;
- préserver les modifications locales ;
- ne faire aucun commit, push, reset ou suppression.

Si la vérification a conclu qu’aucune modification n’est nécessaire, ne pas créer de modification artificielle.

---

## 4. VALIDATE

Après la modification, effectuer les vérifications appropriées :

- syntaxe ;
- tests automatisés ;
- tests ciblés ;
- imports et includes ;
- routes et appels ;
- gestion des erreurs ;
- comportement avant et après ;
- inspection de `git diff`.

Ne jamais déclarer une validation sans avoir réellement exécuté la vérification.

La tâche est validée uniquement si :

- la modification répond à l’objectif ;
- aucune erreur nouvelle n’est détectée ;
- les tests passent ou les vérifications prévues sont concluantes ;
- le périmètre est respecté.

En cas d’échec :

- corriger le problème ;
- recommencer la validation ;
- ne pas marquer la tâche comme terminée.

---

## 5. NEXT_MOVE

Après validation :

1. modifier `dev/TODO_REPRISE.md` ;
2. remplacer `[ ] EN COURS` par `[x] FAIT` ;
3. ajouter les fichiers concernés ;
4. ajouter les modifications réalisées ;
5. ajouter les validations effectuées ;
6. enregistrer le fichier ;
7. relire `dev/TODO_REPRISE.md` ;
8. marquer la prochaine tâche comme `[ ] EN COURS` ;
9. enregistrer à nouveau le fichier.

Ne pas exécuter la tâche suivante pendant la même intervention.

---

## UPDATE_BUGS_IF_NEEDED

Lorsqu’une entrée est ajoutée à `dev/SUIVI_BUGS_AMELIORATIONS.md` :

- l’insérer en tête de la section concernée ;
- conserver un ordre antéchronologique strict, du plus récent au plus ancien ;
- utiliser la date réelle de l’intervention au format `YYYY-MM-DD` ;
- si plusieurs entrées ont la même date, placer la plus récente en premier selon l’ordre des interventions ;
- ne pas réordonner ni réécrire les entrées historiques sans nécessité ;
- vérifier après insertion que l’ordre des dates reste décroissant.

---

## Statuts autorisés

Tâche active :

```markdown
- [ ] EN COURS — Nom de la tâche.
```

Tâche terminée :

```markdown
- [x] FAIT — Nom de la tâche.
```

Tâche bloquée :

```markdown
- [!] BLOQUÉ — Nom de la tâche.
```

Une tâche `FAIT` doit contenir :

- les fichiers inspectés ;
- les fichiers modifiés ;
- l’action réalisée ;
- les tests effectués ;
- le résultat obtenu.

Une tâche `BLOQUÉ` doit contenir :

- la cause exacte ;
- les vérifications réalisées ;
- l’action nécessaire pour débloquer.

---

## Règles de TODO_REPRISE.md

`dev/TODO_REPRISE.md` doit être lu au début de chaque intervention.

Il doit être mis à jour lorsqu’une tâche :

- commence ;
- est vérifiée ;
- est modifiée ;
- est validée ;
- est bloquée ;
- révèle une nouvelle action nécessaire.

Ne jamais :

- supprimer les tâches terminées ;
- créer une seconde TODO concurrente ;
- remplacer une modification par un résumé ;
- marquer une tâche `FAIT` sans validation ;
- passer à la tâche suivante sans mettre à jour le fichier.

---

## Format de fin

Répondre avec :

```text
STATUS: TODO_UPDATED

TASK:
<nom de la tâche>

VERIFY:
<résultat de la vérification>

MODIFY:
<modification réalisée ou AUCUNE>

VALIDATE:
<tests et contrôles effectués>

NEXT_MOVE:
<prochaine tâche active>
```

En cas de blocage :

```text
STATUS: BLOCKED

TASK:
<nom de la tâche>

VERIFY:
<vérifications effectuées>

ERROR:
<cause exacte>

NEXT_MOVE:
<action nécessaire pour débloquer>
```

---

## Point d’entrée

Toute reprise commence par :

```text
Lire dev/PROTOCOL_PERMANENT.md
Lire dev/TODO_REPRISE.md
Lire les fichiers de la tâche active
Vérifier l’état réel
Modifier uniquement si nécessaire
Valider
Mettre à jour dev/TODO_REPRISE.md
Relire dev/TODO_REPRISE.md
```
