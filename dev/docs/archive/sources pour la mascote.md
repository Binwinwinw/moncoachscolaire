# 🐦 Sources et Documentation - Mascotte Colibri

## 📋 Table des Matières

1. [Architecture Actuelle](#architecture-actuelle)
2. [Fichiers et Structure](#fichiers-et-structure)
3. [Intégration PHP](#intégration-php)
4. [Intégration JavaScript](#intégration-javascript)
5. [Gestion des Vidéos](#gestion-des-vidéos)
6. [Optimisation Vidéo](#optimisation-vidéo)
7. [Historique et Évolutions](#historique-et-évolutions)

---

## 🏗️ Architecture Actuelle

### Vue d'ensemble

La mascotte Colibri est un système complet de coaching visuel qui :
- S'affiche automatiquement pour les utilisateurs connectés
- Utilise des vidéos HTML5 pour l'animation continue
- S'adapte au niveau de l'élève (collège = cartoon, lycée = réaliste)
- Réagit aux actions de l'utilisateur (bonnes réponses, erreurs, etc.)

### Composants Principaux

1. **PHP** : `includes/colibri_mascot.php` - Fonctions de rendu
2. **JavaScript** : `assets/js/colibri-mascot.js` - Logique et animations
3. **CSS** : `assets/css/colibri-mascot.css` - Styles et animations
4. **Vidéos** : `assets/img/coach/` - Fichiers vidéo MP4/WebM

---

## 📁 Fichiers et Structure

### Structure Complète

```
moncoachscolaire/
├── includes/
│   └── colibri_mascot.php          # Fonctions PHP pour afficher la mascotte
│
├── assets/
│   ├── css/
│   │   └── colibri-mascot.css      # Styles CSS (animations, poses, bulles)
│   │
│   ├── js/
│   │   └── colibri-mascot.js       # Classe ColibriMascot (723 lignes)
│   │
│   └── img/
│       └── coach/
│           ├── colibri-cartoon_volant.mp4      # Vidéo cartoon (base)
│           ├── colibri-realiste_volant.mp4     # Vidéo réaliste (base)
│           ├── cartoonchampion.mp4             # Animation champion
│           ├── cartoondebutderxercice.mp4       # Début d'exercice
│           ├── cartoonreponsecorrecte.mp4       # Réponse correcte
│           ├── colibricartoonvolantbackground.mp4
│           ├── realistecelebration.mp4         # Célébration réaliste
│           ├── realisteencourager.mp4          # Encouragement réaliste
│           ├── realistefelicitations.mp4       # Félicitations réaliste
│           └── optimized/                      # Dossier des vidéos optimisées
│               ├── [video]_optimized.mp4       # MP4 optimisé (H.264, 720p)
│               └── [video]_optimized.webm     # WebM optimisé (VP9, 720p)
│
├── docs/
│   ├── MASCOTTE-COLIBRI.md         # Documentation complète d'intégration
│   └── SOLUTION-OPTIMISATION-VIDEOS.md  # Solution d'optimisation vidéo
│
└── footer.php                       # Intégration conditionnelle (ligne 139-171)
```

### Fichiers Clés

#### 1. `includes/colibri_mascot.php`

**Fonctions principales** :
- `renderColibriMascot($options)` : Affiche la mascotte avec options
- `renderGlobalColibriMascot($options)` : Mascotte globale (toutes pages)
- `renderColibriMascotVideo($options)` : Version vidéo HTML5
- `renderColibriInFeedback($type, $message)` : Dans les feedbacks d'exercices

**Options disponibles** :
```php
[
    'pose' => 'neutre|heureux|encourageant|celebration|reflexion',
    'size' => 'small|medium|large',
    'position' => 'inline|center|float',
    'message' => 'string',
    'imageType' => 'auto|cartoon|realiste'
]
```

#### 2. `assets/js/colibri-mascot.js`

**Classe principale** : `ColibriMascot`

**Propriétés importantes** :
- `videoElement` : Élément `<video>` HTML5
- `baseVideoSrc` : Chemin vidéo de base (volant)
- `successVideoSrc` : Chemin vidéo de succès
- `videoLoopCount` : Compteur de boucles (max 2)
- `pauseDuration` : 120000ms (2 minutes)

**Méthodes principales** :
- `init()` : Initialisation
- `setupVideoSources(basePath)` : Configuration des sources vidéo optimisées
- `playSuccessVideo()` : Jouer la vidéo de succès
- `setPose(pose, duration)` : Changer la pose
- `showMessage(message, duration)` : Afficher un message

**Détection automatique du niveau** :
```javascript
determineImageType() {
    // Collège : 6ème, 5ème, 4ème, 3ème → 'cartoon'
    // Lycée : Seconde, Première, Terminale, BAC → 'realiste'
}
```

#### 3. `assets/css/colibri-mascot.css`

**Classes principales** :
- `.colibri-mascot` : Container principal
- `.colibri-neutre|heureux|encourageant|celebration|reflexion` : Poses
- `.size-small|medium|large` : Tailles
- `.position-inline|center|float` : Positions

**Animations CSS** :
- `colibri-bounce` : Animation de rebond
- `colibri-float` : Animation de flottement
- `colibri-celebrate` : Animation de célébration
- `colibri-think` : Animation de réflexion

---

## 🔌 Intégration PHP

### Dans `footer.php` (Lignes 139-171)

```php
// Vérification de l'authentification
$has_valid_user_id = !empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
$has_logged_in_flag = !empty($_SESSION['logged_in']);
$is_authenticated = $has_valid_user_id && $has_logged_in_flag;
$is_demo_account = !empty($_SESSION['is_demo']);

$shouldShowMascot = $is_authenticated || $is_demo_account;

if ($shouldShowMascot):
    // Charger CSS et JS
    <link rel="stylesheet" href=".../assets/css/colibri-mascot.css">
    <script src=".../assets/js/colibri-mascot.js"></script>
    
    // Initialiser la mascotte globale
    <div class="colibri-mascot-global" data-colibri-global="true"></div>
endif;
```

### Utilisation dans les Pages

```php
require_once __DIR__ . '/includes/colibri_mascot.php';

// Mascotte basique
renderColibriMascot();

// Avec options
renderColibriMascot([
    'pose' => 'heureux',
    'size' => 'medium',
    'message' => 'Bravo ! 🎉'
]);

// Dans un feedback
renderColibriInFeedback('success', 'Excellent travail !');
```

---

## 💻 Intégration JavaScript

### Auto-initialisation

La mascotte s'initialise automatiquement via les attributs `data-colibri-*` :

```html
<div class="colibri-mascot-global" 
     data-colibri-global="true"
     data-colibri-size="medium"
     data-colibri-image="auto">
</div>
```

### Utilisation Programmatique

```javascript
// Créer une instance
const mascot = new ColibriMascot('.container', {
    pose: 'neutre',
    size: 'medium',
    imageType: 'auto'
});

// Changer la pose
mascot.setPose('celebration', 2000);

// Afficher un message
mascot.showMessage('Bravo ! 🎉', 3000);

// Réactions prédéfinies
mascot.celebrate();      // Célébration
mascot.encourage();      // Encouragement
mascot.think();          // Réflexion
```

### Intégration avec les Exercices

La mascotte réagit automatiquement aux événements des exercices :

```javascript
// Événements écoutés automatiquement
document.addEventListener('exercise:correct', function(e) {
    // Score : e.detail.score / e.detail.total
    // La mascotte réagit automatiquement
});
```

---

## 🎬 Gestion des Vidéos

### Structure des Vidéos

**Vidéos de base** (animation continue) :
- `colibri-cartoon_volant.mp4` : Volant cartoon (collège)
- `colibri-realiste_volant.mp4` : Volant réaliste (lycée)

**Vidéos d'événements** :
- `cartoonchampion.mp4` : Champion cartoon
- `cartoondebutderxercice.mp4` : Début d'exercice cartoon
- `cartoonreponsecorrecte.mp4` : Réponse correcte cartoon
- `realistecelebration.mp4` : Célébration réaliste
- `realisteencourager.mp4` : Encouragement réaliste
- `realistefelicitations.mp4` : Félicitations réaliste

### Système de Sources Multiples

La fonction `setupVideoSources()` crée plusieurs sources dans l'ordre de priorité :

1. **WebM optimisé** (dans `optimized/`) - Priorité maximale
2. **MP4 optimisé** (dans `optimized/`) - Fallback
3. **WebM original** - Fallback
4. **MP4 original** - Fallback ultime

```javascript
setupVideoSources(basePath) {
    // Créer les <source> tags dans l'ordre de priorité
    // Le navigateur choisit automatiquement la première disponible
}
```

### Gestion de la Lecture

**Boucle automatique** :
- Vidéo de base : 2 boucles maximum
- Pause : 2 minutes après 2 boucles
- Reprise automatique après pause

**Vidéo de succès** :
- Se joue lors des bonnes réponses
- Remplace temporairement la vidéo de base
- Revient à la vidéo de base après la fin

---

## 🎥 Optimisation Vidéo

### Problème Résolu

**Erreur initiale** : `Error initializing output stream - maybe incorrect bit_rate, rate, width or height`

**Cause** : Dimensions vidéo non divisibles par 2 (requis par H.264/VP9)

**Solution** : Utiliser `-2` dans le filtre `scale` de FFmpeg

### Scripts d'Optimisation

**Fichiers créés** :
- `tools/optimize_videos_robust.ps1` : Script robuste avec 3 tentatives
- `tools/optimize_videos.ps1` : Script standard
- `tools/analyze_videos.ps1` : Analyse des propriétés vidéo

**Commandes** :
```powershell
# Optimiser toutes les vidéos
.\optimize_videos_robust.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"

# Analyser les vidéos
.\analyze_videos.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"
```

**Résultats** :
- Réduction de taille : 80-95%
- Format : MP4 (H.264) et WebM (VP9)
- Résolution : 720p max
- Qualité : Visuellement acceptable

### Documentation

Voir `docs/SOLUTION-OPTIMISATION-VIDEOS.md` pour les détails complets.

---

## 📚 Historique et Évolutions

### Version Initiale (Structure Recommandée)

**HTML/PHP** :
```html
<div class="coach-container">
  <video id="coach-video" style="display:none;" muted>
    <source src="" type="video/mp4">
  </video>
</div>
```

**JavaScript Simple** :
```javascript
const videos = {
  college: {
    encourager: 'colibri-cartoon-encourager.mp4',
    feliciter: 'colibri-cartoon-feliciter.mp4',
    feter: 'colibri-cartoon-feter.mp4'
  },
  lycee: {
    encourager: 'colibri-realiste-encourager.mp4',
    feliciter: 'colibri-realiste-feliciter.mp4',
    feter: 'colibri-realiste-feter.mp4'
  }
};

function showCoach(niveau, action) {
  const video = document.getElementById('coach-video');
  const source = video.querySelector('source');
  source.src = videos[niveau][action];
  video.load();
  video.style.display = 'block';
  video.play();
  video.onended = () => {
    video.style.display = 'none';
  };
}
```

**CSS** :
```css
.coach-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1000;
  width: 200px;
}

#coach-video {
  width: 100%;
  height: auto;
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.2));
  animation: slideInUp 0.5s ease-out;
}
```

### Évolution Actuelle

**Améliorations apportées** :

1. **Système de classes JavaScript** :
   - Classe `ColibriMascot` complète (723 lignes)
   - Gestion automatique des vidéos
   - Système de poses et messages

2. **Optimisation vidéo** :
   - Support des vidéos optimisées (WebM/MP4)
   - Détection automatique du meilleur format
   - Réduction de 95% de la taille

3. **Intégration automatique** :
   - Affichage conditionnel selon authentification
   - Réaction automatique aux exercices
   - Support du compte démo

4. **Gestion avancée** :
   - Boucles limitées (2 max)
   - Pause automatique (2 minutes)
   - Vidéos de succès contextuelles

### Prochaines Étapes Possibles

- [ ] Création d'un sprite complet avec toutes les poses
- [ ] Personnalisation des messages par niveau
- [ ] Animations CSS plus sophistiquées
- [ ] Support de l'audio (optionnel)
- [ ] Statistiques d'utilisation de la mascotte

---

## 🔗 Références

### Documentation

- `docs/MASCOTTE-COLIBRI.md` : Guide d'intégration complet
- `docs/SOLUTION-OPTIMISATION-VIDEOS.md` : Solution d'optimisation vidéo

### Fichiers Sources

- `includes/colibri_mascot.php` : Fonctions PHP
- `assets/js/colibri-mascot.js` : Logique JavaScript
- `assets/css/colibri-mascot.css` : Styles CSS
- `footer.php` : Intégration conditionnelle

### Scripts Utilitaires

- `tools/optimize_videos_robust.ps1` : Optimisation vidéo
- `tools/analyze_videos.ps1` : Analyse vidéo
- `tools/extract_colibri_sprites.ps1` : Extraction sprites

---

## 📝 Notes Techniques

### Détection du Niveau

**Méthodes utilisées** :
1. `window.userLevel` (variable globale JavaScript)
2. Attributs `data-colibri-*` dans le HTML
3. Classes CSS sur le body
4. Variable PHP `$user_level`

**Mapping** :
- Collège (6ème, 5ème, 4ème, 3ème) → `cartoon`
- Lycée (Seconde, Première, Terminale, BAC) → `realiste`

### Gestion des Erreurs Vidéo

```javascript
videoElement.addEventListener('error', function(e) {
    // Gérer les erreurs de chargement
    // Fallback vers la vidéo suivante dans la liste
});
```

### Compatibilité Navigateurs

- **WebM VP9** : Chrome, Firefox, Edge (moderne)
- **MP4 H.264** : Tous les navigateurs (fallback universel)
- **Détection automatique** : `video.canPlayType()`

---

## ✅ Checklist d'Intégration

- [x] Fichiers PHP créés (`colibri_mascot.php`)
- [x] Fichiers JavaScript créés (`colibri-mascot.js`)
- [x] Fichiers CSS créés (`colibri-mascot.css`)
- [x] Vidéos optimisées (MP4 + WebM)
- [x] Intégration dans `footer.php`
- [x] Support compte démo
- [x] Détection automatique du niveau
- [x] Réactions aux exercices
- [x] Documentation complète

---

*Document archivé le : [Date actuelle]*
*Dernière mise à jour : Après optimisation vidéo et intégration complète*
