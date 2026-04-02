# Suppression de l'Arrière-Plan en Damier - Version Douce

## 🎯 Objectif

Version améliorée et moins agressive pour supprimer l'arrière-plan en damier des images PNG. Cette version utilise des méthodes plus précises qui préservent mieux les détails de l'image.

---

## 🔄 Différences avec la Version Standard

### Version Standard (agressive)
- Supprime tous les pixels correspondant aux couleurs du damier
- Peut supprimer des zones légitimes de l'image
- Traite toute l'image de la même manière

### Version Douce (recommandée)
- **Méthode `edge`** : Supprime uniquement les bords (10% de chaque côté)
- **Méthode `smart`** : Détection intelligente du pattern de damier
- **Méthode `conservative`** : Très conservateur, uniquement blanc pur en bordure
- Préserve mieux les détails au centre de l'image

---

## 📋 Prérequis

### 1. Python 3.x
```powershell
python --version
```

### 2. Pillow et NumPy
```powershell
pip install Pillow numpy
```

---

## 🚀 Utilisation

### Méthode 1 : Edge (Recommandée) - Bords Uniquement

Supprime uniquement le damier sur les bords (10% de chaque côté) :

```powershell
.\remove_checkerboard_background_gentle.ps1 -InputPath "image.png" -Method "edge"
```

**Avantages** :
- ✅ Moins agressive
- ✅ Préserve les détails au centre
- ✅ Idéale pour la plupart des cas

### Méthode 2 : Smart - Détection Intelligente

Détecte le pattern de damier avec analyse du voisinage :

```powershell
.\remove_checkerboard_background_gentle.ps1 -InputPath "image.png" -Method "smart"
```

**Avantages** :
- ✅ Détection plus précise
- ✅ Analyse le pattern alterné
- ✅ Bon compromis

### Méthode 3 : Conservative - Très Conservateur

Supprime uniquement le blanc pur (255,255,255) en bordure :

```powershell
.\remove_checkerboard_background_gentle.ps1 -InputPath "image.png" -Method "conservative" -Threshold 5
```

**Avantages** :
- ✅ Très sûr
- ✅ Ne supprime que ce qui est certainement du damier
- ✅ Idéal si vous avez peur de perdre des détails

---

## ⚙️ Paramètres

### `-Method`
- `edge` : Bords uniquement (défaut, recommandé)
- `smart` : Détection intelligente
- `conservative` : Très conservateur

### `-Threshold`
- Valeur : 5-30 (défaut: 15)
- Plus bas = plus conservateur
- Plus haut = plus agressif

**Exemples** :
```powershell
# Très conservateur
.\remove_checkerboard_background_gentle.ps1 -InputPath "image.png" -Method "edge" -Threshold 5

# Modéré (défaut)
.\remove_checkerboard_background_gentle.ps1 -InputPath "image.png" -Method "edge" -Threshold 15

# Plus agressif
.\remove_checkerboard_background_gentle.ps1 -InputPath "image.png" -Method "edge" -Threshold 25
```

---

## 📝 Exemples

### Exemple 1 : Traiter une image (méthode edge)

```powershell
cd tools
.\remove_checkerboard_background_gentle.ps1 -InputPath "..\assets\img\coach\colibribienvenue.png"
```

**Résultat** :
- `colibribienvenue.png` : Original préservé
- `colibribienvenue_no_bg_edge.png` : Nouveau fichier sans damier (bords uniquement)

### Exemple 2 : Méthode conservative

```powershell
.\remove_checkerboard_background_gentle.ps1 -InputPath "image.png" -Method "conservative" -Threshold 5
```

**Résultat** :
- `image_no_bg_conservative.png` : Très peu de pixels supprimés (sécurité maximale)

### Exemple 3 : Traiter un dossier

```powershell
.\remove_checkerboard_background_gentle.ps1 -InputPath "..\assets\img\coach"
```

**Résultat** : Tous les PNG du dossier sont traités avec la méthode `edge`

---

## 🔍 Comparaison des Méthodes

| Méthode | Agressivité | Précision | Vitesse | Recommandé pour |
|---------|-------------|-----------|---------|-----------------|
| `edge` | Faible | Moyenne | Rapide | **Cas général** |
| `smart` | Moyenne | Élevée | Lente | Images complexes |
| `conservative` | Très faible | Faible | Rapide | Images précieuses |

---

## 💡 Conseils

1. **Commencez par `edge`** : C'est la méthode la plus équilibrée
2. **Si trop agressif** : Utilisez `conservative` avec `-Threshold 5`
3. **Si pas assez** : Utilisez `smart` ou augmentez le threshold
4. **Testez d'abord** : Traitez une image pour voir le résultat avant de traiter tout un dossier

---

## 🛠️ Alternatives si le Script ne Fonctionne Pas

### 1. Outils Graphiques (Recommandé pour précision)

**GIMP** (gratuit) :
1. Ouvrir l'image
2. Outil "Sélection par couleur" (Shift+O)
3. Cliquer sur le damier
4. Supprimer (Delete)
5. Exporter en PNG avec transparence

**Photopea** (en ligne) : https://www.photopea.com/
- Même processus que GIMP
- Fonctionne dans le navigateur

### 2. Remove.bg (Automatique)

https://www.remove.bg/
- Upload l'image
- Télécharger le résultat
- Gratuit pour images < 0.5MB

---

## ✅ Checklist

- [ ] Python 3.x installé
- [ ] Pillow et NumPy installés
- [ ] Tester d'abord sur une image
- [ ] Vérifier le résultat avant traitement en masse
- [ ] Originaux préservés (jamais modifiés)

---

*Version douce créée pour préserver au maximum les détails de l'image*
