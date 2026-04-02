# 📋 Documentation - Scripts de Refactorisation des Exercices JSON

## 🎯 Objectif Global

Moderniser tous les fichiers JSON des exercices pour les aligner avec le **nouveau schéma** de la table `exercises` de la base de données.

---

## 📂 Fichiers Créés

### **1. export_schema.php**
📄 Emplacement : `dev/tools/exercises/export_schema.php`  
🎯 Fonction : Exporte le schéma actuel de la table `exercises`  
📊 Sortie : `dev/reports/exercises_schema.json`

### **2. migrate_exercises_json.php**
📄 Emplacement : `dev/tools/exercises/migrate_exercises_json.php`  
🎯 Fonction : Migre tous les anciens JSON vers le nouveau schéma  
📊 Sortie : Fichiers `*.migrated.json` + `dev/reports/migration_report.txt`

---

## 🚀 Guide d'Utilisation

### **Étape 1 : Exporter le Schéma**

```bash
cd /chemin/vers/moncoachscolaire
php dev/tools/exercises/export_schema.php
```

**Sortie attendue :**
```
═══════════════════════════════════════════════════════════════
  EXPORT SCHEMA - Table exercises
═══════════════════════════════════════════════════════════════

🔌 Connexion à la base de données... ✅ OK
📊 Extraction du schéma de la table 'exercises'... ✅ OK (25 colonnes)

📋 Liste des colonnes :
────────────────────────────────────────────────────────────────
FIELD                     TYPE                 NULL       KEY             DEFAULT
────────────────────────────────────────────────────────────────
id                        int(11)              NO         PRI             NULL
title                     varchar(255)         NO         -               NULL
subject                   varchar(100)         NO         -               NULL
level                     varchar(50)          NO         -               NULL
...

💾 Sauvegarde du schéma... ✅ OK
📄 Fichier généré : dev/reports/exercises_schema.json
📦 Taille : 3,456 octets

═══════════════════════════════════════════════════════════════
  ✅ EXPORT TERMINÉ AVEC SUCCÈS
═══════════════════════════════════════════════════════════════

📊 Résumé :
   • Table : exercises
   • Colonnes : 25
   • Fichier : dev/reports/exercises_schema.json

🚀 Prochaine étape :
   php dev/tools/exercises/migrate_exercises_json.php
```

**Fichier généré** : `dev/reports/exercises_schema.json`

```json
{
  "table_name": "exercises",
  "exported_at": "2026-02-14 15:30:00",
  "total_columns": 25,
  "columns": {
    "id": {
      "field": "id",
      "type": "int(11)",
      "php_type": "integer",
      "nullable": false,
      "key": "PRI",
      "default": "NULL",
      "extra": "auto_increment"
    },
    "title": {
      "field": "title",
      "type": "varchar(255)",
      "php_type": "string",
      "nullable": false,
      "key": "-",
      "default": "NULL",
      "extra": ""
    },
    ...
  }
}
```

---

### **Étape 2 : Simulation de Migration (DRY-RUN)**

Avant de modifier les fichiers, teste la migration :

```bash
php dev/tools/exercises/migrate_exercises_json.php --dry-run
```

**Sortie attendue :**
```
═══════════════════════════════════════════════════════════════
  MIGRATE EXERCISES JSON - Adaptation Nouveau Schéma
═══════════════════════════════════════════════════════════════

⚠️  MODE DRY-RUN : Aucun fichier ne sera modifié

📋 Vérification des prérequis...
─────────────────────────────────────────────────────────────────
✅ Dossier JSON : dev/db/json/exercices
✅ Schéma trouvé : dev/reports/exercises_schema.json
✅ Schéma chargé : 25 colonnes

🔍 Scan des fichiers JSON...
─────────────────────────────────────────────────────────────────
✅ Fichiers trouvés : 150

🔄 Migration en cours...
─────────────────────────────────────────────────────────────────

[1/150] mathematiques_6eme_001.json... ✅ OK
[2/150] francais_5eme_002.json... ✅ OK
[3/150] histoire_4eme_003.json... ⏭️  SKIP (déjà migré)
...

═══════════════════════════════════════════════════════════════
  📊 RÉSUMÉ DE LA MIGRATION
═══════════════════════════════════════════════════════════════

Fichiers traités : 150
✅ Succès : 145
⏭️  Ignorés : 3
❌ Erreurs : 2

⚠️  Mode DRY-RUN : Aucun fichier généré
```

---

### **Étape 3 : Migration Réelle**

Après validation du dry-run, lance la migration :

```bash
php dev/tools/exercises/migrate_exercises_json.php
```

**Résultat :**
- Génère des fichiers `*.migrated.json` dans `dev/db/json/exercices`
- Crée un rapport détaillé : `dev/reports/migration_report.txt`

---

### **Étape 4 : Forcer la Re-migration**

Si des fichiers `.migrated.json` existent déjà et tu veux les réécrire :

```bash
php dev/tools/exercises/migrate_exercises_json.php --force
```

---

## 📊 Structure du Rapport de Migration

**Fichier** : `dev/reports/migration_report.txt`

```
═══════════════════════════════════════════════════════════════
  RAPPORT DE MIGRATION - Exercices JSON
═══════════════════════════════════════════════════════════════

Date : 2026-02-14 15:45:30
Dossier source : dev/db/json/exercices
Schéma référence : dev/reports/exercises_schema.json
Mode : PRODUCTION

═══════════════════════════════════════════════════════════════

─────────────────────────────────────────────────────────────
Fichier 1/150 : mathematiques_6eme_001.json
─────────────────────────────────────────────────────────────
Champs ajoutés : tags, metadata, is_active
Champs supprimés : Difficulte (ancien nom)
Champs modifiés : difficulty
  • difficulty : "moyen" → "medium"
Fichier généré : mathematiques_6eme_001.migrated.json
Status : ✅ SUCCESS

─────────────────────────────────────────────────────────────
Fichier 2/150 : francais_5eme_002.json
─────────────────────────────────────────────────────────────
Champs ajoutés : tags, metadata
Status : ✅ SUCCESS

...

═══════════════════════════════════════════════════════════════
  STATISTIQUES FINALES
═══════════════════════════════════════════════════════════════

Total : 150
Succès : 145
Ignorés : 3
Erreurs : 2

⚠️  AVERTISSEMENTS :
  • histoire_3eme_050.json : JSON invalide : Syntax error
  • anglais_bac_120.json : Champ obligatoire 'title' manquant
```

---

## 🔧 Fonctionnalités Avancées

### **Mapping des Champs Renommés**

Le script gère automatiquement les anciens noms de champs :

| Ancien Nom | Nouveau Nom |
|------------|-------------|
| `Id` | `id` |
| `Titre` | `title` |
| `Matiere` | `subject` |
| `Niveau` | `level` |
| `Difficulte` | `difficulty` |
| `Points` | `points` |
| `Contenu` | `content` |
| `Type` | `type` |

**Modification** : Édite la fonction `mapFieldValue()` dans `migrate_exercises_json.php` pour ajouter d'autres mappings.

---

### **Valeurs par Défaut**

Si un champ est **non nullable** et manquant, le script applique une valeur par défaut selon le type :

| Type PHP | Valeur par Défaut |
|----------|-------------------|
| `integer` | `0` |
| `float` | `0.0` |
| `boolean` | `false` |
| `string` | `""` (chaîne vide) |
| `array` | `[]` (tableau vide) |

---

### **Champs Ignorés**

Les champs suivants sont **ignorés** lors de la migration (gérés par la BDD) :
- `id` (auto-increment)
- `created_at` (timestamp automatique)
- `updated_at` (timestamp automatique)

---

## 📝 Exemple de Migration

### **Avant** : `mathematiques_6eme_001.json`

```json
{
  "Id": 1,
  "Titre": "Calcul mental : addition",
  "Matiere": "Mathématiques",
  "Niveau": "6ème",
  "Difficulte": "facile",
  "Points": 10,
  "Contenu": "<p>Calculez : 25 + 37 = ?</p>",
  "Type": "qcm"
}
```

### **Après** : `mathematiques_6eme_001.migrated.json`

```json
{
  "title": "Calcul mental : addition",
  "subject": "Mathématiques",
  "level": "6ème",
  "difficulty": "easy",
  "points": 10,
  "content": "<p>Calculez : 25 + 37 = ?</p>",
  "type": "qcm",
  "tags": [],
  "metadata": null,
  "is_active": true,
  "duration_minutes": 5,
  "passing_score": 50
}
```

**Changements appliqués :**
- `Id` → supprimé (auto-généré)
- `Titre` → `title`
- `Matiere` → `subject`
- `Niveau` → `level`
- `Difficulte` → `difficulty` (valeur traduite : "facile" → "easy")
- `Points` → `points`
- `Contenu` → `content`
- `Type` → `type`
- Ajout de champs manquants : `tags`, `metadata`, `is_active`, `duration_minutes`, `passing_score`

---

## ⚠️ Cas d'Erreurs Possibles

### **Erreur 1 : Fichier JSON Invalide**

```
[50/150] histoire_3eme_050.json... ❌ ERREUR
Message : JSON invalide : Syntax error
```

**Solution** : Répare manuellement le fichier JSON ou supprime-le.

---

### **Erreur 2 : Champ Obligatoire Manquant**

```
[120/150] anglais_bac_120.json... ❌ ERREUR
Message : Champ obligatoire 'title' manquant
```

**Solution** : Ajoute le champ manquant dans le fichier JSON source.

---

### **Erreur 3 : Schéma Introuvable**

```
❌ Fichier schéma introuvable : dev/reports/exercises_schema.json
💡 Exécutez d'abord : php dev/tools/exercises/export_schema.php
```

**Solution** : Lance d'abord le script `export_schema.php`.

---

## 🔄 Workflow Complet

```
1. Export du schéma
   php dev/tools/exercises/export_schema.php
   ↓
2. Simulation de migration (dry-run)
   php dev/tools/exercises/migrate_exercises_json.php --dry-run
   ↓
3. Vérification du rapport de simulation
   cat dev/reports/migration_report.txt
   ↓
4. Migration réelle
   php dev/tools/exercises/migrate_exercises_json.php
   ↓
5. Vérification des fichiers .migrated.json
   ls -lh dev/db/json/exercices/*.migrated.json
   ↓
6. Import dans la BDD (script séparé)
   php dev/tools/exercises/import_migrated.php
```

---

## 📁 Structure des Dossiers Après Migration

```
dev/
├── db/
│   └── json/
│       └── exercices/
│           ├── mathematiques_6eme_001.json          # Original
│           ├── mathematiques_6eme_001.migrated.json # Migré
│           ├── francais_5eme_002.json
│           ├── francais_5eme_002.migrated.json
│           └── ...
├── reports/
│   ├── exercises_schema.json       # Schéma exporté
│   └── migration_report.txt        # Rapport de migration
└── tools/
    └── exercises/
        ├── export_schema.php       # Script 1
        └── migrate_exercises_json.php # Script 2
```

---

## ✅ Checklist Finale

### **Avant Migration**
- [ ] Base de données accessible
- [ ] Fichiers JSON dans `dev/db/json/exercices`
- [ ] Script `export_schema.php` exécuté avec succès
- [ ] Fichier `dev/reports/exercises_schema.json` créé

### **Pendant Migration**
- [ ] Dry-run effectué et validé
- [ ] Aucune erreur critique détectée
- [ ] Mappings des champs vérifiés

### **Après Migration**
- [ ] Fichiers `.migrated.json` créés
- [ ] Rapport `migration_report.txt` généré
- [ ] Aucune erreur dans le rapport
- [ ] Vérification manuelle de quelques fichiers migrés
- [ ] Import dans la BDD (script séparé)

---

## 🎉 Prochaines Étapes

Après cette migration, tu auras besoin de :

1. **Script d'import BDD** : Charger les `.migrated.json` dans la table `exercises`
2. **Script de validation** : Vérifier l'intégrité des données importées
3. **Script de cleanup** : Supprimer les anciens JSON après import réussi

Veux-tu que je crée ces scripts également ? 😊

---

## 📞 Support

En cas de problème :
1. Vérifie les logs d'erreur dans `migration_report.txt`
2. Utilise `--dry-run` pour tester sans risque
3. Consulte la section "Cas d'Erreurs Possibles"

---

**Créé le** : 14 février 2026  
**Auteur** : MonCoachScolaire Team
