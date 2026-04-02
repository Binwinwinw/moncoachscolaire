<?php

/**
 * API Admin - Gestion des parents et rattachements élèves
 * Endpoint: /api/admin/parents.php
 */

// Activer le rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Démarrer le buffer de sortie
ob_start();

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

// Gérer les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

// Fonction pour retourner une erreur JSON proprement
function sendJsonError($message, $code = 500)
{
    ob_end_clean();
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// Fonction pour retourner une réponse JSON
function sendJsonResponse($data)
{
    ob_end_clean();
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        error_log("Erreur encodage JSON: " . json_last_error_msg());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'encodage JSON'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo $json;
    exit;
}

try {
    // Démarrer la session si nécessaire
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Charger les dépendances
    $configPath = __DIR__ . '/../../config/config.php';
    $connectionPath = __DIR__ . '/../../database/connection.php';
    $legacyConnectionPath = __DIR__ . '/../../../db/connection.php';
    $authPath = __DIR__ . '/../../includes/admin_auth.php';
    $securityPath = __DIR__ . '/../../includes/login_security.php';

    if (!file_exists($configPath)) {
        sendJsonError('Fichier de configuration non trouvé', 500);
    }
    require_once $configPath;

    if (file_exists($connectionPath)) {
        require_once $connectionPath;
    } elseif (file_exists($legacyConnectionPath)) {
        require_once $legacyConnectionPath;
    } else {
        sendJsonError('Fichier de connexion DB non trouvé', 500);
    }

    if (!file_exists($authPath)) {
        sendJsonError('Fichier d\'authentification admin non trouvé', 500);
    }
    require_once $authPath;

    if (file_exists($securityPath)) {
        require_once $securityPath;
    }

    // Vérifier la connexion à la base de données
    if (!isset($pdo) || !$pdo) {
        sendJsonError('Base de données non disponible', 503);
    }

    // Vérifier l'authentification admin
    if (!function_exists('isAdmin')) {
        sendJsonError('Fonction d\'authentification non disponible', 500);
    }

    if (!isAdmin()) {
        sendJsonError('Accès refusé. Administrateur requis.', 403);
    }
} catch (Exception $e) {
    error_log("API parents.php - Erreur initiale: " . $e->getMessage());
    sendJsonError('Erreur lors de l\'initialisation: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("API parents.php - Erreur fatale: " . $e->getMessage());
    sendJsonError('Erreur fatale lors de l\'initialisation', 500);
}

// Fonctions utilitaires parent/enfant basées sur parent_child_invites
function getAcceptedChildrenByParent(int $parentId): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.* FROM users u
        JOIN parent_child_invites pci ON u.Id = pci.child_user_id
        WHERE pci.parent_user_id = ? AND pci.status = 'accepted' AND u.Role = 'student'");
    $stmt->execute([$parentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAcceptedParentsByChild(int $childId): array
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.* FROM users u
        JOIN parent_child_invites pci ON u.Id = pci.parent_user_id
        WHERE pci.child_user_id = ? AND pci.status = 'accepted' AND u.Role IN ('parent','parents')");
    $stmt->execute([$childId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function setParentIdForChild(int $childId): void
{
    global $pdo;
    $parents = getAcceptedParentsByChild($childId);
    $parentId = null;
    if (!empty($parents)) {
        // Garder le parent le plus récent en accepted (dernière entrée dans parent_child_invites)
        $stmt = $pdo->prepare("SELECT parent_user_id FROM parent_child_invites WHERE child_user_id = ? AND status = 'accepted' ORDER BY accepted_at DESC LIMIT 1");
        $stmt->execute([$childId]);
        $parentId = (int) $stmt->fetchColumn();
    }
    $stmt = $pdo->prepare("UPDATE users SET ParentId = ? WHERE Id = ?");
    $stmt->execute([$parentId ?: null, $childId]);
}

function generateInviteToken(int $length = 6): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $token;
}

// Vérifier la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
    $csrfToken = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
    }
    if (!$csrfToken && isset($_POST['csrf_token'])) {
        $csrfToken = $_POST['csrf_token'];
    }
    if (!function_exists('verifyCSRFToken') || !verifyCSRFToken((string) $csrfToken)) {
        sendJsonError('CSRF invalide', 403);
    }
}

try {
    switch ($method) {
        case 'GET':
            // Lister les parents ou les élèves
            handleGetParents();
            break;

        case 'POST':
            // Rattacher un élève à un parent
            handleAttachStudent();
            break;

        case 'PUT':
            // Modifier un rattachement
            handleUpdateAttachment();
            break;

        case 'DELETE':
            // Détacher un élève d'un parent
            handleDetachStudent();
            break;

        default:
            sendJsonError('Méthode non autorisée', 405);
    }
} catch (Exception $e) {
    error_log("API parents.php - Erreur: " . $e->getMessage());
    sendJsonError('Erreur serveur: ' . $e->getMessage(), 500);
} catch (Error $e) {
    error_log("API parents.php - Erreur fatale: " . $e->getMessage());
    sendJsonError('Erreur fatale serveur', 500);
}

/**
 * GET: Lister les parents ou les élèves
 * Paramètres:
 * - type: 'parents' | 'students' | 'attachments' (défaut: 'parents')
 * - parent_id: ID du parent pour voir ses élèves
 */
function handleGetParents()
{
    global $pdo;

    $type = $_GET['type'] ?? 'parents';

    if ($type === 'parents') {
        // Lister tous les parents avec le nombre d'enfants rattachés via parent_child_invites
        $stmt = $pdo->query("SELECT
                u.Id,
                u.Username,
                u.Email,
                u.Nom,
                u.Prenom,
                u.Telephone,
                u.CreatedAt,
                COALESCE(pci.children_count, 0) AS enfants_count
            FROM users u
            LEFT JOIN (
                SELECT parent_user_id, COUNT(*) AS children_count
                FROM parent_child_invites
                WHERE status = 'accepted'
                GROUP BY parent_user_id
            ) pci ON pci.parent_user_id = u.Id
            WHERE u.Role IN ('parent', 'parents')
            ORDER BY u.Nom, u.Prenom, u.Username");
        $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJsonResponse(['success' => true, 'data' => $parents]);

    } elseif ($type === 'students') {
        // Lister tous les élèves (avec ou sans parent)
        $parentId = isset($_GET['parent_id']) ? (int) $_GET['parent_id'] : null;

        $sql = "SELECT
                u.Id,
                u.Username,
                u.Email,
                u.Nom,
                u.Prenom,
                u.UserLevel,
                u.ParentId,
                (SELECT GROUP_CONCAT(p2.Username SEPARATOR ', ') FROM users p2
                    JOIN parent_child_invites pci2 ON pci2.parent_user_id = p2.Id
                    WHERE pci2.child_user_id = u.Id AND pci2.status = 'accepted') AS parent_usernames,
                (SELECT GROUP_CONCAT(p2.Id SEPARATOR ',') FROM users p2
                    JOIN parent_child_invites pci2 ON pci2.parent_user_id = p2.Id
                    WHERE pci2.child_user_id = u.Id AND pci2.status = 'accepted') AS parent_ids,
                u.CreatedAt
            FROM users u
            WHERE u.Role = 'student'";

        $params = [];
        if ($parentId !== null) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM parent_child_invites pci
                WHERE pci.child_user_id = u.Id AND pci.parent_user_id = ? AND pci.status = 'accepted')";
            $params[] = $parentId;
        }

        $sql .= " ORDER BY u.Nom, u.Prenom, u.Username";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJsonResponse(['success' => true, 'data' => $students]);

    } elseif ($type === 'attachments') {
        // Lister les rattachements confirmés parents -> élèves
        $stmt = $pdo->query("SELECT
                p.Id as parent_id,
                p.Username as parent_username,
                p.Email as parent_email,
                p.Nom as parent_nom,
                p.Prenom as parent_prenom,
                e.Id as student_id,
                e.Username as student_username,
                e.Email as student_email,
                e.Nom as student_nom,
                e.Prenom as student_prenom,
                e.UserLevel,
                pci.accepted_at
            FROM parent_child_invites pci
            JOIN users p ON p.Id = pci.parent_user_id AND p.Role IN ('parent', 'parents')
            JOIN users e ON e.Id = pci.child_user_id AND e.Role = 'student'
            WHERE pci.status = 'accepted'
            ORDER BY p.Nom, p.Prenom, e.Nom, e.Prenom");

        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJsonResponse(['success' => true, 'data' => $attachments]);
    }
}

/**
 * POST: Rattacher un élève à un parent
 * Body: { student_id: int, parent_id: int }
 */
function handleAttachStudent()
{
    global $pdo;

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['student_id']) || !isset($input['parent_id'])) {
        sendJsonError('student_id et parent_id requis', 400);
    }

    $studentId = (int) $input['student_id'];
    $parentId = (int) $input['parent_id'];

    // Vérifier que l'élève existe et est bien un élève
    $stmt = $pdo->prepare("SELECT Id FROM users WHERE Id = ? AND Role = 'student'");
    $stmt->execute([$studentId]);
    if (!$stmt->fetch()) {
        sendJsonError('Élève non trouvé', 404);
    }

    // Vérifier que le parent existe et est bien un parent
    $stmt = $pdo->prepare("SELECT Id FROM users WHERE Id = ? AND Role IN ('parent', 'parents')");
    $stmt->execute([$parentId]);
    if (!$stmt->fetch()) {
        sendJsonError('Parent non trouvé', 404);
    }

    // Vérifier s'il existe déjà un rattachement accepted parent-child
    $stmt = $pdo->prepare("SELECT id, status FROM parent_child_invites WHERE parent_user_id = ? AND child_user_id = ?");
    $stmt->execute([$parentId, $studentId]);
    $invite = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nombre de parents déjà attachés
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM parent_child_invites WHERE child_user_id = ? AND status = 'accepted'");
    $stmt->execute([$studentId]);
    $countParents = (int) $stmt->fetchColumn();

    if ($invite && $invite['status'] === 'accepted') {
        sendJsonResponse(['success' => true, 'message' => 'Élève déjà rattaché à ce parent']);
    }

    if ($countParents >= 2 && (!$invite || $invite['status'] !== 'accepted')) {
        sendJsonError('Cet élève a déjà 2 parents rattachés', 400);
    }

    if ($invite) {
        // Mise à jour d’une invitation existante
        $stmt = $pdo->prepare("UPDATE parent_child_invites SET status = 'accepted', accepted_at = NOW(), child_user_id = ? WHERE id = ?");
        $stmt->execute([$studentId, (int) $invite['id']]);
    } else {
        // Création d’une invitation acceptée pour l’admin
        $token = generateInviteToken(8);
        $stmt = $pdo->prepare("INSERT INTO parent_child_invites (parent_user_id, child_user_id, invite_token, status, created_at, accepted_at) VALUES (?, ?, ?, 'accepted', NOW(), NOW())");
        $stmt->execute([$parentId, $studentId, $token]);
    }

    // Met à jour ParentId (compatibilité legacy) si besoin
    setParentIdForChild($studentId);

    if (function_exists('logAdminAction')) {
        logAdminAction('attach_student', "Élève #$studentId rattaché au parent #$parentId", $studentId);
    }

    sendJsonResponse(['success' => true, 'message' => 'Élève rattaché avec succès']);
}

/**
 * PUT: Modifier un rattachement (changer de parent)
 * Body: { student_id: int, parent_id: int }
 */
function handleUpdateAttachment()
{
    // Même logique que POST
    handleAttachStudent();
}

/**
 * DELETE: Détacher un élève d'un parent
 * Paramètre: student_id (dans l'URL ou body)
 */
function handleDetachStudent()
{
    global $pdo;

    $studentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : null;
    $parentId = isset($_GET['parent_id']) ? (int) $_GET['parent_id'] : null;

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$studentId && isset($input['student_id'])) {
        $studentId = (int) $input['student_id'];
    }
    if (!$parentId && isset($input['parent_id'])) {
        $parentId = (int) $input['parent_id'];
    }

    if (!$studentId) {
        sendJsonError('student_id requis', 400);
    }

    // Vérifier que l'élève existe
    $stmt = $pdo->prepare("SELECT Id FROM users WHERE Id = ? AND Role = 'student'");
    $stmt->execute([$studentId]);
    if (!$stmt->fetch()) {
        sendJsonError('Élève non trouvé', 404);
    }

    if ($parentId) {
        $stmt = $pdo->prepare("SELECT id FROM parent_child_invites WHERE parent_user_id = ? AND child_user_id = ? AND status = 'accepted'");
        $stmt->execute([$parentId, $studentId]);
        $relation = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$relation) {
            sendJsonError('Rattachement parent/élève non trouvé', 404);
        }
        $stmt = $pdo->prepare("UPDATE parent_child_invites SET status = 'revoked' WHERE id = ?");
        $stmt->execute([(int) $relation['id']]);
    } else {
        // Tous les parents acceptés pour cet élève
        $stmt = $pdo->prepare("UPDATE parent_child_invites SET status = 'revoked' WHERE child_user_id = ? AND status = 'accepted'");
        $stmt->execute([$studentId]);
    }

    // Mise à jour legacy ParentId pour garder consistant
    setParentIdForChild($studentId);

    if (function_exists('logAdminAction')) {
        logAdminAction('detach_student', "Élève #$studentId détaché du parent" . ($parentId ? " #$parentId" : ''), $studentId);
    }

    sendJsonResponse(['success' => true, 'message' => 'Élève détaché avec succès']);
}

/**
 * Logger une action admin
 * NOTE: Cette fonction est déjà définie dans includes/admin_auth.php
 * On ne la redéfinit pas ici pour éviter les erreurs de redéclaration
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
