<?php
// Test creating a template and enqueuing a notification
require_once __DIR__ . '/../../../src/includes/admin_auth.php';
require_once __DIR__ . '/../../../src/database/connection.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$_SESSION['user_id'] = 1; $_SESSION['username']='dev'; $_SESSION['user_role'] = 'admin';
$pdo = $GLOBALS['pdo'] ?? null; if (!$pdo) { echo "No DB\n"; exit(1); }

// Create template
$stmt = $pdo->prepare('INSERT INTO notification_templates (name,channel,subject,body,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())');
$stmt->execute(['Test Template','email','Sujet test','Corps du message']);
$tid = $pdo->lastInsertId(); echo "Template created: $tid\n";

// Enqueue
$stmt = $pdo->prepare('INSERT INTO notifications (template_id, user_id, channel, payload, status, created_at) VALUES (?,?,?,?,?,NOW())');
$stmt->execute([$tid, 1, 'email', json_encode(['email'=>'admin@example.test','subject'=>'Test','body'=>'Hello']), 'pending']);
$nid = $pdo->lastInsertId(); echo "Notification queued: $nid\n";

// Run worker
echo "Running worker...\n";
passthru('php dev/tools/notifications/send_notifications.php');

// Cleanup
$pdo->prepare('DELETE FROM notifications WHERE id = ?')->execute([$nid]);
$pdo->prepare('DELETE FROM notification_templates WHERE id = ?')->execute([$tid]);
echo "Cleanup done\n";
