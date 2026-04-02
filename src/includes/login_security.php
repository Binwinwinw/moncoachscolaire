<?php
/**
 * Fonctions de sécurité pour le login
 */

/**
 * Limite le nombre de tentatives de connexion (rate limiting)
 */
function checkLoginAttempts($identifier)
{
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    $key = md5($identifier . $_SERVER['REMOTE_ADDR']);
    $attempts = $_SESSION['login_attempts'][$key] ?? ['count' => 0, 'time' => 0];

    // Réinitialiser après 15 minutes
    if (time() - $attempts['time'] > 900) {
        $_SESSION['login_attempts'][$key] = ['count' => 0, 'time' => time()];
        $attempts = $_SESSION['login_attempts'][$key];
    }

    // Limite : 5 tentatives par 15 minutes
    if ($attempts['count'] >= 5) {
        $remaining = 900 - (time() - $attempts['time']);
        return [
            'allowed' => false,
            'remaining' => $remaining,
            'message' => "Trop de tentatives de connexion. Réessayez dans " . ceil($remaining / 60) . " minutes.",
        ];
    }

    return ['allowed' => true];
}

/**
 * Enregistre une tentative de connexion échouée
 */
function recordFailedAttempt($identifier)
{
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }

    $key = md5($identifier . $_SERVER['REMOTE_ADDR']);
    $attempts = $_SESSION['login_attempts'][$key] ?? ['count' => 0, 'time' => time()];

    // Réinitialiser si plus de 15 minutes
    if (time() - $attempts['time'] > 900) {
        $attempts = ['count' => 0, 'time' => time()];
    }

    $attempts['count']++;
    $attempts['time'] = time();
    $_SESSION['login_attempts'][$key] = $attempts;
}

/**
 * Réinitialise les tentatives après une connexion réussie
 */
function resetLoginAttempts($identifier)
{
    if (!isset($_SESSION['login_attempts'])) {
        return;
    }

    $key = md5($identifier . $_SERVER['REMOTE_ADDR']);
    unset($_SESSION['login_attempts'][$key]);
}

/**
 * Génère un token CSRF
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie le token CSRF
 */
function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Valide le format d'un nom d'utilisateur
 */
function validateUsername($username)
{
    // Permet username ou email (car login.php accepte les deux)
    if (empty($username)) {
        return ['valid' => false, 'error' => 'Le nom d\'utilisateur ou l\'email est requis'];
    }

    if (strlen($username) < 3 || strlen($username) > 255) {
        return ['valid' => false, 'error' => 'Le nom d\'utilisateur ou l\'email doit contenir entre 3 et 255 caractères'];
    }

    // Si c'est un email, valider le format email
    if (strpos($username, '@') !== false) {
        if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Format d\'email invalide'];
        }
    } else {
        // Si c'est un username, valider qu'il ne contient que des caractères autorisés
        if (!preg_match('/^[a-zA-Z0-9_@.]+$/', $username)) {
            return ['valid' => false, 'error' => 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres, underscores, @ et points'];
        }
    }

    return ['valid' => true];
}

/**
 * Valide le format d'un mot de passe
 */
function validatePassword($password)
{
    if (empty($password)) {
        return ['valid' => false, 'error' => 'Le mot de passe est requis'];
    }

    if (strlen($password) < 1) {
        return ['valid' => false, 'error' => 'Le mot de passe ne peut pas être vide'];
    }

    // Exiger minimum 8 caractères pour la sécurité
    if (strlen($password) < 8) {
        return ['valid' => false, 'error' => 'Le mot de passe doit contenir au moins 8 caractères'];
    }

    return ['valid' => true];
}

/**
 * Nettoie les entrées utilisateur
 */
function sanitizeInput($input)
{
    return trim(strip_tags($input));
}

/**
 * Échappe les données pour l'affichage HTML
 */
function escapeOutput($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

?>

