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

### [EN COURS] Remplacement des placeholders residuels (post-enrichissement)

13/03/2026 : re-audit execute apres enrichissement.

- Scan effectue : `python dev/tools/quiz/detect_quiz_placeholders.py --quiz-dir src/data/quiz --answers-dir src/data/quiz_answers --output dev/reports/placeholders_detected_2026-03-13.json`
- Resultat : 32127 placeholders detectes sur 1559 quiz (1559 quiz affectes).
- Repartition severite : CRITICAL 9801, HIGH 7701, MEDIUM 14625, LOW 0.
- Categories principales : `too_short_correction` 14621, `generic_template` 9653, `incomplete_sentence` 7701, `placeholder_text` 148.
- Action validee : traiter ce lot comme priorite active distincte (remplacement progressif avec validation qualite).

### [OK] Smoke test E2E diagnostic (post-reboot)

13/03/2026 : smoke E2E execute avec succes.

- Commande : `npx playwright test dev/tools/tests/e2e/diagnostic-quiz-paths.spec.ts --reporter=line`
- Resultat : 3 tests passes (5.2s).
- Conclusion : le flux E2E cible diagnostic est operationnel pour ce scenario.

### Reste a faire valide (priorites 1, 2 et 3)

1. Diagnostic quiz securise (anti-repetition utilisateur)

- Statut: EN COURS
- Reste concret: tester le comportement anti-repetition sur un volume cible (~50 tentatives) pour verifier l'absence de schema repetitif.

2. Pipeline validation diagnostic (parcours complet)

- Statut: EN COURS
- Reste concret:
  - smoke test E2E connecte post-reboot (front + API + progression),
  - ajustement fin des regles de parsing pour quelques cas legacy de `quiz_answers`.

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

# Suivi des bugs et améliorations — MonCoachScolaire

| Priorité | Page/Zone                         | Description courte                                                                                   | Statut                                                         | Responsable        | Date cible    | Commentaire                                                                                                                                                                                                                                           |
| -------- | --------------------------------- | ---------------------------------------------------------------------------------------------------- | -------------------------------------------------------------- | ------------------ | ------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Haute    | src/api/ia/generate_quiz.php      | **Quiz IA** : Générateur de quiz dynamique par IA (Groq / Perplexity / OpenAI)                       | Fait                                                           | Copilot            | 22/03/2026    | Backend opérationnel. Intégration avec `interactive-exercises.js`. Clé Groq gratuite à définir dans `.env` (`GROQ_API_KEY`). Priorité : tests de robustesse (timeout, erreur IA, parsing JSON).                                                       |
| Haute    | src/data/quiz contenu (PHASE 1B)  | Remplacement des placeholders residuels detectes apres enrichissement                                | **ANNULÉ**                                                     | Copilot            | 14/03/2026    | Remplacé par le système **Quiz IA** qui permet de régénérer du contenu de qualité à la demande.                                                                                                                                                       |
| Haute    | eleve/dashboard.php               | Restructuration layout en 4 lignes de 3 cartes + harmonisation headers                               | Fait                                                           | Copilot            | 07/03/2026    | Grid Tailwind strict (`items-stretch`, `h-full`), headers standardisés (`card-header`), hooks JS conservés                                                                                                                                            |
| Haute    | src/data/quiz contenu (PHASE 1)   | Amélioration qualité pédagogique quiz (questions claires, corrections détaillées, niveau académique) | ✅ COMPLÉTÉ - 1558/1559 quiz enrichis (100%), -85.2% problèmes | Copilot            | 08/03/2026    | Enrichissement 31 lots automatiques. HIGH -99.93% (14 problèmes restants), MEDIUM -99.92% (6 problèmes). Stratégie accélération: 1→5→10 lots. Scripts: enrich_batch_auto.py, quiz_quality_workflow.py. Doc: dev/tools/quiz/README_WORKFLOW_QUALITE.md |
| Haute    | src/data/quiz contenu (PHASE 2)   | Enrichissement avec sources vérifiées (Éduscol, Wikiversity) - corrections pédagogiques réelles      | ⏸️ EN ATTENTE - déploiement suspendu                           | Copilot+Perplexity | À replanifier | Déploiement production mis en pause. Autorisé: scan/dry-run/documentation. Reprise uniquement après lot pilote validé, checklist qualité signée et GO produit explicite.                                                                              |
| Haute    | API diagnostic + diagnostic.js    | Pagination + UX quiz diagnostics                                                                     | Fait                                                           | Copilot            | 07/03/2026    | 12 cartes/page, messages friendly, quiz #33 créé, 31 quiz validés                                                                                                                                                                                     |
| Haute    | Pipeline validation diagnostic    | Score réel + révision ciblée + anti-farming XP + choix quiz par ID                                   | En cours                                                       | Copilot            | 11/03/2026    | Livré: boutons refaire/revoir, affichage delta score, `include_answers=1`, anti-farming XP. Reste: smoke test E2E connecté post-reboot + ajustement fin des règles de parsing si cas legacy.                                                          |
| Haute    | src/data/quiz + API diagnostic    | V1 Quiz Bank: harmonisation + migration + 1517 quiz                                                  | Fait                                                           | Copilot            | 07/03/2026    | 1550 quiz (50/pair), métadonnées normalisées, anti-répétition prêt                                                                                                                                                                                    |
| Haute    | landingpage                       | Harmonisation des fonds de section (transparence)                                                    | Fait                                                           | Copilot            | 13/02/2026    | Patch appliqué                                                                                                                                                                                                                                        |
| Moyenne  | landingpage, dashboard, exercices | Harmonisation des ombres et hover des cards                                                          | Fait                                                           | Copilot            | 05/03/2026    | Patch CSS global appliqué (contraste + hover unifié)                                                                                                                                                                                                  |
| Haute    | eleve/lycee/lycee-accueil.php     | Uniformiser boutons (palette, hover)                                                                 | Fait                                                           | Copilot            | 12/02/2026    | Harmonisé                                                                                                                                                                                                                                             |
| Moyenne  | eleve/bac/bac-accueil.php         | Uniformiser boutons (palette, hover)                                                                 | Fait                                                           | Copilot            | 12/02/2026    | Harmonisé                                                                                                                                                                                                                                             |
| Haute    | landingpage                       | Correction duplication bouton Collège+                                                               | Fait                                                           | Copilot            | 12/02/2026    | Corrigé                                                                                                                                                                                                                                               |
| Moyenne  | landingpage                       | Accessibilité (contraste, aria-labels)                                                               | Fait                                                           | Copilot            | 04/03/2026    | Audit réalisé, couleurs et aria mis à jour                                                                                                                                                                                                            |
| Basse    | Toutes pages                      | Ajout d’un mode sombre                                                                               | À étudier                                                      |                    |               |                                                                                                                                                                                                                                                       |
| Haute    | landingpage                       | Séparation claire des sections (balises <section>)                                                   | Fait                                                           | Copilot            | 13/02/2026    | Doublons supprimés                                                                                                                                                                                                                                    |
| Moyenne  | eleve/college/college-accueil.php | Bordure mascotte Collège (épaisseur/couleur)                                                         | Fait                                                           | Copilot            | 12/02/2026    | Harmonisé                                                                                                                                                                                                                                             |
| Moyenne  | eleve/lycee/lycee-accueil.php     | Bordure mascotte Lycée (épaisseur/couleur)                                                           | Fait                                                           | Copilot            | 12/02/2026    | Harmonisé                                                                                                                                                                                                                                             |
| Moyenne  | eleve/bac/bac-accueil.php         | Bordure mascotte Bac (épaisseur/couleur)                                                             | Fait                                                           | Copilot            | 12/02/2026    | Harmonisé                                                                                                                                                                                                                                             |

> Ce tableau doit être mis à jour à chaque évolution, bug ou amélioration majeure.
> Statuts possibles : À faire / En cours / Fait / À étudier / Bloqué
