# Plan d'Intégration des Exercices dans MonCoachScolaire

## 📋 Vue d'ensemble

Ce document décrit le plan pour intégrer les 75 exercices Markdown dans la plateforme MonCoachScolaire.

---

## 🎯 Objectifs

1. **Importer les exercices Markdown dans la base de données**
2. **Créer un système de lecture et affichage des exercices**
3. **Intégrer la gamification (cristaux, badges, XP)**
4. **Créer les pages d'affichage par niveau/matière**

---

## 📊 Structure Actuelle

### Base de données
- Table `Exercises` avec les champs :
  - `Id` (INT, AUTO_INCREMENT)
  - `Subject` (VARCHAR(60)) - Ex: "Mathématiques", "Français"
  - `Level` (VARCHAR(10)) - Ex: "6ème", "3ème", "Seconde", "Première"
  - `Title` (VARCHAR(250))
  - `Content` (LONGTEXT) - Contenu Markdown de l'exercice
  - `Answer` (LONGTEXT) - Correction Markdown

### Fichiers Exercices
- Format : Markdown (.md)
- Structure standardisée avec :
  - Métadonnées (niveau, domaine, difficulté, identifiant)
  - Énoncé
  - Correction détaillée
  - Rappels
  - Astuces
  - Gamification (cristaux, badges, XP)

---

## 🔧 Étapes d'Intégration

### Étape 1 : Script d'Import des Exercices

**Fichier** : `tools/import_exercices_to_db.php`

**Fonctionnalités** :
- Lire tous les fichiers .md dans `exercices/`
- Parser le format Markdown standardisé
- Extraire les métadonnées (niveau, matière, domaine, difficulté)
- Convertir Markdown en HTML pour l'affichage
- Insérer dans la table `Exercises` de la base de données

**Structure des données à extraire** :
```php
[
    'subject' => 'Mathématiques', // ou 'Français'
    'level' => '6ème', // ou '3ème', 'Seconde', 'Première'
    'title' => 'Titre de l\'exercice',
    'content' => 'Énoncé complet en HTML',
    'answer' => 'Correction complète en HTML',
    'domain' => 'Domaine (ex: Fractions)',
    'difficulty' => '★', '★★', '★★★',
    'cristaux' => 25,
    'xp' => 20,
    'badge' => 'Nom du badge'
]
```

### Étape 2 : Système de Lecture des Exercices

**Fichier** : `includes/exercice_loader.php`

**Fonctionnalités** :
- Fonction pour charger un exercice par ID
- Fonction pour lister les exercices par niveau/matière
- Fonction pour parser le Markdown en HTML
- Fonction pour extraire les métadonnées de gamification

### Étape 3 : Affichage des Exercices

**Mise à jour des pages existantes** :
- `pages/college/6eme/exercices-6eme.php`
- `pages/college/3eme/exercices-3eme.php`
- `pages/lycee/seconde/exercices-seconde.php`
- `pages/lycee/premiere/exercices-premiere.php`

**Nouveau système** :
- Créer un composant réutilisable `includes/exercice_card.php`
- Intégrer le système de gamification
- Ajouter les fonctionnalités interactives (corrections, progressions)

### Étape 4 : Système de Gamification

**Intégration avec les tables existantes** :
- Table `UserAchievements` pour les badges
- Table `Users` pour stocker les cristaux et XP
- Système de progression par matière/niveau

**Fonctionnalités** :
- Attribution de cristaux après résolution
- Déblocage de badges
- Calcul de l'XP
- Barre de progression

### Étape 5 : Conversion Markdown → HTML

**Bibliothèque recommandée** : Parsedown ou CommonMark

**Options** :
1. **Parsedown** (simple, léger)
   - Installation : `composer require erusev/parsedown`
   - Utilisation simple, pas d'extensions nécessaires

2. **CommonMark** (standard, extensible)
   - Installation : `composer require league/commonmark`
   - Plus robuste, supporte les extensions

**Recommandation** : Parsedown pour la simplicité

---

## 📁 Structure des Fichiers à Créer

```
moncoachscolaire/
├── tools/
│   └── import_exercices_to_db.php      # Script d'import
├── includes/
│   ├── exercice_loader.php             # Chargement des exercices
│   ├── exercice_card.php               # Composant affichage
│   └── markdown_parser.php             # Parseur Markdown
├── api/
│   └── exercices.php                   # API pour les exercices (AJAX)
└── assets/
    └── css/
        └── pages/
            └── exercice.css            # Styles pour les exercices
```

---

## 🔍 Format des Exercices Markdown

### Structure Actuelle
```markdown
# Exercice X : Titre
**Niveau** : ...
**Domaine** : ...
**Compétence** : ...
**Difficulté** : ★/★★/★★★
**Identifiant** : ...

## Énoncé
...

## Correction
...

## Rappel
...

## Astuce
...

## Gamification
- **Cristaux** : X
- **Badge déblocable** : "..."
- **Points d'expérience** : X XP
```

### Métadonnées à Extraire
- Titre (première ligne après #)
- Niveau (ligne **Niveau**)
- Domaine (ligne **Domaine**)
- Difficulté (ligne **Difficulté**)
- Identifiant (ligne **Identifiant**)
- Cristaux (section Gamification)
- Badge (section Gamification)
- XP (section Gamification)

---

## 🗄️ Amélioration de la Table Exercises

### Champs à Ajouter (Optionnel)

```sql
ALTER TABLE Exercises ADD COLUMN Domain VARCHAR(100) AFTER Subject;
ALTER TABLE Exercises ADD COLUMN Difficulty VARCHAR(10) AFTER Level;
ALTER TABLE Exercises ADD COLUMN Identifier VARCHAR(50) AFTER Difficulty;
ALTER TABLE Exercises ADD COLUMN Cristaux INT DEFAULT 0 AFTER Answer;
ALTER TABLE Exercises ADD COLUMN XP INT DEFAULT 0 AFTER Cristaux;
ALTER TABLE Exercises ADD COLUMN Badge VARCHAR(100) AFTER XP;
ALTER TABLE Exercises ADD COLUMN CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE Exercises ADD COLUMN UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

---

## 🚀 Plan d'Exécution

### Phase 1 : Préparation (30 min)
1. ✅ Créer le plan d'intégration (ce document)
2. Installer la bibliothèque Markdown (Parsedown)
3. Créer les fichiers de structure

### Phase 2 : Script d'Import (1h)
1. Créer `tools/import_exercices_to_db.php`
2. Implémenter le parsing Markdown
3. Tester l'import avec quelques exercices

### Phase 3 : Système de Lecture (1h)
1. Créer `includes/exercice_loader.php`
2. Créer `includes/markdown_parser.php`
3. Tester le chargement d'exercices

### Phase 4 : Affichage (2h)
1. Créer le composant `includes/exercice_card.php`
2. Mettre à jour les pages PHP
3. Ajouter les styles CSS

### Phase 5 : Gamification (1h)
1. Intégrer le système de cristaux/XP
2. Intégrer les badges
3. Créer les barres de progression

### Phase 6 : Tests et Finitions (1h)
1. Tester tous les niveaux
2. Vérifier la gamification
3. Corriger les bugs

**Temps total estimé** : ~6-7 heures

---

## 📝 Notes Techniques

### Conversion Markdown → HTML
- Garder les sections structurées
- Préserver les listes et tableaux
- Gérer les blocs de code
- Maintenir les styles (gras, italique, etc.)

### Performance
- Mettre en cache les exercices convertis
- Utiliser la pagination pour les listes
- Optimiser les requêtes SQL

### Sécurité
- Échapper toutes les sorties HTML
- Valider les entrées utilisateur
- Prévenir les injections SQL (PDO)

---

## ✅ Checklist d'Intégration

- [ ] Installer Parsedown (composer)
- [ ] Créer le script d'import
- [ ] Importer les 75 exercices dans la DB
- [ ] Créer le système de chargement
- [ ] Créer le composant d'affichage
- [ ] Mettre à jour les pages PHP
- [ ] Intégrer la gamification
- [ ] Ajouter les styles CSS
- [ ] Tester tous les niveaux
- [ ] Documenter l'utilisation

---

**Date de création** : 2025-01-XX
**Statut** : En attente d'exécution

