# Workflow Qualité Quiz — MonCoachScolaire

Documentation des workflows automatisés de génération et validation qualité des quiz diagnostics.

**Date création** : 08/03/2026  
**Dernière MAJ** : 09/03/2026

---

## 🆕 NOUVEAUTÉ : Enrichissement Automatique via API (09/03/2026)

**Scripts ajoutés** :

- `detect_quiz_placeholders.py` : Détection avancée des placeholders (CRITICAL → LOW)
- `auto_enrich_quiz_api.py` : Enrichissement automatique via Wikipedia/Wiktionary
- `enrichissement_auto.bat` : Menu interactif Windows

**Documentation dédiée** :

- 📖 **Guide complet** : [README_AUTO_ENRICHMENT.md](README_AUTO_ENRICHMENT.md)
- 🚀 **Démarrage rapide** : [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md)

**Commande rapide** :

```bash
# Menu interactif (recommandé pour débutants)
dev\tools\quiz\enrichissement_auto.bat

# Ou commande directe
.venv\Scripts\python.exe dev/tools/quiz/detect_quiz_placeholders.py
```

**Résultats scan initial (1476 quiz)** :

- 🔴 15 000 placeholders CRITICAL (generic_template)
- 🟠 7 103 placeholders HIGH (incomplete_sentence)
- 🟡 10 435 placeholders MEDIUM (too_short_correction)

➡️ **Pour enrichir automatiquement les placeholders, voir** [QUICKSTART_ENRICHMENT.md](QUICKSTART_ENRICHMENT.md)

---

## 📋 Vue d'ensemble

Le workflow qualité quiz combine :

1. **Génération/Enrichissement** : Création ou amélioration des fichiers quiz JSON
2. **Contrôle sentinelle aléatoire post-génération** : Vérification immédiate d'une paire quiz/answers générée
3. **Validation automatique** : Analyse qualité pédagogique selon template
4. **Seuils de blocage** : Empêche publication si qualité insuffisante

### Contrôle sentinelle post-génération

Après chaque génération d'un script `generate_<niveau>_<matiere>.py`, le workflow doit contrôler automatiquement une sentinelle aléatoire parmi les quiz générés.

Vérifications minimales :

- le fichier `quiz/<id>.json` existe,
- le fichier `quiz_answers/<id>.json` existe,
- le nombre de questions est identique au nombre de réponses,
- les types runtime restent dans `qcm`, `vrai-faux`, `open`.

Sortie attendue :

```bash
[sentinel] OK quiz=1004 questions=8 answers=8
```

### Métriques qualité

Les quiz sont évalués selon :

- **Questions complètes** : ≥25 caractères, formulation claire
- **Corrections détaillées** : 2-4 phrases, ≥80 caractères
- **Notions documentées** : Champ `exercisenotion[]` renseigné
- **Métadonnées** : Niveau, sujet, description présents

### Problèmes prioritaires

- **HIGH (Haute)** : Questions télégraphiques, corrections absentes/courtes
- **MEDIUM (Moyenne)** : Notions manquantes, métadonnées incomplètes
- **LOW (Basse)** : Améliorations mineures (formulation, détails)

---

## 🚀 Scripts NPM disponibles

### Workflow complet (avec validation intégrée)

```bash
# Enrichir 50 quiz progressifs + validation qualité
npm run quiz:workflow:enrich

# Continuer enrichissement à partir de l'ID 192
npm run quiz:workflow:enrich:continue

# Générer quiz bank v1 + validation qualité
npm run quiz:workflow:generate

# Harmoniser métadonnées + validation qualité
npm run quiz:workflow:harmonize
```

### Validation seule (sans enrichissement)

```bash
# Valider qualité et afficher résumé terminal
npm run quiz:quality

# Valider qualité et générer rapport Markdown
npm run quiz:quality:report
```

### Génération/Harmonisation sans validation

```bash
# Générer quiz bank v1 (dry-run)
npm run quiz:generate:v1

# Générer quiz bank v1 (apply)
npm run quiz:generate:v1:apply

# Harmoniser métadonnées (dry-run)
npm run quiz:harmonize:v1

# Harmoniser métadonnées (apply)
npm run quiz:harmonize:v1:apply
```

---

## 🛠️ Script Python workflow (avancé)

Le script `dev/tools/quiz/quiz_quality_workflow.py` permet un contrôle fin des seuils et options.

### Usage de base

```bash
# Enrichissement progressif avec seuils par défaut
python dev/tools/quiz/quiz_quality_workflow.py enrich --batch-size 50

# Enrichissement à partir de l'ID 192
python dev/tools/quiz/quiz_quality_workflow.py enrich --batch-size 50 --start-id 192

# Génération quiz bank avec validation
python dev/tools/quiz/quiz_quality_workflow.py generate

# Harmonisation métadonnées avec validation
python dev/tools/quiz/quiz_quality_workflow.py harmonize
```

### Seuils personnalisés

```bash
# Seuil haute priorité: max 20% (défaut: 30%)
python dev/tools/quiz/quiz_quality_workflow.py enrich \
  --batch-size 50 \
  --high-max-percent 20

# Seuil moyenne priorité: max 40% (défaut: 50%)
python dev/tools/quiz/quiz_quality_workflow.py enrich \
  --batch-size 50 \
  --medium-max-percent 40

# Mode avertissement sans blocage
python dev/tools/quiz/quiz_quality_workflow.py enrich \
  --batch-size 50 \
  --no-block
```

### Exit codes

- **0** : Succès (qualité acceptable)
- **1** : Échec (seuils qualité non respectés)
- **2** : Erreur exécution (dépendance manquante, fichier introuvable)

---

## 📊 Métriques et rapports

### Rapport qualité

Généré automatiquement après chaque workflow :

**Fichier** : `dev/reports/quiz_quality_report.md`

**Contenu** :

- Nombre total de quiz analysés
- Problèmes détectés par priorité (HIGH, MEDIUM, LOW)
- Liste détaillée par quiz (ID, problèmes, recommandations)

### Logs enrichissement

Chaque enrichissement génère un log JSON :

**Fichiers** :

- `dev/reports/enrich_priority_quizzes_log.json` (top 8 manuels)
- `dev/reports/enrich_progressive_batch_{start}_{end}.json` (lots progressifs)

**Contenu** :

- Quiz enrichis (ID, sujet, niveau, date)
- Statistiques (nombre de quiz, distribution par niveau/sujet)
- Erreurs éventuelles

---

## 🎯 Cas d'usage courants

### 1. Enrichir les 1450 quiz restants

**Objectif** : Améliorer qualité par lots de 50, avec validation après chaque lot.

```bash
# Lot 3 (IDs 192-241)
npm run quiz:workflow:enrich:continue

# Lot 4 (IDs 242-291)
python dev/tools/quiz/quiz_quality_workflow.py enrich --batch-size 50 --start-id 242

# Lot 5 (IDs 292-341)
python dev/tools/quiz/quiz_quality_workflow.py enrich --batch-size 50 --start-id 292

# ... continuer jusqu'à épuisement
```

**Avantage** : Validation après chaque lot permet ajustement stratégie en temps réel.

### 2. Régénérer quiz bank avec validation

**Objectif** : Régénérer tous les quiz depuis config, puis valider qualité.

```bash
# Workflow complet
npm run quiz:workflow:generate

# Si seuils dépassés, inspecter rapport
cat dev/reports/quiz_quality_report.md

# Réenrichir quiz problématiques manuellement
# Puis relancer validation
npm run quiz:quality:report
```

### 3. Harmoniser métadonnées après import

**Objectif** : Normaliser métadonnées quiz importés, valider qualité.

```bash
# Workflow complet
npm run quiz:workflow:harmonize

# Si avertissements, consulter rapport
cat dev/reports/quiz_quality_report.md
```

### 4. Valider avant commit Git

**Objectif** : S'assurer qualité minimale avant commit fichiers quiz.

```bash
# Validation rapide (affichage terminal)
npm run quiz:quality

# Si problèmes critiques, enrichir
npm run quiz:workflow:enrich

# Re-valider
npm run quiz:quality
```

---

## 🔧 Configuration

### Seuils par défaut

Définis dans `quiz_quality_workflow.py` :

```python
--high-max-percent 30.0      # Max 30% quiz avec problèmes haute priorité
--medium-max-percent 50.0    # Max 50% quiz avec problèmes moyenne priorité
```

### Template qualité

Référence : `dev/docs/TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md`

**Standards** :

- Questions : ≥25 caractères, contexte clair
- Corrections : 2-4 phrases, explication pédagogique
- Référentiels Education Nationale par niveau

### Fichiers quiz

**Emplacement** :

- Quiz : `src/data/quiz/*.json`
- Réponses : `src/data/quiz_answers/*.json`

**Format** : JSON UTF-8, structure `contents` + `quiz` + `exercisenotion`

---

## 🧪 Pipeline régénération UTF-8 (quiz_packs)

**Ajout du 08/03/2026 :** Pipeline stable pour régénérer des paires `quiz` + `quiz_answers`
dans `src/data/quiz_packs/` sans corruption d'encodage.

### Objectif

- Produire des fichiers de travail `enriched_[id]_quiz.json` et `enriched_[id]_answers.json`
- Conserver la structure métier existante
- Garantir un encodage UTF-8 correct (accents préservés)

### Script CLI

Fichier : `dev/tools/quiz/generate_quiz_packs.py`

Commandes:

```bash
# Exemple lot initial
python dev/tools/quiz/generate_quiz_packs.py --start-id 5 --end-id 10

# Lot suivant
python dev/tools/quiz/generate_quiz_packs.py --start-id 11 --end-id 50
```

### Garanties du pipeline

- Lecture explicite UTF-8 des fichiers sources
- Ecriture UTF-8 (`ensure_ascii=False`) pour conserver les accents
- Synchronisation `question_count` avec le nombre réel de questions
- Normalisation des `id` questions (`1..N`)
- Normalisation des réponses (`index`, `question_id`, `type`)

### Vérification post-génération

```bash
# Détection motifs de mojibake fréquents
rg "Ã|Â|â€™|â€œ|â€|ï»¿" src/data/quiz_packs/*.json
```

Si la commande ne retourne rien, l'encodage est propre.

---

## 📈 Suivi progression

### Métriques cibles

**Objectif 31/03/2026** :

- ✅ Problèmes HIGH : <15% des quiz (actuellement ~30%)
- ✅ Problèmes MEDIUM : <30% des quiz (actuellement ~43%)
- ✅ 1559 quiz enrichis (actuellement 108/1559 = 6.9%)

### Progression actuelle (08/03/2026)

| Étape     | Quiz enrichis | Problèmes totaux | HIGH      | MEDIUM   | LOW      |
| --------- | ------------- | ---------------- | --------- | -------- | -------- |
| Initial   | 0             | 31577            | 20719     | 7103     | 3755     |
| Top 8     | 8             | 31551            | 20711     | 7087     | 3753     |
| Lot 1     | 58            | 30701            | 20033     | 6878     | 3790     |
| **Lot 2** | **108**       | **29868**        | **19333** | **6695** | **3840** |

**Amélioration totale** : -1709 problèmes (-5.4%)

---

## ⚡ Prochaines étapes

1. **[En cours]** Enrichir lots 3-31 (~1450 quiz restants)
   - Script : `npm run quiz:workflow:enrich:continue`
   - Fréquence : 1-2 lots/jour (validation après chaque)

2. **[Pending]** Intégrer validation au pre-commit hook Git
   - Script : `.git/hooks/pre-commit` appelle `npm run quiz:quality`
   - Bloquer commit si >40% problèmes HIGH

3. **[Pending]** Feedback pédagogique adapté niveau élève
   - API corrections personnalisées selon résultats
   - Référentiels Education Nationale dans feedback
   - Ton adapté (encourageant, bienveillant)

---

## 🆘 Dépannage

### Erreur "Python not found"

**Solution** :

```bash
# Vérifier Python installé
python --version

# Installer Python 3.8+ si absent
# https://www.python.org/downloads/
```

### Erreur "UnicodeEncodeError" (Windows)

**Cause** : Emojis Unicode dans terminal Windows (cp1252).

**Solution** : Déjà corrigée dans `quiz_quality_workflow.py` (ASCII au lieu d'emojis).

### Seuils dépassés systématiquement

**Cause** : Quiz nombreux avec qualité insuffisante.

**Solutions** :

1. Enrichir par lots progressifs (stratégie actuelle)
2. Ajuster seuils temporairement : `--high-max-percent 40`
3. Utiliser `--no-block` pour forcer passage (déconseillé)

### Rapport qualité vide ou incomplet

**Cause** : Fichiers quiz malformés (JSON invalide).

**Solution** :

```bash
# Valider JSON quiz
python -m json.tool src/data/quiz/quiz_123.json

# Si erreur, corriger manuellement ou régénérer
npm run quiz:generate:v1:apply
```

---

## 📚 Références

- **Template qualité** : [dev/docs/TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md](../../docs/TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md)
- **Référentiels EN** : [dev/docs/quiz_exemples_qualite/](../../docs/quiz_exemples_qualite/)
- **Synthèse lot qualité** : [dev/docs/SYNTHESE_LOT_QUALITE_PEDAGOGIQUE.md](../../docs/SYNTHESE_LOT_QUALITE_PEDAGOGIQUE.md)
- **Rapport qualité** : [dev/reports/quiz_quality_report.md](../../reports/quiz_quality_report.md)
- **Journal reprise** : [dev/JOURNAL_REPRISE.md](../../JOURNAL_REPRISE.md)

---

**Maintenu par** : GitHub Copilot  
**Contact** : Voir [README.md](../../../README.md)
