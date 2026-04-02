### scan_php_security.py

Script Python d’analyse statique sécurité (PHP & JS) : détecte patterns dangereux, accès fichiers risqués, echo non échappés, patterns JS (eval, Function, innerHTML, document.write, setTimeout/setInterval dynamiques). Rapport console. Voir [security-skill/scripts/scan_php_security.md](security-skill/scripts/scan_php_security.md).

# README Outils MonCoachScolaire

## 📚 Inventaire des endpoints API

La liste exhaustive des endpoints API et leur rôle est centralisée dans :
📖 [dev/reports/api_inventory.md](../reports/api_inventory.md)

> Toujours référencer ce fichier pour la documentation, la maintenance ou l’audit des APIs.

## Centralisation des outils, scripts et tests — MonCoachScolaire

Ce fichier complète l’index général CONTEXT_INDEX.md et la documentation principale DOCUMENTATION.md, en listant explicitement tous les dossiers et points d’entrée pour :

- Scripts d’automatisation et utilitaires
- Outils de migration, import/export, maintenance
- Scripts de test et de debug
- Tests unitaires et E2E

---

## 📁 dev/tools/ — Scripts & Outils

- **db/** : scripts de gestion base de données (import, export, migration)
  - **all_exercises_clean_enriched.normalized.json.sql** : fichier SQL le plus récent, source de vérité pour les exercices (extraction du 21/02/2026)
  - Toujours utiliser ce fichier pour toute migration, import ou audit d'exercices.
  - Les anciens fichiers JSON ou SQL sont obsolètes et doivent être archivés.
- **admin/** : création admin, gestion utilisateurs
- **exercises/** : import/export/validation d’exercices
- **courses/** : scripts liés aux cours (enrichissement, liaison, génération)
- **maintenance/** : activer/désactiver le mode maintenance
- **import_export/** : scripts d’import/export divers
- **migration/** : scripts de migration d’environnements
- **security/** : outils de sécurité et vérification
- **tests/** : scripts de test ad-hoc, debug, et E2E
- **scripts/** : scripts divers (injection, helpers, etc.)
- **quiz/** : scripts de génération et maintenance des quizzes
  - Documentation workflow preview/enrichissement: `dev/docs/SYSTEME_PREVIEW_ENRICHISSEMENT_QUIZ.md`
  - `build_diagnostic_bundles.js` : génère les bundles diagnostics par niveau (`src/data/quiz_packs/*`, `src/data/quiz_answers_packs/*`)
  - `generate_quiz_bank.py` : V1, génère des quiz diagnostics jusqu'a une cible (par defaut 50) a partir des templates existants.
    - Sorties principales: `public/quiz/*` et `src/data/quiz_answers/*`
    - Option compat backend submit: `--sync-src-quiz` pour aussi ecrire `src/data/quiz/*`
    - Garde-fou: la creation reelle exige `--apply` (sinon mode non destructif)
    - Harmonisation sans suppression: `--harmonize-only` normalise `level/subject` dans les fichiers existants
  - `generate_0template.py` : template canonique pour creer un nouveau `generate_<matiere>_<niveau>.py` (pattern public quiz sans reponses + fichier answers separe)
    - Renommage decide le 14/03/2026 pour conserver le template en tete de liste parmi les scripts `generate_*.py`
  - `validate_generator_pattern.py` : controle de conformite des scripts `generate_*.py` avec la checklist projet
  - `validate_quiz_bank.py` : valide la banque generee (schema minimal, non-fuite de reponses dans `public/quiz`, couverture par niveau/matiere)
  - `config_quiz_bank.v1.json` : configuration V1 (cible, aliases de normalisation, chemins)

### Workflow recommande (quiz generators)

1. Copier le template:

- copier `dev/tools/quiz/generate_0template.py`
- creer `generate_<matiere>_<niveau>.py` a partir de ce fichier

2. Verifier la conformite pattern:
   - `python dev/tools/quiz/validate_generator_pattern.py --file dev/tools/quiz/generate_<matiere>_<niveau>.py`
3. Verifier toute la famille de generateurs:
   - `python dev/tools/quiz/validate_generator_pattern.py`
4. Ensuite seulement, lancer la generation reelle.

### Mise a jour migration (12/03/2026)

- Scripts migrés au pattern template (write pipeline + `__main__` + sanitation public quiz):
  - `dev/tools/quiz/generate_hg_1ere.py`
  - `dev/tools/quiz/generate_pc_1ere.py`
  - `dev/tools/quiz/generate_philo_1ere.py`
- Validation globale apres migration:
  - commande: `python dev/tools/quiz/validate_generator_pattern.py`
  - resultat: `failed_checks: 0` sur la famille cible

> Tous les scripts sont appelables en CLI : `php dev/tools/[chemin]/[script].php`
> Scripts Node : `node dev/tools/[chemin]/[script].js`
> Scripts Python : `python dev/tools/[chemin]/[script].py`

---

## 📁 dev/tools/tests/ — Tests & Debug

- **README.md** : documentation d’exécution des tests
- **test\_\*.php** : scripts de test API, sécurité, routing, etc.
- **e2e/** : tests E2E Playwright (JS)
- **phpunit.xml** : configuration PHPUnit
- **ExampleTest.php, ExerciseTest.php, ...** : tests unitaires

> Lancer tous les tests : `./vendor/bin/phpunit` (ou voir README.md pour options)

---

## 📁 dev/reports/ — Rapports d’audit & inventaires

- **api_inventory.md** : inventaire des endpoints API
- **pages_inventory.md** : inventaire des pages PHP
- **hooks_inventory.md** : inventaire des hooks front/JS
- **routing*smoke*\*.md** : rapports de tests de routing

---

## 📁 docs/ — Documentation complète

- Guides d’intégration, sécurité, admin, responsive, API, etc.

---

## 🔗 Références rapides

- **Index général** : CONTEXT_INDEX.md
- **Documentation principale** : DOCUMENTATION.md
- **Suivi bugs/améliorations** : dev/SUIVI_BUGS_AMELIORATIONS.md
- **Tests** : dev/tools/tests/README.md
- **Outils/scripts** : ce fichier

---

> Ce fichier doit être mis à jour à chaque ajout ou déplacement d’un outil/script/test majeur.
> Dernière mise à jour : 12 mars 2026
