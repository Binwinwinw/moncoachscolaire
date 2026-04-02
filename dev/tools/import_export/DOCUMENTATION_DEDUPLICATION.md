# 🧹 Documentation : Dédoublonnage des Exercices

---

## 🎯 Objectif

Ce système permet de :
1. **Extraire** tous les exercices déjà présents dans la base de données
2. **Comparer** avec les exercices des fichiers JSON migrés
3. **Détecter** tous les types de doublons
4. **Générer** un fichier JSON nettoyé et dédoublonné
5. **Créer** un script SQL pour supprimer les doublons de la BDD

---

## 📂 Workflow Complet

```
┌─────────────────────────────────────────────────────────────┐
│  1. EXTRACTION BDD → JSON                                   │
│     php export_exercises_from_db.php                        │
│     → dev/db/json/schema/exercices/exercises_from_database.json
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  2. COLLECTE DES JSON MIGRÉS (si pas déjà fait)            │
│     php collect_exercises.php                               │
│     → dev/db/json/schema/exercices/unified_exercises.json   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  3. ANALYSE & DÉDOUBLONNAGE                                 │
│     php deduplicate_exercises.php                           │
│     → Génère :                                              │
│       • exercises_deduplicated.json (fichier propre)        │
│       • deduplication_report.txt (rapport détaillé)         │
│       • delete_duplicates.sql (script de nettoyage BDD)     │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  4. SAUVEGARDE BDD (IMPORTANT !)                            │
│     mysqldump -u user -p database exercises > backup.sql    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  5. SUPPRESSION DOUBLONS BDD (OPTIONNEL)                    │
│     Éditer delete_duplicates.sql                            │
│     Décommenter COMMIT; si OK                               │
│     Exécuter le script SQL                                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  6. IMPORT FINAL DU FICHIER NETTOYÉ                         │
│     php import_unified_exercises.php                        │
│     (avec exercises_deduplicated.json)                      │
└─────────────────────────────────────────────────────────────┘
```

---

## 🚀 Étape 1 : Extraction BDD → JSON

### Script : `export_exercises_from_db.php`

**Fonction :**
- Extrait TOUS les exercices de la table `exercises`
- Génère `exercises_from_database.json`
- Analyse complète : niveaux, matières, difficulté, types de réponse
- Détecte les identifiers dupliqués **dans la BDD**
- Génère un rapport détaillé

### Utilisation

```bash
php dev/tools/import_export/export_exercises_from_db.php
```

### Sortie Attendue

```
═══════════════════════════════════════════════════════════════
  EXPORT EXERCICES — Base de Données → JSON
═══════════════════════════════════════════════════════════════

🔌 Connexion à la base de données...
✅ Connexion établie
───────────────────────────────────────────────────────────────

📊 Extraction des exercices de la table 'exercises'...
✅ 543 exercices extraits
───────────────────────────────────────────────────────────────

💾 Écriture du fichier JSON...
✅ Fichier créé : .../exercises_from_database.json
📊 Taille       : 156.23 KB
───────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════
  RÉCAPITULATIF FINAL
═══════════════════════════════════════════════════════════════
📝 Exercices extraits     : 543
✅ Actifs                 : 512
❌ Inactifs               : 31
🔑 Avec identifier        : 540
⚠️  Sans identifier       : 3
🔄 Identifiers dupliqués  : 5
───────────────────────────────────────────────────────────────

📚 Répartition par niveau :
  • 6eme                : 120 exercices
  • 5eme                :  98 exercices
  • 1ere                :  87 exercices
  • terminale           :  76 exercices
  • bac                 :  65 exercices
  • 2nd                 :  54 exercices
  • 3eme                :  28 exercices
  • 4eme                :  15 exercices

📖 Répartition par matière :
  • Anglais             : 298 exercices
  • Francais            : 245 exercices

⚠️  IDENTIFIERS DUPLIQUÉS DÉTECTÉS :
───────────────────────────────────────────────────────────────
  • ANGLAIS-6EME-EXERCISE-042 : 2 occurrences
  • FRANCAIS-5EME-ETUDE-018 : 2 occurrences
  • ANGLAIS-1ERE-EXERCISE-056 : 3 occurrences
  • FRANCAIS-BAC-EXERCICE-089 : 2 occurrences
  • ANGLAIS-TERMINALE-012 : 2 occurrences

💡 Action recommandée : Utiliser le script de dédoublonnage

═══════════════════════════════════════════════════════════════
✅ Export terminé avec succès !
═══════════════════════════════════════════════════════════════

📄 Rapport détaillé : dev/reports/export_exercises_from_db.txt
```

---

## 🧹 Étape 2 : Analyse & Dédoublonnage

### Script : `deduplicate_exercises.php`

**Fonction :**
- Compare `exercises_from_database.json` et `unified_exercises.json`
- Détecte **5 types de doublons** :
  1. **Identifiers communs** (même identifier dans BDD et JSON)
  2. **Doublons dans BDD** (même identifier plusieurs fois dans la BDD)
  3. **Doublons dans JSON** (même identifier plusieurs fois dans les JSON)
  4. **BDD uniquement** (exercices présents seulement dans la BDD)
  5. **JSON uniquement** (exercices présents seulement dans les JSON)
- Génère un fichier **dédoublonné** : `exercises_deduplicated.json`
- Génère un script SQL de suppression : `delete_duplicates.sql`
- Génère un rapport détaillé : `deduplication_report.txt`

### Stratégie de Dédoublonnage

```
PRIORITÉ : Les JSON migrés (version la plus récente)

1. Prendre TOUS les exercices des JSON migrés
2. Ajouter UNIQUEMENT les exercices BDD qui n'existent PAS dans les JSON
3. En cas de doublon dans une même source, garder le premier
```

### Utilisation

```bash
php dev/tools/import_export/deduplicate_exercises.php
```

### Sortie Attendue

```
═══════════════════════════════════════════════════════════════
  DÉTECTION & DÉDOUBLONNAGE D'EXERCICES
═══════════════════════════════════════════════════════════════

📄 Chargement des exercices de la BDD...
✅ 543 exercices chargés depuis la BDD
📄 Chargement des exercices des JSON migrés...
✅ 1011 exercices chargés depuis les JSON migrés
───────────────────────────────────────────────────────────────

🔍 Indexation des exercices...
✅ BDD  : 538 identifiers uniques
✅ JSON : 1011 identifiers uniques
───────────────────────────────────────────────────────────────

🔎 Détection des doublons...

✅ Analyse terminée
───────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════
  RÉCAPITULATIF DE L'ANALYSE
═══════════════════════════════════════════════════════════════
📝 Total BDD              : 543 exercices
📝 Total JSON             : 1011 exercices
───────────────────────────────────────────────────────────────
🔗 Identifiers communs    : 398
🔹 Uniquement dans BDD    : 140
🔹 Uniquement dans JSON   : 613
───────────────────────────────────────────────────────────────
🔄 Doublons dans BDD      : 5 identifiers
🔄 Doublons dans JSON     : 0 identifiers
───────────────────────────────────────────────────────────────

💾 Création du fichier dédoublonné...
✅ Fichier créé : .../exercises_deduplicated.json
📊 Taille       : 298.45 KB
📝 Exercices    : 1151 (dédoublonnés)
───────────────────────────────────────────────────────────────

🗑️  Génération du script SQL de suppression des doublons...
✅ Script SQL créé : .../delete_duplicates.sql
🗑️  Suppressions prévues : 403 enregistrements
───────────────────────────────────────────────────────────────

═══════════════════════════════════════════════════════════════
✅ Analyse terminée avec succès !
═══════════════════════════════════════════════════════════════

📄 Rapport détaillé : dev/reports/deduplication_report.txt

═══════════════════════════════════════════════════════════════
  PROCHAINES ÉTAPES
═══════════════════════════════════════════════════════════════

1️⃣  SAUVEGARDER LA BASE DE DONNÉES
    mysqldump -u user -p database exercises > exercises_backup.sql

2️⃣  SUPPRIMER LES DOUBLONS (OPTIONNEL)
    Éditer et exécuter : dev/reports/delete_duplicates.sql
    ⚠️  Vérifier le contenu avant d'exécuter !

3️⃣  IMPORTER LE FICHIER DÉDOUBLONNÉ
    php dev/tools/import_export/import_unified_exercises.php
    (Utiliser exercises_deduplicated.json comme source)

4️⃣  VÉRIFIER LE RÉSULTAT
    SELECT COUNT(*), subject, level FROM exercises GROUP BY subject, level;

═══════════════════════════════════════════════════════════════
```

---

## 📊 Comprendre le Rapport

### Exemple de Rapport Détaillé

```
═══════════════════════════════════════════════════════════════
  RAPPORT DE DÉDOUBLONNAGE — 2026-02-14 15:32:18
═══════════════════════════════════════════════════════════════

Total BDD              : 543 exercices
Total JSON             : 1011 exercices
Identifiers communs    : 398
Uniquement dans BDD    : 140
Uniquement dans JSON   : 613
Doublons dans BDD      : 5 identifiers
Doublons dans JSON     : 0 identifiers

Fichier dédoublonné    : .../exercises_deduplicated.json
Exercices finaux       : 1151
Script SQL             : .../delete_duplicates.sql
Suppressions prévues   : 403

═══════════════════════════════════════════════════════════════
DOUBLONS DANS LA BDD
═══════════════════════════════════════════════════════════════
Identifier : ANGLAIS-6EME-EXERCISE-042 (2 occurrences)
  → ID 123 : Les pronoms personnels
  → ID 456 : Les pronoms personnels (correction)

Identifier : ANGLAIS-1ERE-EXERCISE-056 (3 occurrences)
  → ID 234 : Present Perfect
  → ID 567 : Present Perfect - Version 2
  → ID 890 : Present Perfect (final)

═══════════════════════════════════════════════════════════════
IDENTIFIERS PRÉSENTS DANS BDD ET JSON (choix : garder JSON)
═══════════════════════════════════════════════════════════════
Identifier : ANGLAIS-6EME-EXERCISE-001
  → BDD  : 1 occurrence(s)
  → JSON : 1 occurrence(s)

Identifier : FRANCAIS-5EME-ETUDE-012
  → BDD  : 1 occurrence(s)
  → JSON : 1 occurrence(s)

... (396 autres identifiers communs)
```

### Interprétation

| Statistique | Signification | Action |
|-------------|---------------|--------|
| **Identifiers communs : 398** | 398 exercices existent dans BDD ET JSON | Garder la version JSON, supprimer la version BDD |
| **Uniquement dans BDD : 140** | 140 exercices existent SEULEMENT dans la BDD | Les conserver dans le fichier final |
| **Uniquement dans JSON : 613** | 613 nouveaux exercices dans les JSON | Les ajouter lors de l'import |
| **Doublons dans BDD : 5** | 5 identifiers dupliqués dans la BDD | Supprimer les doublons (garder le plus ancien ID) |

**Résultat final :**
- 1011 exercices JSON (tous conservés)
- 140 exercices BDD uniquiques (ajoutés)
- **= 1151 exercices finaux** (sans doublons)

---

## 🗑️ Script SQL de Suppression

### Exemple de `delete_duplicates.sql`

```sql
-- ═══════════════════════════════════════════════════════════════
-- Script de suppression des doublons dans la table exercises
-- Généré le : 2026-02-14 15:32:18
-- ═══════════════════════════════════════════════════════════════

-- ATTENTION : Ce script va supprimer des enregistrements !
-- Pensez à faire une sauvegarde de la table exercises avant d'exécuter.
-- Commande de sauvegarde : mysqldump -u user -p database exercises > exercises_backup.sql

START TRANSACTION;

-- ───────────────────────────────────────────────────────────────
-- Suppression des doublons dans la BDD (même identifier)
-- ───────────────────────────────────────────────────────────────

-- Identifier : ANGLAIS-6EME-EXERCISE-042 (2 occurrences)
DELETE FROM exercises WHERE id = 456; -- Doublon de ID 123

-- Identifier : ANGLAIS-1ERE-EXERCISE-056 (3 occurrences)
DELETE FROM exercises WHERE id = 567; -- Doublon de ID 234
DELETE FROM exercises WHERE id = 890; -- Doublon de ID 234

-- ───────────────────────────────────────────────────────────────
-- Suppression des exercices BDD également présents dans les JSON migrés
-- (Stratégie : garder la version JSON, supprimer la version BDD)
-- ───────────────────────────────────────────────────────────────

DELETE FROM exercises WHERE id = 123; -- Identifier: ANGLAIS-6EME-EXERCISE-001 (existe dans JSON)
DELETE FROM exercises WHERE id = 234; -- Identifier: FRANCAIS-5EME-ETUDE-012 (existe dans JSON)
... (396 autres suppressions)

-- ═══════════════════════════════════════════════════════════════
-- TOTAL : 403 enregistrements à supprimer
-- ═══════════════════════════════════════════════════════════════

-- Si vous êtes sûr, décommentez la ligne suivante :
-- COMMIT;

-- Sinon, annulez la transaction :
ROLLBACK;
```

### Utilisation du Script SQL

```bash
# 1. Sauvegarder la BDD
mysqldump -u root -p moncoachscolaire exercises > exercises_backup.sql

# 2. Éditer le script SQL
# Vérifier le contenu de dev/reports/delete_duplicates.sql

# 3. Exécuter (attention !)
mysql -u root -p moncoachscolaire < dev/reports/delete_duplicates.sql

# 4. Si tout est OK, modifier le script pour décommenter COMMIT
# Réexécuter
```

---

## ✅ Checklist Complète

### Avant Dédoublonnage
- [ ] La base de données est accessible
- [ ] `export_exercises_from_db.php` s'exécute sans erreur
- [ ] `collect_exercises.php` a généré `unified_exercises.json`
- [ ] Les deux fichiers JSON existent et sont valides

### Pendant Dédoublonnage
- [ ] `deduplicate_exercises.php` s'exécute sans erreur
- [ ] Le rapport `deduplication_report.txt` est généré
- [ ] Le fichier `exercises_deduplicated.json` est créé
- [ ] Le script SQL `delete_duplicates.sql` est généré

### Avant Suppression BDD
- [ ] ⚠️  **SAUVEGARDE BDD EFFECTUÉE** ⚠️
- [ ] Le script SQL a été relu et validé
- [ ] Le nombre de suppressions est cohérent
- [ ] Les ID à supprimer ont été vérifiés

### Après Suppression BDD (Optionnel)
- [ ] La transaction SQL s'est exécutée correctement
- [ ] `SELECT COUNT(*) FROM exercises` affiche le nombre attendu
- [ ] Aucune erreur de clé étrangère

### Import Final
- [ ] Modifier `import_unified_exercises.php` pour pointer vers `exercises_deduplicated.json`
- [ ] Dry-run de l'import : 0 erreur
- [ ] Import réel terminé avec succès
- [ ] Vérification : `SELECT COUNT(*), subject, level FROM exercises GROUP BY subject, level`
- [ ] Interface utilisateur : les exercices s'affichent correctement

---

## 🔧 Personnalisation

### Changer la Stratégie de Priorité

**Par défaut :** JSON prioritaire (version la plus récente)

**Pour prioriser la BDD :**

Modifier `deduplicate_exercises.php` ligne ~200 :

```php
// ───── AVANT (priorité JSON) ─────
foreach ($jsonExercises as $ex) {
    $id = $ex['identifier'] ?? null;
    if ($id && !isset($processedIdentifiers[$id])) {
        $cleanExercises[] = $ex;
        $processedIdentifiers[$id] = true;
    }
}

foreach ($dbExercises as $ex) {
    $id = $ex['identifier'] ?? null;
    if ($id && !isset($processedIdentifiers[$id])) {
        $cleanExercises[] = $ex;
        $processedIdentifiers[$id] = true;
    }
}

// ───── APRÈS (priorité BDD) ─────
foreach ($dbExercises as $ex) {
    $id = $ex['identifier'] ?? null;
    if ($id && !isset($processedIdentifiers[$id])) {
        $cleanExercises[] = $ex;
        $processedIdentifiers[$id] = true;
    }
}

foreach ($jsonExercises as $ex) {
    $id = $ex['identifier'] ?? null;
    if ($id && !isset($processedIdentifiers[$id])) {
        $cleanExercises[] = $ex;
        $processedIdentifiers[$id] = true;
    }
}
```

---

## 📞 Support

**En cas de problème :**
1. Consulter les rapports : `dev/reports/deduplication_report.txt`
2. Vérifier la sauvegarde BDD avant toute suppression
3. Tester avec `--dry-run` sur l'import final
4. Vérifier les logs SQL en cas d'erreur de suppression

---

## 🔗 Fichiers Générés

| Fichier | Description | Taille estimée |
|---------|-------------|----------------|
| `exercises_from_database.json` | Export de la BDD | ~150 KB |
| `unified_exercises.json` | JSON migrés fusionnés | ~250 KB |
| `exercises_deduplicated.json` | Fichier final nettoyé | ~300 KB |
| `deduplication_report.txt` | Rapport détaillé | ~10 KB |
| `delete_duplicates.sql` | Script SQL de nettoyage | ~15 KB |

---

**📅 Dernière mise à jour :** 2026-02-14  
**✍️ Auteur :** MonCoachScolaire — Équipe Technique
