# Vérification de l'Intégrité des Exercices par Niveau

> **Date** : 2025-01-XX  
> **Objectif** : Garantir que chaque élève ne voit QUE les exercices de son niveau

---

## ✅ Règles Strictes

### Principe Fondamental

**Un élève de niveau X ne doit JAMAIS voir des exercices d'un autre niveau.**

- **6ème** → Exercices de 6ème UNIQUEMENT (Collège)
- **5ème** → Exercices de 5ème UNIQUEMENT (Collège)
- **4ème** → Exercices de 4ème UNIQUEMENT (Collège)
- **3ème** → Exercices de 3ème UNIQUEMENT (Collège)
- **Seconde** → Exercices de Seconde UNIQUEMENT (Lycée)
- **Première** → Exercices de Première UNIQUEMENT (Lycée)
- **Terminale** → Exercices de Terminale UNIQUEMENT (Lycée)
- **BAC** → Exercices de BAC UNIQUEMENT (préparation BAC)

### Séparation Collège / Lycée

**CRITIQUE** : Un élève du collège ne doit JAMAIS voir des exercices du lycée, et vice versa.

---

## 🔍 Vérifications Effectuées

### 1. Vérification des Niveaux en Base de Données

✅ **Statut** : Tous les exercices ont un niveau défini et valide

**Répartition actuelle** :
- 6ème : 28 exercices (15 Math, 13 Français)
- 3ème : 15 exercices (15 Math)
- Seconde : 14 exercices (14 Math)
- Première : 15 exercices (15 Français)

### 2. Vérification du Filtrage Strict

✅ **Statut** : Le filtrage fonctionne correctement

**Test effectué** : Pour chaque niveau, vérification que `getExercisesByLevel()` retourne UNIQUEMENT les exercices de ce niveau.

**Résultat** :
- ✅ 6ème : 28 exercices, tous de niveau 6ème
- ✅ 3ème : 15 exercices, tous de niveau 3ème
- ✅ Seconde : 14 exercices, tous de niveau Seconde
- ✅ Première : 15 exercices, tous de niveau Première

### 3. Vérification de la Séparation Collège/Lycée

✅ **Statut** : Aucun mélange entre collège et lycée

**Test effectué** : Vérification qu'un élève du collège ne voit pas d'exercices du lycée, et vice versa.

**Résultat** : ✅ Aucun exercice du mauvais niveau trouvé

---

## 🛠️ Fonction de Filtrage

### `getExercisesByLevel($level, $subject = null, $limit = null, $offset = 0)`

**Localisation** : `includes/exercice_loader.php`

**Fonctionnalités** :
- ✅ Normalise le niveau avant la recherche (gère les variantes : 6ème/6eme)
- ✅ Utilise `WHERE Level IN (...)` pour gérer les variantes
- ✅ Filtre strictement par niveau (pas de mélange)
- ✅ Filtre optionnel par matière

**Exemple d'utilisation** :
```php
// Pour un élève de 6ème
$exercises = getExercisesByLevel('6ème', 'Mathématiques', 10);
// Retourne UNIQUEMENT les exercices de 6ème en Mathématiques
```

---

## ⚠️ Points d'Attention

### 1. Normalisation des Niveaux

**Problème potentiel** : Si un exercice est stocké avec "6eme" (sans accent) mais qu'un élève a le niveau "6ème" (avec accent), la correspondance doit fonctionner.

**Solution** : La fonction `normalizeLevelForDB()` normalise les niveaux avant la recherche.

### 2. Import des Exercices

**Critique** : Lors de l'import, s'assurer que chaque exercice est classé au BON niveau.

**Vérification** : Le script d'import utilise `normalizeLevel()` pour standardiser les niveaux.

### 3. Affichage des Exercices

**Critique** : Toujours utiliser `getExercisesByLevel()` avec le niveau de l'utilisateur, jamais un niveau codé en dur.

**Exemple correct** :
```php
$user_level = $_SESSION['user_level'] ?? '6ème';
$exercises = getExercisesByLevel($user_level, 'Mathématiques');
```

**Exemple INCORRECT** :
```php
// ❌ NE JAMAIS FAIRE ÇA
$exercises = getExercisesByLevel('6ème', 'Mathématiques'); // Niveau codé en dur
```

---

## 🧪 Scripts de Vérification

### 1. `tools/verify_exercises_levels.php`
Vérifie que tous les exercices ont un niveau valide et affiche la répartition.

### 2. `tools/verify_strict_level_filtering.php`
Vérifie que le filtrage est strict : un élève ne voit que les exercices de son niveau.

### 3. `tools/test_level_matching.php`
Teste la correspondance entre différents formats de niveaux (6ème vs 6eme).

---

## 📋 Checklist de Vérification

Avant de mettre en production, vérifier :

- [ ] Tous les exercices ont un niveau défini
- [ ] Les niveaux sont valides (6ème, 5ème, 4ème, 3ème, Seconde, Première, Terminale)
- [ ] Aucun exercice n'est classé dans le mauvais niveau
- [ ] Aucun mélange entre collège et lycée
- [ ] La fonction `getExercisesByLevel()` fonctionne avec toutes les variantes
- [ ] Les pages d'exercices utilisent le niveau de l'utilisateur (pas de niveau codé en dur)
- [ ] Le compte démo fonctionne correctement avec les exercices

---

## 🔧 Maintenance

### Ajouter de Nouveaux Exercices

1. Vérifier que le niveau est correct dans le fichier source
2. Exécuter l'import : `php tools/import_exercices_to_db.php`
3. Vérifier avec : `php tools/verify_strict_level_filtering.php`

### Modifier un Niveau d'Exercice

1. Identifier l'exercice en base de données
2. Mettre à jour le champ `Level`
3. Vérifier avec : `php tools/verify_strict_level_filtering.php`

---

**Date de création** : 2025-01-XX  
**Dernière mise à jour** : 2025-01-XX  
**Auteur** : MonCoachScolaire Team

