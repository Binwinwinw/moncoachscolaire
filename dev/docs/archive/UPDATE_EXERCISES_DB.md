# 📚 Mise à Jour de la Base de Données - Exercices

## Vue d'ensemble

Ce système permet d'importer et mettre à jour les exercices dans la base de données de manière structurée et organisée.

## 🎯 Objectif

Ranger correctement les exercices dans la BDD selon :
- **Niveau scolaire** : 6ème, 5ème, 4ème, 3ème, Seconde, Première, Terminale
- **Matière** : Mathématiques, Français, Anglais, etc.
- **Structure** : Content (consigne) | Answer (réponse) | Tips (astuces)

## 📁 Fichiers

### Scripts principaux
- `tools/update_exercises_db.php` - Script de mise à jour de la BDD
- `tools/generate_all_exercises_html.php` - Génère un HTML de tous les exercices

### Exemples de données
- `docs/exercices/exemple_exercices.json` - Exemple au format JSON
- `docs/exercices/exemple_exercices.csv` - Exemple au format CSV

## 🔧 Formats supportés

### 1. Format JSON (recommandé)

```json
[
  {
    "level": "6ème",
    "subject": "Mathématiques",
    "title": "Titre de l'exercice",
    "content": "📝 Consigne de l'exercice...",
    "answer": "✅ Réponse détaillée...",
    "tips": "💡 Astuces pédagogiques..."
  }
]
```

### 2. Format CSV

```csv
Level,Subject,Title,Content,Answer,Tips
6ème,Mathématiques,Titre,"Consigne","Réponse","Astuces"
```

### 3. Format HTML

HTML généré par `generate_all_exercises_html.php` avec la structure :
- `<div class="exercise-card">` contient un exercice
- Sections : `.consigne`, `.reponse`, `.astuces`

## 📝 Utilisation

### Importer depuis JSON

```bash
php tools/update_exercises_db.php --source docs/exercices/exercices_nouveaux.json
```

### Importer depuis CSV

```bash
php tools/update_exercises_db.php --source docs/exercices/exercices_nouveaux.csv
```

### Importer depuis HTML

```bash
php tools/update_exercises_db.php --source docs/exercices/tous_les_exercices.html
```

## 🔄 Workflow de mise à jour

### 1. Préparer les nouveaux exercices

Créer un fichier JSON avec vos nouveaux exercices :

```json
[
  {
    "level": "5ème",
    "subject": "SVT",
    "title": "La photosynthèse",
    "content": "Explique le processus de photosynthèse...",
    "answer": "La photosynthèse est le processus...",
    "tips": "Rappel: CO2 + H2O + lumière → glucose + O2"
  }
]
```

### 2. Exécuter le script d'import

```bash
php tools/update_exercises_db.php --source mes_nouveaux_exercices.json
```

### 3. Vérifier les résultats

Le script affichera :
- ➕ Exercices insérés (nouveaux)
- 🔄 Exercices mis à jour (existants modifiés)
- ⚠️ Exercices ignorés (données incomplètes)

## 📊 Colonnes de la table Exercises

| Colonne | Type | Description | Requis |
|---------|------|-------------|--------|
| `Id` | INT | ID auto-incrémenté | Auto |
| `Level` | VARCHAR(10) | Niveau scolaire (6ème, 5ème, etc.) | ✅ Oui |
| `Subject` | VARCHAR(60) | Matière (Mathématiques, Français, etc.) | ✅ Oui |
| `Title` | VARCHAR(250) | Titre de l'exercice | ✅ Oui |
| `Content` | LONGTEXT | **📝 Consigne** de l'exercice | ✅ Oui |
| `Answer` | LONGTEXT | **✅ Réponse** détaillée | ✅ Oui |
| `Tips` | TEXT | **💡 Astuces** pédagogiques | ⚪ Non |

## ✅ Niveaux scolaires valides

### Collège
- `6ème`, `6eme`, `6EME` → normalisé en `6ème`
- `5ème`, `5eme`, `5EME` → normalisé en `5ème`
- `4ème`, `4eme`, `4EME` → normalisé en `4ème`
- `3ème`, `3eme`, `3EME` → normalisé en `3ème`

### Lycée
- `Seconde`, `seconde`, `2nde` → normalisé en `Seconde`
- `Première`, `Premiere`, `première`, `1ère` → normalisé en `Première`
- `Terminale`, `terminale`, `BAC`, `Bac` → normalisé en `Terminale`

## 📚 Matières supportées

- Mathématiques
- Français
- Anglais
- Espagnol
- Histoire-Géographie
- SVT
- Physique-Chimie
- Technologie
- Arts
- EPS
- Philosophie

## 🔍 Détection des doublons

Le script détecte automatiquement les doublons selon :
- Même **Titre**
- Même **Niveau**
- Même **Matière**

Si un doublon est détecté :
- **Mise à jour** des colonnes Content, Answer, Tips
- **Pas de création** de nouvelle ligne

## 💡 Bonnes pratiques

### Structure de Content (Consigne)

```
Énoncé clair et précis de l'exercice

Questions:
a) Première question
b) Deuxième question
c) Troisième question
```

### Structure de Answer (Réponse)

```
a) Réponse détaillée avec explications
   Étape 1: ...
   Étape 2: ...
   Résultat: ...

b) Deuxième réponse avec méthode
   Formule utilisée: ...
   Application: ...
   Résultat: ...
```

### Structure de Tips (Astuces)

```
💡 Conseil méthodologique principal

💡 Formule ou rappel important à retenir

💡 Astuce pour éviter les erreurs courantes
```

## 🚀 Exemples d'utilisation

### Exemple 1: Importer 10 nouveaux exercices de maths

```bash
# 1. Créer le fichier JSON
nano docs/exercices/maths_6eme_nouveaux.json

# 2. Importer
php tools/update_exercises_db.php --source docs/exercices/maths_6eme_nouveaux.json

# Résultat attendu:
# ➕ Inséré: [6ème] Mathématiques - Les fractions (ID: 488)
# ➕ Inséré: [6ème] Mathématiques - Les décimaux (ID: 489)
# ...
```

### Exemple 2: Mettre à jour des exercices existants

```bash
# Si un exercice avec le même titre/niveau/matière existe,
# seules les colonnes Content, Answer, Tips seront mises à jour

php tools/update_exercises_db.php --source docs/exercices/corrections_maths.json

# Résultat:
# 🔄 Mis à jour: [6ème] Mathématiques - Les fractions (ID: 42)
```

### Exemple 3: Export puis réimport

```bash
# 1. Générer un HTML de tous les exercices
php tools/generate_all_exercises_html.php

# 2. Modifier le HTML si besoin
# 3. Réimporter (attention: peut créer des doublons si la structure change)
php tools/update_exercises_db.php --source docs/exercices/tous_les_exercices_2025-12-30.html
```

## ⚠️ Attention

- Les exercices avec **données incomplètes** sont ignorés (niveau, matière ou titre manquant)
- Le format HTML peut perdre certaines informations (niveau notamment)
- **Préférer JSON ou CSV** pour les imports importants
- Toujours faire une **sauvegarde de la BDD** avant un import massif

## 📈 Statistiques après import

Le script affiche :
```
╔════════════════════════════════════════════════════════════════╗
║   ✅ Traitement terminé                                        ║
╚════════════════════════════════════════════════════════════════╝

📊 Statistiques:
   • Exercices insérés : 25
   • Exercices mis à jour : 5
   • Exercices ignorés : 2
   • Total traité : 32
```

## 🔧 Dépannage

### Erreur "Connexion à la base de données échouée"

Vérifier que `db/connection.php` est correctement configuré.

### Erreur "Colonne manquante"

Pour CSV, vérifier que toutes les colonnes sont présentes :
`Level,Subject,Title,Content,Answer,Tips`

### Erreur JSON

Vérifier la syntaxe JSON avec un validateur en ligne.

## 📞 Support

Pour toute question ou problème, consulter la documentation dans `docs/` ou contacter l'administrateur système.
