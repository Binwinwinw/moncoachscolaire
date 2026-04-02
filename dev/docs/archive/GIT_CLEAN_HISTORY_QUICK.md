# 🚀 Guide: Nettoyer l'Historique Git de MonCoachScolaire

**Situation actuelle:** `.env.production` et `tools/` sont dans l'historique git.

**Objectif:** Les supprimer complètement.

---

## ⏱️ Temps estimé: 5-10 minutes

---

## 📋 Checklist Avant de Commencer

- [ ] Vous êtes connecté en administrateur du repo
- [ ] Pas de développeurs actifs sur le repo en ce moment
- [ ] Sauvegarde locale du repo (juste pour être sûr)
- [ ] Tous les secrets ont été changés (ou vont être changés)

---

## 🎯 Plan d'Action

### ÉTAPE 1: Installer git-filter-repo

```bash
pip install git-filter-repo
```

**Vérification:**
```bash
git filter-repo --version
# Doit afficher: git-filter-repo version X.X.X
```

---

### ÉTAPE 2: Aller dans le répertoire du projet

```bash
cd d:\Hostinger\public_html\moncoachscolaire
```

---

### ÉTAPE 3: Nettoyer `.env.production`

```bash
git filter-repo --invert-paths --path .env.production
```

**Attendez ~2-5 secondes**

---

### ÉTAPE 4: Nettoyer `tools/`

```bash
git filter-repo --invert-paths --path tools
```

**Attendez ~5-10 secondes (plus de fichiers)**

---

### ÉTAPE 5: Optimiser l'historique

```bash
git reflog expire --expire=now --all
git gc --prune=now --aggressive
```

**Cela peut prendre 10-30 secondes**

---

### ÉTAPE 6: Vérifier le résultat

```bash
# Vérifier que .env.production ne revient plus
git log --all -- .env.production

# Vérifier que tools/ ne revient plus
git log --all -- tools

# Les deux commandes doivent retourner RIEN (pas de sortie)
```

---

### ÉTAPE 7: Force Push

⚠️ **ATTENTION:** Cela réécrit l'historique!

```bash
git push --force
```

**Ou plus sûr (avec protection):**
```bash
git push --force-with-lease
```

---

## ✅ Vérification Après

Aller sur GitHub/votre plateforme git et vérifier:

1. L'historique est court (quelques commits au lieu de 100+)
2. `.env.production` ne s'affiche plus nulle part
3. `tools/` ne s'affiche plus dans l'historique

---

## 🔒 APRÈS NETTOYAGE: CHANGER LES SECRETS

Les fichiers sont supprimés de l'historique, **MAIS** les secrets restent compromis s'ils ont été vus.

**Actions obligatoires:**

```sql
-- 1. Changer la DB password
ALTER USER 'db_user'@'localhost' IDENTIFIED BY 'NEW_SECURE_PASSWORD';
FLUSH PRIVILEGES;
```

```bash
# 2. Mettre à jour .env.production
# Changer DB_PASSWORD=...
```

```bash
# 3. Renouveler les API keys (si utilisées)
# Renouveler les tokens (si utilisés)
```

---

## 📢 Si Vous Avez Coéquipiers

**Informez-les AVANT de faire le force-push:**

```
Sujet: Git History Rewrite - Action nécessaire

Les fichiers sensibles ont été supprimés de l'historique git.
Veuillez faire:

1. git fetch origin
2. git rebase origin/main

Si vous avez des commits non-pushés, sauvegardez-les d'abord.
```

---

## ❌ Si Quelque Chose Se Passe Mal

```bash
# Récupérer l'état précédent (git-filter-repo crée des backups)
git reflog

# Trouver le commit original
git reset --hard ORIGINAL_COMMIT_HASH

# Ou: re-cloner le repo avant la modification
```

---

## 🎯 TL;DR (Résumé Ultra-Court)

```bash
# 1. Installer
pip install git-filter-repo

# 2. Nettoyer
git filter-repo --invert-paths --path .env.production
git filter-repo --invert-paths --path tools

# 3. Optimiser
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# 4. Vérifier
git log --all -- .env.production  # Doit être vide

# 5. Pousser
git push --force

# 6. Changer les secrets!
```

---

## ❓ Questions Fréquentes

**Q: Ça risque de casser quelque chose?**  
R: Non, si vous avez un seul développeur (vous). Si vous avez une équipe, les autres devront rebaser.

**Q: Les fichiers restent-ils sur mon disque?**  
R: Oui, `tools/` et `.env.production` restent physiquement. Git ignore juste l'historique.

**Q: Peut-on récupérer les fichiers après?**  
R: Pas via git (force-push a écrasé). Mais si vous avez une sauvegarde locale, vous pouvez la restaurer manuellement.

**Q: Combien de temps ça prend?**  
R: 5-10 minutes maximum.

**Q: Est-ce que c'est dangereux?**  
R: Non si vous êtes seul. Risky si vous avez une équipe (ils devront rebaser).

---

## 🚀 Commande Unique (Si Vous Êtes Courageux)

```bash
# À exécuter d'un coup
pip install git-filter-repo && `
git filter-repo --invert-paths --path .env.production && `
git filter-repo --invert-paths --path tools && `
git reflog expire --expire=now --all && `
git gc --prune=now --aggressive && `
echo "✅ Nettoyage terminé - Vérifiez avec: git log --all -- .env.production" && `
echo "Puis: git push --force"
```

---

**Questions?** Consultez `docs/GIT_CLEAN_HISTORY.md` pour plus de détails.

**Date:** 1 janvier 2026
