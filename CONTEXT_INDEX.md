# CONTEXT_INDEX.md — Index de contexte (MonCoachScolaire)

**Ajout du 01/04/2026 :** Traçabilité consolidée de l'harmonisation des couleurs des guides de remédiation (collège vert, lycée violet/pourpre, bac doré), avec règle d'implémentation via `get_theme_variant_by_level`. Voir `dev/JOURNAL_REPRISE.md` (entrée datée du 01/04/2026) et `DOCUMENTATION.md` (section "Trace consolidée").

**Ajout du 07/03/2026 :** Ajout explicite des fichiers de suivi opérationnel (`dev/JOURNAL_REPRISE.md`, `dev/SUIVI_BUGS_AMELIORATIONS.md`) dans les fichiers essentiels de reprise.

## 📝 Points primordiaux à respecter

### Enrichissement documentaire

Pour toute documentation ou guide technique, enrichir le fichier adapté selon le type :

- Contexte produit, workflows métier, philosophie, architecture : `.github/CONTEXT_PRODUIT.md`
- Roadmap technique, jalons, lots de travail : `.github/copilot-plan.md`
- Conventions IA, patterns, règles de patch : `.github/REGLES_IA.md`
- Index rapide, centralisation des fichiers essentiels : `.github/PROJECT_CONTEXT.md`, `CONTEXT_INDEX.md`
- Documentation utilisateur ou technique générale : `README.md`, `DOCUMENTATION.md`
  Règle anti-doublon : ne jamais dupliquer l’information, toujours référencer le fichier source.

### Outils, scripts et sauvegardes

- Scripts et outils : toujours référencer ou enrichir `dev/tools/README.md` (source unique)
- Sauvegardes SQL : référencer ou enrichir `db/*.sql` (structure, migrations, backups)
- Sauvegardes JSON : référencer ou enrichir `db/json/*.json` (exercices, sources de vérité)
- Sources de vérité JSON : toujours utiliser le fichier le plus central (ex : `db/json/unified_exercises.json`)

Exemple :

> 📖 Pour en savoir plus : Voir [README.md](../README.md), [DOCUMENTATION.md](../DOCUMENTATION.md), [dev/tools/README.md](../dev/tools/README.md)

But de ce fichier : centraliser rapidement les points essentiels à lire après un redémarrage.

## Fichiers essentiels (ordre recommandé)

- `.github/copilot-instructions.md`
- `CONTEXT_INDEX.md` (ce fichier)
- `README.md`
- `DOCUMENTATION.md`
- `dev/tools/README.md` # ← Index central des scripts, outils, tests
- `dev/JOURNAL_REPRISE.md` # ← Journal daté de reprise
- `dev/SUIVI_BUGS_AMELIORATIONS.md` # ← Suivi priorisé des bugs/ameliorations
- `package.json`
- `playwright.config.js`
- `.github/workflows/ci.yml`
- `.github/skills/SKILLS.md`
- `.github/skills/quiz-generator/SKILL.md`
- `.github/agents/planner.agent.md`
- `.github/agents/implementer.agent.md`
- `.github/agents/reviewer.agent.md`
- `.github/agents/release-manager.agent.md`
- `src/components/footer_component.php`
- `dev/tools/scripts/inject-footer.js`
- `public/index.php`

> Ajoutez ici tout fichier clé supplémentaire si vous en trouvez (1 par ligne).

## Règles non négociables (10)

1. Routing centralisé via `index.php?page=...` (ne pas casser les alias legacy).
2. `APP_URL` / `BASEURL` : toujours référencer `site_url()` pour générer les liens.
3. Scoping CSS : éviter `html`/`body` globaux ; utiliser classes de page (`body.landing-page`) pour ciblage. Les fonds de page globaux doivent être gérés **dans `public/assets/css/style.css`** via variables (`--page-bg-image`, `--page-bg-overlay`) et les overrides par page se font en changeant ces variables sur `body.page-*`.
4. Pas de `!important` dans les nouvelles règles (éviter sauf exception documentée).
5. Tests : les features critiques doivent avoir smoke tests + un E2E simple (Playwright).
6. PHP : PSR-12, PDO prepared statements, pas de `echo` pour JSON endpoints.
7. Sécurité : sessions HttpOnly + SameSite, CSRF token sur tous les formulaires.
8. Scripts & outils : privilégier `dev/tools/` pour scripts d'automatisation, versionnés.
9. Static includes : pour pages statiques, utiliser `public/assets/html/*.html` + script d'injection.
10. Logs & Rollback : garder commits petits, fournir diff + test de rollback simple.

## Travail en cours / Où on s'est arrêté (court)

- Migration footer : composant + fragment + injection terminé (Version 2.2.2).
- Centralisation `exercices` : CTAs redirigés vers `index.php?page=exercices` (progressif).
- Stylelint : règles renforcées, reste ~13 warnings à traiter (priorité secondaire).
- CI : Playwright ajouté — proposer job conditionnel `RUN_E2E` pour exécuter E2E en PR seulement.

## Regénérer le bundle de contexte

1. `npm install` (si nécessaire)
2. `npm run context` — crée/actualise `CONTEXT_BUNDLE.md` à la racine.

## Note rapide pour Copilot

- Après redémarrage, exécuter `npm run context` puis lire `CONTEXT_BUNDLE.md`.

### Prompt Copilot (FR) à coller dans Copilot Chat

```text
@workspace
Objectif : recharger le contexte projet après redémarrage.

1) Vérifie que ces fichiers existent et lis-les dans cet ordre :
- .github/copilot-instructions.md
- CONTEXT_INDEX.md
- CONTEXT_BUNDLE.md (si présent, il remplace les fichiers ci-dessus)
- README.md
- DOCUMENTATION.md
- dev/JOURNAL_REPRISE.md
- dev/SUIVI_BUGS_AMELIORATIONS.md
- PROJECT_CONTEXT.md (ou PROJECTCONTEXT.md)

2) Donne-moi :
- 10 bullets "règles non négociables" (routing, BASEURL, conventions, CSS scoping, tests)
- 5 bullets "où on en est / derniers chantiers"
- La prochaine action la plus logique (1 seule), avec les fichiers à modifier.

3) Si CONTEXT_INDEX.md ou CONTEXT_BUNDLE.md manque : propose le contenu exact à créer (format Markdown), sans inventer de règles.
```

---

_Fichier généré manuellement le: 2026-02-09 — mettre à jour si objectifs changent._
