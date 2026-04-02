# 📑 INDEX - Enrichissement Automatique des Quiz

## 🚀 Démarrage Rapide (5 min)

**Nouveau ? Commencez ici** : [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md)

**Menu interactif Windows** :
```bash
dev\tools\quiz\enrichissement_auto.bat
```

---

## 📂 Structure des Fichiers

### 🔧 Scripts Exécutables

| Fichier | Type | Description | Usage |
|---------|------|-------------|-------|
| `detect_quiz_placeholders.py` | Python | Détection placeholders | `python detect_quiz_placeholders.py` |
| `auto_enrich_quiz_api.py` | Python | Enrichissement via API | `python auto_enrich_quiz_api.py --quiz-id 10` |
| `enrichissement_auto.bat` | Batch | Menu interactif Windows | Double-clic ou `enrichissement_auto.bat` |

### 📖 Documentation

| Fichier | Pages | Contenu | Public |
|---------|-------|---------|--------|
| [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md) | 8 | Guide démarrage rapide | ⭐ Débutants |
| [README_AUTO_ENRICHMENT.md](README_AUTO_ENRICHMENT.md) | 20 | Documentation complète | Utilisateurs avancés |
| [RECAP_ENRICHMENT_20260309.md](RECAP_ENRICHMENT_20260309.md) | 10 | Récapitulatif projet | Chefs de projet |
| [README_WORKFLOW_QUALITE.md](README_WORKFLOW_QUALITE.md) | 15 | Workflow qualité global | Tous |

### 📊 Rapports Générés

| Fichier | Taille | Description | Dernière MAJ |
|---------|--------|-------------|--------------|
| `dev/reports/placeholders_detected.json` | ~4 MB | Scan par défaut | 09/03/2026 |
| `dev/reports/placeholders_full_scan_20260309.json` | ~4 MB | Archive scan complet | 09/03/2026 11h00 |

### 💾 Backups Automatiques

| Dossier | Contenu | Nommage |
|---------|---------|---------|
| `dev/backups/quiz_enrichment/` | Backups avant modification | `{quiz_id}_{type}_{timestamp}.json` |

---

## 🎯 Parcours d'Utilisation Recommandés

### ⚡ Parcours Express (Débutant)

1. **Lancer menu** : `enrichissement_auto.bat`
2. **Choix [1]** : Détection placeholders (scan complet)
3. **Choix [3]** : Enrichissement CRITICAL (dry-run test)
4. **Review console** → si satisfait :
5. **Choix [4]** : Enrichissement CRITICAL (production)

📖 Voir : [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md)

---

### 🎓 Parcours Avancé (Utilisateur expérimenté)

1. **Détection personnalisée** :
   ```bash
   python detect_quiz_placeholders.py --min-id 1 --max-id 100
   ```

2. **Enrichissement ciblé** :
   ```bash
   python auto_enrich_quiz_api.py --quiz-ids 6,7,8,9,10 --dry-run
   ```

3. **Production avec filtrage sévérité** :
   ```bash
   python auto_enrich_quiz_api.py \
     --from-placeholders dev/reports/placeholders_detected.json \
     --severity critical
   ```

4. **Validation post-enrichissement** :
   ```bash
   python validate_quiz_quality.py
   ```

📖 Voir : [README_AUTO_ENRICHMENT.md](README_AUTO_ENRICHMENT.md)

---

### 🏢 Parcours Production (Chef de projet)

1. **Audit initial** : Lire [RECAP_ENRICHMENT_20260309.md](RECAP_ENRICHMENT_20260309.md)
2. **Planification** : 32 538 placeholders → ~200h effort (voir estimations)
3. **Workflow progressif** :
   - Semaine 1-2 : CRITICAL (15 000 items)
   - Semaine 3-5 : HIGH (7 103 items)
   - Semaine 6-15 : MEDIUM (10 435 items)
4. **KPIs** :
   - Taux succès enrichissement : ≥ 85%
   - Taux review manuelle : ≤ 15%
   - Qualité finale : 95%+ quiz conformes

📖 Voir : [RECAP_ENRICHMENT_20260309.md](RECAP_ENRICHMENT_20260309.md)

---

## 🔍 Navigation par Besoin

### "Je veux détecter les placeholders"

➡️ **Script** : `detect_quiz_placeholders.py`  
➡️ **Doc** : [README_AUTO_ENRICHMENT.md](README_AUTO_ENRICHMENT.md#-script-1--détection-des-placeholders)  
➡️ **Exemple** :
```bash
python detect_quiz_placeholders.py --min-id 1 --max-id 50
```

---

### "Je veux enrichir 1 quiz spécifique"

➡️ **Script** : `auto_enrich_quiz_api.py`  
➡️ **Doc** : [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md#enrichir-un-seul-quiz-test)  
➡️ **Exemple** :
```bash
python auto_enrich_quiz_api.py --quiz-id 42 --dry-run
```

---

### "Je veux enrichir tous les CRITICAL"

➡️ **Script** : `auto_enrich_quiz_api.py` + rapport détection  
➡️ **Doc** : [README_AUTO_ENRICHMENT.md](README_AUTO_ENRICHMENT.md#option-b--enrichissement-semi-automatique-recommandé)  
➡️ **Workflow** :
```bash
# 1. Détection
python detect_quiz_placeholders.py

# 2. Enrichissement
python auto_enrich_quiz_api.py \
  --from-placeholders dev/reports/placeholders_detected.json \
  --severity critical
```

---

### "Je veux comprendre les résultats du scan"

➡️ **Rapport** : `dev/reports/placeholders_detected.json`  
➡️ **Doc** : [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md#-comprendre-le-rapport-de-détection)  
➡️ **Statistiques clés** :
- 32 538 placeholders détectés
- 1476 quiz affectés (100%)
- 🔴 15 000 CRITICAL, 🟠 7 103 HIGH, 🟡 10 435 MEDIUM

---

### "Je veux voir des exemples avant/après enrichissement"

➡️ **Doc** : [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md#-exemples-de-placeholders-détectés)  
➡️ **Exemples** :
- CRITICAL : "TODO: question..." → "Which definition best describes..."
- HIGH : "Le mot = ...?" → "Parmi les propositions concernant {concept}..."
- MEDIUM : "Bonne réponse." → "Fraction : En mathématiques... (150 car)"

---

### "J'ai un problème technique"

➡️ **Dépannage** : [README_AUTO_ENRICHMENT.md](README_AUTO_ENRICHMENT.md#-dépannage)  
➡️ **Problèmes fréquents** :
- "ModuleNotFoundError: requests" → `pip install requests`
- "Quiz scannés: 0" → Vérifier range/path
- "API timeout" → Augmenter `API_DELAY`
- Score qualité < 0.7 → Review manuelle nécessaire

---

## 📊 Statistiques Globales (09/03/2026)

### Scan Complet

- **Quiz scannés** : 1476
- **Placeholders détectés** : 32 538
- **Quiz affectés** : 1476 (100%)

### Répartition Sévérité

| 🔴 CRITICAL | 🟠 HIGH | 🟡 MEDIUM | 🟢 LOW |
|-------------|---------|-----------|--------|
| 15 000 (46%) | 7 103 (22%) | 10 435 (32%) | 0 (0%) |

### Top Catégories

1. **generic_template** : 14 967 ("concept a", "notion 1")
2. **too_short_correction** : 10 431 (< 80 caractères)
3. **incomplete_sentence** : 7 103 (finissent par "...")
4. **placeholder_text** : 33 ("TODO", "FIXME")

---

## 🎯 Roadmap Enrichissement

### Phase 1 : CRITICAL (Semaines 1-2)
- **Objectif** : 15 000 placeholders
- **Focus** : generic_template + placeholder_text
- **Durée estimée** : 100 heures

### Phase 2 : HIGH (Semaines 3-5)
- **Objectif** : 7 103 placeholders
- **Focus** : incomplete_sentence
- **Durée estimée** : 47 heures

### Phase 3 : MEDIUM (Semaines 6-15)
- **Objectif** : 10 435 placeholders
- **Focus** : too_short_correction
- **Durée estimée** : 52 heures

**TOTAL PROJET** : ~200 heures (~4 mois à 3h/jour)

---

## 🔗 Liens Rapides

### Documentation Essentielle

- 🚀 [Guide Démarrage Rapide](QUICKSTART_ENRICHMENT.md) — 5 min de lecture
- 📖 [Documentation Complète](README_AUTO_ENRICHMENT.md) — 20 pages
- 📊 [Récapitulatif Projet](RECAP_ENRICHMENT_20260309.md) — Métriques & estimations

### Scripts & Outils

- 🐍 `detect_quiz_placeholders.py` — Détection avancée
- 🤖 `auto_enrich_quiz_api.py` — Enrichissement automatique
- 🪟 `enrichissement_auto.bat` — Menu interactif Windows

### Rapports

- 📄 `dev/reports/placeholders_detected.json` — Résultats scan
- 💾 `dev/backups/quiz_enrichment/` — Backups automatiques

---

## 🆘 Support

### Ordre de consultation

1. **Problème technique** → [README_AUTO_ENRICHMENT.md](README_AUTO_ENRICHMENT.md#-dépannage)
2. **Question workflow** → [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md)
3. **Question stratégie** → [RECAP_ENRICHMENT_20260309.md](RECAP_ENRICHMENT_20260309.md)
4. **Validation qualité** → `validate_quiz_quality.py` + [README_WORKFLOW_QUALITE.md](README_WORKFLOW_QUALITE.md)

---

## 📅 Historique

| Date | Version | Changements |
|------|---------|-------------|
| 09/03/2026 | 1.0.0 | Création scripts enrichissement automatique via API |
| 09/03/2026 | 1.0.0 | Documentation complète (README, QUICKSTART, RECAP) |
| 09/03/2026 | 1.0.0 | Scan initial 1476 quiz → 32 538 placeholders détectés |

---

**Dernière mise à jour** : 09/03/2026 11h15  
**Auteur** : MonCoachScolaire - Équipe Qualité Quiz  
**Licence** : Usage interne projet
