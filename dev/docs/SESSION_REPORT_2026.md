# Tableau de bord tests Playwright — 2026-02-25

## État actuel
- Total tests exécutés : 106
- Succès : 71
- Échecs : 35

## Blocages identifiés
- Plusieurs tests échouent sur des IDs d'exercices non routés ou inexistants.
- Les pages lycée ne sont pas incorporées dans les tests actuels (absence ou non routées).
- Sélecteurs Playwright robustes, mais certains chemins HTML ne correspondent pas (ex : badge matière, sous-questions).

## Actions à poursuivre
- Lister les IDs échoués et vérifier leur présence en base et leur routabilité.
- Incorporer les pages lycée dans la suite de tests (ajouter IDs lycée, vérifier routes).
- Adapter les tests pour ignorer ou signaler les IDs inexistants.
- Vérifier la cohérence des sélecteurs HTML côté PHP.

## Plan de reprise
1. Extraire la liste des IDs échoués (logs Playwright).
2. Vérifier la présence des exercices lycée dans la base.
3. Corriger les routes manquantes (pages lycée).
4. Adapter les tests pour couvrir collège + lycée.
5. Documenter chaque étape dans le journal de reprise.

## Prochaine étape
- Diagnostic automatique des erreurs restantes.
- Patch pour intégrer lycée dans les tests.
- Correction des routes et sélecteurs.

Auteur : Copilot/Assistance

---

Pour la reprise exacte après interruption, consulter le journal daté [dev/JOURNAL_REPRISE.md](../dev/JOURNAL_REPRISE.md).
