<?php

/**
 * API Admin - Statistiques en temps réel
 * Endpoint: /api/admin/stats.php
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../../includes/admin_auth.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($pdo) || !$pdo) {
    json_error('Base de données non disponible', 503, 'ERR_DB');
}

api_require([
    'method' => 'GET',
    'auth' => true,
    'roles' => ['admin'],
    'rate' => ['key' => 'admin_stats_get', 'limit' => 120, 'window' => 60],
]);

try {
    $stats = getAdminStats();
    $stats['timestamp'] = date('Y-m-d H:i:s');
    json_response($stats);
} catch (Exception $e) {
    error_log("API stats.php - Erreur getAdminStats: " . $e->getMessage());
    json_error('Erreur lors de la récupération des statistiques', 500, 'ERR_STATS');
} catch (Error $e) {
    error_log("API stats.php - Erreur fatale getAdminStats: " . $e->getMessage());
    json_error('Erreur fatale lors de la récupération des statistiques', 500, 'ERR_STATS');
}

function getAdminStats()
{
    global $pdo;

    $stats = [];

    // Statistiques utilisateurs
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['users'] = [
            'total' => (int) $stmt->fetch()['total'],
        ];

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE Role = 'admin'");
        $stats['users']['admins'] = (int) $stmt->fetch()['total'];

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE Role = 'student'");
        $stats['users']['students'] = (int) $stmt->fetch()['total'];

        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE Role = 'parent'");
        $stats['users']['parents'] = (int) $stmt->fetch()['total'];

        // Nouveaux utilisateurs (derniers 7 jours)
        $stmt = $pdo->query("
            SELECT COUNT(*) as total
            FROM users
            WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stats['users']['newLast7Days'] = (int) $stmt->fetch()['total'];

        // Nouveaux utilisateurs (derniers 30 jours)
        $stmt = $pdo->query("
            SELECT COUNT(*) as total
            FROM users
            WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stats['users']['newLast30Days'] = (int) $stmt->fetch()['total'];
    } catch (Exception $e) {
        $stats['users'] = ['error' => $e->getMessage()];
    }

    // Statistiques progression
    try {
        // Vérifier si la table UserProgress existe
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'UserProgress'");
            $tableExists = $checkStmt->rowCount() > 0;
        } catch (Exception $e) {
            // Table n'existe pas
        }

        if ($tableExists) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM UserProgress");
            $stats['progress'] = [
                'totalRecords' => (int) $stmt->fetch()['total'],
            ];

            $stmt = $pdo->query("SELECT SUM(XP) as total FROM UserProgress");
            $result = $stmt->fetch();
            $stats['progress']['totalXP'] = (int) ($result['total'] ?? 0);

            $stmt = $pdo->query("SELECT AVG(XP) as avg FROM UserProgress");
            $result = $stmt->fetch();
            $stats['progress']['averageXP'] = round((float) ($result['avg'] ?? 0), 2);
        } else {
            // Table n'existe pas, valeurs par défaut
            $stats['progress'] = [
                'totalRecords' => 0,
                'totalXP' => 0,
                'averageXP' => 0,
            ];
        }
    } catch (Exception $e) {
        error_log("Erreur stats progression: " . $e->getMessage());
        $stats['progress'] = [
            'totalRecords' => 0,
            'totalXP' => 0,
            'averageXP' => 0,
        ];
    }

    // Statistiques exercices
    try {
        if ($pdo->query("SHOW TABLES LIKE 'ExerciseResponses'")->rowCount() > 0) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM ExerciseResponses");
            $stats['exercises'] = [
                'totalResponses' => (int) $stmt->fetch()['total'],
            ];

            $stmt = $pdo->query("SELECT COUNT(*) as total FROM ExerciseResponses WHERE Correct = 1");
            $stats['exercises']['correctResponses'] = (int) $stmt->fetch()['total'];

            $stmt = $pdo->query("SELECT AVG(Score) as avg FROM ExerciseResponses");
            $result = $stmt->fetch();
            $stats['exercises']['averageScore'] = round((float) ($result['avg'] ?? 0), 2);
        }
    } catch (Exception $e) {
        $stats['exercises'] = ['error' => $e->getMessage()];
    }

    // Statistiques système
    $stats['system'] = [
        'phpVersion' => PHP_VERSION,
        'serverTime' => date('Y-m-d H:i:s'),
        'timezone' => date_default_timezone_get(),
        'memoryUsage' => [
            'current' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
        ],
        'database' => [
            'connected' => isset($pdo) && $pdo !== null,
        ],
    ];

    // Statistiques logs admin
    try {
        // Vérifier si la table AdminLogs existe
        $tableExists = false;
        try {
            $checkStmt = $pdo->query("SHOW TABLES LIKE 'AdminLogs'");
            $tableExists = $checkStmt->rowCount() > 0;
        } catch (Exception $e) {
            // Table n'existe pas
        }

        if ($tableExists) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM AdminLogs");
            $stats['logs'] = [
                'adminActions' => (int) $stmt->fetch()['total'],
            ];

            // Actions récentes (dernières 24h)
            $stmt = $pdo->query("
                SELECT COUNT(*) as total
                FROM AdminLogs
                WHERE CreatedAt >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $stats['logs']['last24Hours'] = (int) $stmt->fetch()['total'];
        } else {
            // Table n'existe pas, valeurs par défaut
            $stats['logs'] = [
                'adminActions' => 0,
                'last24Hours' => 0,
            ];
        }
    } catch (Exception $e) {
        error_log("Erreur stats logs: " . $e->getMessage());
        $stats['logs'] = [
            'adminActions' => 0,
            'last24Hours' => 0,
        ];
    }

    return $stats;
}
