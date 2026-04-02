# Guide d'Installation de FFmpeg pour Windows

## 🎯 Objectif

Installer FFmpeg sur Windows pour pouvoir optimiser les vidéos MP4 de l'application.

---

## 📥 Méthode 1 : Installation Manuelle (Recommandée)

### Étape 1 : Télécharger FFmpeg

1. **Aller sur le site officiel** : https://www.gyan.dev/ffmpeg/builds/
2. **Choisir la version** : Cliquer sur "ffmpeg-release-essentials.zip"
   - Cette version contient tout ce qu'il faut sans être trop lourde
   - Taille : ~50-60 MB

### Étape 2 : Extraire FFmpeg

1. **Créer un dossier** : `C:\ffmpeg` (ou `C:\Program Files\ffmpeg`)
2. **Extraire le ZIP** dans ce dossier
   - Vous devriez avoir : `C:\ffmpeg\bin\ffmpeg.exe`

### Étape 3 : Ajouter au PATH (Optionnel mais Recommandé)

**Option A : Via l'Interface Graphique**

1. **Ouvrir les Variables d'environnement** :
   - Appuyer sur `Windows + R`
   - Taper `sysdm.cpl` et appuyer sur Entrée
   - OU : Panneau de configuration → Système → Paramètres système avancés

2. **Modifier les variables** :
   - Cliquer sur "Variables d'environnement"
   - Dans "Variables système", trouver "Path"
   - Cliquer sur "Modifier"
   - Cliquer sur "Nouveau"
   - Ajouter : `C:\ffmpeg\bin`
   - Cliquer sur "OK" partout

3. **Redémarrer PowerShell/Terminal** pour que les changements prennent effet

**Option B : Via PowerShell (Administrateur)**

```powershell
# Ouvrir PowerShell en tant qu'administrateur
# Ajouter au PATH utilisateur
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\ffmpeg\bin", "User")

# OU ajouter au PATH système (nécessite admin)
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\ffmpeg\bin", "Machine")
```

### Étape 4 : Vérifier l'Installation

**Ouvrir un nouveau PowerShell** et taper :

```powershell
ffmpeg -version
```

**Résultat attendu** :
```
ffmpeg version 6.x.x Copyright (c) 2000-2024 the FFmpeg developers
...
```

✅ **Si vous voyez la version, FFmpeg est installé correctement !**

---

## 📦 Méthode 2 : Installation via Chocolatey (Alternative)

Si vous avez **Chocolatey** installé :

```powershell
# Ouvrir PowerShell en tant qu'administrateur
choco install ffmpeg
```

---

## 📦 Méthode 3 : Installation via Scoop (Alternative)

Si vous avez **Scoop** installé :

```powershell
scoop install ffmpeg
```

---

## 🔍 Vérification et Test

### Test 1 : Vérifier que FFmpeg est dans le PATH

```powershell
# Depuis n'importe quel dossier
ffmpeg -version
```

### Test 2 : Tester avec le script d'optimisation

```powershell
# Depuis le dossier tools/
cd d:\Hostinger\public_html\moncoachscolaire\tools

# Si FFmpeg est dans le PATH
.\optimize_videos.ps1

# OU avec le chemin complet
.\optimize_videos.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"
```

### Test 3 : Optimiser une seule vidéo (test rapide)

```powershell
.\quick_optimize_video.ps1 -inputFile "..\assets\img\coach\colibri-cartoon_volant.mp4"
```

---

## ⚠️ Dépannage

### Problème : "ffmpeg n'est pas reconnu comme commande"

**Solutions** :

1. **Vérifier le chemin** :
   ```powershell
   # Tester avec le chemin complet
   C:\ffmpeg\bin\ffmpeg.exe -version
   ```

2. **Vérifier le PATH** :
   ```powershell
   $env:Path -split ';' | Select-String ffmpeg
   ```

3. **Redémarrer PowerShell** après modification du PATH

4. **Utiliser le chemin complet dans les scripts** :
   ```powershell
   .\optimize_videos.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"
   ```

### Problème : "Accès refusé" lors de l'ajout au PATH

**Solution** : Exécuter PowerShell en tant qu'administrateur

### Problème : FFmpeg ne trouve pas les codecs

**Solution** : Télécharger la version "essentials" ou "full" (pas "shared")

---

## 📋 Checklist d'Installation

- [ ] FFmpeg téléchargé depuis https://www.gyan.dev/ffmpeg/builds/
- [ ] FFmpeg extrait dans `C:\ffmpeg` (ou autre dossier)
- [ ] Dossier `bin` contient `ffmpeg.exe`
- [ ] PATH mis à jour (optionnel mais recommandé)
- [ ] PowerShell redémarré
- [ ] `ffmpeg -version` fonctionne
- [ ] Script d'optimisation testé avec succès

---

## 🚀 Après Installation

Une fois FFmpeg installé, vous pouvez :

1. **Optimiser toutes les vidéos** :
   ```powershell
   cd tools
   .\optimize_videos.ps1
   ```

2. **Tester sur une vidéo** :
   ```powershell
   .\quick_optimize_video.ps1 -inputFile "..\assets\img\coach\colibri-cartoon_volant.mp4"
   ```

3. **Vérifier les résultats** dans `assets/img/coach/optimized/`

---

## 📚 Ressources

- **Site officiel FFmpeg** : https://ffmpeg.org/
- **Builds Windows** : https://www.gyan.dev/ffmpeg/builds/
- **Documentation** : https://ffmpeg.org/documentation.html

---

## 💡 Astuce

Si vous ne voulez pas modifier le PATH, vous pouvez toujours utiliser le chemin complet dans les scripts :

```powershell
.\optimize_videos.ps1 -ffmpegPath "C:\ffmpeg\bin\ffmpeg.exe"
```

Cela fonctionne même si FFmpeg n'est pas dans le PATH !
