# 🔧 OUTILS D'ANALYSE ET DE DIAGNOSTIC

## 📍 Outils Disponibles pour Analyser le Chargement CSS/JS

Ces outils vous aident à vérifier et diagnostiquer les problèmes de chargement des assets en local et en production.

---

## 1️⃣ **analyze_asset_links.php**

### 📝 Description
Analyse complète et détaillée de la structure des assets et de la fonction `asset_url()`.

### 🎯 Utilisation
```bash
php tools/analyze_asset_links.php
```

### 📊 Ce qu'il affiche
- ✅ Structure actuelle des répertoires
- ✅ Contenu des dossiers CSS/JS/Images
- ✅ Test de la fonction `asset_url()`
- ✅ Problèmes identifiés
- ✅ Pages qui chargent CSS/JS
- ✅ Recommandations pour solution hybride

### 💡 Quand l'utiliser
- Pour diagnostiquer les problèmes initiaux
- Pour comprendre la structure actuelle
- Pour identifier rapidement où se trouvent les assets

---

## 2️⃣ **test_hybrid_paths.php**

### 📝 Description
Test interactif avec interface web pour comparer local et production.

### 🎯 Utilisation
```bash
# Ouvrir dans le navigateur:
http://localhost/moncoachscolaire/tools/test_hybrid_paths.php
```

### 🖥️ Interface
- Boutons de sélection: "Local (XAMPP)" / "Production (Hostinger)"
- Affichage des chemins pour chaque environnement
- Liste détaillée des fichiers à tester
- Status: ✅ Existe / ❌ Manquant / ❓ À vérifier

### 💡 Quand l'utiliser
- Pour comparer visuellement local et production
- Pour tester manuellement les chemins
- Pour présenter le diagnostic au client

---

## 3️⃣ **diagnose_css_loading.php**

### 📝 Description
Diagnostic détaillé du chargement CSS avec vérification des fichiers critiques.

### 🎯 Utilisation
```bash
php tools/diagnose_css_loading.php
```

### 📊 Ce qu'il affiche
- ✅ Structure complète des répertoires
- ✅ Contenu des fichiers CSS/JS critiques
- ✅ Taille et date de modification des fichiers
- ✅ Résultat de la fonction `asset_url()` pour chaque asset
- ✅ Variables globales définies
- ✅ Résultats attendus par environnement

### 💡 Quand l'utiliser
- Pour vérifier que les fichiers CSS existent réellement
- Pour diagnostiquer les fichiers vides ou corrompus
- Pour confirmer les chemins générés
- À exécuter en LOCAL ET en PRODUCTION pour comparer

---

## 4️⃣ **report_hybrid_before_after.php**

### 📝 Description
Rapport comparatif détaillé montrant l'impact de la solution hybride.

### 🎯 Utilisation
```bash
php tools/report_hybrid_before_after.php
```

### 📊 Ce qu'il affiche
- 🔴 Logique AVANT (problèmes)
- 🟢 Logique APRÈS (solution)
- 📋 Tableau comparatif (7 aspects)
- 📄 Impact sur 6 fichiers différents
- 🔍 Exemple concret du chargement de style.css
- ✅ Checklist de vérification

### 💡 Quand l'utiliser
- Pour comprendre le problème en détail
- Pour montrer l'amélioration apportée
- Pour documenter la solution

---

## 5️⃣ **setup_hybrid_hostinger.sh**

### 📝 Description
Guide d'installation et commandes pour déployer la solution en production.

### 🎯 Utilisation
```bash
# À consulter pour les commandes de déploiement
cat tools/setup_hybrid_hostinger.sh
```

### 📋 Ce qu'il contient
- 🔧 Vérification de la structure en production
- 📦 Commandes de copie des assets
- 🔗 Alternatives (RewriteRule, symlink)
- ✅ Commandes de test

### 💡 Quand l'utiliser
- Avant de déployer en production
- Pour copier les assets au bon endroit
- Pour vérifier les URLs après déploiement

---

## 📊 MATRICE DE SÉLECTION

| Situation | Outil à utiliser | Objectif |
|-----------|------------------|----------|
| Diagnostiquer le problème initial | `analyze_asset_links.php` | Comprendre la structure |
| Comparer local/prod visuellement | `test_hybrid_paths.php` | Voir les différences |
| Vérifier les fichiers CSS en détail | `diagnose_css_loading.php` | Confirmer l'existence |
| Montrer l'amélioration apportée | `report_hybrid_before_after.php` | Documenter la solution |
| Déployer en production | `setup_hybrid_hostinger.sh` | Mettre en place les assets |

---

## 🚀 WORKFLOW RECOMMANDÉ

### Phase 1: Diagnostic Local
```bash
# 1. Analyser la structure
php tools/analyze_asset_links.php

# 2. Diagnostiquer en détail
php tools/diagnose_css_loading.php

# 3. Visualiser les chemins
# Ouvrir: http://localhost/moncoachscolaire/tools/test_hybrid_paths.php
```

### Phase 2: Déploiement Production
```bash
# 1. Copier les assets
# Suivre les instructions dans setup_hybrid_hostinger.sh

# 2. Vérifier en production
ssh user@hostinger
php tools/diagnose_css_loading.php
```

### Phase 3: Validation
```bash
# Accéder à l'application
https://moncoachscolaire.fr

# Vérifier dans F12 > Network
# Les fichiers CSS doivent être chargés (Status 200)

# Vérifier le rendu CSS
# Tous les styles doivent s'afficher correctement
```

---

## 🔍 COMMANDES UTILES

### Tester les URLs CSS directement
```bash
# Local
curl http://localhost/moncoachscolaire/public/assets/css/style.css | head -20

# Production
curl https://moncoachscolaire.fr/assets/css/style.css | head -20
```

### Vérifier les répertoires en prod (via SSH)
```bash
ssh u936396612@moncoachscolaire.fr
ls -la /home/u936396612/domains/moncoachscolaire.fr/public_html/assets/css/
```

### Tester asset_url() directement
```bash
php -r "
require 'src/config/config.php';
echo asset_url('assets/css/style.css') . PHP_EOL;
"
```

---

## 📈 RÉSULTATS ATTENDUS

### ✅ Après la correction hybride

#### Local (XAMPP)
```
asset_url('assets/css/style.css')
→ /public/assets/css/style.css ✅
```

#### Production (Hostinger)
```
asset_url('assets/css/style.css')
→ /assets/css/style.css ✅
```

Ou (si assets copiés):
```
asset_url('assets/css/style.css')
→ /assets/css/style.css ✅
```

---

## ❓ QUESTIONS FRÉQUENTES

**Q: Quel outil je dois utiliser en premier?**  
R: Commence par `analyze_asset_links.php` pour avoir une vue d'ensemble.

**Q: Comment je sais si la solution fonctionne?**  
R: Ouvre F12 dans le navigateur (Network tab) et vérifie que les CSS ont HTTP 200.

**Q: Je dois exécuter les outils en production?**  
R: Oui, particulièrement `diagnose_css_loading.php` et `test_hybrid_paths.php` pour comparer.

**Q: Que faire si les outils montrent des erreurs?**  
R: Consulte le fichier `ANALYSE_HYBRIDE_CSS.md` pour les solutions.

---

## 📞 SUPPORT

Pour plus d'informations, consultez:
- 📖 [ANALYSE_HYBRIDE_CSS.md](../ANALYSE_HYBRIDE_CSS.md) - Guide complet
- 📝 [src/config/config.php](../src/config/config.php) - Fonction `asset_url()` améliorée
- 🔧 [.htaccess](../.htaccess) - Configuration serveur (si RewriteRule utilisée)

---

**Dernière mise à jour**: 31 décembre 2025  
**Statut**: ✅ Prêt pour production
