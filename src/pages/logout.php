<?php

// Démarrer la session si elle n'est pas déjà démarrée
if (function_exists('ensure_session_started')) {
    ensure_session_started();
} else {
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_start();
        } else {
            error_log('logout.php: impossible de démarrer la session — headers déjà envoyés.');
        }
    }
}

// Charger la configuration
require_once __DIR__ . '/../config/config.php';

// Vérifier si c'est un compte démo
$was_demo = !empty($_SESSION['is_demo']);

// Nettoyer toutes les variables de session (élèves, admins et parents)
$_SESSION = [];

// Détruire la session complètement
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// Vider le buffer de sortie pour éviter les problèmes de headers
if (ob_get_level() > 0) {
    ob_end_clean();
}

// Rediriger vers la landing page avec un message si c'était un compte démo
$redirectUrl = site_url('landingpage');
$reason = isset($_GET['reason']) ? trim((string) $_GET['reason']) : '';
$allowedReasons = ['timeout', 'manual'];
$reasonParam = in_array($reason, $allowedReasons, true) ? '&reason=' . urlencode($reason) : '';
if ($was_demo) {
    header('Location: ' . $redirectUrl . '&logout=demo' . $reasonParam, true, 302);
} else {
    header('Location: ' . $redirectUrl . '&logout=success' . $reasonParam, true, 302);
}
exit;
