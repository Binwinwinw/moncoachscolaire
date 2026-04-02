# Guide d'Optimisation des Vidéos - MonCoachScolaire

## 🎯 Objectif

Réduire le poids des vidéos MP4 de **20% à 50%** pour améliorer les performances de l'application web et réduire les temps de chargement.

---

## 📊 Résultats Attendus

- **MP4 optimisé** : -20 à -40% de poids
- **WebM (VP9)** : -30 à -50% de poids
- **Qualité** : Visuellement identique à l'original
- **Résolution** : Max 720p (suffisant pour les animations de mascotte)

---

## 1. Installation de FFmpeg

### Windows (XAMPP)

1. **Télécharger FFmpeg** : https://www.gyan.dev/ffmpeg/builds/
   - Choisir "ffmpeg-release-essentials.zip"
   - Extraire dans `C:\ffmpeg` (ou autre dossier)

2. **Ajouter au PATH** (optionnel) :
   - Ouvrir "Variables d'environnement"
   - Ajouter `C:\ffmpeg\bin` au PATH
   - OU utiliser le chemin complet dans les scripts

3. **Vérifier l'installation** :
   ```powershell
   ffmpeg -version
   ```

### Linux

```bash
sudo apt-get update
sudo apt-get install ffmpeg
```

### Mac

```bash
brew install ffmpeg
```

---

## 2. Utilisation des Scripts

### 2.1 Script PowerShell (Windows) - RECOMMANDÉ

```powershell
# Depuis le dossier tools/
cd tools

# Optimiser toutes les vidéos
.\optimize_videos.ps1

# Avec chemin personnalisé vers FFmpeg
.\optimize_videos.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"

# Sans créer de WebM (seulement MP4)
.\optimize_videos.ps1 -createWebM:$false

# Sans backup des originaux
.\optimize_videos.ps1 -backupOriginal:$false
```

**Résultat** :
- Vidéos optimisées dans `assets/img/coach/optimized/`
- Backups dans `backups/videos_original/`
- Rapport de réduction affiché

### 2.2 Script Bash (Linux/Mac)

```bash
# Rendre exécutable
chmod +x tools/optimize_videos.sh

# Exécuter
./tools/optimize_videos.sh

# Avec chemin personnalisé
FFMPEG_PATH=/usr/local/bin/ffmpeg ./tools/optimize_videos.sh
```

### 2.3 Script PHP (Multi-plateforme)

```bash
# Depuis la racine du projet
php tools/optimize_videos.php

# Avec options
php tools/optimize_videos.php --ffmpeg-path="C:\ffmpeg\bin\ffmpeg.exe"
php tools/optimize_videos.php --no-webm
php tools/optimize_videos.php --no-backup
```

### 2.4 Test Rapide (Une seule vidéo)

```powershell
# Tester sur une seule vidéo
.\quick_optimize_video.ps1 -inputFile "assets\img\coach\colibri-cartoon_volant.mp4"
```

---

## 3. Formats et Techniques

### 3.1 Formats de Sortie

#### MP4 Optimisé (H.264)
- **Codec** : H.264 (libx264)
- **CRF** : 28 (qualité élevée, compression maximale)
- **Preset** : slow (meilleure compression)
- **Résolution** : Max 720p
- **Audio** : AAC 64kbps
- **Avantage** : Compatibilité maximale
- **Réduction** : -20 à -40%

#### WebM (VP9)
- **Codec** : VP9 (libvpx-vp9)
- **CRF** : 30 (équivalent qualité)
- **Bitrate** : Variable (0 = automatique)
- **Résolution** : Max 720p
- **Audio** : Opus 64kbps
- **Avantage** : Meilleure compression
- **Réduction** : -30 à -50%
- **Support** : Navigateurs modernes (Chrome, Firefox, Edge)

### 3.2 Paramètres d'Optimisation Utilisés

| Paramètre | Valeur | Raison |
|-----------|--------|--------|
| Résolution max | 720p | Suffisant pour animations, réduit la taille |
| CRF MP4 | 28 | Bon compromis qualité/compression |
| CRF WebM | 30 | Équivalent qualité VP9 |
| Preset | slow | Meilleure compression (plus lent) |
| Audio bitrate | 64kbps | Suffisant pour animations muettes |
| Faststart | Activé | Début rapide pour web |

---

## 4. Intégration dans l'Application

### 4.1 Structure des Fichiers

```
assets/img/coach/
├── colibri-cartoon_volant.mp4          (original)
├── colibri-realiste_volant.mp4          (original)
└── optimized/
    ├── colibri-cartoon_volant.mp4      (optimisé)
    ├── colibri-cartoon_volant.webm     (WebM)
    ├── colibri-realiste_volant.mp4     (optimisé)
    └── colibri-realiste_volant.webm   (WebM)
```

### 4.2 Code JavaScript Mis à Jour

Le code JavaScript (`colibri-mascot.js`) a été mis à jour pour :
- ✅ Détecter automatiquement le support WebM
- ✅ Utiliser WebM en priorité (plus léger)
- ✅ Fallback MP4 optimisé si WebM non disponible
- ✅ Fallback MP4 original en dernier recours

**Ordre de priorité** :
1. WebM optimisé (`optimized/*.webm`)
2. MP4 optimisé (`optimized/*.mp4`)
3. WebM original (`*.webm`)
4. MP4 original (`*.mp4`)

---

## 5. Après Optimisation

### 5.1 Étapes de Déploiement

1. **Exécuter le script d'optimisation**
   ```powershell
   .\tools\optimize_videos.ps1
   ```

2. **Vérifier les résultats**
   - Comparer les tailles de fichiers
   - Tester la qualité visuelle
   - Vérifier la lecture dans le navigateur

3. **Remplacer les originaux** (optionnel)
   ```powershell
   # Copier les optimisées vers le dossier principal
   Copy-Item "assets\img\coach\optimized\*.mp4" "assets\img\coach\" -Force
   ```

4. **OU garder les deux versions**
   - Les originaux dans `coach/`
   - Les optimisées dans `coach/optimized/`
   - Le JavaScript utilisera automatiquement les optimisées

### 5.2 Vérification

- ✅ Taille réduite
- ✅ Qualité visuelle acceptable
- ✅ Lecture fluide dans le navigateur
- ✅ Pas d'artefacts visuels
- ✅ Mascotte s'affiche correctement

---

## 6. Alternatives si FFmpeg Non Disponible

### 6.1 HandBrake (Interface Graphique)

1. Télécharger : https://handbrake.fr/
2. Ouvrir la vidéo
3. Preset : "Web/Google - 720p30"
4. Encoder : H.264 (x264)
5. Quality : RF 28
6. Exporter

### 6.2 Outils en Ligne

- **CloudConvert** : https://cloudconvert.com/
- **FreeConvert** : https://www.freeconvert.com/
- **Online-Convert** : https://www.online-convert.com/

### 6.3 Services Cloud

- **AWS MediaConvert**
- **Google Cloud Video Intelligence**
- **Azure Media Services**

---

## 7. Optimisations Avancées (Futur)

### 7.1 Lazy Loading

Charger les vidéos uniquement quand elles sont visibles :
```javascript
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.load();
        }
    });
});
```

### 7.2 Préchargement Intelligent

Précharger uniquement la première vidéo :
```html
<link rel="preload" href="video.mp4" as="video" type="video/mp4">
```

### 7.3 Qualité Adaptative

Proposer plusieurs qualités selon la connexion :
- 480p pour connexions lentes
- 720p pour connexions normales
- 1080p pour connexions rapides

### 7.4 Format AV1 (Futur)

- **Réduction** : -50% par rapport à H.264
- **Support** : Encore limité (Chrome, Firefox récents)
- **À considérer** : Dans 1-2 ans quand le support sera généralisé

---

## 8. Dépannage

### Problème : FFmpeg non trouvé

**Solution** :
```powershell
# Utiliser le chemin complet
.\optimize_videos.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"
```

### Problème : Erreur de conversion

**Vérifier** :
- ✅ Permissions d'écriture
- ✅ Espace disque disponible
- ✅ Fichiers non ouverts ailleurs
- ✅ Version de FFmpeg récente

### Problème : WebM non créé

**Cause** : VP9 non disponible dans votre version de FFmpeg

**Solution** :
- Installer une version récente de FFmpeg avec support VP9
- OU utiliser seulement MP4 optimisé (toujours efficace)

### Problème : Qualité dégradée

**Solution** : Ajuster le CRF dans les scripts
- CRF plus bas (ex: 23) = meilleure qualité, fichier plus gros
- CRF plus haut (ex: 32) = moins de qualité, fichier plus petit

---

## 9. Comparaison des Tailles

### Exemple avec une vidéo de 10 MB

| Format | Taille | Réduction |
|--------|--------|-----------|
| Original MP4 | 10 MB | - |
| MP4 optimisé | 6-8 MB | -20 à -40% |
| WebM (VP9) | 5-7 MB | -30 à -50% |

### Estimation pour toutes les vidéos

Si vous avez **50 MB** de vidéos :
- **MP4 optimisé** : ~30-40 MB (-20 à -40%)
- **WebM** : ~25-35 MB (-30 à -50%)
- **Économie totale** : 10-25 MB

---

## 10. Commandes FFmpeg Manuelles

Si vous préférez optimiser manuellement :

### MP4 Optimisé
```bash
ffmpeg -i input.mp4 \
  -c:v libx264 \
  -preset slow \
  -crf 28 \
  -vf "scale='min(720,iw)':'min(720,ih)':force_original_aspect_ratio=decrease" \
  -c:a aac \
  -b:a 64k \
  -movflags +faststart \
  output.mp4
```

### WebM (VP9)
```bash
ffmpeg -i input.mp4 \
  -c:v libvpx-vp9 \
  -crf 30 \
  -b:v 0 \
  -vf "scale='min(720,iw)':'min(720,ih)':force_original_aspect_ratio=decrease" \
  -c:a libopus \
  -b:a 64k \
  -row-mt 1 \
  output.webm
```

---

## ✅ Checklist de Déploiement

- [ ] FFmpeg installé et fonctionnel
- [ ] Scripts d'optimisation testés sur une vidéo
- [ ] Toutes les vidéos optimisées
- [ ] Qualité visuelle vérifiée
- [ ] Fichiers optimisés déployés
- [ ] JavaScript mis à jour (déjà fait)
- [ ] Tests dans différents navigateurs
- [ ] Performance améliorée vérifiée

---

## 📚 Ressources

- **FFmpeg Documentation** : https://ffmpeg.org/documentation.html
- **VP9 Encoding Guide** : https://developers.google.com/media/vp9
- **WebM Support** : https://caniuse.com/webm
- **Video Optimization Best Practices** : https://web.dev/fast/#optimize-your-videos

