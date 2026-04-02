# Correction complète des accents cassés : Affichage du niveau utilisateur

## Problème identifié
Plusieurs pages affichaient les niveaux avec accents cassés au lieu de la bonne UTF-8 :
- ❌ "6??me" au lieu de "6ème"
- ❌ "Premi??re" au lieu de "Première"
- ❌ Dans dashboard: "Apprenti Érudit • Niveau scolaire : 6??me"
- ❌ Dans quiz: "Quiz du 01/01/2026 - ...Niveau : 6??me"
- ❌ Dans cours: "Cours adaptés à ton niveau : 6??me"

## Solution appliquée : Intégration systématique de la normalisation

### 1. **Landing Page** (`src/pages/landingpage.php`) ✅
- ✓ Charger `level_normalization.php`
- ✓ Créer `$user_level_display`
- ✓ Remplacer `$user_level` → `$user_level_display` (3 occurrences d'affichage)
- ✓ Utiliser `is_college_level()`, `is_lycee_level()` pour détection

### 2. **Dashboard** (`src/pages/dashboard.php`) ✅
- ✓ Charger `level_normalization.php`
- ✓ Créer `$user_level_display` et `$user_level_normalized`
- ✓ Remplacer clés du `$levelConfig` : '6ème' → '6eme', 'Première' → 'Premiere'
- ✓ Affichage du niveau (ligne 291) : `$user_level` → `$user_level_display`
- ✓ Message de recommandation (ligne 728) : `$user_level` → `$user_level_display`
- ✓ Utiliser `is_college_level()` au lieu de `in_array()`

### 3. **Quiz** (`src/pages/quiz.php`) ✅
- ✓ Charger `level_normalization.php`
- ✓ Créer `$user_level_display`
- ✓ Header du quiz (ligne 62) : `$user_level` → `$user_level_display`
- ✓ Message du coach (ligne 79) : `$user_level` → `$user_level_display`
- ✓ Grille des autres quiz (ligne 161) : `$user_level` → `$user_level_display`
- ✓ Utiliser `is_college_level()`, `is_lycee_level()` pour détection

### 4. **Cours** (`src/pages/cours.php`) ✅
- ✓ Charger `level_normalization.php`
- ✓ Créer `$user_level_display`
- ✓ Subtitle (ligne 113) : `$user_level` → `$user_level_display`
- ✓ Message démo (ligne 154) : `$user_level` → `$user_level_display`
- ✓ Message "Aucun cours" (ligne 305) : `$user_level` → `$user_level_display`
- ✓ Utiliser `is_college_level()`, `is_lycee_level()` pour détection

### 5. **Cours Détail** (`src/pages/cours-detail.php`) ✅
- ✓ Charger `level_normalization.php`
- ✓ Créer `$user_level_display`
- ✓ Meta du cours (ligne 131) : `$user_level` → `$user_level_display`
- ✓ Utiliser `is_college_level()` pour détection

### 6. **Lycée Accueil** (`src/pages/lycee/lycee-accueil.php`) ✅
- ✓ Bouton "Guide de remédiation" (ligne 133) : `$user_level` → `$user_level_display`

## Résumé des modifications

| Fichier | Charge normalisation | Crée $user_level_display | Affichages corrigés |
|---------|:---:|:---:|:---:|
| landingpage.php | ✓ | ✓ | 3 |
| dashboard.php | ✓ | ✓ | 2 + config |
| quiz.php | ✓ | ✓ | 3 |
| cours.php | ✓ | ✓ | 3 |
| cours-detail.php | ✓ | ✓ | 1 |
| lycee-accueil.php | ✓ (déjà) | ✓ (déjà) | 1 |

## Tests de validation

### ✅ Test de normalisation des niveaux
```
php tools/test_level_normalization.php
Résultat: 42/42 ✅
```

### ✅ Test d'homogénéisation
```
php tools/test_homogenization_access.php
Résultat: 15/15 ✅
```

### ✅ Tests spécifiques par page
- `test_landingpage_normalization.php`: 7/7 ✅
- `test_dashboard_normalization.php`: 5/5 ✅
- `test_quiz_normalization.php`: 5/5 ✅

## Résultat final

### Avant la correction:
```
Apprenti Érudit • Niveau scolaire : 6??me
Quiz du 01/01/2026 - Niveau : 6??me
Cours adaptés à ton niveau : 6??me
```

### Après la correction:
```
Apprenti Érudit • Niveau scolaire : 6ème ✅
Quiz du 01/01/2026 - Niveau : 6ème ✅
Cours adaptés à ton niveau : 6ème ✅
```

## Impact
- ✅ **Tous les niveaux** (6ème à Terminale) s'affichent maintenant avec la bonne UTF-8
- ✅ **Aucune régression** sur les tests existants
- ✅ **Robustesse accrue** : Fonctionne même avec des encodages corrompus en session
- ✅ **Homogénéité** : Même approche systématique partout

## Fichiers créés pour les tests
1. `tools/test_landingpage_normalization.php`
2. `tools/test_dashboard_normalization.php`
3. `tools/test_quiz_normalization.php`
4. `tools/test_global_level_normalization.php`
5. `tools/simulate_landingpage_display.php`

## Système de normalisation utilisé
Le système utilise deux niveaux :
1. **Normalisation interne** : `normalize_school_level('6??me')` → `'6eme'`
2. **Affichage UTF-8** : `get_level_display_name('6eme')` → `'6ème'`
3. **Détection robuste** : `levels_match('6??me', '6ème')` → `true`

Voir `src/includes/level_normalization.php` pour la documentation complète.
