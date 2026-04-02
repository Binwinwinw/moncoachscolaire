# Ressources et Techniques d'Optimisation Vidéo

## 📚 Sources et Références

Ce document compile les meilleures pratiques et ressources trouvées pour l'optimisation et la conversion de vidéos MP4 vers WebM.

---

## 🔍 Problèmes Identifiés et Solutions

### Problème 1 : Erreur "maybe incorrect bit_rate, rate, width or height"

**Cause** : Les encodeurs vidéo (H.264, VP9) exigent que les dimensions (largeur et hauteur) soient **divisibles par 2**. Si le filtre `scale` produit des dimensions impaires, l'encodage échoue.

**Solution** : Utiliser `-2` dans le filtre scale au lieu de `-1` ou de valeurs calculées.

**Exemple** :
```bash
# ❌ INCORRECT (peut produire des dimensions impaires)
-vf "scale=720:-1"

# ✅ CORRECT (garantit des dimensions paires)
-vf "scale=-2:720"    # Hauteur 720, largeur calculée automatiquement (paire)
-vf "scale=1280:-2"   # Largeur 1280, hauteur calculée automatiquement (paire)
```

**Référence** : [Stack Overflow - FFmpeg error while opening encoder when resizing](https://stackoverflow.com/questions/31926401/ffmpeg-error-while-opening-encoder-when-resizing)

---

## 🎬 Conversion MP4 vers WebM (VP9)

### Commandes de Base

#### Conversion Simple
```bash
ffmpeg -i input.mp4 -c:v libvpx-vp9 -c:a libopus output.webm
```

#### Conversion Optimisée (Recommandée)
```bash
ffmpeg -i input.mp4 \
  -c:v libvpx-vp9 \
  -crf 30 \
  -b:v 0 \
  -vf "scale=-2:720" \
  -c:a libopus \
  -b:a 64k \
  -speed 2 \
  -tile-columns 2 \
  -threads 0 \
  output.webm
```

**Paramètres expliqués** :
- `-crf 30` : Qualité constante (0=meilleur, 63=pire). 30 = bon compromis
- `-b:v 0` : Mode qualité constante (bitrate variable)
- `-vf "scale=-2:720"` : Réduction à 720p avec dimensions paires
- `-speed 2` : Vitesse d'encodage (0=plus lent/meilleur, 4=plus rapide)
- `-tile-columns 2` : Parallélisation pour accélération
- `-threads 0` : Utiliser tous les threads CPU disponibles

**Références** :
- [Google - VP9 Encoding Guide](https://developers.google.com/media/vp9/bitrate-modes/)
- [WebM Project - FFmpeg VP9 Encoding Guide](https://wiki.webmproject.org/ffmpeg/vp9-encoding-guide)

---

## 🎯 Encodage en Deux Passes (Meilleure Qualité)

Pour une compression optimale, utilisez l'encodage en deux passes :

### Première Passe (Analyse)
```bash
ffmpeg -i input.mp4 \
  -c:v libvpx-vp9 \
  -b:v 1000K \
  -pass 1 \
  -an \
  -f null /dev/null
```

### Deuxième Passe (Encodage)
```bash
ffmpeg -i input.mp4 \
  -c:v libvpx-vp9 \
  -b:v 1000K \
  -pass 2 \
  -c:a libopus \
  -b:a 64k \
  output.webm
```

**Avantages** :
- Meilleure compression
- Qualité optimale pour un bitrate donné
- Fichiers plus petits à qualité équivalente

**Inconvénient** : Plus lent (deux passes nécessaires)

**Référence** : [Video Stack Exchange - Convert MP4 to WebM without quality loss](https://video.stackexchange.com/questions/19590/convert-mp4-to-webm-without-quality-loss-with-ffmpeg)

---

## 📐 Conversion QuickTime/MOV vers MP4

### Remuxing (Sans Ré-encodage)
```bash
ffmpeg -i input.mov -c:v copy -c:a copy -movflags +faststart output.mp4
```

**Avantages** :
- Très rapide
- Aucune perte de qualité
- Préserve les codecs originaux

**Inconvénient** : Nécessite que les codecs soient compatibles MP4

### Ré-encodage avec Optimisation
```bash
ffmpeg -i input.mov \
  -c:v libx264 \
  -crf 18 \
  -preset slow \
  -pix_fmt yuv420p \
  -c:a aac \
  -b:a 192k \
  -movflags +faststart \
  output.mp4
```

**Référence** : [OTTverse - Convert MOV to MP4 using FFmpeg](https://ottverse.com/convert-mov-to-mp4-using-ffmpeg/)

---

## ⚙️ Paramètres de Qualité Recommandés

### MP4 (H.264)
- **CRF** : 18-28
  - 18 = Visuellement sans perte
  - 23 = Très bonne qualité (défaut)
  - 28 = Bonne qualité, compression élevée
- **Preset** : slow, medium, fast
  - slow = Meilleure compression (plus lent)
  - medium = Compromis (recommandé)
  - fast = Plus rapide (moins de compression)

### WebM (VP9)
- **CRF** : 15-35
  - 15 = Très haute qualité
  - 30 = Bon compromis (recommandé)
  - 35 = Compression élevée
- **Speed** : 0-4
  - 0 = Plus lent, meilleure compression
  - 2 = Compromis (recommandé)
  - 4 = Plus rapide

---

## 🎥 Résolutions et Bitrates Recommandés

### Pour Web (720p)
- **Résolution** : 1280x720
- **Bitrate MP4** : 2-3 Mbps
- **Bitrate WebM** : 1-2 Mbps

### Pour Web (1080p)
- **Résolution** : 1920x1080
- **Bitrate MP4** : 5-8 Mbps
- **Bitrate WebM** : 3-5 Mbps

**Note** : Pour des animations courtes (5 secondes), 720p est largement suffisant.

---

## 🚀 Optimisations Avancées

### Accélération Matérielle (NVIDIA)
```bash
ffmpeg -hwaccel cuda -i input.mp4 -c:v h264_nvenc output.mp4
```

### Parallélisation VP9
```bash
ffmpeg -i input.mp4 \
  -c:v libvpx-vp9 \
  -tile-columns 2 \
  -tile-rows 2 \
  -threads 4 \
  output.webm
```

---

## 📖 Ressources en Ligne

### Documentation Officielle
- **FFmpeg** : https://ffmpeg.org/documentation.html
- **VP9 Encoding** : https://developers.google.com/media/vp9
- **WebM Project** : https://www.webmproject.org/

### Guides et Tutoriels
- **Stack Overflow** : Questions/réponses sur FFmpeg
- **Video Stack Exchange** : Forum spécialisé vidéo
- **OTTVerse** : Guides d'optimisation vidéo
- **Streaming Learning Center** : Techniques d'encodage

### Outils en Ligne
- **CloudConvert** : https://cloudconvert.com/
- **FreeConvert** : https://www.freeconvert.com/
- **HandBrake** : https://handbrake.fr/ (GUI)

---

## 🔧 Commandes de Test

### Tester une Vidéo Individuelle
```bash
# MP4 optimisé
ffmpeg -i input.mp4 -c:v libx264 -crf 28 -vf "scale=-2:720" -c:a aac -b:a 64k -f mp4 output.mp4

# WebM optimisé
ffmpeg -i input.mp4 -c:v libvpx-vp9 -crf 30 -b:v 0 -vf "scale=-2:720" -c:a libopus -b:a 64k -speed 2 output.webm
```

### Vérifier les Dimensions
```bash
ffprobe -v error -select_streams v:0 -show_entries stream=width,height -of json input.mp4
```

---

## ✅ Checklist de Conversion

- [ ] Dimensions paires (divisibles par 2) ✅
- [ ] Format pixel yuv420p pour compatibilité ✅
- [ ] Faststart activé pour web ✅
- [ ] Timestamps corrigés (avoid_negative_ts) ✅
- [ ] Format MP4 forcé (pas QuickTime) ✅
- [ ] Résolution réduite à 720p max ✅
- [ ] Bitrate audio optimisé (64k pour animations) ✅

---

## 💡 Astuces

1. **Toujours utiliser `-2` dans scale** pour garantir des dimensions paires
2. **Tester d'abord sur une vidéo** avant de traiter toutes les vidéos
3. **Vérifier la qualité** des fichiers optimisés avant de remplacer les originaux
4. **Utiliser WebM en priorité** pour les navigateurs modernes (plus léger)
5. **Garder MP4 comme fallback** pour compatibilité maximale

---

## 🆘 Dépannage

### Erreur : "maybe incorrect bit_rate, rate, width or height"
→ Utiliser `-2` dans le filtre scale

### Erreur : "Codec not found"
→ Installer une version complète de FFmpeg avec tous les codecs

### Fichier trop petit après conversion
→ Vérifier la qualité, peut-être réduire le CRF

### Conversion très lente
→ Utiliser `-preset fast` ou `-speed 4` (moins de compression)

---

*Dernière mise à jour : Basé sur les meilleures pratiques 2024*
