# 🎯 Plan Phase 3 — Création de Nouveaux Exercices & Cours

**Date de démarrage :** 06/04/2026

## Objectif

Créer un lot pilote de **nouveaux exercices de qualité** pour 2-3 notions clés, en utilisant les endpoints IA (Quiz AI + Explications) pour générer du contenu pédagogique riche et cohérent.

---

## Contexte

- ✅ Endpoints IA sont PRÊTS PROD (`generate_quiz.php`, `generate_exercise_explanation.php`, `generate_precise_course.php`)
- ✅ Pile diagnostic est ROBUSTE (anti-répétition, fallback, E2E)
- ❌ **Besoin :** Contenu pédagogique de **qualité testée** (actuellement 1550 legacy + 48 formatés)
- 📊 **Produit :** Pivot Quiz AI confirmé → priorité = qualité sur quantité

---

## Lot Pilote 1 : Mathématiques 4ème

### Notion cible : "Addition et soustraction de fractions"

#### Sous-produits

1. **Exercice diagnostique** (Quiz AI)
   - 5 questions QCM + 2 questions texte libres
   - Couvre : dénominateurs simples → dénominateurs complexes
   - Appel API : `POST /src/api/ia/generate_quiz.php`
   - Payload : `{ level: "4eme", subject: "Mathématiques", competence: "Addition de fractions", type: "qcm" }`

2. **Explications d'erreurs** (Explanation API)
   - Générer pour 3 erreurs courantes (mauvais dénominateur, oubli de simplification, erreur de signe)
   - Appel API : `POST /src/api/ia/generate_exercise_explanation.php`
   - Payload : erreurs + réponse officielle

3. **Mini-cours ciblé** (Precise Course API)
   - Générer un mini-cours "Comment ajouter des fractions"
   - Appel API : `POST /src/api/ia/generate_precise_course.php`
   - Payload : `{ level: "4eme", subject: "Mathématiques", competence: "Addition de fractions", exercise_source: "Exercise ID 42" }`

4. **Tests de qualité**
   - Vérifier clarté pédagogique (compréhension 4ème)
   - Vérifier cohérence quiz ↔ explication ↔ cours
   - Vérifier absence de placeholders
   - Vérifier fonctionnement E2E (clic → réponse → explication)

#### Livrables

- `src/data/quiz/{id}.json` — Quiz QCM + texte
- `src/data/quiz_answers/{id}.json` — Réponses + corrections
- `src/data/courses/{id}.json` — Mini-cours ciblé (optionnel)
- `dev/reports/pilot-math-4eme.md` — Rapport qualité

---

## Lot Pilote 2 : Français 1ère

### Notion cible : "L'argumentation et la persuasion"

#### Sous-produits

1. **Exercice diagnostique** (Quiz AI)
   - 4 questions analyse de texte + 1 question production
   - Couvre : identifier arguments → construire argumentation
   - Utiliser fallback multi-provider

2. **Explications d'erreurs**
   - Erreur 1 : confusion argument/exemple
   - Erreur 2 : absence de connecteur logique

3. **Mini-cours**
   - Structure "Comment argumenter efficacement"

4. **Tests E2E**
   - Vérifier affichage correct des textes longs
   - Vérifier navigation dans l'explication multi-paragraphes

---

## Lot Pilote 3 : SVT 3ème

### Notion cible : "Reproduction et hérédité"

#### Sous-produits

1. **Exercice diagnostique** (Quiz schématisé)
   - 3 QCM + 2 schémas à compléter
   - Utiliser images/diagrammes si possible

2. **Explications visuelles**
   - Incorporer des explications sur processus biologiques

3. **Ressources complémentaires**
   - Générer mini-cours avec références (Eduscol, etc.)

---

## Processus Itératif

### Week 1 : Setup + Génération

1. [ ] Définir 3 notions cibles (math/français/svt)
2. [ ] Exécuter appels API pour générer Q/A/C par API
3. [ ] Valider pédagogiquement (human review)
4. [ ] Normaliser JSON selon `src/data/quiz` structure

### Week 2 : Tests + Affinage

1. [ ] Tests unitaires (JSON valide, champs présents)
2. [ ] Tests E2E (Playwright : clic quiz → réponse → explication → mini-cours modal)
3. [ ] Tests de régression (pas de cassure diagnostic existant)
4. [ ] Performance (temps de réponse API < 3s)

### Week 3 : Documentation + Release

1. [ ] Documenter processus création contenu
2. [ ] Créer guide qualité validation pédagogique
3. [ ] Release pilot + communication équipe

---

## Critères de Succès

| Critère                     | Target          | Status |
| --------------------------- | --------------- | ------ |
| Nouveaux exercices créés    | 3 lots          | ?      |
| Qualité pédagogique validée | 100% lot pilote | ?      |
| E2E tests PASSÉS            | 3/3             | ?      |
| Zéro erreur API             | 100% appels     | ?      |
| Documentation mise à jour   | Draft + Final   | ?      |

---

## Dépendances

- ✅ Endpoints IA fonctionnels
- ✅ E2E test framework (Playwright)
- ⚠️ **Human review pédagogique** (expert métier requis)
- ⚠️ Définition "qualité" d'un exercice (grille évaluation)

---

## Risques & Mitigation

| Risque                             | Impact    | Mitigation                          |
| ---------------------------------- | --------- | ----------------------------------- |
| Générations IA de mauvaise qualité | **Haut**  | Human review before release         |
| Incohérence quiz ↔ explication     | **Moyen** | Tester E2E complet                  |
| Approche produit ambiguë           | **Moyen** | Clarifier metrics de succès avec PO |

---

## Resources

- API endpoint doc : [src/api/ia/README.md](../../src/api/ia/README.md) (si existe)
- Quiz structure : [src/data/quiz/README.md](../../src/data/quiz/README.md) (si existe)
- Test E2E : [dev/tools/tests/e2e/diagnostic-quiz-paths.spec.ts](../tools/tests/e2e/diagnostic-quiz-paths.spec.ts)
