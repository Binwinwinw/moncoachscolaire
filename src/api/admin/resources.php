<?php

// Endpoint : /api/admin/resources.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../database/connection.php';
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../../includes/login_security.php';

$response = [ 'success' => false ];
$type = isset($_GET['type']) ? $_GET['type'] : 'list';

try {
    requireAdmin();

    // $pdo est fourni par le fichier de connection (src/database/connection.php)
    if (!isset($pdo) || !$pdo) {
        throw new Exception('Base de données non disponible');
    }
    $csrfToken = null;
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
    }
    if ($type === 'list') {
        // Liste des ressources pédagogiques
        $stmt = $pdo->query('SELECT id, Titre, Type, Lien, Date_Ajout, Actif FROM ressources ORDER BY Date_Ajout DESC LIMIT 100');
        $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = [ 'success' => true, 'data' => $resources ];
    } elseif ($type === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ajout d'une ressource
        $data = json_decode(file_get_contents('php://input'), true);
        $csrfToken = $data['csrf_token'] ?? $csrfToken;
        if (!verifyCSRFToken((string) $csrfToken)) {
            throw new Exception('CSRF invalide');
        }
        if (!empty($data['Titre']) && !empty($data['Type']) && !empty($data['Lien'])) {
            $stmt = $pdo->prepare('INSERT INTO ressources (Titre, Type, Lien, Date_Ajout, Actif) VALUES (?, ?, ?, NOW(), 1)');
            $stmt->execute([$data['Titre'], $data['Type'], $data['Lien']]);
            $response = [ 'success' => true ];
        } else {
            $response['error'] = 'Champs manquants';
        }
    } elseif ($type === 'toggle' && isset($_POST['id'])) {
        // Activation/désactivation
        $id = intval($_POST['id']);
        $csrfToken = $_POST['csrf_token'] ?? $csrfToken;
        if (!verifyCSRFToken((string) $csrfToken)) {
            throw new Exception('CSRF invalide');
        }
        $stmt = $pdo->prepare('UPDATE ressources SET Actif = 1 - Actif WHERE id = ?');
        $stmt->execute([$id]);
        $response = [ 'success' => true ];
    } else {
        $response['error'] = 'Type de requête non supporté';
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    $response['success'] = false;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
