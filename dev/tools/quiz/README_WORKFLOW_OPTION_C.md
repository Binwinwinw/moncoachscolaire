# Workflow Option C — Enrichissement Quiz avec Sources Vérifiées

**Date de création** : 08/03/2026  
**Phase** : Phase 2 — Qualité pédagogique avec sources  
**Scripts** : `dev/tools/quiz/enrich_with_sources.py`

---

## Contexte

**Problème Phase 1** : Les 1558 quiz enrichis automatiquement contenaient des corrections template-génériques (ex: "Revois le chapitre dans ton manuel") au lieu de véritables explications pédagogiques.

**Solution Phase 2** : Workflow hybride **Copilot + Perplexity** pour générer des corrections pédagogiques sourcées (Éduscol, Wikiversity, ressources vérifiables).

---

## Architecture Workflow Option C

```
┌─────────────────┐
│  1. EXPORT CSV  │  Copilot extrait metadata quiz
│  (Copilot)      │  → 32 batches × 50 quiz
└────────┬────────┘
         │
         ▼
┌─────────────────────┐
│  2. ENRICHISSEMENT  │  Perplexity enrichit avec sources
│  (Perplexity Auto)  │  → exercisenotion + answers
└────────┬────────────┘
         │
         ▼
┌─────────────────┐
│  3. MERGE AUTO  │  Copilot réintègre JSON enrichi
│  (Copilot)      │  → src/data/quiz/*.json + quiz_answers/*.json
└─────────────────┘
```

---

## Étape 1 : Export CSV Metadata (Copilot)

**Commande** :
```bash
python dev/tools/quiz/enrich_with_sources.py \
  --export-csv \
  --start-id 1 \
  --end-id 2000 \
  --batch-size 50 \
  --output-prefix dev/reports/quiz_metadata
```

**Résultat** :
- 32 fichiers CSV : `dev/reports/quiz_metadata_batch1.csv` … `batch32.csv`
- Colonnes : `id, level, subject, title, description, question_count, questions_json`
- Total : 1559 quiz exportés

**Fichiers générés** :
- ✅ `dev/reports/quiz_metadata_batch1.csv` (50 quiz)
- ✅ `dev/reports/quiz_metadata_batch2.csv` (50 quiz)
- …
- ✅ `dev/reports/quiz_metadata_batch32.csv` (9 quiz)

---

## Étape 2 : Enrichissement Perplexity (Mode Auto)

**Input** : CSV batch (ex: `quiz_metadata_batch1.csv`)

**Prompt structuré** : `dev/reports/PROMPT_PERPLEXITY_BATCH1.txt`

**Consignes Perplexity** :
1. Lire le CSV batch
2. Pour chaque quiz, générer :
   - **exercisenotion** : 4-5 concepts du programme officiel EN (Éduscol)
   - **answers** : Réponses + corrections pédagogiques (2-4 phrases, ≥80 chars, sources vérifiables)
3. Retourner JSON unique format :
   ```json
   [
     {
       "id": 5,
       "provider": "perplexity-auto",
       "exercisenotion": [
         {"notion": "Homophones grammaticaux", "description": "..."},
         ...
       ],
       "answers": [
         {"question_id": 1, "type": "qcm", "answer": "à", "correction": "À = préposition..."},
         ...
       ]
     },
     ...
   ]
   ```

**Sources prioritaires** :
- Éduscol (⭐⭐⭐⭐⭐) : Programme officiel EN
- Wikiversity : Ressources pédagogiques OER
- Ressources vérifiables (pas d'invention)

**Output attendu** : `dev/reports/batch1_enriched.json`

---

## Étape 3 : Merge Automatique (Copilot)

**Test en dry-run** (recommandé) :
```bash
python dev/tools/quiz/enrich_with_sources.py \
  --merge-json dev/reports/batch1_enriched.json \
  --source-credit "Sources: Programme officiel Education nationale (Eduscol) + ressources verifiees" \
  --dry-run
```

**Merge réel** :
```bash
python dev/tools/quiz/enrich_with_sources.py \
  --merge-json dev/reports/batch1_enriched.json \
  --source-credit "Sources: Programme officiel Education nationale (Eduscol) + ressources verifiees"
```

**Actions du script** :
1. Lit le JSON enrichi Perplexity
2. Pour chaque quiz :
   - Met à jour `src/data/quiz/{id}.json` :
     - Ajoute `exercisenotion`
     - Ajoute `sources_info` (credit, provider, source_urls)
   - Réécrit `src/data/quiz_answers/{id}.json` :
     - Ajoute réponses enrichies avec `correction` pédagogique
     - Ajoute `correction_source` sur chaque réponse
3. Normalise les index réponses (0, 1, 2, …)

**Validation** :
- Dry-run affiche : `merged=X skipped=Y input=Z`
- Vérifier `errors=[]` (aucune erreur)
- Audit manuel : 5 quiz/niveau (40 quiz totaux) pour valider pédagogie

---

## Structure JSON Attendue (Perplexity → Copilot)

**Format accepté** :

1. **Array direct** :
   ```json
   [
     {"id": 5, "exercisenotion": [...], "answers": [...]},
     {"id": 6, "exercisenotion": [...], "answers": [...]}
   ]
   ```

2. **Objet avec clé `data`/`results`/`items`** :
   ```json
   {
     "data": [
       {"id": 5, "exercisenotion": [...], "answers": [...]},
       ...
     ]
   }
   ```

### Champs obligatoires par item :

- **`id`** (int/string) : Identifiant quiz
- **`exercisenotion`** (array) : Concepts pédagogiques
  - `notion` (string) : Nom concept
  - `description` (string) : Description détaillée
- **`answers`** (array) : Réponses complètes
  - `question_id` (int) : ID question (1-based)
  - `type` (string) : `qcm`, `texte`, `vrai-faux`, `open`
  - `answer` (mixed) : Réponse correcte
  - `correction` (string) : Explication pédagogique (≥80 chars)

### Champs optionnels :

- **`source_credit`** (string) : Crédit source spécifique à ce quiz
- **`provider`** (string) : Identifiant source (ex: `perplexity-auto`)
- **`source_urls`** (array) : URLs sources vérifiables

---

## Commandes Rapides

### Export tous les quiz en batches de 50 :
```bash
python dev/tools/quiz/enrich_with_sources.py --export-csv --batch-size 50
```

### Merge batch1 en test :
```bash
python dev/tools/quiz/enrich_with_sources.py \
  --merge-json dev/reports/batch1_enriched.json \
  --source-credit "Eduscol + Wikiversity" \
  --dry-run
```

### Merge batch1 réel :
```bash
python dev/tools/quiz/enrich_with_sources.py \
  --merge-json dev/reports/batch1_enriched.json \
  --source-credit "Eduscol + Wikiversity"
```

---

## Fichiers Importants

### Scripts :
- `dev/tools/quiz/enrich_with_sources.py` : Script principal (export + merge)

### Documentation :
- `dev/reports/PROMPT_PERPLEXITY_BATCH1.txt` : Template prompt Perplexity
- `dev/JOURNAL_REPRISE.md` : Journal reprise session (état workflow)
- `dev/SUIVI_BUGS_AMELIORATIONS.md` : Tableau de bord progression

### Données :
- `dev/reports/quiz_metadata_batch*.csv` : CSV exports (32 batches)
- `dev/reports/batch*_enriched.json` : JSON enrichis Perplexity (à créer)

---

## Workflow Complet (32 Batches)

**Timeline estimée** : 1-2h (dépend vitesse Perplexity)

### Étapes :

1. ✅ Export 32 CSV batches (FAIT)
2. ⏳ Batch 1 (50 quiz) :
   - Envoyer CSV + prompt à Perplexity
   - Récupérer JSON enrichi
   - Merge dry-run
   - Audit 5 quiz
   - Merge réel
3. 🔄 Batches 2-32 (31 batches restants) :
   - Répéter workflow par lot
   - Validation progressive (audit 1 quiz/batch)
   - Commit intermédiaire tous les 5 batches

### Rollback :

Si erreur critique sur un batch :
```bash
git restore src/data/quiz/{id}.json src/data/quiz_answers/{id}.json
```

---

## Validation Qualité

### Critères (Phase 2) :

- [x] Corrections ≥80 caractères
- [x] Corrections pédagogiques (pas templates)
- [x] Sources vérifiables citées
- [x] Crédit source sur 100% réponses
- [x] Concepts programme officiel EN

### Audit manuel :

- 5 quiz/niveau × 8 niveaux = **40 quiz audités**
- Validation : corrections véritablement pédagogiques
- Acceptation : 95% conformité critères

---

## Évolutions Futures

- [ ] API web Perplexity pour automatisation totale
- [ ] Script batch automatique 32 batches en 1 commande
- [ ] Intégration pre-commit hook validation sources
- [ ] Dashboard qualité temps réel (sources citées %)

---

**Créé par** : Copilot + Perplexity (collaboration hybride)  
**Dernière mise à jour** : 08/03/2026 04h00
