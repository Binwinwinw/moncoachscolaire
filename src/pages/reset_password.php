<?php
$page_title = 'Réinitialisation du mot de passe - MonCoachScolaire';
$page_css = 'login.css';

if (is_file(dirname(__DIR__, 2) . '/config/site_boot.php')) {
    require_once dirname(__DIR__, 2) . '/config/site_boot.php';
}

// S'assurer que $pdo est disponible
if (!isset($pdo) || !$pdo) {
    require_once dirname(__DIR__, 2) . '/database/connection.php';
}
if (is_file(dirname(__DIR__, 2) . '/includes/login_security.php')) {
    require_once dirname(__DIR__, 2) . '/includes/login_security.php';
}

$csrfToken = function_exists('generateCSRFToken')
    ? generateCSRFToken()
    : ((string) ($_SESSION['csrf_token'] ?? ''));

$error = null;
$success = null;
$token = $_GET['token'] ?? '';

// Vérifier le token
$user = null;
if ($token) {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        if ($reset) {
            // Récupérer l'utilisateur
            $stmtUser = $pdo->prepare("SELECT Id, Username, Email FROM users WHERE Id = ? LIMIT 1");
            $stmtUser->execute([$reset['user_id']]);
            $user = $stmtUser->fetch();
        } else {
            $error = "Lien invalide ou expiré. Veuillez refaire une demande de réinitialisation.";
        }
    }
} else {
    $error = "Lien de réinitialisation manquant.";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $postedCsrf = trim((string) ($_POST['csrf_token'] ?? ''));
    $csrfValid = function_exists('verifyCSRFToken')
        ? verifyCSRFToken($postedCsrf)
        : ($postedCsrf !== '' && hash_equals($csrfToken, $postedCsrf));

    if (!$csrfValid) {
        $error = 'Jeton de sécurité invalide. Merci de recharger la page.';
    }

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if ($error === null && strlen($new_password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($error === null && $new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif ($error === null) {
        // Mettre à jour le mot de passe
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET PasswordHash = ? WHERE Id = ?")->execute([$hash, $user['Id']]);
        // Marquer le token comme utilisé
        $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$reset['id']]);
        $success = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
        $user = null;
    }
}
?>

<main class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-8">
    <div class="max-w-md w-full bg-white rounded-xl shadow-xl p-8 mx-auto my-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-blue-600 mb-2">🔒 Réinitialisation du mot de passe</h2>
            <p class="text-gray-600 text-base">Choisis un nouveau mot de passe pour accéder à ton compte.</p>
        </div>
        <?php if ($success): ?>
            <div class="bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-500 rounded-lg p-4 mb-6 flex items-center gap-3 text-green-800">
                <span class="text-xl">✅</span>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
            <div class="text-center mt-8 pt-8 border-t border-gray-200">
                <a href="<?php echo site_url('login'); ?>" class="text-blue-600 hover:text-blue-800 font-medium">&#8592; Retour à la connexion</a>
            </div>
        <?php elseif ($error): ?>
            <div class="bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-500 rounded-lg p-4 mb-6 flex items-center gap-3 text-red-800 animate-pulse" id="error-message" role="alert">
                <span class="text-xl">❌</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <div class="text-center mt-8 pt-8 border-t border-gray-200">
                <a href="<?php echo site_url('forgot_password'); ?>" class="text-blue-600 hover:text-blue-800 font-medium">&#8592; Refaire une demande</a>
            </div>
        <?php elseif ($user): ?>
            <form method="POST" class="flex flex-col gap-6" autocomplete="off" id="reset-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="flex flex-col mb-4">
                    <label for="new_password" class="font-semibold text-gray-700 mb-2 text-base">Nouveau mot de passe <span class="text-red-500 font-bold ml-1">*</span></label>
                    <input type="password" id="new_password" name="new_password" required minlength="8" placeholder="Nouveau mot de passe"
                        class="px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        aria-describedby="new-password-help new-password-error"
                        aria-invalid="false">
                    <span class="text-gray-500 text-xs mt-1 italic block" id="new-password-help">Minimum 8 caractères</span>
                    <span class="text-red-600 text-sm mt-1 min-h-5 block" id="new-password-error" role="alert"></span>
                </div>
                <div class="flex flex-col mb-4">
                    <label for="confirm_password" class="font-semibold text-gray-700 mb-2 text-base">Confirmer le mot de passe <span class="text-red-500 font-bold ml-1">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Confirmer le mot de passe"
                        class="px-4 py-3 border-2 border-gray-300 rounded-lg text-base transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        aria-describedby="confirm-password-help confirm-password-error"
                        aria-invalid="false">
                    <span class="text-gray-500 text-xs mt-1 italic block" id="confirm-password-help">Répète le mot de passe</span>
                    <span class="text-red-600 text-sm mt-1 min-h-5 block" id="confirm-password-error" role="alert"></span>
                </div>
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-500 text-white border-none py-4 px-8 rounded-lg text-lg font-semibold cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/30 mt-4" id="submit-btn">
                    <span class="btn-text">Réinitialiser le mot de passe</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>
