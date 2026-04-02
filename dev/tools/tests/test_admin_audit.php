<?php
// Quick test for admin_audit helper (run from project root)
require_once __DIR__ . '/../../../src/includes/admin_audit.php';
$legacyConn = __DIR__ . '/../../../db/connection.php';
if (file_exists($legacyConn)) require_once $legacyConn;
// Ensure session emulation
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'test-admin';

$result = logAdminAction('test_event', 'Test description', 999, ['foo' => 'bar']);
if ($result) echo "logAdminAction returned true\n";
else echo "logAdminAction returned false\n";

// Check last entry
try {
    $pdo = $GLOBALS['pdo'] ?? null;
    if (!$pdo) {
        echo "No PDO available for validation\n";
        exit(1);
    }
    $stmt = $pdo->query("SELECT * FROM admin_audit ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Last audit: action={$row['action']}, username={$row['username']}, meta={$row['meta']}\n";
    } else {
        echo "No rows found in admin_audit\n";
    }
} catch (Exception $e) {
    echo "Error querying admin_audit: " . $e->getMessage() . "\n";
}
