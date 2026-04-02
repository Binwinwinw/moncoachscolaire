# Plan de réorganisation détaillé — Documentation MonCoachScolaire

Date : 13 février 2026

Ce tableau liste, pour chaque section/documentation identifiée comme redondante ou à fusionner, l’action précise à effectuer.

| Fichier source                        | Section concernée/extrait                                      | Fichier de destination                | Action à effectuer                |
|---------------------------------------|---------------------------------------------------------------|---------------------------------------|-----------------------------------|
| README.md                            | Exemples d’usage scripts/import/export/maintenance/tests       | dev/tools/README.md                   | Déplacer, centraliser, référencer |
| README.md                            | Inventaire technique (pages, API, hooks)                       | dev/reports/pages_inventory.md, ...   | Garder uniquement le lien         |
| README.md                            | Liste des scripts/outils/tests                                 | dev/tools/README.md                   | Supprimer, garder le lien         |
| DOCUMENTATION.md                     | Exemples d’usage scripts/import/export/maintenance/tests       | dev/tools/README.md                   | Déplacer, centraliser, référencer |
| DOCUMENTATION.md                     | Inventaires techniques                                         | dev/reports/                          | Garder uniquement le lien         |
| DOCUMENTATION.md                     | Checklists de test manuel                                      | dev/tools/README.md                   | Déplacer, centraliser             |
| CONTEXT_INDEX.md                     | Liens vers scripts/outils/tests                                | dev/tools/README.md                   | Garder uniquement le lien         |
| CONTEXT_INDEX.md                     | Index/inventaire des docs                                      | CONTEXT_BUNDLE.md                     | Fusionner, éviter la redondance   |
| docs/archive/OUTILS_DIAGNOSTIC_CSS.md| Liste d’outils/scripts de diagnostic                           | dev/tools/README.md                   | Résumer, référencer, archiver     |
| docs/archive/UPDATE_EXERCISES_DB.md  | Procédures d’import/export, détection de doublons              | dev/tools/README.md                   | Résumer, référencer, archiver     |
| docs/archive/XML_IMPORT_SYSTEM.md     | Procédures d’import XML, détection de doublons                 | dev/tools/README.md                   | Résumer, référencer, archiver     |
| docs/archive/                        | Guides/checklists obsolètes ou redondants                      | dev/tools/README.md ou docs/          | Archiver, référencer, supprimer   |
| dev/reports/                         | Inventaires techniques                                         | dev/tools/README.md                   | Garder uniquement le lien         |
| dev/SUIVI_BUGS_AMELIORATIONS.md      | Suivi des corrections de doublons                              | dev/tools/README.md                   | Référence croisée                 |
| .github/PROJECT_CONTEXT.md           | Règles IA, conventions, roadmap                                | CONTEXT_INDEX.md                      | Garder uniquement le lien         |
| .github/REGLES_IA.md                 | Règles d’implémentation                                        | CONTEXT_INDEX.md                      | Garder uniquement le lien         |
| ...                                  | ...                                                           | ...                                   | ...                               |

> Ce tableau doit être complété/ajusté lors de la validation finale, chaque ligne correspondant à une action concrète à appliquer lors du ménage documentaire.
