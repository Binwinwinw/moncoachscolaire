# 🔍 Guide de Débogage - Affichage des Exercices

## ❓ Problème : La page n'affiche pas d'exercices

### Causes Possibles

1. **Exercices non importés dans la base de données**
2. **Base de données non configurée**
3. **Erreur de chargement des fichiers includes**
4. **Problème de connexion à la base de données**

---

## 🔧 Diagnostic Étape par Étape

### Étape 1 : Vérifier que les exercices sont importés

**Commande** :
```bash
php tools/import_exercices_to_db.php --dry-run --limit=3
```

**Si ça fonctionne** : Passez à l'étape 2
**Si erreur** : Vérifiez la connexion à la base de données

### Étape 2 : Vérifier la base de données

**Option A - Via SQL** :
```sql
SELECT COUNT(*) FROM Exercises WHERE Level = '6ème';
```

**Option B - Via PHP** :
Créez un fichier de test : `test_db_exercises.php`
```php
<?php
require_once 'db/connection.php';
require_once 'includes/exercice_loader.php';

if (!$pdo) {
    die("❌ Base de données non connectée");
}

$exercises = getExercicesByLevel('6ème');
echo "Exercices trouvés : " . count($exercises) . "\n";
foreach ($exercises as $ex) {
    echo "- " . $ex['Title'] . " (" . $ex['Subject'] . ")\n";
}
?>
```

Exécutez : `php test_db_exercises.php`

### Étape 3 : Vérifier les fichiers includes

**Vérifier que les fichiers existent** :
```bash
ls includes/exercice_loader.php
ls includes/exercice_card.php
```

**Vérifier les chemins dans la page** :
Le fichier `pages/college/6eme/exercices-6eme.php` doit charger :
```php
require_once __DIR__ . '/../../../includes/exercice_loader.php';
require_once __DIR__ . '/../../../includes/exercice_card.php';
```

### Étape 4 : Activer le mode debug

Ajoutez ce code temporairement dans la page pour voir ce qui se passe :

```php
<?php
// DEBUG (à retirer après)
echo "<!-- DEBUG: has_coach_access = " . ($has_coach_access ? 'true' : 'false') . " -->";
echo "<!-- DEBUG: exercisesLoaded = " . ($exercisesLoaded ? 'true' : 'false') . " -->";
echo "<!-- DEBUG: mathExercises count = " . count($mathExercises) . " -->";
echo "<!-- DEBUG: frenchExercises count = " . count($frenchExercises) . " -->";
echo "<!-- DEBUG: pdo exists = " . (isset($pdo) && $pdo ? 'true' : 'false') . " -->";
?>
```

---

## ✅ Solutions

### Solution 1 : Importer les exercices

Si les exercices ne sont pas dans la base de données :

```bash
php tools/import_exercices_to_db.php
```

### Solution 2 : Vérifier la configuration de la base de données

**Vérifier les variables d'environnement** :
- `DB_HOST`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

**Tester la connexion** :
```bash
php db/test_connection.php
```

### Solution 3 : Vérifier les chemins

**Le fichier est dans** : `pages/college/6eme/exercices-6eme.php`
**Les includes doivent être à** : `includes/exercice_loader.php` (depuis la racine)

**Chemin relatif** :
- `pages/college/6eme/` → `../../../` → racine
- Donc : `__DIR__ . '/../../../includes/exercice_loader.php'`

### Solution 4 : Vérifier les permissions

Les fichiers includes doivent être lisibles par PHP.

---

## 🎯 Messages d'Erreur Courants

### "Fonction getExercicesByLevel non trouvée"
**Solution** : Vérifier que `includes/exercice_loader.php` est bien chargé

### "Base de données non disponible"
**Solution** : Configurer la connexion à la base de données (voir `db/README.md`)

### "Aucun exercice trouvé dans la base de données"
**Solution** : Importer les exercices avec le script d'import

### Page blanche ou erreur PHP
**Solution** : Vérifier les logs d'erreur PHP (`error_log`)

---

## 📝 Checklist de Vérification

- [ ] Les exercices sont importés dans la DB
- [ ] La base de données est configurée et accessible
- [ ] Les fichiers includes existent et sont accessibles
- [ ] Les chemins relatifs sont corrects
- [ ] Les fonctions PHP sont chargées
- [ ] Aucune erreur dans les logs PHP
- [ ] La page PHP n'a pas d'erreurs de syntaxe

---

## 🔍 Test Rapide

Créez un fichier `test_page_exercices.php` à la racine :

```php
<?php
require_once 'db/connection.php';
require_once 'includes/exercice_loader.php';
require_once 'includes/exercice_card.php';

echo "<h1>Test d'affichage des exercices</h1>";

if (!$pdo) {
    die("<p>❌ Base de données non connectée</p>");
}

$exercises = getExercicesByLevel('6ème');
echo "<p>Exercices trouvés : " . count($exercises) . "</p>";

if (empty($exercises)) {
    echo "<p>⚠️ Aucun exercice. Lancez : php tools/import_exercices_to_db.php</p>";
} else {
    echo "<h2>Affichage des exercices :</h2>";
    renderExerciseList($exercises, ['showAnswer' => false]);
}
?>
```

Exécutez : `php test_page_exercices.php`

---

## 💡 Informations de Debug dans la Page

La page affiche maintenant automatiquement des messages selon la situation :

1. **Si DB non disponible** : Message d'avertissement + contenu statique
2. **Si aucun exercice** : Message + instructions pour importer
3. **Si exercices trouvés** : Affichage avec compteur

---

**Date de création** : 2025-01-XX
**Version** : 1.0


