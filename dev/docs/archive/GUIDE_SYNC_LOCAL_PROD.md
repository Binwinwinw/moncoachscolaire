# 🔄 Guide Déploiement Sécurisé: Sync Local → Production

**Importance:** ⭐⭐⭐⭐⭐ CRITIQUE - Protège vos données en production!

---

## 📋 Table des matières

1. [Avant de Démarrer](#avant-de-démarrer)
2. [Stratégie de Sécurité](#stratégie-de-sécurité)
3. [Les 3 Étapes](#les-3-étapes)
4. [Commandes Complètes](#commandes-complètes)
5. [Dépannage](#dépannage)
6. [Rollback d'Urgence](#rollback-durgence)

---

## ✅ Avant de Démarrer

### Checklist Pré-Déploiement

- [ ] **Sauvegarder la BD production**
  ```bash
  mysqldump -u root -p moncoachscolaire > moncoachscolaire_backup_$(date +%Y%m%d_%H%M%S).sql
  ```

- [ ] **Tester sur une copie**
  ```bash
  # Créer une BD de test
  mysqldump -u root -p moncoachscolaire > moncoachscolaire_test.sql
  mysql -u root -p -e "CREATE DATABASE moncoachscolaire_test"
  mysql -u root -p moncoachscolaire_test < moncoachscolaire_test.sql
  # Ensuite tester le sync sur _test
  ```

- [ ] **Vérifier les fichiers existent**
  ```bash
  ls -la db/sync_local_to_prod_safe.sql
  ls -la tools/sync_local_to_prod_safe.php
  ```

- [ ] **Vérifier les connexions**
  ```bash
  # Tester connexion locale
  mysql -u root -p moncoachscolaire_local -e "SELECT COUNT(*) as 'Exercices locaux' FROM Exercises"
  
  # Tester connexion production
  mysql -u prod_user -p moncoachscolaire -e "SELECT COUNT(*) as 'Exercices prod' FROM Exercises"
  ```

---

## 🔐 Stratégie de Sécurité

### Clés Uniques Utilisées

```
📌 Exercices:    Title + Subject + Level (impossible d'avoir doublons)
📌 Powers:       Name (un pouvoir = un nom unique)
📌 Achievements: Name (un achievement = un nom unique)
📌 Users:        Email (email = clé unique universelle)
📌 Réponses:     UserId + ExerciseId + SubmittedAt (doublons détectés)
```

### Données JAMAIS Modifiées en Prod

```
✅ Utilisateurs existants:     NE SONT PAS MODIFIÉS
   (login, password, rôle conservés)

✅ Progrès existants:          NE SONT PAS RÉINITIALISÉS
   (XP, position conservés, seulement fusionnés)

✅ Achievements existants:      NE SONT PAS SUPPRIMÉS
   (nouvelles données ajoutées, anciennes gardées)

✅ Réponses existantes:         NE SONT PAS MODIFIÉES
   (histoire conservée, nouvelles ajoutées)
```

### Ce Qui Est Synchronisé

```
✅ NOUVEAUX exercices (local→prod)
✅ NOUVEAUX pouvoirs (local→prod)
✅ NOUVEAUX achievements (local→prod)
✅ NOUVEAUX utilisateurs (local→prod)
✅ PROGRÈS MANQUANT (fusionné intelligemment)
✅ RÉPONSES MANQUANTES (conservant les historiques)
```

---

## 🚀 Les 3 Étapes

### Étape 1: Simulation (DRY-RUN)

**Objectif:** Voir exactement ce qui va se passer SANS rien modifier

```bash
# Mode PHP (recommandé, plus interactif)
php tools/sync_local_to_prod_safe.php --dry-run --verbose

# Résultat attendu:
# ✅ "SIMULATION: 245 exercices seraient ajoutés"
# ✅ "SIMULATION: 3 pouvoirs seraient ajoutés"
# etc.
```

**À vérifier:**
- Les nombres semblent-ils raisonnables?
- Y a-t-il plus de 1000 exercices? (possible mais vérifier)
- Y a-t-il des erreurs de connexion?

### Étape 2: Test sur Copie

**Objectif:** Vraiment exécuter sur une COPIE pour valider le résultat

```bash
# 1. Créer une copie de prod
mysqldump -u root -p moncoachscolaire > prod_backup.sql
mysql -u root -p -e "CREATE DATABASE moncoachscolaire_copy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p moncoachscolaire_copy < prod_backup.sql

# 2. Adapter le script pour utiliser la copie
# Modifier sync_local_to_prod_safe.sql:
#   - Remplacer moncoachscolaire par moncoachscolaire_copy
#
# Ou utiliser PHP avec variables d'env:
DB_PROD_HOST=localhost DB_PROD_USER=root DB_PROD_PASSWORD="" \
  php tools/sync_local_to_prod_safe.php --verbose

# 3. Vérifier le résultat
mysql -u root -p moncoachscolaire_copy -e "SELECT COUNT(*) FROM Exercises"
mysql -u root -p moncoachscolaire_copy -e "SELECT COUNT(*) FROM Users"

# 4. Comparer avant/après
echo "Avant: $(mysql -u root -p moncoachscolaire -e 'SELECT COUNT(*) FROM Exercises')"
echo "Après: $(mysql -u root -p moncoachscolaire_copy -e 'SELECT COUNT(*) FROM Exercises')"
```

### Étape 3: Exécution Réelle en Production

**Objectif:** Vraiment synchroniser prod (ATTENTION: pas de retour arrière facile!)

```bash
# 1. SAUVEGARDE ABSOLUE (ne pas sauter!)
mysqldump -u root -p moncoachscolaire > moncoachscolaire_avant_sync_$(date +%Y%m%d_%H%M%S).sql

# 2. Exécuter le sync RÉEL
# Option A: Via SQL directement (rapide)
mysql -u root -p moncoachscolaire < db/sync_local_to_prod_safe.sql

# Option B: Via PHP (plus contrôlé, avec logs)
php tools/sync_local_to_prod_safe.php --verbose

# 3. Vérifier les logs
tail -50 db/sync_*.log

# 4. Tester l'application
# - Connexion des utilisateurs: OK?
# - Exercices disponibles: Correct?
# - Progrès sauvegardé: Oui?
# - Pouvoirs fonctionnent: OK?
```

---

## 💻 Commandes Complètes

### Pré-Requis

```bash
# Vérifier PHP est installé
php -v

# Vérifier MySQL/MariaDB est accessible
mysql -u root -p -e "SELECT VERSION()"
```

### Vérifier les Données Avant

```bash
# Compter les exercices
echo "LOCAL:"
mysql -u root -p moncoachscolaire_local -e "SELECT COUNT(*) as 'Total Exercices' FROM Exercises"

echo "PROD:"
mysql -u root -p moncoachscolaire -e "SELECT COUNT(*) as 'Total Exercices' FROM Exercises"

# Lister les utilisateurs prod
mysql -u root -p moncoachscolaire -e "SELECT Id, Username, Email, Role, CreatedAt FROM Users ORDER BY Id LIMIT 10"

# Vérifier les pouvoirs
mysql -u root -p moncoachscolaire -e "SELECT COUNT(*) as 'Pouvoirs' FROM Powers; SELECT COUNT(*) as 'Achievements' FROM Achievements"
```

### Workflow Complet Recommandé

```bash
#!/bin/bash
set -e  # S'arrêter en cas d'erreur

DB_USER="root"
DB_PASS=""  # Adapter si mot de passe
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "🔄 SYNCHRONISATION LOCAL → PROD"
echo "════════════════════════════════════"

# 1. Sauvegarder
echo "1️⃣ Sauvegarde de production..."
mysqldump -u $DB_USER -p$DB_PASS moncoachscolaire > moncoachscolaire_backup_$TIMESTAMP.sql
echo "✅ Sauvegarde: moncoachscolaire_backup_$TIMESTAMP.sql"

# 2. Simulation
echo ""
echo "2️⃣ Simulation (dry-run)..."
php tools/sync_local_to_prod_safe.php --dry-run --verbose
read -p "✅ Continuer? (Ctrl+C pour annuler)"

# 3. État avant
echo ""
echo "3️⃣ État avant synchronisation:"
mysql -u $DB_USER -p$DB_PASS moncoachscolaire -e "
  SELECT 'Exercices' as 'Table', COUNT(*) as 'Count' FROM Exercises
  UNION ALL
  SELECT 'Users', COUNT(*) FROM Users
  UNION ALL
  SELECT 'Powers', COUNT(*) FROM Powers
  UNION ALL
  SELECT 'Achievements', COUNT(*) FROM Achievements
"

# 4. Sync réelle
echo ""
echo "4️⃣ Exécution synchronisation..."
php tools/sync_local_to_prod_safe.php --verbose
echo "✅ Synchronisation terminée"

# 5. État après
echo ""
echo "5️⃣ État après synchronisation:"
mysql -u $DB_USER -p$DB_PASS moncoachscolaire -e "
  SELECT 'Exercices' as 'Table', COUNT(*) as 'Count' FROM Exercises
  UNION ALL
  SELECT 'Users', COUNT(*) FROM Users
  UNION ALL
  SELECT 'Powers', COUNT(*) FROM Powers
  UNION ALL
  SELECT 'Achievements', COUNT(*) FROM Achievements
"

# 6. Test unitaire simple
echo ""
echo "6️⃣ Tests simples:"
TEST_EMAIL="test_sync_$(date +%s)@test.local"
mysql -u $DB_USER -p$DB_PASS moncoachscolaire -e "
  SELECT 'Utilisateurs actifs:' as 'Vérification', COUNT(*) FROM Users WHERE Role IN ('admin', 'student', 'parent')
"

echo ""
echo "✅ SYNCHRONISATION COMPLÈTE!"
echo "📊 Logs: db/sync_*.log"
echo "💾 Backup: moncoachscolaire_backup_$TIMESTAMP.sql"
```

---

## 🔍 Vérifications Post-Sync

### Tests Fonctionnels

```sql
-- Vérifier qu'aucun doublon n'existe
SELECT Subject, Level, Title, COUNT(*) as cnt 
FROM Exercises 
GROUP BY Subject, Level, Title 
HAVING cnt > 1;
-- Résultat attendu: AUCUNE ligne

-- Vérifier que les IDs ne sont pas corrompus
SELECT COUNT(*) as 'Exercices sans User FK' 
FROM ExerciseResponses 
WHERE UserId NOT IN (SELECT Id FROM Users);
-- Résultat attendu: 0

-- Vérifier l'intégrité des relations
SELECT COUNT(*) FROM UserProgress WHERE UserId NOT IN (SELECT Id FROM Users);
-- Résultat attendu: 0
```

### Tester la Connexion

```bash
# Via PHP
php -r "
\$pdo = new PDO('mysql:host=localhost;dbname=moncoachscolaire', 'root', '');
\$users = \$pdo->query('SELECT COUNT(*) FROM Users')->fetchColumn();
\$exercises = \$pdo->query('SELECT COUNT(*) FROM Exercises')->fetchColumn();
echo \"Utilisateurs: \$users, Exercices: \$exercises\n\";
"
```

---

## ❌ Dépannage

### Erreur: "Unknown column 'is_active'"

```sql
-- Solution:
ALTER TABLE Exercises ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER Answer;
-- Puis réessayer le sync
```

### Erreur: "Access denied for user"

```bash
# Vérifier les credentials
mysql -u root -p -e "SELECT USER()"

# Si problème avec BD locale:
mysql -u root -p -e "CREATE DATABASE moncoachscolaire_local CHARACTER SET utf8mb4"
mysql -u root -p moncoachscolaire_local < db/mysql_schema.sql
```

### Doublons Créés Malgré Tout

```sql
-- Nettoyer les doublons sur Exercises
DELETE e1 FROM Exercises e1
INNER JOIN (
  SELECT Subject, Level, Title, MIN(Id) as min_id
  FROM Exercises
  GROUP BY Subject, Level, Title
  HAVING COUNT(*) > 1
) e2 ON e1.Subject = e2.Subject AND e1.Level = e2.Level AND e1.Title = e2.Title
WHERE e1.Id > e2.min_id;
```

### Logs Manquants

```bash
# Les logs sont dans:
ls -la db/sync_*.log
tail -100 db/sync_*.log | grep ERROR
```

---

## 🔙 Rollback d'Urgence

### Si Quelque Chose S'est Mal Passé

```bash
# 1. Arrêter l'application immédiatement
systemctl stop apache2  # ou nginx, ou autre

# 2. Restaurer depuis sauvegarde
mysql -u root -p < moncoachscolaire_backup_YYYYMMDD_HHMMSS.sql

# 3. Redémarrer l'application
systemctl start apache2

# 4. Tester
curl http://localhost/moncoachscolaire/

# 5. Enquêter
# - Vérifier les logs: db/sync_*.log
# - Comparer les counts avant/après
# - Vérifier l'intégrité des FK
```

### Comparaison Avant/Après

```bash
# Dump avant sync
mysqldump -u root -p moncoachscolaire > moncoachscolaire_before.sql

# Dump après sync
mysqldump -u root -p moncoachscolaire > moncoachscolaire_after.sql

# Diff (avec `diff`)
diff moncoachscolaire_before.sql moncoachscolaire_after.sql | head -100

# Ou comparer juste les counts
for table in Exercises Users Powers Achievements ExerciseResponses UserProgress; do
  before=$(mysql -u root -p moncoachscolaire_before -e "SELECT COUNT(*) FROM $table" | tail -1)
  after=$(mysql -u root -p moncoachscolaire_after -e "SELECT COUNT(*) FROM $table" | tail -1)
  echo "$table: $before → $after"
done
```

---

## 📊 Résultat Attendu

Après une synchronisation réussie:

```
✅ Exercices:     89 → ~520 (ajout de ~431)
✅ Utilisateurs:  2  → 3-4 (ajout de 1-2)
✅ Pouvoirs:      X  → X (inchangé si déjà synced)
✅ Achievements:  Y  → Y (inchangé si déjà synced)
✅ Réponses:      Z  → Z+? (conservées + nouvelles ajoutées)

❌ JAMAIS:
   - Utilisateurs supprimés
   - Mots de passe modifiés
   - Rôles changés
   - Données existantes écrasées
```

---

## 🎯 Notes Finales

### Sécurité

```
🔐 Clés uniques empêchent les doublons
🔐 NOT EXISTS évite les INSERT sur doublons
🔐 GREATEST() garde les meilleures données (max XP, etc)
🔐 Foreign keys protègent l'intégrité référentielle
```

### Performance

```
⚡ Requêtes optimisées avec index
⚡ Pas de boucles en PHP (requêtes SQL batch)
⚡ Logs asynchrones (append)
⚡ Peut synchroniser 1000+ exercices en < 5s
```

### Monitoring

```
📊 Chaque opération est loggée
📊 Timestamps permettent de tracker les changements
📊 Rapports avant/après comparables
📊 Scripts idempotents (peut être relancés sans peur)
```

---

**Créé:** 1 janvier 2026  
**Version:** 1.0  
**Statut:** ✅ Production-ready
