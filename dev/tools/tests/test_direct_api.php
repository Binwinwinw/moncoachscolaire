<?php
// Test direct de l'API sans passer par le routeur
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/database/connection.php';

// Simuler session admin
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Charger l'API directement
require_once __DIR__ . '/../src/api/exercices/manage.php';
