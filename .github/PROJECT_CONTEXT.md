# PROJECT_CONTEXT

**Ajout du 22/03/2026 :** Synchronisation globale de la documentation avec le `JOURNAL_REPRISE.md` et le `SUIVI_BUGS_AMELIORATIONS.md`. Ces fichiers sont désormais les sources de vérité pour le suivi d'avancement (dashboards).

## Règle d’inclusion du dashboard élève

Le dashboard élève (`eleve/dashboard`) doit toujours passer par la génération du head HTML du routeur (`public/index.php`).
Il ne doit pas être listé dans `$pages_sensibles` pour garantir l’inclusion automatique du CSS et des assets.
Cette règle évite tout bug d’affichage lié au style ou au head manquant.

## PROJECT_CONTEXT (index) [19/02/2026]

**Ajout du 25/02/2026 :** Les pages lycée et bac sont désormais intégrées dans les tests Playwright, impacts sur la liste des pages critiques et la documentation. Voir [docs/SESSION_REPORT_2026.md](../docs/SESSION_REPORT_2026.md).

## Contexte produit

Lire : `.github/CONTEXT_PRODUIT.md`

## Règles IA / conventions de code

Lire : `.github/REGLES_IA.md`

## Roadmap / migrations

Lire : `.github/copilot-plan.md` (si présent)

## Configuration Copilot (skills + agents)

Voir :

- `.github/skills/SKILLS.md` (index skills, regles de selection, orchestration V2)
- `.github/skills/quiz-generator/SKILL.md` (pattern generateurs quiz)
- `.github/agents/planner.agent.md`
- `.github/agents/implementer.agent.md`
- `.github/agents/reviewer.agent.md`
- `.github/agents/release-manager.agent.md`

Rappel workflow V2:

- `planner` -> `implementer` -> `reviewer` -> `release-manager` (manuel en fin de revue)

## Migration guides UI/UX (février 2026)

Voir :

- [DOCUMENTATION.md](../DOCUMENTATION.md) (détails techniques)
- [.github/CONTEXT_PRODUIT.md](../.github/CONTEXT_PRODUIT.md) (contexte produit)
- [dev/JOURNAL_REPRISE.md](../dev/JOURNAL_REPRISE.md) (journal de migration)
- [dev/SUIVI_BUGS_AMELIORATIONS.md](../dev/SUIVI_BUGS_AMELIORATIONS.md) (suivi des bugs et améliorations)

## Règle de base

Toute demande de patch doit sortir :

- Fichiers impactés
- Diff
- Comment tester
- Hooks conservés/modifiés
