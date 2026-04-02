<?php
/**
 * Proxy pour les API admin
 * Route /api/admin/* vers /src/api/admin/*
 * 
 * Ce fichier gère le routage des API en production
 * où le serveur LiteSpeed ne peut pas faire de rewrite interne propre
 */

// Démarrer la session si elle n'est pas déjà active
// Ceci est CRITIQUE pour que les APIs puissent vérifier l'authentification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Extraire le chemin demandé depuis REQUEST_URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$path = '';

// Parser l'URI pour trouver le chemin après /api/
// Gérer aussi ?page= si le routeur passe par là
if (preg_match('#^/api/(.+)$#', $requestUri, $matches)) {
    $path = $matches[1];
    // Retirer la query string du chemin
    if (strpos($path, '?') !== false) {
        $path = strstr($path, '?', true);
    }
}

if (empty($path)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No API path specified'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Construire le chemin vers le fichier API réel
// Chercher d'abord dans le vrai chemin
$apiFile = __DIR__ . '/src/api/' . $path;

// Vérifier que le fichier existe
if (!is_file($apiFile)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'API endpoint not found',
        'path' => $path,
        'looked_in' => $apiFile
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérifier que le fichier est bien un PHP (sécurité)
if (pathinfo($apiFile, PATHINFO_EXTENSION) !== 'php') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid file type'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Inclure le fichier API
// La session est déjà démarrée, donc l'authentification fonctionnera
try {
    require $apiFile;
} catch (Exception $e) {
    error_log("API Proxy Error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

