# 📚 Documentation complète — Migration des Exercices JSON

## 1. Objectif

Moderniser et fiabiliser la gestion des exercices en JSON pour MonCoachScolaire, en assurant la compatibilité totale avec le schéma SQL actuel de la table `exercises`.

---

## 2. Scripts fournis

### a) export_schema.php
- **Emplacement** : `dev/tools/exercises/export_schema.php`
- **But** : Exporte le schéma SQL de la table `exercises` en JSON détaillé.
- **Sortie** : `dev/reports/exercises_schema.json`
- **Utilisation** :
  ```bash
  php dev/tools/exercises/export_schema.php
  ```
- **Ce que fait le script** :
  - Connexion à la BDD (via `src/database/connection.php`)
  - Extraction de la structure (noms, types, contraintes, valeurs par défaut)
  - Génération d’un fichier JSON réutilisable pour la migration
  - Affichage console détaillé (tableau des colonnes)

### b) migrate_exercises_json.php
- **Emplacement** : `dev/tools/exercises/migrate_exercises_json.php`
- **But** : Migre tous les anciens fichiers JSON d’exercices vers le nouveau schéma
- **Entrée** :
  - Dossier source : `dev/db/json/exercices`
  - Schéma : `dev/reports/exercises_schema.json`
- **Sortie** :
  - Fichiers `.migrated.json` (un par exercice)
  - Rapport : `dev/reports/migration_report.txt`
- **Options** :
  - `--dry-run` : simulation sans écriture
  - `--force` : écrase les fichiers migrés existants
- **Ce que fait le script** :
  - Mapping intelligent des champs (renommage, casse, valeurs par défaut)
  - Gestion des champs auto-générés et ignorés (`id`, `created_at`, `updated_at`)
  - Rapport détaillé (succès, erreurs, warnings, diff par fichier)
  - Sécurité : dry-run, gestion d’erreur robuste

---

## 3. Workflow recommandé

1. **Exporter le schéma**
   ```bash
   php dev/tools/exercises/export_schema.php
   ```
2. **Simulation de migration (dry-run)**
   ```bash
   php dev/tools/exercises/migrate_exercises_json.php --dry-run
   ```
3. **Vérification du rapport**
   - Lire `dev/reports/migration_report.txt`
   - Corriger les erreurs éventuelles (JSON invalide, champ manquant)
4. **Migration réelle**
   ```bash
   php dev/tools/exercises/migrate_exercises_json.php
   ```
5. **Vérification des fichiers migrés**
   - Les `.migrated.json` sont générés à côté des originaux
   - Rapport détaillé mis à jour
6. **Import BDD** (script séparé à prévoir)

---

## 4. Mapping des champs (exemples)

| Ancien nom   | Nouveau nom |
|--------------|-------------|
| `Id`         | `id`        |
| `Titre`      | `title`     |
| `Matiere`    | `subject`   |
| `Niveau`     | `level`     |
| `Difficulte` | `difficulty`|
| `Points`     | `points`    |
| `Contenu`    | `content`   |
| `Type`       | `type`      |

- Voir la fonction `mapFieldValue()` pour ajouter d’autres mappings si besoin.

---

## 5. Valeurs par défaut

- Si un champ non nullable est absent, une valeur par défaut adaptée au type est appliquée :
  - `integer` : `0`
  - `float` : `0.0`
  - `boolean` : `false`
  - `string` : `""`
  - `array` : `[]`
- Les champs auto-générés (`id`, `created_at`, `updated_at`) sont ignorés à l’export.

---

## 6. Gestion des erreurs

- **JSON invalide** : le fichier est ignoré, message dans le rapport
- **Champ obligatoire manquant** : message d’erreur explicite
- **Schéma absent** : le script s’arrête, invite à lancer d’abord l’export du schéma

---

## 7. Structure des dossiers après migration

```
dev/
├── db/
│   └── json/
│       └── exercices/
│           ├── mathematiques_6eme_001.json          # Original
│           ├── mathematiques_6eme_001.migrated.json # Migré
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

## 8. Liens avec le projet

- **Connexion BDD** : utilise `src/database/connection.php` (doit être fonctionnel)
- **Compatibilité** : le schéma exporté doit refléter la structure réelle de la table `exercises`
- **Import** : prévoir un script d’import pour charger les `.migrated.json` dans la BDD
- **Rollback** : les originaux ne sont jamais écrasés, migration non destructive

---

## 9. Conseils pour une nouvelle session

- Toujours commencer par exporter le schéma pour garantir la compatibilité
- Utiliser le dry-run pour détecter les problèmes avant migration réelle
- Lire le rapport pour corriger les éventuelles erreurs
- Adapter le mapping si de nouveaux champs apparaissent
- Documenter toute modification dans ce fichier

---

## 10. Support & maintenance

- En cas de problème, consulter le rapport de migration
- Pour toute évolution du schéma, mettre à jour le mapping et relancer l’export
- Pour automatiser l’import BDD, prévoir un script dédié (voir roadmap)

---

## 11. Documentation du schéma à la sortie

- **À chaque export du schéma** (`export_schema.php`), le fichier `dev/reports/exercises_schema.json` doit être archivé ou versionné.
- **But** :
  - Garder une trace de l’évolution de la structure de la table `exercises` (utile pour l’historique, la conformité, la migration future).
  - Faciliter la présentation du modèle de données lors de démos, audits, ou onboarding.
  - Permettre de vérifier rapidement la compatibilité des nouveaux exercices à venir.
- **Conseil** :
  - Ajouter un extrait du schéma JSON dans la documentation projet (README ou docs/SCHEMA_EXERCISES.md) après chaque modification majeure.
  - Utiliser ce schéma comme référence pour tout développement ou import d’exercices futurs.

---

**Dernière mise à jour : 14 février 2026**
**Auteur : MonCoachScolaire Team**
