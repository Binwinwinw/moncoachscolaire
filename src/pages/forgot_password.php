
<?php
$page_title = 'Mot de passe oublié - MonCoachScolaire';
$page_css = 'login.css';
if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}
if (!isset($pdo) || !$pdo) {
    require_once dirname(__DIR__, 2) . '/database/connection.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/login_security.php')) {
    require_once dirname(__DIR__, 2) . '/includes/login_security.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/security_logger.php')) {
    require_once dirname(__DIR__, 2) . '/includes/security_logger.php';
}

global $pdo;

$csrfToken = function_exists('generateCSRFToken')
    ? generateCSRFToken()
    : ((string) ($_SESSION['csrf_token'] ?? ''));

$email_sent = false;
$error = null;
$success = null;
$securityLogger = ensureSecurityLogger($pdo ?? null);
if (!isset($_SESSION['request_id']) || $_SESSION['request_id'] === '') {
    $_SESSION['request_id'] = bin2hex(random_bytes(8));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedCsrf = trim((string) ($_POST['csrf_token'] ?? ''));
    $csrfValid = function_exists('verifyCSRFToken')
        ? verifyCSRFToken($postedCsrf)
        : ($postedCsrf !== '' && hash_equals($csrfToken, $postedCsrf));

    if (!$csrfValid) {
        $error = 'Jeton de sécurité invalide. Merci de recharger la page.';
        if ($securityLogger instanceof SecurityLogger) {
            $securityLogger->log('auth_reset_request', [
                'scope' => 'forgot_password',
                'result' => 'failure',
                'identifier' => $email ?? null,
                'failure_reason' => 'csrf_invalid',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'request_id' => $_SESSION['request_id'] ?? null,
            ]);
        }
    }

    $email = trim($_POST['email'] ?? '');
    if ($error === null && (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $error = "Merci de saisir une adresse email valide.";
        if ($securityLogger instanceof SecurityLogger) {
            $securityLogger->log('auth_reset_request', [
                'scope' => 'forgot_password',
                'result' => 'failure',
                'identifier' => $email,
                'failure_reason' => 'invalid_email',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'request_id' => $_SESSION['request_id'] ?? null,
            ]);
        }
    } elseif ($error === null) {
        $rateLimit = checkLoginAttempts($email, 'forgot_password');
        if (!$rateLimit['allowed']) {
            $error = $rateLimit['message'];
            if ($securityLogger instanceof SecurityLogger) {
                $securityLogger->log('auth_blocked', [
                    'scope' => 'forgot_password',
                    'result' => 'blocked',
                    'identifier' => $email,
                    'failure_reason' => 'rate_limited',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'request_id' => $_SESSION['request_id'] ?? null,
                    'metadata' => ['remaining' => $rateLimit['remaining'] ?? null],
                ]);
            }
        } else {
            recordFailedAttempt($email, 'forgot_password');
            $success = "Si un compte existe pour cet email, un lien de réinitialisation a été envoyé.";
            $email_sent = true;
            if (isset($pdo) && $pdo instanceof PDO) {
                $stmt = $pdo->prepare("SELECT Id, Email FROM users WHERE Email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $token);
                    $expires_at = date('Y-m-d H:i:s', time() + 3600);
                    $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ?")->execute([$email]);
                    $pdo->prepare("INSERT INTO password_resets (user_id, email, token, expires_at, used) VALUES (?, ?, ?, ?, 0)")
                        ->execute([$user['Id'], $email, $tokenHash, $expires_at]);
                    $reset_link = site_url('reset_password', ['token' => $token]);
                    if ($securityLogger instanceof SecurityLogger) {
                        $securityLogger->log('auth_reset_request', [
                            'scope' => 'forgot_password',
                            'result' => 'success',
                            'user_id' => $user['Id'] ?? null,
                            'identifier' => $email,
                            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                            'request_id' => $_SESSION['request_id'] ?? null,
                        ]);
                    }
                    require_once dirname(__DIR__, 2) . '/includes/send_mail.php';
                    $subject = 'Réinitialisation de votre mot de passe - MonCoachScolaire';
                    $message = "Bonjour,\n\nVous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous pour choisir un nouveau mot de passe :\n\n$reset_link\n\nCe lien est valable 1 heure. Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.";
                    send_mail_smtp($email, $subject, $message);
                }
            }
        }
    }
}
?>
<main class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-8">
    <div class="max-w-md w-full bg-white rounded-xl shadow-xl p-8 mx-auto my-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-blue-600 mb-2">🔑 Mot de passe oublié</h2>
            <p class="text-gray-600 text-base">Saisis ton adresse email pour recevoir un lien de réinitialisation.</p>
        </div>
        <?php if ($success): ?>
            <div class="bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-500 rounded-lg p-4 mb-6 flex items-center gap-3 text-green-800">
                <span class="text-xl">✅</span>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-500 rounded-lg p-4 mb-6 flex items-center gap-3 text-red-800 animate-pulse" id="error-message" role="alert">
                    <span class="text-xl">❌</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            <form method="POST" class="flex flex-col gap-6" autocomplete="off" id="forgot-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="flex flex-col mb-4">
                    <label for="email" class="font-semibold text-gray-700 mb-2 text-base">Adresse email utilisée pour le compte <span class="text-red-500 font-bold ml-1">*</span></label>
                    <input type="email" id="email" name="email" required placeholder="exemple@domaine.com" autocomplete="email"
                        class="px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        aria-describedby="email-error email-help"
                        aria-invalid="<?php echo isset($error) ? 'true' : 'false'; ?>">
                    <span class="text-gray-500 text-xs mt-1 italic block" id="email-help">Un email valide est requis</span>
                    <span class="text-red-600 text-sm mt-1 min-h-5 block" id="email-error" role="alert"></span>
                </div>
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 text-white border-none py-4 px-8 rounded-lg text-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 mt-4" id="submit-btn">
                    <span class="btn-text">Envoyer le lien de réinitialisation</span>
                </button>
            </form>
        <?php endif; ?>
        <div class="text-center mt-8 pt-8 border-t border-gray-200">
            <a href="<?php echo site_url('login'); ?>" class="text-blue-600 hover:text-blue-800 font-medium">&#8592; Retour à la connexion</a>
        </div>
    </div>
</main>
