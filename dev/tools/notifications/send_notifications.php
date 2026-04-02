<?php
/**
 * Worker to send pending notifications
 * Run: php send_notifications.php
 */
require_once __DIR__ . '/../../../src/includes/admin_auth.php';
require_once __DIR__ . '/../../../src/includes/send_mail.php';
require_once __DIR__ . '/../../../src/database/connection.php';
require_once __DIR__ . '/../../../src/includes/admin_audit.php';

$pdo = $GLOBALS['pdo'] ?? null;
if (!$pdo) { echo "No DB\n"; exit(1); }

$limit = 50;
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE status = 'pending' AND (scheduled_at IS NULL OR scheduled_at <= NOW()) ORDER BY created_at ASC LIMIT ?");
$stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) { echo "No pending notifications\n"; exit(0); }

foreach ($rows as $n) {
    $id = $n['id'];
    $templateId = $n['template_id'];
    $channel = $n['channel'] ?: 'email';
    $payload = json_decode($n['payload'] ?? '{}', true) ?: [];
    $toUserId = $n['user_id'] ?: null;

    // Load template
    $tpl = null;
    if ($templateId) {
        $s = $pdo->prepare('SELECT * FROM notification_templates WHERE id = ?'); $s->execute([$templateId]); $tpl = $s->fetch(PDO::FETCH_ASSOC);
    }

    $subject = $tpl['subject'] ?? ($payload['subject'] ?? 'Notification MonCoachScolaire');
    $body = $tpl['body'] ?? ($payload['body'] ?? json_encode($payload));

    $sent = false;
    $errorMsg = null;

    try {
        if ($channel === 'email') {
            // Determine recipient email
            $email = null;
            if ($toUserId) {
                $u = $pdo->prepare('SELECT Email FROM users WHERE Id = ?'); $u->execute([$toUserId]); $ur = $u->fetch(); $email = $ur['Email'] ?? null;
            }
            if (!$email && !empty($payload['email'])) $email = $payload['email'];
            if (!$email) throw new Exception('No recipient email');
            $ok = send_mail_smtp($email, $subject, $body);
            if (!$ok) throw new Exception('send_mail failed');
            $sent = true;
        } else {
            // UI notification or other channels - currently simulate
            // write into notification journal (status sent)
            $sent = true;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }

    if ($sent) {
        $upd = $pdo->prepare('UPDATE notifications SET status = ?, sent_at = NOW(), retry_count = retry_count + 1 WHERE id = ?');
        $upd->execute(['sent', $id]);
        if (function_exists('logAdminAction')) logAdminAction('notification_sent','Sent notification ' . $id, $id, ['template'=>$templateId, 'channel'=>$channel]);
        echo "Sent notification #$id\n";
    } else {
        $upd = $pdo->prepare('UPDATE notifications SET status = ?, retry_count = retry_count + 1, fail_message = ? WHERE id = ?');
        $upd->execute(['failed', $errorMsg, $id]);
        if (function_exists('logAdminAction')) logAdminAction('notification_failed','Failed notification ' . $id, $id, ['error'=>$errorMsg]);
        echo "Failed notification #$id: $errorMsg\n";
    }
}
