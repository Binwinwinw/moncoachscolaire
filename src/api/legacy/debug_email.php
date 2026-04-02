<?php

// API Admin - Debug Email/SMTP
// Endpoint: /api/admin/debug_email.php

/**
 * API Legacy - Endpoint fermé
 */

require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../_core/response.php';
require_once __DIR__ . '/../_core/deprecated.php';

api_deprecated(null);
json_error('Endpoint obsolète. Merci d’utiliser la nouvelle API.', 410, 'ERR_GONE');
echo json_encode(['success' => false, 'error' => 'Accès refusé. Administrateur requis.']);
exit;


if (!isDebugEnabled()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Debug désactivé']);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

switch ($action) {
    case 'smtp':
        // Afficher la config SMTP (hors mot de passe)
        $smtp = [
            'host' => getenv('MAIL_HOST'),
            'port' => getenv('MAIL_PORT'),
            'username' => getenv('MAIL_USERNAME'),
            'from' => getenv('MAIL_FROM'),
            'from_name' => getenv('MAIL_FROM_NAME'),
            'secure' => 'ssl/tls',
        ];
        echo json_encode(['success' => true, 'smtp' => $smtp]);
        exit;
    case 'smtp_log':
        // Lire le dernier log PHPMailer (error_log)
        $logFile = ini_get('error_log');
        $log = '';
        if ($logFile && file_exists($logFile)) {
            $lines = file($logFile);
            $lines = array_reverse($lines);
            foreach ($lines as $line) {
                if (strpos($line, 'PHPMailer') !== false) {
                    $log = $line;
                    break;
                }
            }
        }
        echo json_encode(['success' => true, 'log' => $log]);
        exit;
    case 'send':
        // Envoyer un email de test
        $to = $_POST['to'] ?? '';
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Adresse email invalide']);
            exit;
        }
        $subject = 'Test SMTP MonCoachScolaire';
        $body = "Ceci est un email de test envoyé depuis le panneau d'administration MonCoachScolaire.";
        $ok = send_mail_smtp($to, $subject, $body);
        echo json_encode(['success' => $ok, 'message' => $ok ? 'Email envoyé avec succès.' : 'Échec de l\'envoi. Voir logs.']);
        exit;
    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
        exit;
}
