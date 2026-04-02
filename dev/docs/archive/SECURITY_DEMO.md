# Sécurité du Compte Démo - MonCoachScolaire

## Vue d'ensemble

Ce document décrit les mesures de sécurité mises en place pour garantir que le compte démo n'a accès qu'aux fonctionnalités autorisées et ne peut pas compromettre la sécurité de l'application.

## Principes de Sécurité

### 1. Principe du Moindre Privilège
Le compte démo a **uniquement** les droits de lecture. Aucune opération d'écriture n'est autorisée.

### 2. Isolation Complète
- Le compte démo est isolé des autres utilisateurs
- Aucun accès aux données sensibles (mots de passe, emails réels, données parentales)
- Aucun accès aux fonctionnalités administratives

### 3. Vérification Multi-Niveaux
- Vérification au niveau de la session (`$_SESSION['is_demo']`)
- Vérification au niveau de la base de données (email/username)
- Vérification au niveau des APIs (blocage des écritures)

## Mesures de Sécurité Implémentées

### 1. Système de Sécurité Centralisé (`includes/demo_security.php`)

Fichier central contenant toutes les fonctions de sécurité :

- `isDemoUser()` : Vérifie si l'utilisateur actuel est en mode démo
- `isDemoAccount($userId)` : Vérifie si un ID utilisateur correspond au compte démo
- `blockDemoAccess()` : Bloque l'accès et redirige vers la page de démo
- `checkDemoWritePermission()` : Vérifie les permissions d'écriture
- `checkDemoPageAccess()` : Vérifie l'accès aux pages sensibles
- `enforceDemoReadOnly()` : Force le mode lecture seule pour les requêtes SQL

### 2. Protection des APIs

#### `api/save-progress.php`
- ✅ Vérification `isDemoUser()` avant toute sauvegarde
- ✅ Blocage immédiat avec code HTTP 403 si compte démo
- ✅ Message explicite invitant à créer un compte

#### `api/track-demo-action.php`
- ✅ Vérification que l'utilisateur est bien en mode démo
- ✅ Opération limitée à la session uniquement (pas de BDD)
- ✅ Blocage si utilisateur non-démo tente d'accéder

### 3. Protection des Pages Sensibles

#### `dashboard.php`
- ✅ Redirection automatique vers `pages/demo.php` si compte démo
- ✅ Empêche l'accès au tableau de bord réel

#### `progression.php`
- ✅ Redirection vers l'aperçu de démo si compte démo
- ✅ Empêche l'accès à la progression réelle

#### `parents.php` et `suivi_enfant.php`
- ✅ Protection existante via `$_SESSION['parent_id']`
- ✅ Le compte démo n'a jamais `parent_id` défini
- ✅ Accès automatiquement bloqué

### 4. Protection des Fonctions de Gamification

#### `includes/gamification.php`
- ✅ Fonction `completeExercise()` vérifie `isDemoAccount()`
- ✅ Retourne une erreur explicite si tentative d'écriture
- ✅ Aucune transaction BDD n'est initiée pour le compte démo

### 5. Protection au Niveau SQL

La fonction `enforceDemoReadOnly()` bloque automatiquement :
- `INSERT` - Insertion de données
- `UPDATE` - Modification de données
- `DELETE` - Suppression de données
- `DROP` - Suppression de tables
- `ALTER` - Modification de structure
- `CREATE` - Création d'objets
- `TRUNCATE` - Vidage de tables

## Restrictions du Compte Démo

### ✅ Autorisé
- ✅ Consultation des exercices (lecture seule)
- ✅ Consultation des cours (lecture seule)
- ✅ Consultation des quiz (lecture seule)
- ✅ Aperçu du Labo des Génies (données pré-remplies)
- ✅ Test des exercices interactifs (sans sauvegarde)
- ✅ Navigation dans la page de démo

### ❌ Bloqué
- ❌ Sauvegarde de progression dans la BDD
- ❌ Modification de profil utilisateur
- ❌ Accès au dashboard réel
- ❌ Accès à la progression réelle
- ❌ Accès aux pages parents/admin
- ❌ Modification de données d'autres utilisateurs
- ❌ Toute opération d'écriture en BDD
- ❌ Accès aux données sensibles

## Vérifications de Sécurité

### Points de Contrôle

1. **Au niveau de la session** :
   ```php
   if (isDemoUser()) {
       // Blocage ou redirection
   }
   ```

2. **Au niveau des APIs** :
   ```php
   if (isDemoUser()) {
       http_response_code(403);
       exit;
   }
   ```

3. **Au niveau des fonctions** :
   ```php
   if (isDemoAccount($userId)) {
       return ['error' => 'Opération non autorisée'];
   }
   ```

4. **Au niveau SQL** :
   ```php
   $sql = enforceDemoReadOnly($sql);
   ```

## Tests de Sécurité Recommandés

### Tests Manuels

1. **Test d'accès dashboard** :
   - Se connecter en mode démo
   - Tenter d'accéder à `/index.php?page=dashboard`
   - ✅ Vérifier la redirection vers la page de démo

2. **Test de sauvegarde** :
   - Compléter un exercice en mode démo
   - Vérifier dans la console réseau que l'API retourne 403
   - ✅ Vérifier qu'aucune donnée n'est sauvegardée en BDD

3. **Test d'accès parents** :
   - Se connecter en mode démo
   - Tenter d'accéder à `/index.php?page=parents`
   - ✅ Vérifier le blocage d'accès

4. **Test de modification** :
   - Tenter de modifier des données via l'API
   - ✅ Vérifier le blocage avec code 403

### Tests Automatisés (À Implémenter)

```php
// tests/test_demo_security.php
function testDemoCannotSaveProgress() {
    // Simuler connexion démo
    $_SESSION['is_demo'] = true;
    $_SESSION['user_id'] = 1; // ID du compte démo
    
    // Tenter de sauvegarder
    $result = callAPI('api/save-progress.php', ['exerciseId' => 1]);
    
    assert($result['code'] === 403);
    assert(strpos($result['body'], 'démo') !== false);
}

function testDemoRedirectedFromDashboard() {
    // Simuler connexion démo
    $_SESSION['is_demo'] = true;
    
    // Tenter d'accéder au dashboard
    $response = simulateRequest('dashboard.php');
    
    assert($response['status'] === 302);
    assert(strpos($response['location'], 'demo') !== false);
}
```

## Monitoring et Logging

Toutes les tentatives d'opérations non autorisées sont loggées :

```php
error_log("SECURITY: Tentative d'écriture bloquée pour compte démo - Opération: $operation");
```

Ces logs permettent de :
- Détecter les tentatives d'abus
- Analyser les patterns suspects
- Améliorer la sécurité si nécessaire

## Réinitialisation du Compte Démo

Le compte démo peut être réinitialisé périodiquement pour :
- Nettoyer les données de test
- Réinitialiser la progression
- Garantir un état propre pour les nouveaux visiteurs

**Script de réinitialisation recommandé** :
```sql
-- Réinitialiser la progression du compte démo
DELETE FROM ExerciseResponses WHERE UserId = (SELECT Id FROM Users WHERE Email = 'demo@example.com');
DELETE FROM UserProgress WHERE UserId = (SELECT Id FROM Users WHERE Email = 'demo@example.com');
DELETE FROM UserPowers WHERE UserId = (SELECT Id FROM Users WHERE Email = 'demo@example.com');
DELETE FROM UserAchievements WHERE UserId = (SELECT Id FROM Users WHERE Email = 'demo@example.com');
```

## Évolutions Futures

### Améliorations Possibles

1. **Rate Limiting** : Limiter le nombre de requêtes par minute pour le compte démo
2. **IP Tracking** : Logger les IPs pour détecter les abus
3. **Session Timeout** : Réduire la durée de session pour le compte démo
4. **Captcha** : Ajouter un captcha après X actions pour éviter les bots
5. **Whitelist/Blacklist** : Système de liste pour bloquer certaines IPs

## Conclusion

Le système de sécurité du compte démo est conçu pour être **robuste, multi-niveaux et défensif**. Chaque point d'entrée vérifie le statut démo et bloque les opérations non autorisées.

**Principe fondamental** : En cas de doute, bloquer plutôt que permettre.

---

**Dernière mise à jour** : Décembre 2024
**Responsable sécurité** : Équipe MonCoachScolaire

