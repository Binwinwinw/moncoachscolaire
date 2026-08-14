<!-- gitnexus:start -->

# GitNexus — Intelligence de code

Ce projet est indexé par GitNexus sous le nom **moncoachscolaire** (11 symboles, 0 relations, 0 flux d’exécution). Utilisez les outils MCP GitNexus pour comprendre le code, évaluer l’impact et naviguer en toute sécurité.

> Si un outil GitNexus indique que l’index est obsolète, exécutez d’abord `npx gitnexus analyze` dans le terminal.

## À faire systématiquement

- **DOIT effectuer une analyse d’impact avant de modifier n’importe quel symbole.** Avant de modifier une fonction, une classe ou une méthode, exécutez `gitnexus_impact({target: "symbolName", direction: "upstream"})` et faites remonter au user le rayon d’impact (appelants directs, processus affectés, niveau de risque).
- **DOIT exécuter `gitnexus_detect_changes()` avant de valider** pour vérifier que vos modifications n’affectent que les symboles et flux d’exécution attendus.
- **DOIT lancer des tests Playwright ciblés pour les zones UI/workflow affectées** après les modifications de code, avant de finaliser le travail.
- **DOIT avertir l’utilisateur** si l’analyse d’impact renvoie un risque HIGH ou CRITICAL avant de poursuivre les modifications.
- En explorant un code inconnu, utilisez `gitnexus_query({query: "concept"})` au lieu de grep. Cela renvoie des résultats groupés par processus classés par pertinence.
- Lorsque vous avez besoin du contexte complet d’un symbole spécifique — appelants, appelés, flux d’exécution impliqués — utilisez `gitnexus_context({name: "symbolName"})`.

## En cas de débogage

1. `gitnexus_query({query: "<erreur ou symptôme>"})` — trouver les flux d’exécution liés au problème
2. `gitnexus_context({name: "<fonction suspecte>"})` — voir tous les appelants, appelés et la participation aux processus
3. `READ gitnexus://repo/moncoachscolaire/process/{processName}` — tracer le flux d’exécution complet étape par étape
4. Pour les régressions : `gitnexus_detect_changes({scope: "compare", base_ref: "main"})` — voir ce que votre branche a modifié

## En cas de refactorisation

- **Renommage** : DOIT utiliser `gitnexus_rename({symbol_name: "old", new_name: "new", dry_run: true})` en premier. Vérifiez l’aperçu — les modifications de graphe sont sûres, les modifications text_search exigent une revue manuelle. Puis exécutez avec `dry_run: false`.
- **Extraction/Séparation** : DOIT exécuter `gitnexus_context({name: "target"})` pour voir toutes les références entrantes/sortantes, puis `gitnexus_impact({target: "target", direction: "upstream"})` pour trouver tous les appelants externes avant de déplacer le code.
- Après toute refactorisation : exécutez `gitnexus_detect_changes({scope: "all"})` pour vérifier que seuls les fichiers attendus ont changé.

## Ne jamais faire

- NE JAMAIS modifier une fonction, une classe ou une méthode sans avoir d’abord exécuté `gitnexus_impact` sur celle-ci.
- NE JAMAIS ignorer un avertissement de risque HIGH ou CRITICAL issu de l’analyse d’impact.
- NE JAMAIS renommer des symboles avec recherche/remplacement — utilisez `gitnexus_rename`, qui comprend le graphe d’appels.
- NE JAMAIS valider des modifications sans exécuter `gitnexus_detect_changes()` pour vérifier la portée affectée.

## Référence rapide des outils

| Outil            | Quand l’utiliser                  | Commande                                                                |
| ---------------- | --------------------------------- | ----------------------------------------------------------------------- |
| `query`          | Trouver du code par concept       | `gitnexus_query({query: "auth validation"})`                            |
| `context`        | Vue à 360° d’un même symbole      | `gitnexus_context({name: "validateUser"})`                              |
| `impact`         | Rayon d’impact avant édition      | `gitnexus_impact({target: "X", direction: "upstream"})`                 |
| `detect_changes` | Vérification avant commit         | `gitnexus_detect_changes({scope: "staged"})`                            |
| `rename`         | Renommage multi-fichiers sûr      | `gitnexus_rename({symbol_name: "old", new_name: "new", dry_run: true})` |
| `cypher`         | Requêtes de graphe personnalisées | `gitnexus_cypher({query: "MATCH ..."})`                                 |

## Niveaux de risque d’impact

| Profondeur | Signification                                 | Action                     |
| ---------- | --------------------------------------------- | -------------------------- |
| d=1        | CASSERA — appelants/importateurs directs      | DOIT mettre à jour ceux-ci |
| d=2        | PROBABLEMENT AFFECTÉ — dépendances indirectes | Devrait tester             |
| d=3        | PEUT NÉCESSITER DES TESTS — transitif         | Tester si chemin critique  |

## Ressources

| Ressource                                         | Usage                                         |
| ------------------------------------------------- | --------------------------------------------- |
| `gitnexus://repo/moncoachscolaire/context`        | Vue d’ensemble du code, vérifier l’index      |
| `gitnexus://repo/moncoachscolaire/clusters`       | Toutes les zones fonctionnelles               |
| `gitnexus://repo/moncoachscolaire/processes`      | Tous les flux d’exécution                     |
| `gitnexus://repo/moncoachscolaire/process/{name}` | Traçage d’un flux d’exécution étape par étape |

## Auto-vérification avant de terminer

Avant de terminer toute tâche de modification de code, vérifiez :

1. `gitnexus_impact` a été exécuté pour tous les symboles modifiés
2. Aucun avertissement de risque HIGH/CRITICAL n’a été ignoré
3. `gitnexus_detect_changes()` confirme que les modifications correspondent à la portée attendue
4. Tous les dépendants d=1 (CASSERA) ont été mis à jour
5. Les tests Playwright ciblés pertinents ont été exécutés pour les changements UI/workflow

## Maintenir l’index à jour

Après avoir validé des modifications de code, l’index GitNexus devient obsolète. Relancez l’analyse pour le mettre à jour :

```bash
npx gitnexus analyze
```

Si l’index contenait auparavant des embeddings, conservez-les en ajoutant `--embeddings` :

```bash
npx gitnexus analyze --embeddings
```

Pour vérifier si des embeddings existent, inspectez `.gitnexus/meta.json` — le champ `stats.embeddings` indique le nombre (0 signifie aucun embedding). **Lancer analyze sans `--embeddings` supprimera les embeddings précédemment générés.**

> Utilisateurs Claude Code : un hook PostToolUse gère cela automatiquement après `git commit` et `git merge`.

## CLI

| Tâche                                                     | Lire ce fichier de skill                                    |
| --------------------------------------------------------- | ----------------------------------------------------------- |
| Comprendre l’architecture / « Comment X marche ? »        | `.claude/skills/gitnexus/gitnexus-exploring/SKILL.md`       |
| Rayon d’impact / « Qu’est-ce qui casse si je change X ? » | `.claude/skills/gitnexus/gitnexus-impact-analysis/SKILL.md` |
| Tracer les bugs / « Pourquoi X échoue ? »                 | `.claude/skills/gitnexus/gitnexus-debugging/SKILL.md`       |
| Renommer / extraire / séparer / refactoriser              | `.claude/skills/gitnexus/gitnexus-refactoring/SKILL.md`     |
| Outils, ressources, référence de schéma                   | `.claude/skills/gitnexus/gitnexus-guide/SKILL.md`           |
| Index, statut, nettoyage, commandes wiki CLI              | `.claude/skills/gitnexus/gitnexus-cli/SKILL.md`             |

<!-- gitnexus:end -->

## État du projet (MonCoachScolaire)

Avant un chantier significatif (hors simple lecture GitNexus), consulter :

- `dev/SUIVI_BUGS_AMELIORATIONS.md` — priorités, bugs/améliorations, tableau de couverture quiz
- `dev/JOURNAL_REPRISE.md` — sessions datées, décisions, suites ; fin du fichier : TODO consolidée

Voir aussi `ARCHITECTURE.md` (section « État du projet et ligne conductrice ») et `dev/README.md`.

# Instructions de langue

Toutes les réponses doivent être rédigées exclusivement en français.
Ne jamais utiliser d'autres langues dans les réponses.
