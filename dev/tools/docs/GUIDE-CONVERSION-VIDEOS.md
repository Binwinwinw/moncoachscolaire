# Guide de Conversion et Optimisation Vidéo - Solutions Trouvées

## 🔍 Problème Identifié

L'erreur **"maybe incorrect bit_rate, rate, width or height"** survient car les encodeurs vidéo (H.264, VP9) exigent que les **dimensions soient divisibles par 2**.

## ✅ Solution Principale

Utiliser **`-2`** dans le filtre `scale` au lieu de calculer manuellement les dimensions :

```bash
# ❌ INCORRECT (peut produire des dimensions impaires)
-vf "scale=720:-1"
-vf "scale='min(720,iw)':'min(720,ih)'"

# ✅ CORRECT (garantit des dimensions paires)
-vf "scale=-2:720"    # Hauteur 720, largeur calculée automatiquement (paire)
-vf "scale=1280:-2"   # Largeur 1280, hauteur calculée automatiquement (paire)
```

**Référence** : [Stack Overflow - FFmpeg error while opening encoder when resizing](https://stackoverflow.com/questions/31926401/ffmpeg-error-while-opening-encoder-when-resizing)

---

## 🎬 Commandes de Conversion Optimisées

### MP4 Optimisé (H.264)

```bash
ffmpeg -i input.mp4 \
  -c:v libx264 \
  -preset medium \
  -crf 28 \
  -vf "scale=-2:720" \
  -pix_fmt yuv420p \
  -c:a aac \
  -b:a 64k \
  -movflags +faststart \
  -avoid_negative_ts make_zero \
  -f mp4 \
  output.mp4
```

**Points clés** :
- `scale=-2:720` : Dimensions paires garanties
- `-f mp4` : Force le format MP4 (pas QuickTime)
- `-pix_fmt yuv420p` : Compatibilité maximale

### WebM Optimisé (VP9)

```bash
ffmpeg -i input.mp4 \
  -c:v libvpx-vp9 \
  -crf 30 \
  -b:v 0 \
  -vf "scale=-2:720" \
  -pix_fmt yuv420p \
  -c:a libopus \
  -b:a 64k \
  -speed 2 \
  -tile-columns 2 \
  -threads 0 \
  output.webm
```

**Points clés** :
- `scale=-2:720` : Dimensions paires garanties
- `-speed 2` : Compromis vitesse/qualité
- `-tile-columns 2` : Parallélisation
- `-threads 0` : Utilise tous les CPU disponibles

---

## 📚 Ressources et Techniques Avancées

### Encodage en Deux Passes (Meilleure Compression)

Pour une compression optimale, utilisez l'encodage en deux passes :

**Première passe (analyse)** :
```bash
ffmpeg -i input.mp4 \
  -c:v libvpx-vp9 \
  -b:v 1000K \
  -pass 1 \
  -an \
  -f null /dev/null
```

**Deuxième passe (encodage)** :
```bash
ffmpeg -i input.mp4 \
  -c:v libvpx-vp9 \
  -b:v 1000K \
  -pass 2 \
  -c:a libopus \
  -b:a 64k \
  output.webm
```

**Avantage** : Fichiers 10-20% plus petits à qualité équivalente

**Référence** : [Video Stack Exchange - Convert MP4 to WebM without quality loss](https://video.stackexchange.com/questions/19590/convert-mp4-to-webm-without-quality-loss-with-ffmpeg)

---

## 🔧 Scripts Améliorés

Les scripts ont été mis à jour avec :

1. **Filtre scale corrigé** : Utilisation de `-2` pour garantir des dimensions paires
2. **Tentatives multiples** : 3 méthodes différentes si la première échoue
3. **Meilleures pratiques WebM** : Paramètres optimisés pour VP9
4. **Gestion d'erreurs améliorée** : Messages détaillés pour diagnostiquer

### Utilisation

```powershell
# Script robuste (recommandé)
.\optimize_videos_robust.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"

# Script standard
.\optimize_videos.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"
```

---

## 📖 Sources et Références

### Documentation Officielle
- **FFmpeg** : https://ffmpeg.org/documentation.html
- **VP9 Encoding** : https://developers.google.com/media/vp9
- **WebM Project** : https://www.webmproject.org/

### Guides et Tutoriels
- **Stack Overflow** : Questions/réponses FFmpeg
- **Video Stack Exchange** : Forum spécialisé vidéo
- **OTTVerse** : Guides d'optimisation vidéo
- **Streaming Learning Center** : Techniques d'encodage

### Outils Alternatifs
- **HandBrake** : https://handbrake.fr/ (Interface graphique)
- **CloudConvert** : https://cloudconvert.com/ (En ligne)
- **FreeConvert** : https://www.freeconvert.com/ (En ligne)

---

## 💡 Paramètres Recommandés par Cas d'Usage

### Animations Courtes (5 secondes, 720p)
- **MP4** : CRF 28, preset medium
- **WebM** : CRF 30, speed 2
- **Résolution** : 720p max
- **Audio** : 64kbps (suffisant pour animations)

### Vidéos Éducatives (Longues, 1080p)
- **MP4** : CRF 23, preset slow
- **WebM** : CRF 25, speed 1
- **Résolution** : 1080p
- **Audio** : 128kbps

---

## ✅ Checklist de Conversion

- [x] Dimensions paires (utiliser `-2` dans scale)
- [x] Format pixel yuv420p
- [x] Faststart activé pour web
- [x] Timestamps corrigés
- [x] Format MP4 forcé (pas QuickTime)
- [x] Résolution adaptée (720p pour animations)
- [x] Bitrate audio optimisé

---

*Basé sur les meilleures pratiques 2024 et recherches approfondies*
