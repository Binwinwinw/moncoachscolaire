# ARCHITECTURE

## Vue d’ensemble

MonCoachScolaire est une application web basée sur PHP 8+, MySQL 8+, Tailwind CSS 4 et Vanilla JS. L’architecture privilégie la modularité via des composants réutilisables (PHP/HTML/JS) et une logique métier centralisée.

## Structure principale

- `src/pages/` : Pages PHP (publiques, élève, admin)
- `src/components/` : Composants réutilisables (modales, cards, tables)
- `src/includes/` : Logique partagée (chargement, helpers, sécurité)
- `src/api/` : Endpoints JSON pour l’interactivité JS
- `public/assets/` : CSS (build Tailwind), JS, images
- `dev/` : Outils CLI, documentation technique, scripts d’import/export
- `db/` : Schéma SQL, migrations, sauvegardes
- `.memory/` : fichiers mémoire Copilot (instructions, décisions, préférences, sécurité)

## Conventions

- Respecter la séparation pages / composants / includes.
- PHP : PSR-12, sécurité PDO, pas de sortie avant headers.
- CSS : Tailwind CSS, pas de custom CSS si possible.
- JS : Vanilla, hooks via data-attributes/classes, pas de framework.
- Documenter toute décision technique dans `.memory/` ou `dev/JOURNAL_REPRISE.md`.

## Automatisation

- Build CSS : `npm run build:css` (voir Tailwind et PostCSS config)
- Tests E2E : Playwright (`npx playwright test`)
- Lint PHP : PSR-12

Pour plus de détails, voir `.github/copilot-instructions.md` et `DOCUMENTATION.md`.

## État du projet et ligne conductrice (sources canoniques)

Ne pas se fier uniquement à ce fichier ou à `CONTEXT_INDEX.md` pour savoir **où en est le travail** : deux fichiers dans `dev/` font foi.

| Fichier | Rôle |
| -------- | ------ |
| [dev/JOURNAL_REPRISE.md](dev/JOURNAL_REPRISE.md) | Chronologie datée des sessions, décisions, commandes, « prochaines étapes ». En fin de fichier : TODO consolidée et rappels récents. |
| [dev/SUIVI_BUGS_AMELIORATIONS.md](dev/SUIVI_BUGS_AMELIORATIONS.md) | Priorités actives, bugs et améliorations, tableau de couverture quiz (lots incomplets), statut réalisé / en cours. |

**Procédure recommandée avant un chantier significatif (correctif, feature, refonte) :**

1. Lire le haut de `dev/SUIVI_BUGS_AMELIORATIONS.md` (priorités et tableau de couverture à jour).
2. Parcourir les entrées récentes en tête de `dev/JOURNAL_REPRISE.md` (dernières sessions).
3. Consulter la fin de `dev/JOURNAL_REPRISE.md` pour la TODO consolidée et les décisions encore ouvertes.

Portail court : [dev/README.md](dev/README.md).
