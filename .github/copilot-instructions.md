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

<!-- hacklm-memory:start -->
## Memory-Augmented Context

Read memory files on-demand — not all at once.

| File | When to read |
|------|-------------|
| [.memory/instructions.md](.memory/instructions.md) | How to behave |
| [.memory/quirks.md](.memory/quirks.md) | When something breaks unexpectedly |
| [.memory/preferences.md](.memory/preferences.md) | Style/design/naming choices |
| [.memory/decisions.md](.memory/decisions.md) | Architectural changes |
| [.memory/security.md](.memory/security.md) | **ALWAYS — before any code change** |

### Memory Tools

Call `queryMemory` before answering anything about architecture, conventions, or style.

Call `storeMemory` (with a kebab-case `slug`) when:
1. User states a preference or rule → store as Instruction or Preference **before** acting
2. User corrects you → store the correction
3. A command or build fails → store root cause and fix
4. After completing any implementation task → store each architectural decision, convention, or pattern applied that is not already in memory. Do this **before ending the turn**.

Same slug = update, not duplicate.

### Writing Style for Memory Entries
Hemingway style. Short sentences. No jargon. No filler. Be blunt.
Bad: "The system employs an asynchronous locking mechanism to serialise concurrent write operations."
Good: "Use a lock before writing. One write at a time."

### Categories
| Category | Use for |
|----------|---------|
| Instruction | How to behave |
| Quirk | Project-specific weirdness |
| Preference | Style/design/naming |
| Decision | Architectural commitments |
| Security | Rules that must NEVER be broken |
<!-- hacklm-memory:end -->
