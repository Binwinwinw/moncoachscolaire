# 🚀 Guide de Démarrage Rapide - Enrichissement Automatique

## Objectif
Détecter et enrichir automatiquement les quiz contenant des placeholders en **5 minutes** via APIs gratuites.

---

## ✅ Prérequis (1 min)

```bash
# 1. Vérifier environnement Python
.venv\Scripts\python.exe --version
# Doit afficher : Python 3.8+ 

# 2. Vérifier dépendances
.venv\Scripts\python.exe -c "import requests; print('OK')"
# Si erreur : .venv\Scripts\python.exe -m pip install requests
```

---

## 🎯 Workflow Simple (3 étapes)

### Étape 1 : Détecter les placeholders (30s)

```bash
# Option A : Script Python
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py

# Option B : Script batch Windows (menu interactif)
dev\tools\quiz\enrichissement_auto.bat
# Puis choisir [1] Detection placeholders (scan complet)
```

**Résultat** : Fichier `dev/reports/placeholders_detected.json` généré

---

### Étape 2 : Test enrichissement (1 min)

```bash
# Mode dry-run (AUCUNE modification)
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
  --from-placeholders dev/reports/placeholders_detected.json ^
  --severity critical ^
  --dry-run
```

**Résultat** : Vous voyez ce qui **serait** enrichi sans modification

---

### Étape 3 : Enrichissement production (2 min)

```bash
# Production (avec backup automatique)
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
  --from-placeholders dev/reports/placeholders_detected.json ^
  --severity critical
```

**Résultat** : 
- Quiz enrichis dans `src/data/quiz/` et `src/data/quiz_answers/`
- Backups créés dans `dev/backups/quiz_enrichment/`

---

## 📊 Comprendre le Rapport de Détection

Après l'étape 1, vous obtenez un résumé dans la console :

```
📊 RAPPORT DE DÉTECTION DES PLACEHOLDERS
════════════════════════════════════════
Quiz scannés: 1476
Placeholders détectés: 32538
Quiz affectés: 1476

📈 Répartition par sévérité:
  🔴 CRITICAL: 15000    ← À traiter en PRIORITÉ
  🟠 HIGH: 7103         ← Important
  🟡 MEDIUM: 10435      ← Améliorations
  🟢 LOW: 0             ← Cosmétique

🎯 Quiz prioritaires (critical + high):
  IDs: [5, 6, 7, 8, 9, 10, ...]
```

**Action recommandée** : Enrichir d'abord les CRITICAL, puis HIGH, puis MEDIUM.

---

## 🎨 Exemples de Placeholders Détectés

### 🔴 CRITICAL - Contenu inutilisable

**Avant** :
```json
{
  "question": "TODO: question de diagnostic pour Anglais niveau 3eme",
  "correction": "FIXME"
}
```

**Détection** :
- Catégorie : `placeholder_text`
- Pattern : `TODO|FIXME|question\s+de\s+diagnostic`

**Après enrichissement API** :
```json
{
  "question": "Which definition best describes 'English grammar'?",
  "correction": "English grammar : English grammar is the set of structural rules of the English language...\nSource vérifiée : Wikipedia - https://fr.wikipedia.org/wiki/English_grammar"
}
```

---

### 🟠 HIGH - Contenu incomplet

**Avant** :
```json
{
  "question": "Le mot = ...?",
  "correction": "Réponse..."
}
```

**Détection** :
- Catégorie : `incomplete_sentence`
- Pattern : `\.{3,}` (points de suspension)

**Après enrichissement API** :
```json
{
  "question": "Parmi les propositions suivantes concernant grammaire, laquelle est correcte ?",
  "correction": "Grammaire : La grammaire est l'étude systématique des éléments constitutifs d'une langue...\nSource vérifiée : Wikipedia - https://fr.wikipedia.org/wiki/Grammaire"
}
```

---

### 🟡 MEDIUM - Formulation à améliorer

**Avant** :
```json
{
  "correction": "Bonne réponse."
}
```

**Détection** :
- Catégorie : `too_short_correction` (< 80 caractères)
- Catégorie : `generic_feedback` (pas d'explication)

**Après enrichissement API** :
```json
{
  "correction": "Fraction : En mathématiques, une fraction est un moyen d'écrire un nombre rationnel comme quotient de deux entiers...\n\nPour simplifier une fraction, on divise le numérateur et le dénominateur par leur PGCD.\nExemple : 2/4 = 1/2 (divisé par 2).\n\nSource vérifiée : Wikipedia - https://fr.wikipedia.org/wiki/Fraction"
}
```

---

## 🛠️ Commandes Avancées

### Enrichir un seul quiz (test)

```bash
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
  --quiz-id 42 ^
  --dry-run
```

### Enrichir une liste de quiz

```bash
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
  --quiz-ids 5,6,7,8,9,10,11,12
```

### Scan d'un range spécifique

```bash
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py ^
  --min-id 1 ^
  --max-id 100
```

### Enrichir HIGH + CRITICAL ensemble

```bash
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
  --from-placeholders dev/reports/placeholders_detected.json ^
  --severity high
# Traite HIGH + CRITICAL (car HIGH inclut CRITICAL dans l'ordre de priorité)
```

---

## 📁 Fichiers Générés

### Rapports de détection
```
dev/reports/
├── placeholders_detected.json          ← Scan par défaut
├── placeholders_full_scan_20260309.json ← Archive horodatée
└── quiz_quality_report.md              ← Validation qualité
```

### Backups automatiques
```
dev/backups/quiz_enrichment/
├── 10_quiz_20260309_110530.json        ← Quiz avant modification
├── 10_answers_20260309_110530.json     ← Answers avant modification
├── 11_quiz_20260309_110535.json
└── 11_answers_20260309_110535.json
```

### Logs d'enrichissement
Les résultats sont affichés dans la console et peuvent être redirigés :

```bash
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
  --quiz-id 10 > logs/enrichment_10.log 2>&1
```

---

## ⚠️ Bonnes Pratiques

### ✅ À FAIRE

1. **Toujours commencer par `--dry-run`** pour voir ce qui sera modifié
2. **Vérifier les backups** dans `dev/backups/quiz_enrichment/` avant commit Git
3. **Enrichir par lots de 20-50 quiz** max pour review manuelle
4. **Valider avec** `validate_quiz_quality.py` après enrichissement
5. **Régénérer quiz_packs** après enrichissement :
   ```bash
   .venv\Scripts\python.exe dev/tools/quiz/generate_quiz_packs.py
   ```

### ❌ À ÉVITER

1. ❌ Ne PAS enrichir 1000+ quiz d'un coup sans review
2. ❌ Ne PAS ignorer les quiz avec `quality_score < 0.7` (review manuelle requise)
3. ❌ Ne PAS commit sans vérifier les diffs (`git diff src/data/quiz/`)
4. ❌ Ne PAS désactiver les backups (`--no-backup`) en production
5. ❌ Ne PAS dépasser les rate limits API (respecter 200 req/h Wikipedia)

---

## 🐛 Dépannage Express

### Problème : "ModuleNotFoundError: No module named 'requests'"

**Solution** :
```bash
.venv\Scripts\python.exe -m pip install requests
```

---

### Problème : "Quiz scannés: 0"

**Cause** : Mauvais chemin ou range vide  
**Solution** :
```bash
# Vérifier que les fichiers existent
dir src\data\quiz\*.json
# Ajuster le range
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py --min-id 1 --max-id 20
```

---

### Problème : "API timeout" ou "No matches found"

**Cause** : Rate limit dépassé ou concept introuvable  
**Solution** :
```python
# Dans auto_enrich_quiz_api.py, ligne 34
API_DELAY = 1.0  # au lieu de 0.5 (ralentit les requêtes)
```

---

### Problème : Enrichissement avec score < 0.7

**Cause** : Concept trop spécifique ou source Wikipedia absente  
**Solution** : Review manuelle + enrichissement manuel
```bash
# Voir le backup pour rollback si nécessaire
dir dev\backups\quiz_enrichment\
```

---

## 📈 Statistiques Attendues

Sur la base du scan initial (1476 quiz) :

| Métrique | Valeur |
|----------|--------|
| Placeholders totaux | 32 538 |
| Quiz affectés | 1476 (100%) |
| 🔴 CRITICAL | 15 000 (46%) |
| 🟠 HIGH | 7 103 (22%) |
| 🟡 MEDIUM | 10 435 (32%) |
| Temps scan complet | ~2 min |
| Temps enrichissement 100 quiz | ~8-12 min |
| Taux succès enrichissement | ~85% (score ≥ 0.7) |

---

## 🎓 Workflow Progressif Recommandé

### Semaine 1 : Familiarisation (20 quiz)

```bash
# Jour 1 : Détection
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py --min-id 1 --max-id 20

# Jour 2 : Test enrichissement dry-run
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py --quiz-ids 1,2,3,4,5 --dry-run

# Jour 3 : Enrichissement production (5 quiz)
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py --quiz-ids 1,2,3,4,5

# Jour 4 : Review manuelle + validation
# Vérifier manuellement les fichiers enrichis
git diff src/data/quiz/1.json
```

### Semaine 2 : Montée en charge (100 quiz)

```bash
# Enrichir CRITICAL sur range 1-100
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py --min-id 1 --max-id 100
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
  --from-placeholders dev/reports/placeholders_detected.json ^
  --severity critical
```

### Semaine 3+ : Production complète (1476 quiz)

```bash
# Scan complet
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py

# Enrichissement CRITICAL (33 quiz)
.venv\Scripts\python.exe dev/tools/quiz/auto_enrich_quiz_api.py ^
  --from-placeholders dev/reports/placeholders_detected.json ^
  --severity critical

# Enrichissement HIGH (7103 items)
# Par lots de 50 quiz
# ... (à découper)
```

---

## 📞 Support

**Documentation complète** : [README_AUTO_ENRICHMENT.md](README_AUTO_ENRICHMENT.md)  
**Workflows détaillés** : Section "Workflow Recommandé"  
**Dépannage avancé** : Section "Dépannage"

**Fichiers de référence** :
- Scripts : `dev/tools/quiz/detect_quiz_placeholders.py`, `auto_enrich_quiz_api.py`
- Batch interactif : `dev/tools/quiz/enrichissement_auto.bat`
- Template qualité : `dev/docs/TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md`

---

**Version** : 1.0 (09/03/2026)  
**Temps de lecture** : 5 minutes  
**Temps d'exécution** : 5 minutes
