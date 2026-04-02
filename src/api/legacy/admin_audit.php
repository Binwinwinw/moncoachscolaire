<?php

// Admin audit API with pagination, filters and export
require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/deprecated.php';
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../../database/connection.php';
require_once __DIR__ . '/../../includes/admin_audit.php';

/**
 * API Legacy - Endpoint fermé
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/deprecated.php';

api_deprecated(null);
json_error('Endpoint obsolète. Merci d’utiliser la nouvelle API.', 410, 'ERR_GONE');
exit;


// Build WHERE and bindings
function build_where($params, &$bindings)
{
    $where = '1=1';
    $bindings = [];

    if (!empty($params['admin_id'])) {
        $where .= ' AND admin_id = :admin_id';
        $bindings[':admin_id'] = $params['admin_id'];
    }
    if (!empty($params['action'])) {
        $where .= ' AND action = :action';
        $bindings[':action'] = $params['action'];
    }
    if (!empty($params['resource'])) {
        $where .= ' AND resource = :resource';
        $bindings[':resource'] = $params['resource'];
    }
    if (!empty($params['from'])) {
        $where .= ' AND created_at >= :from';
        $bindings[':from'] = $params['from'];
    }
    if (!empty($params['to'])) {
        $where .= ' AND created_at <= :to';
        $bindings[':to'] = $params['to'];
    }
    if (!empty($params['search'])) {
        $where .= ' AND (username LIKE :search OR action LIKE :search OR resource LIKE :search)';
        $bindings[':search'] = '%' . $params['search'] . '%';
    }

    return $where;
}

if ($action === 'export') {
    $bindings = [];
    $where = build_where($_GET, $bindings);
    $sql = "SELECT id, admin_id, username, action, resource, meta, ip, user_agent, created_at FROM admin_audit WHERE $where ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindings);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=admin_audit_export.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id','admin_id','username','action','resource','meta','ip','user_agent','created_at']);
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($out, [$r['id'],$r['admin_id'],$r['username'],$r['action'],$r['resource'],is_string($r['meta']) ? $r['meta'] : json_encode($r['meta']),$r['ip'],$r['user_agent'],$r['created_at']]);
    }
    fclose($out);
    exit;
}

// Pagination params
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = min(200, max(10, (int) ($_GET['limit'] ?? 30)));
$offset = ($page - 1) * $limit;

$bindings = [];
$where = build_where($_GET, $bindings);

// Count total
$countSql = "SELECT COUNT(*) as total FROM admin_audit WHERE $where";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($bindings);
$total = (int) $countStmt->fetchColumn();

// Fetch page
$sql = "SELECT id, admin_id, username, action, resource, meta, ip, user_agent, created_at FROM admin_audit WHERE $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($bindings as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'data' => $rows,
    'meta' => [
        'total' => $total,
        'page' => $page,
        'per_page' => $limit,
        'pages' => (int) ceil($total / $limit),
    ],
]);
