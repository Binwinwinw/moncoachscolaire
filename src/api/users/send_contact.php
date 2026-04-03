<?php

/**
 * Endpoint: /src/api/users/send_contact.php
 * Receives POST from contact form, validates, optionally stores to DB and sends an email.
 * Returns JSON for XHR requests, or redirects to site_url('contact') with ?success=1 or ?error=1
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/middleware.php';

header('Content-Type: application/json; charset=utf-8');

// Charger site helpers + config
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}
if (!function_exists('site_url')) {
    // Best-effort: try local bootstrap
    if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
        require_once dirname(__DIR__, 2) . '/config/site_boot.php';
    }
}

// DB connection (optional)
if (!isset($pdo) || !$pdo) {
    if (is_file(dirname(__DIR__, 2) . '/database/connection.php')) {
        require_once dirname(__DIR__, 2) . '/database/connection.php';
    }
}

// Simple rate limit using session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$now = time();
$last = $_SESSION['last_contact_time'] ?? 0;
$minInterval = 30; // seconds between submissions
if ($now - $last < $minInterval) {
    $err = 'Veuillez patienter avant d\'envoyer un nouveau message.';
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'error' => $err]);
        exit;
    } else {
        $redirect = function_exists('site_url') ? site_url('users/contact', ['error' => 1]) : '/index.php?page=contact&error=1';
        header('Location: ' . $redirect);
        exit;
    }
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$csrfToken = trim((string) ($_POST['csrf_token'] ?? ''));
$sessionToken = isset($_SESSION['csrf_token']) ? (string) $_SESSION['csrf_token'] : '';
if ($csrfToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    $err = 'Jeton de sécurité invalide. Merci de recharger la page.';
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'error' => $err]);
        exit;
    }

    $redirect = function_exists('site_url') ? site_url('users/contact') . '?error=1' : '/index.php?page=contact&error=1';
    header('Location: ' . $redirect);
    exit;
}

// Read POST data
$nom = trim((string) ($_POST['nom'] ?? $_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$message = trim((string) ($_POST['message'] ?? $_POST['msg'] ?? ''));

$errors = [];
if ($nom === '' || mb_strlen($nom) < 2) {
    $errors[] = 'Merci d\'indiquer votre nom (au moins 2 caractères).';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Merci d\'indiquer une adresse email valide.';
}
if ($message === '' || mb_strlen($message) < 10) {
    $errors[] = 'Le message est trop court (au moins 10 caractères).';
}

if (!empty($errors)) {
    $err = implode(' ', $errors);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo json_encode(['success' => false, 'error' => $err]);
        exit;
    } else {
        $redirect = function_exists('site_url') ? site_url('users/contact') . '?error=1' : '/index.php?page=contact&error=1';
        header('Location: ' . $redirect);
        exit;
    }
}

// Save to DB if possible
$saved = false;
if (isset($pdo) && $pdo) {
    try {
        // Attempt to create table if not exists (safe - catches exception on limited privileges)
        $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            ip VARCHAR(45) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message, ip) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nom, $email, $message, $_SERVER['REMOTE_ADDR'] ?? null]);
        $saved = true;
    } catch (PDOException $e) {
        error_log('send_contact: impossible de sauvegarder en BDD: ' . $e->getMessage());
        // non fatal
    }
}

// Send email to admin/support
$sent = false;
$adminEmail = 'contact@moncoachscolaire.example'; // fallback
if (is_file(dirname(__DIR__, 2) . '/config/config.php')) {
    // try to get configured admin email
    try {
        require_once dirname(__DIR__, 2) . '/config/config.php';
    } catch (Exception $e) {
    }
}
if (defined('SITE_ADMIN_EMAIL')) {
    $configuredAdminEmail = (string) constant('SITE_ADMIN_EMAIL');
    if ($configuredAdminEmail !== '') {
        $adminEmail = $configuredAdminEmail;
    }
}

$subject = '[Contact] Message depuis le site - ' . htmlspecialchars($nom);
$body = "Nom: $nom\nEmail: $email\nIP: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "\n\nMessage:\n$message\n";

if (is_file(dirname(__DIR__, 2) . '/includes/send_mail.php')) {
    require_once dirname(__DIR__, 2) . '/includes/send_mail.php';
    if (function_exists('send_mail_smtp')) {
        try {
            send_mail_smtp($adminEmail, $subject, $body);
            $sent = true;
        } catch (Exception $e) {
            error_log('send_contact: send_mail_smtp failed: ' . $e->getMessage());
        }
    }
}

// Fallback to PHP mail()
if (!$sent) {
    $headers = "From: " . $nom . " <" . $email . ">\r\n" . "Reply-To: " . $email . "\r\n";
    // Suppress warnings
    try {
        @mail($adminEmail, $subject, $body, $headers);
        $sent = true;
    } catch (Exception $e) {
        error_log('send_contact: mail() failed: ' . $e->getMessage());
    }
}

// Mark last contact time
$_SESSION['last_contact_time'] = time();

// Respond
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo json_encode(['success' => ($sent || $saved), 'sent' => $sent, 'saved' => $saved]);
    exit;
} else {
    if ($sent || $saved) {
        $redirect = function_exists('site_url') ? site_url('users/contact') . '?success=1' : '/index.php?page=contact&success=1';
        header('Location: ' . $redirect);
        exit;
    } else {
        $redirect = function_exists('site_url') ? site_url('users/contact') . '?error=1' : '/index.php?page=contact&error=1';
        header('Location: ' . $redirect);
        exit;
    }
}
