# Plan de réorganisation — Synthèse stratégique

Date : 13 février 2026

Cette synthèse regroupe les actions par grandes catégories de contenu pour une vision macro de la réorganisation documentaire.

## 1. Scripts, outils, tests
- **Centralisation** : Tous les scripts, outils et tests doivent être référencés uniquement dans `dev/tools/README.md`.
- **Suppression des listes redondantes** : Les listes détaillées présentes dans `README.md`, `DOCUMENTATION.md`, `CONTEXT_INDEX.md` sont supprimées au profit d’un lien unique.
- **Archivage** : Les guides d’archive sur des outils/scripts sont résumés et référencés, l’historique est archivé.

## 2. Inventaires techniques
- **Unicité** : Les inventaires (pages, API, hooks, etc.) sont maintenus dans `dev/reports/` et référencés depuis les documents centraux.
- **Suppression des doublons** : Les inventaires partiels ou obsolètes dans d’autres fichiers sont supprimés ou remplacés par un lien.

## 3. Guides, checklists, procédures
- **Fusion** : Les guides et checklists redondants sont fusionnés, la version la plus à jour est conservée.
- **Référence unique** : Les procédures d’import/export, maintenance, etc. sont centralisées dans `dev/tools/README.md`.
- **Archivage** : Les anciennes versions ou guides obsolètes sont déplacés dans `docs/archive/` avec mention de leur statut.

## 4. Index, contextes, conventions
- **Index unique** : `CONTEXT_INDEX.md` reste l’index principal, les autres fichiers d’index (ex : `CONTEXT_BUNDLE.md`) sont fusionnés ou référencés.
- **Conventions et règles** : Les règles IA, conventions de code, roadmap sont référencées depuis l’index, pas recopiées.

## 5. Historique et archivage
- **Archivage clair** : Les documents historiques ou obsolètes sont déplacés dans `docs/archive/` et signalés comme tels.
- **Référencement** : Les documents d’archive utiles sont référencés dans les guides actuels si besoin.

## 6. Exemples d’usage et instructions
- **Exemples** : Les exemples d’usage de scripts sont centralisés dans `dev/tools/README.md`.
- **Suppression des doublons** : Les exemples présents ailleurs sont supprimés ou remplacés par un lien.

---

> Ce plan synthétique permet de valider la stratégie globale avant d’appliquer les actions détaillées du tableau micro.
