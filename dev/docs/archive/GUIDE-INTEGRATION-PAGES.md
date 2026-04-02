# Guide d'Intégration dans les Pages Existantes

## 📋 Vue d'Ensemble

Ce guide explique comment intégrer le système d'exercices dans les pages PHP existantes tout en conservant le contenu statique comme fallback.

---

## 🎯 Stratégie d'Intégration

### Approche Hybride

1. **Essayer de charger depuis la DB** en premier
2. **Si disponible** → Afficher les exercices depuis la DB
3. **Sinon** → Afficher le contenu statique existant

Cette approche garantit :
- ✅ Le site fonctionne même sans base de données
- ✅ Progression douce vers le nouveau système
- ✅ Pas de rupture pour les utilisateurs existants

---

## 📝 Exemple d'Intégration

### Étape 1 : Ajouter les includes en haut du fichier

```php
<?php
$page_title = 'Exercices 6ème - MonCoachScolaire';
$page_css = 'college/6eme/exercices-6eme.css';

// Vérifier si l'utilisateur est connecté
$has_coach_access = !empty($is_logged_in);

// Charger le système d'exercices (si disponible)
$exercisesLoaded = false;
$mathExercises = [];
$frenchExercises = [];

try {
    require_once __DIR__ . '/../../../includes/exercice_loader.php';
    require_once __DIR__ . '/../../../includes/exercice_card.php';
    
    if (isset($pdo) && $pdo) {
        $allExercises = getExercicesByLevel('6ème');
        
        if (!empty($allExercises)) {
            $exercisesLoaded = true;
            
            // Séparer par matière
            foreach ($allExercises as $exercise) {
                $subject = $exercise['Subject'] ?? '';
                if ($subject === 'Mathématiques') {
                    $mathExercises[] = $exercise;
                } elseif ($subject === 'Français') {
                    $frenchExercises[] = $exercise;
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Erreur chargement exercices: " . $e->getMessage());
    $exercisesLoaded = false;
}
?>
```

### Étape 2 : Remplacer la section des exercices

**AVANT** (contenu statique) :
```php
<section id="francais">
    <h2>📚 Français - Maître des Mots</h2>
    
    <div class="exercise-card">
        <!-- Contenu statique de l'exercice -->
    </div>
</section>
```

**APRÈS** (hybride) :
```php
<section id="francais">
    <h2>📚 Français - Maître des Mots</h2>

    <div class="coach-message">
        <strong>💬 Petit conseil de coach :</strong> Le français, c'est comme un jeu vidéo...
    </div>

    <?php if ($exercisesLoaded && !empty($frenchExercises)): ?>
        <!-- Afficher les exercices depuis la DB -->
        <div class="exercise-subject-group">
            <div class="exercise-grid">
                <?php
                foreach ($frenchExercises as $exercise) {
                    renderExerciseCard($exercise, [
                        'showAnswer' => false,
                        'showDetails' => true
                    ]);
                }
                ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Fallback : Contenu statique existant -->
        <div class="exercise-card">
            <!-- Votre contenu statique existant ici -->
        </div>
    <?php endif; ?>
</section>
```

---

## 🔧 Modifications par Page

### Page 6ème : `pages/college/6eme/exercices-6eme.php`

**À ajouter en haut** :
- Chargement du système d'exercices
- Variables `$exercisesLoaded`, `$mathExercises`, `$frenchExercises`

**À modifier** :
- Section `<!-- FRANÇAIS -->` (ligne ~110)
- Section `<!-- MATHÉMATIQUES -->` (ligne ~170)

**Voir** : `pages/college/6eme/exercices-6eme-integrated.php` (version exemple)

### Page 3ème : `pages/college/3eme/exercices-3eme.php`

**Modifications similaires** :
```php
$allExercises = getExercicesByLevel('3ème');
// Filtrer pour Mathématiques uniquement (pas de Français en 3ème pour l'instant)
```

### Page Seconde : `pages/lycee/seconde/exercices-seconde.php`

```php
$allExercises = getExercicesByLevel('Seconde');
// Filtrer pour Mathématiques uniquement
```

### Page Première : `pages/lycee/premiere/exercices-premiere.php`

```php
$allExercises = getExercicesByLevel('Première');
// Filtrer pour Français uniquement (pas de Math en Première pour l'instant)
```

---

## ✅ Checklist d'Intégration

Pour chaque page à intégrer :

- [ ] Ajouter les includes en haut du fichier
- [ ] Initialiser les variables de chargement
- [ ] Charger les exercices depuis la DB
- [ ] Séparer par matière si nécessaire
- [ ] Remplacer chaque section d'exercices par le code hybride
- [ ] Conserver le contenu statique comme fallback
- [ ] Tester avec et sans base de données
- [ ] Vérifier l'affichage sur la page

---

## 🎨 Styles CSS

Les composants utilisent les classes CSS existantes :
- `.exercise-card` - Déjà défini dans les CSS
- `.exercise-grid` - À ajouter si nécessaire
- `.exercise-subject-group` - Pour grouper par matière

### CSS Additionnel (si nécessaire)

```css
.exercise-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 1.5rem 0;
}

.exercise-subject-group {
    margin: 2rem 0;
}
```

---

## 🐛 Gestion des Erreurs

### Si la DB n'est pas disponible

Le système détecte automatiquement et affiche le contenu statique :

```php
try {
    // Tentative de chargement
    $exercises = getExercicesByLevel('6ème');
    $exercisesLoaded = !empty($exercises);
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    $exercisesLoaded = false;
    // Le fallback statique sera affiché
}
```

### Si aucun exercice n'est trouvé

```php
<?php if ($exercisesLoaded && !empty($exercises)): ?>
    <!-- Afficher depuis la DB -->
<?php else: ?>
    <!-- Contenu statique -->
<?php endif; ?>
```

---

## 💡 Bonnes Pratiques

1. **Toujours garder le fallback** : Le contenu statique doit rester disponible

2. **Gérer les erreurs silencieusement** : Utiliser `try/catch` pour éviter de casser la page

3. **Loguer les erreurs** : Utiliser `error_log()` pour le débogage

4. **Tester les deux cas** : Avec et sans base de données

5. **Progressive Enhancement** : Le nouveau système améliore l'expérience, mais ne doit pas la casser

---

## 📊 Exemple Complet Minimal

```php
<?php
$page_title = 'Exercices 6ème - MonCoachScolaire';
$page_css = 'college/6eme/exercices-6eme.css';

// Chargement système exercices
$exercisesLoaded = false;
$exercises = [];

try {
    require_once __DIR__ . '/../../../includes/exercice_loader.php';
    require_once __DIR__ . '/../../../includes/exercice_card.php';
    
    if (isset($pdo) && $pdo) {
        $exercises = getExercicesByLevel('6ème', 'Mathématiques');
        $exercisesLoaded = !empty($exercises);
    }
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
}
?>

<main>
    <h1>Exercices 6ème</h1>
    
    <section id="maths">
        <h2>Mathématiques</h2>
        
        <?php if ($exercisesLoaded): ?>
            <div class="exercise-grid">
                <?php foreach ($exercises as $exercise): ?>
                    <?php renderExerciseCard($exercise); ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Contenu statique -->
            <div class="exercise-card">
                <h3>Exercice statique</h3>
                <!-- ... -->
            </div>
        <?php endif; ?>
    </section>
</main>
```

---

## 🚀 Prochaines Étapes

1. **Intégrer dans la page 6ème** (priorité 1)
2. **Tester avec données réelles**
3. **Intégrer dans les autres pages**
4. **Ajouter la gamification** (Phase 4)

---

**Date de création** : 2025-01-XX
**Version** : 1.0

