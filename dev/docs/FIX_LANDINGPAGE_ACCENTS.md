# Correction des accents cassés dans landingpage.php

## Problème identifié
Sur la landing page, les niveaux avec accents s'affichaient avec des caractères corrompus :
- ❌ "Ton espace 6??me"  
- ❌ "Tu es connecté en tant qu'élève de 6??me"

Cela affectait tous les niveaux avec accents : 6ème, 5ème, 4ème, 3ème, Première

## Solution appliquée

### 1. Intégration du système de normalisation
**Fichier modifié:** `src/pages/landingpage.php`

Ajout de la normalisation des niveaux après le chargement de `site_boot.php` :
```php
// Charger le système de normalisation des niveaux pour gérer les problèmes d'encodage UTF-8
require_once __DIR__ . '/../includes/level_normalization.php';
```

### 2. Création de la variable d'affichage
Après récupération du niveau utilisateur :
```php
$user_level = $_SESSION['user_level'] ?? '';
$user_level_display = get_level_display_name($user_level); // Normalise pour affichage
```

### 3. Utilisation des helpers pour la détection
Remplacement des vérifications manuelles :
```php
// AVANT
$is_college = in_array($user_level, ['6ème', '5ème', '4ème', '3ème']);
$is_lycee = in_array($user_level, ['Seconde', 'Première', 'Terminale']);

// APRÈS
$is_college = is_college_level($user_level);
$is_lycee = is_lycee_level($user_level);
```

### 4. Remplacement dans tous les affichages
Tous les `htmlspecialchars($user_level)` ont été remplacés par `htmlspecialchars($user_level_display)` :

- **Header** (ligne ~124)
- **Titre de section** (ligne ~140)
- **Message de bienvenue** (ligne ~158)
- **Alt des images** (mascottes)

### 5. Normalisation des mappings
Pour les cartes de niveau collège et lycée :
```php
// Créer une variable normalisée pour le mapping
$user_level_normalized = normalize_school_level($user_level);

// Utiliser des clés normalisées (sans accents)
$level_map = [
    '6eme' => ['title' => '6ème', ...],
    '5eme' => ['title' => '5ème', ...],
    // ...
];

// Utiliser le niveau normalisé pour le mapping
$level_info = $level_map[$user_level_normalized] ?? ['title' => $user_level_display, ...];
```

### 6. Comparaisons avec levels_match()
Pour les conditions Première/Terminale :
```php
// AVANT
<?php if ($user_level === 'Première' || $user_level === 'Terminale'): ?>

// APRÈS
<?php if (levels_match($user_level, 'Première') || levels_match($user_level, 'Terminale')): ?>
```

## Tests de validation

### Test 1 : Suite de tests complète
**Fichier:** `tools/test_landingpage_normalization.php`
```bash
php tools/test_landingpage_normalization.php
```
**Résultat:** ✅ 7/7 tests passés

### Test 2 : Simulation d'affichage
**Fichier:** `tools/simulate_landingpage_display.php`
```bash
php tools/simulate_landingpage_display.php
```
**Résultat:**
```
Niveau en session: '6??me' (corrompu)
Niveau affiché: '6ème' (normalisé)

Header: "Ton espace personnalisé pour le niveau 6ème"
Message: "Tu es connecté en tant qu'élève de 6ème."

✅ OK: Le niveau '6??me' a été converti en '6ème'
```

### Test 3 : Non-régression
```bash
php tools/test_homogenization_access.php  # ✅ 15/15
php tools/test_level_normalization.php    # ✅ 42/42
```

## Résultat final

### Avant
- ❌ "Ton espace 6??me"
- ❌ "Tu es connecté en tant qu'élève de 6??me"

### Après
- ✅ "Ton espace 6ème"
- ✅ "Tu es connecté en tant qu'élève de 6ème"

## Fichiers modifiés
1. `src/pages/landingpage.php` - Intégration complète de la normalisation
2. `tools/test_landingpage_normalization.php` - Suite de tests (NOUVEAU)
3. `tools/simulate_landingpage_display.php` - Simulation d'affichage (NOUVEAU)

## Impact
- **Tous les niveaux** avec accents (6ème à 3ème, Première) s'affichent maintenant correctement
- **Aucune régression** sur les tests existants
- **Robustesse** : Fonctionne même si la session contient des encodages corrompus (6??me, 5??me, etc.)
