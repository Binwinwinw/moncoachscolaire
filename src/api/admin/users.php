<?php

/**
 * API Admin - Gestion des utilisateurs (VERSION SIMPLIFIÉE)
 * Endpoint: /api/admin/users.php
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../../includes/admin_auth.php';

$adminAuditPath = __DIR__ . '/../../includes/admin_audit.php';
if (file_exists($adminAuditPath)) {
    require_once $adminAuditPath;
}

function sendJsonError($message, $code = 500)
{
    json_error($message, $code, 'ERR_GENERIC');
}

function sendJsonResponse($data)
{
    if (is_array($data) && (isset($data['success']) || isset($data['error']))) {
        header('Content-Type: application/json; charset=utf-8');
        $data['request_id'] = api_request_id();
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    json_response($data, 200);
}

if (!isset($pdo) || !$pdo) {
    json_error('Base de données non disponible', 503, 'ERR_DB');
}

/**
 * Créer la table UserProgress si elle n'existe pas
 */
function ensureUserProgressTable($pdo)
{
    try {
        // Vérifier si la table existe
        $checkStmt = $pdo->query("SHOW TABLES LIKE 'UserProgress'");
        if ($checkStmt->rowCount() > 0) {
            return; // La table existe déjà
        }

        // Créer la table sans contrainte de clé étrangère d'abord (plus simple)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `UserProgress` (
                `Id` INT NOT NULL AUTO_INCREMENT,
                `UserId` INT NOT NULL,
                `CurrentPosition` INT NOT NULL DEFAULT 1,
                `XP` INT NOT NULL DEFAULT 0,
                `ProgressJson` JSON NULL,
                `UpdatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`Id`),
                INDEX `idx_userid` (`UserId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Essayer d'ajouter la contrainte de clé étrangère après (optionnel)
        try {
            $pdo->exec("
                ALTER TABLE `UserProgress`
                ADD CONSTRAINT `FK_UserProgress_User`
                FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`) ON DELETE CASCADE
            ");
        } catch (PDOException $e) {
            // Si ça échoue, ce n'est pas grave, on continue sans la contrainte
            error_log("Note: FK constraint may already exist: " . $e->getMessage());
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la création de UserProgress: " . $e->getMessage());
        // On continue quand même, la requête fonctionnera sans cette table
    }
}

// Vérifier la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    switch ($method) {
        case 'GET':
            api_require([
                'method' => 'GET',
                'auth' => true,
                'roles' => ['admin'],
                'rate' => ['key' => 'admin_users_get', 'limit' => 120, 'window' => 60],
            ]);
            handleGetUsers();
            break;

        case 'POST':
            api_require([
                'method' => 'POST',
                'auth' => true,
                'roles' => ['admin'],
                'csrf' => true,
                'rate' => ['key' => 'admin_users_post', 'limit' => 60, 'window' => 60],
            ]);
            handleCreateUser();
            break;

        case 'PUT':
            api_require([
                'method' => 'PUT',
                'auth' => true,
                'roles' => ['admin'],
                'csrf' => true,
                'rate' => ['key' => 'admin_users_put', 'limit' => 60, 'window' => 60],
            ]);
            handleUpdateUser();
            break;

        case 'DELETE':
            api_require([
                'method' => 'DELETE',
                'auth' => true,
                'roles' => ['admin'],
                'csrf' => true,
                'rate' => ['key' => 'admin_users_delete', 'limit' => 30, 'window' => 60],
            ]);
            handleDeleteUser();
            break;

        default:
            sendJsonError('Méthode non autorisée', 405);
    }
} catch (PDOException $e) {
    error_log("API users.php - Erreur PDO: " . $e->getMessage());
    $errorMsg = 'Erreur de base de données';
    if (defined('APP_ENV') && APP_ENV === 'local') {
        $errorMsg .= ': ' . $e->getMessage();
    }
    sendJsonError($errorMsg, 500);
} catch (Exception $e) {
    error_log("API users.php - Erreur: " . $e->getMessage());
    $errorMsg = 'Erreur lors du traitement de la requête';
    if (defined('APP_ENV') && APP_ENV === 'local') {
        $errorMsg .= ': ' . $e->getMessage();
    }
    sendJsonError($errorMsg, 500);
} catch (Error $e) {
    error_log("API users.php - Erreur fatale: " . $e->getMessage());
    $errorMsg = 'Erreur fatale lors du traitement de la requête';
    if (defined('APP_ENV') && APP_ENV === 'local') {
        $errorMsg .= ': ' . $e->getMessage();
    }
    sendJsonError($errorMsg, 500);
}

function handleGetUsers()
{
    global $pdo;

    // Fallback si PDO n'est pas disponible
    if (!isset($pdo) || !$pdo) {
        sendJsonError('Base de données non disponible', 503);
        return;
    }

    try {
        ensureUserProgressTable($pdo);

        // Pagination (accepter 'p' ou 'page' pour compatibilité LOCAL/PROD)
        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : (isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1);
        $limit = isset($_GET['limit']) ? min(100, max(10, (int) $_GET['limit'])) : 20;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $role = isset($_GET['role']) ? trim($_GET['role']) : '';

        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(Username LIKE ? OR Email LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if ($role) {
            $where[] = "Role = ?";
            $params[] = $role;
        }

        $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

        // Compter le total
        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM users {$whereClause}");
        $countStmt->execute($params);
        $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
        $total = $countResult ? (int) $countResult['total'] : 0;

        // Récupérer les utilisateurs
        $limitInt = max(1, min(100, (int) $limit));
        $offsetInt = max(0, (int) $offset);

        // Vérifier si UserProgress existe
        $userProgressExists = false;
        try {
            $checkTable = $pdo->query("SHOW TABLES LIKE 'UserProgress'");
            $userProgressExists = $checkTable->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Erreur vérification UserProgress: " . $e->getMessage());
        }

        // Construire la requête SQL avec ou sans UserProgress
        if ($userProgressExists) {
            $sql = "
                SELECT
                    Id,
                    Username,
                    Email,
                    Role,
                    COALESCE(UserLevel, '') as UserLevel,
                    CreatedAt,
                    COALESCE((SELECT COUNT(*) FROM UserProgress WHERE UserId = users.Id), 0) as ProgressCount,
                    COALESCE((SELECT SUM(XP) FROM UserProgress WHERE UserId = users.Id), 0) as TotalXP
                FROM users
                {$whereClause}
                ORDER BY CreatedAt DESC
                LIMIT {$limitInt} OFFSET {$offsetInt}
            ";
        } else {
            $sql = "
                SELECT
                    Id,
                    Username,
                    Email,
                    Role,
                    COALESCE(UserLevel, '') as UserLevel,
                    CreatedAt,
                    0 as ProgressCount,
                    0 as TotalXP
                FROM users
                {$whereClause}
                ORDER BY CreatedAt DESC
                LIMIT {$limitInt} OFFSET {$offsetInt}
            ";
        }

        $stmt = $pdo->prepare($sql);

        if (empty($params)) {
            $stmt->execute();
        } else {
            $stmt->execute($params);
        }

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($users)) {
            $users = [];
        }

        sendJsonResponse([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $total > 0 ? ceil($total / $limit) : 0,
            ],
        ]);
    } catch (PDOException $e) {
        error_log("Erreur PDO handleGetUsers: " . $e->getMessage());
        $errorMsg = 'Erreur lors de la récupération des utilisateurs';
        if (defined('APP_ENV') && APP_ENV === 'local') {
            $errorMsg .= ': ' . $e->getMessage();
        }
        sendJsonError($errorMsg, 500);
    } catch (Exception $e) {
        error_log("Erreur handleGetUsers: " . $e->getMessage());
        $errorMsg = 'Erreur lors de la récupération des utilisateurs';
        if (defined('APP_ENV') && APP_ENV === 'local') {
            $errorMsg .= ': ' . $e->getMessage();
        }
        sendJsonError($errorMsg, 500);
    }
}

function handleCreateUser()
{
    global $pdo;

    if (!isset($pdo) || !$pdo) {
        sendJsonError('Base de données non disponible', 503);
    }

    try {
        $rawInput = file_get_contents('php://input');
        if (empty($rawInput)) {
            sendJsonError('Données JSON manquantes', 400);
        }

        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonError('Données JSON invalides: ' . json_last_error_msg(), 400);
        }

        if (!$data || !is_array($data)) {
            sendJsonError('Données JSON invalides', 400);
        }

        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? 'student';
        $userLevel = $data['userLevel'] ?? '6ème';

        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            sendJsonError('Username, email et password sont requis', 400);
        }

        // Vérifier si l'utilisateur existe déjà
        $checkStmt = $pdo->prepare('SELECT Id FROM users WHERE Username = ? OR Email = ? LIMIT 1');
        $checkStmt->execute([$username, $email]);
        if ($checkStmt->fetch()) {
            sendJsonError('Un utilisateur avec ce nom ou cet email existe déjà', 400);
        }

        // Hasher le mot de passe
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Créer l'utilisateur
        $stmt = $pdo->prepare("
            INSERT INTO users (Username, Email, PasswordHash, Role, UserLevel)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$username, $email, $passwordHash, $role, $userLevel]);
        $userId = $pdo->lastInsertId();

        // Initialiser la progression (non bloquant)
        try {
            $progressStmt = $pdo->prepare("INSERT INTO UserProgress (UserId, XP, CurrentPosition) VALUES (?, 0, 1)");
            $progressStmt->execute([$userId]);
        } catch (Exception $e) {
            // Non bloquant
        }

        logAdminAction('user_create', "Création utilisateur: {$username} ({$email})", $userId);

        sendJsonResponse([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'userId' => $userId,
        ]);
    } catch (PDOException $e) {
        error_log("Erreur PDO handleCreateUser: " . $e->getMessage());
        sendJsonError('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Erreur handleCreateUser: " . $e->getMessage());
        sendJsonError('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage(), 500);
    }
}

function handleUpdateUser()
{
    global $pdo;

    if (!isset($pdo) || !$pdo) {
        sendJsonError('Base de données non disponible', 503);
    }

    try {
        $rawInput = file_get_contents('php://input');
        if (empty($rawInput)) {
            sendJsonError('Données JSON manquantes', 400);
        }

        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonError('Données JSON invalides: ' . json_last_error_msg(), 400);
        }

        if (!$data || !is_array($data)) {
            sendJsonError('Données JSON invalides', 400);
        }

        $userId = $data['userId'] ?? null;

        if (!$userId) {
            sendJsonError('ID utilisateur requis', 400);
        }

        // Récupérer l'utilisateur actuel
        $stmt = $pdo->prepare('SELECT Username, Email, Role FROM users WHERE Id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $currentUser = $stmt->fetch();

        if (!$currentUser) {
            sendJsonError('Utilisateur non trouvé', 404);
        }

        $updates = [];
        $params = [];

        if (isset($data['username'])) {
            $updates[] = "Username = ?";
            $params[] = $data['username'];
        }

        if (isset($data['email'])) {
            // Vérifier unicité
            $checkStmt = $pdo->prepare('SELECT Id FROM users WHERE Email = ? AND Id != ? LIMIT 1');
            $checkStmt->execute([$data['email'], $userId]);
            if ($checkStmt->fetch()) {
                sendJsonError('Cet email est déjà utilisé', 400);
            }
            $updates[] = "Email = ?";
            $params[] = $data['email'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $updates[] = "PasswordHash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (isset($data['role'])) {
            $updates[] = "Role = ?";
            $params[] = $data['role'];
        }

        if (isset($data['userLevel'])) {
            $updates[] = "UserLevel = ?";
            $params[] = $data['userLevel'];
        }

        if (empty($updates)) {
            sendJsonError('Aucune modification à effectuer', 400);
        }

        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE Id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        logAdminAction('user_update', "Modification utilisateur ID: {$userId}", $userId);

        sendJsonResponse([
            'success' => true,
            'message' => 'Utilisateur mis à jour avec succès',
        ]);
    } catch (PDOException $e) {
        error_log("Erreur PDO handleUpdateUser: " . $e->getMessage());
        sendJsonError('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Erreur handleUpdateUser: " . $e->getMessage());
        sendJsonError('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage(), 500);
    }
}

function handleDeleteUser()
{
    global $pdo;

    if (!isset($pdo) || !$pdo) {
        sendJsonError('Base de données non disponible', 503);
    }

    try {
        $userId = $_GET['id'] ?? null;

        if (!$userId) {
            sendJsonError('ID utilisateur requis', 400);
        }

        // Ne pas permettre la suppression de soi-même
        if ($userId == $_SESSION['user_id']) {
            sendJsonError('Vous ne pouvez pas supprimer votre propre compte', 400);
        }

        // Récupérer les infos avant suppression
        $stmt = $pdo->prepare('SELECT Username, Email FROM users WHERE Id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user) {
            sendJsonError('Utilisateur non trouvé', 404);
        }

        // Supprimer (CASCADE supprimera aussi UserProgress, etc.)
        $deleteStmt = $pdo->prepare('DELETE FROM users WHERE Id = ?');
        $deleteStmt->execute([$userId]);

        logAdminAction('user_delete', "Suppression utilisateur: {$user['Username']} ({$user['Email']})", $userId);

        sendJsonResponse([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès',
        ]);
    } catch (PDOException $e) {
        error_log("Erreur PDO handleDeleteUser: " . $e->getMessage());
        sendJsonError('Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        error_log("Erreur handleDeleteUser: " . $e->getMessage());
        sendJsonError('Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage(), 500);
    }
}

/**
 * Logger une action admin
 */
if (!function_exists('logAdminAction')) {
    function logAdminAction($action, $details, $targetUserId = null)
    {
        global $pdo;

        if (!isset($_SESSION['user_id'])) {
            return;
        }

        try {
            // Vérifier si la table AdminLogs existe
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS AdminLogs (
                    Id INT AUTO_INCREMENT PRIMARY KEY,
                    AdminId INT NOT NULL,
                    Action VARCHAR(100) NOT NULL,
                    Details TEXT,
                    TargetUserId INT NULL,
                    IpAddress VARCHAR(45),
                    UserAgent TEXT,
                    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $stmt = $pdo->prepare("
                INSERT INTO AdminLogs (AdminId, Action, Details, TargetUserId, IpAddress, UserAgent)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                $action,
                $details,
                $targetUserId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log("Erreur log admin: " . $e->getMessage());
        }
    }
}
