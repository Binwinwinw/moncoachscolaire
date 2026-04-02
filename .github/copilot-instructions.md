# Copilot Instructions — MonCoachScolaire (2026)

Objectif: fichier d'entrée ultra-court. Le détail est déplacé dans des fichiers dédiés.

## Lecture minimale obligatoire

1. [CONTEXT_INDEX.md](../CONTEXT_INDEX.md)
2. [DOCUMENTATION.md](../DOCUMENTATION.md)
3. [dev/JOURNAL_REPRISE.md](../dev/JOURNAL_REPRISE.md)
4. [dev/SUIVI_BUGS_AMELIORATIONS.md](../dev/SUIVI_BUGS_AMELIORATIONS.md)
5. [README.md](../README.md)

## Référentiel détaillé déplacé

- [COPILOT_REFERENCE.md](COPILOT_REFERENCE.md) (index rapide, stack, workflows, build/tests, anti-blocage)
- [PROJECT_CONTEXT.md](PROJECT_CONTEXT.md)
- [CONTEXT_PRODUIT.md](CONTEXT_PRODUIT.md)
- [REGLES_IA.md](REGLES_IA.md)

## Conventions par langage

- PHP: [instructions/php.instructions.md](instructions/php.instructions.md)
- JS: [instructions/js.instructions.md](instructions/js.instructions.md)
- SQL: [instructions/sql.instructions.md](instructions/sql.instructions.md)
- Accessibilité front: [instructions/frontend-accessibility.instructions.md](instructions/frontend-accessibility.instructions.md)

## Skills, agents, mémoire

- Skills catalog: [../.agents/skills/SKILLS.md](../.agents/skills/SKILLS.md)
- Règle: toujours charger le `SKILL.md` pertinent avant implémentation
- Agents: [../AGENTS.md](../AGENTS.md)
- Mémoire augmentée: consulter `.memory/*` avant architecture/conventions/sécurité

## Procédure anti-blocage (rappel)

1. Ouvrir `public/index.php` ou `index.php`
2. Faire max 3 recherches repo
3. Lister 3-8 fichiers candidats
4. Proposer patch ou NON TROUVE avec traces
