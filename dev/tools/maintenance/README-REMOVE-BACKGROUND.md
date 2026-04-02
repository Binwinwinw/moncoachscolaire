# Suppression de l'Arrière-Plan en Damier des Images PNG

## 🎯 Objectif

Supprimer automatiquement l'arrière-plan en damier (transparent) des images PNG. Le damier est généralement composé de carrés blancs et gris clairs alternés.

---

## 📋 Prérequis

### 1. Python 3.x

Télécharger depuis : https://www.python.org/

**Vérification** :
```powershell
python --version
```

### 2. Pillow (PIL)

**Installation** :
```powershell
pip install Pillow
```

**Vérification** :
```powershell
python -c "import PIL; print(PIL.__version__)"
```

---

## 🚀 Utilisation

### Méthode 1 : Script PowerShell (Recommandé)

```powershell
# Traiter un fichier
.\remove_checkerboard_background.ps1 -InputPath "..\assets\img\coach\colibribienvenue.png"

# Traiter un dossier entier
.\remove_checkerboard_background.ps1 -InputPath "..\assets\img\coach"

# Spécifier un fichier de sortie
.\remove_checkerboard_background.ps1 -InputPath "input.png" -OutputPath "output.png"

# Choisir la méthode de détection
.\remove_checkerboard_background.ps1 -InputPath "input.png" -Method "white"  # Blanc uniquement
.\remove_checkerboard_background.ps1 -InputPath "input.png" -Method "gray"   # Gris uniquement
.\remove_checkerboard_background.ps1 -InputPath "input.png" -Method "both"   # Blanc et gris
.\remove_checkerboard_background.ps1 -InputPath "input.png" -Method "auto"   # Auto (défaut)

# Sans backup
.\remove_checkerboard_background.ps1 -InputPath "input.png" -NoBackup
```

### Méthode 2 : Script Python Direct

```powershell
# Traiter un fichier
python remove_checkerboard_background.py "input.png"

# Traiter un dossier
python remove_checkerboard_background.py "assets/img/coach"

# Avec options
python remove_checkerboard_background.py "input.png" -o "output.png" -m "auto"
```

---

## 🔧 Méthodes de Détection

### `auto` (Défaut)
Détecte automatiquement les pixels blancs et gris clairs du damier.

### `white`
Détecte uniquement les pixels blancs (255, 255, 255) et variations proches.

### `gray`
Détecte uniquement les pixels gris clairs (200-240).

### `both`
Détecte explicitement blanc ET gris.

---

## 📝 Exemples

### Exemple 1 : Traiter une image

```powershell
cd tools
.\remove_checkerboard_background.ps1 -InputPath "..\assets\img\coach\colibribienvenue.png"
```

**Résultat** :
- `colibribienvenue.png` : Image traitée (damier supprimé)
- `colibribienvenue.png.backup` : Backup de l'original

### Exemple 2 : Traiter tout le dossier coach

```powershell
.\remove_checkerboard_background.ps1 -InputPath "..\assets\img\coach"
```

**Résultat** : Tous les PNG du dossier sont traités avec backup automatique.

### Exemple 3 : Méthode personnalisée

Si le damier est très clair (presque blanc) :
```powershell
.\remove_checkerboard_background.ps1 -InputPath "image.png" -Method "white"
```

Si le damier est gris :
```powershell
.\remove_checkerboard_background.ps1 -InputPath "image.png" -Method "gray"
```

---

## 🛠️ Alternatives

### 1. Outils en Ligne

- **Remove.bg** : https://www.remove.bg/
- **Photopea** : https://www.photopea.com/ (gratuit, comme Photoshop)
- **LunaPic** : https://www.lunapic.com/editor/

### 2. Logiciels Desktop

- **GIMP** (gratuit) : Outil "Sélection par couleur" → Supprimer
- **Photoshop** : Magic Wand → Delete
- **Paint.NET** (Windows) : Outil "Magic Wand" → Delete

### 3. ImageMagick (Ligne de commande)

```bash
# Installer ImageMagick
# Puis utiliser :
magick input.png -fuzz 10% -transparent white output.png
```

---

## ⚙️ Fonctionnement Technique

Le script :
1. Ouvre l'image PNG en mode RGBA
2. Parcourt tous les pixels
3. Détecte les pixels correspondant aux couleurs du damier (avec tolérance)
4. Rend ces pixels transparents (alpha = 0)
5. Sauvegarde l'image modifiée

**Tolérance** : 10 points de différence RGB (pour gérer les variations)

---

## 🔍 Dépannage

### Problème : "Python n'est pas reconnu"
**Solution** : Ajouter Python au PATH ou utiliser le chemin complet

### Problème : "No module named 'PIL'"
**Solution** : `pip install Pillow`

### Problème : Le damier n'est pas complètement supprimé
**Solution** : Essayer une autre méthode (`-Method "white"` ou `-Method "gray"`)

### Problème : Des parties importantes sont supprimées
**Solution** : 
1. Restaurer depuis le backup (`.backup`)
2. Utiliser un outil graphique (GIMP, Photoshop) pour sélection manuelle

---

## 📊 Résultats Attendus

- **Avant** : Image PNG avec damier visible (blanc/gris)
- **Après** : Image PNG avec transparence réelle (damier invisible)
- **Backup** : Fichier `.backup` créé automatiquement

---

## ✅ Checklist

- [ ] Python 3.x installé
- [ ] Pillow installé (`pip install Pillow`)
- [ ] Scripts dans le dossier `tools/`
- [ ] Backup activé (par défaut)
- [ ] Test sur une image avant traitement en masse

---

*Script créé pour automatiser la suppression des arrière-plans en damier*
