# 🤖 Scripts d'Enrichissement Automatique des Quiz

## Vue d'ensemble

Deux scripts Python professionnels pour **détecter** et **enrichir automatiquement** les quiz contenant des placeholders, avec **validation par APIs gratuites** pour garantir la qualité et la vérifiabilité du contenu.

---

## 📋 Script 1 : Détection des Placeholders

### Fichier
`detect_quiz_placeholders.py`

### Objectif
Scanne les fichiers `quiz/*.json` et `quiz_answers/*.json` pour détecter automatiquement tous les types de placeholders (contenu générique, incomplet, non pédagogique).

### Catégories de placeholders détectés

#### 🔴 CRITICAL (contenu inutilisable)
- **placeholder_text** : "question de diagnostic pour...", "TODO", "FIXME", "[...]"
- **generic_template** : "concept a", "notion 1", "chapitre 2"
- **lorem_ipsum** : "lorem ipsum dolor sit amet"

#### 🟠 HIGH (contenu incomplet)
- **incomplete_sentence** : Phrases finissant par "...", trop courtes (< 20 car)
- **missing_context** : "Le mot = ...", "Vrai ?" sans contexte
- **variable_placeholder** : `{variable}`, `$var`, `%%placeholder%%`

#### 🟡 MEDIUM (formulation à améliorer)
- **telegraphic** : Formulations type "mot = ...?"
- **too_short_correction** : Corrections < 80 caractères
- **generic_feedback** : "Correct", "Bravo" sans explication

#### 🟢 LOW (améliorations cosmétiques)
- **formatting_issue** : Espaces multiples, ponctuation excessive
- **typo_indicators** : TOUT EN MAJUSCULES, MauvaiseCapitalisation

### Usage

```bash
# Scan complet de tous les quiz
python dev/tools/quiz/detect_quiz_placeholders.py

# Scan d'un range spécifique
python dev/tools/quiz/detect_quiz_placeholders.py --min-id 1 --max-id 100

# Export personnalisé
python dev/tools/quiz/detect_quiz_placeholders.py \
  --quiz-dir src/data/quiz \
  --answers-dir src/data/quiz_answers \
  --output dev/reports/placeholders_full_scan.json
```

### Sortie

**Console** : Résumé avec statistiques
```
📊 RAPPORT DE DÉTECTION DES PLACEHOLDERS
════════════════════════════════════════════════════════════════════
Quiz scannés: 1559
Placeholders détectés: 342
Quiz affectés: 127

📈 Répartition par sévérité:
  🔴 CRITICAL: 45
  🟠 HIGH: 89
  🟡 MEDIUM: 178
  🟢 LOW: 30

🎯 Quiz prioritaires (critical + high):
  IDs: [6, 7, 8, 9, 10, 11, 12, 42, 58, 91, ...]
```

**JSON** (`dev/reports/placeholders_detected.json`) :
```json
{
  "meta": {
    "scan_date": "2026-03-09T10:30:00",
    "total_quiz_scanned": 1559,
    "total_placeholders_found": 342,
    "quiz_with_placeholders_count": 127
  },
  "statistics": {
    "by_severity": {"critical": 45, "high": 89, "medium": 178, "low": 30},
    "by_category": {
      "placeholder_text": 23,
      "too_short_correction": 156,
      "incomplete_sentence": 67
    }
  },
  "quiz_ids_affected": [6, 7, 8, 9, 10, 11, 12, ...],
  "detections": [
    {
      "quiz_id": 10,
      "level": "3eme",
      "subject": "Anglais",
      "location": "question",
      "category": "placeholder_text",
      "severity": "critical",
      "pattern": "question\\s+(de\\s+)?diagnostic\\s+pour",
      "context": "...question de diagnostic pour Anglais niveau 3eme...",
      "question_index": 0,
      "answer_index": null
    }
  ]
}
```

---

## 🔧 Script 2 : Enrichissement Automatique via API

### Fichier
`auto_enrich_quiz_api.py`

### Objectif
Enrichit automatiquement les quiz détectés comme contenant des placeholders en **générant du contenu pédagogique** vérifié via **APIs gratuites** (Wikipedia, Wiktionary, Wikiversity).

### APIs utilisées (100% gratuites)

| API | Usage | Confiance | Rate limit |
|-----|-------|-----------|------------|
| **Wikipedia FR** | Définitions académiques, contexte | 90% | 200/h |
| **Wiktionary FR** | Définitions linguistiques (Français/Anglais) | 80% | 200/h |
| **Wikiversity FR** | Contenu pédagogique par niveau | 85% | 200/h |

### Fonctionnalités

✅ **Extraction automatique du concept** depuis le texte original  
✅ **Requêtes API multi-sources** pour croiser les données  
✅ **Génération de questions** adaptées au niveau scolaire  
✅ **Corrections pédagogiques** avec sources vérifiables  
✅ **Score de qualité** (0-1) pour chaque enrichissement  
✅ **Flagging** pour review manuelle si score < 0.7  
✅ **Backup automatique** avant toute modification  
✅ **Mode dry-run** pour tester sans modifier  

### Usage

#### Enrichir un quiz spécifique
```bash
python dev/tools/quiz/auto_enrich_quiz_api.py --quiz-id 10
```

#### Enrichir plusieurs quiz
```bash
python dev/tools/quiz/auto_enrich_quiz_api.py --quiz-ids 6,7,8,9,10,11,12
```

#### Enrichir depuis le rapport de détection (RECOMMANDÉ)
```bash
# 1. Détecter les placeholders
python dev/tools/quiz/detect_quiz_placeholders.py

# 2. Enrichir tous les CRITICAL + HIGH
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity high

# 3. Mode test (dry-run) avant production
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity critical \
  --dry-run
```

### Sortie

**Console** :
```
════════════════════════════════════════════════════════════════════
📝 Enrichissement Quiz #10 - Anglais 3eme
════════════════════════════════════════════════════════════════════

🔍 Question 1: Enrichissement requis
  ✅ Enrichi (score: 0.87)
🔍 Correction 1: Enrichissement requis
  ✅ Enrichi (score: 0.92)
🔍 Correction 2: Enrichissement requis
  ✅ Enrichi (score: 0.78)

💾 Fichier answers sauvegardé: src/data/quiz_answers/10.json
💾 Fichier quiz sauvegardé: src/data/quiz/10.json

════════════════════════════════════════════════════════════════════
📊 RÉSUMÉ DE L'ENRICHISSEMENT
════════════════════════════════════════════════════════════════════
Quiz traités: 7
Questions enrichies: 12
Corrections enrichies: 56
Mode: PRODUCTION
```

**Backups** : `dev/backups/quiz_enrichment/`
```
10_quiz_20260309_103045.json
10_answers_20260309_103045.json
```

### Exemple d'enrichissement

**Avant** (placeholder) :
```json
{
  "question": "question de diagnostic pour Anglais niveau 3eme",
  "correction": "..."
}
```

**Après** (enrichi via API Wikipedia) :
```json
{
  "question": "Which definition best describes 'English grammar'?",
  "correction": "English grammar : English grammar is the set of structural rules of the English language. This includes the structure of words, phrases, clauses, sentences, and whole texts.\n\nSource vérifiée : Wikipedia - https://fr.wikipedia.org/wiki/English_grammar"
}
```

**Score de qualité** : 0.87/1.00  
**Review nécessaire** : Non (score > 0.7)

---

## 🎯 Workflow Recommandé

### Option A : Enrichissement manuel ciblé

```bash
# 1. Détecter les placeholders
python dev/tools/quiz/detect_quiz_placeholders.py

# 2. Lire le rapport JSON
cat dev/reports/placeholders_detected.json

# 3. Enrichir manuellement les quiz critiques (IDs spécifiques)
# (Édition manuelle via Python ou directement dans JSON)
```

### Option B : Enrichissement semi-automatique (RECOMMANDÉ)

```bash
# 1. Détecter
python dev/tools/quiz/detect_quiz_placeholders.py

# 2. Test en dry-run des CRITICAL
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity critical \
  --dry-run

# 3. Review des propositions dans la console

# 4. Application en production
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity critical

# 5. Validation manuelle des quiz enrichis (score < 0.7)
#    Voir les backups dans dev/backups/quiz_enrichment/

# 6. Répéter pour HIGH, puis MEDIUM
```

### Option C : Enrichissement automatique complet

```bash
# 1. Détecter
python dev/tools/quiz/detect_quiz_placeholders.py

# 2. Enrichir CRITICAL (backup auto)
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity critical

# 3. Enrichir HIGH
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity high

# 4. Validation globale
python dev/tools/quiz/validate_quiz_quality.py

# 5. Review manuelle uniquement des quiz avec quality_score < 0.7
```

---

## ⚙️ Configuration

### Dépendances Python

```bash
pip install requests
```

Toutes les autres dépendances sont dans la stdlib Python 3.8+.

### Rate Limiting

Les scripts respectent automatiquement les limites des APIs :
- **Délai entre requêtes** : 0.5 seconde (configurable dans `API_DELAY`)
- **User-Agent** : `MonCoachScolaire/1.0 (Educational; quiz enrichment bot)`
- **Timeout** : 10 secondes par requête

### Structure de dossiers attendue

```
src/data/
├── quiz/              # Fichiers quiz (1.json, 2.json, ...)
└── quiz_answers/      # Fichiers réponses (1.json, 2.json, ...)

dev/
├── reports/           # Rapports de détection
│   └── placeholders_detected.json
├── backups/           # Backups automatiques
│   └── quiz_enrichment/
│       ├── 10_quiz_20260309_103045.json
│       └── 10_answers_20260309_103045.json
└── tools/quiz/        # Scripts
    ├── detect_quiz_placeholders.py
    └── auto_enrich_quiz_api.py
```

---

## 📊 Métriques de qualité

### Score de qualité (0-1)

Le script `auto_enrich_quiz_api.py` calcule un **score de qualité** pour chaque enrichissement :

| Critère | Poids | Détails |
|---------|-------|---------|
| **Longueur** | 30% | ≥ 150 car = 0.3, ≥ 80 car = 0.15 |
| **Sources** | 40% | 1 source = 0.2, 2+ sources = 0.4 |
| **Confiance** | 30% | Moyenne des confiances API (Wikipedia = 0.9) |

**Exemple** :
- Texte 180 caractères → 0.3
- 2 sources (Wikipedia + Wiktionary) → 0.4
- Confiance moyenne (0.9 + 0.8)/2 = 0.85 → 0.255
- **Score total** : 0.3 + 0.4 + 0.255 = **0.955/1.00** ✅

### Seuils de validation

- **Score ≥ 0.7** : Validation automatique ✅
- **Score < 0.7** : Review manuelle requise ⚠️
- **Score < 0.5** : Enrichissement rejeté ❌

---

## 🔐 Sécurité et fiabilité

### ✅ Points forts

1. **Backup automatique** avant toute modification
2. **Mode dry-run** pour tester sans risque
3. **Sources vérifiables** (URLs Wikipedia/Wiktionary dans corrections)
4. **Rate limiting** respecté (pas de ban API)
5. **Logs détaillés** pour traçabilité
6. **Isolation** : un quiz ne peut pas corrompre les autres

### ⚠️ Limitations connues

1. **APIs gratuites** : limites de 200 requêtes/heure
2. **Qualité variable** : certains concepts absents de Wikipedia
3. **Langue** : Optimisé pour Français (Wikipedia FR)
4. **Review manuelle** : nécessaire pour score < 0.7
5. **Pas de génération de choix multiples** : questions seulement

### 🎯 Recommandations

- **Toujours lancer en `--dry-run`** avant production
- **Enrichir par lots** (10-20 quiz max) pour review
- **Vérifier les backups** avant commit Git
- **Compléter manuellement** les enrichissements à score < 0.7
- **Utiliser `validate_quiz_quality.py`** après enrichissement

---

## 🛠️ Dépannage

### Problème : "No matches found" / API timeout

**Cause** : Rate limit dépassé ou concept introuvable  
**Solution** :
```bash
# Augmenter le délai entre requêtes
# Dans auto_enrich_quiz_api.py, ligne ~34:
API_DELAY = 1.0  # au lieu de 0.5
```

### Problème : Score qualité toujours < 0.7

**Cause** : Concept trop spécifique ou absent Wikipedia  
**Solution** :
```bash
# Enrichir manuellement ces quiz ou  
# Utiliser une API alternative (ex: DBpedia, Wikidata)
```

### Problème : Backup non supprimable

**Cause** : Fichiers en lecture seule  
**Solution** :
```bash
# PowerShell
Remove-Item -Recurse -Force dev\backups\quiz_enrichment\
```

---

## 📈 Statistiques attendues

Sur la base de 1559 quiz analysés :

| Métrique | Valeur estimée |
|----------|----------------|
| Quiz avec placeholders | ~8% (127/1559) |
| Placeholders CRITICAL | ~45 (3%) |
| Placeholders HIGH | ~89 (6%) |
| Enrichissements réussis (score ≥ 0.7) | ~85% |
| Review manuelle requise | ~15% |
| Temps moyen par quiz | ~5-8 secondes |
| Temps total (127 quiz) | ~10-15 minutes |

---

## 🚀 Roadmap / Améliorations futures

- [ ] Support multilingue (Wikipedia EN pour quiz Anglais)
- [ ] Génération automatique des choix multiples
- [ ] Intégration API Éduscol (si disponible)
- [ ] Cache local des requêtes API (éviter doublons)
- [ ] Export rapport HTML avec diff avant/après
- [ ] Validation post-enrichissement automatique
- [ ] Mode interactif (approbation manuelle par quiz)

---

## 📝 Exemples de commandes complètes

### Scénario 1 : Premier scan complet

```bash
cd d:/Hostinger/public_html/moncoachscolaire

# Activer venv Python
.venv\Scripts\activate

# Scan complet
python dev/tools/quiz/detect_quiz_placeholders.py

# Voir le résumé
cat dev/reports/placeholders_detected.json | jq '.meta'
```

### Scénario 2 : Test enrichissement sur un quiz

```bash
# Test dry-run sur quiz #10
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --quiz-id 10 \
  --dry-run

# Application réelle
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --quiz-id 10
```

### Scénario 3 : Production complète

```bash
# 1. Détection
python dev/tools/quiz/detect_quiz_placeholders.py

# 2. Test CRITICAL
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity critical \
  --dry-run

# 3. Production CRITICAL
python dev/tools/quiz/auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity critical

# 4. Validation
python dev/tools/quiz/validate_quiz_quality.py

# 5. Régénération packs
python dev/tools/quiz/generate_quiz_packs.py
```

---

## 🤝 Contribution

Pour améliorer ces scripts :

1. Tester en `--dry-run` sur > 50 quiz
2. Documenter les cas limites rencontrés
3. Proposer de nouveaux patterns de placeholders
4. Ajouter des API alternatives (DBpedia, Wikidata)
5. Mettre à jour ce README avec exemples réels

---

## 📚 Voir aussi

- `dev/tools/quiz/validate_quiz_quality.py` : Validation qualité pédagogique
- `dev/tools/quiz/generate_quiz_packs.py` : Génération des packs pour frontend
- `dev/docs/TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md` : Template qualité manuel
- `dev/docs/SYNTHESE_LOT_QUALITE_PEDAGOGIQUE.md` : Synthèse lot qualité

---

**Version** : 1.0.0 (09/03/2026)  
**Auteur** : MonCoachScolaire - Enrichissement automatique  
**Licence** : Usage interne projet
