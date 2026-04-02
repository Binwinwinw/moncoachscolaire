# Workflow d'Intégration des Exercices - MonCoachScolaire

> **Date de création** : 2025-01-XX  
> **Objectif** : Extraire, normaliser et intégrer les exercices des dossiers `exercices/` et `docs/` dans la base de données

---

## 📋 Vue d'ensemble

Ce workflow décrit le processus complet pour :
1. **Extraire** les exercices des fichiers Markdown structurés
2. **Normaliser** les données selon le schéma de la base de données
3. **Valider** la qualité et la complétude des données
4. **Intégrer** les exercices dans la table `Exercises`
5. **Vérifier** l'intégrité et la disponibilité des exercices

---

## 🗂️ Structure des Sources de Données

### 1. Dossier `exercices/` (Fichiers Markdown structurés)

**Structure** : `exercices/{college|lycee}/{niveau}/{matiere}/exercice-XXX-*.md`

**Format des fichiers** :
```markdown
# Exercice 1 : [Titre]
**Niveau** : [6ème|5ème|4ème|3ème|Seconde|Première|Terminale]
**Domaine** : [Domaine]
**Compétence** : [Compétence]
**Difficulté** : [★|★★|★★★]
**Identifiant** : [MATH-6EME-FRACTIONS-001]

## Énoncé
[Questions et instructions]

## Correction
[Corrections détaillées]

## Gamification
- **Cristaux mathématiques** : [nombre]
- **Badge déblocable** : [nom]
- **Points d'expérience** : [XP]
```

**Champs à extraire** :
- `Title` : Titre de l'exercice (ligne #)
- `Level` : Niveau (normalisé)
- `Subject` : Matière (déduit du dossier)
- `Content` : Section "Énoncé"
- `Answer` : Section "Correction"

### 2. Fichier `docs/exercices-sources-par-niveau.md` (Format simplifié)

**Format** :
```markdown
#### Exercice X : [Titre]
**Matière** : [Matière]
**Niveau** : [Niveau]
**Type** : [Type]

**Contenu** :
[Questions]

**Réponse attendue** :
[Correction]
```

**Champs à extraire** :
- `Title` : Titre de l'exercice
- `Level` : Niveau
- `Subject` : Matière
- `Content` : Section "Contenu"
- `Answer` : Section "Réponse attendue"

---

## 🎯 Schéma de la Base de Données

**Table `Exercises`** :
```sql
CREATE TABLE `Exercises` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Subject` VARCHAR(60) NULL,
  `Level` VARCHAR(10) NULL,
  `Title` VARCHAR(250) NULL,
  `Content` LONGTEXT NULL,
  `Answer` LONGTEXT NULL,
  PRIMARY KEY (`Id`)
);
```

**Mapping des champs** :
- `Subject` ← Matière (normalisée)
- `Level` ← Niveau (normalisé)
- `Title` ← Titre de l'exercice
- `Content` ← Énoncé/Contenu
- `Answer` ← Correction/Réponse attendue

---

## 🔄 Workflow d'Intégration

### Phase 1 : Analyse et Inventaire

#### 1.1 Scanner les fichiers Markdown
- Parcourir récursivement `exercices/college/` et `exercices/lycee/`
- Lister tous les fichiers `.md`
- Compter les exercices par niveau et matière

#### 1.2 Analyser le fichier sources
- Parser `docs/exercices-sources-par-niveau.md`
- Extraire tous les exercices par section (niveau/matière)
- Compter les exercices

#### 1.3 Générer un rapport d'inventaire
- Total d'exercices trouvés
- Répartition par niveau
- Répartition par matière
- Exercices en double potentiels

### Phase 2 : Extraction et Parsing

#### 2.1 Parser les fichiers Markdown structurés (`exercices/`)

**Algorithme** :
1. Lire le fichier ligne par ligne
2. Extraire le titre (ligne commençant par `#`)
3. Extraire les métadonnées (lignes `**Clé** : Valeur`)
4. Extraire la section "Énoncé" (entre `## Énoncé` et `## Correction`)
5. Extraire la section "Correction" (après `## Correction`)
6. Déduire la matière du chemin du fichier
7. Normaliser le niveau

**Normalisation des niveaux** :
```php
$levelMapping = [
    '6ème' => '6ème',
    '6eme' => '6ème',
    '5ème' => '5ème',
    '5eme' => '5ème',
    '4ème' => '4ème',
    '4eme' => '4ème',
    '3ème' => '3ème',
    '3eme' => '3ème',
    'Seconde' => 'Seconde',
    'Première' => 'Première',
    'Premiere' => 'Première',
    'Terminale' => 'Terminale',
    'BAC' => 'Terminale' // BAC = Terminale
];
```

**Normalisation des matières** :
```php
$subjectMapping = [
    'mathematiques' => 'Mathématiques',
    'francais' => 'Français',
    'histoire-geographie' => 'Histoire-Géographie',
    'svt' => 'SVT',
    'physique-chimie' => 'Physique-Chimie',
    'anglais' => 'Anglais',
    'philosophie' => 'Philosophie'
];
```

#### 2.2 Parser le fichier sources (`docs/exercices-sources-par-niveau.md`)

**Algorithme** :
1. Détecter les sections de niveau (lignes `## 🎓 [Niveau]`)
2. Détecter les sections de matière (lignes `### [Matière]`)
3. Pour chaque exercice (lignes `#### Exercice X :`):
   - Extraire le titre
   - Extraire les métadonnées (`**Matière**`, `**Niveau**`, `**Type**`)
   - Extraire la section "Contenu"
   - Extraire la section "Réponse attendue"

### Phase 3 : Normalisation et Validation

#### 3.1 Normalisation des données

**Titre** :
- Limiter à 250 caractères
- Nettoyer les caractères spéciaux
- Supprimer les emojis si nécessaire

**Niveau** :
- Appliquer le mapping de normalisation
- Vérifier que le niveau est valide

**Matière** :
- Appliquer le mapping de normalisation
- Vérifier que la matière est valide

**Contenu** :
- Préserver le formatage Markdown
- Nettoyer les espaces multiples
- Échapper les caractères spéciaux SQL

**Réponse** :
- Préserver le formatage Markdown
- Nettoyer les espaces multiples
- Échapper les caractères spéciaux SQL

#### 3.2 Validation des données

**Règles de validation** :
- ✅ Titre non vide
- ✅ Niveau valide (dans la liste autorisée)
- ✅ Matière valide (dans la liste autorisée)
- ✅ Contenu non vide (minimum 10 caractères)
- ✅ Réponse non vide (minimum 5 caractères)

**Détection des doublons** :
- Comparer titre + niveau + matière
- Signaler les exercices potentiellement dupliqués

### Phase 4 : Intégration dans la Base de Données

#### 4.1 Préparation de la base de données

**Vérifications** :
- Connexion à la base de données active
- Table `Exercises` existe
- Permissions d'écriture

#### 4.2 Insertion des exercices

**Stratégie** :
1. **Mode INSERT** : Insérer tous les exercices (pour première importation)
2. **Mode UPDATE** : Mettre à jour les exercices existants (basé sur titre + niveau + matière)
3. **Mode UPSERT** : Insérer ou mettre à jour selon l'existence

**Requête SQL** :
```sql
INSERT INTO Exercises (Subject, Level, Title, Content, Answer)
VALUES (?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
    Content = VALUES(Content),
    Answer = VALUES(Answer)
```

**Gestion des erreurs** :
- Logger les erreurs d'insertion
- Continuer le traitement même en cas d'erreur
- Générer un rapport des erreurs

#### 4.3 Vérification post-insertion

**Vérifications** :
- Nombre d'exercices insérés
- Exercices par niveau
- Exercices par matière
- Exercices manquants (si attendus)

### Phase 5 : Rapport et Validation

#### 5.1 Génération du rapport

**Contenu du rapport** :
- Total d'exercices traités
- Total d'exercices insérés
- Total d'exercices mis à jour
- Total d'erreurs
- Répartition par niveau
- Répartition par matière
- Liste des erreurs (si applicable)

#### 5.2 Tests de validation

**Tests à effectuer** :
1. Vérifier que les exercices sont accessibles via `getExercisesByLevel()`
2. Tester l'affichage sur une page d'exercices
3. Vérifier le formatage Markdown
4. Tester la génération interactive des exercices

---

## 🛠️ Implémentation Technique

### Script Principal : `tools/import_exercises.php`

**Fonctionnalités** :
- Scanner les dossiers `exercices/` et `docs/`
- Parser les fichiers Markdown
- Normaliser les données
- Insérer dans la base de données
- Générer un rapport

**Options de ligne de commande** :
- `--dry-run` : Mode simulation (pas d'insertion)
- `--verbose` : Mode verbeux
- `--source=exercices` : Importer uniquement depuis `exercices/`
- `--source=docs` : Importer uniquement depuis `docs/`
- `--level=6ème` : Importer uniquement un niveau spécifique
- `--subject=Mathématiques` : Importer uniquement une matière

### Classes/Fonctions nécessaires

#### `ExerciseParser` (classe)
- `parseMarkdownFile($filePath)` : Parse un fichier Markdown structuré
- `parseSourcesFile($filePath)` : Parse le fichier sources
- `normalizeLevel($level)` : Normalise un niveau
- `normalizeSubject($subject)` : Normalise une matière
- `validateExercise($exercise)` : Valide un exercice

#### `ExerciseImporter` (classe)
- `importFromDirectory($directory)` : Importe depuis un dossier
- `importFromSourcesFile($filePath)` : Importe depuis le fichier sources
- `insertExercise($exercise)` : Insère un exercice
- `generateReport()` : Génère le rapport

---

## 📊 Exemple d'Exécution

```bash
# Import complet (simulation)
php tools/import_exercises.php --dry-run --verbose

# Import uniquement depuis exercices/
php tools/import_exercises.php --source=exercices

# Import uniquement pour la 6ème
php tools/import_exercises.php --level=6ème

# Import réel
php tools/import_exercises.php
```

**Sortie attendue** :
```
=== Import des Exercices - MonCoachScolaire ===

[Phase 1] Analyse et Inventaire
  - Fichiers Markdown trouvés : 75
  - Exercices dans sources.md : 150+
  - Total estimé : 225+ exercices

[Phase 2] Extraction et Parsing
  - Exercices extraits depuis exercices/ : 75
  - Exercices extraits depuis docs/ : 150
  - Total extrait : 225

[Phase 3] Normalisation et Validation
  - Exercices valides : 220
  - Exercices invalides : 5
  - Doublons détectés : 10

[Phase 4] Intégration dans la Base de Données
  - Exercices insérés : 210
  - Exercices mis à jour : 10
  - Erreurs : 0

[Phase 5] Rapport Final
  - Total traité : 225
  - Total intégré : 220
  - Répartition par niveau :
    * 6ème : 45 exercices
    * 5ème : 30 exercices
    * 4ème : 25 exercices
    * 3ème : 40 exercices
    * Seconde : 35 exercices
    * Première : 25 exercices
    * Terminale : 20 exercices
  - Répartition par matière :
    * Mathématiques : 80 exercices
    * Français : 70 exercices
    * Sciences : 50 exercices
    * Autres : 20 exercices

✅ Import terminé avec succès !
```

---

## ⚠️ Points d'Attention

### 1. Gestion des doublons
- Détecter les exercices similaires (même titre + niveau + matière)
- Proposer une stratégie de fusion ou de remplacement

### 2. Formatage Markdown
- Préserver le formatage pour l'affichage
- Convertir si nécessaire pour la base de données

### 3. Performance
- Traiter par lots (batch) pour les gros volumes
- Utiliser des transactions pour garantir l'intégrité

### 4. Sécurité
- Valider et échapper toutes les entrées
- Utiliser des requêtes préparées
- Logger les erreurs sans exposer les détails

### 5. Maintenance
- Créer un script de mise à jour incrémentale
- Permettre la suppression d'exercices obsolètes
- Gérer les versions des exercices

---

## 🔄 Workflow Recommandé

### Étape 1 : Préparation
1. Créer le script `tools/import_exercises.php`
2. Créer les classes `ExerciseParser` et `ExerciseImporter`
3. Tester sur un petit échantillon

### Étape 2 : Test en mode simulation
1. Exécuter avec `--dry-run`
2. Vérifier le rapport
3. Corriger les problèmes détectés

### Étape 3 : Import réel
1. Faire une sauvegarde de la base de données
2. Exécuter l'import complet
3. Vérifier les résultats

### Étape 4 : Validation
1. Tester l'affichage des exercices
2. Vérifier la génération interactive
3. Tester sur différents niveaux

### Étape 5 : Documentation
1. Documenter les exercices importés
2. Créer un guide de maintenance
3. Mettre à jour la documentation utilisateur

---

## 📝 Checklist d'Implémentation

- [ ] Créer le script `tools/import_exercises.php`
- [ ] Créer la classe `ExerciseParser`
- [ ] Créer la classe `ExerciseImporter`
- [ ] Implémenter le parsing des fichiers Markdown structurés
- [ ] Implémenter le parsing du fichier sources
- [ ] Implémenter la normalisation des niveaux
- [ ] Implémenter la normalisation des matières
- [ ] Implémenter la validation des exercices
- [ ] Implémenter la détection des doublons
- [ ] Implémenter l'insertion en base de données
- [ ] Implémenter le mode UPSERT
- [ ] Implémenter la génération de rapport
- [ ] Tester sur un échantillon
- [ ] Tester en mode simulation
- [ ] Exécuter l'import complet
- [ ] Valider les résultats
- [ ] Documenter le processus

---

## 🎯 Prochaines Étapes

Une fois ce workflow implémenté, les prochaines étapes seront :
1. **Automatisation** : Créer un script de mise à jour automatique
2. **Interface Admin** : Créer une interface pour gérer les exercices
3. **Validation continue** : Mettre en place des tests automatiques
4. **Enrichissement** : Ajouter des métadonnées supplémentaires (tags, difficulté, etc.)

---

**Document créé le** : 2025-01-XX  
**Dernière mise à jour** : 2025-01-XX  
**Auteur** : MonCoachScolaire Team

