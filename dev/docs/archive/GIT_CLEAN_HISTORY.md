# 🔐 Comment Effacer les Traces des Fichiers Sensibles dans Git

**Situation:** Des fichiers sensibles (`.env.production`, `tools/`, `tests/`) ont été commités et restent dans l'historique git.

**Problème:** Même s'ils ne sont plus visibles, ils peuvent être récupérés via:
```bash
git show COMMIT_HASH:.env.production  # Récupère la version d'un commit passé
git log -p -- .env.production          # Voit toutes les versions
```

---

## ⚠️ Importance: RÉELLEMENT Supprimer l'Historique

Si `.env.production` contient des secrets (BD password, API keys, etc.):
- ❌ Les secrets restent accessibles dans l'historique
- ❌ Quiconque clone le repo peut voir l'historique complet
- ✅ Il FAUT réécrire l'historique pour les supprimer

---

## 🎯 Solutions Disponibles

### Option 1: `git filter-repo` (RECOMMANDÉ - Moderne)
**Avantage:** Outil maintenu par GitHub, plus puissant et sûr  
**Inconvénient:** Nécessite installation

```bash
# 1. Installer
pip install git-filter-repo

# 2. Nettoyer l'historique
git filter-repo --invert-paths --path .env.production
git filter-repo --invert-paths --path tools/
git filter-repo --invert-paths --path tests/

# 3. Force push
git push --force
```

### Option 2: `BFG Repo-Cleaner` (PLUS SIMPLE)
**Avantage:** Interface simple, très rapide  
**Inconvénient:** Moins flexible que filter-repo

```bash
# 1. Télécharger: https://rtyley.github.io/bfg-repo-cleaner/

# 2. Utiliser
bfg --delete-files .env.production my-repo.git
bfg --delete-folders tools my-repo.git

# 3. Reflog et gc
cd my-repo.git
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# 4. Force push
git push --force
```

### Option 3: `git filter-branch` (NATIF - Mais complexe)
**Avantage:** Aucune dépendance externe  
**Inconvénient:** Lent et complexe

```bash
# ⚠️ DANGEREUX - À utiliser avec précaution

# Supprimer un fichier
git filter-branch --tree-filter 'rm -f .env.production' HEAD

# Supprimer un dossier
git filter-branch --tree-filter 'rm -rf tools' HEAD

# Force push
git push --force
```

---

## 📋 Étapes Complètes: Nettoyer Proprement

### Avant de commencer:
1. ✅ S'assurer qu'on est dans le bon repo
2. ✅ Créer une sauvegarde du repo local
3. ✅ Informer l'équipe (ça réécrit l'historique!)
4. ✅ Changer TOUS les secrets immédiatement (BD password, API keys, etc.)

### Plan:

**Étape 1: Sauvegarde**
```bash
# Copier le dossier local comme backup
Copy-Item moncoachscolaire moncoachscolaire.backup -Recurse
```

**Étape 2: Installer l'outil**
```bash
# Option: git-filter-repo (RECOMMANDÉ)
pip install git-filter-repo

# Ou: BFG (si git-filter-repo pose problème)
# Télécharger depuis https://rtyley.github.io/bfg-repo-cleaner/
```

**Étape 3: Clone miroir (pour BFG)**
```bash
git clone --mirror https://github.com/yourname/moncoachscolaire.git moncoachscolaire.git
cd moncoachscolaire.git
```

**Étape 4: Nettoyer avec BFG**
```bash
# Supprimer le fichier sensible
bfg --delete-files .env.production

# Supprimer les dossiers
bfg --delete-folders tools
bfg --delete-folders tests
```

**Étape 5: Reflog et Garbage Collection**
```bash
# Expirer les anciennes références
git reflog expire --expire=now --all

# Nettoyer les objets orphelins
git gc --prune=now --aggressive
```

**Étape 6: Force Push**
```bash
git push --force
```

---

## ⚡ Méthode Rapide Avec git-filter-repo

**Le plus simple à faire:**

```bash
# 1. Installer
pip install git-filter-repo

# 2. Aller dans le repo
cd moncoachscolaire

# 3. Nettoyer l'historique
git filter-repo --invert-paths --path .env.production
git filter-repo --invert-paths --path tools
git filter-repo --invert-paths --path tests

# 4. Force push
git push --force
```

**Résultat:** Les fichiers sont complètement supprimés de l'historique. Personne ne peut les récupérer.

---

## ⚠️ Conséquences du Force Push

### Pour les autres développeurs:
```bash
# ❌ Leur repo local sera "en avance"
# ✅ Solution: Rebase
git fetch origin
git rebase origin/main

# OU: Re-cloner
git clone https://github.com/yourname/moncoachscolaire.git
```

### Important:
1. **Informer l'équipe AVANT** (chat, email, etc.)
2. **Fournir des instructions** pour rebase
3. **Attendre que tout le monde synchronise**

---

## 🔒 Vérification Après Nettoyage

```bash
# Vérifier que les fichiers ne sont plus dans l'historique
git log --all -- .env.production
# (Doit être vide)

git log --all -- tools/
# (Doit être vide)

# Vérifier que le fichier n'est pas accessible
git rev-list --all -- .env.production | wc -l
# Doit afficher: 0
```

---

## 🚨 SI C'EST TROP TARD (Repo public depuis longtemps)

Si le repo était public et les secrets ont pu être clonés:

1. ❌ **Nettoyer l'historique** (comme ci-dessus)
2. ❌ **MAIS:** Les secrets restent compromis (quelqu'un les a peut-être vus)
3. ✅ **Action obligatoire:** Changer IMMÉDIATEMENT les secrets
   - [ ] Changer DB password
   - [ ] Renouveler API keys
   - [ ] Changer tokens
   - [ ] Audit des accès

---

## 📋 Checklist: Avant/Après Nettoyage

### AVANT:
- [ ] Créer sauvegarde locale
- [ ] Informer l'équipe
- [ ] Installer l'outil (pip install git-filter-repo)
- [ ] Vérifier quels fichiers contiennent des secrets

### PENDANT:
- [ ] Exécuter le nettoyage
- [ ] Vérifier que les fichiers sont supprimés
- [ ] Force push

### APRÈS:
- [ ] Vérifier que le repo est propre
- [ ] Informer l'équipe (rebase nécessaire)
- [ ] CHANGER TOUS LES SECRETS
- [ ] Audit des accès et logs

---

## 💡 Prévention Future

**Toujours ajouter au `.gitignore` AVANT de commiter:**

```gitignore
# Configuration sensible
.env
.env.production
.env.local
.env.*.local

# Scripts internes (optionnel)
tools/
tests/

# Autres
node_modules/
vendor/
.vscode/
.idea/
```

**Puis:** `git add .gitignore && git commit -m "Add .gitignore"`

---

## 🎯 Décision: Faut-il Vraiment Nettoyer?

**OUI, nettoyer si:**
- ✅ `.env.production` contient des secrets (password, API keys)
- ✅ Fichiers exposent la structure d'infrastructure
- ✅ Repo est/sera public
- ✅ Données sensibles ont été commitées accidentellement

**NON, peut attendre si:**
- ❌ Repo est 100% privé et sera toujours privé
- ❌ Secrets sont déjà changés
- ❌ Données non-sensibles uniquement

**Pour MonCoachScolaire:** ✅ **OUI, il faut nettoyer** (`.env.production` a des secrets)

---

**Auteur:** Basé sur expérience Git  
**Sources:** GitHub Docs, Pro Git Book  
**Dernière mise à jour:** 1 janvier 2026
