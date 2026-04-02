# 🐦 Mascotte Colibri - Guide d'intégration

## Vue d'ensemble

La mascotte Colibri est un personnage coach interactif qui encourage les élèves lors de leurs exercices. Elle apparaît automatiquement dans les feedbacks d'exercices avec différentes poses selon les résultats.

## Fonctionnalités

- **5 poses différentes** : neutre, heureux, encourageant, célébrant, réflexion
- **Bulle de parole** : affiche des messages d'encouragement personnalisés
- **Animations fluides** : transitions et effets visuels selon le contexte
- **Intégration automatique** : s'affiche automatiquement dans les feedbacks d'exercices
- **Responsive** : s'adapte à toutes les tailles d'écran

## Structure des fichiers

```
assets/
├── css/
│   └── colibri-mascot.css      # Styles CSS pour la mascotte
├── js/
│   └── colibri-mascot.js       # JavaScript pour la logique de la mascotte
└── img/
    └── coach/
        ├── colibricartoon.jpg  # Image du colibri (utilisée comme sprite)
        ├── colibrirealiste.jpg # Version réaliste
        └── video-*.mp4         # Vidéos d'animation (alternative)

includes/
└── colibri_mascot.php          # Fonctions PHP pour afficher la mascotte
```

## Intégration dans les pages

### 1. Ajouter les fichiers CSS et JS

Dans vos pages PHP (ex: `pages/college/6eme/exercices-6eme.php`), ajoutez :

```php
<!-- CSS pour la mascotte -->
<link rel="stylesheet" href="<?php echo isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>/assets/css/colibri-mascot.css">

<!-- JavaScript pour la mascotte (à charger APRÈS interactive-exercises.js) -->
<script src="<?php echo isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>/assets/js/colibri-mascot.js"></script>
```

### 2. Intégration automatique

La mascotte s'intègre **automatiquement** avec le système d'exercices interactifs. Lorsqu'un élève vérifie ses réponses :

- ✅ **100% de bonnes réponses** → Pose "celebration" avec message de félicitation
- ⚠️ **50-99% de bonnes réponses** → Pose "encourageant" avec message d'encouragement
- ❌ **< 50% de bonnes réponses** → Pose "encourageant" avec message de motivation

## Utilisation en PHP

### Fonction `renderColibriMascot()`

Affiche la mascotte avec des options personnalisées :

```php
require_once __DIR__ . '/includes/colibri_mascot.php';

// Mascotte basique
renderColibriMascot();

// Mascotte avec options
renderColibriMascot([
    'pose' => 'heureux',
    'size' => 'medium',  // small, medium, large
    'position' => 'inline',  // inline, center, float
    'message' => 'Bravo pour ta persévérance ! 🎉',
    'containerClass' => 'my-custom-class'
]);
```

### Fonction `renderColibriInFeedback()`

Affiche la mascotte dans un feedback d'exercice :

```php
// Succès
renderColibriInFeedback('success', 'Excellent travail ! ⭐');

// Erreur
renderColibriInFeedback('error', 'Pas grave, réessaie ! 💪');

// Réflexion
renderColibriInFeedback('thinking', 'Réfléchis bien... 🤔');
```

### Fonction `renderColibriMascotVideo()`

Affiche la mascotte avec une vidéo HTML5 (alternative aux sprites) :

```php
renderColibriMascotVideo([
    'video' => 'cartoon',  // ou 'realiste'
    'autoplay' => true,
    'loop' => true,
    'muted' => true,
    'size' => 'medium'
]);
```

## Utilisation en JavaScript

### Créer une mascotte

```javascript
// Méthode 1 : Via la classe
const mascot = new ColibriMascot('.my-container', {
    pose: 'neutre',
    size: 'medium',
    position: 'inline',
    showSpeechBubble: true
});

// Méthode 2 : Via la fonction helper
const mascot = createColibriMascot('.my-container', {
    pose: 'heureux',
    size: 'small'
});
```

### Changer la pose

```javascript
// Pose permanente
mascot.setPose('heureux');

// Pose temporaire (revient à 'neutre' après 2 secondes)
mascot.setPose('celebration', 2000);
```

### Afficher un message

```javascript
// Message avec durée par défaut (3 secondes)
mascot.showMessage('Bravo ! Continue comme ça ! 🎉');

// Message avec durée personnalisée
mascot.showMessage('Excellent !', 5000);
```

### Méthodes de réaction

```javascript
// Célébrer (bonne réponse)
mascot.celebrate();

// Encourager (mauvaise réponse)
mascot.encourage();

// Réfléchir (attente)
mascot.think();

// Grande célébration (succès majeur)
mascot.majorCelebration();

// Encouragement aléatoire
mascot.showRandomEncouragement();
```

### Auto-initialisation avec attributs data

Vous pouvez initialiser automatiquement des mascottes avec des attributs HTML :

```html
<div data-colibri="true"
     data-colibri-pose="heureux"
     data-colibri-size="medium"
     data-colibri-position="center"
     data-colibri-message="Bienvenue ! 👋"
     data-colibri-duration="3000">
</div>
```

## Intégration avec les exercices

La mascotte est **automatiquement intégrée** dans les feedbacks d'exercices via `interactive-exercises.js`. 

### Événements personnalisés

Vous pouvez écouter les événements de feedback :

```javascript
// Exercice réussi
document.addEventListener('exercise:correct', function(e) {
    console.log('Score:', e.detail.score, '/', e.detail.total);
    // Faire quelque chose...
});

// Exercice échoué
document.addEventListener('exercise:incorrect', function(e) {
    console.log('Score:', e.detail.score, '/', e.detail.total);
    // Faire quelque chose...
});

// Exercice complété
document.addEventListener('exercise:completed', function(e) {
    console.log('Exercice terminé !');
    // Faire quelque chose...
});
```

## Styles CSS personnalisés

### Tailles disponibles

- `.size-small` : 120px × 120px
- `.size-medium` : 200px × 200px (par défaut)
- `.size-large` : 300px × 300px

### Positions disponibles

- `.position-inline` : Affichage inline (par défaut)
- `.position-center` : Centré dans son conteneur
- `.position-float` : Flottant en bas à droite (fixed)

### Poses disponibles

- `.colibri-neutre` : Pose neutre
- `.colibri-heureux` : Pose joyeuse avec animation bounce
- `.colibri-encourageant` : Pose encourageante avec animation float
- `.colibri-celebration` : Pose de célébration avec animation
- `.colibri-reflexion` : Pose de réflexion avec animation douce

### États contextuels

- `.state-success` : État de succès (brightness augmenté)
- `.state-error` : État d'erreur (brightness réduit)
- `.state-thinking` : État de réflexion

## Personnalisation avancée

### Créer un sprite personnalisé

Si vous voulez créer votre propre sprite avec les 5 poses :

1. Créez une image avec 5 poses horizontalement (200px × 200px chacune)
2. Sauvegardez-la dans `assets/img/coach/sprites-colibri-cartoon.png`
3. La mascotte utilisera automatiquement le sprite si disponible

### Ajouter des messages personnalisés

Modifiez les tableaux de messages dans `colibri-mascot.js` :

```javascript
// Dans la méthode celebrate()
const messages = [
    'Bravo ! 🎉',
    'Excellent travail ! ⭐',
    'Ton message personnalisé ! 💪'
];
```

## Responsive

La mascotte s'adapte automatiquement sur mobile :

- Taille réduite sur écrans < 768px
- Bulle de parole ajustée
- Animations optimisées pour les performances

## Prochaines étapes

1. ✅ **Sprites CSS/JS** - Implémenté
2. ✅ **Intégration automatique** - Implémenté
3. ⏳ **Création du sprite complet** - À faire (actuellement utilise colibricartoon.jpg)
4. ⏳ **Vidéos HTML5** - Disponibles mais pas encore intégrées par défaut
5. ⏳ **Personnalisation par niveau** - Messages différents selon le niveau de l'élève

## Notes techniques

- La mascotte utilise `background-position` pour les sprites
- Les animations sont basées sur CSS `@keyframes`
- Les événements utilisent l'API CustomEvent du DOM
- Compatible avec tous les navigateurs modernes (IE11+)

## Support

Pour toute question ou problème, consultez les logs JavaScript dans la console du navigateur.

