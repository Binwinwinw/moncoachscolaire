<?php
/**
 * API parent/enfant (generate code + attach code + family)
 *
 * Route:
 * - GET  /src/api/parent_family.php?action=my_codes
 * - GET  /src/api/parent_family.php?action=my_family
 * - POST /src/api/parent_family.php (action=generate_code|attach_code)
 */

header('Content-Type: application/json; charset=utf-8');

$loginSecurityPath = dirname(__DIR__) . '/includes/login_security.php';
if (is_file($loginSecurityPath)) {
    require_once $loginSecurityPath;
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(503);
    echo json_encode(['success' => false, 'error' => 'Base de données non disponible']);
    exit;
}

function json_error(string $message, int $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function json_ok(array $data = []) {
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function setParentIdForChild(int $childId): void
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT parent_user_id FROM parent_child_invites WHERE child_user_id = ? AND status = 'accepted' ORDER BY accepted_at DESC LIMIT 1");
    $stmt->execute([$childId]);
    $parentId = $stmt->fetchColumn();
    $stmt = $pdo->prepare("UPDATE users SET ParentId = ? WHERE Id = ?");
    $stmt->execute([$parentId ?: null, $childId]);
}

function generateInviteToken(int $length = 6): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $token;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$userRole = strtolower((string) ($_SESSION['user_role'] ?? ''));
$isParent = ($userRole === 'parent' || $userRole === 'parents');
$isStudent = ($userRole === 'student');

$action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'my_codes') {
        if (!$isParent) json_error('Accès parent requis', 403);

        $stmt = $pdo->prepare("SELECT invite_token, status, created_at, expired_at, accepted_at, child_user_id FROM parent_child_invites WHERE parent_user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        json_ok(['codes' => $codes]);
    }

    if ($action === 'my_family') {
        if (!$isStudent) json_error('Accès élève requis', 403);

        $stmt = $pdo->prepare("SELECT p.Id, p.Username, p.Nom, p.Prenom, p.Email, p.Telephone, pci.accepted_at FROM parent_child_invites pci JOIN users p ON p.Id = pci.parent_user_id AND p.Role IN ('parent','parents') WHERE pci.child_user_id = ? AND pci.status = 'accepted' ORDER BY pci.accepted_at DESC");
        $stmt->execute([$userId]);
        $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($parents)) {
            // compatibilité anciens rattachements
            $stmt = $pdo->prepare("SELECT p.Id, p.Username, p.Nom, p.Prenom, p.Email, p.Telephone, NULL as accepted_at FROM parent_enfants pe JOIN users p ON p.Id = pe.parent_id WHERE pe.student_id = ? ORDER BY p.Nom, p.Prenom");
            $stmt->execute([$userId]);
            $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        json_ok(['parents' => $parents]);
    }

    json_error('Action GET invalide', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (empty($csrfToken) || !function_exists('verifyCSRFToken') || !verifyCSRFToken($csrfToken)) {
        json_error('CSRF invalide', 403);
    }

    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = trim((string) ($body['action'] ?? $action));

    if ($action === 'generate_code') {
        if (!$isParent) json_error('Accès parent requis', 403);

        $expiresAt = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');
        $inviteToken = '';

        for ($i = 0; $i < 20; $i++) {
            $inviteToken = generateInviteToken();
            $check = $pdo->prepare("SELECT 1 FROM parent_child_invites WHERE invite_token = ?");
            $check->execute([$inviteToken]);
            if (!$check->fetch()) {
                break;
            }
            $inviteToken = '';
        }

        if ($inviteToken === '') {
            json_error('Impossible de générer un code', 500);
        }

        $stmt = $pdo->prepare("INSERT INTO parent_child_invites (parent_user_id, invite_token, status, created_at, expired_at) VALUES (?, ?, 'pending', NOW(), ?)");
        $stmt->execute([$userId, $inviteToken, $expiresAt]);

        json_ok(['invite_token' => $inviteToken, 'expires_at' => $expiresAt]);
    }

    if ($action === 'attach_code') {
        if (!$isStudent) json_error('Accès élève requis', 403);

        $codeInput = strtoupper(trim((string) ($body['code'] ?? '')));
        if ($codeInput === '') json_error('Code parent requis', 400);

        $stmt = $pdo->prepare("SELECT id, parent_user_id, status, expired_at FROM parent_child_invites WHERE invite_token = ?");
        $stmt->execute([$codeInput]);
        $invite = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$invite) json_error('Code invalide', 404);

        if ($invite['status'] !== 'pending') {
            json_error('Code déjà utilisé ou expiré', 409);
        }

        if (!empty($invite['expired_at'])) {
            $expiresAt = new DateTime($invite['expired_at']);
            if ($expiresAt <= new DateTime()) {
                $pdo->prepare("UPDATE parent_child_invites SET status = 'expired' WHERE id = ?")->execute([$invite['id']]);
                json_error('Code expiré', 410);
            }
        }

        // Limite 2 parents, remplacement du plus ancien si dépasse
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM parent_child_invites WHERE child_user_id = ? AND status = 'accepted'");
        $countStmt->execute([$userId]);
        $countParents = (int) $countStmt->fetchColumn();

        if ($countParents >= 2) {
            $oldStmt = $pdo->prepare("SELECT id FROM parent_child_invites WHERE child_user_id = ? AND status = 'accepted' ORDER BY accepted_at ASC LIMIT 1");
            $oldStmt->execute([$userId]);
            $oldId = $oldStmt->fetchColumn();
            if ($oldId) {
                $pdo->prepare("UPDATE parent_child_invites SET status = 'revoked' WHERE id = ?")->execute([$oldId]);
            }
        }

        $pdo->prepare("UPDATE parent_child_invites SET status = 'accepted', child_user_id = ?, accepted_at = NOW() WHERE id = ?")->execute([$userId, $invite['id']]);

        // Mise à jour ParentId pour compatibilité avec les anciens flux
        setParentIdForChild($userId);

        json_ok(['message' => 'Parent rattaché']);
    }

    json_error('Action POST invalide', 400);
}

json_error('Méthode non autorisée', 405);
