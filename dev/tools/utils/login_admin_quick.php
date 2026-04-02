<?php
/**
 * Script de connexion rapide - POUR DEV SEULEMENT
 * Connecte l'utilisateur admin pour tester le dashboard
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>🔐 Connexion Admin Rapide</h1>\n";
echo "<pre>\n";

// Charger la BD
require_once __DIR__ . '/src/database/connection.php';

// Chercher l'admin
$stmt = $pdo->prepare("SELECT * FROM Users WHERE Role = 'admin' LIMIT 1");
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo "❌ Aucun admin trouvé dans la base!\n";
    echo "Créer un admin:\n";
    echo "INSERT INTO Users (Username, Email, PasswordHash, Role) VALUES ('admin', 'admin@example.com', 'hash', 'admin');\n";
    exit;
}

echo "✅ Admin trouvé: {$admin['Username']}\n\n";

// Connecter l'utilisateur
$_SESSION['user_id'] = $admin['Id'];
$_SESSION['logged_in'] = true;
$_SESSION['user_role'] = $admin['Role'];
$_SESSION['user_name'] = $admin['Username'];
$_SESSION['email'] = $admin['Email'];
$_SESSION['user_level'] = $admin['UserLevel'] ?? '';

echo "✅ Utilisateur connecté!\n";
echo "Session ID: " . session_id() . "\n";
echo "User ID: {$_SESSION['user_id']}\n";
echo "Role: {$_SESSION['user_role']}\n";
echo "Username: {$_SESSION['user_name']}\n\n";

echo "🎉 Redirection vers le dashboard...\n";
echo "<script>window.location.href = '/moncoachscolaire/public/index.php?page=dashboard_admin';</script>";

