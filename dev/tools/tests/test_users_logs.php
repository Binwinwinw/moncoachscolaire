<?php
/**
 * Debug ciblé pour users.php et logs.php
 * Upload à la racine public_html en prod
 * Accès: https://moncoachscolaire.fr/test_users_logs.php
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: application/json');

$rootPath = dirname(__FILE__);

$result = [
    'files_exist' => [
        'users.php' => file_exists($rootPath . '/src/api/admin/users.php'),
        'logs.php' => file_exists($rootPath . '/src/api/admin/logs.php'),
        'stats.php' => file_exists($rootPath . '/src/api/admin/stats.php'),
        'parents.php' => file_exists($rootPath . '/src/api/admin/parents.php'),
    ],
    'test_calls' => []
];

// Test direct d'exécution
session_start();

// Charger config
if (file_exists($rootPath . '/src/config/config.php')) {
    require_once $rootPath . '/src/config/config.php';
}

// Test users.php directement
if (file_exists($rootPath . '/src/api/admin/users.php')) {
    // Simuler $_GET pour l'API
    $_GET['page'] = $_GET['page'] ?? 1;
    $_GET['limit'] = $_GET['limit'] ?? 20;
    
    ob_start();
    try {
        require $rootPath . '/src/api/admin/users.php';
        $output = ob_get_clean();
        $result['test_calls']['users_direct'] = [
            'status' => 'executed',
            'output_length' => strlen($output),
            'output_start' => substr($output, 0, 100)
        ];
    } catch (Exception $e) {
        ob_end_clean();
        $result['test_calls']['users_direct'] = [
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
} else {
    $result['test_calls']['users_direct'] = ['status' => 'file_not_found'];
}

// Test logs.php directement
if (file_exists($rootPath . '/src/api/admin/logs.php')) {
    $_GET['type'] = $_GET['type'] ?? 'all';
    $_GET['page'] = $_GET['page'] ?? 1;
    $_GET['limit'] = $_GET['limit'] ?? 50;
    
    ob_start();
    try {
        require $rootPath . '/src/api/admin/logs.php';
        $output = ob_get_clean();
        $result['test_calls']['logs_direct'] = [
            'status' => 'executed',
            'output_length' => strlen($output),
            'output_start' => substr($output, 0, 100)
        ];
    } catch (Exception $e) {
        ob_end_clean();
        $result['test_calls']['logs_direct'] = [
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }
} else {
    $result['test_calls']['logs_direct'] = ['status' => 'file_not_found'];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
