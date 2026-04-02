# Guide d'Intégration des Exercices dans les Pages

## 📋 Vue d'Ensemble

Ce guide explique comment intégrer le système d'exercices dans les pages PHP de MonCoachScolaire.

---

## 🚀 Utilisation Basique

### 1. Charger les fonctions nécessaires

Au début de votre page PHP, ajoutez :

```php
<?php
$page_title = 'Exercices 6ème - MonCoachScolaire';

// Charger le système d'exercices
require_once __DIR__ . '/../../includes/exercice_loader.php';
require_once __DIR__ . '/../../includes/exercice_card.php';

// Charger la connexion DB si nécessaire
require_once __DIR__ . '/../../db/connection.php';
?>
```

### 2. Récupérer les exercices depuis la base de données

```php
// Récupérer tous les exercices pour la 6ème
$exercises = getExercisesByLevel('6ème');

// Ou filtrer par matière
$mathExercises = getExercisesByLevel('6ème', 'Mathématiques');
$frenchExercises = getExercisesByLevel('6ème', 'Français');

// Ou un exercice spécifique
$exercise = getExerciseById(1);
```

### 3. Afficher les exercices

```php
<?php
// Afficher une liste d'exercices
renderExerciseList($exercises, [
    'showAnswer' => false,  // Ne pas afficher les corrections par défaut
    'groupBySubject' => true  // Grouper par matière
]);
?>
```

---

## 📝 Exemples Complets

### Exemple 1 : Page simple avec liste d'exercices

```php
<?php
$page_title = 'Exercices 6ème - MonCoachScolaire';
require_once __DIR__ . '/../../includes/exercice_loader.php';
require_once __DIR__ . '/../../includes/exercice_card.php';
require_once __DIR__ . '/../../db/connection.php';
?>

<main class="main-content">
    <h1>⚔️ Exercices 6ème</h1>
    
    <?php
    $exercises = getExercisesByLevel('6ème');
    
    if (empty($exercises)) {
        echo '<p>Aucun exercice disponible pour le moment.</p>';
    } else {
        renderExerciseList($exercises, [
            'showAnswer' => false,
            'groupBySubject' => true
        ]);
    }
    ?>
</main>
```

### Exemple 2 : Page avec filtres par matière

```php
<?php
$page_title = 'Exercices 6ème - MonCoachScolaire';
require_once __DIR__ . '/../../includes/exercice_loader.php';
require_once __DIR__ . '/../../includes/exercice_card.php';
require_once __DIR__ . '/../../db/connection.php';

// Récupérer le filtre matière depuis l'URL
$subject = $_GET['matiere'] ?? null;
$level = '6ème';
?>

<main class="main-content">
    <h1>⚔️ Exercices 6ème</h1>
    
    <!-- Filtres -->
    <div class="exercise-filters">
        <a href="?matiere=" class="filter-btn <?php echo !$subject ? 'active' : ''; ?>">
            Toutes les matières
        </a>
        <a href="?matiere=Mathématiques" class="filter-btn <?php echo $subject === 'Mathématiques' ? 'active' : ''; ?>">
            🧮 Mathématiques
        </a>
        <a href="?matiere=Français" class="filter-btn <?php echo $subject === 'Français' ? 'active' : ''; ?>">
            📚 Français
        </a>
    </div>
    
    <?php
    // Récupérer les exercices selon le filtre
    $exercises = getExercicesByLevel($level, $subject);
    
    // Afficher le nombre d'exercices
    $count = count($exercises);
    echo "<p><strong>$count exercice(s) trouvé(s)</strong></p>";
    
    // Afficher la liste
    renderExerciseList($exercises, [
        'showAnswer' => false,
        'groupBySubject' => !$subject  // Grouper seulement si toutes les matières
    ]);
    ?>
</main>
```

### Exemple 3 : Page d'un exercice spécifique

```php
<?php
$page_title = 'Exercice - MonCoachScolaire';
require_once __DIR__ . '/../../includes/exercice_loader.php';
require_once __DIR__ . '/../../includes/exercice_card.php';
require_once __DIR__ . '/../../db/connection.php';

// Récupérer l'ID depuis l'URL
$exerciseId = (int)($_GET['id'] ?? 0);
$exercise = getExerciseById($exerciseId);
?>

<main class="main-content">
    <?php if ($exercise): ?>
        <div class="exercise-single">
            <a href="exercices-6eme.php" class="back-link">← Retour aux exercices</a>
            <?php
            // Afficher l'exercice complet avec correction
            renderExerciseFull($exercise, [
                'showAnswer' => true
            ]);
            ?>
        </div>
    <?php else: ?>
        <p>Exercice non trouvé.</p>
        <a href="exercices-6eme.php">Retour aux exercices</a>
    <?php endif; ?>
</main>
```

---

## 🎨 Options d'Affichage

### Options de `renderExerciseCard()`

```php
renderExerciseCard($exercise, [
    'showAnswer' => false,      // Afficher la correction (default: false)
    'showDetails' => true,      // Afficher les détails (default: true)
    'cardClass' => 'custom-class'  // Classes CSS supplémentaires
]);
```

### Options de `renderExerciseList()`

```php
renderExerciseList($exercises, [
    'showAnswer' => false,           // Afficher les corrections
    'groupBySubject' => true,        // Grouper par matière
    'emptyMessage' => 'Aucun exercice'  // Message si vide
]);
```

---

## 🔍 Fonctions Disponibles

### Chargement d'exercices

```php
// Par ID
$exercise = getExerciseById($id);

// Par niveau et matière
$exercises = getExercicesByLevel('6ème', 'Mathématiques');

// Comptage
$count = countExercisesByLevel('6ème', 'Mathématiques');

// Recherche
$results = searchExercises('fractions', '6ème', 'Mathématiques');

// Tous avec filtres
$all = getAllExercises(['level' => '6ème', 'subject' => 'Mathématiques']);

// Statistiques
$stats = getExerciseStats();
```

### Normalisation

```php
$level = normalizeLevel('6eme');      // Retourne '6ème'
$subject = normalizeSubject('math');   // Retourne 'Mathématiques'
```

---

## 🎯 Intégration dans les Pages Existantes

### Pour `pages/college/6eme/exercices-6eme.php`

Remplacer le contenu statique par :

```php
<?php
// En haut du fichier, après les require
require_once __DIR__ . '/../../../includes/exercice_loader.php';
require_once __DIR__ . '/../../../includes/exercice_card.php';

// Dans la section Mathématiques (ligne ~170)
$mathExercises = getExercicesByLevel('6ème', 'Mathématiques');
if (!empty($mathExercises)) {
    renderExerciseList($mathExercises, ['showAnswer' => false]);
} else {
    // Afficher le contenu statique de fallback
}
?>
```

---

## 💡 Bonnes Pratiques

1. **Gérer l'absence de base de données** :
   ```php
   if (!$pdo) {
       echo '<p>Base de données non disponible. Affichage du contenu statique.</p>';
       // Afficher le contenu statique existant
   }
   ```

2. **Fallback sur contenu statique** :
   ```php
   $exercises = getExercicesByLevel('6ème');
   if (empty($exercises)) {
       // Afficher le contenu statique existant
   } else {
       renderExerciseList($exercises);
   }
   ```

3. **Pagination** (si beaucoup d'exercices) :
   ```php
   $limit = 10;
   $offset = (int)($_GET['page'] ?? 0) * $limit;
   $exercises = getExercicesByLevel('6ème', null, $limit, $offset);
   ```

4. **Gérer les erreurs** :
   ```php
   try {
       $exercises = getExercicesByLevel('6ème');
   } catch (Exception $e) {
       error_log("Erreur chargement exercices: " . $e->getMessage());
       $exercises = [];
   }
   ```

---

## 🎨 Styles CSS

Les composants utilisent des classes CSS standardisées :

- `.exercise-card` : Carte d'exercice
- `.exercise-header` : En-tête avec titre
- `.exercise-content` : Contenu de l'exercice
- `.exercise-answer` : Section correction
- `.exercise-actions` : Boutons d'action

Voir les fichiers CSS existants dans `assets/css/pages/` pour les styles.

---

## 📝 Checklist d'Intégration

- [ ] Charger `exercice_loader.php` et `exercice_card.php`
- [ ] Charger la connexion DB (`db/connection.php`)
- [ ] Récupérer les exercices avec `getExercicesByLevel()`
- [ ] Afficher avec `renderExerciseList()` ou `renderExerciseCard()`
- [ ] Gérer l'absence de données (fallback)
- [ ] Tester avec différents niveaux et matières
- [ ] Vérifier les styles CSS

---

**Date de création** : 2025-01-XX
**Version** : 1.0

