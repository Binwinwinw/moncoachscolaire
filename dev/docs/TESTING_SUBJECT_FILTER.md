# Tests & URL acceptées — Filtre Matière

Ce document décrit les URL acceptées pour consulter et filtrer les exercices par matière, ainsi que les tests E2E ajoutés.

## URL supportées (legacy & canonical)
- Collège (index): `index.php?page=college/exercices-college`
- Niveaux collège (legacy/canonical):
  - `index.php?page=college/6eme/exercices-6eme`
  - `index.php?page=college/5eme/exercices-5eme`
  - `index.php?page=college/4eme/exercices-4eme`
  - `index.php?page=college/3eme/exercices-3eme`
- Lycée (variantes prises en charge):
  - `index.php?page=lycee/2nde/exercices-2nde`
  - `index.php?page=lycee/seconde/exercices-seconde`
  - `index.php?page=lycee/1ere/exercices-1ere`
  - `index.php?page=lycee/premiere/exercices-premiere`
  - La page fusion `index.php?page=lycee/exercices-lycee&niveau=2nde|1ere` est prise en charge mais certaines variantes 404 si mal formées. Préférer les routes ci-dessus.
- BAC : `index.php?page=bac/exercices-bac` (conteneur dynamique chargé par JS)

## Comportement du filtre
- Le sélecteur `Matière` est **affiché uniquement** pour les utilisateurs connectés.
- Le paramètre `?subject=` peut être utilisé **en GET** pour filtrer les exercices côté serveur (fonctionne aussi pour invités).

## Tests ajoutés
- Playwright E2E: `dev/tools/tests/e2e/subject-filter.spec.js`
  - Scénarios couverts:
    - Invité: vérifie que le sélecteur n\'apparaît pas mais que le filtrage par query param fonctionne.
    - Utilisateur demo: connexion automatique via `demo/demo`, vérification que le sélecteur est visible et que la sélection filtre les exercices.

Validation & importation (outils ajoutés):
- Validation JSON: `dev/tools/import_export/validate_exercises_json.php` — vérifie structure et champs des JSON selon `db/json/exercices/schema_parsing_exercice.json`.
- Dry-run import JSON: `dev/tools/import_export/dry_run_import_json.php` — simule un import et affiche un rapport sans toucher la base.
- API: `src/api/subjects.php` — `GET ?level=Seconde` retourne `{ subjects: [...] }`.
- Tests smoke ajoutés:
  - `dev/tools/tests/test_validate_exercises_json.php` — exécute le validateur.
  - `dev/tools/tests/test_api_subjects.php` — vérifie que l'API subjects renvoie un JSON valide.

## Exécution des tests
- Lancer Playwright (prendre soin d\'avoir le serveur local démarré sur `http://localhost/moncoachscolaire`):

```bash
npx playwright install
npm run test:e2e
```

---
Si tu veux, j\'ajoute aussi un petit script pour exposer les matières disponibles via une API `GET /api/subjects.php?level=...` (utile pour clients SPA).
