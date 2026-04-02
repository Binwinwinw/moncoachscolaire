<?php
// demo_login.php — connecte un visiteur à l'utilisateur 'demo@example.com' existant
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db/connection.php';

// Si la base n'est pas disponible (par ex. mode local sans .env), créer une session 'visiteur' sans table Users
if (!isset($pdo) || !$pdo) {
    $_SESSION['user_id'] = 0; // id virtuel
    $_SESSION['user_name'] = 'Visiteur';
    $_SESSION['user_level'] = '6eme';
    $_SESSION['is_demo'] = true;

    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
    header('Location: ' . $redirect);
    exit;
}

// Cherche l'utilisateur demo
// Trouve l'utilisateur demo (se base sur l'email demo@example.com)
$stmt = $pdo->prepare('SELECT Id, Username, UserLevel FROM Users WHERE Email = ? LIMIT 1');
$stmt->execute(['demo@example.com']);
$user = $stmt->fetch();

// Si aucun user demo n'existe, on le crée automatiquement (utile après reset de la base)
if (!$user) {
    // Respect read-only mode: don't create demo user if DB is set to read-only
    $dbReadOnly = isset($dbReadOnly) ? $dbReadOnly : (filter_var(getenv('DB_READ_ONLY') ?: 'false', FILTER_VALIDATE_BOOLEAN));
    if ($dbReadOnly) {
        // fallback to a guest session rather than creating a DB entry
        $_SESSION['user_id'] = 0;
        $_SESSION['user_name'] = 'Visiteur';
        $_SESSION['user_level'] = '6eme';
        $_SESSION['is_demo'] = true;
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
        header('Location: ' . $redirect);
        exit;
    }
    $insert = $pdo->prepare('INSERT INTO Users (Username, Email, PasswordHash, Role, UserLevel) VALUES (?, ?, ?, ?, ?)');
    // PasswordHash pourra être remplacé si l'utilisateur crée un vrai compte — on laisse une valeur simple mais non exploitable
    $insert->execute(['demo', 'demo@example.com', 'demo-hash', 'student', '6eme']);
    $stmt->execute(['demo@example.com']);
    $user = $stmt->fetch();
}

if ($user) {
    // Crée une session pour l'utilisateur de démonstration
    $_SESSION['user_id'] = $user['Id'];
    $_SESSION['user_name'] = $user['Username'];
    $_SESSION['user_level'] = $user['UserLevel'];
    $_SESSION['is_demo'] = true;

    // Redirection vers la page demandée si fournie
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
    header('Location: ' . $redirect);
    exit;
} else {
    // Si aucun utilisateur demo n'existe, rediriger vers la page d'inscription
    header('Location: ' . site_url('register'));
    exit;
}
