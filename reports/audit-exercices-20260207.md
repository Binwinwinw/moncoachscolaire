# Audit exercices — 2026-02-07

## 1) Routes & navigation (élève)

### Sidebar (navigation principale)
- Lien 6ème : index.php?page=college/6eme/exercices-6eme
- Lien 5ème : index.php?page=college/5eme/exercices-5eme
- Lien 4ème : index.php?page=college/4eme/exercices-4eme
- Lien 3ème : index.php?page=college/3eme/exercices-3eme
- Lien Seconde : index.php?page=lycee/2nde/exercices-2nde
- Lien Première : index.php?page=lycee/premiere/exercices-premiere
- Lien Terminale : index.php?page=lycee/terminale/exercices-terminale
- Lien BAC : index.php?page=bac/exercices-bac

Source : src/includes/sidebar.php

### Footer (lien “Exercices” contextualisé)
- Lien généré selon le niveau utilisateur (college/lycee/bac)
- Fallback : page=exercices

Source : src/includes/footer.php

### Hub system/exercices
- Page multi-niveaux : src/pages/system/exercices.php
- Liens niveau → pages collège/lycée/bac (exercices-xx)

Conclusion : le “système actif” côté navigation élève pointe sur les pages niveaux (college/6eme/exercices-6eme, etc.), pas sur le hub system/exercices.

## 2) Système dynamique (DynamicExerciseSystem)

### JS inclus
- public/assets/js/dynamic-exercises.js
- public/assets/js/interactive-exercises.js

### API utilisée
- Alias via routeur : index.php?page=api/get_exercises
- Implémentation réelle : src/api/exercices/get_exercises.php

## 3) Pages niveaux (exemple 6ème)

- Page : src/pages/eleve/college/6eme/exercices-6eme.php
- Présence d’un container dynamique (data-dynamic-exercises)
- Chargement JS : dynamic-exercises.js + interactive-exercises.js
- Anciennes sections hardcodées conservées (masquées via CSS)

## 4) Schéma DB (table exercises)

Champs principaux observés :
- Id, Subject, Level, Title, Content, Answer
- Tips, Domain, Competence, Difficulty, Identifier
- AnswerType, Choices, Instruction
- is_active, XP_Points

Source : db/moncoachscolaire_struc_n_files.sql.sql + dev/db/dump_structure_20260115.sql

## 5) Tests manuels (local)

- Non exécutés (pas d’environnement local disponible).

Checklist prévue :
1. Ouvrir index.php?page=eleve/college/6eme/exercices-6eme (connecté)
2. Vérifier que DynamicExerciseSystem charge les matières
3. Cliquer une matière → exercices listés + rendu HTML
4. Vérifier l’init des exercices interactifs

## 6) Prochaines pages à migrer

- src/pages/eleve/college/5eme/exercices-5eme.php
- src/pages/eleve/college/4eme/exercices-4eme.php
- src/pages/eleve/college/3eme/exercices-3eme.php
- src/pages/eleve/lycee/2nde/exercices-2nde.php
- src/pages/eleve/lycee/premiere/exercices-premiere.php
- src/pages/eleve/lycee/terminale/exercices-terminale.php
- src/pages/eleve/bac/exercices-bac.php
