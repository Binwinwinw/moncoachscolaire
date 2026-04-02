# Prompt — Documentation de reprise MonCoachScolaire

Objectif : garantir une documentation claire, structurée et à jour pour permettre une reprise rapide et fiable du projet en cas de problème, interruption ou changement d’équipe.

---

## Instructions pour Copilot / IA

1. **Vérifier la présence d’une documentation complète** :
   - Lire `DOCUMENTATION.md`, `README.md`, `.github/CONTEXT_PRODUIT.md`, `.github/REGLES_IA.md`.
   - S’assurer que chaque section critique (architecture, workflows, endpoints, scripts, tests, sécurité) est couverte.

2. **Enrichir la documentation si besoin** :
   - Ajouter un résumé de l’état actuel (arborescence, scripts, endpoints, pages).
   - Documenter les workflows de reprise (installation, migration, tests, rollback).
   - Lister les contacts, liens utiles, guides d’utilisation.

3. **Vérifier la clarté et la structure** :
   - Utiliser des titres, sous-titres, tableaux, schémas.
   - Ajouter des exemples de commandes, scripts, tests.
   - Préciser les points d’entrée, les fichiers critiques, les conventions.

4. **Documenter les points de reprise** :
   - Pour chaque lot ou tâche, indiquer :
     - Objectif
     - Fichiers impactés
     - Plan de patch
     - Plan de test
     - Risques + rollback
     - Checklist de validation

5. **Mettre à jour la roadmap** :
   - Synchroniser avec `.github/copilot-plan.md`.
   - Ajouter les lots en cours ou à venir.

6. **Rendre la documentation accessible** :
   - Vérifier que tous les liens sont valides.
   - Ajouter un index ou une table des matières.
   - Préciser où trouver chaque info (fichier, dossier, URL).

---

## Critère de validation
- Toute personne (développeur, mainteneur, IA) doit pouvoir reprendre le projet sans blocage, en suivant la documentation.
- En cas de problème, la procédure de reprise doit être lisible, testable, et permettre un rollback ou une correction rapide.

---

## Exemple de workflow de reprise

1. Lire `DOCUMENTATION.md` et `.github/CONTEXT_PRODUIT.md`.
2. Vérifier l’état du code (git status, tests).
3. Suivre le plan de patch ou rollback indiqué.
4. Tester la correction (tests unitaires, smoke tests).
5. Mettre à jour la documentation si besoin.

---

## Format recommandé
- Titres clairs
- Table des matières
- Exemples de commandes
- Plans de patch/tests
- Checklist de validation
- Liens vers les fichiers critiques

---

## Rappel
- Ne jamais laisser une documentation incomplète ou obsolète.
- Toujours documenter les points de reprise, les scripts de rollback, et les contacts.
- Synchroniser la roadmap et les prompts Copilot.

---

Dernière mise à jour : 13 février 2026
