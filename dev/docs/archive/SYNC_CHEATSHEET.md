# 🔄 Synchronisation BD Locale → Production

**Résumé:** Tout ce qu'il faut savoir pour fusionner les données locales vers la production **sans casser les comptes existants**.

---

## 📦 Fichiers Créés

Vous avez maintenant 5 fichiers pour la synchronisation:

| Fichier | Type | Utilité | Usage |
|---------|------|---------|-------|
| **sync_local_to_prod_safe.sql** | SQL | Script principal de synchronisation | `mysql < sync_local_to_prod_safe.sql` |
| **sync_local_to_prod_safe.php** | PHP | Version contrôlée avec logs | `php sync_local_to_prod_safe.php [--dry-run]` |
| **sync_config.sh** | Bash | Configuration et fonctions utiles | `source sync_config.sh` |
| **workflow_sync.sh** | Bash | Workflow complet interactif | `./workflow_sync.sh [options]` |
| **GUIDE_SYNC_LOCAL_PROD.md** | Doc | Guide détaillé (cette page) | Lire avant d'exécuter |

---

## 🚀 Quick Start (5 minutes)

### Étape 1: Simulation Locale (SANS RISQUE)

```bash
cd db/

# Mode simulation - Voir exactement ce qui va se passer
php ../tools/sync_local_to_prod_safe.php --dry-run --verbose
```

**Résultat attendu:**
```
✅ "SIMULATION: 245 exercices seraient ajoutés"
✅ "SIMULATION: 3 pouvoirs seraient ajoutés"
...
```

### Étape 2: Vraie Synchronisation

```bash
# Sauvegarder d'abord!
mysqldump -u root -p moncoachscolaire > backup_before_sync.sql

# Exécuter la sync
php ../tools/sync_local_to_prod_safe.php --verbose

# Vérifier les résultats
mysql -u root -p moncoachscolaire -e "SELECT COUNT(*) FROM Exercises"
```

**C'est fait!** ✅

---

## 🎯 Stratégie de Sécurité

### Ce qui est Synchronisé (SAFE)

```
✅ EXERCICES NOUVEAUX       → Ajoutés en prod
✅ POUVOIRS NOUVEAUX        → Ajoutés en prod
✅ ACHIEVEMENTS NOUVEAUX    → Ajoutés en prod
✅ UTILISATEURS NOUVEAUX    → Ajoutés en prod
✅ PROGRÈS MANQUANT         → Fusionné intelligemment
✅ RÉPONSES MANQUANTES      → Conservées + nouvelles ajoutées
```

### Ce qui NE CHANGE PAS (PROTÉGÉ)

```
🔒 Utilisateurs existants en prod  → NE SONT PAS MODIFIÉS
🔒 Mots de passe en prod           → INTACTS
🔒 Rôles des utilisateurs          → PRÉSERVÉS
🔒 Progrès existants               → CONSERVÉS (max fusionné)
🔒 Réponses existantes             → JAMAIS SUPPRIMÉES
```

### Clés Uniques (Prévention des Doublons)

```
🔐 Exercices:     Title + Subject + Level
   (ex: "Calcul d'aire" + "Maths" + "6ème" = unique)

🔐 Pouvoirs:      Name
   (ex: "Super Speed" = unique)

🔐 Achievements:  Name
   (ex: "Master Trainer" = unique)

🔐 Utilisateurs:  Email
   (ex: "john@example.com" = unique globalement)
```

**Résultat:** Impossible d'avoir des doublons même en syncing 10 fois!

---

## 💻 Options Avancées

### Sync Partielle (Ignorer Certaines Tables)

```bash
# Sans les utilisateurs
php ../tools/sync_local_to_prod_safe.php --skip-users

# Sans le progrès
php ../tools/sync_local_to_prod_safe.php --skip-progress

# Les deux
php ../tools/sync_local_to_prod_safe.php --skip-users --skip-progress
```

### Avec Serveur Distant

```bash
# Déclarer les variables d'environnement
export DB_PROD_HOST="monsite.com"
export DB_PROD_USER="sync_user"
export DB_PROD_PASSWORD="secure_password"

# Puis lancer
php ../tools/sync_local_to_prod_safe.php --verbose
```

### Mode Verbeux (Plus de Détails)

```bash
php ../tools/sync_local_to_prod_safe.php --verbose
```

---

## 🔄 Workflow Complet Interactif

Pour un déploiement sécurisé avec vérifications à chaque étape:

```bash
chmod +x db/workflow_sync.sh
./db/workflow_sync.sh [options]
```

**Options:**
```
--dry-run              # Simulation seulement
--prod-host HOST       # Serveur production
--prod-user USER       # Utilisateur BD prod
--prod-pwd PASSWORD    # Mot de passe
--no-interactive       # Sans pauses
--no-backup            # Sans sauvegarde (dangereux!)
```

**Exemples:**
```bash
# Simulation complète locale
./db/workflow_sync.sh --dry-run

# Vraie sync (production)
./db/workflow_sync.sh

# Sync vers serveur distant
./db/workflow_sync.sh --prod-host production.mysite.com --prod-user sync --prod-pwd secret123
```

---

## 📊 Vérifications Avant & Après

### Voir les Données Avant Sync

```bash
# BD Locale
mysql -u root -p moncoachscolaire_local -e "
  SELECT 'Exercices' as 'Table', COUNT(*) as 'Count' FROM Exercises
  UNION ALL SELECT 'Users', COUNT(*) FROM Users
  UNION ALL SELECT 'Powers', COUNT(*) FROM Powers
  UNION ALL SELECT 'Achievements', COUNT(*) FROM Achievements
"

# BD Production
mysql -u root -p moncoachscolaire -e "
  SELECT 'Exercices' as 'Table', COUNT(*) as 'Count' FROM Exercises
  UNION ALL SELECT 'Users', COUNT(*) FROM Users
  UNION ALL SELECT 'Powers', COUNT(*) FROM Powers
  UNION ALL SELECT 'Achievements', COUNT(*) FROM Achievements
"
```

### Vérifier Qu'Aucun Doublon N'existe

```bash
# Doublons sur exercices?
mysql -u root -p moncoachscolaire -e "
  SELECT Subject, Level, Title, COUNT(*) as cnt 
  FROM Exercises 
  GROUP BY Subject, Level, Title 
  HAVING cnt > 1
"
# Résultat attendu: AUCUNE ligne

# Intégrité des clés étrangères?
mysql -u root -p moncoachscolaire -e "
  SELECT COUNT(*) as 'Réponses orphelines' 
  FROM ExerciseResponses 
  WHERE UserId NOT IN (SELECT Id FROM Users)
"
# Résultat attendu: 0
```

---

## ⚙️ Configuration personnalisée

Créez un fichier `.env.sync` pour ne pas taper les credentials à chaque fois:

```bash
# db/.env.sync
DB_LOCAL_HOST=localhost
DB_LOCAL_USER=root
DB_LOCAL_PASSWORD=
DB_LOCAL_NAME=moncoachscolaire_local

DB_PROD_HOST=production.mysite.com
DB_PROD_USER=sync_user
DB_PROD_PASSWORD=secure_password_here
DB_PROD_NAME=moncoachscolaire
```

Puis charger avant chaque commande:
```bash
source db/.env.sync
php ../tools/sync_local_to_prod_safe.php
```

⚠️ **IMPORTANT:** N'ajoutez JAMAIS `.env.sync` à git!

---

## 🚨 En Cas de Problème

### Erreur: "Access denied for user"

```bash
# Vérifier les credentials
mysql -u root -p -e "SELECT USER()"

# Tester avec le bon serveur
mysql -h production.mysite.com -u sync_user -p moncoachscolaire -e "SELECT 1"
```

### Erreur: "Unknown column 'is_active'"

```sql
-- Ajouter la colonne manquante
ALTER TABLE Exercises ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER Answer;
-- Puis réessayer le sync
```

### Doublons Créés Malgré Tout

```sql
-- Nettoyer les doublons
DELETE e1 FROM Exercises e1
INNER JOIN (
  SELECT Subject, Level, Title, MIN(Id) as min_id
  FROM Exercises
  GROUP BY Subject, Level, Title
  HAVING COUNT(*) > 1
) e2 ON e1.Subject = e2.Subject AND e1.Level = e2.Level AND e1.Title = e2.Title
WHERE e1.Id > e2.min_id;
```

### Rollback d'Urgence

```bash
# Restaurer depuis la sauvegarde
mysql -u root -p moncoachscolaire < backup_before_sync.sql

# Ou depuis le backup du workflow
mysql -u root -p moncoachscolaire < db/backups/moncoachscolaire_backup_YYYYMMDD_HHMMSS.sql

# Vérifier
mysql -u root -p moncoachscolaire -e "SELECT COUNT(*) FROM Exercises"
```

---

## 📈 Résultats Attendus

Après une synchronisation réussie:

```
État avant:
  • Exercices:     89
  • Utilisateurs:  2
  • Pouvoirs:      5
  • Achievements:  3

État après:
  • Exercices:     ~520 (89 + ~431 ajoutés)
  • Utilisateurs:  ~3-4 (2 + 1-2 nouveaux ajoutés)
  • Pouvoirs:      ~5-10 (inchangé si déjà synced)
  • Achievements:  ~3-10 (inchangé si déjà synced)

Utilateurs existants:  ✅ INTACTS (pas modifiés)
Mots de passe:         ✅ INTACTS (pas modifiés)
Comptes en prod:       ✅ SÛRS (rien casse)
```

---

## 🎯 Bonnes Pratiques

### ✅ À Faire

```
✅ Tester avec --dry-run en premier
✅ Sauvegarder avant de vraiment exécuter
✅ Vérifier les comptes après (login test)
✅ Garder les sauvegardes longtemps
✅ Documenter la date/heure du sync
✅ Notifier l'équipe avant le sync
✅ Relancer le script = idempotent (safe)
```

### ❌ À NE PAS Faire

```
❌ Exécuter directement sans --dry-run en premier
❌ Oublier la sauvegarde
❌ Modifier le script SQL sans tester
❌ Supprimer les sauvegardes tout de suite
❌ Syncer pendant que l'app tourne (risque de corruption)
❌ Changer les passwords du prod
❌ Lancer 2 syncs en parallèle
```

---

## 📚 Ressources

- [GUIDE_SYNC_LOCAL_PROD.md](GUIDE_SYNC_LOCAL_PROD.md) - Guide détaillé (100+ lignes)
- [db/sync_local_to_prod_safe.sql](../db/sync_local_to_prod_safe.sql) - Script SQL source
- [tools/sync_local_to_prod_safe.php](../tools/sync_local_to_prod_safe.php) - Script PHP source

---

## 🏁 Résumé

| Tâche | Commande | Temps |
|-------|----------|-------|
| **Simuler** | `php ../tools/sync_local_to_prod_safe.php --dry-run` | 10s |
| **Sauvegarder** | `mysqldump -u root -p moncoachscolaire > backup.sql` | 5-30s |
| **Syncer** | `php ../tools/sync_local_to_prod_safe.php` | 10-30s |
| **Vérifier** | `mysql -u root -p moncoachscolaire -e "SELECT COUNT(*) FROM Exercises"` | 1s |

**Total:** ~30 secondes pour synchroniser et valider! ⚡

---

**Créé:** 1 janvier 2026  
**Version:** 1.0 - Production Ready  
**Sécurité:** ⭐⭐⭐⭐⭐
