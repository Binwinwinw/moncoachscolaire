# 🔄 Workflow d'Intégration des Exercices - Explication Complète

## 📋 Vue d'Ensemble

Ce document explique **comment fonctionne le système complet** d'intégration des exercices dans MonCoachScolaire, depuis les fichiers Markdown jusqu'à l'affichage dans la plateforme avec la gamification.

---

## 🎯 Le Parcours d'un Exercice

### Étape 1 : Fichier Markdown (Source)

**Localisation** : `exercices/college/6eme/mathematiques/exercice-001-fractions-addition.md`

**Contenu** : Un fichier Markdown avec :
- Métadonnées (niveau, matière, difficulté, identifiant)
- Énoncé de l'exercice
- Correction détaillée
- Rappels, astuces, erreurs fréquentes
- Informations de gamification (cristaux, XP, badges)

**Exemple** :
```markdown
# Exercice 1 : Les courses d'Emma
**Niveau** : 6ème
**Domaine** : Addition de fractions
**Difficulté** : ★★
**Identifiant** : MATH-6EME-FRACTIONS-001

## Énoncé
[Contenu de l'énoncé...]

## Correction
[Correction détaillée...]

## Gamification
- **Cristaux** : 25
- **Badge déblocable** : "Maître pâtissier"
- **Points d'expérience** : 15 XP
```

---

### Étape 2 : Import dans la Base de Données

**Script** : `tools/import_exercices_to_db.php`

**Processus** :
1. Le script **parcourt** tous les fichiers `.md` dans `exercices/`
2. Pour chaque fichier :
   - **Parse** le contenu Markdown
   - **Extrait** les métadonnées (niveau, matière, titre, etc.)
   - **Sépare** l'énoncé de la correction
   - **Convertit** Markdown → HTML (avec Parsedown ou conversion basique)
   - **Insère** dans la table `Exercises` de la base de données

**Table `Exercises`** :
```sql
Id | Subject      | Level | Title              | Content (HTML) | Answer (HTML)
---|--------------|-------|--------------------|----------------|----------------
1  | Mathématiques| 6ème  | Les courses d'Emma | <h2>Énoncé...</h2> | <h2>Correction...</h2>
```

**Commande** :
```bash
php tools/import_exercices_to_db.php
```

**Résultat** : Les 75 exercices sont maintenant dans la base de données, prêts à être affichés.

---

### Étape 3 : Chargement depuis la Base de Données

**Fichier** : `includes/exercice_loader.php`

**Quand** : À chaque affichage d'une page d'exercices

**Processus** :
1. La page PHP **appelle** `getExercicesByLevel('6ème', 'Mathématiques')`
2. La fonction **interroge** la base de données :
   ```sql
   SELECT * FROM Exercises 
   WHERE Level = '6ème' AND Subject = 'Mathématiques'
   ORDER BY Id ASC
   ```
3. Retourne un **tableau d'exercices** avec toutes les données

**Exemple d'utilisation** :
```php
require_once __DIR__ . '/../../includes/exercice_loader.php';

// Charger les exercices de mathématiques de 6ème
$exercises = getExercicesByLevel('6ème', 'Mathématiques');
// $exercises contient maintenant un tableau avec tous les exercices
```

---

### Étape 4 : Affichage dans la Page

**Fichier** : `includes/exercice_card.php`

**Processus** :
1. La page PHP **appelle** `renderExerciseList($exercises)`
2. La fonction **parcourt** chaque exercice
3. Pour chaque exercice, **génère** du HTML avec :
   - Titre, niveau, matière
   - Énoncé (HTML converti depuis Markdown)
   - Boutons (Voir la correction, Marquer comme terminé)
   - Classes CSS pour le style

**HTML généré** :
```html
<div class="exercise-card" data-exercise-id="1">
    <div class="exercise-header">
        <h3>Les courses d'Emma</h3>
        <span class="exercise-level">6ème</span>
    </div>
    <div class="exercise-content">
        <div class="exercise-enonce">
            <!-- Énoncé en HTML -->
        </div>
    </div>
    <div class="exercise-actions">
        <button onclick="toggleAnswer(1)">📝 Voir la correction</button>
        <button onclick="markExerciseComplete(1)">✅ Marquer comme terminé</button>
    </div>
</div>
```

---

### Étape 5 : Interaction Utilisateur (JavaScript)

**Fichier** : `includes/exercice_card.php` (section JavaScript)

**Quand l'utilisateur clique sur "Voir la correction"** :
1. La fonction JavaScript `toggleAnswer(exerciseId)` est appelée
2. Si la correction n'est pas encore chargée, elle **affiche/masque** la section correction
3. Le HTML de la correction est **déjà présent** dans la page (chargé depuis la DB)

**Quand l'utilisateur clique sur "Marquer comme terminé"** :
1. La fonction JavaScript `markExerciseComplete(exerciseId)` est appelée
2. **Appel AJAX** vers `api/save_exercise_progress.php`
3. Le serveur **sauvegarde** dans la base de données
4. **Attribue** les récompenses (cristaux, XP, badges)
5. **Retourne** les résultats (cristaux gagnés, XP, badge débloqué)
6. Le JavaScript **affiche** les animations de récompense

---

### Étape 6 : Sauvegarde de Progression (API)

**Fichier** : `api/save_exercise_progress.php`

**Processus** :
1. **Reçoit** la requête AJAX avec :
   - `exercise_id` : ID de l'exercice
   - `correct` : Réponse correcte ou non

2. **Vérifie** la session utilisateur (doit être connecté)

3. **Appelle** `completeExercise()` depuis `gamification.php` :
   - Vérifie si l'exercice existe
   - Vérifie si déjà complété (évite les doublons)
   - **Sauvegarde** dans `ExerciseResponses`
   - **Ajoute** l'XP dans `UserProgress`
   - **Ajoute** les cristaux dans `UserProgress.ProgressJson`
   - **Débloque** le badge si présent
   - **Vérifie** les badges automatiques

4. **Retourne** un JSON avec :
   ```json
   {
     "success": true,
     "cristaux": 25,
     "xp": 15,
     "badge_unlocked": true,
     "badge_name": "Maître pâtissier",
     "progress": { ... }
   }
   ```

---

### Étape 7 : Gamification et Récompenses

**Fichier** : `includes/gamification.php`

**Système de récompenses** :

1. **Cristaux** :
   - Stockés dans `UserProgress.ProgressJson`
   - Format JSON : `{"cristaux": 125, "cristaux_by_subject": {"Mathématiques": 75, "Français": 50}}`
   - Attribués à chaque exercice complété (si correct)

2. **XP (Points d'Expérience)** :
   - Stockés dans `UserProgress.XP`
   - Utilisés pour calculer le **niveau** de l'utilisateur
   - Niveaux : 1 (0 XP), 2 (100 XP), 3 (300 XP), etc.

3. **Badges** :
   - Stockés dans `Achievements` (liste des badges)
   - Liés aux utilisateurs via `UserAchievements`
   - **Déblocage automatique** :
     - "Premier pas" : 1er exercice complété
     - "Débutant confirmé" : 10 exercices
     - "Expert en exercices" : 50 exercices
     - Badges par matière (5, 15 exercices)
   - **Badge de l'exercice** : Si défini dans les métadonnées

---

### Étape 8 : Affichage de la Progression

**Fichier** : `includes/progress_display.php`

**Composants disponibles** :

1. **Barre de progression compacte** :
   ```php
   renderProgressBar($userId);
   ```
   Affiche :
   - Niveau actuel et XP
   - Cristaux collectés
   - Nombre de badges
   - Exercices complétés

2. **Statistiques détaillées** :
   ```php
   renderDetailedProgress($userId);
   ```
   Affiche :
   - Statistiques complètes
   - Progression par matière
   - Liste des badges débloqués
   - Graphiques de progression

---

## 🔄 Flux Complet : De A à Z

```
┌─────────────────────────────────────────────────────────┐
│  1. FICHIER MARKDOWN                                    │
│     exercices/.../exercice-001-fractions-addition.md   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  2. SCRIPT D'IMPORT                                     │
│     tools/import_exercices_to_db.php                   │
│     • Parse Markdown                                   │
│     • Extrait métadonnées                              │
│     • Convertit HTML                                   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  3. BASE DE DONNÉES                                     │
│     Table: Exercises                                    │
│     • Id, Subject, Level, Title                        │
│     • Content (HTML énoncé)                            │
│     • Answer (HTML correction)                         │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  4. PAGE PHP CHARGE LES EXERCICES                      │
│     includes/exercice_loader.php                       │
│     $exercises = getExercicesByLevel('6ème', 'Math');  │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  5. AFFICHAGE                                           │
│     includes/exercice_card.php                         │
│     renderExerciseList($exercises);                    │
│     • Génère le HTML                                   │
│     • Ajoute les boutons                               │
│     • Inclut le JavaScript                             │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  6. UTILISATEUR INTERAGIT                               │
│     • Clique "Voir la correction"                      │
│     • Clique "Marquer comme terminé"                   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  7. JAVASCRIPT APPEL AJAX                               │
│     markExerciseComplete(exerciseId)                   │
│     → fetch('api/save_exercise_progress.php')          │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  8. API SAUVEGARDE                                      │
│     api/save_exercise_progress.php                     │
│     → includes/gamification.php                        │
│     • completeExercise()                               │
│     • updateUserXP()                                   │
│     • addCristaux()                                    │
│     • unlockBadge()                                    │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  9. BASE DE DONNÉES MIS À JOUR                         │
│     • ExerciseResponses (sauvegarde réponse)           │
│     • UserProgress (XP, cristaux)                      │
│     • UserAchievements (badges débloqués)              │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  10. RETOUR JSON + ANIMATIONS                           │
│      • Affiche cristaux gagnés                         │
│      • Affiche XP gagné                                │
│      • Notification badge débloqué                     │
│      • Met à jour la progression                       │
└─────────────────────────────────────────────────────────┘
```

---

## 🗄️ Tables de la Base de Données Utilisées

### 1. `Exercises` (Stockage des exercices)
```
Id | Subject      | Level | Title              | Content | Answer
---|--------------|-------|--------------------|---------|--------
1  | Mathématiques| 6ème  | Les courses d'Emma | <h2>... | <h2>...
```

### 2. `ExerciseResponses` (Progression utilisateur)
```
Id | UserId | ExerciseId | Correct | Score | SubmittedAt
---|--------|------------|---------|-------|-------------
1  | 5      | 1          | 1       | 100   | 2025-01-XX
```

### 3. `UserProgress` (XP et cristaux)
```
Id | UserId | XP   | ProgressJson
---|--------|------|------------------------------------
1  | 5      | 150  | {"cristaux":125,"cristaux_by_subject":{...}}
```

### 4. `Achievements` (Liste des badges)
```
Id | Name              | Description       | Points
---|-------------------|-------------------|-------
1  | Premier pas       | Premier exercice  | 0
```

### 5. `UserAchievements` (Badges débloqués)
```
Id | UserId | AchievementId | UnlockedAt
---|--------|---------------|------------
1  | 5      | 1             | 2025-01-XX
```

---

## 💻 Exemple Concret : Un Utilisateur Complète un Exercice

### Scénario
Un utilisateur (ID: 5) complète l'exercice "Les courses d'Emma" (ID: 1) correctement.

### Étape par Étape

**1. L'utilisateur voit l'exercice** :
- La page charge via `getExercicesByLevel('6ème', 'Mathématiques')`
- L'exercice est affiché avec `renderExerciseCard()`

**2. L'utilisateur clique "Marquer comme terminé"** :
```javascript
markExerciseComplete(1, true);
```

**3. Requête AJAX envoyée** :
```javascript
POST api/save_exercise_progress.php
{
  exercise_id: 1,
  correct: '1'
}
```

**4. Le serveur traite** :
```php
completeExercise(5, 1, true, [
  'cristaux' => 25,
  'xp' => 15,
  'badge' => 'Maître pâtissier'
]);
```

**5. Actions en base de données** :
- **INSERT** dans `ExerciseResponses` :
  ```sql
  INSERT INTO ExerciseResponses (UserId, ExerciseId, Correct, Score)
  VALUES (5, 1, 1, 100)
  ```
- **UPDATE** `UserProgress.XP` : `0 → 15`
- **UPDATE** `UserProgress.ProgressJson` : Ajoute 25 cristaux
- **INSERT** dans `UserAchievements` si badge débloqué

**6. Réponse JSON retournée** :
```json
{
  "success": true,
  "cristaux": 25,
  "xp": 15,
  "badge_unlocked": true,
  "badge_name": "Maître pâtissier"
}
```

**7. Le JavaScript affiche** :
- Animation : 💎 +25 cristaux
- Animation : ⭐ +15 XP
- Notification : 🏆 Badge débloqué "Maître pâtissier"
- Mise à jour de la barre de progression

---

## 🔍 Points Clés du Workflow

### 1. **Import Unique**
- L'import se fait **une seule fois** (ou lors de mises à jour)
- Les exercices sont ensuite toujours chargés depuis la DB

### 2. **Affichage Dynamique**
- Les exercices sont **chargés à la demande** depuis la DB
- Pas besoin de fichiers Markdown après l'import

### 3. **Fallback Statique**
- Si la DB n'est pas disponible, **affichage du contenu statique**
- Le site fonctionne dans tous les cas

### 4. **Gamification en Temps Réel**
- Les récompenses sont **calculées et attribuées immédiatement**
- Animations pour feedback visuel

### 5. **Progression Persistante**
- Toutes les données sont **sauvegardées en base**
- L'utilisateur peut consulter sa progression à tout moment

---

## 🎨 Structure des Fichiers

```
moncoachscolaire/
├── exercices/                          # Fichiers Markdown sources
│   ├── college/
│   │   ├── 6eme/
│   │   │   ├── mathematiques/
│   │   │   │   └── exercice-001-*.md
│   │   │   └── francais/
│   │   │       └── exercice-001-*.md
│   │   └── 3eme/...
│   └── lycee/...
│
├── tools/                              # Scripts d'import
│   └── import_exercices_to_db.php
│
├── includes/                           # Système de chargement
│   ├── exercice_loader.php            # Chargement depuis DB
│   ├── exercice_card.php              # Affichage
│   ├── gamification.php               # Récompenses
│   └── progress_display.php           # Progression
│
├── api/                                # API AJAX
│   └── save_exercise_progress.php
│
├── pages/                              # Pages PHP
│   └── college/6eme/
│       └── exercices-6eme-integrated.php
│
└── docs/                               # Documentation
    └── WORKFLOW-INTEGRATION-EXPLICATION.md (ce fichier)
```

---

## 🚀 Commandes Clés

### Import Initial
```bash
php tools/import_exercices_to_db.php
```

### Test Import
```bash
php tools/import_exercices_to_db.php --dry-run --limit=5
```

### Vérifier la Base de Données
```sql
SELECT COUNT(*) FROM Exercises;
SELECT Subject, Level, COUNT(*) FROM Exercises GROUP BY Subject, Level;
```

---

## 💡 Avantages de ce Workflow

1. **Séparation des responsabilités** : Chaque fichier a un rôle clair
2. **Réutilisabilité** : Les fonctions peuvent être utilisées partout
3. **Maintenabilité** : Facile à modifier et étendre
4. **Performance** : Chargement depuis la DB optimisé
5. **Robustesse** : Gestion d'erreurs à tous les niveaux
6. **Évolutivité** : Facile d'ajouter de nouvelles fonctionnalités

---

## ❓ Questions Fréquentes

### Q : Comment mettre à jour un exercice ?
**R** : Modifier le fichier Markdown, puis relancer l'import. Le script détecte les doublons et met à jour.

### Q : Que se passe-t-il si la DB n'est pas disponible ?
**R** : Le système affiche automatiquement le contenu statique existant. Pas de panne.

### Q : Comment ajouter un nouveau type d'exercice ?
**R** : Créer le fichier Markdown avec le format standardisé, puis importer.

### Q : Les récompenses sont-elles personnalisables ?
**R** : Oui, elles sont définies dans les métadonnées de chaque exercice Markdown.

---

**Date de création** : 2025-01-XX
**Version** : 1.0

Ce workflow garantit un système **robuste**, **évolutif** et **facile à maintenir** !

