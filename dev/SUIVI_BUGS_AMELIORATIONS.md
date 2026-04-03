## [02/04/2026] Decision produit — Pivot Quiz AI (mise a jour des priorites)

- Le pivot **Quiz AI** est confirme comme choix produit principal pour la generation de quiz cote eleve.
- Les scripts de creation manuelle de quiz ne sont plus une finalite; ils restent des outils de support technique.
- Le lot historique de generation massive (1643 quiz) est classe comme retour d'experience qualite, pas comme cible a reproduire.
- Priorites actives alignees: robustesse Quiz AI (timeouts/erreurs/parsing), qualite UX eleve, et developpement de nouveaux exercices/cours.

## [13/03/2026] Synthese priorites actives (clarification)

### [14/03/2026] Re-baseline detection placeholders (raffinement anti-bruit)

14/03/2026 : detecteur affine puis re-scan complet execute.

- Rapport KPI de reference : `dev/reports/placeholders_detected_2026-03-14_refined.json`
- Quiz scannes : 1559
- Placeholders detectes : 15984
- Quiz affectes : 1443
- Severite : CRITICAL 8099, HIGH 7366, MEDIUM 519, LOW 0
- Categories principales : `generic_template` 7892, `incomplete_sentence` 7366, `too_short_correction` 515, `placeholder_text` 207

Comparatif vs scan 14/03 non raffine (`dev/reports/placeholders_detected_2026-03-14.json`) :

- Total placeholders : 30855 -> 15984 (delta -14871)
- Quiz affectes : 1511 -> 1443 (delta -68)
- `too_short_correction` : 15386 -> 515

Decision : baseline qualite mise a jour sur le rapport raffine pour eviter les faux positifs QCM/vrai-faux.

### [CLOS - REMPLACE PAR QUIZ AI] Remplacement des placeholders residuels (post-enrichissement)

13/03/2026 : re-audit execute apres enrichissement.

- Scan effectue : `python dev/tools/quiz/detect_quiz_placeholders.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers --output dev/reports/placeholders_detected_2026-03-13.json`
- Resultat : 32127 placeholders detectes sur 1559 quiz (1559 quiz affectes).
- Repartition severite : CRITICAL 9801, HIGH 7701, MEDIUM 14625, LOW 0.
- Categories principales : `too_short_correction` 14621, `generic_template` 9653, `incomplete_sentence` 7701, `placeholder_text` 148.
- Action initiale archivee : ce lot n'est plus une priorite active depuis la validation du pivot Quiz AI.

### [OK] Smoke test E2E diagnostic (post-reboot)

13/03/2026 : smoke E2E execute avec succes.

- Commande : `npx playwright test dev/tools/tests/e2e/diagnostic-quiz-paths.spec.ts --reporter=line`
- Resultat : 3 tests passes (5.2s).
- Conclusion : le flux E2E cible diagnostic est operationnel pour ce scenario.

### Reste a faire valide (priorites 1, 2 et 3)

1. Diagnostic quiz securise (anti-repetition utilisateur)

- Statut: EN COURS
- **[02/04/2026] Objectif "50 tentatives" ABANDONNÉ** — voir section "Abandonné / Remplacé" ci-dessus.
- Nouveau reste concret: (1) enrichir les petits pools (ex: 4eme Mathémaiques = 2 seulement), (2) afficher UX si répétitions fréquentes, (3) valider simulation sur tous les niveaux/sujets produits.

2. Pipeline validation diagnostic (parcours complet)

- Statut: FAIT (02/04/2026)
- Smoke test E2E connecté livré:
  - Charge liste diagnostique ✅
  - Clique quiz → affiche questions ✅
  - Remplit réponses ✅
  - API submit.php reçoit et répond (401 si non authentifié, conforme) ✅
  - Test en `dev/tools/tests/e2e/diagnostic-quiz-paths.spec.ts`
- Reste futur: tester avec utilisateur authentifié (amélioration, pas blocker)

3. Mode sombre

- Statut: A ETUDIER
- Reste concret: cadrage fonctionnel/UI, impact CSS global, priorisation produit.

### Suspendu / en attente (repertorie)

- PHASE 2 enrichissement avec sources verifiees (Eduscol/Wikiversity): EN ATTENTE / SUSPENDU
  - Condition de reprise: lot pilote valide + checklist qualite signee + GO produit explicite.

- Deploiement enrichissement automatique a grande echelle: SUSPENDU
  - Autorise pendant suspension: scan, dry-run, documentation.

### [OK] Restructuration Dashboard Élève

07/03/2026 : Réorganisation complète du layout en 4 lignes de 3 cartes alignées horizontalement avec harmonisation des styles. Grid Tailwind strict (`grid-cols-1 md:grid-cols-2 xl:grid-cols-3`), alignement vertical (`items-stretch`), hauteur homogène (`h-full`). Headers standardisés avec pattern `card-header` pour toutes les cartes. Hooks JS conservés (aucune régression fonctionnelle). Validation lint PHP OK.

### [OK] Migration TailwindCSS dashboard élève

17/02/2026 : Migration et fiabilisation du chargement CSS (dashboard élève) terminée avec succès. Le head HTML est désormais toujours généré par le routeur, le CSS s’applique sans hack. (Voir DOCUMENTATION.md et .github/PROJECT_CONTEXT.md)

### [EN COURS] Diagnostic quiz sécurisé (questions publiques, correction serveur)

06/03/2026 :

- Fait : correction des liens assets dynamiques sur pages exercices niveaux (collège, lycée, bac) pour éliminer les 404 selon environnement.
- Fait : décision d'architecture validée -> ne plus exposer `answer`/`correction` dans les JSON publics.
- Fait : endpoint serveur `src/api/diagnostic/submit.php` (correction, score, feedback, enregistrement `quizresult`, update `userprogress`).
- Fait : `public/assets/js/diagnostic.js` branché sur endpoint serveur (plus de score aléatoire côté client).
- Fait : `public/quiz/*.json` épurés (`answer`/`correction` retirés) + version privée serveur `src/data/quiz_private/*.json`.
- À faire : historique anti-répétition utilisateur (~50 tests sans même schéma) + smoke test E2E complet connecté.
- Statut : En cours (lot minimal livré, lot anti-répétition restant).

# Suivi des bugs et ameliorations -- MonCoachScolaire

> Maj au 02/04/2026. Statuts : A faire / En cours / Fait / Abandonne / A etudier
> Pour l'historique complet, voir dev/JOURNAL_REPRISE.md.

---

## Priorites actives (a faire / en cours)

| Priorite | Zone                         | Description                                                           | Statut    | Commentaire                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| -------- | ---------------------------- | --------------------------------------------------------------------- | --------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Haute    | src/api/ia/generate_quiz.php | Quiz IA - robustesse : timeout, fallback, validation JSON             | En cours  | Durcissement endpoint livre le 02/04/2026 : validation entree, ordre provider Groq en priorite, fallback, normalisation JSON, erreurs HTTP propres. Reste : test reel multi-provider et cas timeout.                                                                                                                                                                                                                                                  |
| Haute    | API diagnostic + front       | Anti-repetition quiz : adapter au stock disponible par niveau/matière | En cours  | **[02/04/2026] Objectif "50 tentatives sans répétition" ABANDONNÉ** (relevé du contexte batch). Correctif livré : tri basé sur historique cumulé (tentatives + récence), simulation OK. Validation : pool de 41 → 40 uniques/50 tentatives; pool de 2 → répétition rapide (limite structurelle). **Ligne directrice actuelle**: anti-répétition dans les limites du stock réel; enrichissement petit pools ou affichage UX si répétitions fréquentes. |
| Haute    | Pipeline diagnostic          | Smoke test E2E connecte livré (list → quiz → questions → submit)      | Fait      | Smoke test E2E livré le 02/04/2026. Couvre : chargement diagnostic, clique quiz, affichage questions, remplissage réponses, soumission API. Test dans `dev/tools/tests/e2e/diagnostic-quiz-paths.spec.ts`. Amélioration future : authentifier les tests pour valider la soumission complète (pas blocker).                                                                                                                                            |
| Haute    | Nouveaux exercices et cours  | Creer du contenu pedagogique de qualite (exercices, cours)            | A faire   | Priorite produit confirmee le 02/04/2026 suite au pivot Quiz AI.                                                                                                                                                                                                                                                                                                                                                                                      |
| Basse    | Toutes pages                 | Mode sombre                                                           | A etudier | Cadrage UI/CSS global a definir.                                                                                                                                                                                                                                                                                                                                                                                                                      |

---

## [02/04/2026] Chantier transverse — Normalisation des assets + cache-busting

Objectif:

- fiabiliser le chargement CSS/JS/images sur toutes les pages
- eliminer les chemins hardcodes fragiles (`/assets/...`, `/public/assets/...`)
- eviter les faux bugs de cache navigateur

Perimetre mesure:

- hard refs detectees: 40
- fichiers concernes: 15
- fichiers utilisant deja `asset_url(...)`: 25

Etat valide a date:

- `asset_url(...)` ajoute maintenant un parametre de version `?v=<filemtime>` dans:
  - `src/config/site_boot.php`
  - `src/config/config.php`
- correction critique livree sur landing:
  - chemin background CSS corrige vers `../../img/...` depuis `assets/css/pages/`

Plan de lotissement (source de verite):

| Lot | Intitule                                 | Statut   | Cible                                                            |
| --- | ---------------------------------------- | -------- | ---------------------------------------------------------------- |
| 1   | Socle helper assets                      | FAIT     | Cache-busting `?v=filemtime`, verification locale CSS versionnes |
| 2   | Pages publiques prioritaires             | FAIT     | landing/login/register/pages legales                             |
| 3   | Pages exercices eleve (plus gros volume) | FAIT     | college/lycee/bac exercices\* (scripts + css)                    |
| 4   | Composants partages                      | A FAIRE  | footer/topbar/components communs                                 |
| 5   | Audit final + documentation              | EN COURS | checklist de reprise, preuves, reste a faire                     |

Preuves Lot 2 (cloture):

- `src/pages/login.php` migre sur `asset_url('assets/css/...')`
- `src/pages/register.php` migre sur `asset_url('assets/css/...')`
- scan cible pages publiques: plus de references hardcodees `/assets` ou `/public/assets`

Preuves Lot 3 (cloture):

- 9 fichiers `src/pages/eleve/**/exercices*.php` migres (blocs `if/else` hardcodes remplaces)
- verification syntaxe PHP OK sur toutes les pages exercices du perimetre
- scan perimetre Lot 3: `HITS=0` pour `<link|script>` hardcodes `/assets` et `/public/assets`

Definition de fini (DoD) du chantier:

- plus aucune reference hardcodee critique dans `src/**/*.php` pour CSS/JS d'app
- toutes les pages publiques critiques chargent des assets versionnes (`?v=`)
- verification visuelle locale sur pages cibles (desktop + mobile)
- bloc "passation" mis a jour dans `dev/JOURNAL_REPRISE.md`

Commandes de controle a reutiliser:

```powershell
# 1) inventaire refs hardcodees
$files = Get-ChildItem src -Recurse -File -Include *.php
$hard = $files | Select-String -Pattern '<link[^>]+href="/(assets|public/assets)|<script[^>]+src="/(assets|public/assets)' -AllMatches
"HARD_REF_MATCHES=$($hard.Count)"
"FILES_WITH_HARD_REFS=$((($hard | Select-Object -ExpandProperty Path -Unique).Count))"

# 2) verification href CSS versionnes sur landing
node dev/tmp/check-css-version.js
```

Regles d'execution pour reprise:

- traiter un lot a la fois
- livrer un diff minimal par lot
- verifier rendu avant lot suivant
- noter immediatement dans ce fichier ce qui est "FAIT / EN COURS / A FAIRE"

---

## Realise (cloture)

| Zone                                     | Description                                               | Date       | Commentaire                                                    |
| ---------------------------------------- | --------------------------------------------------------- | ---------- | -------------------------------------------------------------- |
| src/api/ia/generate_quiz.php             | Quiz IA backend livre (Groq / Perplexity / OpenAI)        | 22/03/2026 | Integre avec interactive-exercises.js                          |
| Guides remediation college / lycee / bac | Palette couleur par niveau via get_theme_variant_by_level | 01/04/2026 | College vert, lycee violet, bac dore. Zero couleur en dur.     |
| eleve/dashboard.php                      | Restructuration layout 4 lignes x 3 cartes                | 07/03/2026 | Grid Tailwind strict, headers standardises, hooks JS conserves |
| API diagnostic + diagnostic.js           | Pagination + UX quiz diagnostics                          | 07/03/2026 | 12 cartes/page, messages friendly, 31 quiz valides             |
| src/data/quiz + API diagnostic           | V1 Quiz Bank : 1550 quiz, metadonnees normalisees         | 07/03/2026 | Anti-repetition pret, separation public/prive operationnelle   |
| src/api/diagnostic/submit.php            | Correction serveur, score reel, anti-farming XP           | 06/03/2026 | Reponses/corrections retirees des JSON publics                 |
| Audit securite complet                   | PHP, API, fichiers sensibles, dependances                 | 19/03/2026 | composer audit OK, recommandations .gitignore et acces serveur |
| landingpage                              | Harmonisation fonds, ombres, hover, sections, boutons     | fev. 2026  | Doublons supprimes, contraste + aria mis a jour                |
| eleve/college, lycee, bac                | Uniformisation boutons, mascottes, cartes niveau          | fev. 2026  | Harmonise sur les 3 niveaux                                    |

---

## Abandonne / Remplace (historique)

> Ces phases font partie de l'histoire du projet. Elles ne sont plus des objectifs actifs.

| Zone                             | Description                                              | Abandon    | Raison                                                                                                                                                                     |
| -------------------------------- | -------------------------------------------------------- | ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Diagnostic quiz anti-repetition  | **Objectif "50 tentatives sans répétition"**             | 02/04/2026 | Spécifique à l'ère batch (volume → diversité). Pivot Quiz AI remplace : anti-répétition s'adapte au stock actuel par niveau/matière. Enrichissement ou UX si petits pools. |
| src/data/quiz -- PHASE 1 (masse) | Generation de ~1643 quiz par scripts automatiques        | mars 2026  | Volume eleve mais qualite insuffisante : placeholders massifs, QCM creux. Decision : changer de direction.                                                                 |
| src/data/quiz -- PHASE 1B        | Remplacement manuel des placeholders residuels           | mars 2026  | Sans objet : remplace par Quiz AI qui genere a la demande avec qualite pilotee.                                                                                            |
| src/data/quiz -- PHASE 2         | Enrichissement sources verifiees (Eduscol / Wikiversity) | mars 2026  | Suspendu definitivement : la direction Quiz AI rend ce lot sans objet.                                                                                                     |
