# 🎬 Migration : Colibri → Coach WebM

**Date** : 26 décembre 2025  
**Status** : ✅ COMPLÉTÉ

---

## 📋 Résumé de la Migration

Le système **Colibri** (mascotte statique) a été remplacé par le système **Coach WebM** (vidéos transparentes animées). Cette migration améliore significativement l'expérience utilisateur avec des vidéos réalistes et adaptées à différents scénarios.

---

## 🔄 Changements Effectués

### 1. **Suppression des Références Colibri**

#### footer.php (SUPPRIMÉ)
- ❌ `<link rel="stylesheet" href="colibri-mascot.css">`
- ❌ Configuration JavaScript pour Colibri
- ❌ `<script src="colibri-mascot.js"></script>`
- ✅ Remplacé par : `<!-- Coach WebM - Chargé automatiquement par dashboard et pages d'exercices/quiz -->`

#### interactive-exercises.js
- ❌ Fonction `showColibriMascotInFeedback()`
- ❌ Appels à `showColibriMascotInFeedback()` (2 occurrences)
- ✅ Remplacé par : Fonction `showCoachInFeedback()` qui déclenche `window.onExerciseComplete(percentage)`

### 2. **Fichiers Existants (Non Supprimés)**

Les fichiers Colibri restent dans le dossier pour référence historique :
- `assets/js/colibri-mascot.js` (peut être supprimé ultérieurement)
- `assets/css/colibri-mascot.css` (peut être supprimé ultérieurement)
- `includes/colibri_mascot.php` (peut être supprimé ultérieurement)

### 3. **Nouveau Système Coach WebM**

#### Fichiers Clés
- **`assets/js/coach-webm.js`** : Module coach avec scénarios et événements
- **`includes/coach_webm_include.php`** : HTML/CSS include (optionnel, inline sur dashboard)
- **`includes/coach_webm_events.php`** : Documentation des scénarios

#### Pages Intégrées
- ✅ `dashboard.php` : Affichage au chargement avec `showDailyGreeting()`
- ✅ `quiz.php` : Déclenche `onExerciseComplete()` après le quiz
- ✅ `exercices.php` : Déclenche `onExerciseComplete()` après l'exercice

#### Vidéos Disponibles
```
assets/img/coach/humain/
├── bonjour_bienvenue.webm (+ .mp4)
├── bravo.webm (+ .mp4)
├── championdumonde.webm (+ .mp4)
├── posture_positive_accueillante.webm (+ .mp4)
├── toituvasyarriver.webm (+ .mp4)
└── tresbien.webm (+ .mp4)
```

---

## 📊 Couverture des Cas d'Usage

### Colibri → Coach WebM Mapping

| Cas d'Usage | Colibri | Coach WebM | Scénario |
|---|---|---|---|
| **Bienvenue** | neutre | bonjour | Première visite du jour |
| **Bonne Réponse** | celebrate | bravo | 3 réponses correctes |
| **Mauvaise Réponse** | encourage | encouragement | Erreur commise |
| **Score 70-90%** | celebrate | tresbien | Bon résultat |
| **Score >90%** | celebrate | champion | Excellent résultat |
| **Niveau Complété** | celebration | fete | Badge ou niveau débloqué |
| **Pensée** | reflexion | (N/A) | Non implémenté |

### Améliorations

✅ **Vidéos transparentes** : Fond supprimé, meilleure intégration visuelle  
✅ **Scénarios réactifs** : Réaction immédiate et contextuelle  
✅ **localStorage** : Suivi des visites quotidiennes  
✅ **Authentification** : Uniquement pour utilisateurs connectés  
✅ **Responsive** : Adaptation automatique aux résolutions  
✅ **Performance** : WebM avec compression 95% vs MP4

---

## 🔗 Intégration dans les Pages

### dashboard.php
```javascript
// Au chargement
window.showDailyGreeting(); // Affiche "bonjour" si première visite du jour
```

### quiz.php & exercices.php
```javascript
// Après validation de réponse
if (typeof window.onCorrectAnswer === 'function') {
  window.onCorrectAnswer(); // 3x = "bravo"
}
if (typeof window.onIncorrectAnswer === 'function') {
  window.onIncorrectAnswer(); // "encouragement"
}

// Après score final
if (typeof window.onExerciseComplete === 'function') {
  window.onExerciseComplete(percentage); // >90% = "champion", 70-90% = "tresbien", etc.
}
```

### interactive-exercises.js
```javascript
// Avant : showColibriMascotInFeedback(feedbackDiv, score, total);
// Après : showCoachInFeedback(feedbackDiv, score, total);
```

---

## ⚙️ Configuration Technique

### MIME Types (.htaccess)
```apache
AddType video/webm .webm
AddType video/mp4 .mp4
Header set Cache-Control "max-age=2592000, public"
```

### Appels API Coach
```javascript
// Afficher un scénario
window.showCoach(action, duration);

// Événements
window.onCorrectAnswer();           // Bonne réponse
window.onIncorrectAnswer();         // Mauvaise réponse
window.onExerciseComplete(score);   // Fin d'exercice (0-100)
window.onLevelOrBadgeComplete();    // Niveau complété
window.showDailyGreeting();         // Accueil du jour
```

---

## 📱 Responsive Design

```css
/* Desktop : 250px */
.coach-overlay {
  width: 250px;
  bottom: 20px;
  right: 20px;
}

/* Mobile : 180px */
@media (max-width: 480px) {
  .coach-overlay {
    width: 180px;
    bottom: 10px;
    right: 10px;
  }
}
```

---

## 🧪 Validation

### ✅ Complété
- Coach visible sur dashboard.php
- Coach réagit aux scores de quiz
- Coach réagit aux exercices
- Vidéos WebM avec alpha channel
- localStorage pour visites quotidiennes
- Styles animation fluides
- Responsive design

### ⚠️ À Surveiller
- Performance sur connexions lentes (prévoir cache)
- Support WebM sur anciens navigateurs (fallback MP4)
- localStorage quota limitations

---

## 🗑️ Nettoyage Futur

Une fois validé, les fichiers Colibri peuvent être supprimés :
```
assets/js/colibri-mascot.js
assets/css/colibri-mascot.css
includes/colibri_mascot.php
docs/MASCOTTE-COLIBRI.md
docs/WORKFLOWS-MASCOTTE-COLIBRI.md
```

---

## 📚 Documentation Associée

- [Coach WebM Integration Guide](Guideintegrationfondtransparent.md)
- [Coach Events Reference](includes/coach_webm_events.php)
- [Coach Module Code](assets/js/coach-webm.js)
