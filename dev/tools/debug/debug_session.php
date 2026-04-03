<?php
// Page de debug pour tester la session
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Session Debug</h1>";

// Charger la config
require_once __DIR__ . '/../src/config/config.php';

echo "<h2>1. Configuration session</h2>";
echo "<pre>";
echo "Cookie path: " . ini_get('session.cookie_path') . "\n";
echo "Cookie lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "Cookie httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "Cookie secure: " . ini_get('session.cookie_secure') . "\n";
echo "Session save path: " . ini_get('session.save_path') . "\n";
echo "\nSERVER variables:\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
echo "</pre>";

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>2. Session active</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session name: " . session_name() . "\n";
echo "</pre>";

// Si on reçoit un paramètre pour définir la session
if (isset($_GET['set'])) {
    $_SESSION['test_time'] = time();
    $_SESSION['test_data'] = 'Login test at ' . date('H:i:s');
    echo "<p>✓ Session définie avec succès!</p>";
}

echo "<h2>3. Contenu de la session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>4. Cookies reçus</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h2>Actions</h2>";
echo "<a href='?set=1'>Définir une session</a> | ";
echo "<a href='?'>Recharger</a> | ";
echo "<a href='../public/index.php?page=login'>Aller au login</a>";
?>
session_start();
require_once __DIR__ . '/../db/connection.php';

echo "=== DEBUG SESSION & ROLE ===\n\n";
echo "Session ID: " . session_id() . "\n";
echo "Logged in: " . (isset($_SESSION['logged_in']) ? 'Yes' : 'No') . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "User Role (session): " . ($_SESSION['user_role'] ?? 'NOT SET') . "\n";

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT Id, Username, Role FROM Users WHERE Id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    echo "\n=== DATABASE ===\n";
    if ($user) {
        echo "User ID: " . $user['Id'] . "\n";
        echo "Username: " . $user['Username'] . "\n";
        echo "Role: " . $user['Role'] . "\n";
    } else {
        echo "User NOT FOUND in database!\n";
    }
}

echo "\n=== ALL SESSIONS VARS ===\n";
print_r($_SESSION);

