<?php

/**
 * Helpers to log admin actions in a central `admin_audit` table
 */
if (!function_exists('logAdminAction')) {
    function logAdminAction($action, $details = null, $targetId = null, $meta = [])
    {
        // Try to get a PDO instance
        $pdo = null;
        if (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
            $pdo = $GLOBALS['pdo'];
        } else {
            // Fallback to legacy connection path
            $conn1 = __DIR__ . '/../../database/connection.php';
            $conn2 = __DIR__ . '/../../../db/connection.php';
            if (file_exists($conn1)) {
                require_once $conn1;
                if (isset($pdo)) {
                }
            } elseif (file_exists($conn2)) {
                require_once $conn2;
            }
            if (isset($pdo) && $pdo) {
                $pdo = $pdo;
            } // ensure variable available
            elseif (isset($GLOBALS['pdo']) && $GLOBALS['pdo']) {
                $pdo = $GLOBALS['pdo'];
            }
        }

        // Minimal context
        $adminId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        try {
            if (!$pdo) {
                return false;
            }

            // Ensure table exists (idempotent)
            $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_audit` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `admin_id` INT UNSIGNED DEFAULT NULL,
                `username` VARCHAR(100) DEFAULT NULL,
                `action` VARCHAR(191) NOT NULL,
                `resource` VARCHAR(191) DEFAULT NULL,
                `meta` JSON DEFAULT NULL,
                `ip` VARCHAR(45) DEFAULT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $stmt = $pdo->prepare('INSERT INTO admin_audit (admin_id, username, action, resource, meta, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $adminId,
                $username,
                $action,
                $targetId,
                json_encode($meta, JSON_UNESCAPED_UNICODE),
                $ip,
                $ua,
            ]);

            return true;
        } catch (Exception $e) {
            error_log('admin_audit::log error: ' . $e->getMessage());
            return false;
        }
    }
}
