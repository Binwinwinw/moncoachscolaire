<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$projectRoot = dirname(__DIR__, 3);

$loginSecurityPath = $projectRoot . '/src/includes/login_security.php';
if (is_file($loginSecurityPath)) {
    require_once $loginSecurityPath;
}

$adminAuditPath = $projectRoot . '/src/includes/admin_audit.php';
if (is_file($adminAuditPath)) {
    require_once $adminAuditPath;
}

if (!isset($pdo) || !$pdo instanceof PDO) {
    $connectionPath = $projectRoot . '/src/database/connection.php';
    if (is_file($connectionPath)) {
        require_once $connectionPath;
    }
}

$pdoConnection = null;
if (isset($pdo) && $pdo instanceof PDO) {
    $pdoConnection = $pdo;
} elseif (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
    $pdoConnection = $GLOBALS['pdo'];
}

if (!function_exists('json_error')) {
    function json_error(string $message, int $code = 400, array $meta = []): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => $code,
            'meta' => $meta,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('json_ok')) {
    function json_ok(array $data = []): void
    {
        echo json_encode([
            'success' => true,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('subscription_table_exists')) {
    function subscription_table_exists(PDO $pdo, string $tableName): bool
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
        $stmt->execute([$tableName]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

if (!function_exists('subscription_column_exists')) {
    function subscription_column_exists(PDO $pdo, string $tableName, string $columnName): bool
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
        $stmt->execute([$tableName, $columnName]);
        return (int) $stmt->fetchColumn() > 0;
    }
}

if (!function_exists('get_parent_subscription_endpoint_url')) {
    function get_parent_subscription_endpoint_url(): string
    {
        if (function_exists('site_url')) {
            return (string) site_url('api/parents/subscription_action');
        }

        return '/index.php?page=api/parents/subscription_action';
    }
}

if (!function_exists('get_parent_subscription_page_url')) {
    function get_parent_subscription_page_url(): string
    {
        if (function_exists('site_url')) {
            return (string) site_url('parents/suivi_abo');
        }

        return '/index.php?page=parents/suivi_abo';
    }
}

if (!function_exists('respond_or_redirect')) {
    function respond_or_redirect(array $payload, bool $success): void
    {
        $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        $isJsonRequest = stripos($accept, 'application/json') !== false;
        $redirectMode = filter_input(INPUT_POST, 'redirect', FILTER_VALIDATE_INT) === 1;

        if ($redirectMode && !$isJsonRequest) {
            $baseUrl = get_parent_subscription_page_url();
            $query = [
                'op_status' => $success ? 'success' : 'error',
                'op_action' => $payload['action'] ?? '',
                'op_effective' => $payload['effective_date'] ?? '',
                'op_request' => $payload['request_id'] ?? '',
                'op_persisted' => !empty($payload['persisted']) ? '1' : '0',
                'op_message' => $payload['message'] ?? ($success ? 'Action traitée.' : 'Action refusée.'),
            ];

            $target = $baseUrl . '?' . http_build_query($query);
            header('Location: ' . $target, true, 303);
            exit;
        }

        if ($success) {
            json_ok($payload);
        }

        json_error((string) ($payload['message'] ?? 'Erreur'), 400, $payload);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Méthode non autorisée', 405);
}

if (!$pdoConnection instanceof PDO) {
    json_error('Connexion BDD indisponible', 500);
}

$parentId = (int) ($_SESSION['parent_id'] ?? $_SESSION['user_id'] ?? 0);
$userRole = strtolower((string) ($_SESSION['user_role'] ?? ''));
$isParent = in_array($userRole, ['parent', 'parents'], true);
$isAdmin = function_exists('isAdmin') && isAdmin();

if (!$isAdmin && (!$isParent || $parentId <= 0)) {
    json_error('Accès parent requis', 403);
}

$csrfToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? ''));
$csrfValid = function_exists('verifyCSRFToken')
    ? verifyCSRFToken($csrfToken)
    : (isset($_SESSION['csrf_token']) && $csrfToken !== '' && hash_equals((string) $_SESSION['csrf_token'], $csrfToken));

if (!$csrfValid) {
    json_error('CSRF invalide', 403);
}

$action = strtolower(trim((string) ($_POST['action'] ?? '')));
$requestedPlan = strtolower(trim((string) ($_POST['target_plan'] ?? '')));
$confirmed = filter_input(INPUT_POST, 'confirmed', FILTER_VALIDATE_INT) === 1;

$allowedActions = ['change-plan', 'pause', 'cancel'];
if (!in_array($action, $allowedActions, true)) {
    respond_or_redirect([
        'action' => $action,
        'message' => 'Action inconnue.',
    ], false);
}

if (!$confirmed) {
    respond_or_redirect([
        'action' => $action,
        'message' => 'Confirmation requise avant exécution.',
    ], false);
}

if ($action === 'change-plan' && !in_array($requestedPlan, ['decouverte', 'suivi', 'famille'], true)) {
    respond_or_redirect([
        'action' => $action,
        'message' => 'Formule cible invalide.',
    ], false);
}

$requestId = 'abo_' . date('YmdHis') . '_' . bin2hex(random_bytes(4));
$effectiveDate = (string) ($_POST['effective_date'] ?? '');
if ($effectiveDate === '') {
    $effectiveDate = date('Y-m-d');
}

$trace = [];
$persisted = false;

$candidates = [
    ['table' => 'parent_subscriptions', 'ownerColumn' => 'parent_user_id', 'planColumns' => ['plan_name', 'plan']],
    ['table' => 'subscriptions', 'ownerColumn' => 'user_id', 'planColumns' => ['plan_name', 'plan']],
    ['table' => 'billing_subscriptions', 'ownerColumn' => 'user_id', 'planColumns' => ['plan_name', 'plan']],
];

foreach ($candidates as $candidate) {
    $tableName = $candidate['table'];
    $ownerColumn = $candidate['ownerColumn'];

    if (!subscription_table_exists($pdoConnection, $tableName)) {
        $trace[] = ['table' => $tableName, 'result' => 'missing_table'];
        continue;
    }

    if (!subscription_column_exists($pdoConnection, $tableName, $ownerColumn)) {
        $trace[] = ['table' => $tableName, 'result' => 'missing_owner_column', 'owner_column' => $ownerColumn];
        continue;
    }

    if (!subscription_column_exists($pdoConnection, $tableName, 'id')) {
        $trace[] = ['table' => $tableName, 'result' => 'missing_id_column'];
        continue;
    }

    $findSql = "SELECT id FROM {$tableName} WHERE {$ownerColumn} = ? ORDER BY id DESC LIMIT 1";
    $findStmt = $pdoConnection->prepare($findSql);
    $findStmt->execute([$parentId]);
    $rowId = (int) ($findStmt->fetchColumn() ?: 0);

    if ($rowId <= 0) {
        $trace[] = ['table' => $tableName, 'result' => 'no_row_for_parent'];
        continue;
    }

    $setParts = [];
    $params = [];

    if ($action === 'pause' || $action === 'cancel') {
        if (subscription_column_exists($pdoConnection, $tableName, 'auto_renew')) {
            $setParts[] = 'auto_renew = ?';
            $params[] = 0;
        }
    }

    if (subscription_column_exists($pdoConnection, $tableName, 'status')) {
        $statusValue = $action === 'pause' ? 'paused' : ($action === 'cancel' ? 'cancel_requested' : 'active');
        $setParts[] = 'status = ?';
        $params[] = $statusValue;
    }

    if ($action === 'change-plan') {
        foreach ($candidate['planColumns'] as $planColumn) {
            if (subscription_column_exists($pdoConnection, $tableName, $planColumn)) {
                $setParts[] = $planColumn . ' = ?';
                $params[] = $requestedPlan;
                break;
            }
        }
    }

    if (subscription_column_exists($pdoConnection, $tableName, 'updated_at')) {
        $setParts[] = 'updated_at = NOW()';
    }

    if (empty($setParts)) {
        $trace[] = ['table' => $tableName, 'result' => 'no_mutable_columns'];
        continue;
    }

    $updateSql = "UPDATE {$tableName} SET " . implode(', ', $setParts) . ' WHERE id = ?';
    $params[] = $rowId;
    $updateStmt = $pdoConnection->prepare($updateSql);
    $updateStmt->execute($params);

    $persisted = true;
    $trace[] = [
        'table' => $tableName,
        'result' => 'updated',
        'row_id' => $rowId,
        'updated_columns' => $setParts,
    ];
    break;
}

$auditMeta = [
    'request_id' => $requestId,
    'parent_id' => $parentId,
    'action' => $action,
    'target_plan' => $requestedPlan !== '' ? $requestedPlan : null,
    'effective_date' => $effectiveDate,
    'persisted' => $persisted,
    'trace' => $trace,
];

$logged = false;
if (function_exists('logAdminAction')) {
    $logged = (bool) logAdminAction('parent_subscription_' . $action, null, 'parents/suivi_abo', $auditMeta);
}

$message = $persisted
    ? 'Action enregistrée et appliquée sur la source de données active.'
    : 'Action enregistrée. Aucune table compatible trouvée, demande tracée pour traitement métier.';

respond_or_redirect([
    'request_id' => $requestId,
    'action' => $action,
    'target_plan' => $requestedPlan !== '' ? $requestedPlan : null,
    'effective_date' => $effectiveDate,
    'persisted' => $persisted,
    'audit_logged' => $logged,
    'trace' => $trace,
    'message' => $message,
], true);
