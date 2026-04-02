# Workflows de la Mascotte Colibri

## Vue d'ensemble

La mascotte Colibri (`assets/js/colibri-mascot.js`) est un système interactif qui anime un colibri pour encourager et guider les élèves. Elle fonctionne avec des vidéos animées et réagit aux actions de l'utilisateur.

---

## 1. Workflow d'Initialisation

### 1.1 Initialisation Automatique (Auto-initialisation)

**Déclencheur** : Au chargement du DOM (`DOMContentLoaded`)

**Processus** :
1. Recherche tous les éléments avec `data-colibri="true"`
2. Pour chaque élément trouvé :
   - Lit les attributs `data-colibri-pose`, `data-colibri-size`, `data-colibri-position`, `data-colibri-message`
   - Crée une instance `ColibriMascot`
   - Affiche un message si `data-colibri-message` est défini
   - Stocke l'instance dans `container.colibriMascot`

**Exemple HTML** :
```html
<div data-colibri="true" 
     data-colibri-pose="heureux" 
     data-colibri-size="medium"
     data-colibri-message="Bonjour !">
</div>
```

### 1.2 Initialisation Manuelle

**Méthode 1** : Via la classe
```javascript
const mascot = new ColibriMascot(container, {
    pose: 'neutre',
    size: 'medium',
    position: 'inline',
    showSpeechBubble: true
});
```

**Méthode 2** : Via la fonction helper
```javascript
const mascot = window.createColibriMascot(container, options);
```

### 1.3 Initialisation de la Mascotte Globale

**Déclencheur** : Automatique au chargement de la page

**Processus** :
1. Vérifie si `.colibri-mascot-global` existe
2. Si non, crée le container
3. Crée une instance avec `position: 'float'` (mascotte qui vole)
4. Stocke l'instance dans `globalContainer.colibriMascot`
5. Animation initiale : pose "encourageant" pendant 2 secondes, puis "neutre"

**Caractéristiques** :
- Taille : `medium` (120px)
- Position : flottante sur la page
- Visible sur toutes les pages (si utilisateur connecté)

---

## 2. Workflow de Gestion Vidéo

### 2.1 Cycle de Lecture Vidéo

**États** :
- **Lecture** : Vidéo joue en boucle
- **Pause automatique** : Après 2 boucles complètes
- **Reprise** : Après 2 minutes de pause

**Processus détaillé** :

1. **Démarrage initial** (`startVideoLoop()`)
   - Remet la vidéo à `currentTime = 0`
   - Appelle `videoElement.play()`
   - Gère la promesse pour éviter les erreurs DOMException

2. **Fin de boucle** (`handleVideoEnded()`)
   - Incrémente `videoLoopCount`
   - Si `videoLoopCount >= 2` :
     - Appelle `pauseVideo()`
     - Programme la reprise avec `scheduleResume()` (après 2 minutes)
   - Sinon :
     - Relance `startVideoLoop()`

3. **Pause** (`pauseVideo()`)
   - Met `isPaused = true`
   - Vérifie que la vidéo n'est pas déjà en pause avant d'appeler `pause()`
   - La dernière frame reste visible

4. **Reprise** (`resumeVideo()`)
   - Met `isPaused = false`
   - Réinitialise `videoLoopCount = 0`
   - Relance `startVideoLoop()`

### 2.2 Interruption de Pause

**Déclencheur** : Interaction utilisateur (exercice complété, bonne réponse, etc.)

**Méthode** : `interruptPause()`

**Processus** :
1. Annule le timeout de reprise programmée
2. Si la vidéo est en pause, reprend immédiatement
3. Réinitialise le compteur de boucles

**Utilisé dans** :
- `celebrate()` - Bonne réponse
- `encourage()` - Mauvaise réponse
- `majorCelebration()` - Succès majeur

### 2.3 Vidéo de Succès

**Déclencheur** : `playSuccessVideo()` appelé lors de célébrations

**Processus** :
1. Sauvegarde la source vidéo actuelle
2. Retire temporairement l'event listener `ended`
3. Change la source vers `successVideoSrc`
4. Remet `currentTime = 0`
5. Lance la lecture
6. À la fin de la vidéo de succès :
   - Revient à `baseVideoSrc`
   - Réinitialise le compteur de boucles
   - Remet l'event listener
   - Relance le cycle normal

**Vidéos utilisées** :
- **Collège** : `colibri-cartoon_volant.mp4` (animation spéciale)
- **Lycée** : `colibri-realiste_volant.mp4` (même vidéo que la base)

---

## 3. Workflow de Réactions aux Événements

### 3.1 Réaction : Bonne Réponse (`celebrate()`)

**Déclencheur** :
- Événement `exercise:correct`
- Feedback avec ✅ ou "Correct"
- Appel manuel `mascot.celebrate()`

**Actions** :
1. Interrompt la pause si active (`interruptPause()`)
2. Change la pose vers `heureux` (durée : 2.5 secondes)
3. Joue la vidéo de succès (`playSuccessVideo()`)
4. Affiche un message aléatoire parmi :
   - "Bravo ! 🎉"
   - "Excellent travail ! ⭐"
   - "Tu es sur la bonne voie ! 💪"
   - "Superbe ! Continue comme ça ! 🚀"
   - "Parfait ! Tu progresses ! ✨"
   - "Formidable ! 👏"
   - "Génial ! 🎊"
   - "Tu es doué(e) ! 🌟"

### 3.2 Réaction : Mauvaise Réponse (`encourage()`)

**Déclencheur** :
- Événement `exercise:incorrect`
- Feedback avec ❌ ou "Incorrect"
- Appel manuel `mascot.encourage()`

**Actions** :
1. Interrompt la pause si active (`interruptPause()`)
2. Change la pose vers `encourageant` (durée : 2.5 secondes)
3. Affiche un message encourageant aléatoire parmi :
   - "Pas grave, réessaie ! 💪"
   - "Tu vas y arriver ! 🎯"
   - "Courage, tu progresses ! 🌟"
   - "Continue, tu es presque là ! 🔥"
   - "N'abandonne pas ! 💎"
   - "Chaque erreur fait progresser ! 📈"
   - "Tu peux le faire ! 💪"
   - "Persévère, c'est la clé ! 🔑"
   - "C'est en essayant qu'on réussit ! 🎯"
   - "Tu apprends de chaque erreur ! 📚"
   - "Ne te décourage pas ! 💪"
   - "Tu progresses à chaque essai ! ⬆️"

**Note** : Toujours encourageant, jamais négatif

### 3.3 Réaction : Succès Majeur (`majorCelebration()`)

**Déclencheur** :
- Événement `exercise:completed`
- Exercice complété
- Badge obtenu
- Appel manuel `mascot.majorCelebration()`

**Actions** :
1. Interrompt la pause si active (`interruptPause()`)
2. Change la pose vers `celebration` (durée : 3 secondes)
3. Joue la vidéo de succès (`playSuccessVideo()`)
4. Affiche un message de célébration majeur :
   - "🎊 FÉLICITATIONS ! 🎊"
   - "🌟 TU ES GÉNIAL(E) ! 🌟"
   - "🏆 EXCELLENT ! 🏆"
   - "💎 BRAVO CHAMPION(NE) ! 💎"

### 3.4 Réaction : Réflexion (`think()`)

**Déclencheur** : Appel manuel `mascot.think()`

**Actions** :
1. Change la pose vers `reflexion` (permanente, durée = 0)
2. Affiche un message de réflexion :
   - "Réfléchis bien... 🤔"
   - "Tu peux le faire ! 💭"
   - "Prends ton temps... ⏱️"
   - "Concentre-toi... 🎯"

---

## 4. Workflow d'Interaction Utilisateur

### 4.1 Clic sur la Mascotte

**Déclencheur** : Clic sur l'élément `.colibri-mascot`

**Actions simultanées** :
1. **Morph visuel** (`cycleSpriteMorph()`)
   - Change l'overlay sprite vers la pose suivante
   - Effet de fondu (opacity 0 → 1 → 0)
   - Cycle entre : neutre, heureux, encourageant, celebration, reflexion
   - Dossier sprite selon le type : `cartoon` ou `realiste`

2. **Message d'encouragement** (`showRandomEncouragement()`)
   - Change la pose vers `encourageant` (1.5 secondes)
   - Affiche un message aléatoire :
     - "Tu es capable ! 💪"
     - "Continue comme ça ! 🌟"
     - "Je crois en toi ! ⭐"
     - "Chaque erreur te fait progresser ! 📈"
     - "Tu es un champion ! 🏆"

---

## 5. Workflow d'Intégration avec les Exercices

### 5.1 Détection Automatique des Feedbacks

**Méthode** : `integrateWithExerciseFeedback()`

**Processus** :
1. Crée un `MutationObserver` qui surveille le DOM
2. Détecte l'ajout de nouveaux éléments avec classes :
   - `.qcm-feedback`
   - `.math-feedback`
   - `.conjugation-feedback`
3. Pour chaque feedback détecté :
   - Analyse le contenu pour déterminer si correct/incorrect
   - Trouve ou crée un container de mascotte
   - Appelle `celebrate()` ou `encourage()` selon le résultat

### 5.2 Événements Personnalisés

**Événements écoutés** :

1. **`exercise:correct`**
   - Déclenche : `celebrate()` sur la mascotte globale
   - Payload : `{ score, total }`

2. **`exercise:incorrect`**
   - Déclenche : `encourage()` sur la mascotte globale
   - Payload : `{ score, total }`

3. **`exercise:completed`**
   - Déclenche : `majorCelebration()` sur la mascotte globale
   - Payload : `{ score, total }`

**Émission** : Par `interactive-exercises.js` lors de la vérification des réponses

---

## 6. Workflow de Détection du Type d'Image

### 6.1 Détermination Automatique (`determineImageType()`)

**Priorité de détection** :

1. **Option explicite** : Si `options.imageType !== 'auto'`, utilise cette valeur

2. **`window.userLevel`** :
   - Collège : `['6ème', '6eme', '5ème', '5eme', '4ème', '4eme', '3ème', '3eme']` → `cartoon`
   - Lycée : `['seconde', 'première', 'premiere', 'terminale', 'bac']` → `realiste`

3. **Classes CSS du body** :
   - `.college` ou `data-level-type="college"` → `cartoon`
   - `.lycee` ou `data-level-type="lycee"` → `realiste`

4. **URL** :
   - Contient `/college/`, `/6eme`, `/5eme`, `/4eme`, `/3eme` → `cartoon`
   - Contient `/lycee/`, `/seconde`, `/premiere`, `/terminale`, `/bac` → `realiste`

5. **Texte de la page** :
   - Mots-clés lycée : `['Seconde', 'Première', 'Terminale', 'BAC', 'Lycée']` → `realiste`
   - Mots-clés collège : `['6ème', '5ème', '4ème', '3ème', 'Collège']` → `cartoon`

6. **Défaut** : `cartoon` (collège)

### 6.2 Fichiers Vidéo Utilisés

**Collège (cartoon)** :
- Base : `colibricartoonvolantbackground.mp4`
- Succès : `colibri-cartoon_volant.mp4`

**Lycée (realiste)** :
- Base : `colibri-realiste_volant.mp4`
- Succès : `colibri-realiste_volant.mp4` (même fichier)

---

## 7. Workflow de Gestion des Messages

### 7.1 Affichage de Message (`showMessage()`)

**Processus** :
1. Crée la bulle de parole si elle n'existe pas
2. Définit le texte du message
3. Ajoute la classe `visible` pour l'animation CSS
4. Si `autoHideSpeechBubble = true` :
   - Programme le masquage après `speechBubbleDuration` (défaut : 3000ms)
   - Ou après la durée personnalisée fournie

### 7.2 Masquage de Message (`hideMessage()`)

**Processus** :
1. Retire la classe `visible`
2. L'animation CSS gère le fondu de sortie

---

## 8. Workflow de Gestion des Poses

### 8.1 Changement de Pose (`setPose()`)

**Processus** :
1. Valide la pose (doit être dans : `neutre`, `heureux`, `encourageant`, `celebration`, `reflexion`)
2. Retire l'ancienne classe CSS `colibri-{anciennePose}`
3. Ajoute la nouvelle classe CSS `colibri-{nouvellePose}`
4. Met à jour `currentPose`
5. Si `duration > 0` :
   - Programme le retour à `neutre` après la durée
   - Annule tout timeout précédent

**Note** : La vidéo reste toujours visible, les poses servent pour les animations CSS et les messages

---

## 9. Diagramme de Flux Principal

```
[Chargement Page]
    ↓
[DOMContentLoaded]
    ↓
[Auto-initialisation]
    ├─→ [Mascottes avec data-colibri]
    └─→ [Mascotte Globale]
            ↓
    [Détermination Type Image]
            ↓
    [Création Vidéo]
            ↓
    [startVideoLoop()]
            ↓
    [Lecture Vidéo]
            ↓
    [Fin Boucle] → [Compteur++]
            ↓
    [Compteur >= 2 ?]
        ├─ OUI → [pauseVideo()] → [scheduleResume()] → [Attente 2 min] → [resumeVideo()]
        └─ NON → [startVideoLoop()]
```

---

## 10. Diagramme de Flux des Réactions

```
[Événement Exercice]
    ↓
[interruptPause()] → [Reprend vidéo si en pause]
    ↓
[Type d'événement ?]
    ├─→ [exercise:correct] → [celebrate()]
    │       ├─→ [setPose('heureux')]
    │       ├─→ [playSuccessVideo()]
    │       └─→ [showMessage(encouragement)]
    │
    ├─→ [exercise:incorrect] → [encourage()]
    │       ├─→ [setPose('encourageant')]
    │       └─→ [showMessage(encouragement)]
    │
    └─→ [exercise:completed] → [majorCelebration()]
            ├─→ [setPose('celebration')]
            ├─→ [playSuccessVideo()]
            └─→ [showMessage(célébration)]
```

---

## 11. Points d'Intégration

### 11.1 Avec `interactive-exercises.js`

- Émission d'événements : `exercise:correct`, `exercise:incorrect`, `exercise:completed`
- Création automatique de mascottes dans les feedbacks
- Réactions automatiques aux résultats

### 11.2 Avec PHP (`includes/colibri_mascot.php`)

- Fonction `renderColibriMascot()` : Génère le HTML avec attributs `data-colibri`
- Fonction `renderGlobalColibriMascot()` : Génère le container de la mascotte globale
- Détection automatique du type d'image selon `$user_level`

### 11.3 Avec le Footer (`footer.php`)

- Chargement conditionnel du script (seulement si utilisateur connecté ou compte démo)
- Définition de `window.baseUrl` et `window.userLevel` pour le JavaScript

---

## 12. Configuration et Options

### 12.1 Options du Constructeur

```javascript
{
    pose: 'neutre',                    // Pose initiale
    size: 'medium',                    // Taille : 'small', 'medium', 'large'
    position: 'inline',                // Position : 'inline', 'center', 'float'
    showSpeechBubble: true,            // Afficher la bulle de parole
    autoHideSpeechBubble: true,        // Masquer automatiquement
    speechBubbleDuration: 3000,       // Durée d'affichage (ms)
    imageType: 'auto'                  // 'auto', 'cartoon', 'realiste'
}
```

### 12.2 Paramètres Vidéo

- `maxLoops`: 2 (nombre de boucles avant pause)
- `pauseDuration`: 120000 (2 minutes en ms)
- `baseVideoSrc`: Vidéo de base selon le type
- `successVideoSrc`: Vidéo de succès selon le type

---

## 13. Méthodes Publiques Principales

| Méthode | Description |
|---------|-------------|
| `setPose(pose, duration)` | Change la pose du colibri |
| `showMessage(message, duration)` | Affiche un message dans la bulle |
| `hideMessage()` | Cache la bulle de parole |
| `celebrate()` | Réaction à une bonne réponse |
| `encourage()` | Réaction à une mauvaise réponse |
| `think()` | Pose de réflexion |
| `majorCelebration()` | Célébration pour succès majeur |
| `showRandomEncouragement()` | Message d'encouragement aléatoire |
| `cycleSpriteMorph()` | Change le sprite au clic |
| `pauseVideo()` | Met en pause la vidéo |
| `resumeVideo()` | Reprend la vidéo |
| `interruptPause()` | Interrompt la pause programmée |
| `destroy()` | Détruit l'instance |

---

## 14. Bonnes Pratiques

1. **Toujours gérer les promesses `play()`** : Utiliser `.then()` et `.catch()` pour éviter les erreurs DOMException
2. **Ne pas appeler `pause()` immédiatement après `play()`** : Attendre que la promesse soit résolue
3. **Vérifier `isPaused` avant de reprendre** : Éviter les conflits
4. **Interrompre la pause lors d'interactions** : Pour une meilleure expérience utilisateur
5. **Utiliser les événements personnalisés** : Pour une intégration découplée avec les exercices

---

## 15. Dépannage

### Problème : Vidéo ne démarre pas
- Vérifier que `autoplay` n'est pas bloqué par le navigateur
- Vérifier les permissions de lecture automatique
- Vérifier que les fichiers vidéo existent aux chemins spécifiés

### Problème : Mascotte ne réagit pas aux exercices
- Vérifier que `interactive-exercises.js` émet les événements
- Vérifier que la mascotte globale est initialisée
- Vérifier la console pour les erreurs JavaScript

### Problème : Type d'image incorrect
- Vérifier que `window.userLevel` est défini
- Vérifier les classes CSS du body
- Forcer le type avec `imageType: 'cartoon'` ou `'realiste'`
