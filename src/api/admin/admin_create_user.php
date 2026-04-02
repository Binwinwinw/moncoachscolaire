<?php

// API : Création d'un nouvel utilisateur (admin)
// Accès : réservé aux administrateurs authentifiés
require_once __DIR__ . '/../_core/bootstrap.php';
require_once __DIR__ . '/../../includes/admin_auth.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($pdo) || !$pdo) {
    json_error('Base de données non disponible', 503, 'ERR_DB');
}

api_require([
    'method' => 'POST',
    'auth' => true,
    'roles' => ['admin'],
    'csrf' => true,
    'rate' => ['key' => 'admin_create_user', 'limit' => 30, 'window' => 60],
]);

// Récupérer et valider les données POST
$username = validate_string($_POST['username'] ?? '', 'username', 50);
$email = validate_string($_POST['email'] ?? '', 'email', 255);
$password = (string) ($_POST['password'] ?? '');
$role = validate_enum($_POST['role'] ?? '', 'role', ['student', 'parent', 'admin']);
$userLevel = trim($_POST['userLevel'] ?? '');

if ($username === '' || $email === '') {
    json_error('Champs obligatoires manquants.', 422, 'ERR_VALIDATION');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Email invalide.', 422, 'ERR_VALIDATION');
}
if ($password === '') {
    json_error('Mot de passe requis.', 422, 'ERR_VALIDATION');
}
if ($role === 'student' && $userLevel === '') {
    json_error('Le niveau est obligatoire pour un élève.', 422, 'ERR_VALIDATION');
}

// Vérifier unicité email/username
$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE Email = ? OR Username = ?');
$stmt->execute([$email, $username]);
if ($stmt->fetchColumn() > 0) {
    json_error('Email ou nom d\'utilisateur déjà utilisé.', 409, 'ERR_CONFLICT');
}

// Hacher le mot de passe
// Hacher le mot de passe
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insérer l'utilisateur (dans PasswordHash)
$stmt = $pdo->prepare('INSERT INTO users (Username, Email, PasswordHash, Role, UserLevel, CreatedAt) VALUES (?, ?, ?, ?, ?, NOW())');
$ok = $stmt->execute([$username, $email, $hash, $role, $userLevel]);
if ($ok) {
    json_response(['message' => 'Utilisateur créé avec succès.']);
}

json_error('Erreur lors de la création.', 500, 'ERR_CREATE');
