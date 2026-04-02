<?php
// Quick test: create an integration and test connection
require_once __DIR__ . '/../../../src/includes/admin_auth.php';
require_once __DIR__ . '/../../../src/database/connection.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'devtest';
$_SESSION['user_role'] = 'admin';

$pdo = $GLOBALS['pdo'] ?? null;
if (!$pdo) { echo "No DB connection\n"; exit(1); }

// Create
$data = ['name' => 'Test Integration', 'type' => 'http', 'config' => ['url' => 'https://www.example.com'], 'enabled' => 1];
$stmt = $pdo->prepare('INSERT INTO admin_api_integrations (name,type,config,enabled,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())');
$stmt->execute([$data['name'],$data['type'],json_encode($data['config'], JSON_UNESCAPED_UNICODE),1]);
$id = (int)$pdo->lastInsertId();
if ($id) echo "Created integration id=$id\n";

// Test
$ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $data['config']['url']); curl_setopt($ch, CURLOPT_NOBODY, true); curl_setopt($ch, CURLOPT_TIMEOUT, 6); curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
echo "HTTP code: " . ($code ?: 'none') . "\n";

// Cleanup
$pdo->prepare('DELETE FROM admin_api_integrations WHERE id = ?')->execute([$id]);
echo "Cleanup done\n";
