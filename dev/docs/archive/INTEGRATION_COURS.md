# Intégration des Cours Pédagogiques - Documentation

## Vue d'ensemble

Cette documentation décrit l'intégration complète des cours pédagogiques dans l'application MonCoachScolaire.

## Architecture

### 1. Structure des fichiers

```
cours/
└── college/
    ├── 3eme/
    │   ├── mathematiques/
    │   │   ├── cours-1-equations.md
    │   │   ├── cours-2-fonctions-lineaires.md
    │   │   └── ...
    │   ├── francais/
    │   ├── svt/
    │   └── ...
    ├── 4eme/
    ├── 5eme/
    └── 6eme/

exercices/
└── college/
    ├── 3eme/
    │   ├── mathematiques/
    │   │   ├── exercice-1-equations.md
    │   │   └── ...
    │   └── ...
    └── ...
```

### 2. Tables de base de données

#### `Courses`
Stocke tous les cours avec leurs métadonnées.

```sql
CREATE TABLE Courses (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Subject VARCHAR(100) NOT NULL,
    Level VARCHAR(50) NOT NULL,
    CourseNumber INT NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    FilePath VARCHAR(500) NOT NULL,
    Keywords TEXT,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Données actuelles** : 19 cours importés

#### `ExerciseCourseLinks`
Liens bidirectionnels entre cours et exercices.

```sql
CREATE TABLE ExerciseCourseLinks (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    ExerciseId INT NOT NULL,
    CourseId INT NOT NULL,
    LinkType VARCHAR(50) DEFAULT 'theory',
    LinkedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ExerciseId) REFERENCES Exercises(Id) ON DELETE CASCADE,
    FOREIGN KEY (CourseId) REFERENCES Courses(Id) ON DELETE CASCADE
);
```

**Données actuelles** : 32 liens créés

#### `UserCourseProgress`
Suivi de la progression des utilisateurs dans les cours.

```sql
CREATE TABLE UserCourseProgress (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    CourseId INT NOT NULL,
    StartedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    CompletedAt DATETIME,
    TimeSpent INT DEFAULT 0,
    Status VARCHAR(20) DEFAULT 'in_progress',
    Notes TEXT,
    FOREIGN KEY (UserId) REFERENCES Users(Id) ON DELETE CASCADE,
    FOREIGN KEY (CourseId) REFERENCES Courses(Id) ON DELETE CASCADE
);
```

#### `CourseViewEvents`
Analytics sur les consultations de cours.

```sql
CREATE TABLE CourseViewEvents (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT,
    CourseId INT NOT NULL,
    ViewedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    DurationSeconds INT,
    ScrollDepth INT,
    Source VARCHAR(50),
    FOREIGN KEY (UserId) REFERENCES Users(Id) ON DELETE SET NULL,
    FOREIGN KEY (CourseId) REFERENCES Courses(Id) ON DELETE CASCADE
);
```

#### `CourseComments`
Commentaires et questions sur les cours.

```sql
CREATE TABLE CourseComments (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    UserId INT NOT NULL,
    CourseId INT NOT NULL,
    Comment TEXT NOT NULL,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    Status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (UserId) REFERENCES Users(Id) ON DELETE CASCADE,
    FOREIGN KEY (CourseId) REFERENCES Courses(Id) ON DELETE CASCADE
);
```

### 3. Fichiers PHP

#### `includes/course_markdown_loader.php`
Fonctions principales pour la gestion des cours :

- `loadCourseMarkdown($filePath)` - Charge et parse un fichier markdown
- `parseMarkdownCourse($content)` - Extrait sections, objectifs, métadonnées
- `getCourseById($courseId, $loadMarkdown)` - Récupère un cours par ID
- `getCoursesBySubjectAndLevel($subject, $level, $limit)` - Liste les cours
- `getCoursesForExercise($exerciseId)` - Cours liés à un exercice
- `getExercisesForCourse($courseId)` - Exercices liés à un cours
- `linkExerciseToCourse($exerciseId, $courseId, $linkType)` - Créer un lien
- `updateUserCourseProgress($userId, $courseId, $status, $timeSpent)` - Suivi progression

#### `tools/import_courses_exercises.php`
Script d'importation des cours et exercices depuis les fichiers markdown.

**Usage** :
```bash
php tools/import_courses_exercises.php action=import
```

**Résultats dernière exécution** :
- 19 cours importés
- 63 exercices importés
- 0 erreurs

#### `tools/create_course_exercise_links.php`
Script de création automatique de liens intelligents entre cours et exercices.

**Usage** :
```bash
php tools/create_course_exercise_links.php
```

**Algorithme de matching** :
1. Filtrage par niveau + matière
2. Extraction de mots-clés (4+ caractères)
3. Règles spécifiques par matière (Pythagore, fractions, cellule, etc.)
4. Score de correspondance basé sur les mots-clés partagés

**Résultats** : 32 liens créés

### 4. Pages utilisateur

#### `view_course.php`
Page d'affichage d'un cours complet avec :
- Métadonnées (matière, niveau, numéro)
- Contenu markdown rendu en HTML
- Liste des exercices liés
- Responsive design

**URL** : `view_course.php?id={courseId}`

#### `cours.php` (modifié)
Page de liste des cours par matière :
- Charge les cours depuis la BDD via `getCoursesBySubjectAndLevel()`
- Support du mode démo (1 cours maximum)
- Affiche aperçu + nombre d'exercices liés
- Liens vers `view_course.php`

#### `exercices.php` + API
Exercices affichent maintenant les cours liés :
- `api/get_exercises.php` modifié pour inclure `LinkedCourses`
- `assets/js/dynamic-exercises.js` affiche section "📚 Cours associés"
- Liens directs vers `view_course.php`

### 5. Styles CSS

Ajouts dans `assets/css/style.css` :

- `.linked-courses-section` - Conteneur des cours liés
- `.linked-course-item` - Carte de cours avec effet hover
- `.cours-exercises-count` - Badge nombre d'exercices
- `.no-cours-message` - Message quand aucun cours

## Flux utilisateur

### Consultation de cours

1. **Accès** : Utilisateur va sur [cours.php](d:\Hostinger\public_html\moncoachscolaire\cours.php)
2. **Navigation** : Sélectionne une matière (Mathématiques, Français, etc.)
3. **Liste** : Voit les cours disponibles pour son niveau
4. **Détail** : Clique "📖 Voir le cours complet"
5. **Apprentissage** : Lit le cours sur [view_course.php](d:\Hostinger\public_html\moncoachscolaire\view_course.php)
6. **Pratique** : Clique sur les exercices liés pour s'entraîner

### Consultation d'exercices

1. **Accès** : Utilisateur va sur [exercices.php](d:\Hostinger\public_html\moncoachscolaire\exercices.php)
2. **Sélection** : Choisit un exercice
3. **Révision** : Voit section "📚 Cours associés" si disponible
4. **Retour théorie** : Clique sur un cours pour réviser avant de faire l'exercice

## Mode démo

Les comptes démo voient :
- **1 seul cours** par matière dans [cours.php](d:\Hostinger\public_html\moncoachscolaire\cours.php)
- Tous les cours liés dans les exercices (pas de limite)

Code de détection :
```php
if ($is_demo && !$is_admin && !empty($courses)) {
    $courses = [reset($courses)]; // Limiter à 1 cours
}
```

## Statistiques

### Données importées
- **Cours** : 19 fichiers markdown
  - 3ème : 7 cours (Mathématiques, Français, SVT, Physique-Chimie, Anglais, Histoire-Géo)
  - 4ème : 4 cours
  - 5ème : 4 cours
  - 6ème : 4 cours

- **Exercices** : 63 nouveaux exercices
  - Total base : 216 exercices

- **Liens** : 32 associations cours-exercices

### Exemples de liens créés

| Niveau | Matière | Cours | Exercices liés |
|--------|---------|-------|----------------|
| 3ème | Mathématiques | Équations | 4 exercices |
| 6ème | SVT | La cellule | 1 exercice |
| 5ème | Anglais | Present Simple | 1 exercice |
| 4ème | Physique | Circuits électriques | 1 exercice |

## Maintenance

### Ajouter un nouveau cours

1. **Créer le fichier** : `cours/college/{niveau}/{matiere}/cours-{n}-{slug}.md`
2. **Format markdown** :
```markdown
# Titre du cours

## Objectifs
- Objectif 1
- Objectif 2

## Introduction
Texte d'introduction...

## Section 1
Contenu...

## Exercices recommandés
- Exercice 1
- Exercice 2
```

3. **Importer** :
```bash
php tools/import_courses_exercises.php action=import
```

4. **Créer liens** :
```bash
php tools/create_course_exercise_links.php
```

### Modifier un cours existant

1. Modifier le fichier `.md`
2. Ré-importer (l'import met à jour si déjà existant)
3. Les liens existants sont préservés

### Supprimer un cours

```sql
DELETE FROM Courses WHERE Id = {courseId};
-- Les liens et progressions sont supprimés automatiquement (CASCADE)
```

## Migration en production

1. **Uploader les fichiers** :
   - `cours/` complet
   - `includes/course_markdown_loader.php`
   - `view_course.php`
   - `cours.php` (modifié)
   - `api/get_exercises.php` (modifié)
   - `assets/js/dynamic-exercises.js` (modifié)
   - `assets/css/style.css` (avec ajouts)

2. **Créer les tables** :
```bash
mysql -u user -p database < db/migration_courses_mysql.sql
```

3. **Importer les données** :
```bash
php tools/import_courses_exercises.php action=import
php tools/create_course_exercise_links.php
```

4. **Vérifier** :
```sql
SELECT COUNT(*) FROM Courses; -- Devrait retourner 19
SELECT COUNT(*) FROM ExerciseCourseLinks; -- Devrait retourner 32
```

## Logs et debug

### Vérifier les imports
```bash
tail -f /xampp/apache/logs/error.log
# ou
tail -f /var/log/apache2/error.log
```

### Tester la connexion BDD
```bash
php db/test_connection.php
```

### Vérifier les cours chargés
```php
require_once 'includes/course_markdown_loader.php';
$courses = getCoursesBySubjectAndLevel('Mathématiques', '3ème');
print_r($courses);
```

## Améliorations futures

1. **Système de favoris** : Permettre aux élèves de marquer des cours
2. **Notes personnelles** : Ajouter des annotations sur les cours
3. **Quiz intégrés** : Questions dans les cours eux-mêmes
4. **Parcours recommandés** : Suggérer ordre de consultation
5. **Analytics avancées** : Temps passé, sections les plus consultées
6. **Export PDF** : Télécharger cours en PDF
7. **Mode hors-ligne** : PWA avec cache des cours
8. **Recherche full-text** : Chercher dans le contenu des cours

## Support

Pour toute question sur l'intégration des cours :
- Consulter `DOCUMENTATION.md`
- Vérifier les logs Apache/PHP
- Tester les fonctions dans `includes/course_markdown_loader.php`
- Vérifier l'intégrité de la BDD avec les foreign keys

---

**Date de création** : 2025-01-30  
**Dernière mise à jour** : 2025-01-30  
**Version** : 1.0
