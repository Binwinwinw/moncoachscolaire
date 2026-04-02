<?php
/**
 * Test debug - Vérifier ce qui arrive aux appels API
 * Upload à la racine de public_html/ en prod
 * Accès: https://moncoachscolaire.fr/test_api_debug.php?test=users
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: application/json');

$test = $_GET['test'] ?? 'users';

// Simuler ce que public/index.php voit
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$pageParam = (string)($_GET['page'] ?? '');

echo json_encode([
    'REQUEST_URI' => $requestUri,
    'page_param' => $pageParam,
    'parse_url_path' => parse_url($requestUri, PHP_URL_PATH),
    'SERVER' => [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'],
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
    ],
    'test_param' => $test,
    'root_path' => __DIR__,
    'files_check' => [
        'users.php' => file_exists(__DIR__ . '/src/api/admin/users.php'),
        'logs.php' => file_exists(__DIR__ . '/src/api/admin/logs.php'),
    ]
], JSON_PRETTY_PRINT);
