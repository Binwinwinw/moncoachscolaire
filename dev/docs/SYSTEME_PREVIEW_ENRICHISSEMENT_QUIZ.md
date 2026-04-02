# Systeme Preview - Enrichissement et Rendu Pedagogique des Quiz

Date: 13/03/2026
Perimetre: generateurs Python dans dev/tools/quiz

## Objectif

Mettre en place un workflow fiable pour:

- enrichir pedagogiquement un lot de quiz,
- previsualiser le resultat sans impacter les donnees runtime,
- valider rapidement la qualite,
- appliquer ensuite en generation reelle avec rollback simple.

Ce document formalise la methode utilisee sur le lot HGGSP 1ere (IDs 942-962), reutilisable pour d'autres matieres/niveaux.

## Principe general

Le systeme repose sur 2 etapes distinctes:

1. Preview isolee

- On genere des artefacts de test dans un dossier dedie preview.
- Aucune ecriture sur src/data/quiz ni src/data/quiz_answers.
- Permet revue pedagogique et validation humaine avant propagation.

2. Application reelle

- On regenere le lot complet via le generateur.
- Les sorties sont ecrites dans les destinations standard:
  - dev/tools/quiz/<matiere>\_<niveau>\_quizzes/quiz
  - dev/tools/quiz/<matiere>\_<niveau>\_quizzes/quiz_answers
  - dev/tools/quiz/output/<matiere>\_<niveau>\_quizzes/quiz
  - dev/tools/quiz/output/<matiere>\_<niveau>\_quizzes/quiz_answers
  - src/data/quiz
  - src/data/quiz_answers

## Architecture recommandee dans un generateur

## 1) Donnees d'enrichissement

Declarer une liste centrale ENRICHMENT_SPECS:

- id quiz
- titre
- angle du theme
- acteur cle
- instrument/outil
- exemple concret

Objectif:

- garder une source unique de parametres,
- faciliter l'audit et la maintenance,
- eviter les hardcodes disperses.

## 2) Builder de questions enrichies

Centraliser la logique pedagogique dans une fonction de type:

- build_quality_lot_questions(qid, angle, acteur, exemple, outils)

Bonnes pratiques:

- conserver le pattern 8 questions (qcm, vrai-faux, open, qcm, vrai-faux, open, qcm, vrai-faux),
- varier Q3 (ouverte) selon plusieurs patrons,
- varier Q7 (methodologie HGGSP) selon plusieurs patrons,
- aligner la correction avec la variante choisie,
- maintenir des formulations bienveillantes et exploitables par l'eleve.

## 3) Regles de variation pedagogique

Pour eviter la repetition mecanique entre quiz:

- introduire des variantes determinees par qid (modulo),
- alterner les formulations sans casser le schema JSON,
- maintenir la coherencedu triplet question/reponse/correction.

Exemples d'axes de variation:

- problematisation geopolitique,
- articulation des echelles,
- methodologie argumentaire,
- comparaison d'acteurs et d'interets.

## 4) Normalisation technique

Conserver les invariants runtime:

- types normalises: qcm, vrai-faux, open,
- structure contents + quiz,
- question_count coherent,
- mapping answers coherent (index, question_id, correct pour qcm).

Conserver aussi:

- encodage UTF-8,
- sanitation texte (si deja presente),
- pipeline d'ecriture unique via write_quiz_files().

## Workflow operatoire

## Phase A - Preview (safe)

1. Selectionner un quiz sentinelle (ou mini-lot).
2. Generer des fichiers preview dans:
   - dev/tools/quiz/preview\_<lot>/
3. Verifier:
   - lisibilite pedagogique,
   - absence de telegraphique,
   - adequation theme/exemple/correction,
   - structure JSON valide.
4. Corriger le builder tant que necessaire.

## Phase B - Application

1. Lancer la generation reelle du script.
2. Verifier la reussite batch (IDs, nombre de fichiers, nombre de questions).
3. Relire un echantillon sentinelle (au moins 5 IDs repartis).
4. Comparer quiz et quiz_answers sur ces sentinelles.

## Phase C - Documentation + commit

1. Rediger un rapport date dans dev/reports.
2. Referencer ce rapport dans un document de synthese (pas de duplication).
3. Commit separable:
   - commit technique (generateur + JSON),
   - commit documentation.

## Checklist de validation rapide

- [ ] La preview est generee hors src/data
- [ ] Les questions Q3 ne sont pas toutes identiques
- [ ] Les questions Q7 ne sont pas toutes identiques
- [ ] Les corrections ouvertes sont contextualisees
- [ ] Le schema runtime est conserve
- [ ] Le lot passe sur sentinelles (ex: 942, 948, 955, 960, 962)
- [ ] La doc de suivi est mise a jour

## Risques frequents et parades

1. Repetition pedagogique excessive

- Parade: variantes par patrons + verification sentinelles.

2. Melange preview/production

- Parade: dossier preview dedie et jamais source runtime.

3. Incoherence question/reponse

- Parade: lecture croisee quiz + quiz_answers sur echantillon.

4. Bruit de commit

- Parade: exclure previews et cache Python du commit technique.

## Cas d'usage de reference

Implementation de reference:

- script: dev/tools/quiz/generate_hggsp_1ere.py
- rapport de lot: dev/reports/hggsp_1ere_quality_pass2_2026-03-13.md

Ces deux fichiers servent de base pour reproduire le systeme sur un autre generateur.
