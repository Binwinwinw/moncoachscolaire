<?php

// Envoi d'email via PHPMailer (SMTP Hostinger)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function send_mail_smtp($to, $subject, $body, $from = null, $fromName = null)
{
    if (!function_exists('env')) {
        function env($key, $default = null)
        {
            $value = getenv($key);
            return $value !== false ? $value : $default;
        }
    }
    $mail = new PHPMailer(true);
    try {
        // Paramètres serveur depuis .env
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST', 'smtp.hostinger.com');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = (int) env('MAIL_PORT', 465);
        // Expéditeur
        $from = $from ?: env('MAIL_FROM', 'support@moncoachscolaire.fr');
        $fromName = $fromName ?: env('MAIL_FROM_NAME', 'MonCoachScolaire');
        $mail->setFrom($from, $fromName);
        $mail->addReplyTo($from, $fromName);
        // Destinataire
        $mail->addAddress($to);
        // Contenu
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('[PHPMailer] Erreur : ' . $mail->ErrorInfo);
        error_log('[PHPMailer] Exception : ' . $e->getMessage());
        error_log('[PHPMailer] SMTP debug : '
            . 'Host=' . env('MAIL_HOST')
            . ', Port=' . env('MAIL_PORT')
            . ', Username=' . env('MAIL_USERNAME')
            . ', From=' . $from
            . ', To=' . $to);
        return false;
    }
}
