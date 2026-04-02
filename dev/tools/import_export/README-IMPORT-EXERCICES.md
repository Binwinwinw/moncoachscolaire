# Guide d'Import des Exercices

## 📋 Description

Ce script permet d'importer automatiquement tous les exercices Markdown dans la base de données MonCoachScolaire.

## 🚀 Installation

### 1. Installer Parsedown (Recommandé)

Pour une conversion Markdown → HTML de qualité, installez Parsedown :

```bash
composer require erusev/parsedown
```

**Note** : Le script fonctionne aussi sans Parsedown, mais avec une conversion basique.

### 2. Vérifier la connexion à la base de données

Le script utilise la même configuration que le site (`db/connection.php`). Assurez-vous que vos variables d'environnement sont configurées :

- `DB_HOST`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## 💻 Utilisation

### Mode Test (Dry-Run)

Pour voir ce qui serait importé **sans modifier la base de données** :

```bash
php tools/import_exercices_to_db.php --dry-run
```

### Import Complet

Pour importer tous les exercices :

```bash
php tools/import_exercices_to_db.php
```

### Import Limité (Test)

Pour tester avec seulement quelques exercices :

```bash
php tools/import_exercices_to_db.php --limit=5
```

### Combinaison d'Options

```bash
php tools/import_exercices_to_db.php --dry-run --limit=10
```

## 📊 Fonctionnalités

### Ce que fait le script

1. **Détecte tous les fichiers Markdown** dans `exercices/`
2. **Parse les métadonnées** :
   - Titre
   - Niveau (6ème, 3ème, Seconde, Première)
   - Matière (Mathématiques, Français)
   - Domaine
   - Difficulté
   - Identifiant
   - Cristaux, XP, Badge (gamification)

3. **Sépare le contenu** :
   - Énoncé
   - Correction

4. **Convertit Markdown → HTML**

5. **Insère ou met à jour** dans la table `Exercises`

### Détection automatique

Le script détecte automatiquement :
- La **matière** (depuis le chemin du fichier ou l'identifiant)
- Le **niveau** (depuis les métadonnées)
- Les **récompenses** (cristaux, XP, badges)

### Gestion des doublons

Si un exercice existe déjà (même titre, niveau et matière), il sera **mis à jour** au lieu d'être dupliqué.

## ⚙️ Structure de la Table

Le script utilise la table `Exercises` avec les champs suivants :

- `Id` : Identifiant unique (AUTO_INCREMENT)
- `Subject` : Matière (Mathématiques, Français)
- `Level` : Niveau (6ème, 3ème, Seconde, Première)
- `Title` : Titre de l'exercice
- `Content` : Énoncé (HTML)
- `Answer` : Correction (HTML)

## 🔍 Vérification

Après l'import, vous pouvez vérifier dans la base de données :

```sql
SELECT COUNT(*) FROM Exercises;
SELECT Subject, Level, COUNT(*) as count 
FROM Exercises 
GROUP BY Subject, Level;
```

## 🐛 Résolution de Problèmes

### Erreur : "Impossible de se connecter à la base de données"

**Solution** : Vérifiez vos variables d'environnement ou la configuration dans `db/connection.php`.

### Erreur : "Parsedown non disponible"

**Solution** : 
```bash
composer require erusev/parsedown
```

Le script fonctionnera quand même avec une conversion basique.

### Certains exercices ne sont pas importés

Le script affiche les erreurs pour chaque fichier. Vérifiez :
- Format Markdown correct
- Métadonnées présentes (Niveau, Domaine, Titre)
- Fichier lisible

## 📝 Format Requis des Fichiers Markdown

Les fichiers doivent suivre ce format :

```markdown
# Exercice X : Titre
**Niveau** : 6ème
**Domaine** : ...
**Compétence** : ...
**Difficulté** : ★★
**Identifiant** : ...

## Énoncé
...

## Correction
...

## Gamification
- **Cristaux** : 25
- **Badge déblocable** : "..."
- **Points d'expérience** : 15 XP
```

## ✅ Exemple de Sortie

```
🚀 Démarrage de l'import des exercices...
✅ Parsedown chargé.
📁 75 fichiers d'exercices trouvés.

📄 [1/75] Traitement de : exercices/college/6eme/mathematiques/exercice-001-fractions-addition.md
   ✓ Matière: Mathématiques
   ✓ Niveau: 6ème
   ✓ Titre: Les courses d'Emma
   ✓ Cristaux: 25
   ✅ Exercice inséré (ID: 1)

...

============================================================
📊 RÉSUMÉ DE L'IMPORT
============================================================
Total de fichiers traités : 75
✅ Succès : 75
⚠️  Ignorés : 0
❌ Erreurs : 0

✅ Import terminé !
```

## 🎯 Prochaines Étapes

Après l'import :
1. Vérifier les exercices dans la base de données
2. Créer les pages d'affichage (voir `docs/PLAN-INTEGRATION-EXERCICES.md`)
3. Intégrer la gamification

---

**Créé le** : 2025-01-XX
**Version** : 1.0

