<?php
/**
 * Test API Logs
 */

// Créer une session d'admin
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['role'] = 'admin';
$_SESSION['logged_in'] = true;

echo "=== TEST API LOGS ===\n\n";

// Charger les dépendances
require_once __DIR__ . '/src/config/config.php';
require_once __DIR__ . '/src/database/connection.php';
require_once __DIR__ . '/src/includes/admin_auth.php';

echo "1. Vérifications préalables:\n";
echo "   - isAdmin() = " . (isAdmin() ? "✅ TRUE" : "❌ FALSE") . "\n";
echo "   - PDO = " . (isset($pdo) && $pdo ? "✅ DISPONIBLE" : "❌ INDISPONIBLE") . "\n\n";

// Tester l'API logs.php
echo "2. Test GET /api/admin/logs.php\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['type'] = 'all';
$_GET['page'] = 1;
$_GET['limit'] = 10;

ob_start();
include __DIR__ . '/src/api/admin/logs.php';
$output = ob_get_clean();

echo "   Réponse brute (premiers 500 chars):\n";
echo "   " . substr($output, 0, 500) . "\n\n";

$result = json_decode($output, true);
if ($result) {
    echo "   JSON parsé:\n";
    echo "   - success: " . ($result['success'] ? "✅" : "❌") . "\n";
    echo "   - data count: " . (count($result['data'] ?? []) ?? 0) . "\n";
    if (isset($result['error'])) {
        echo "   - error: " . $result['error'] . "\n";
    }
} else {
    echo "   ❌ Réponse non-JSON!\n";
    echo "   Première ligne: " . substr($output, 0, 100) . "\n";
}

echo "\n=== FIN TEST ===\n";
?>
