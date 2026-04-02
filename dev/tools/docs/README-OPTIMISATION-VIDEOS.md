# Guide d'Utilisation - Optimisation des Vidéos

## 🎯 Objectif

Réduire le poids des vidéos MP4 de 20% à 50% pour améliorer les performances de l'application web.

## 📋 Prérequis

### Installation de FFmpeg

**Windows :**
1. Télécharger : https://www.gyan.dev/ffmpeg/builds/
2. Extraire dans `C:\ffmpeg` (ou autre dossier)
3. Ajouter au PATH ou utiliser le chemin complet dans les scripts

**Linux :**
```bash
sudo apt-get update
sudo apt-get install ffmpeg
```

**Mac :**
```bash
brew install ffmpeg
```

## 🚀 Utilisation

### Option 1 : Script PowerShell (Windows)

```powershell
# Depuis le dossier tools/
cd tools
.\optimize_videos.ps1

# Avec chemin personnalisé vers FFmpeg
.\optimize_videos.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"

# Sans créer de WebM
.\optimize_videos.ps1 -createWebM:$false

# Sans backup
.\optimize_videos.ps1 -backupOriginal:$false
```

### Option 2 : Script Bash (Linux/Mac)

```bash
# Rendre exécutable
chmod +x tools/optimize_videos.sh

# Exécuter
./tools/optimize_videos.sh

# Avec chemin personnalisé
FFMPEG_PATH=/usr/local/bin/ffmpeg ./tools/optimize_videos.sh
```

### Option 3 : Script PHP (Multi-plateforme)

```bash
# Depuis la racine du projet
php tools/optimize_videos.php

# Avec options
php tools/optimize_videos.php --ffmpeg-path="C:\ffmpeg\bin\ffmpeg.exe"
php tools/optimize_videos.php --no-webm
php tools/optimize_videos.php --no-backup
```

## 📊 Résultats Attendus

- **MP4 optimisé** : -20 à -40% de poids
- **WebM (VP9)** : -30 à -50% de poids
- **Qualité** : Visuellement identique à l'original
- **Résolution** : Max 720p (suffisant pour les animations)

## 🔄 Après Optimisation

1. Les vidéos optimisées sont dans `assets/img/coach/optimized/`
2. Les originaux sont sauvegardés dans `backups/videos_original/`
3. Remplacer les vidéos originales par les optimisées
4. Mettre à jour le code JavaScript pour utiliser WebM avec fallback MP4

## ⚙️ Paramètres d'Optimisation

### MP4 (H.264)
- **CRF** : 28 (qualité élevée, compression maximale)
- **Preset** : slow (meilleure compression)
- **Résolution** : Max 720p
- **Audio** : AAC 64kbps

### WebM (VP9)
- **CRF** : 30 (équivalent qualité)
- **Bitrate** : Variable (0 = automatique)
- **Résolution** : Max 720p
- **Audio** : Opus 64kbps

## 🎬 Formats de Sortie

### MP4 Optimisé
- Format : MP4 (H.264)
- Compatibilité : Tous navigateurs
- Utilisation : Fallback pour compatibilité

### WebM
- Format : WebM (VP9)
- Compatibilité : Navigateurs modernes
- Utilisation : Format principal (plus léger)

## 📝 Notes Importantes

1. **Temps de traitement** : Compte 2-5 minutes par vidéo selon la taille
2. **Espace disque** : Prévoir 2x l'espace des originaux (backup + optimisé)
3. **Qualité** : Les paramètres sont optimisés pour animations, pas pour vidéos haute qualité
4. **Test** : Toujours tester une vidéo avant de traiter toutes les vidéos

## 🔍 Vérification

Après optimisation, vérifier :
- ✅ Taille réduite
- ✅ Qualité visuelle acceptable
- ✅ Lecture fluide dans le navigateur
- ✅ Pas d'artefacts visuels

## 🆘 Dépannage

### FFmpeg non trouvé
- Vérifier l'installation
- Utiliser le paramètre `--ffmpeg-path` avec le chemin complet

### Erreur de conversion
- Vérifier les permissions d'écriture
- Vérifier l'espace disque disponible
- Vérifier que les fichiers ne sont pas ouverts ailleurs

### WebM non créé
- Normal si VP9 n'est pas disponible dans votre version de FFmpeg
- Installer une version récente de FFmpeg avec support VP9
