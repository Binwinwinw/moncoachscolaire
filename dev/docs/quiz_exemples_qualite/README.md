# Quiz Exemples Qualité Pédagogique

**Date création** : 07/03/2026  
**Objectif** : Fournir des modèles de quiz de qualité pour MonCoachScolaire

---

## 📁 Contenu du dossier

### Quiz améliorés (APRÈS réécriture pédagogique)

| Fichier | Niveau | Matière | Description |
|---------|--------|---------|-------------|
| `33_4eme_maths_ameliore.json` | 4ème | Mathématiques | Équations, Pythagore, fonctions, probabilités |
| `33_4eme_maths_ameliore_answers.json` | 4ème | Mathématiques | Corrections détaillées avec explications |
| `100_seconde_francais_ameliore.json` | Seconde | Français | Genres littéraires, versification, auteurs classiques |
| `100_seconde_francais_ameliore_answers.json` | Seconde | Français | Corrections enrichies contextualisées |
| `111_bac_espagnol_ameliore.json` | BAC | Espagnol | Conjugaison, grammaire, ser/estar, impératif |
| `111_bac_espagnol_ameliore_answers.json` | BAC | Espagnol | Explications grammaticales complètes |

---

## ✨ Améliorations apportées

### 1. Questions reformulées
**AVANT** (télégraphique) :
```
"Équation premier degré ax+b=0, solution...?"
```

**APRÈS** (pédagogique) :
```
"Pour résoudre une équation du premier degré de la forme ax + b = 0 
(avec a ≠ 0), quelle est la solution ?"
```

### 2. Corrections enrichies
**AVANT** (minimaliste) :
```
"Isoler variable."
```

**APRÈS** (explicative) :
```
"La bonne réponse est x = -b/a. Pour résoudre l'équation ax + b = 0, 
on isole x en soustrayant b des deux côtés (ax = -b), puis en divisant 
par a (x = -b/a). Par exemple, si 3x + 6 = 0, alors x = -6/3 = -2."
```

### 3. Notions documentées
Chaque quiz inclut désormais un bloc `exercisenotion` détaillé :
```json
{
  "notion": "Équations du premier degré",
  "description": "Résolution d'équations de type ax + b = 0 par isolation de la variable"
}
```

### 4. Alternatives de réponses
Pour les questions texte, plusieurs variantes sont acceptées :
```json
{
  "answer": "Victor Hugo",
  "alternatives": ["Hugo", "Victor HUGO", "V. Hugo"]
}
```

---

## 🎯 Comment utiliser ces exemples

### Pour créer un nouveau quiz
1. Copier la structure d'un quiz exemple du même niveau
2. Adapter les questions au thème ciblé
3. Respecter les principes du [TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md](../TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md)
4. Vérifier la checklist de validation

### Pour améliorer un quiz existant
1. Comparer avec l'exemple du même niveau
2. Reformuler les questions télégraphiques
3. Enrichir les corrections (minimum 2-3 phrases)
4. Ajouter des exemples concrets
5. Documenter les notions

### Pour générer en masse
1. Utiliser ces quiz comme templates pour `generate_quiz_bank.py`
2. Créer des variations thématiques (algèbre, géométrie, littérature...)
3. Maintenir le niveau de qualité pédagogique

---

## 📊 Différences clés par niveau

### 4ème (Collège)
- Formulations claires mais accessibles
- Exemples numériques concrets
- Corrections pédagogiques étape par étape
- Vocabulaire mathématique précis mais simple

### Seconde (Lycée)
- Contexte culturel et historique
- Références aux œuvres et auteurs
- Explications littéraires approfondies
- Nuances de sens et d'interprétation

### BAC (Préparation examen)
- Niveau de complexité grammaticale élevé
- Références croisées (variations linguistiques)
- Explications adaptées au niveau terminal
- Préparation aux exigences du baccalauréat

---

## 🔧 Checklist avant publication

Utiliser ces fichiers comme référence pour valider :

- [ ] Questions : phrases complètes, pas d'abréviations
- [ ] Corrections : 2-4 phrases d'explication minimum
- [ ] Choix QCM : 4 options équilibrées
- [ ] Notions : bloc `exercisenotion` renseigné
- [ ] Alternatives : variantes de réponses acceptées (texte)
- [ ] Ton : bienveillant et encourageant
- [ ] Niveau : cohérent avec référentiel EN

---

## 📚 Ressources liées

- [TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md](../TEMPLATE_QUIZ_QUALITE_PEDAGOGIQUE.md) - Standards et référentiels
- `src/data/quiz/` - Quiz actuels (à améliorer progressivement)
- `src/data/quiz_answers/` - Fichiers réponses actuels

---

## 🚀 Prochaines étapes

1. ✅ Créer template qualité pédagogique
2. ✅ Définir référentiels par niveau
3. ✅ Réécrire 3 quiz exemples
4. 🔄 Créer script validation automatique
5. ⏳ Appliquer progressivement aux 1550 quiz existants

---

**Maintenu par** : Copilot AI  
**Dernière mise à jour** : 07/03/2026
