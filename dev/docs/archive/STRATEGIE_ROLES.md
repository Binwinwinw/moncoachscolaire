# 🔐 STRATÉGIE DES RÔLES - Analyse Complète

## 📊 Vue d'ensemble

Le système MonCoachScolaire utilise un système de rôles basé sur une colonne `Role` dans la table `Users`.

### Rôles actuellement définis

```sql
Role VARCHAR(50) NOT NULL DEFAULT 'student'
```

**Rôles disponibles** :
1. `admin` - Administrateur du système
2. `student` - Élève
3. `parent` - Parent d'élève (en développement)

---

## 🏗️ Architecture

### 1. Table Users (schéma)

```sql
CREATE TABLE `Users` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Username` VARCHAR(100) NOT NULL,
  `Email` VARCHAR(255) NOT NULL UNIQUE,
  `PasswordHash` VARCHAR(255) NOT NULL,
  `Role` VARCHAR(50) NOT NULL DEFAULT 'student',  ← Rôle
  `UserLevel` VARCHAR(10) NOT NULL DEFAULT '6eme',
  `CreatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`)
)
```

**Par défaut** : Tous les nouveaux utilisateurs sont `student`.

---

## 🔑 Vérification des rôles

### Fonction `isAdmin()` 

**Fichier** : `src/includes/admin_auth.php`

```php
function isAdmin() {
    global $pdo;
    
    // 1. Vérifier la session
    if (empty($_SESSION['user_id']) || empty($_SESSION['logged_in'])) {
        return false;
    }
    
    // 2. Vérifier le cache session (rapide)
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        return true;
    }
    
    // 3. Vérifier en base de données (fiable)
    if ($pdo) {
        $stmt = $pdo->prepare('SELECT Role FROM Users WHERE Id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user && $user['Role'] === 'admin') {
            $_SESSION['user_role'] = 'admin';  // Mettre à jour le cache
            return true;
        }
    }
    
    return false;
}
```

**Logique de vérification** :
1. ✅ Vérifier la session (utilisateur connecté)
2. ✅ Vérifier le cache session (`$_SESSION['user_role']`)
3. ✅ Vérifier en base de données (pour sécurité)
4. ✅ Mettre à jour le cache si admin

**Avantages** :
- Cache rapide pour les vérifications répétées
- Fallback base de données pour la sécurité
- Détecte les changements de rôle en temps réel

---

## 🛡️ Contrôle d'accès

### Fonction `requireAdmin()`

**Fichier** : `src/includes/admin_auth.php`

Trois niveaux de sécurité :

```php
function requireAdmin() {
    if (!isAdmin()) {
        // Détecter le type de requête
        $isApiRequest = false;
        
        // 1. Vérifier header Accept
        if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $isApiRequest = true;
        }
        
        // 2. Vérifier le chemin /api/
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            $isApiRequest = true;
        }
        
        // 3. Vérifier le script filename
        if (strpos($_SERVER['SCRIPT_FILENAME'], '/api/') !== false) {
            $isApiRequest = true;
        }
        
        if ($isApiRequest) {
            // Pour API : lancer exception
            throw new Exception('Accès refusé. Administrateur requis.');
            // → Retourne 403 JSON
        } else {
            // Pour pages web : rediriger
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . $baseUrl . '/login.php');
            exit;
        }
    }
}
```

**Réponses selon le contexte** :

| Type de requête | Non-admin | Admin |
|---|---|---|
| **API JSON** | HTTP 403 + JSON | Données |
| **Page web** | Redirection login | Page admin |

---

## 📍 Utilisation dans le code

### 1. Pages Admin

**Fichier** : `src/pages/dashboard_admin.php`

```php
// Vérifier l'authentification admin AVANT de rendre le contenu
if (!isAdmin()) {
    header('Location: /login');
    exit;
}
```

### 2. APIs Admin

**Fichier** : `src/api/admin/*.php`

```php
require_once __DIR__ . '/../../includes/admin_auth.php';

// Vérifier admin AVANT de retourner les données
try {
    requireAdmin();
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès interdit']);
    exit;
}

// Traiter la requête...
```

**Exemples d'APIs protégées** :
- `src/api/admin/stats.php` - Statistiques globales
- `src/api/admin/exercise_quality.php` - Qualité exercices (fusionné)
- `src/api/admin/users.php` - Gestion utilisateurs
- `src/api/admin/logs.php` - Logs système
- `src/api/admin/parents.php` - Gestion parents

### 3. Login/Register

**Fichier** : `src/pages/login.php`

```php
// Récupérer le rôle depuis la base de données
$stmt = $pdo->prepare('SELECT Id, Role FROM Users WHERE Username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    // Sauvegarder le rôle en session
    $_SESSION['user_role'] = $user['Role'];  // 'student' ou 'admin'
    
    // Redirection selon le rôle
    if ($user['Role'] === 'admin') {
        header('Location: /dashboard_admin');
    } elseif ($user['Role'] === 'parent') {
        header('Location: /dashboard_parent');
    } else {
        header('Location: /dashboard');  // student
    }
}
```

---

## 👥 Utilisateurs actuels

### Dans la base de données

```
Id | Username | Email | Role | UserLevel
1  | user1    | user1@test.com | student | 6eme
2  | user2    | user2@test.com | student | 5eme
4  | zinzin   | zinzin@test.com | admin | 6eme
```

**Admin actuel** : 
- Username : `zinzin`
- ID : 4
- Role : `admin`

---

## 🎯 Stratégie Actuelle

### Points forts ✅

1. **Simple et clair** : Une colonne Role VARCHAR(50)
2. **Flexible** : Facile d'ajouter de nouveaux rôles
3. **Sécurisé** : Vérification en base de données + cache
4. **Performant** : Cache session pour éviter les requêtes répétées
5. **Scalable** : Détection automatique API/Web pour les réponses appropriées

### Points à améliorer 🔄

1. **Pas de table de rôles** : Actuellement VARCHAR(50) sans contrainte
   - Solution : Créer table `Roles` avec enum strict

2. **Pas de permissions** : Seulement admin/student/parent
   - Solution : Implémenter un système RBAC (Role-Based Access Control)
   - Exemple : rôles `admin_stats`, `admin_users`, `admin_exercises`

3. **Pas de propriétés rôles** : Pas de description, d'icône, de permissions
   - Solution : Table `Roles` avec relations

4. **Parent role incomplet** : Déclaré mais non implémenté
   - Solution : Finir l'implémentation

---

## 🚀 Recommandations d'amélioration

### Option 1 : Amélioration simple (court terme)

Garder la structure actuelle mais ajouter validation :

```sql
ALTER TABLE Users 
MODIFY Role ENUM('admin', 'student', 'parent') NOT NULL DEFAULT 'student';
```

### Option 2 : RBAC complet (long terme)

Créer un système de permissions :

```sql
CREATE TABLE Roles (
    Id INT PRIMARY KEY,
    Name VARCHAR(50) UNIQUE,
    Description VARCHAR(255),
    Icon VARCHAR(50)
);

CREATE TABLE Permissions (
    Id INT PRIMARY KEY,
    Name VARCHAR(100) UNIQUE,
    Description VARCHAR(255)
);

CREATE TABLE RolePermissions (
    RoleId INT,
    PermissionId INT,
    FOREIGN KEY (RoleId) REFERENCES Roles(Id),
    FOREIGN KEY (PermissionId) REFERENCES Permissions(Id)
);
```

Puis vérifier :

```php
function canAccess($permission) {
    $stmt = $pdo->prepare('
        SELECT 1 FROM RolePermissions rp
        JOIN UserRoles ur ON ur.RoleId = rp.RoleId
        JOIN Permissions p ON p.Id = rp.PermissionId
        WHERE ur.UserId = ? AND p.Name = ?
    ');
    $stmt->execute([$_SESSION['user_id'], $permission]);
    return $stmt->rowCount() > 0;
}
```

---

## 📋 État actuel

| Aspect | État | Notes |
|---|---|---|
| **Authentification** | ✅ Complète | Session + DB |
| **Autorisation Admin** | ✅ Opérationnelle | Vérification stricte |
| **Rôle Student** | ✅ Opérationnel | Par défaut |
| **Rôle Parent** | ⏳ Partiel | Code présent mais incomplet |
| **Validation rôles** | ⚠️ À améliorer | VARCHAR sans contrainte |
| **Système permissions** | ❌ Non implémenté | Seulement niveau rôle |

---

## 💡 Conclusion

La stratégie actuelle est **simple et fonctionnelle** pour un MVP, mais pourrait être améliorée avec :

1. **Court terme** : ENUM strict au lieu de VARCHAR
2. **Moyen terme** : Finir le rôle "parent"
3. **Long terme** : Implémenter un RBAC complet

Le système de rôles est **sécurisé** et **performant** pour l'usage actuel.
