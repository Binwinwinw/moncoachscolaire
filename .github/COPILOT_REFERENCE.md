# Copilot Reference — MonCoachScolaire (2026)

Ce document contient le détail déplacé depuis `.github/copilot-instructions.md` afin de garder un fichier d'instructions principal court et rapide à charger.

## Index rapide

- [README.md](../README.md) — présentation, démarrage, stack, sécurité
- [CONTEXT_INDEX.md](../CONTEXT_INDEX.md) — index de contexte et règles de reprise
- [DOCUMENTATION.md](../DOCUMENTATION.md) — état des lots, sécurité, patterns API
- [dev/tools/README.md](../dev/tools/README.md) — scripts, outils, tests, workflow quiz
- [dev/JOURNAL_REPRISE.md](../dev/JOURNAL_REPRISE.md) — journal daté
- [dev/SUIVI_BUGS_AMELIORATIONS.md](../dev/SUIVI_BUGS_AMELIORATIONS.md) — priorités bugs/améliorations
- [dev/docs/INDEX.md](../dev/docs/INDEX.md) — table des matières documentaire

## Stack et structure

- Stack: PHP 8+, MySQL 8+, Tailwind CSS 4, Vanilla JS, Playwright
- Dossiers clés:
  - `src/pages/`
  - `src/api/`
  - `src/components/`
  - `src/includes/`
  - `public/assets/`
  - `dev/tools/`
  - `db/`

## Workflows et conventions

- PHP: PSR-12, PDO explicite, validation input, API JSON only
- JS: Vanilla ES6+, hooks data-attributes/classes, fetch + gestion erreurs
- SQL: scripts rejouables, pas de renumérotation PK, index FK
- CSS: Tailwind only, hooks conservés
- Sécurité: HttpOnly/SameSite, CSRF, pas d'exposition des secrets
- API: format `{success, data, message}`
- Quiz: séparation questions/réponses, workflow outillé

Références directes:

- [instructions/php.instructions.md](instructions/php.instructions.md)
- [instructions/js.instructions.md](instructions/js.instructions.md)
- [instructions/sql.instructions.md](instructions/sql.instructions.md)
- [dev/docs/API_REFERENCE.md](../dev/docs/API_REFERENCE.md)

## Build et tests

- CSS: `npm run build:css` ou `npm run watch:css`
- Dépendances: `composer install`, `npm install`
- E2E: `npx playwright test`

## Procédure anti-blocage

1. Ouvrir `public/index.php` ou `index.php`
2. Faire max 3 recherches repo (mots-clés courts)
3. Lister 3-8 fichiers candidats (chemin + extrait)
4. Proposer un patch ou NON TROUVE avec recherches tentées

## Skills et mémoire

- Tableau skills: [../.agents/skills/SKILLS.md](../.agents/skills/SKILLS.md)
- Règle: charger le `SKILL.md` pertinent avant implémentation
- Mémoire augmentée: lire et mettre à jour `.memory/*` selon le type (instructions, preferences, decisions, quirks, security)
